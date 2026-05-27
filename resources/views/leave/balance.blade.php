@extends('layouts.app')

@section('title', !empty($selectedType) ? $selectedType . ' Leave Balance' : 'Leave Balance')

@section('content')
@include('partials.balance-page-styles')

@php
    $fmtDays = function ($n) {
        return rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
    };
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
            <form class="balance-year-form balance-toolbar-left" method="GET" action="{{ route('leave.balance') }}">
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
                <a href="{{ route('dashboard') }}" class="balance-btn balance-btn--secondary">Dashboard</a>
                <a href="{{ route('leave.myRequests') }}" class="balance-btn balance-btn--secondary">My Requests</a>
            </div>
        </div>

        @if($annualAllocated > 0)
            <div class="balance-annual-strip">
                <div>
                    <div class="strip-label">Annual leave remaining</div>
                    <div class="strip-value">{{ $fmtDays($annualRemaining) }}</div>
                    <div class="strip-caption">
                        {{ $fmtDays($annualTaken) }} of {{ $fmtDays($annualAllocated) }} days used in {{ $year }}
                    </div>
                </div>
                <div class="strip-meter">
                    <div class="balance-usage-label">
                        <span>Annual usage</span>
                        <span>{{ $annualUsagePercent }}%</span>
                    </div>
                    <div class="balance-usage-track">
                        <div class="balance-usage-fill {{ $annualUsagePercent >= 90 ? 'is-danger' : ($annualUsagePercent >= 70 ? 'is-warn' : '') }}"
                             style="width: {{ max($annualUsagePercent, 2) }}%;"></div>
                    </div>
                </div>
            </div>
        @endif

        <ul class="balance-type-grid">
            @foreach($leaveTypeOptions as $option)
                <li>
                    <a href="{{ route('leave.balance', ['year' => $year, 'type' => $option['type']]) }}"
                       class="balance-type-card {{ $option['disabled'] ? 'is-disabled' : '' }}">
                        <span class="type-name">{{ $option['type'] }} Leave</span>
                        <span class="type-days">{{ $fmtDays($option['remaining']) }} days</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        @php
            $allocated = (float) ($focusedBalance['default_days'] ?? 0);
            $taken = (float) ($focusedBalance['taken_days'] ?? 0);
            $remaining = (float) ($focusedBalance['remaining_days'] ?? 0);
            $usagePct = (int) ($focusedBalance['usage_percent'] ?? 0);
            $meterClass = $usagePct >= 90 ? 'is-danger' : ($usagePct >= 70 ? 'is-warn' : '');
        @endphp

        <h1>{{ $selectedType }} Leave</h1>
        <p class="balance-page-lead">
            Your <strong>{{ $year }}</strong> balance and activity for this leave type.
        </p>

        <div class="balance-toolbar">
            <form class="balance-year-form balance-toolbar-left" method="GET" action="{{ route('leave.balance') }}">
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
                    <a href="{{ route('apply', ['leave_type' => $selectedType]) }}" class="balance-btn balance-btn--apply">+ Apply for {{ $selectedType }}</a>
                @endif
                <a href="{{ route('leave.myRequests') }}" class="balance-btn balance-btn--secondary">My Requests</a>
                <a href="{{ route('leave.balance', ['year' => $year]) }}" class="balance-btn balance-btn--secondary">Change type</a>
            </div>
        </div>

        <div class="balance-hero">
            <div>
                <div class="hero-label">{{ $selectedType }} remaining</div>
                <div class="hero-value">{{ $fmtDays($remaining) }}</div>
                <div class="hero-caption">days available in {{ $year }}</div>
            </div>
            <div class="balance-stat-row">
                <div class="balance-stat balance-stat--used">
                    <div class="stat-k">Used</div>
                    <div class="stat-v">{{ $fmtDays($taken) }}</div>
                </div>
                <div class="balance-stat balance-stat--alloc">
                    <div class="stat-k">Allocated</div>
                    <div class="stat-v">{{ $fmtDays($allocated) }}</div>
                </div>
            </div>
        </div>

        @if($isTypeDisabled)
            <div class="balance-alert balance-alert--danger">
                Applications for {{ $selectedType }} leave are disabled because your annual balance is exhausted.
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
            <div class="balance-panel balance-usage">
                <div class="balance-usage-label">
                    <span>Usage in {{ $year }}</span>
                    <span>{{ $usagePct }}% · {{ $fmtDays($taken) }} / {{ $fmtDays($allocated) }} days</span>
                </div>
                <div class="balance-usage-track">
                    <div class="balance-usage-fill {{ $meterClass }}" style="width: {{ max($usagePct, 2) }}%;"></div>
                </div>
            </div>
        @endif

        <div class="balance-panel">
            <div class="balance-facts">
                <div class="balance-fact">
                    Half days
                    <strong>{{ !empty($focusedBalance['allow_half_days']) ? 'Allowed' : 'Not allowed' }}</strong>
                </div>
                <div class="balance-fact">
                    Documents
                    <strong>{{ !empty($focusedBalance['requires_proof']) ? 'Required' : 'Not required' }}</strong>
                </div>
                <div class="balance-fact">
                    Pending requests
                    <strong>{{ $requestCounts['pending'] ?? 0 }} ({{ $fmtDays($pendingDaysTotal) }} days)</strong>
                </div>
                <div class="balance-fact">
                    Approved requests
                    <strong>{{ $requestCounts['approved'] ?? 0 }}</strong>
                </div>
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
                                    <td><span class="balance-badge balance-badge--pending">Pending</span></td>
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
                                <td><span class="balance-badge balance-badge--approved">Approved</span></td>
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
            <a href="{{ route('leave.balance', ['year' => $year]) }}">← Choose another leave type</a>
        </div>
    @endif
</div>
@endsection
