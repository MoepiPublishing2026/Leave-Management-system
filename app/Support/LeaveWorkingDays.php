<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Services\PublicHolidayService;
use Carbon\Carbon;

class LeaveWorkingDays
{
    public static function count(LeaveRequest $leaveRequest): float
    {
        $start = Carbon::parse($leaveRequest->start_date);
        $end = Carbon::parse($leaveRequest->end_date);

        $publicHolidays = PublicHolidayService::datesForYearRange($start->year, $end->year);

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
