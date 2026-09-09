<?php

namespace App\Support;

use App\Models\PerformanceReview;
use App\Models\User;
use App\Notifications\PerformanceReviewAcknowledgedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

class PerformanceReviews
{
    /** @return array<string, string> */
    public static function criteria(): array
    {
        return [
            'team_work' => 'Team work',
            'attendance' => 'Attendance',
            'creativity' => 'Creativity',
            'problem_solving' => 'Problem solving',
            'leadership_skills' => 'Leadership skills',
            'time_management' => 'Time management',
            'reliability' => 'Reliability',
            'organizational_skills' => 'Organizational skills',
            'willingness_to_learn' => 'Willingness to learn',
            'consistency' => 'Consistency',
            'productivity' => 'Productivity',
            'adaptability' => 'Adaptability',
        ];
    }

    /** @return array<int, string> */
    public static function scoreLabels(): array
    {
        return [
            1 => 'Unsatisfactory',
            2 => 'Below expectation',
            3 => 'Satisfactory',
            4 => 'Above expectation',
            5 => 'Exceptional',
        ];
    }

    public static function layout(?User $user): string
    {
        return match (strtolower((string) ($user->role ?? 'employee'))) {
            'admin' => 'layouts.admin',
            'manager' => 'layouts.manager',
            default => 'layouts.app',
        };
    }

    public static function routeName(?User $user, string $action): string
    {
        $prefix = match (strtolower((string) ($user->role ?? 'employee'))) {
            'admin' => 'admin.reviews.',
            'manager' => 'manager.reviews.',
            default => 'reviews.',
        };

        return $prefix.$action;
    }

    public static function displayName(?User $user): string
    {
        if (! $user) {
            return '';
        }

        return trim(($user->first_name ?? '').' '.($user->last_name ?? ''));
    }

    public static function positionLabel(?User $user): string
    {
        if (! $user) {
            return '';
        }

        return trim((string) ($user->designation ?: $user->department ?: ''));
    }

    /** Assigned senior manager: employee → their manager → that manager's senior. */
    public static function assignedSenior(?User $person): ?User
    {
        if (! $person) {
            return null;
        }

        $role = strtolower(trim((string) ($person->role ?? '')));
        $seniorId = 0;

        if ($role === 'employee') {
            $manager = $person->manager_id
                ? User::query()->where('role', 'manager')->whereKey($person->manager_id)->first()
                : null;
            $seniorId = (int) ($manager?->manager_id ?? 0);
        } else {
            $seniorId = (int) ($person->manager_id ?? 0);
        }

        if ($seniorId > 0) {
            $senior = User::query()
                ->where('role', 'admin')
                ->where(function ($q) {
                    $q->where('is_active', true)->orWhereNull('is_active');
                })
                ->whereKey($seniorId)
                ->first();
            if ($senior) {
                return $senior;
            }
        }

        return null;
    }

    public static function signedReviewUrl(PerformanceReview $review): string
    {
        return URL::temporarySignedRoute(
            'reviews.email.review',
            now()->addDays(7),
            ['review' => $review->id]
        );
    }

    public static function signedSubmitUrl(PerformanceReview $review): string
    {
        return URL::temporarySignedRoute(
            'reviews.email.submit',
            now()->addDays(7),
            ['review' => $review->id]
        );
    }

    public static function complete(PerformanceReview $review, array $scores, ?string $comment = null, ?User $senior = null): void
    {
        $review->scores = $scores;
        $review->status = 'reviewed';
        $review->reviewed_at = now();
        $review->senior_comment = $comment !== null && trim($comment) !== '' ? trim($comment) : null;
        if ($senior) {
            $review->senior_id = $senior->id;
        }
        $review->save();

        $submitter = $review->submitter ?: $review->employee;
        if ($submitter) {
            try {
                $submitter->notify(new PerformanceReviewAcknowledgedNotification($review->fresh()));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public static function seniorsToNotify(?User $submitter): Collection
    {
        $senior = self::assignedSenior($submitter);

        return $senior ? collect([$senior]) : collect();
    }

    public static function statusLabel(string $status, ?string $role = null): string
    {
        $status = strtolower(trim($status));
        $role = strtolower(trim((string) $role));

        return match ($status) {
            'submitted' => $role === 'admin' ? 'Awaiting review' : 'Submitted',
            'reviewed' => 'Reviewed',
            default => 'Draft',
        };
    }

    public static function badgeClass(string $status): string
    {
        return match (strtolower(trim($status))) {
            'submitted' => 'pending',
            'reviewed' => 'approved',
            default => 'withdrawn',
        };
    }
}
