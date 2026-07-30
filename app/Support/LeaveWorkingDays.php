<?php

namespace App\Support;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Yasumi\Yasumi;

class LeaveWorkingDays
{
    public static function count(LeaveRequest $leaveRequest): float
    {
        $start = Carbon::parse($leaveRequest->start_date);
        $end = Carbon::parse($leaveRequest->end_date);

        $publicHolidays = [];
        for ($year = $start->year; $year <= $end->year; $year++) {
            $holidays = Yasumi::create('SouthAfrica', $year);
            foreach ($holidays as $holiday) {
                $publicHolidays[] = $holiday->format('Y-m-d');
            }
        }

        if (! empty($leaveRequest->is_half_day)) {
            if (! $start->isWeekend() && ! in_array($start->toDateString(), $publicHolidays, true)) {
                return 0.5;
            }

            return 0.0;
        }

        $workingDays = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            if (! $current->isWeekend() && ! in_array($current->toDateString(), $publicHolidays, true)) {
                $workingDays++;
            }
            $current->addDay();
        }

        return (float) $workingDays;
    }
}
