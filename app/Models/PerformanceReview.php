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
        'scores',
        'status',
        'submitted_at',
        'reviewed_at',
        'senior_comment',
    ];

    protected $casts = [
        'review_date' => 'date',
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
        $value = $this->scores[$criterion] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    public function averageScore(): ?float
    {
        $scores = collect($this->scores ?? [])
            ->filter(fn ($value) => is_numeric($value) && (int) $value >= 1)
            ->map(fn ($value) => (int) $value);

        if ($scores->isEmpty()) {
            return null;
        }

        return round($scores->avg(), 1);
    }

    public function statusLabel(?string $role = null): string
    {
        return PerformanceReviews::statusLabel((string) $this->status, $role);
    }
}
