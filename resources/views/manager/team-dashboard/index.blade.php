@extends('layouts.manager')

@section('title', 'Manager Dashboard')

@section('content')
@include('partials.dashboard-status-card-styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    html body { font-family: 'Inter', 'Segoe UI', sans-serif; }

    .mgr-page-header { width: 100%; align-items: center; margin-bottom: 22px; padding: 0 30px; box-sizing: border-box; }
    .mgr-page-header .mgr-page-header__main h1 {
        font-family: 'Century Gothic', 'Poppins', sans-serif;
        font-size: 38px;
        color: #253141;
    }
    .mgr-page-header .mgr-scope-switch {
        background: #d5253a;
        border-color: #d5253a;
        border-radius: 14px;
        padding: 12px 28px;
        font-size: 12px;
    }
    .manager-container {
        font-family: 'Inter', 'Segoe UI', sans-serif;
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
    .manager-container .cards .card.card-all      { background: #4ee5c5 !important; }
    .manager-container .cards .card.card-pending  { background: #f3b86b !important; }
    .manager-container .cards .card.card-overdue  { background: #ae8df5 !important; }
    .manager-container .cards .card.card-approved { background: #a33d86 !important; }
    .manager-container .cards .card.card-rejected { background: #8fc4c7 !important; }
    .manager-container .cards .card.card-onleave  { background: #bad550 !important; }
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
        background: #ffffff;
        padding: 16px 16px 12px;
        border-radius: 18px;
        box-shadow: 0 8px 22px rgba(38, 49, 66, 0.08);
        border: 1px solid #eef2f6;
        color: #263142;
        display: flex;
        flex-direction: column;
        min-height: 250px;
    }
    .chart-card h3 {
        margin: 0 0 10px 0;
        font-weight: 700;
        font-family: 'Inter', 'Segoe UI', sans-serif;
        font-size: 0.92rem;
        color: #253141;
    }
    .chart-body {
        position: relative;
        width: 100%;
        overflow: visible;
    }
    
    .manager-container .chart-body--ring { height: 214px; }
    .manager-container .chart-body--ring canvas { max-height: 214px; }
    .manager-container .chart-body--paid { height: 186px; }
    .manager-container .chart-body--paid canvas { max-height: 186px; height: 100% !important; }
    .manager-container .chart-body--plot { height: 186px; }
    .manager-container .chart-html-legend li,
    .manager-container .chart-html-legend span,
    .manager-container .chart-html-legend-label {
        font-family: 'Inter', 'Segoe UI', sans-serif !important;
        font-size: 0.62rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
    }

    .manager-container .dashboard-period-filter {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 14px;
    }
    .manager-container .dashboard-period-filter__form {
        display: inline-flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 10px 14px !important;
        background: #d9e5e4;
        border: none;
        border-radius: 14px;
        box-shadow: none;
        padding: 10px 14px;
        width: auto !important;
        min-height: 0 !important;
    }
    .manager-container .dashboard-period-filter__row {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 8px !important;
        width: auto !important;
        min-height: 0 !important;
        position: relative !important;
        margin: 0 !important;
    }
    .manager-container .dashboard-period-filter__label,
    .manager-container .dashboard-period-filter__label-1 {
        display: block !important;
        font-size: 0.72rem !important;
        font-weight: 400 !important;
        color: #4b5563 !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
        margin: 0 !important;
        padding: 0 !important;
        white-space: nowrap !important;
        pointer-events: none !important;
        position: static !important;
        text-align: left !important;
    }
    .manager-container .dashboard-period-filter .filter-select-wrap {
        position: relative !important;
        inset: auto !important;
        width: auto !important;
        min-width: 150px !important;
        flex: 0 0 auto !important;
        z-index: auto !important;
    }
    .manager-container .dashboard-period-filter .filter-select__trigger {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        border: none;
        border-radius: 14px;
        font-size: 0.8rem;
        font-weight: 700;
        box-shadow: none;
        background: #fff !important;
        min-height: 36px !important;
        height: auto !important;
        width: auto !important;
        padding: 8px 14px !important;
        gap: 8px !important;
    }
    .manager-container .dashboard-period-filter .filter-select__label {
        position: static !important;
        width: auto !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        clip: auto !important;
        white-space: nowrap !important;
        border: 0 !important;
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
            <div class="chart-body chart-body--plot">
                <canvas id="departmentBar"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3>Monthly Requests by Status ({{ \App\Support\LeaveYear::label((int) ($chartYear ?? now()->year)) }})</h3>
            <div class="chart-body chart-body--plot">
                <canvas id="monthlyStatusStackedBar"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3>Paid vs Unpaid Leave</h3>
            <div class="chart-body chart-body--paid">
                <canvas id="leaveChart"></canvas>
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

    window.chartTextColor = '#3d4450';
    window.chartBoldFontSpec = { family: "'Inter', 'Segoe UI', sans-serif", size: 10, weight: '500', style: 'normal' };
    window.chartAxisTicksBold = { color: window.chartTextColor, font: { ...window.chartBoldFontSpec } };
    window.applyBoldChartDefaults();
    window.finalizeChartDefaults();

    const protoColors = ['#4a7ff7', '#b08cf5', '#f5b96b', '#f0d24a', '#4fe8c6', '#90c8c8', '#a04088', '#b8d850'];
    const statusSliceColors = @json(config('leave_status_colors.status_pie'));
    const monthlyStatusColors = {
        Approved: '#7ebade',
        Pending: '#105078',
        Overdue: '#f4a0ab',
        Rejected: '#d4e04a',
    };
    const blue = @json(config('leave_status_colors.department_bar'));
    const paidColor = @json(config('leave_status_colors.paid_unpaid.Paid'));
    const unpaidColor = @json(config('leave_status_colors.paid_unpaid.Unpaid'));
    const monthlyLineColor = @json(config('leave_status_colors.monthly_applications_line'));

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
            ctx.font = '500 9px Inter, sans-serif';
            ctx.fillStyle = '#3d4450';
            ctx.textBaseline = 'middle';

            for (let i = 0; i < slices.length; i++) {
                if (Number(values[i]) === 0) {
                    continue;
                }
                const slice = slices[i];
                const middleAngle = (slice.startAngle + slice.endAngle) / 2;
                const labelRadius = slice.outerRadius + 18;
                const x = slice.x + Math.cos(middleAngle) * labelRadius;
                const y = slice.y + Math.sin(middleAngle) * labelRadius;

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
                lines.push(String(values[i]) + ' (' + (Math.round(values[i] / total * 1000) / 10) + '%)');

                for (let n = 0; n < lines.length; n++) {
                    ctx.fillText(lines[n], x, y + (n - (lines.length - 1) / 2) * 11);
                }
            }
            ctx.restore();
        }
    };
    const roomForLabels = { padding: { top: 34, bottom: 34, left: 56, right: 56 } };

    const leaveTypeLabels = @json(array_keys($leaveTypeCounts));
    const leaveTypeData = @json(array_values($leaveTypeCounts));
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

    const payStatusData = @json(array_values($payStatusCounts ?? ['Paid' => 0, 'Unpaid' => 0]));
    const payStatusPct = @json($payStatusPercentages ?? []);
    const paidPercentage = Number(payStatusPct['Paid'] ?? 0);
    const unpaidPercentage = Number(payStatusPct['Unpaid'] ?? 0);

    new Chart(document.getElementById('leaveChart'), {
        type: 'bar',
        data: {
            labels: [''],
            datasets: [
                {
                    label: 'Paid',
                    data: [paidPercentage],
                    backgroundColor: paidColor,
                    borderWidth: 0,
                    borderRadius: { topLeft: 5, bottomLeft: 5, topRight: 0, bottomRight: 0 },
                    borderSkipped: false,
                    categoryPercentage: 0.85,
                    barPercentage: 0.9,
                    stack: 'leave'
                },
                {
                    label: 'Unpaid',
                    data: [unpaidPercentage],
                    backgroundColor: unpaidColor,
                    borderWidth: 0,
                    borderRadius: { topLeft: 0, bottomLeft: 0, topRight: 5, bottomRight: 5 },
                    borderSkipped: false,
                    categoryPercentage: 0.85,
                    barPercentage: 0.9,
                    stack: 'leave'
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: 2, right: 4, bottom: 2, left: 4 } },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'center',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 14,
                        color: '#3d4450',
                        font: { family: "'Inter', 'Segoe UI', sans-serif", size: 12, weight: '400' }
                    }
                },
                tooltip: {
                    callbacks: {
                        title: () => '',
                        label: ctx => `${ctx.dataset.label}: ${ctx.raw}%`
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    min: 0,
                    max: 100,
                    grid: { display: false, drawBorder: false },
                    border: { display: false },
                    ticks: {
                        stepSize: 20,
                        callback: value => value + '%',
                        color: '#3d4450',
                        font: { family: "'Inter', 'Segoe UI', sans-serif", size: 11, weight: '400' }
                    }
                },
                y: {
                    stacked: true,
                    display: false,
                    grid: { display: false },
                    border: { display: false }
                }
            }
        }
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
    const statusPieColors = statusPieLabels.map((label) => statusSliceColors[label] || '#90c8c8');

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
    const monthlyColor = monthlyLineColor;
    const monthlyLineChart = new Chart(document.getElementById('monthlyLine'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Total Requests',
                data: monthlyTotals,
                backgroundColor: hexToRgba(monthlyColor, 0.2),
                borderColor: monthlyColor,
                borderWidth: 2,
                tension: 0.45,
                cubicInterpolationMode: 'monotone',
                fill: false,
                pointRadius: 4,
                pointHoverRadius: 5,
                pointBackgroundColor: monthlyColor,
                pointBorderColor: monthlyColor,
                pointBorderWidth: 0,
                capBezierPoints: true
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
                    borderRadius: window.barTopRadius,
                    borderSkipped: false,
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
                    backgroundColor: monthlyStatusColors.Approved,
                    borderWidth: 0,
                    borderRadius: 0,
                    borderSkipped: false,
                    maxBarThickness: 28,
                    stack: 'status'
                },
                {
                    label: 'Pending',
                    data: monthlyPending,
                    backgroundColor: monthlyStatusColors.Pending,
                    borderWidth: 0,
                    borderRadius: 0,
                    borderSkipped: false,
                    maxBarThickness: 28,
                    stack: 'status'
                },
                {
                    label: 'Overdue',
                    data: monthlyOverdue,
                    backgroundColor: monthlyStatusColors.Overdue,
                    borderWidth: 0,
                    borderRadius: 0,
                    borderSkipped: false,
                    maxBarThickness: 28,
                    stack: 'status'
                },
                {
                    label: 'Rejected',
                    data: monthlyRejected,
                    backgroundColor: monthlyStatusColors.Rejected,
                    borderWidth: 0,
                    borderRadius: 0,
                    borderSkipped: false,
                    maxBarThickness: 28,
                    stack: 'status'
                }
            ];

    const monthlyStatusChart = new Chart(document.getElementById('monthlyStatusStackedBar'), {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: monthlyStatusDatasets
        },
        options: mergeChartOptions({
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: false,
                        boxWidth: 12,
                        boxHeight: 12,
                        borderRadius: 0,
                        padding: 14,
                        color: '#263142',
                        font: { family: "'Inter', 'Segoe UI', sans-serif", size: 11, weight: '700' }
                    }
                }
            },
            scales: {
                x: { stacked: true, grid: { display: false }, ticks: window.getMonthAxisTicks() },
                y: { stacked: true, beginAtZero: true, grid: { color: '#d9d9d9' }, border: { display: false }, ticks: { precision: 0 } }
            }
        })
    });
    window.registerMonthAxisChart(monthlyStatusChart);

    window.replaceEmptyDashboardCharts([
        { id: 'leaveDoughnut', values: leaveTypeData, message: 'No leave requests by type for this filter.' },
        { id: 'statusPie', values: statusPieData, message: 'No leave requests by status for this filter.' },
        { id: 'monthlyLine', values: monthlyTotals, message: 'No applications submitted in this leave year.' },
        { id: 'departmentBar', values: @json($departmentData ?? []), message: 'No leave requests by department for this filter.' },
        { id: 'monthlyStatusStackedBar', values: monthlyStatusDatasets, message: 'No monthly status data for this leave year.' },
        { id: 'leaveChart', values: payStatusData, message: 'No paid or unpaid leave for this filter.' },
    ]);

});
</script>
@endsection