<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\CalendarService;
use App\Support\DashboardEmployeeFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $context = CalendarService::monthContext(
            (int) $request->query('month', Carbon::now()->month),
            (int) $request->query('year', Carbon::now()->year)
        );

        $leavesQuery = LeaveRequest::query()
            ->byStaffApplicants()
            ->with('user')
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->where(function ($query) use ($context) {
                $query->whereBetween('start_date', [$context['startOfMonth'], $context['endOfMonth']])
                    ->orWhereBetween('end_date', [$context['startOfMonth'], $context['endOfMonth']])
                    ->orWhere(function ($sub) use ($context) {
                        $sub->where('start_date', '<=', $context['startOfMonth'])
                            ->where('end_date', '>=', $context['endOfMonth']);
                    });
            })
            ->orderBy('start_date');
        DashboardEmployeeFilter::apply($leavesQuery, null);
        $leaves = $leavesQuery->get();

        $events = CalendarService::buildEvents(
            $leaves,
            $context['startOfMonth'],
            $context['endOfMonth'],
            $context['publicHolidays'],
            true
        );

        $summary = CalendarService::monthSummary($events);

        return view('admin.calendar.index', array_merge($context, [
            'events' => $events,
            'summary' => $summary,
            'calendarTitle' => 'Staff Leave Calendar',
            'calendarRoute' => 'admin.calendar',
            'showUserInModal' => true,
            'interactive' => true,
        ]));
    }
}
