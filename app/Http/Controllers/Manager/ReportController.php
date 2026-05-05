<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yasumi\Yasumi;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Base leave query excluding withdrawn
        $leaveQuery = LeaveRequest::whereRaw('LOWER(status) != ?', ['withdrawn']);

        // Filter by created_at if dates supplied
        if ($startDate && $endDate) {
            $leaveQuery->whereDate('created_at', '>=', $startDate)
                       ->whereDate('created_at', '<=', $endDate);
        }

        // Fetch approved leaves to calculate days
        $approvedLeaves = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['approved'])->get();

        // Generate all public holidays within year range of filter
        $publicHolidays = [];
        $startYear = $startDate ? Carbon::parse($startDate)->year : Carbon::now()->year;
        $endYear = $endDate ? Carbon::parse($endDate)->year : Carbon::now()->year;
        for ($year = $startYear; $year <= $endYear; $year++) {
            $yearHolidays = Yasumi::create('SouthAfrica', $year);
            foreach ($yearHolidays as $holiday) {
                $publicHolidays[] = $holiday->format('Y-m-d');
            }
        }

        // Calculate total approved leave days excluding weekends and public holidays
        $daysApproved = 0;
        foreach ($approvedLeaves as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            $current = $start->copy();
            while ($current->lte($end)) {
                if (!$current->isWeekend() && !in_array($current->toDateString(), $publicHolidays)) {
                    $daysApproved++;
                }
                $current->addDay();
            }
        }

        $totalRequests = (clone $leaveQuery)->count();
        $pendingTotal = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['pending'])->count();
        $overdueRequests = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today())
            ->count();
        $pendingRequests = max(0, $pendingTotal - $overdueRequests);
        $approvedRequests = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['approved'])->count();
        $rejectedRequests = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['rejected'])->count();

        // Fetch recent leaves excluding withdrawn
        $recentLeaves = (clone $leaveQuery)->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($leave) use ($publicHolidays) {
                $start = Carbon::parse($leave->start_date);
                $end = Carbon::parse($leave->end_date);
                $days = 0;
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

        return view('manager.reports.index', compact(
            'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests',
            'overdueRequests',
            'daysApproved', 'recentLeaves', 'startDate', 'endDate'
        ));
    }



    public function exportCsv(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Base query excluding withdrawn
        $query = LeaveRequest::whereRaw('LOWER(status) != ?', ['withdrawn']);

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
            // Help Excel reliably split columns
            // - UTF-8 BOM so names render correctly
            // - "sep=," line tells Excel the delimiter
            fwrite($file, "\xEF\xBB\xBF");
            fwrite($file, "sep=,\r\n");
            fputcsv($file, $csvHeaders, ',');

            if($leaves->isEmpty()) {
                fputcsv($file, ['No leave records found for the selected date range'], ',');
            } else {
                foreach ($leaves as $leave) {
                    $days = Carbon::parse($leave->end_date)->diffInDays(Carbon::parse($leave->start_date)) + 1;
                    $status = $leave->status;
                    $statusLower = strtolower(trim((string) $status));
                    if ($statusLower === 'pending' && $leave->start_date && Carbon::parse($leave->start_date)->lt(Carbon::today())) {
                        $status = 'Overdue';
                    }
                    $employeeName = trim(($leave->user->first_name ?? '') . ' ' . ($leave->user->last_name ?? ''));
                    if ($employeeName === '') {
                        $employeeName = $leave->user->email ?? 'Unknown';
                    }

                    fputcsv($file, [
                        $employeeName,
                        $leave->leave_type,
                        $leave->start_date,
                        $leave->end_date,
                        $days,
                        $status,
                        $leave->created_at
                    ], ',');
                }
            }
            fclose($file);
        };

        $filename = 'leave_requests_' . ($startDate ?? 'all') . '_to_' . ($endDate ?? 'all') . '.csv';

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
