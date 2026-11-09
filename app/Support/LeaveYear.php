<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class LeaveYear
{
    public const START_MONTH = 5;
    public const START_DAY = 1;
    public const END_MONTH = 4;
    public const END_DAY = 30;

    public static function current(?Carbon $now = null): int
    {
        return self::forDate($now ?? Carbon::now());
    }

    public static function forDate(Carbon|string $date): int
    {
        $d = $date instanceof Carbon ? $date : Carbon::parse($date);

        return ((int) $d->month) < self::START_MONTH
            ? (int) $d->year - 1
            : (int) $d->year;
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public static function window(int $leaveYear): array
    {
        $start = Carbon::create($leaveYear, self::START_MONTH, self::START_DAY)->startOfDay();
        $end = Carbon::create($leaveYear + 1, self::END_MONTH, self::END_DAY)->endOfDay();

        return [$start, $end];
    }

    public static function label(int $leaveYear): string
    {
        return $leaveYear.'/'.($leaveYear + 1);
    }

    public static function rangeLabel(int $leaveYear): string
    {
        return '1 May '.$leaveYear.' – 30 April '.($leaveYear + 1);
    }

    public static function contains(Carbon|string $date, int $leaveYear): bool
    {
        [$start, $end] = self::window($leaveYear);
        $d = $date instanceof Carbon ? $date->copy()->startOfDay() : Carbon::parse($date)->startOfDay();

        return $d->betweenIncluded($start->startOfDay(), $end->copy()->startOfDay());
    }

    public static function constrain(Builder $query, int $leaveYear, string $column): void
    {
        [$start, $end] = self::window($leaveYear);
        $query->where($column, '>=', $start)->where($column, '<=', $end);
    }

    /** @return list<string> */
    public static function barLabels(): array
    {
        return ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr'];
    }

    public static function monthIndex(Carbon|string $date): int
    {
        $month = (int) ($date instanceof Carbon ? $date->month : Carbon::parse($date)->month);

        return self::monthIndexFromNumber($month);
    }

    public static function monthIndexFromNumber(int $month): int
    {
        return ($month - self::START_MONTH + 12) % 12;
    }

    /** @return list<int> */
    public static function options(?int $current = null, int $past = 4, int $future = 1): array
    {
        $current ??= self::current();

        return range($current - $past, $current + $future);
    }
}