<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class LeavePayFilter
{
    public static function normalize(mixed $value): string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, ['paid', 'unpaid'], true) ? $value : 'all';
    }

    public static function apply(Builder $query, string $payFilter): void
    {
        if ($payFilter === 'unpaid') {
            $query->whereRaw('LOWER(TRIM(leave_type)) = ?', ['unpaid']);

            return;
        }

        if ($payFilter === 'paid') {
            $query->whereRaw('LOWER(TRIM(leave_type)) != ?', ['unpaid']);
        }
    }
}
