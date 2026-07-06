<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\CalendarService;
use App\Support\LeaveReminderService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarsController extends Controller
{
    public function index(Request $request)
    {
        $context = CalendarService::monthContext(
            (int) $request->query('month', Carbon::now()->month),
            (int) $request->query('year', Carbon::now()->year)
        );

        $leaves = LeaveRequest::query()
            ->byEmployeeApplicants()
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
            ->orderBy('start_date')
            ->get();

        $events = CalendarService::buildEvents(
            $leaves,
            $context['startOfMonth'],
            $context['endOfMonth'],
            $context['publicHolidays'],
            true
        );

        $summary = CalendarService::monthSummary($events);

        return view('manager.calendar.index', array_merge($context, [
            'events' => $events,
            'summary' => $summary,
            'calendarTitle' => 'Team Leave Calendar',
            'calendarRoute' => 'manager.calendar',
            'showUserInModal' => true,
            'interactive' => true,
        ]));
    }

    public function personal(Request $request)
    {
        $user = auth()->user();
        LeaveReminderService::syncForUser($user);

        $userId = auth()->id();

        $context = CalendarService::monthContext(
            (int) $request->query('month', Carbon::now()->month),
            (int) $request->query('year', Carbon::now()->year)
        );

        $leaves = LeaveRequest::query()
            ->where('user_id', $userId)
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

        return view('manager.calendar.index', array_merge($context, [
            'events' => $events,
            'summary' => $summary,
            'calendarTitle' => 'My Leave Calendar',
            'calendarRoute' => 'manager.myCalendar',
            'showUserInModal' => false,
            'interactive' => true,
        ]));
    }
}
