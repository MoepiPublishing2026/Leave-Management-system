<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yasumi\Yasumi;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $leaveQuery = LeaveRequest::query()
            ->whereRaw('LOWER(status) != ?', ['withdrawn']);
        if ($startDate && $endDate) {
            $leaveQuery->whereDate('created_at', '>=', $startDate)
                       ->whereDate('created_at', '<=', $endDate);
        }

        $totalRequests = (clone $leaveQuery)->count();
        $pendingTotal = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['pending'])->count();
        $overdueRequests = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today())
            ->count();
        $pendingRequests = max(0, $pendingTotal - $overdueRequests);
        $approvedRequests = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['approved'])->count();
        $rejectedRequests = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['rejected'])->count();

        $leaves = $leaveQuery->get();

        // Prepare public holidays list for the years covered by filter or current year
        $startYear = $startDate ? Carbon::parse($startDate)->year : Carbon::now()->year;
        $endYear = $endDate ? Carbon::parse($endDate)->year : Carbon::now()->year;
        $publicHolidays = [];
        for ($year = $startYear; $year <= $endYear; $year++) {
            $holidays = Yasumi::create('SouthAfrica', $year);
            foreach ($holidays as $holiday) {
                $publicHolidays[] = $holiday->format('Y-m-d');
            }
        }

        // Calculate total approved leave days excluding weekends and holidays
        $daysApproved = 0;
        foreach ($leaves as $leave) {
            if (strtolower($leave->status) === 'approved') {
                $date = Carbon::parse($leave->start_date);
                $leaveEndDate = Carbon::parse($leave->end_date);
                while ($date->lte($leaveEndDate)) {
                    if (!$date->isWeekend() && !in_array($date->toDateString(), $publicHolidays)) {
                        $daysApproved++;
                    }
                    $date->addDay();
                }
            }
        }

        // Recent leaves
        $recentLeaves = $leaves->sortByDesc('created_at')->take(10)->map(function ($leave) use ($publicHolidays) {
            $days = 0;
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            $current = $start->copy();
            while ($current->lte($end)) {
                if (!$current->isWeekend() && !in_array($current->toDateString(), $publicHolidays)) {
                    $days++;
                }
                $current->addDay();
            }
            $leave->days = $days;
            return $leave;
        });

        return view('admin.reports.index', compact(
            'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests',
            'overdueRequests',
            'daysApproved', 'recentLeaves', 'startDate', 'endDate'
        ));
    }

    public function exportCsv(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = LeaveRequest::query()
            ->whereRaw('LOWER(status) != ?', ['withdrawn']);

        if ($startDate && $endDate) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        }

        $leaves = $query->with('user')->get();

        $csvHeaders = [
            'Employee Name', 'Leave Type', 'Start Date', 'End Date', 'Days', 'Status', 'Requested At'
        ];

        $callback = function() use ($leaves, $csvHeaders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $csvHeaders);

            if($leaves->isEmpty()) {
                fputcsv($file, ['No leave records found for the selected date range']);
            } else {
                foreach ($leaves as $leave) {
                    $days = Carbon::parse($leave->end_date)->diffInDays(Carbon::parse($leave->start_date)) + 1;
                    $status = $leave->status;
                    $statusLower = strtolower(trim((string) $status));
                    if ($statusLower === 'pending' && $leave->start_date && Carbon::parse($leave->start_date)->lt(Carbon::today())) {
                        $status = 'Overdue';
                    }
                    fputcsv($file, [
                        $leave->user->first_name ?? 'Unknown',
                        $leave->leave_type,
                        $leave->start_date,
                        $leave->end_date,
                        $days,
                        $status,
                        $leave->created_at
                    ]);
                }
            }

            fclose($file);
        };

        $filename = 'leave_requests_' . ($startDate ?? 'all') . '_to_' . ($endDate ?? 'all') . '.csv';

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
