<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendaController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $context = CalendarService::monthContext(
            (int) $request->query('month', Carbon::now()->month),
            (int) $request->query('year', Carbon::now()->year)
        );

        $leaves = LeaveRequest::where('user_id', $userId)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->where(function ($query) use ($context) {
                $query->whereBetween('start_date', [$context['startOfMonth'], $context['endOfMonth']])
                    ->orWhereBetween('end_date', [$context['startOfMonth'], $context['endOfMonth']])
                    ->orWhere(function ($sub) use ($context) {
                        $sub->where('start_date', '<=', $context['startOfMonth'])
                            ->where('end_date', '>=', $context['endOfMonth']);
                    });
            })
            ->orderBy('start_date')
            ->get();

        $events = CalendarService::buildEvents(
            $leaves,
            $context['startOfMonth'],
            $context['endOfMonth'],
            $context['publicHolidays'],
            false
        );

        $summary = CalendarService::monthSummary($events);

        return view('employee.calendar.index', array_merge($context, [
            'events' => $events,
            'summary' => $summary,
            'calendarTitle' => 'My Leave Calendar',
            'calendarRoute' => 'employee.calendar',
            'showUserInModal' => false,
            'interactive' => true,
        ]));
    }
}
