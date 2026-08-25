<?php

namespace App\Support;

use App\Mail\LeaveConvertedToUnpaidMail;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveConvertedToUnpaidNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MissingProofUnpaidConverter
{
    public const REASON = 'Required supporting documents were not submitted by the agreed deadline.';

    public static function candidates(): Builder
    {
        return LeaveRequest::query()
            ->whereRaw('LOWER(TRIM(status)) = ?', ['approved'])
            ->whereRaw('LOWER(TRIM(leave_type)) = ?', ['sick'])
            ->whereNull('converted_to_unpaid_at');
    }

    public static function shouldConvert(LeaveRequest $leaveRequest, ?Carbon $now = null): bool
    {
        $now ??= Carbon::now();
        $deadline = LeaveProofRules::proofDeadlineAt($leaveRequest);

        return strtolower(trim((string) $leaveRequest->status)) === 'approved'
            && strtolower(trim((string) $leaveRequest->leave_type)) !== 'unpaid'
            && $leaveRequest->converted_to_unpaid_at === null
            && LeaveProofRules::allowsDeferredProof($leaveRequest->leave_type)
            && LeaveProofRules::requiresProof($leaveRequest->leave_type)
            && ! LeaveProofRules::hasProofAttached($leaveRequest)
            && $deadline !== null
            && $now->gt($deadline);
    }

    public static function convert(LeaveRequest $leaveRequest, ?Carbon $now = null): bool
    {
        $now ??= Carbon::now();

        $converted = DB::transaction(function () use ($leaveRequest, $now): ?LeaveRequest {
            $locked = LeaveRequest::query()->lockForUpdate()->find($leaveRequest->id);
            if (! $locked || ! self::shouldConvert($locked, $now)) {
                return null;
            }

            $locked->original_leave_type = $locked->original_leave_type ?: $locked->leave_type;
            $locked->leave_type = 'Unpaid';
            $locked->converted_to_unpaid_at = $now;
            $locked->converted_to_unpaid_reason = self::REASON;
            $locked->save();

            return $locked->fresh(['user']);
        });

        if (! $converted) {
            return false;
        }

        self::notifyRecipients($converted);

        return true;
    }

    public static function convertDue(): int
    {
        $converted = 0;

        self::candidates()
            ->with('user')
            ->orderBy('id')
            ->chunkById(100, function ($requests) use (&$converted): void {
                foreach ($requests as $leaveRequest) {
                    try {
                        if (self::convert($leaveRequest)) {
                            $converted++;
                        }
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            });

        return $converted;
    }

    private static function notifyRecipients(LeaveRequest $leaveRequest): void
    {
        $applicant = $leaveRequest->user;
        $recipients = collect();

        if ($applicant) {
            $recipients->push($applicant);

            if (strtolower((string) $applicant->role) === 'employee') {
                $managerId = $leaveRequest->recommended_by ?: $applicant->manager_id;
                if ($managerId) {
                    $manager = User::query()->whereKey($managerId)->where('is_active', true)->first();
                    if ($manager) {
                        $recipients->push($manager);
                    }
                }
            }
        }

        $recipients = $recipients
            ->merge(\App\Support\LeaveApprovalWorkflow::seniorsForLeave($leaveRequest))
            ->unique('id')
            ->values();

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(new LeaveConvertedToUnpaidNotification($leaveRequest));
            } catch (\Throwable $e) {
                report($e);
            }

            if (! empty($recipient->email)) {
                try {
                    Mail::to($recipient->email)->send(new LeaveConvertedToUnpaidMail(
                        $leaveRequest,
                        \App\Support\EmailGreeting::displayName($recipient),
                        LeavePortalLinks::reviewUrlFor($recipient, $leaveRequest->id)
                    ));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
    }
}
