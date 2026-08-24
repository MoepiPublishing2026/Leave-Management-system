<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\LeaveRequestConversation;
use App\Models\LeaveRequestMessage;
use App\Models\User;
use App\Notifications\LeaveStatusNotification;
use App\Notifications\NewLeaveRequestNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
    }

    /**
     * Only the senior manager assigned to this applicant's manager.
     * Never falls back to every admin — unassigned reporting lines stay unassigned.
     */
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

        return $seniors->unique('id')->values();
    }

    public static function assignedSeniorForUser(?User $user): ?User
    {
        if (! $user) {
            return null;
        }

        $role = strtolower(trim((string) ($user->role ?? '')));
        $seniorId = 0;

        if ($role === 'manager') {
            $seniorId = (int) ($user->manager_id ?? 0);
        } elseif ($role === 'employee') {
            $manager = $user->manager_id
                ? User::query()->managers()->whereKey($user->manager_id)->first()
                : null;
            $seniorId = (int) ($manager?->manager_id ?? 0);
        }

        if ($seniorId < 1) {
            return null;
        }

        return User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->whereKey($seniorId)
            ->first();
    }

    /** Manager IDs assigned to this senior, plus those managers' employees. */
    public static function reportingLineUserIds(?User $senior): Collection
    {
        if (! $senior || strtolower((string) ($senior->role ?? '')) !== 'admin') {
            return collect();
        }

        $managerIds = User::query()
            ->managers()
            ->where('manager_id', $senior->id)
            ->pluck('id');

        $employeeIds = $managerIds->isEmpty()
            ? collect()
            : User::query()->employees()->whereIn('manager_id', $managerIds)->pluck('id');

        return $managerIds->merge($employeeIds)->unique()->values();
    }

    public static function seniorCanAccessLeave(?User $senior, LeaveRequest $leave): bool
    {
        if (! $senior || strtolower((string) ($senior->role ?? '')) !== 'admin') {
            return false;
        }

        $lineIds = self::reportingLineUserIds($senior);

        return $lineIds->contains((int) $leave->user_id);
    }

    public static function canDecide(User $user, LeaveRequest $leave): bool
    {
        if (strtolower((string) ($user->role ?? '')) !== 'admin' || ! $user->is_active) {
            return false;
        }

        return self::seniorsForLeave($leave)->contains(
            fn (User $assigned) => (int) $assigned->id === (int) $user->id
        );
    }

    /**
     * Who to record as the decision-maker for an email or in-app action.
     * Never falls back to "first admin in the database".
     */
    public static function resolveActingSenior(LeaveRequest $leave): User
    {
        $assigned = self::seniorsForLeave($leave);
        $signedId = (int) request()->query('senior', request()->input('senior', 0));

        if ($signedId > 0) {
            $fromLink = $assigned->firstWhere('id', $signedId);
            if (! $fromLink) {
                abort(403, 'You are not the senior manager assigned to this employee\'s manager.');
            }

            return $fromLink;
        }

        if (auth()->check() && strtolower((string) (auth()->user()->role ?? '')) === 'admin') {
            $loggedIn = $assigned->firstWhere('id', auth()->id());
            if ($loggedIn) {
                return $loggedIn;
            }
        }

        $assignedSenior = $assigned->first();
        if ($assignedSenior) {
            return $assignedSenior;
        }

        abort(403, 'No senior manager is assigned to this employee\'s manager, so this request cannot be decided yet.');
    }

    /** @return array<string, int> */
    public static function emailDecisionRouteParams(LeaveRequest $leave, ?User $senior = null): array
    {
        $assigned = self::seniorsForLeave($leave);
        if (! $senior) {
            $signedId = (int) request()->query('senior', request()->input('senior', 0));
            $senior = $signedId > 0 ? $assigned->firstWhere('id', $signedId) : null;
            $senior = $senior ?: $assigned->first();
        }
        $params = ['leaveRequest' => $leave->id];
        if ($senior) {
            $params['senior'] = $senior->id;
        }

        return $params;
    }

    public static function scopeLeaveRequestsForSenior($query, User $senior)
    {
        $ids = self::reportingLineUserIds($senior);
        if ($ids->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('user_id', $ids);
    }

    /**
     * @return array{applicant_name: string, manager_name: string, is_manager_applicant: bool}
     */
    public static function reportingContext(LeaveRequest $leave): array
    {
        $leave->loadMissing(['user.manager']);
        $applicant = $leave->user;
        $isManagerApplicant = $applicant && strtolower(trim((string) ($applicant->role ?? ''))) === 'manager';
        $applicantName = EmailGreeting::displayName($applicant, 'a staff member');

        if ($isManagerApplicant) {
            return [
                'applicant_name' => $applicantName,
                'manager_name' => $applicantName,
                'is_manager_applicant' => true,
            ];
        }

        $manager = $applicant?->manager;
        if (! $manager && $applicant?->manager_id) {
            $manager = User::query()->managers()->whereKey($applicant->manager_id)->first();
        }

        return [
            'applicant_name' => $applicantName,
            'manager_name' => EmailGreeting::displayName($manager),
            'is_manager_applicant' => false,
        ];
    }

    public static function notifyAssignedSeniorAboutTeamLeave(LeaveRequest $leave): void
    {
        $leave->loadMissing('user');
        if (self::isAwaitingSenior($leave) || $leave->isManagerApplicant()) {
            return;
        }

        $senior = self::assignedSeniorForUser($leave->user);
        if (! $senior) {
            return;
        }

        try {
            $senior->notifyNow(new NewLeaveRequestNotification($leave), ['database']);
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $senior->notifyNow(new NewLeaveRequestNotification($leave), ['mail']);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public static function notifySeniorsForDecision(LeaveRequest $leave): void
    {
        $admins = self::seniorsForLeave($leave);
        if ($admins->isEmpty()) {
            \Illuminate\Support\Facades\Log::warning('No senior managers to notify for leave decision email.', [
                'leave_request_id' => $leave->id,
            ]);

            return;
        }

        // Database first so the in-app bell still updates if email sending fails.
        foreach ($admins as $admin) {
            try {
                $admin->notifyNow(new NewLeaveRequestNotification($leave), ['database']);
            } catch (\Throwable $e) {
                report($e);
                \Illuminate\Support\Facades\Log::error('Failed saving senior in-app leave notification.', [
                    'leave_request_id' => $leave->id,
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                $admin->notifyNow(new NewLeaveRequestNotification($leave), ['mail']);
            } catch (\Throwable $e) {
                report($e);
                \Illuminate\Support\Facades\Log::error('Failed emailing senior of leave awaiting decision.', [
                    'leave_request_id' => $leave->id,
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage(),
                ]);
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

        if (! self::canDecide($decider, $leave)) {
            throw new \RuntimeException('Only the senior manager assigned to this employee\'s manager can approve or reject this request.');
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
