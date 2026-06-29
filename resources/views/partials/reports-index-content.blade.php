@php
    $filterRoute = $filterRoute ?? 'admin.reports';
    $exportRoute = $exportRoute ?? 'admin.reports.export_pdf';
    $pageTitle = $pageTitle ?? 'Leave Requests Report';
@endphp

<div class="reports-page">
    <h1>{{ $pageTitle }}</h1>
    <p class="reports-page-lead">Summary and recent activity for the selected period.</p>

    <div class="reports-toolbar">
        <form id="reportsFilterForm" method="GET" action="{{ route($filterRoute) }}" class="reports-filter-form reports-toolbar-forms">
            <select name="month" class="filter-select" aria-label="Period" onchange="this.form.submit()">
                <option value="this_year" {{ ($monthFilter ?? 'this_year') === 'this_year' ? 'selected' : '' }}>This Year</option>
                <option value="this_quarter" {{ ($monthFilter ?? '') === 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                <option value="this_month" {{ ($monthFilter ?? '') === 'this_month' ? 'selected' : '' }}>This Month</option>
                @foreach(($years ?? []) as $y)
                    <option value="{{ $y }}" {{ (string) ($monthFilter ?? '') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <input type="text" name="start_date" id="reports_start_date" class="reports-date-input" value="{{ request('start_date') }}" placeholder="Start date" autocomplete="off" aria-label="Start date" />
            <input type="text" name="end_date" id="reports_end_date" class="reports-date-input" value="{{ request('end_date') }}" placeholder="End date" autocomplete="off" aria-label="End date" />
            <button type="submit" class="reports-btn reports-btn--primary">Filter</button>
            <a href="{{ route($filterRoute) }}" class="reports-btn reports-btn--secondary">Clear</a>
        </form>
        <form method="GET" action="{{ route($exportRoute) }}" class="reports-toolbar-forms">
            <input type="hidden" name="month" value="{{ $monthFilter ?? request('month', 'this_year') }}" />
            <input type="hidden" name="start_date" value="{{ request('start_date') }}" />
            <input type="hidden" name="end_date" value="{{ request('end_date') }}" />
            <button type="submit" class="reports-btn reports-btn--export">Export PDF</button>
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

    <h2 class="reports-section-title">Recent Leave Requests</h2>
    <div class="reports-table-wrap">
        <table class="reports-table" role="table" aria-label="Recent leave requests">
            <thead>
            <tr>
                <th>Employee</th>
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
                    <td>{{ $employeeName }}</td>
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
