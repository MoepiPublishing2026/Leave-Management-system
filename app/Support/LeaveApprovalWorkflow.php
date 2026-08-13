<?php

namespace App\Support;

use App\Mail\SeniorLeaveDecisionMail;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestConversation;
use App\Models\LeaveRequestMessage;
use App\Models\User;
use App\Notifications\LeaveRecommendationNotification;
use App\Notifications\LeaveStatusNotification;
use App\Notifications\NewLeaveRequestNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class LeaveApprovalWorkflow
{
    public static function ensureHandledColumnsExist(): void
    {
        if (Schema::hasColumn('leave_requests', 'handled_by')) {
            return;
        }

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->string('handled_action', 40)->nullable();
        });
    }

    public static function isAwaitingSenior(LeaveRequest $leave): bool
    {
        return strtolower(trim((string) ($leave->status ?? ''))) === 'pending_senior';
    }

    public static function isAwaitingManager(LeaveRequest $leave): bool
    {
        return strtolower(trim((string) ($leave->status ?? ''))) === 'pending'
            && ! $leave->isManagerApplicant();
    }

    /** Manager recommends approve/reject and forwards to Senior Manager. */
    public static function recommend(LeaveRequest $leave, User $manager, string $recommendation, ?string $comment = null): void
    {
        $recommendation = strtolower(trim($recommendation)) === 'reject' ? 'reject' : 'approve';

        $leave->manager_recommendation = $recommendation;
        $leave->manager_recommendation_comment = $comment !== null && trim($comment) !== '' ? trim($comment) : null;
        $leave->recommended_by = $manager->id;
        $leave->recommended_at = Carbon::now();
        $leave->status = 'pending_senior';
        $leave->pending_review_reminder_stage = 0;
        $leave->last_pending_review_reminder_at = null;
        $leave->save();

        $conversation = LeaveRequestConversation::firstOrCreate([
            'leave_request_id' => $leave->id,
            'manager_id' => $manager->id,
        ]);
        $label = $recommendation === 'approve' ? 'Recommend Approve' : 'Recommend Reject';
        $body = 'Manager recommendation: '.$label.'.';
        if ($leave->manager_recommendation_comment) {
            $body .= ' Comments: '.$leave->manager_recommendation_comment;
        }
        LeaveRequestMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $manager->id,
            'body' => $body,
        ]);
        $conversation->touch();

        self::notifySeniorsForDecision($leave->fresh(['user']));

        $applicant = $leave->user;
        if ($applicant) {
            try {
                $applicant->notify(new LeaveRecommendationNotification($leave->fresh(['user'])));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /** Prefer the assigned senior manager; fall back to all active admins. */
    public static function seniorsForLeave(LeaveRequest $leave): Collection
    {
        $leave->loadMissing('user');
        $seniors = collect();

        $pushSenior = function (?int $userId) use (&$seniors): void {
            if (! $userId) {
                return;
            }
            $senior = User::query()
                ->where('role', 'admin')
                ->where('is_active', true)
                ->whereKey($userId)
                ->first();
            if ($senior) {
                $seniors->push($senior);
            }
        };

        // Manager applicant → their assigned senior (manager_id).
        if ($leave->isManagerApplicant()) {
            $pushSenior((int) ($leave->user?->manager_id ?? 0));
        }

        // After manager recommendation → recommender's assigned senior.
        if ($leave->recommended_by) {
            $recommender = User::query()->find($leave->recommended_by);
            $pushSenior((int) ($recommender?->manager_id ?? 0));
        }

        // Employee request before/without recommendation → employee's manager's senior.
        if ($seniors->isEmpty() && $leave->user?->manager_id) {
            $manager = User::query()->managers()->whereKey($leave->user->manager_id)->first();
            $pushSenior((int) ($manager?->manager_id ?? 0));
        }

        if ($seniors->isEmpty()) {
            return User::query()
                ->where('role', 'admin')
                ->where('is_active', true)
                ->get()
                ->unique('id')
                ->values();
        }

        return $seniors->unique('id')->values();
    }

    public static function notifySeniorsForDecision(LeaveRequest $leave): void
    {
        $admins = self::seniorsForLeave($leave);
        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send(new SeniorLeaveDecisionMail($leave));
            } catch (\Throwable $e) {
                report($e);
            }
        }
        if ($admins->isNotEmpty()) {
            try {
                Notification::send($admins, new NewLeaveRequestNotification($leave));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public static function finalize(LeaveRequest $leave, User $decider, string $action, ?string $rejectionReason = null): string
    {
        $normalized = strtolower(trim($action));
        $action = in_array($normalized, ['reject', 'rejected'], true) ? 'rejected' : 'approved';

        if ($action === 'rejected' && (trim((string) $rejectionReason) === '')) {
            throw new \InvalidArgumentException('A rejection reason is required.');
        }

        if (! self::isAwaitingSenior($leave) && ! ($leave->isManagerApplicant() && strtolower(trim((string) $leave->status)) === 'pending')) {
            throw new \RuntimeException('This request is not awaiting senior manager decision.');
        }

        // Allow migrating legacy manager pending → treat as senior queue
        if (strtolower(trim((string) $leave->status)) === 'pending' && $leave->isManagerApplicant()) {
            $leave->status = 'pending_senior';
        }

        self::ensureHandledColumnsExist();
        $leave->status = $action === 'approved' ? 'approved' : 'rejected';
        if ($action === 'rejected') {
            $leave->rejection_reason = trim((string) $rejectionReason);
        } else {
            // Clear any previous rejection reason if approving
            $leave->rejection_reason = null;
        }
        $leave->handled_by = $decider->id;
        $leave->handled_at = Carbon::now();
        $leave->handled_action = $action === 'approved' ? 'approved' : 'rejected';
        $leave->save();

        $managerIdForChat = $leave->isManagerApplicant()
            ? (int) $leave->user_id
            : (int) ($leave->recommended_by ?: ($leave->user?->manager_id ?: $decider->id));

        $conversation = LeaveRequestConversation::firstOrCreate(
            ['leave_request_id' => $leave->id, 'manager_id' => $managerIdForChat],
            ['admin_id' => $decider->id]
        );
        if (! $conversation->admin_id) {
            $conversation->admin_id = $decider->id;
            $conversation->save();
        }

        $body = $action === 'approved'
            ? 'Senior Manager decision: Approved.'
            : 'Senior Manager decision: Rejected. Reason: '.$leave->rejection_reason;
        LeaveRequestMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $decider->id,
            'body' => $body,
        ]);
        $conversation->touch();

        self::notifyFinalDecision($leave->fresh(['user']), $decider);

        return $action;
    }

    public static function notifyFinalDecision(LeaveRequest $leave, User $decider): void
    {
        $applicant = $leave->user;
        if ($applicant) {
            try {
                $applicant->notify(new LeaveStatusNotification($leave, $decider));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Notify the recommending / assigned manager (not the applicant).
        if ($applicant && ! $leave->isManagerApplicant()) {
            $manager = null;
            if ($leave->recommended_by) {
                $manager = User::query()->find($leave->recommended_by);
            }
            if (! $manager && $applicant->manager_id) {
                $manager = User::query()->managers()->whereKey($applicant->manager_id)->first();
            }
            if ($manager && (int) $manager->id !== (int) $applicant->id) {
                try {
                    $manager->notify(new LeaveStatusNotification($leave, $decider));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        if ($applicant && strtolower(trim((string) $leave->status)) === 'approved') {
            $opens = LeaveProofRules::proofWindowOpensAt($leave);
            $windowAlreadyOpen = $opens && ! $opens->copy()->startOfDay()->gt(Carbon::today()->startOfDay());
            ProofUploadReminderService::notifyIfNeeded($leave, 'approved', null, $windowAlreadyOpen);
        }
    }
}
