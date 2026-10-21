<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Support\LeaveRequestPeriodFilter;
use App\Support\LeaveYear;
use App\Support\ManagerTeam;
use App\Support\PdfDocuments;
use Illuminate\Http\Request;
use App\Services\PublicHolidayService;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $monthFilter = $request->query('month', 'this_year');
        $nowYear = LeaveYear::current();
        $years = range($nowYear - 1, $nowYear - 5);

        // Base leave query excluding withdrawn
        $leaveQuery = LeaveRequest::query()
            ->byEmployeeApplicants()
            ->tap(fn ($q) => ManagerTeam::scopeLeaveRequests($q))
            ->whereRaw('LOWER(status) != ?', ['withdrawn']);

        // Filter by created_at if dates supplied
        if ($startDate && $endDate) {
            $leaveQuery->whereDate('created_at', '>=', $startDate)
                       ->whereDate('created_at', '<=', $endDate);
        } else {
            LeaveRequestPeriodFilter::apply($leaveQuery, $monthFilter, 'created_at');
        }

        // Fetch approved leaves to calculate days
        $approvedLeaves = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['approved'])->get();

        // Generate all public holidays within year range of filter
        $startYear = $startDate ? Carbon::parse($startDate)->year : Carbon::now()->year;
        $endYear = $endDate ? Carbon::parse($endDate)->year : Carbon::now()->year;
        $publicHolidays = PublicHolidayService::datesForYearRange($startYear, $endYear);

        // Calculate total approved leave days excluding weekends and public holidays
        // (half-day requests count as 0.5 day)
        $daysApproved = 0.0;
        foreach ($approvedLeaves as $leave) {
            if (!empty($leave->is_half_day)) {
                $d = Carbon::parse($leave->start_date);
                if (!$d->isWeekend() && !in_array($d->toDateString(), $publicHolidays)) {
                    $daysApproved += 0.5;
                }
                continue;
            }
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
        $pendingTotal = (clone $leaveQuery)->whereRaw('LOWER(TRIM(status)) in (?, ?)', ['pending', 'pending_senior'])->count();
        $overdueRequests = (clone $leaveQuery)->overdue()->count();
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
                $days = 0.0;

                if (!empty($leave->is_half_day)) {
                    if (!$start->isWeekend() && !in_array($start->toDateString(), $publicHolidays)) {
                        $days = 0.5;
                    }
                    $leave->days = $days;
                    return $leave;
                }

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
            'daysApproved', 'recentLeaves', 'startDate', 'endDate', 'monthFilter', 'years'
        ));
    }



    public function exportCsv(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $monthFilter = $request->query('month', 'this_year');

        // Base query excluding withdrawn
        $query = LeaveRequest::query()
            ->byEmployeeApplicants()
            ->tap(fn ($q) => ManagerTeam::scopeLeaveRequests($q))
            ->whereRaw('LOWER(status) != ?', ['withdrawn']);

        if ($startDate && $endDate) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        } else {
            LeaveRequestPeriodFilter::apply($query, $monthFilter, 'created_at');
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
                    $days = !empty($leave->is_half_day)
                        ? 0.5
                        : (Carbon::parse($leave->end_date)->diffInDays(Carbon::parse($leave->start_date)) + 1);
                    $status = $leave->status;
                    if ($leave->isOverdue()) {
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

    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $monthFilter = $request->query('month', 'this_year');

        $query = LeaveRequest::query()
            ->byEmployeeApplicants()
            ->tap(fn ($q) => ManagerTeam::scopeLeaveRequests($q))
            ->whereRaw('LOWER(status) != ?', ['withdrawn'])
            ->with('user')
            ->orderByDesc('created_at');

        if ($startDate && $endDate) {
            $query->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        } else {
            LeaveRequestPeriodFilter::apply($query, $monthFilter, 'created_at');
        }

        $leaves = $query->get();

        $totalRequests = $leaves->count();
        $pendingTotal = $leaves->filter(
            fn ($l) => in_array(strtolower(trim((string) $l->status)), ['pending', 'pending_senior'], true)
        )->count();
        $overdueRequests = $leaves->filter(fn ($l) => $l->isOverdue())->count();
        $pendingRequests = max(0, $pendingTotal - $overdueRequests);
        $approvedRequests = $leaves->filter(fn ($l) => strtolower((string) $l->status) === 'approved')->count();
        $rejectedRequests = $leaves->filter(fn ($l) => strtolower((string) $l->status) === 'rejected')->count();

        $startYear = $startDate ? Carbon::parse($startDate)->year : Carbon::now()->year;
        $endYear = $endDate ? Carbon::parse($endDate)->year : Carbon::now()->year;
        $publicHolidays = PublicHolidayService::datesForYearRange($startYear, $endYear);

        $leavesForPdf = $leaves->take(200)->values()->map(function ($leave) use ($publicHolidays) {
            $days = 0.0;
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            if (!empty($leave->is_half_day)) {
                if (!$start->isWeekend() && !in_array($start->toDateString(), $publicHolidays)) {
                    $days = 0.5;
                }
                $leave->days = $days;
                return $leave;
            }
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

        $title = 'Leave Requests Report (Manager)';
        $subtitle = $startDate && $endDate
            ? "From $startDate to $endDate"
            : "Period: $monthFilter";

        $pdf = PdfDocuments::make('manager.reports.pdf', [
            'title' => $title,
            'subtitle' => $subtitle,
            'generatedAt' => Carbon::now(),
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'overdueRequests' => $overdueRequests,
            'approvedRequests' => $approvedRequests,
            'rejectedRequests' => $rejectedRequests,
            'leaves' => $leavesForPdf,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('leave_requests_report_manager.pdf');
    }
}
