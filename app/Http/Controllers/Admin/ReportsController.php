<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Support\DashboardEmployeeFilter;
use App\Support\LeaveRequestPeriodFilter;
use App\Support\LeaveYear;
use App\Support\PdfDocuments;
use Illuminate\Http\Request;
use App\Services\PublicHolidayService;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $monthFilter = $request->query('month', 'this_year');
        $nowYear = LeaveYear::current();
        $years = range($nowYear - 1, $nowYear - 5);

        $leaveQuery = LeaveRequest::query()
            ->byStaffApplicants()
            ->whereRaw('LOWER(status) != ?', ['withdrawn']);
        DashboardEmployeeFilter::apply($leaveQuery, null);
        if ($startDate && $endDate) {
            $leaveQuery->whereDate('created_at', '>=', $startDate)
                       ->whereDate('created_at', '<=', $endDate);
        } else {
            LeaveRequestPeriodFilter::apply($leaveQuery, $monthFilter, 'created_at');
        }

        $totalRequests = (clone $leaveQuery)->count();
        $pendingTotal = (clone $leaveQuery)
            ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(status))'), ['pending', 'pending_senior'])
            ->count();
        $overdueRequests = (clone $leaveQuery)->overdue()->count();
        $pendingRequests = max(0, $pendingTotal - $overdueRequests);
        $approvedRequests = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['approved'])->count();
        $rejectedRequests = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['rejected'])->count();

        $leaves = $leaveQuery->get();

        // Prepare public holidays list for the years covered by filter or current year
        $startYear = $startDate ? Carbon::parse($startDate)->year : Carbon::now()->year;
        $endYear = $endDate ? Carbon::parse($endDate)->year : Carbon::now()->year;
        $publicHolidays = PublicHolidayService::datesForYearRange($startYear, $endYear);

        // Calculate total approved leave days excluding weekends and holidays
        // (half-day requests count as 0.5 day)
        $daysApproved = 0.0;
        foreach ($leaves as $leave) {
            if (strtolower($leave->status) === 'approved') {
                if (!empty($leave->is_half_day)) {
                    $d = Carbon::parse($leave->start_date);
                    if (!$d->isWeekend() && !in_array($d->toDateString(), $publicHolidays)) {
                        $daysApproved += 0.5;
                    }
                    continue;
                }
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
            $days = 0.0;
            if (!empty($leave->is_half_day)) {
                $d = Carbon::parse($leave->start_date);
                if (!$d->isWeekend() && !in_array($d->toDateString(), $publicHolidays)) {
                    $days = 0.5;
                }
                $leave->days = $days;
                return $leave;
            }

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
            'daysApproved', 'recentLeaves', 'startDate', 'endDate', 'monthFilter', 'years'
        ));
    }

    public function exportCsv(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $monthFilter = $request->query('month', 'this_year');

        $query = LeaveRequest::query()
            ->byStaffApplicants()
            ->whereRaw('LOWER(status) != ?', ['withdrawn']);
        DashboardEmployeeFilter::apply($query, null);

        if ($startDate && $endDate) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        } else {
            LeaveRequestPeriodFilter::apply($query, $monthFilter, 'created_at');
        }

        $leaves = $query->with('user')->get();

        $csvHeaders = [
            'Applicant Name', 'Role', 'Leave Type', 'Start Date', 'End Date', 'Days', 'Status', 'Requested At'
        ];

        $callback = function() use ($leaves, $csvHeaders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $csvHeaders);

            if($leaves->isEmpty()) {
                fputcsv($file, ['No leave records found for the selected date range']);
            } else {
                foreach ($leaves as $leave) {
                    $days = !empty($leave->is_half_day)
                        ? 0.5
                        : (Carbon::parse($leave->end_date)->diffInDays(Carbon::parse($leave->start_date)) + 1);
                    $status = $leave->status;
                    if ($leave->isOverdue()) {
                        $status = 'Overdue';
                    }
                    fputcsv($file, [
                        trim(($leave->user->first_name ?? '').' '.($leave->user->last_name ?? '')) ?: 'Unknown',
                        \App\Support\RoleLabels::display($leave->user->role ?? null),
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

    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $monthFilter = $request->query('month', 'this_year');

        $query = LeaveRequest::query()
            ->byStaffApplicants()
            ->whereRaw('LOWER(status) != ?', ['withdrawn'])
            ->with('user')
            ->orderByDesc('created_at');
        DashboardEmployeeFilter::apply($query, null);

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

        // Public holidays (for half-day + working days display)
        $startYear = $startDate ? Carbon::parse($startDate)->year : Carbon::now()->year;
        $endYear = $endDate ? Carbon::parse($endDate)->year : Carbon::now()->year;
        $publicHolidays = PublicHolidayService::datesForYearRange($startYear, $endYear);

        // Calculate days per leave (reuse logic: half-day 0.5)
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

        $title = 'Leave Requests Report (Senior Manager)';
        $subtitle = $startDate && $endDate
            ? "From $startDate to $endDate"
            : "Period: $monthFilter";

        $pdf = PdfDocuments::make('admin.reports.pdf', [
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

        return $pdf->download('leave_requests_report_senior_manager.pdf');
    }
}
