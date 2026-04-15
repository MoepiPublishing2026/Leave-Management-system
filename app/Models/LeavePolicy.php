<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeavePolicy extends Model
{
    protected $fillable = [
        'leave_type', 'default_days', 'max_carry_over', 'allow_half_days', 'requires_proof'
    ];
}

