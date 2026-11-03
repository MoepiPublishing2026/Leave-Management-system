@php
    $filterRoute = $filterRoute ?? 'admin.reports';
    $exportRoute = $exportRoute ?? 'admin.reports.export_pdf';
    $pageTitle = $pageTitle ?? 'Leave Requests Report';
@endphp

<div class="reports-page">
    <h1>{{ $pageTitle }}</h1>
    <p class="reports-page-lead">Summary and recent activity for the selected period.</p>

    <div class="reports-toolbar" style="display: flex !important; flex-wrap: wrap !important; justify-content: flex-start !important; gap: 12px !important; margin-bottom: 24px !important; align-items: center !important; background: #dbe4e6 !important; padding: 12px 16px !important; border-radius: 12px !important; border: none !important; width: 100% !important; box-sizing: border-box !important;">
        <form id="reportsFilterForm" method="GET" action="{{ route($filterRoute) }}" style="display: contents !important;">
            
            <select name="month" class="filter-select" aria-label="Period" onchange="this.form.submit()" style="border: none !important; border-radius: 8px !important; padding: 8px 12px !important; font-size: 14px !important; font-weight: 600 !important; color: #334155 !important; background: transparent !important; height: 38px !important; outline: none !important; min-width: 120px !important;">
                <option value="this_year" {{ ($monthFilter ?? 'this_year') === 'this_year' ? 'selected' : '' }}>This Year</option>
                <option value="this_quarter" {{ ($monthFilter ?? '') === 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                <option value="this_month" {{ ($monthFilter ?? '') === 'this_month' ? 'selected' : '' }}>This Month</option>
                @foreach(($years ?? []) as $y)
                    <option value="{{ $y }}" {{ (string) ($monthFilter ?? '') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            
            <input type="text" name="start_date" id="reports_start_date" class="reports-date-input" value="{{ request('start_date') }}" placeholder="Start Date" autocomplete="off" aria-label="Start Date" style="border: none !important; border-radius: 8px !important; padding: 8px 12px !important; font-size: 14px !important; font-weight: 500 !important; color: #334155 !important; background: #fff !important; height: 38px !important; outline: none !important; width: 120px !important;" />
            
            <input type="text" name="end_date" id="reports_end_date" class="reports-date-input" value="{{ request('end_date') }}" placeholder="End Date" autocomplete="off" aria-label="End Date" style="border: none !important; border-radius: 8px !important; padding: 8px 12px !important; font-size: 14px !important; font-weight: 500 !important; color: #334155 !important; background: #fff !important; height: 38px !important; outline: none !important; width: 120px !important;" />
            
            <button type="submit" class="reports-btn reports-btn--primary" style="background: #cbe212 !important; color: #0a1347 !important; border: none !important; border-radius: 6px !important; padding: 0 16px !important; font-weight: 700 !important; font-size: 14px !important; height: 38px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; cursor: pointer !important;">Filter</button>
            
            <a href="{{ route($filterRoute) }}" class="reports-btn reports-btn--secondary" style="background: #cbe212 !important; color: #0a1347 !important; border: none !important; border-radius: 6px !important; padding: 0 16px !important; font-weight: 700 !important; font-size: 14px !important; height: 38px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; text-decoration: none !important;">Clear</a>
        
            <!-- Hidden inputs for Export -->
            <input type="hidden" name="export_month" value="{{ $monthFilter ?? request('month', 'this_year') }}" />
            <input type="hidden" name="export_start_date" value="{{ request('start_date') }}" />
            <input type="hidden" name="export_end_date" value="{{ request('end_date') }}" />
        </form>
        
        <!-- Export PDF pushed to the far right using margin-left: auto -->
        <form method="GET" action="{{ route($exportRoute) }}" style="display: inline-flex !important; margin-left: auto !important;">
            <input type="hidden" name="month" value="{{ $monthFilter ?? request('month', 'this_year') }}" />
            <input type="hidden" name="start_date" value="{{ request('start_date') }}" />
            <input type="hidden" name="end_date" value="{{ request('end_date') }}" />
            <button type="submit" class="reports-btn reports-btn--export" style="background: #cbe212 !important; color: #0a1347 !important; border: none !important; border-radius: 6px !important; padding: 0 16px !important; font-weight: 700 !important; font-size: 14px !important; height: 38px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; cursor: pointer !important;">Export PDF</button>
        </form>
    </div>

    <div class="cards" role="list">
        <div class="card card-all" role="listitem">
            <div class="card-title">All Requests</div>
            <div class="card-value">{{ $totalRequests ?? 0 }}</div>
        </div>
        <div class="card card-pending" role="listitem">
            <div class="card-title">Pending Requests</div>
            <div class="card-value">{{ $pendingRequests ?? 0 }}</div>
        </div>
        <div class="card card-overdue" role="listitem">
            <div class="card-title">Overdue Requests</div>
            <div class="card-value">{{ $overdueRequests ?? 0 }}</div>
        </div>
        <div class="card card-approved" role="listitem">
            <div class="card-title">Approved Requests</div>
            <div class="card-value">{{ $approvedRequests ?? 0 }}</div>
        </div>
        <div class="card card-rejected" role="listitem">
            <div class="card-title">Rejected Requests</div>
            <div class="card-value">{{ $rejectedRequests ?? 0 }}</div>
        </div>
    </div>

    @php $reportsForSeniorManager = strtolower((string) (auth()->user()->role ?? '')) === 'admin'; @endphp
    <h2 class="reports-section-title">Recent Leave Requests</h2>
    <div class="reports-table-wrap">
        <table class="reports-table" role="table" aria-label="Recent leave requests">
            <thead>
            <tr>
                <th>{{ $reportsForSeniorManager ? 'Applicant' : 'Employee' }}</th>
                <th>Type</th>
                <th>Duration</th>
                <th>Days</th>
                <th>Status</th>
                <th>Requested</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($recentLeaves as $leave)
                @php
                    $isOverdue = method_exists($leave, 'isOverdue') ? $leave->isOverdue() : false;
                    $statusText = $isOverdue ? 'overdue' : strtolower((string) $leave->status);
                    if ($statusText === 'pending_senior') {
                        $statusText = 'pending';
                    }
                    $statusClass = match ($statusText) {
                        'pending' => 'request-badge--pending',
                        'overdue' => 'request-badge--overdue',
                        'approved' => 'request-badge--approved',
                        'rejected' => 'request-badge--rejected',
                        'withdrawn' => 'request-badge--withdrawn',
                        default => 'request-badge--pending',
                    };
                    $employeeName = trim(($leave->user->first_name ?? '') . ' ' . ($leave->user->last_name ?? ''));
                    if ($employeeName === '') {
                        $employeeName = 'Unknown';
                    }
                @endphp
                <tr>
                    <td>
                        {{ $employeeName }}
                    </td>
                    <td>{{ $leave->leave_type }}</td>
                    <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }} – {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                    <td>{{ \App\Support\LeaveDaysFormatter::format($leave->days ?? '') }}</td>
                    <td>
                        @if ($statusText === 'overdue')
                            <span class="request-badge request-badge--overdue">Overdue</span>
                        @else
                            <span class="request-badge {{ $statusClass }}">{{ ucfirst($statusText) }}</span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($leave->created_at)->format('d M Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="reports-empty">No leave requests for this period.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@include('partials.flatpickr-grid-fix')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const reportsFpOptions = { dateFormat: 'Y-m-d', disableMobile: true };
    flatpickr('#reports_start_date', reportsFpOptions);
    flatpickr('#reports_end_date', reportsFpOptions);
});

document.getElementById('reportsFilterForm')?.addEventListener('submit', function (e) {
    const form = this;
    const start = form.querySelector('input[name="start_date"]')?.value || '';
    const end = form.querySelector('input[name="end_date"]')?.value || '';

    if ((start && !end) || (!start && end)) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Select both dates',
            text: 'Please select both start and end date to filter.',
            confirmButtonText: 'OK'
        });
        return;
    }

    e.preventDefault();
    Swal.fire({
        icon: 'success',
        title: 'Filter Successful',
        text: 'Filtering was successful. You can now proceed to export the PDF.',
        confirmButtonText: 'OK'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>