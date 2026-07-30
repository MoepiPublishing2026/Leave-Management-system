@extends('layouts.admin')

@section('content')
<style>
    /* Scope page styles so navbar/layout stays consistent */
    .admin-analytics-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background-color: white;
        color: #1e293b;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 4px 32px;
    }
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e4e7ec;
    }
    .page-header h2 {
        font-weight: 700;
        font-size: 40px;
        color: #2c3e50;
        margin: 0;
        letter-spacing: 0.04em;
    }
    .filter-form .filter-clear {
        margin-left: 0;
        padding: 10px 16px;
        border-radius: 10px;
        border: 1px solid #91bf14;
        background: #b2cd16;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.92rem;
        color: #0a1347;
        transition: background-color 0.15s ease, color 0.15s ease;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .filter-form .filter-clear:hover {
        background: #9eb812;
        color: #0a1347;
    }
    .metrics-row {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        margin-bottom: 40px;
        align-items: stretch;
    }
    .metrics-col {
        flex: 1 1 calc(25% - 24px);
        min-width: 240px;
        display: flex;
    }
    .metrics-col > .card {
        flex: 1;
        width: 100%;
    }
    .card {
        background: #fff;
        border-radius: 15px;
          box-shadow: 0 0 10px #a7bd8dff;
        padding: 20px;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 20px 40px rgba(99, 99, 99, 0.18);
        transform: translateY(-4px);
    }
    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .card-icon {
        font-size: 24px;
        padding: 10px;
        border-radius: 12px;
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.12);
    }
    .bg-blue-100 { background-color: #b3d7e6ff; }
    .text-blue-600 { color: #2563eb; }
    .bg-yellow-100 { background-color: #ebdfabff; }
    .text-yellow-600 { color: #d97706; }
    .bg-green-100 { background-color: #9acaaaff; }
    .text-green-600 { color: #22c55e; }
    .bg-pink-100 { background-color: #eca6acff; }
    .text-pink-600 { color: #360310ff; }
    .card h5 {
        font-weight: 600;
        color: #334155;
        font-size: 1.1rem;
        margin: 0 0 0.3rem;
    }
    .card h3 {
        font-weight: 700;
        font-size: 2.2rem;
        color: #0f172a;
        margin: 0 0 0.4rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .card small,
    .card .card-metric-note {
        font-size: 0.9rem;
        font-weight: 600;
        min-height: 1.35rem;
        margin-top: auto;
        display: block;
    }
    .text-success { color: #22c55e !important; }
    .text-warning { color: #f59e0b !important; }
    .text-primary { color: #2563eb !important; }
    .text-muted { color: #64748b !important; }
    .stats-row {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        margin-bottom: 40px;
        align-items: stretch;
    }
    .chart-card {
        flex: 1 1 calc(50% - 24px);
        min-width: 360px;
        display: flex;
    }
    .table-card {
        flex: 1 1 100%;
    }
    .chart-card > .card {
        width: 100%;
        height: 100%;
    }
    .leave-type-list {
        flex: 1;
        overflow-y: auto;
        padding-right: 6px;
    }
    .progress {
        height: 14px;
        border-radius: 14px;
        background-color: #e2e8f0;
        margin-top: 4px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        width: 100%;
    }
    .progress-bar {
        border-radius: 14px;
        transition: width 0.4s ease;
        height: 100%;
    }
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
    }
    th {
        font-weight: 600;
        color: #475569;
        font-size: 1rem;
        padding-bottom: 12px;
        text-align: left;
        border-bottom: 2px solid #e2e8f0;
    }
    td {
        background: #f1f5f9;
        padding: 12px 15px;
        border-radius: 12px;
        font-size: 0.95rem;
        color: #334155;
        vertical-align: middle;
    }
    h2 {
        font-weight: 700;
        font-size: 2.4rem;
        color: #2c3e50;
        margin: 0 0 20px 0;
        letter-spacing: 0.04em;
    }
    tr:hover td {
        background-color: #e0e7ff;
    }
    .leave-type-entry {
        display: flex;
        flex-direction: column;
        margin-bottom: 15px;
    }
    .leave-type-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 600;
        font-size: 1rem;
    }
    .leave-type-name {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .leave-type-icon {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: inline-block;
    }
    table.table tbody tr {
        background-color: #f9fafb;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: background-color 0.3s, box-shadow 0.3s;
    }
    table.table tbody tr:hover {
        background-color: #e0e7ff;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.15);
    }
    table.table thead tr th {
        border-bottom: none !important;
    }
    table.table tbody tr td {
        border: none !important;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .page-header {
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 10px;
        }
        .page-header h2 {
            font-size: 1.6rem;
        }
        .filter-form {
            width: 100%;
        }
        .filter-form .filter-clear {
            width: 100%;
            text-align: center;
        }

        .metrics-row {
            gap: 12px;
        }
        .metrics-col {
            flex: 1 1 100%;
            min-width: 0;
        }

        .stats-row {
            gap: 12px;
            min-width: 0;
        }
        .table-card {
            min-width: 0;
            flex: 1 1 100%;
        }
        .chart-card {
            flex: 1 1 100%;
            min-width: 0;
        }
        .leave-type-list {
            max-height: none;
            overflow: visible;
        }

        /* Flex children need min-width:0 or horizontal scroll never engages */
        .table-card > .card {
            min-width: 0;
        }
        .table-scroll-x {
            width: 100%;
            min-width: 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 8px;
            padding-left: 16px;
            padding-right: 16px;
            margin-left: -16px;
            margin-right: -16px;
            scrollbar-width: thin;
            overscroll-behavior-x: contain;
        }
        .table-scroll-x th,
        .table-scroll-x td {
            white-space: nowrap;
            padding: 8px 10px;
            font-size: 0.85rem;
        }
        .table-scroll-x th:last-child,
        .table-scroll-x td:last-child {
            min-width: 8.5rem;
        }
    }
</style>

<div class="admin-analytics-page">
<div class="container-fluid">
    <div class="page-header">
        <h2>Analytics & Insights</h2>
        <form method="GET" class="filter-form" id="monthFilterForm">
            <select name="month" class="filter-select" onchange="document.getElementById('monthFilterForm').submit()">
                <option value="this_year" {{ $monthFilter == 'this_year' ? 'selected' : '' }}>This Year</option>
                <option value="this_quarter" {{ $monthFilter == 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                <option value="this_month" {{ $monthFilter == 'this_month' ? 'selected' : '' }}>This Month</option>
                @foreach(($years ?? []) as $year)
                    <option value="{{ $year }}" {{ (string) $monthFilter === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
            <select name="staff" class="filter-select" onchange="document.getElementById('monthFilterForm').submit()">
                <option value="all" {{ (string) ($staff ?? 'all') === 'all' ? 'selected' : '' }}>All Staff</option>
                @foreach(($staffOptions ?? []) as $u)
                    <option value="{{ $u->id }}" {{ (string) ($staff ?? 'all') === (string) $u->id ? 'selected' : '' }}>
                        {{ trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ('User #' . $u->id) }}
                        @if (($u->role ?? '') === 'manager') (Manager) @endif
                    </option>
                @endforeach
            </select>
            <a href="{{ route('admin.analytics', ['month' => 'this_year', 'staff' => 'all']) }}" class="filter-clear">Clear</a>
        </form>
    </div>

    <div class="metrics-row">
        <div class="metrics-col">
            <div class="card">
                <div class="card-header">
                    <h5>Total Leaves</h5>
                    <div class="card-icon bg-blue-100 text-blue-600"><i class="fas fa-calendar-alt"></i></div>
                </div>
                <h3>{{ $totalLeaves }}</h3>
                <small class="text-muted">Requested</small>
            </div>
        </div>
        <div class="metrics-col">
            <div class="card">
                <div class="card-header">
                    <h5>Pending Requests</h5>
                    <div class="card-icon bg-yellow-100 text-yellow-600"><i class="fas fa-hourglass-half"></i></div>
                </div>
                <h3>{{ $pendingRequests }}</h3>
                <small class="text-warning">Awaiting approval</small>
            </div>
        </div>
        <div class="metrics-col">
            <div class="card">
                <div class="card-header">
                    <h5>Active Staff</h5>
                    <div class="card-icon bg-green-100 text-green-600"><i class="fas fa-user-check"></i></div>
                </div>
                <h3>{{ $employeesWhoApplied }}</h3>
                <small class="text-muted">of {{ $totalEmployees }} total</small>
            </div>
        </div>
        <div class="metrics-col">
            <div class="card">
                <div class="card-header">
                    <h5>Approved</h5>
                    <div class="card-icon bg-pink-100 text-pink-600"><i class="fas fa-chart-line"></i></div>
                </div>
                <h3>{{ $approvedCount }}</h3>
                <small class="text-primary">{{ $approvalRate }}% approval rate</small>
            </div>
        </div>
    </div>

    <div class="stats-row">
        <div class="chart-card">
            <div class="card">
                <h5><i class="fas fa-chart-pie me-2 text-primary"></i>Leaves by Type</h5>
                <hr>
                <div class="leave-type-list">
                    @php
                        $normalizeKey = function ($value) {
                            $v = strtolower(trim((string) $value));
                            if ($v === '') return '';
                            if (str_ends_with($v, ' leave')) {
                                $v = trim(substr($v, 0, -5));
                            }
                            return $v;
                        };

                        $palette = config('leave_type_colors.fallback_palette', []);
                        $fixedTypeColors = config('leave_type_colors.colors', []);

                        $hashString = function ($str) {
                            $s = (string) $str;
                            $h = 0;
                            $len = strlen($s);
                            for ($i = 0; $i < $len; $i++) {
                                $h = (($h << 5) - $h) + ord($s[$i]);
                                $h &= 0xFFFFFFFF;
                            }
                            return abs((int) $h);
                        };

                        $colorForType = function ($type) use ($normalizeKey, $fixedTypeColors, $palette, $hashString) {
                            $key = $normalizeKey($type);
                            if ($key !== '' && array_key_exists($key, $fixedTypeColors)) {
                                return $fixedTypeColors[$key];
                            }
                            $idx = ($key === '' ? 0 : ($hashString($key) % max(count($palette), 1)));
                            return $palette[$idx];
                        };
                    @endphp
                    @foreach ($leavesByType as $type)
                        @php
                        $percentage = $typePercentages[$type->leave_type] ?? 0;
                        $color = $colorForType($type->leave_type);
                        @endphp
                        <div class="leave-type-entry">
                            <div class="leave-type-label">
                                <div class="leave-type-name" style="color: {{ $color }};">
                                    <span class="leave-type-icon" style="background-color: {{ $color }}"></span>
                                    {{ ucfirst($type->leave_type) }}
                                </div>
                                <span style="color: {{ $color }};">{{ $type->total }} ({{ $percentage }}%)</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $percentage }}%; background-color: {{ $color }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="chart-card">
            <div class="card">
                <h5><i class="fas fa-chart-pie me-2 text-success"></i>Leaves by Department</h5>
                <hr>
                <div class="chart-body chart-body--ring">
                    <canvas id="designationPieChart"></canvas>
                </div>
                <ul class="chart-html-legend" id="designationPieLegend" aria-label="Department legend"></ul>
            </div>
        </div>
    </div>

    <div class="stats-row">
        <div class="table-card">
            <div class="card">
                <h5><i class="fas fa-trophy me-2 text-warning"></i>Top Leave Takers</h5>
                <hr>
                <div class="table-scroll-x" role="region" aria-label="Top leave takers table">
                <table class="table table-borderless align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Staff Member</th>
                            <th>Total Leaves</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topLeaveTakers as $index => $taker)
                            @php
                                $percentage = $taker->percentage ?? 0;
                                $percentageColor = '#a80d55ff';
                            @endphp
                            <tr style="background-color: #f9fafb; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: background-color 0.3s, box-shadow 0.3s;">
                                <td style="font-weight: 600; color: #64748b;">#{{ $index + 1 }}</td>
                                <td style="font-weight: 600; color: #334155;">
                                    {{ trim(($taker->user->first_name ?? '').' '.($taker->user->last_name ?? '')) ?: 'Unknown' }}
                                    @if (($taker->user->role ?? '') === 'manager') (Manager) @endif
                                </td>
                                <td style="font-weight: 600; color: #334155;">{{ $taker->total }} leaves</td>
                                <td>
                                    <div class="progress" style="height: 12px; border-radius: 12px; margin-bottom: 6px;">
                                        <div class="progress-bar" style="width: {{ $percentage }}%; background-color: {{ $percentageColor }};"></div>
                                    </div>
                                    <small style="font-weight: 700; color: {{ $percentageColor }};">{{ $percentage }}%</small>
                                </td>
                            </tr>
                        @endforeach
                        @if ($topLeaveTakers->isEmpty())
                            <tr><td colspan="4" class="text-center text-muted p-4">No data available</td></tr>
                        @endif
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@include('partials.dashboard-charts-styles')
@include('partials.dashboard-chart-vars')
<script>
    const ctx = document.getElementById('designationPieChart').getContext('2d');
    window.applyBoldChartDefaults();
    window.finalizeChartDefaults();
@include('partials.analytics-designation-chart-script')
</script>
@endsection
