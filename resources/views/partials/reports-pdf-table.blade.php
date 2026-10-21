@php
    $nameHeading = $nameHeading ?? 'Employee';
@endphp
<p class="reports-section-title">Recent Leave Requests</p>
<div class="reports-table-shell">
    <table class="data">
        <thead>
            <tr>
                <th style="width: 18%;">{{ $nameHeading }}</th>
                <th style="width: 12%;">Type</th>
                <th style="width: 24%;">Duration</th>
                <th style="width: 8%;">Days</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 16%;">Requested</th>
            </tr>
        </thead>
        <tbody>
        @forelse(($leaves ?? []) as $leave)
            @php
                $isOverdue = method_exists($leave, 'isOverdue') ? $leave->isOverdue() : false;
                $statusKey = \App\Support\LeaveStatusLabels::badgeClass($leave->status, $isOverdue);
                $statusLabel = \App\Support\LeaveStatusLabels::display($leave->status, $isOverdue);
                $employeeName = trim(($leave->user->first_name ?? '') . ' ' . ($leave->user->last_name ?? ''));
                if ($employeeName === '') {
                    $employeeName = $leave->user->email ?? 'Unknown';
                }
                $start = $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('d M Y') : '—';
                $end = $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('d M Y') : '—';
            @endphp
            <tr>
                <td>
                    {{ $employeeName }}
                    @if (!empty($showRole) && ! in_array(strtolower((string) ($leave->user->role ?? '')), ['employee', ''], true))
                        ({{ \App\Support\RoleLabels::display($leave->user->role ?? null) }})
                    @endif
                </td>
                <td>{{ $leave->leave_type }}</td>
                <td>{{ $start }} – {{ $end }}</td>
                <td>{{ \App\Support\LeaveDaysFormatter::format($leave->days ?? '') }}</td>
                <td><span class="pdf-badge pdf-badge--{{ $statusKey }}">{{ $statusLabel }}</span></td>
                <td>{{ optional($leave->created_at)->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="reports-empty">No leave requests for this period.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
