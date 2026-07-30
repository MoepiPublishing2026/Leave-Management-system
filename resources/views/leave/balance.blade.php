@extends($layout ?? \App\Support\LeaveApplicant::layout(auth()->user()))

@section('title', !empty($selectedType) ? $selectedType . ' Leave Balance' : 'Leave Balance')

@section('content')
@include('partials.balance-page-styles')

@php
    $fmtDays = function ($n) {
        return rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
    };
    $statusColors = config('leave_status_colors.colors', []);
    $chartPending = $statusColors['pending'] ?? '#C291A4';
    $chartApproved = $statusColors['approved'] ?? '#E27A53';
    $chartDeclined = $statusColors['rejected'] ?? '#87AFC7';
    $annualInfo = $leaveBalances[$annualKey] ?? ['default_days' => 0, 'taken_days' => 0, 'remaining_days' => 0];
    $annualAllocated = (float) ($annualInfo['default_days'] ?? 0);
    $annualTaken = (float) ($annualInfo['taken_days'] ?? 0);
@endphp

<div class="balance-page">
    @if(empty($selectedType) || empty($focusedBalance))
        <h1>Leave Balance</h1>
        <p class="balance-page-lead">
            Choose a leave type to view your <strong>{{ $year }}</strong> balance and activity.
            @if($year === $currentYear)
                Showing the current calendar year.
            @endif
        </p>

        <div class="balance-toolbar">
            <form class="balance-year-form balance-toolbar-left" method="GET" action="{{ route($balanceRoute ?? \App\Support\LeaveApplicant::balanceRoute()) }}">
                <label for="year">Year</label>
                <select id="year" name="year">
                    @foreach($yearOptions as $optionYear)
                        <option value="{{ $optionYear }}" @selected($year === $optionYear)>
                            {{ $optionYear }}{{ $optionYear === $currentYear ? ' (Current)' : '' }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="balance-btn balance-btn--primary">Update</button>
            </form>
            <div class="balance-toolbar-right">
                <a href="{{ route($dashboardRoute ?? \App\Support\LeaveApplicant::personalDashboardRoute(auth()->user())) }}" class="balance-btn balance-btn--secondary">My dashboard</a>
                @if(\App\Support\LeaveApplicant::isManager(auth()->user()))
                    <a href="{{ route('manager.dashboard') }}" class="balance-btn balance-btn--secondary">Dashboard</a>
                @endif
                <a href="{{ route($myRequestsRoute ?? \App\Support\LeaveApplicant::myRequestsRoute(auth()->user())) }}" class="balance-btn balance-btn--secondary">My Requests</a>
            </div>
        </div>

        @if($annualAllocated > 0)
            <div class="balance-summary-grid" style="margin-bottom: 22px;">
                <div class="balance-summary-card">
                    <div class="balance-summary-k">Annual remaining</div>
                    <div class="balance-summary-v">{{ $fmtDays($annualRemaining) }}</div>
                    <div class="balance-summary-cap">days available in {{ $year }}</div>
                </div>
            </div>
            <div class="balance-usage-panel">
                <div class="balance-usage-label">
                    <span>Annual usage in {{ $year }}</span>
                    <span>{{ $annualUsagePercent }}% · {{ $fmtDays($annualTaken) }} / {{ $fmtDays($annualAllocated) }} days</span>
                </div>
                <div class="balance-usage-track">
                    <div class="balance-usage-fill" style="width: {{ max($annualUsagePercent, 2) }}%;"></div>
                </div>
            </div>
        @endif

        <ul class="balance-type-grid">
            @foreach($leaveTypeOptions as $option)
                <li>
                    @if($option['disabled'])
                        <span class="balance-type-card is-disabled" aria-disabled="true"
                              title="{{ $option['disabled_reason'] ?? ($option['type'] . ' leave is not available') }}">
                            <span class="type-name">{{ $option['type'] }} Leave (Unavailable)</span>
                            <span class="type-days">{{ $fmtDays($option['remaining']) }} days</span>
                        </span>
                    @else
                        <a href="{{ route($balanceRoute ?? \App\Support\LeaveApplicant::balanceRoute(), ['year' => $year, 'type' => $option['type']]) }}"
                           class="balance-type-card">
                            <span class="type-name">{{ $option['type'] }} Leave</span>
                            <span class="type-days">{{ $fmtDays($option['remaining']) }} days</span>
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        @php
            $allocated = (float) ($focusedBalance['default_days'] ?? 0);
            $taken = (float) ($focusedBalance['taken_days'] ?? 0);
            $remaining = (float) ($focusedBalance['remaining_days'] ?? 0);
            $usagePct = (int) ($focusedBalance['usage_percent'] ?? 0);
            $remainingPct = $allocated > 0 ? min(100, (int) round(($remaining / $allocated) * 100)) : 0;
            $usedMeterWidth = max($usagePct, $taken > 0 ? 12 : 0);
            $remainingMeterWidth = max($remainingPct, $remaining > 0 ? 12 : 0);
            $statusMax = max(1, (int) ($requestCounts['pending'] ?? 0), (int) ($requestCounts['approved'] ?? 0), (int) ($requestCounts['rejected'] ?? 0));
            $pendingBarHeight = max(12, (int) round(((int) ($requestCounts['pending'] ?? 0) / $statusMax) * 48));
            $approvedBarHeight = max(12, (int) round(((int) ($requestCounts['approved'] ?? 0) / $statusMax) * 48));
            $declinedBarHeight = max(12, (int) round(((int) ($requestCounts['rejected'] ?? 0) / $statusMax) * 48));
            $docLabel = !empty($focusedBalance['requires_proof']) ? 'Required' : 'Not required';
            $halfDayLabel = !empty($focusedBalance['allow_half_days']) ? 'Allowed' : 'Not allowed';
        @endphp

        <h1>{{ $selectedType }} Leave</h1>
        <p class="balance-page-lead">
            Your <strong>{{ $year }}</strong> balance and activity for this leave type.
        </p>

        <div class="balance-toolbar">
            <form class="balance-year-form balance-toolbar-left" method="GET" action="{{ route($balanceRoute ?? \App\Support\LeaveApplicant::balanceRoute()) }}">
                <input type="hidden" name="type" value="{{ $selectedType }}" />
                <label for="year-detail">Year</label>
                <select id="year-detail" name="year">
                    @foreach($yearOptions as $optionYear)
                        <option value="{{ $optionYear }}" @selected($year === $optionYear)>
                            {{ $optionYear }}{{ $optionYear === $currentYear ? ' (Current)' : '' }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="balance-btn balance-btn--primary">Update</button>
            </form>
            <div class="balance-toolbar-right">
                @if(!$isTypeDisabled)
                    <a href="{{ route($applyRoute ?? \App\Support\LeaveApplicant::applyRoute(auth()->user()), ['leave_type' => $selectedType]) }}" class="balance-btn balance-btn--apply">+ Apply for {{ $selectedType }}</a>
                @endif
                <a href="{{ route($myRequestsRoute ?? \App\Support\LeaveApplicant::myRequestsRoute(auth()->user())) }}" class="balance-btn balance-btn--secondary">My Requests</a>
                <a href="{{ route($balanceRoute ?? \App\Support\LeaveApplicant::balanceRoute(), ['year' => $year]) }}" class="balance-btn balance-btn--secondary">Change type</a>
            </div>
        </div>

        <div class="balance-summary-grid">
            <div class="balance-summary-card">
                <div class="balance-summary-k">{{ $selectedType }} remaining</div>
                <div class="balance-summary-body">
                    <div class="balance-summary-v">{{ $fmtDays($remaining) }}</div>
                    <div class="balance-summary-cap">days available in {{ $year }}</div>
                </div>
            </div>
            <div class="balance-summary-card balance-summary-card--used">
                <div class="balance-summary-k">Used</div>
                <div class="balance-summary-body balance-summary-body--meter">
                    <div class="balance-summary-meter">
                        <div class="balance-summary-meter-fill" style="width: {{ $usedMeterWidth }}%;">
                            {{ $fmtDays($taken) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="balance-summary-card balance-summary-card--remaining-meter">
                <div class="balance-summary-k">Remaining</div>
                <div class="balance-summary-body balance-summary-body--meter">
                    <div class="balance-summary-meter">
                        <div class="balance-summary-meter-fill" style="width: {{ $remainingMeterWidth }}%;">
                            {{ $fmtDays($remaining) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="balance-summary-card balance-summary-card--allocated">
                <div class="balance-summary-k">Allocated</div>
                <div class="balance-summary-body">
                    <div class="balance-summary-v">{{ $fmtDays($allocated) }}</div>
                    <div class="balance-summary-cap balance-summary-cap--spacer" aria-hidden="true">&nbsp;</div>
                </div>
            </div>
        </div>

        @if($isTypeDisabled)
            <div class="balance-alert balance-alert--danger">
                @if($typeExhausted)
                    Applications for {{ $selectedType }} leave are disabled because no balance remains for this leave type.
                @else
                    Applications for {{ $selectedType }} leave are currently unavailable.
                @endif
            </div>
        @elseif($countsAgainstAnnual && $annualRemaining <= 5)
            <div class="balance-alert balance-alert--warn">
                This leave counts against your annual pool. You have <strong>{{ $fmtDays($annualRemaining) }}</strong> annual day(s) left overall.
            </div>
        @endif

        @if($pendingDaysTotal > 0)
            <div class="balance-alert balance-alert--info">
                <strong>{{ $fmtDays($pendingDaysTotal) }}</strong> day(s) pending approval — not deducted until approved.
            </div>
        @endif

        @if($allocated > 0)
            <div class="balance-usage-panel">
                <div class="balance-usage-label">
                    <span>Usage in {{ $year }}</span>
                    <span>{{ $usagePct }}% · {{ $fmtDays($taken) }} / {{ $fmtDays($allocated) }} days</span>
                </div>
                <div class="balance-usage-track">
                    <div class="balance-usage-fill" style="width: {{ max($usagePct, 2) }}%;"></div>
                </div>
            </div>
        @endif

        <div class="balance-detail-grid">
            <div class="balance-detail-card balance-detail-card--status">
                <div class="detail-k">Requests status</div>
                <div class="balance-status-bars" role="img" aria-label="Pending {{ $requestCounts['pending'] ?? 0 }}, approved {{ $requestCounts['approved'] ?? 0 }}, declined {{ $requestCounts['rejected'] ?? 0 }}">
                    <div class="balance-status-bar" style="height: {{ $pendingBarHeight }}px; background: {{ $chartPending }};" title="Pending: {{ $requestCounts['pending'] ?? 0 }}"></div>
                    <div class="balance-status-bar" style="height: {{ $approvedBarHeight }}px; background: {{ $chartApproved }};" title="Approved: {{ $requestCounts['approved'] ?? 0 }}"></div>
                    <div class="balance-status-bar" style="height: {{ $declinedBarHeight }}px; background: {{ $chartDeclined }};" title="Declined: {{ $requestCounts['rejected'] ?? 0 }}"></div>
                </div>
                <div class="detail-v detail-v--status-meta">Declined: {{ $requestCounts['rejected'] ?? 0 }}</div>
            </div>
            <div class="balance-detail-card">
                <div class="detail-k">Pending requests</div>
                <div class="detail-v">{{ $requestCounts['pending'] ?? 0 }} ({{ $fmtDays($pendingDaysTotal) }} days)</div>
            </div>
            <div class="balance-detail-card">
                <div class="detail-k">Documents</div>
                <div class="detail-v">{{ $docLabel }} · Half days {{ strtolower($halfDayLabel) }}</div>
            </div>
            <div class="balance-detail-card">
                <div class="detail-k">Approved requests</div>
                <div class="detail-v">{{ $requestCounts['approved'] ?? 0 }}</div>
            </div>
        </div>

        @if($upcomingLeaves->isNotEmpty())
            <div class="balance-panel">
                <h3 class="balance-section-title">Upcoming approved leave</h3>
                <div class="balance-table-wrap">
                    <table class="balance-table">
                        <thead>
                            <tr>
                                <th>Start</th>
                                <th>End</th>
                                <th>Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingLeaves as $leave)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                                    <td>{{ $fmtDays($leave->days ?? 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($pendingLeavesDisplay->isNotEmpty())
            <div class="balance-panel">
                <h3 class="balance-section-title">Pending leave</h3>
                <div class="balance-table-wrap">
                    <table class="balance-table">
                        <thead>
                            <tr>
                                <th>Start</th>
                                <th>End</th>
                                <th>Days</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingLeavesDisplay as $leave)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                                    <td>{{ $fmtDays($leave->days ?? 0) }}</td>
                                    <td><span class="request-badge request-badge--pending">Pending</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="balance-panel">
            <h3 class="balance-section-title">Approved leave taken ({{ $year }})</h3>
            <div class="balance-table-wrap">
                <table class="balance-table">
                    <thead>
                        <tr>
                            <th>Start</th>
                            <th>End</th>
                            <th>Days</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($approvedLeaves as $leave)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                                <td>{{ $fmtDays($leave->days ?? 0) }}</td>
                                <td><span class="request-badge request-badge--approved">Approved</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="balance-empty">No approved {{ $selectedType }} leave for {{ $year }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="balance-back">
            <a href="{{ route($balanceRoute ?? \App\Support\LeaveApplicant::balanceRoute(), ['year' => $year]) }}">← Choose another leave type</a>
        </div>
    @endif
</div>
@endsection
