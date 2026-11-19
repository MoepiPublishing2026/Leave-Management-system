<?php

namespace App\Models;

use App\Support\PerformanceReviews;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReview extends Model
{
    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'submitted_by',
        'senior_id',
        'employee_name',
        'employee_position',
        'reviewer_name',
        'review_period',
        'review_date',
        'period_year',
        'period_month',
        'goals_achieved',
        'quality_of_work',
        'initiative_examples',
        'areas_for_improvement',
        'self_scores',
        'scores',
        'status',
        'submitted_at',
        'reviewed_at',
        'senior_comment',
    ];

    protected $casts = [
        'review_date' => 'date',
        'self_scores' => 'array',
        'scores' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'period_year' => 'integer',
        'period_month' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function senior(): BelongsTo
    {
        return $this->belongsTo(User::class, 'senior_id');
    }

    /** Prefer the live user name so profile edits show everywhere. */
    public function liveEmployeeName(): string
    {
        $live = PerformanceReviews::displayName($this->employee);

        return $live !== '' ? $live : (string) ($this->employee_name ?? '');
    }

    public function liveReviewerName(): string
    {
        $live = PerformanceReviews::displayName($this->reviewer);

        return $live !== '' ? $live : (string) ($this->reviewer_name ?? '');
    }

    public function hasRequiredContent(): bool
    {
        foreach ([
            'review_period',
            'review_date',
            'goals_achieved',
            'quality_of_work',
            'initiative_examples',
            'areas_for_improvement',
        ] as $field) {
            if (trim((string) $this->{$field}) === '') {
                return false;
            }
        }

        return (int) $this->period_year > 0
            && (int) $this->period_month >= 1
            && (int) $this->period_month <= 12
            && $this->hasCompleteSelfScores();
    }

    public function hasCompleteSelfScores(): bool
    {
        foreach (array_keys(PerformanceReviews::criteria()) as $key) {
            $value = $this->self_scores[$key] ?? null;
            if (! is_numeric($value) || (int) $value < 1 || (int) $value > 5) {
                return false;
            }
        }

        return true;
    }

    public function isDraft(): bool
    {
        return strtolower((string) $this->status) === 'draft';
    }

    public function isSubmitted(): bool
    {
        return strtolower((string) $this->status) === 'submitted';
    }

    public function isReviewed(): bool
    {
        return strtolower((string) $this->status) === 'reviewed';
    }

    public function periodLabel(): string
    {
        $month = str_pad((string) $this->period_month, 2, '0', STR_PAD_LEFT);

        return $this->period_year.'-'.$month;
    }

    public function scoreFor(string $criterion): ?int
    {
        return $this->numericScore($this->scores[$criterion] ?? null);
    }

    public function selfScoreFor(string $criterion): ?int
    {
        return $this->numericScore($this->self_scores[$criterion] ?? null);
    }

    public function averageScore(): ?float
    {
        return $this->averageFrom($this->scores);
    }

    public function averageSelfScore(): ?float
    {
        return $this->averageFrom($this->self_scores);
    }

    public function statusLabel(?string $role = null): string
    {
        return PerformanceReviews::statusLabel((string) $this->status, $role);
    }

    public function statusLabelFor(?User $viewer = null): string
    {
        return PerformanceReviews::statusLabelFor((string) $this->status, $viewer, $this);
    }

    private function numericScore(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function averageFrom(?array $scores): ?float
    {
        $values = collect($scores ?? [])
            ->filter(fn ($value) => is_numeric($value) && (int) $value >= 1)
            ->map(fn ($value) => (int) $value);

        if ($values->isEmpty()) {
            return null;
        }

        return round($values->avg(), 1);
    }
}
