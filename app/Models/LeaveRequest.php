<?php

namespace App\Models;

use App\Support\LeaveDaysFormatter;
use App\Support\LeaveWorkingDays;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type', 'start_date', 'end_date', 'is_half_day', 'status', 'file_uploads', 'important_comment',
        'original_leave_type', 'converted_to_unpaid_at', 'converted_to_unpaid_reason',
        'manager_recommendation', 'manager_recommendation_comment', 'recommended_by', 'recommended_at',
        'pending_review_reminder_stage', 'last_pending_review_reminder_at',
        'overdue_notified_at', 'approval_comment',
    ];

    protected $casts = [
        'is_half_day' => 'boolean',
        'recommended_at' => 'datetime',
        'last_pending_review_reminder_at' => 'datetime',
        'converted_to_unpaid_at' => 'datetime',
        'overdue_notified_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function conversations()
    {
        return $this->hasMany(LeaveRequestConversation::class, 'leave_request_id');
    }

    public function overdueEscalatedBy()
    {
        return $this->belongsTo(User::class, 'overdue_escalated_by');
    }

    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /** Leave requests submitted by employees only. */
    public function scopeByEmployeeApplicants($query)
    {
        return $query->whereIn(
            'user_id',
            User::query()->employees()->select('id')
        );
    }

    /** Leave requests submitted by managers (approved by admin). */
    public function scopeByManagerApplicants($query)
    {
        return $query->whereIn(
            'user_id',
            User::query()->where('role', 'manager')->select('id')
        );
    }

    /** Leave requests submitted by staff who participate in the approval workflow. */
    public function scopeByStaffApplicants($query)
    {
        return $query->whereIn(
            'user_id',
            User::query()->whereIn('role', ['employee', 'manager'])->select('id')
        );
    }

    public function isManagerApplicant(): bool
    {
        return strtolower((string) ($this->user?->role ?? '')) === 'manager';
    }

    public function recommendedBy()
    {
        return $this->belongsTo(User::class, 'recommended_by');
    }

    public function isOverdue(): bool
    {
        $status = strtolower(trim((string) ($this->status ?? '')));
        if (! in_array($status, ['pending', 'pending_senior'], true)) {
            return false;
        }

        if (! $this->start_date) {
            return false;
        }

        return Carbon::parse($this->start_date)->startOfDay()->lt(Carbon::today());
    }

    /** Still awaiting a decision, and the leave start date has already passed. */
    public function scopeOverdue($query)
    {
        return $query->whereRaw('LOWER(TRIM(status)) in (?, ?)', ['pending', 'pending_senior'])
            ->whereDate('start_date', '<', Carbon::today());
    }

    /** Pending overdue requests still awaiting a manager late-response reason. */
    public function scopeNeedsOverdueEscalationReason($query)
    {
        return $query->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today())
            ->whereNull('overdue_escalated_at');
    }

    public function needsOverdueEscalationReason(): bool
    {
        $status = strtolower(trim((string) ($this->status ?? '')));

        return $status === 'pending' && $this->isOverdue() && empty($this->overdue_escalated_at);
    }

    public function canBeEditedByApplicant(): bool
    {
        return \App\Support\LeaveStatusLabels::isStillPending($this->status);
    }

    public function canBeWithdrawnByEmployee(): bool
    {
        $status = strtolower(trim((string) ($this->status ?? '')));

        if (! in_array($status, ['pending', 'pending_senior', 'approved'], true)) {
            return false;
        }

        if (! $this->end_date) {
            return false;
        }

        return Carbon::parse($this->end_date)->startOfDay()->gte(Carbon::today());
    }

    public function canBeDeletedByEmployee(): bool
    {
        $currentYear = \App\Support\LeaveYear::current();
        $requestYear = $this->start_date
            ? \App\Support\LeaveYear::forDate($this->start_date)
            : $currentYear;

        if ($this->isOverdue()) {
            return $requestYear < $currentYear;
        }

        return $requestYear === ($currentYear - 1);
    }

    /** Whether this manager may open the private admin↔manager chat for this request. */
    public function managerCanOpenChat(User $manager): bool
    {
        $managerId = (int) $manager->id;
        $status = strtolower(trim((string) ($this->status ?? '')));

        if ($status === 'withdrawn') {
            return false;
        }

        if ($this->conversations()->where('manager_id', $managerId)->exists()) {
            return true;
        }

        if ($this->conversations()->where('manager_id', '!=', $managerId)->exists()) {
            return false;
        }

        if ((int) ($this->handled_by ?? 0) === $managerId) {
            return true;
        }

        if ((int) ($this->overdue_escalated_by ?? 0) === $managerId) {
            return true;
        }

        if (!$this->conversations()->exists() && $this->handled_by === null) {
            return true;
        }

        return false;
    }

    /** Dates and short labels for the request-details popup timeline. */
    public function popupMeta(): array
    {
        $stamp = static function ($value): string {
            if (! $value) {
                return '';
            }

            return Carbon::parse($value)->format('j M, H:i');
        };

        $status = strtolower(trim((string) ($this->status ?? '')));
        $outcomeAt = $this->handled_at;
        if (! $outcomeAt && in_array($status, ['withdrawn', 'approved', 'rejected'], true)) {
            $outcomeAt = $this->updated_at;
        }

        return [
            'submittedAt' => $stamp($this->created_at),
            'reviewedAt' => $stamp($this->recommended_at),
            'outcomeAt' => $stamp($outcomeAt),
            'startShort' => $this->start_date ? Carbon::parse($this->start_date)->format('j M') : '',
            'endShort' => $this->end_date ? Carbon::parse($this->end_date)->format('j M') : '',
            'days' => LeaveDaysFormatter::format(
                ($this->days !== null && $this->days !== '')
                    ? $this->days
                    : (($this->start_date && $this->end_date) ? LeaveWorkingDays::count($this) : '')
            ),
            'isHalfDay' => (bool) $this->is_half_day,
        ];
    }
}
