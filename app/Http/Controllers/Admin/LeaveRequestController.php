<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\UserDeletionService;
use Illuminate\Http\Request;
use App\Support\LeaveRequestSearch;
use App\Support\LeaveRequestPeriodFilter;
use App\Support\DashboardEmployeeFilter;
use App\Support\LeavePayFilter;
use App\Support\MissingProofUnpaidConverter;
use Illuminate\Support\Carbon;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        MissingProofUnpaidConverter::convertDue();

        $status = strtolower((string) $request->query('status', 'all'));
        $payFilter = LeavePayFilter::normalize($request->query('pay', 'all'));
        $search = trim((string) $request->query('search', ''));
        $highlightRequestId = (int) $request->query('request_id', 0);
        if ($highlightRequestId > 0 && $search === '') {
            $search = (string) $highlightRequestId;
        }
        $searchKey = strtolower($search);
        $monthFilter = $request->query('month');
        $employeeFilter = DashboardEmployeeFilter::selectedId($request->query('employee'));

        $query = LeaveRequest::query()
            ->whereHas('user', fn ($q) => $q->whereIn('role', ['employee', 'manager']))
            ->with(['user', 'overdueEscalatedBy', 'handledBy'])
            ->orderByDesc('created_at');

        LeaveRequestPeriodFilter::apply($query, is_string($monthFilter) ? $monthFilter : null, 'created_at');
        DashboardEmployeeFilter::apply($query, $employeeFilter);
        LeavePayFilter::apply($query, $payFilter);

        if ($status === 'overdue') {
            $query->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
                ->whereDate('start_date', '<', Carbon::today());
        } elseif ($status === 'pending') {
            // Awaiting Senior Manager decision
            $query->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(status)) = ?', ['pending_senior'])
                    ->orWhere(function ($inner) {
                        $inner->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
                            ->whereHas('user', fn ($u) => $u->where('role', 'manager'));
                    });
            });
        } elseif ($status === 'pending_manager') {
            $query->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
                ->whereHas('user', fn ($u) => $u->where('role', 'employee'))
                ->whereDate('start_date', '>=', Carbon::today());
        } elseif ($status === 'pending_senior') {
            $query->whereRaw('LOWER(TRIM(status)) = ?', ['pending_senior']);
        } elseif ($status !== 'all') {
            $query->whereRaw('LOWER(TRIM(status)) = ?', [$status]);
        } else {
            $query->whereRaw('LOWER(TRIM(status)) != ?', ['withdrawn']);
        }

        $effectiveSearch = $search;
        if ($status === 'all' && $searchKey === 'overdue') {
            $query->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
                ->whereDate('start_date', '<', Carbon::today());
            $effectiveSearch = '';
        } elseif ($status === 'all' && $searchKey === 'pending') {
            $query->where(function ($pendingQuery) {
                $pendingQuery->whereRaw('LOWER(TRIM(status)) = ?', ['pending_senior'])
                    ->orWhere(function ($managerReviewQuery) {
                        $managerReviewQuery->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
                            ->whereDate('start_date', '>=', Carbon::today());
                    });
            });
            $effectiveSearch = '';
        }

        if ($effectiveSearch !== '') {
            LeaveRequestSearch::apply($query, $effectiveSearch);
        }

        $requests = $query->paginate(10)->withQueryString();

        return view('admin.requests.index', compact(
            'requests',
            'status',
            'payFilter',
            'search',
            'monthFilter',
            'employeeFilter'
        ));
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        if (strtolower((string) (auth()->user()->role ?? '')) !== 'admin') {
            abort(403);
        }

        $status = strtolower(trim((string) ($leaveRequest->status ?? '')));
        $currentYear = (int) \Carbon\Carbon::now()->year;
        $requestYear = $leaveRequest->start_date ? (int) \Carbon\Carbon::parse($leaveRequest->start_date)->year : $currentYear;
        $isPreviousYear = $requestYear === ($currentYear - 1);
        if (! $isPreviousYear) {
            return redirect()
                ->back()
                ->with('error', 'Only requests from the previous year can be deleted.');
        }

        try {
            $requestId = $leaveRequest->id;
            app(UserDeletionService::class)->deleteLeaveRequestRecord($leaveRequest);

            return redirect()
                ->back()
                ->with('success', 'Leave request #' . $requestId . ' deleted permanently.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with('error', 'Unable to delete this leave request. Please try again.');
        }
    }
}
