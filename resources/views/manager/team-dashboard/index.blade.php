@extends('layouts.manager')

@section('title', 'Manager Dashboard')

@section('content')
@include('partials.dashboard-status-card-styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    html body { font-family: 'Poppins', 'Segoe UI', sans-serif; }

    .mgr-page-header { width: 100%; align-items: center; margin-bottom: 22px; padding: 0 30px; box-sizing: border-box; }
    .mgr-page-header .mgr-page-header__main h1 {
        font-family: 'Century Gothic', 'Poppins', sans-serif;
        font-size: 38px;
        color: #253141;
    }
    .mgr-page-header .mgr-scope-switch {
        background: #d9253f;
        border-color: #d9253f;
        border-radius: 14px;
        padding: 12px 28px;
        font-size: 12px;
    }
    .manager-container {
        font-family: 'Poppins', 'Segoe UI', sans-serif;
        width: 100%;
        max-width: 1200px;
        box-sizing: border-box;
        padding: 12px 12px 14px;
        background: white;
        border: 1px solid #d3d8de;
        border-radius: 14px;
        box-shadow: 0 0 14px rgba(0, 0, 0, 0.16);
        margin: 0 auto 56px;
    }

    h1 {
        font-weight: 700;
        font-size: 36px;
        color: #253141;
        margin-bottom: 24px;
        padding: 0;
        border: none;
        letter-spacing: 0.04em;
    }

    /* Coloured stat cards */
    .cards {
        display: flex;
        flex-wrap: nowrap;
        gap: 12px;
        margin-bottom: 32px;
        width: 100%;
        overflow-x: auto;
        padding-bottom: 8px;
    }
   .manager-container .cards .card {
        flex: 1 1 0;
        min-width: 0;
        height: 92px;
        min-height: 92px;
        border: none !important;
        border-radius: 20px;
        box-shadow: none !important;
        padding: 10px 6px;
        text-align: center;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        justify-content: center;
        color: #fff;
        box-sizing: border-box;
        opacity: 1;
    }
    .manager-container .cards .card:hover { 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25); 
    }
    .manager-container .cards .card.card-all      { background: #4fe6c5 !important; }
    .manager-container .cards .card.card-pending  { background: #f2b96b !important; }
    .manager-container .cards .card.card-overdue  { background: #ab8cf5 !important; }
    .manager-container .cards .card.card-approved { background: #a03d85 !important; }
    .manager-container .cards .card.card-rejected { background: #94c4c8 !important; }
    .manager-container .cards .card.card-onleave  { background: #b9d84a !important; }
    .card .card-title svg, .card .card-title .icon { display: none; }
    .card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    }
    .card .card-title svg,
    .card .card-title .icon {
        display: none;
    }
    .card .card-title {
        justify-content: center;
        text-align: center;
    }
    .card-title {
        font-size: 0.85rem;
        font-weight: 400;
        margin: 2px 0 6px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
    }
    .card-value {
        font-weight: bold;
        font-size: 1.4rem;
        margin-top: 0;
    }
    .charts-container {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 10px;
        align-items: stretch;
    }
    .chart-card {
        background: white;
        padding: 12px 12px;
        border-radius: 14px;
        box-shadow: 0 3px 9px rgba(0, 0, 0, 0.18);
        border: 1px solid #d6dbe0;
        color: #222;
        display: flex;
        flex-direction: column;
        min-height: 236px;
    }
    .chart-card h3 {
        margin: 0 0 10px 0;
        font-weight: 700;
        font-size: 0.95rem;
        color: #253141;
    }
    .chart-body {
        position: relative;
        width: 100%;
        overflow: visible;
    }
    
    .manager-container .chart-body--ring { height: 214px; }
    .manager-container .chart-body--ring canvas { max-height: 214px; }
    .manager-container .chart-body--plot { height: 186px; }
    .manager-container .chart-html-legend li,
    .manager-container .chart-html-legend span { font-size: 0.68rem !important; font-weight: 400 !important; }

    .manager-container .dashboard-period-filter__form {
        background: #d9e5e4;
        border: none;
        border-radius: 14px;
        box-shadow: none;
    }
    .manager-container .dashboard-period-filter__label { font-size: 0.72rem; font-weight: 400; color: #4b5563; }
    .manager-container .dashboard-period-filter .filter-select__trigger {
        border: none;
        border-radius: 14px;
        font-size: 0.8rem;
        font-weight: 700;
        box-shadow: none;
    }
    .manager-container .dashboard-period-filter .filter-select__option { text-align: center; font-weight: 700; }
    .manager-container .dashboard-period-filter .filter-select__option.is-selected { background: #a2b224; color: #1f2937; }

    @media (max-width: 1200px) {
        .charts-container {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 600px) {
        .charts-container {
            grid-template-columns: 1fr;
        }
    }
    svg.icon {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
    }
    @media (max-width: 992px) {
        .card { min-width: 210px; flex-basis: 210px; }
    }
    @media (max-width: 600px) {
        .cards { gap: 12px; }
        .card { max-width: none; width: 100%; }
    }
</style>

<div class="mgr-page-header">
    <div class="mgr-page-header__main">
        <h1>Manager Dashboard</h1>
    </div>
    @include('partials.manager-scope-switch', [
        'scope' => 'team',
        'personalUrl' => route('manager.personal'),
        'personalLabel' => 'My Leave Dashboard',
    ])
</div>

<div class="manager-container">
@include('partials.dashboard-period-filter', [
    'monthFilter' => $monthFilter ?? 'this_year',
    'employeeFilter' => $employeeFilter ?? null,
    'employees' => $employees ?? collect(),
])
@php
    $cardFilters = $dashboardFilters ?? ['month' => $monthFilter ?? 'this_year'];
@endphp
    
    <div class="cards">
    <a href="{{ route('manager.requests.index', array_merge(['status' => 'all'], $cardFilters)) }}" class="card card-all">
        <div class="card-title">All Requests</div>
        <div class="card-value">{{ $allRequestsCount }}</div>
    </a>

    <a href="{{ route('manager.requests.index', array_merge(['status' => 'pending'], $cardFilters)) }}" class="card card-pending">
        <div class="card-title">Pending Requests</div>
        <div class="card-value">{{ $pendingRequestsCount }}</div>
    </a>

    <a href="{{ route('manager.requests.index', array_merge(['status' => 'overdue'], $cardFilters)) }}" class="card card-overdue">
        <div class="card-title">Overdue Requests</div>
        <div class="card-value">{{ $overduePendingCount ?? 0 }}</div>
    </a>

    <a href="{{ route('manager.requests.index', array_merge(['status' => 'approved'], $cardFilters)) }}" class="card card-approved">
        <div class="card-title">Approved Requests</div>
        <div class="card-value">{{ $approvedRequestsCount }}</div>
    </a>

    <a href="{{ route('manager.requests.index', array_merge(['status' => 'rejected'], $cardFilters)) }}" class="card card-rejected">
        <div class="card-title">Rejected Requests</div>
        <div class="card-value">{{ $rejectedRequestsCount }}</div>
    </a>

    <a href="{{ route('manager.calendar') }}" class="card card-onleave" aria-label="Employees on leave today">
        <div class="card-title">On Leave Today</div>
        <div class="card-value">{{ $onLeaveTodayCount ?? 0 }}</div>
    </a>
</div>

    <div class="charts-container">
        <div class="chart-card">
            <h3>Leave Requests by Type</h3>
            <div class="chart-body chart-body--ring">
                <canvas id="leaveDoughnut"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3>Leave Requests by Status</h3>
            <div class="chart-body chart-body--ring">
                <canvas id="statusPie"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3>Monthly Applications Submitted ({{ \App\Support\LeaveYear::label((int) ($chartYear ?? now()->year)) }})</h3>
            <div class="chart-body chart-body--plot">
                <canvas id="monthlyLine"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3>Requests by Department</h3>
            @if(empty($departmentLabels))
                <p style="margin:0; color:#64748b; font-size:14px;">No leave requests by department for this period.</p>
            @else
                <div class="chart-body chart-body--plot">
                    <canvas id="departmentBar"></canvas>
                </div>
            @endif
        </div>
        <div class="chart-card">
            <h3>Monthly Requests by Status ({{ \App\Support\LeaveYear::label((int) ($chartYear ?? now()->year)) }})</h3>
            <div class="chart-body chart-body--plot">
                <canvas id="monthlyStatusStackedBar"></canvas>
            </div>
            <ul class="chart-html-legend" id="monthlyStatusStackedBarLegend" aria-label="Monthly status legend"></ul>
        </div>
        <div class="chart-card">
            <h3>Paid vs Unpaid Leave</h3>
            <div class="chart-body chart-body--ring">
                <canvas id="payStatusPolar"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@include('partials.dashboard-charts-styles')
@include('partials.dashboard-chart-vars')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const hslToHex = (h, s, l) => {
        s /= 100;
        l /= 100;
        const k = n => (n + h / 30) % 12;
        const a = s * Math.min(l, 1 - l);
        const f = n => l - a * Math.max(-1, Math.min(k(n) - 3, Math.min(9 - k(n), 1)));
        const toHex = x => Math.round(255 * x).toString(16).padStart(2, '0');
        return `#${toHex(f(0))}${toHex(f(8))}${toHex(f(4))}`;
    };
    const hexToRgba = (hex, alpha) => {
        const raw = String(hex || '').replace('#', '');
        const r = parseInt(raw.slice(0, 2), 16);
        const g = parseInt(raw.slice(2, 4), 16);
        const b = parseInt(raw.slice(4, 6), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    };

    const normalizeKey = (value) => {
        const v = String(value || '').trim().toLowerCase();
        if (!v) return '';
        return v.endsWith(' leave') ? v.slice(0, -5).trim() : v;
    };
    const fixedTypeColors = @json(config('leave_type_colors.colors'));
    const fallbackColors = @json(config('leave_type_colors.fallback_palette'));

    // Local palette for non-leave-type charts (status/monthly/etc.)
    // Keep these bright and distinct (not tied to leave-type colors).
    const chartPalette = {
        blue: '#2563eb',
        teal: '#06b6d4',
        green: '#22c55e',
        amber: '#f59e0b',
        red: '#ef4444',
        pink: '#ec4899',
        purple: '#a855f7',
        indigo: '#6366f1',
        slate: '#64748b',
        coral: '#fb7185',
        orange: '#f97316',
        magenta: '#d946ef',
    };
    const hashString = (str) => {
        let h = 0;
        for (let i = 0; i < str.length; i += 1) {
            h = ((h << 5) - h) + str.charCodeAt(i);
            h |= 0;
        }
        return Math.abs(h);
    };
    const hashToColor = (key) => fallbackColors[hashString(key || 'default') % fallbackColors.length];
    const getColorForType = (type) => {
        const key = normalizeKey(type);
        return fixedTypeColors[key] || hashToColor(key);
    };

    window.applyBoldChartDefaults();
    window.finalizeChartDefaults();

    window.chartBoldFontSpec = { family: "'Poppins', sans-serif", size: 9, weight: '400', style: 'normal' };
    window.chartAxisTicksBold = { color: window.chartTextColor, font: window.chartBoldFontSpec };

    const protoColors = ['#4a7ff7', '#b08cf5', '#f5b96b', '#ffdc50', '#4fe6c5', '#94c4c8', '#a03d85', '#b9d84a'];
    const blue = '#4a7ff7';

    const sliceLabels = {
        id: 'sliceLabels',
        afterDatasetsDraw: function (chart) {
            const ctx = chart.ctx;
            const values = chart.data.datasets[0].data;
            const names = chart.data.labels;
            const slices = chart.getDatasetMeta(0).data;

            let total = 0;
            for (let i = 0; i < values.length; i++) {
                total = total + Number(values[i]);
            }
            if (total === 0) {
                return;
            }

            ctx.save();
            ctx.font = '9px Poppins, sans-serif';
            ctx.fillStyle = '#253141';
            ctx.textBaseline = 'middle';

            for (let i = 0; i < slices.length; i++) {
                if (Number(values[i]) === 0) {
                    continue;
                }
                const slice = slices[i];
                const middleAngle = (slice.startAngle + slice.endAngle) / 2;
                const x = slice.x + Math.cos(middleAngle) * (slice.outerRadius + 8);
                const y = slice.y + Math.sin(middleAngle) * (slice.outerRadius + 20);

                if (Math.cos(middleAngle) > 0.25) {
                    ctx.textAlign = 'left';
                } else if (Math.cos(middleAngle) < -0.25) {
                    ctx.textAlign = 'right';
                } else {
                    ctx.textAlign = 'center';
                }

                let lines = [names[i]];
                if (names[i].length > 11 && names[i].indexOf(' ') > -1) {
                    lines = names[i].split(' ');
                }
                lines.push((Math.round(values[i] / total * 1000) / 10) + '%');

                for (let n = 0; n < lines.length; n++) {
                    ctx.fillText(lines[n], x, y + (n - (lines.length - 1) / 2) * 11);
                }
            }
            ctx.restore();
        }
    };
    const roomForLabels = { padding: { top: 34, bottom: 34, left: 56, right: 56 } };

    const leaveTypeLabels = @json($leaveTypeCounts->sortDesc()->keys()->toArray());
    const leaveTypeData = @json($leaveTypeCounts->sortDesc()->values()->toArray());
    const leaveTypePct = @json($leaveTypePercentages ?? []);
    const leaveTypeColors = leaveTypeLabels.map((t, i) => protoColors[i % protoColors.length]);

    new Chart(document.getElementById('leaveDoughnut'), {
        type: 'doughnut',
        data: {
            labels: leaveTypeLabels,
            datasets: [{
                data: leaveTypeData,
                backgroundColor: leaveTypeColors,
                borderWidth: 0
            }]
        },
        plugins: [sliceLabels],
        options: mergeChartOptions({
            cutout: '50%',
            layout: roomForLabels,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const pct = (leaveTypePct[ctx.label] ?? 0).toFixed(1);
                            return `${ctx.label}: ${ctx.parsed} (${pct}%)`;
                        }
                    }
                }
            }
        })
    });

    const deptPieLabels = @json($departmentLabels);
    const deptPieData = @json($departmentData);
    const deptPieColors = deptPieLabels.map((name, i) => protoColors[i % protoColors.length]);

    new Chart(document.getElementById('payStatusPolar'), {
        type: 'pie',
        data: {
            labels: deptPieLabels,
            datasets: [{
                data: deptPieData,
                backgroundColor: deptPieColors,
                borderWidth: 0
            }]
        },
        plugins: [sliceLabels],
        options: mergeChartOptions({
            layout: roomForLabels,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.label}: ${ctx.raw} requests`
                    }
                }
            }
        })
    });

    const statusPct = @json($statusPercentages ?? []);

    let statusList = [
        { label: 'Pending',  value: {{ $statusCounts['Pending'] ?? 0 }} },
        { label: 'Overdue',  value: {{ $statusCounts['Overdue'] ?? 0 }} },
        { label: 'Rejected', value: {{ $statusCounts['Rejected'] ?? 0 }} },
        { label: 'Approved', value: {{ $statusCounts['Approved'] ?? 0 }} }
    ];
    statusList.sort(function (a, b) {
        return b.value - a.value;
    });
    const statusPieLabels = statusList.map(function (item) { return item.label; });
    const statusPieData = statusList.map(function (item) { return item.value; });
    const statusPieColors = statusPieLabels.map((label, i) => protoColors[i % protoColors.length]);

    new Chart(document.getElementById('statusPie'), {
        type: 'pie',
        data: {
            labels: statusPieLabels,
            datasets: [{
                data: statusPieData,
                backgroundColor: statusPieColors,
                borderWidth: 0
            }]
        },
        plugins: [sliceLabels],
        options: mergeChartOptions({
            layout: roomForLabels,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const pct = (statusPct[ctx.label] ?? 0).toFixed(1);
                            return `${ctx.label}: ${ctx.parsed} (${pct}%)`;
                        }
                    }
                }
            }
        })
    });

    const allMonthLabels = @json($barLabels ?? \App\Support\LeaveYear::barLabels());
    const padMonthlySeries = (values) => {
        const series = Array.isArray(values) ? [...values] : [];
        while (series.length < 12) {
            series.push(0);
        }
        return series.slice(0, 12);
    };
    const monthlyLabels = allMonthLabels;
    const monthlyTotals = padMonthlySeries(@json($barData));
    const monthlyApproved = padMonthlySeries(@json($monthlyApprovedData));
    const monthlyPending = padMonthlySeries(@json($monthlyPendingData));
    const monthlyOverdue = padMonthlySeries(@json($monthlyOverdueData));
    const monthlyRejected = padMonthlySeries(@json($monthlyRejectedData));
    const monthlyColor = blue;
    const monthlyLineChart = new Chart(document.getElementById('monthlyLine'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Total Requests',
                data: monthlyTotals,
                backgroundColor: hexToRgba(monthlyColor, 0.2),
                borderColor: monthlyColor,
                borderWidth: 1.5,
                tension: 0.4,
                fill: false,
                pointRadius: 2,
                pointHoverRadius: 4
            }]
        },
        options: mergeChartOptions({
            layout: window.getMonthChartLayout(),
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: window.getMonthAxisTicks() },
                y: { beginAtZero: true, grid: { color: '#d9d9d9' }, border: { display: false }, ticks: { precision: 0 } }
            }
        })
    });
    window.registerMonthAxisChart(monthlyLineChart);

    const departmentBarCanvas = document.getElementById('departmentBar');
    if (departmentBarCanvas) {
        const deptLabels = @json($departmentLabels);
        const deptData = @json($departmentData);
        new Chart(departmentBarCanvas, {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'Requests',
                    data: deptData,
                    backgroundColor: blue,
                    borderWidth: 0,
                    borderRadius: 5,
                    maxBarThickness: 48
                }]
            },
            options: mergeChartOptions({
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: window.chartAxisTicksBold },
                    y: { beginAtZero: true, grid: { color: '#d9d9d9' }, border: { display: false }, ticks: { precision: 0 } }
                }
            }),
        });
    }

    const monthlyStatusDatasets = [
                {
                    label: 'Approved',
                    data: monthlyApproved,
                    backgroundColor: '#a03d85',
                    borderWidth: 1,
                    borderRadius: 4,
                    stack: 'status'
                },
                {
                    label: 'Pending',
                    data: monthlyPending,
                    backgroundColor: '#f2b96b',
                    borderWidth: 0,
                    borderRadius: 3,
                    stack: 'status'
                },
                {
                    label: 'Overdue',
                    data: monthlyOverdue,
                    backgroundColor: '#ab8cf5',
                    borderWidth: 0,
                    borderRadius: 3,
                    stack: 'status'
                },
                {
                    label: 'Rejected',
                    data: monthlyRejected,
                    backgroundColor: '#94c4c8',
                    borderWidth: 0,
                    borderRadius: 3,
                    stack: 'status'
                }
            ];

    const monthlyStatusChart = new Chart(document.getElementById('monthlyStatusStackedBar'), {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Total Requests',
                data: monthlyTotals,
                backgroundColor: blue,
                borderWidth: 0,
                borderRadius: 4,
                maxBarThickness: 40
            }]
        },
        options: mergeChartOptions({
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        afterBody: function (items) {
                            const i = items[0].dataIndex;
                            return [
                                'Approved: ' + monthlyApproved[i],
                                'Pending: ' + monthlyPending[i],
                                'Overdue: ' + monthlyOverdue[i],
                                'Rejected: ' + monthlyRejected[i]
                            ];
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: window.getMonthAxisTicks() },
                y: { beginAtZero: true, grid: { color: '#d9d9d9' }, border: { display: false }, ticks: { precision: 0 } }
            }
        })
    });
    window.registerMonthAxisChart(monthlyStatusChart);

});
</script>
@endsection