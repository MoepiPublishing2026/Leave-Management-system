<?php

namespace App\Support;

use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use Illuminate\Support\Carbon;

class LeaveProofRules
{
    public static function allowsDeferredProof(?string $leaveType): bool
    {
        return strtolower(trim((string) $leaveType)) === 'sick';
    }

    public static function policyForType(?string $leaveType): ?LeavePolicy
    {
        if (! $leaveType) {
            return null;
        }

        return LeavePolicy::where('leave_type', $leaveType)->first();
    }

    public static function requiresProof(?string $leaveType): bool
    {
        $policy = self::policyForType($leaveType);

        return $policy && (bool) $policy->requires_proof;
    }

    public static function requiresProofAtSubmit(?string $leaveType, $startDate = null, $endDate = null): bool
    {
        if (! self::requiresProof($leaveType)) {
            return false;
        }

        // Non-sick types that require proof must attach it when applying.
        if (! self::allowsDeferredProof($leaveType)) {
            return true;
        }

        // Sick leave may defer the letter for current/future leave, but backdated
        // sick leave already happened — the doctor's letter must be uploaded now.
        if ($startDate && $endDate) {
            try {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->startOfDay();
            } catch (\Throwable $e) {
                return false;
            }

            return LeaveWorkdayRules::isRetroactiveLeave($start, $end);
        }

        return false;
    }

    public static function backdatedSickProofMessage(): string
    {
        return 'A doctor\'s letter is required for backdated sick leave. Attach it when you apply.';
    }

    public static function hasProofAttached(LeaveRequest $request): bool
    {
        if (empty($request->file_uploads)) {
            return false;
        }

        $decoded = json_decode($request->file_uploads, true);
        if (is_array($decoded)) {
            return count(array_filter($decoded)) > 0;
        }

        return trim((string) $request->file_uploads) !== '';
    }

    public static function proofWindowOpensAt(LeaveRequest $request): ?Carbon
    {
        if (! $request->start_date) {
            return null;
        }

        return Carbon::parse($request->start_date)->startOfDay();
    }

    public static function proofDeadlineAt(LeaveRequest $request): ?Carbon
    {
        // Deadline is the day after leave ends (not after it starts), so multi-day sick leave is fair.
        $anchor = $request->end_date ?: $request->start_date;
        if (! $anchor) {
            return null;
        }

        return Carbon::parse($anchor)->addDay()->endOfDay();
    }

    /**
     * Plain-language upload window, e.g. "from 02 Jul 2026, by 04 Jul 2026 at the latest".
     */
    public static function proofUploadWindowDescription(LeaveRequest $request): ?string
    {
        $opens = self::proofWindowOpensAt($request);
        $deadline = self::proofDeadlineAt($request);
        if (! $opens || ! $deadline) {
            return null;
        }

        return 'from '.$opens->format('d M Y').', by '.$deadline->format('d M Y').' at the latest';
    }

    public static function proofUploadWindowSentence(LeaveRequest $request, string $prefix = 'You can upload'): ?string
    {
        $description = self::proofUploadWindowDescription($request);

        return $description ? $prefix.' '.$description.'.' : null;
    }

    private static function isActiveDeferredSickRequest(LeaveRequest $request): bool
    {
        if (! self::allowsDeferredProof($request->leave_type) || ! self::requiresProof($request->leave_type)) {
            return false;
        }

        $status = strtolower(trim((string) ($request->status ?? '')));

        return in_array($status, ['pending', 'approved'], true);
    }

    public static function isProofUploadWindowOpen(LeaveRequest $request): bool
    {
        if (! self::allowsDeferredProof($request->leave_type) || ! self::requiresProof($request->leave_type)) {
            return false;
        }

        $opens = self::proofWindowOpensAt($request);
        $deadline = self::proofDeadlineAt($request);
        if (! $opens || ! $deadline) {
            return false;
        }

        $now = Carbon::now();

        return $now->gte($opens) && $now->lte($deadline);
    }

    public static function isProofUploadWindowPending(LeaveRequest $request): bool
    {
        if (! self::isActiveDeferredSickRequest($request)) {
            return false;
        }

        $opens = self::proofWindowOpensAt($request);

        return $opens && Carbon::now()->lt($opens);
    }

    public static function isProofPastDeadline(LeaveRequest $request): bool
    {
        if (! self::requiresDoctorsLetter($request)) {
            return false;
        }

        $deadline = self::proofDeadlineAt($request);

        return $deadline && Carbon::now()->gt($deadline);
    }

    public static function isApprovedSickLeave(LeaveRequest $request): bool
    {
        if (! self::allowsDeferredProof($request->leave_type) || ! self::requiresProof($request->leave_type)) {
            return false;
        }

        return strtolower(trim((string) ($request->status ?? ''))) === 'approved';
    }

    public static function requiresDoctorsLetter(LeaveRequest $request): bool
    {
        return self::isApprovedSickLeave($request) && ! self::hasProofAttached($request);
    }

    public static function employeeCanManageAttachments(LeaveRequest $request): bool
    {
        $status = strtolower(trim((string) ($request->status ?? '')));

        if (in_array($status, ['pending', 'pending_senior'], true)) {
            return true;
        }

        if (self::isApprovedSickLeave($request)) {
            return ! self::isProofPastDeadline($request);
        }

        return false;
    }

    public static function employeeCanSubmitAttachmentNow(LeaveRequest $request): bool
    {
        if (! self::employeeCanManageAttachments($request)) {
            return false;
        }

        $status = strtolower(trim((string) ($request->status ?? '')));

        if (in_array($status, ['pending', 'pending_senior'], true)) {
            return true;
        }

        return self::isApprovedSickLeave($request) && self::isProofUploadWindowOpen($request);
    }

    public static function employeeAttachmentActionLabel(LeaveRequest $request): string
    {
        if (self::allowsDeferredProof($request->leave_type)) {
            return self::hasProofAttached($request) ? 'Replace doctor\'s letter' : 'Upload doctor\'s letter';
        }

        return self::hasProofAttached($request) ? 'Replace attachment' : 'Upload attachment';
    }

    public static function canUploadProof(LeaveRequest $request): bool
    {
        return self::requiresDoctorsLetter($request) && self::isProofUploadWindowOpen($request);
    }

    public static function shouldShowCertificateUpload(LeaveRequest $request): bool
    {
        if (! self::requiresDoctorsLetter($request)) {
            return false;
        }

        return ! self::isProofPastDeadline($request);
    }

    public static function doctorsLetterStatusLabel(LeaveRequest $request): ?string
    {
        if (! self::requiresDoctorsLetter($request)) {
            return null;
        }

        if (self::isProofPastDeadline($request)) {
            return 'Doctor\'s letter missing';
        }

        if (self::isProofUploadWindowPending($request)) {
            $opens = self::proofWindowOpensAt($request);

            return 'Doctor\'s letter required'.($opens ? ' (from '.$opens->format('d M Y').')' : '');
        }

        return 'Doctor\'s letter required';
    }

    public static function canApprove(LeaveRequest $request): bool
    {
        if (! self::requiresProof($request->leave_type)) {
            return true;
        }

        if (self::allowsDeferredProof($request->leave_type)) {
            return true;
        }

        return self::hasProofAttached($request);
    }

    public static function approvalBlockedReason(LeaveRequest $request): ?string
    {
        if (! self::requiresProof($request->leave_type) || self::allowsDeferredProof($request->leave_type)) {
            return null;
        }

        if (self::hasProofAttached($request)) {
            return null;
        }

        return 'Supporting documentation is required for this leave type.';
    }

    public static function managerCertificateNote(LeaveRequest $request): ?string
    {
        if (! self::isApprovedSickLeave($request) || self::hasProofAttached($request)) {
            if (self::isActiveDeferredSickRequest($request) && ! self::isApprovedSickLeave($request) && ! self::hasProofAttached($request)) {
                $opens = self::proofWindowOpensAt($request);
                $deadline = self::proofDeadlineAt($request);
                if ($opens && $deadline) {
                    return 'After approval, the employee must upload a doctor\'s letter '
                        .self::proofUploadWindowDescription($request)
                        .' to confirm the hospital or doctor visit.';
                }
            }

            return null;
        }

        if (self::isProofPastDeadline($request)) {
            $deadline = self::proofDeadlineAt($request);

            return 'Approved sick leave is missing the required doctor\'s letter. Deadline was '.($deadline ? $deadline->format('d M Y') : 'not set').'.';
        }

        $opens = self::proofWindowOpensAt($request);
        $deadline = self::proofDeadlineAt($request);
        if (! $opens || ! $deadline) {
            return 'Approved sick leave requires a doctor\'s letter to confirm the hospital or doctor visit.';
        }

        return 'Approved sick leave requires a doctor\'s letter '
            .self::proofUploadWindowDescription($request)
            .' to confirm the hospital or doctor visit.';
    }

    public static function employeeProofStatusLabel(LeaveRequest $request): ?string
    {
        return self::doctorsLetterStatusLabel($request);
    }
}
