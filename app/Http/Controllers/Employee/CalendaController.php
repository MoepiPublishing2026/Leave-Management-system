<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yasumi\Yasumi;

class CalendaController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $month = (int) $request->query('month', Carbon::now()->month);
        $year = (int) $request->query('year', Carbon::now()->year);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Generate public holidays for year range
        $publicHolidays = [];
        for ($y = $startOfMonth->year; $y <= $endOfMonth->year; $y++) {
            $holidays = Yasumi::create('SouthAfrica', $y);
            foreach ($holidays as $holiday) {
                $publicHolidays[] = $holiday->format('Y-m-d');
            }
        }

        // Fetch approved leaves of logged-in employee overlapping selected month
        $leaves = LeaveRequest::where('user_id', $userId)
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

        // Build day-wise leave events excluding weekends and holidays
        $events = [];
        foreach ($leaves as $leave) {
            $period = Carbon::parse($leave->start_date)
                ->daysUntil(Carbon::parse($leave->end_date)->addDay());

            foreach ($period as $day) {
                if ($day->between($startOfMonth, $endOfMonth)
                    && !$day->isWeekend()
                    && !in_array($day->toDateString(), $publicHolidays)
                ) {
                    $dateKey = $day->format('Y-m-d');
                    $events[$dateKey][] = [
                        'type' => ucfirst($leave->leave_type),
                    ];
                }
            }
        }

        $leaveColors = [
            'Annual Leave' => '#d4edda',
            'Sick Leave' => '#f8d7da',
            'Family Responsibility' => '#fff3cd',
            'Study Leave' => '#cce5ff',
            'Maternity Leave' => '#f8d7f5',
            'Compassionate Leave' => '#d6d8fb',
            'Unpaid Leave' => '#e2e3e5',
        ];

        return view('employee.calendar.index', compact('month', 'year', 'events', 'leaveColors', 'publicHolidays'));
    }
}
