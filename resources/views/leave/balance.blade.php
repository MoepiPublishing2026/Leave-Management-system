@extends('layouts.app')

@section('title', !empty($selectedType) ? $selectedType . ' Leave Balance' : 'Leave Balance')

@section('content')
@php
    $fmtDays = function ($n) {
        return rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
    };
@endphp
<style>
    .admin-container {
        font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
        max-width: 1200px;
        margin: 20px auto 40px;
        background: white;
        border-radius: 20px;
        padding: 20px 30px 30px;
        box-shadow: 0 6px 20px rgba(163, 71, 71, 0.08);
    }
    .admin-container h1 {
        font-weight: 700;
        font-size: 36px;
        color: #2c3e50;
        border-left: 6px solid #f00e65;
        padding-left: 15px;
        margin: 0 0 8px;
        letter-spacing: 0.04em;
    }
    .page-lead {
        color: #64748b;
        font-weight: 700;
        margin: 0 0 20px;
        padding-left: 21px;
        font-size: 1rem;
    }
    .toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    .toolbar label { font-weight: 800; color: #334155; margin: 0; }
    .toolbar input[type="number"] {
        width: 100px;
        border: 2px solid #f00e65;
        border-radius: 6px;
        padding: 8px 12px;
        font-weight: 700;
    }
    .toolbar .spacer { flex: 1; min-width: 8px; }
    .btn-action {
        display: inline-flex;
        align-items: center;
        padding: 9px 16px;
        border-radius: 6px;
        font-weight: 800;
        font-size: 0.95rem;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }
    .btn-apply {
        background: #b2cd16;
        color: #0a1347;
        border: 1px solid #91bf14;
    }
    .btn-apply:hover { background: #9eb812; }
    .btn-muted {
        background: #f8f9fa;
        color: #0f172a;
        border: 1px solid #cbd5e1;
    }
    .btn-muted:hover { background: #e2e8f0; }
    .btn-update {
        background: #0d6efd;
        color: #fff;
    }

    /* Dashboard-matched balance cards */
    .balance-hero {
        background: rgb(252, 213, 234);
        border-radius: 16px;
        padding: 22px 26px;
        margin-bottom: 20px;
        border-left: 5px solid #f00e65;
        border-top: 1px solid #f00e65;
        border-right: 1px solid #f00e65;
        border-bottom: 1px solid #f00e65;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .balance-hero .hero-main .label {
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #680523;
        letter-spacing: 0.06em;
    }
    .balance-hero .hero-main .big {
        font-size: 3rem;
        font-weight: 800;
        color: #850621;
        line-height: 1.1;
        margin-top: 4px;
    }
    .balance-hero .hero-main .caption {
        font-weight: 700;
        color: #374151;
        margin-top: 6px;
    }

    .stat-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 22px;
    }
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 14px 18px;
        min-width: 140px;
        flex: 1 1 140px;
        box-shadow: 0 6px 16px rgba(8, 8, 8, 0.08);
        border: 1px solid #e5e7eb;
        border-left: 5px solid #f00e65;
    }
    .stat-card.used { border-left-color: #dab00c; }
    .stat-card.alloc { border-left-color: #53e21a; }
    .stat-card .k {
        font-size: 0.8rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
    }
    .stat-card .v {
        font-size: 1.75rem;
        font-weight: 800;
        color: #2c3e50;
        margin-top: 4px;
    }

    .usage-wrap { margin-bottom: 22px; }
    .usage-wrap .usage-label {
        display: flex;
        justify-content: space-between;
        font-weight: 800;
        color: #475569;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    .usage-track {
        height: 10px;
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
    }
    .usage-fill {
        height: 100%;
        background: #f00e65;
        border-radius: 999px;
    }
    .usage-fill.warn { background: #dab00c; }
    .usage-fill.danger { background: #eb0e8f; }

    .facts {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
    }
    .fact {
        flex: 1 1 200px;
        padding: 12px 14px;
        border-radius: 10px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        font-weight: 700;
        color: #374151;
    }
    .fact strong { color: #0a1347; }

    .alert-box {
        padding: 12px 14px;
        border-radius: 10px;
        font-weight: 700;
        margin-bottom: 16px;
        border-left: 5px solid;
        line-height: 1.45;
    }
    .alert-warn { background: #fffbeb; border-color: #dab00c; color: #92400e; }
    .alert-info { background: #eff6ff; border-color: #0d6efd; color: #1e40af; }
    .alert-danger { background: #fff1f2; border-color: #eb0e8f; color: #881f54; }

    .picker-list {
        display: flex;
        flex-wrap: wrap;
        gap: 18px 24px;
        margin-bottom: 20px;
        padding: 0;
        list-style: none;
    }
    .picker-list a {
        background-color: rgb(252, 213, 234);
        border-radius: 16px;
        padding: 18px 26px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-grow: 1;
        min-width: 230px;
        max-width: 320px;
        font-size: 1.15rem;
        font-weight: 600;
        color: #050505;
        box-shadow: 0 3px 7px rgba(13, 59, 53, 0.12);
        text-decoration: none;
        border-left: 5px solid #f00e65;
        border-top: 1px solid #f00e65;
        border-right: 1px solid #f00e65;
        border-bottom: 1px solid #f00e65;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .picker-list a:hover:not(.disabled) {
        box-shadow: 0 8px 20px rgba(13, 59, 53, 0.25);
        transform: translateY(-2px);
    }
    .picker-list a.disabled {
        opacity: 0.55;
        pointer-events: none;
        filter: grayscale(0.2);
    }
    .picker-list .leave-days {
        font-weight: 700;
        color: #850621;
        padding-left: 12px;
        white-space: nowrap;
    }

    .section-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: #2c3e50;
        margin: 24px 0 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e5e7eb;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 1rem;
        margin-bottom: 8px;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 12px 15px;
        text-align: left;
        font-weight: 700;
    }
    th {
        background-color: #f4f4f4;
        color: #2c3e50;
    }
    tr:hover { background-color: #f9f9f9; }

    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 999px;
        font-weight: 800;
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    .badge-approved { background: #bbf7d0; color: #065f46; border: 1px solid #22c55e; }
    .badge-pending { background: #fde68a; color: #92400e; border: 1px solid #f59e0b; }

    .empty-cell {
        text-align: center;
        color: #6b7280;
        font-style: italic;
        padding: 24px !important;
    }

    .back-row { margin-top: 20px; }
    .back-row a {
        font-weight: 800;
        color: #f00e65;
        text-decoration: none;
    }
    .back-row a:hover { text-decoration: underline; }
</style>

<div class="admin-container">
    @if(empty($selectedType) || empty($focusedBalance))
        <h1>Leave Balance</h1>
        <p class="page-lead">Choose a leave type to view your {{ $year }} balance and history.</p>

        <form class="toolbar" method="GET" action="{{ route('leave.balance') }}">
            <label for="year">Year</label>
            <input id="year" name="year" type="number" min="2000" max="2100" value="{{ $year }}" />
            <button type="submit" class="btn-action btn-update">Update</button>
            <span class="spacer"></span>
            <a href="{{ route('dashboard') }}" class="btn-action btn-muted">Dashboard</a>
        </form>

        <ul class="picker-list">
            @foreach($leaveTypeOptions as $option)
                <li>
                    <a href="{{ route('leave.balance', ['year' => $year, 'type' => $option['type']]) }}"
                       class="{{ $option['disabled'] ? 'disabled' : '' }}">
                        <span>{{ $option['type'] }} Leave</span>
                        <span class="leave-days">{{ $fmtDays($option['remaining']) }} days left</span>
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
            $meterClass = $usagePct >= 90 ? 'danger' : ($usagePct >= 70 ? 'warn' : '');
        @endphp

        <h1>{{ $selectedType }} Leave — {{ $year }}</h1>
        <p class="page-lead">Your balance and activity for this leave type only.</p>

        <form class="toolbar" method="GET" action="{{ route('leave.balance') }}">
            <input type="hidden" name="type" value="{{ $selectedType }}" />
            <label for="year">Year</label>
            <input id="year" name="year" type="number" min="2000" max="2100" value="{{ $year }}" />
            <button type="submit" class="btn-action btn-update">Update</button>
            <span class="spacer"></span>
            @if(!$isTypeDisabled)
                <a href="{{ route('apply', ['leave_type' => $selectedType]) }}" class="btn-action btn-apply">+ Apply for {{ $selectedType }}</a>
            @endif
            <a href="{{ route('leave.myRequests') }}" class="btn-action btn-muted">My Requests</a>
            <a href="{{ route('leave.balance', ['year' => $year]) }}" class="btn-action btn-muted">Change type</a>
        </form>

        <div class="balance-hero">
            <div class="hero-main">
                <div class="label">{{ $selectedType }} remaining</div>
                <div class="big">{{ $fmtDays($remaining) }}</div>
                <div class="caption">days available in {{ $year }}</div>
            </div>
            <div class="stat-cards" style="margin:0; flex:1 1 280px;">
                <div class="stat-card used">
                    <div class="k">Used</div>
                    <div class="v">{{ $fmtDays($taken) }}</div>
                </div>
                <div class="stat-card alloc">
                    <div class="k">Allocated</div>
                    <div class="v">{{ $fmtDays($allocated) }}</div>
                </div>
            </div>
        </div>

        @if($isTypeDisabled)
            <div class="alert-box alert-danger">
                Applications for {{ $selectedType }} leave are disabled because your annual balance is exhausted.
            </div>
        @elseif($countsAgainstAnnual && $annualRemaining <= 5)
            <div class="alert-box alert-warn">
                This leave counts against your annual pool. You have <strong>{{ $fmtDays($annualRemaining) }}</strong> annual day(s) left overall.
            </div>
        @endif

        @if($pendingDaysTotal > 0)
            <div class="alert-box alert-info">
                <strong>{{ $fmtDays($pendingDaysTotal) }}</strong> day(s) pending approval — not deducted until approved.
            </div>
        @endif

        @if($allocated > 0)
            <div class="usage-wrap">
                <div class="usage-label">
                    <span>Usage in {{ $year }}</span>
                    <span>{{ $usagePct }}% · {{ $fmtDays($taken) }} / {{ $fmtDays($allocated) }} days</span>
                </div>
                <div class="usage-track">
                    <div class="usage-fill {{ $meterClass }}" style="width: {{ max($usagePct, 2) }}%;"></div>
                </div>
            </div>
        @endif

        <div class="facts">
            <div class="fact">Half days: <strong>{{ !empty($focusedBalance['allow_half_days']) ? 'Allowed' : 'Not allowed' }}</strong></div>
            <div class="fact">Documents: <strong>{{ !empty($focusedBalance['requires_proof']) ? 'Required' : 'Not required' }}</strong></div>
            <div class="fact">Pending: <strong>{{ $requestCounts['pending'] ?? 0 }}</strong> ({{ $fmtDays($pendingDaysTotal) }} days)</div>
            <div class="fact">Approved requests: <strong>{{ $requestCounts['approved'] ?? 0 }}</strong></div>
        </div>

        @if($upcomingLeaves->isNotEmpty())
            <h3 class="section-title">Upcoming approved leave</h3>
            <table>
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
        @endif

        @if($pendingLeavesDisplay->isNotEmpty())
            <h3 class="section-title">Pending leave</h3>
            <table>
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
                            <td><span class="badge badge-pending">Pending</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <h3 class="section-title">Approved leave taken ({{ $year }})</h3>
        <table>
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
                        <td><span class="badge badge-approved">Approved</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-cell">No approved {{ $selectedType }} leave for {{ $year }}.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="back-row">
            <a href="{{ route('leave.balance', ['year' => $year]) }}">← Choose another leave type</a>
        </div>
    @endif
</div>
@endsection
