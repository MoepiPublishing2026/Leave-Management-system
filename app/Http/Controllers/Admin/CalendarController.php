<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yasumi\Yasumi;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->query('month', Carbon::now()->month);
        $year = (int) $request->query('year', Carbon::now()->year);

        $firstDay = Carbon::create($year, $month, 1);
        $startDay = $firstDay->dayOfWeek; // 0=Sun, 6=Sat
        $daysInMonth = $firstDay->daysInMonth;
        $startOfMonth = $firstDay->copy()->startOfMonth();
        $endOfMonth = $firstDay->copy()->endOfMonth();

        // Generate SA public holidays for all years spanned by month view
        $publicHolidays = [];
        for ($y = $startOfMonth->year; $y <= $endOfMonth->year; $y++) {
            $holidays = Yasumi::create('SouthAfrica', $y);
            foreach ($holidays as $holiday) {
                $publicHolidays[] = $holiday->format('Y-m-d');
            }
        }

        $leaves = LeaveRequest::with('user')
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($sub) use ($startOfMonth, $endOfMonth) {
                        $sub->where('start_date', '<=', $startOfMonth)
                            ->where('end_date', '>=', $endOfMonth);
                    });
            })
            ->orderBy('start_date')
            ->get();

        $events = [];

        foreach ($leaves as $leave) {
            $period = Carbon::parse($leave->start_date)->daysUntil(Carbon::parse($leave->end_date)->addDay());
            foreach ($period as $day) {
                // Exclude weekends and public holidays
                if (
                    $day->between($startOfMonth, $endOfMonth) &&
                    !$day->isWeekend() &&
                    !in_array($day->toDateString(), $publicHolidays)
                ) {
                    $dateKey = $day->format('Y-m-d');
                    $events[$dateKey][] = [
                        'user' => $leave->user ? ($leave->user->first_name . ' ' . $leave->user->last_name) : 'Unknown',
                        'type' => ucfirst($leave->leave_type),
                    ];
                }
            }
        }

        return view('admin.calendar.index', compact('month', 'year', 'startDay', 'daysInMonth', 'events', 'publicHolidays'));
    }
}
