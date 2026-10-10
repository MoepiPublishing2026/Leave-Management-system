<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class LeaveRequestPeriodFilter
{
    /**
     * Filter requests by submission period so status tabs match dashboard counts.
     * "This year" and numbered years use the company leave year (1 May – 30 April).
     */
    public static function apply(Builder $query, mixed $monthFilter, string $column = 'created_at'): void
    {
        if (! is_string($monthFilter) || $monthFilter === '') {
            return;
        }

        $column = self::qualifyColumn($query, $column);
        $now = Carbon::now();

        if (ctype_digit($monthFilter)) {
            LeaveYear::constrain($query, (int) $monthFilter, $column);

            return;
        }

        if ($monthFilter === 'this_month') {
            $query->whereYear($column, $now->year)
                ->whereMonth($column, $now->month);

            return;
        }

        if ($monthFilter === 'this_quarter') {
            $start = $now->copy()->startOfQuarter();
            $end = $now->copy()->endOfQuarter();
            $query->whereBetween($column, [$start->startOfDay(), $end->endOfDay()]);

            return;
        }

        if ($monthFilter === 'this_year') {
            LeaveYear::constrain($query, LeaveYear::current($now), $column);
        }
    }

    private static function qualifyColumn(Builder $query, string $column): string
    {
        if (str_contains($column, '.')) {
            return $column;
        }

        return $query->getModel()->getTable().'.'.$column;
    }
}
