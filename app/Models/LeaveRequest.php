<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'leave_type', 'start_date', 'end_date', 'is_half_day', 'status', 'file_uploads', 'important_comment'
    ];

    protected $casts = [
        'is_half_day' => 'boolean',
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

    /** Leave requests submitted by employees only (managers/admins do not apply). */
    public function scopeByEmployeeApplicants($query)
    {
        return $query->whereIn(
            'user_id',
            User::query()->employees()->select('id')
        );
    }

    public function isOverdue(): bool
    {
        $status = strtolower(trim((string) ($this->status ?? '')));
        if ($status !== 'pending') {
            return false;
        }

        if (!$this->start_date) {
            return false;
        }

        return Carbon::parse($this->start_date)->lt(Carbon::today());
    }
}
