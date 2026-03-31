<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequestConversation extends Model
{
    protected $fillable = [
        'leave_request_id',
        'manager_id',
        'admin_id',
    ];

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function messages()
    {
        return $this->hasMany(LeaveRequestMessage::class, 'conversation_id');
    }
}
