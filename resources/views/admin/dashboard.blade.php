@extends('layouts.admin')

@section('title', 'Senior Manager Dashboard')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    html body { font-family: 'Poppins', 'Segoe UI', sans-serif; }
    body, html {
        margin: 0;
        padding: 0;
    }
    .admin-container {
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
    .admin-page-header {
        max-width: 1200px;
        margin: 0 auto 22px;
        padding: 0 30px;
        box-sizing: border-box;
    }
    .admin-page-header h1 {
        font-family: 'Century Gothic', 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 38px;
        color: #253141;
        margin: 0;
        padding: 0;
        letter-spacing: 0.04em;
        line-height: 1.15;
    }
    .cards {
        display: flex;
        flex-wrap: nowrap;
        gap: 12px;
        margin-bottom: 32px;
        width: 100%;
        overflow-x: auto;
        padding-bottom: 8px;
    }
    .admin-container .cards .card {
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
    .admin-container .cards .card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25) !important;
    }
    .admin-container .cards .card.card-all      { background: #4fe6c5 !important; }
    .admin-container .cards .card.card-pending  { background: #f2b96b !important; }
    .admin-container .cards .card.card-overdue  { background: #ab8cf5 !important; }
    .admin-container .cards .card.card-approved { background: #a03d85 !important; }
    .admin-container .cards .card.card-rejected { background: #94c4c8 !important; }
    .admin-container .cards .card.card-onleave  { background: #b9d84a !important; }
    .card-title {
        font-size: 0.85rem;
        font-weight: 400;
        margin: 2px 0 6px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
        text-align: center;
    }
    .card-value {
        font-weight: bold;
        font-size: 1.4rem;
        margin-top: 0;
    }
    .dash-charts-grid {
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
    .admin-container .chart-body--ring { height: 214px; }
    .admin-container .chart-body--ring canvas { max-height: 214px; }
    .admin-container .chart-body--plot { height: 186px; }
    .admin-container .dashboard-period-filter__form {
        background: #d9e5e4;
        border: none;
        border-radius: 14px;
        box-shadow: none;
    }
    .admin-container .dashboard-period-filter__label { font-size: 0.72rem; font-weight: 400; color: #4b5563; }
    .admin-container .dashboard-period-filter .filter-select__trigger {
        border: none;
        border-radius: 14px;
        font-size: 0.8rem;
        font-weight: 700;
        box-shadow: none;
    }
    .admin-container .dashboard-period-filter .filter-select__option { text-align: center; font-weight: 700; }
    .admin-container .dashboard-period-filter .filter-select__option.is-selected { background: #a2b224; color: #1f2937; }
    @media (max-width: 1200px) {
        .admin-page-header h1 { font-size: 28px; }
        .dash-charts-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 992px) {
        .dash-charts-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .card { min-width: 210px; flex-basis: 210px; }
    }
    @media (max-width: 600px) {
        .dash-charts-grid { grid-template-columns: 1fr; }
        .cards { gap: 12px; }
        .card { max-width: none; width: 100%; }
    }
</style>

<div class="admin-page-header">
    <h1>Senior Manager Dashboard</h1>
</div>
<div class="admin-container">
@include('partials.dashboard-period-filter', [
    'monthFilter' => $monthFilter ?? 'this_year',
    'employeeFilter' => $employeeFilter ?? null,
    'employees' => $employees ?? collect(),
])
@php
    $cardFilters = $dashboardFilters ?? ['month' => $monthFilter ?? 'this_year'];
@endphp
    <div class="cards" id="cardsContainer" role="list">

        <a href="{{ route('admin.requests', array_merge(['status' => 'all'], $cardFilters)) }}" role="listitem" aria-label="All leave requests" class="card card-all">
            <div class="card-title">All Requests</div>
            <div class="card-value">{{ $allRequestsCount ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.requests', array_merge(['status' => 'pending'], $cardFilters)) }}" role="listitem" aria-label="Pending leave requests" class="card card-pending">
            <div class="card-title">Pending Requests</div>
            <div class="card-value">{{ $pendingRequestsCount }}</div>
        </a>

        <a href="{{ route('admin.requests', array_merge(['status' => 'overdue'], $cardFilters)) }}" role="listitem" aria-label="Overdue leave requests" class="card card-overdue">
            <div class="card-title">Overdue Requests</div>
            <div class="card-value">{{ $overduePendingCount ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.requests', array_merge(['status' => 'approved'], $cardFilters)) }}" role="listitem" aria-label="Approved leave requests" class="card card-approved">
            <div class="card-title">Approved Requests</div>
            <div class="card-value">{{ $approvedRequestsCount ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.requests', array_merge(['status' => 'rejected'], $cardFilters)) }}" role="listitem" aria-label="Rejected leave requests" class="card card-rejected">
            <div class="card-title">Rejected Requests</div>
            <div class="card-value">{{ $rejectedRequestsCount ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.calendar') }}" role="listitem" aria-label="Staff on leave today" class="card card-onleave">
            <div class="card-title">On Leave Today</div>
            <div class="card-value">{{ $onLeaveTodayCount }}</div>
        </a>

    </div>

    <div class="dash-charts-grid">
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
        const raw = hex.replace('#', '');
        const r = parseInt(raw.slice(0, 2), 16);
        const g = parseInt(raw.slice(2, 4), 16);
        const b = parseInt(raw.slice(4, 6), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
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
        const s = String(str || '');
        for (let i = 0; i < s.length; i += 1) {
            h = ((h << 5) - h) + s.charCodeAt(i);
            h |= 0;
        }
        return Math.abs(h);
    };
    const normalizeKey = (value) => {
        const v = String(value || '').trim().toLowerCase();
        if (!v) return '';
        return v.endsWith(' leave') ? v.slice(0, -5).trim() : v;
    };
    const colorForKey = (key) => {
        return fallbackColors[hashString(key || 'default') % fallbackColors.length];
    };
    const getColorForType = (type) => {
        const key = normalizeKey(type);
        return fixedTypeColors[key] || colorForKey(key);
    };

    window.chartBoldFontSpec = { family: "'Poppins', sans-serif", size: 12, weight: 'bold', style: 'normal' };
    window.chartAxisTicksBold = { color: window.chartTextColor, font: { ...window.chartBoldFontSpec } };
    window.applyBoldChartDefaults();
    window.finalizeChartDefaults();

    const container = document.getElementById('cardsContainer');
    let dragged;

    container.addEventListener('dragstart', (e) => {
        dragged = e.target;
        e.target.style.opacity = 0.5;
        e.dataTransfer.effectAllowed = "move";
    });

    container.addEventListener('dragend', (e) => {
        e.target.style.opacity = "";
    });

    container.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = "move";
        const target = e.target.closest('.card');
        if(target && target !== dragged) {
            const rect = target.getBoundingClientRect();
            const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
            container.insertBefore(dragged, next && target.nextSibling || target);
        }
    });

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
            ctx.font = 'bold 11px Poppins, sans-serif';
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

    const statusPct = @json($statusPercentages ?? []);
    let statusList = [
        { label: 'Pending',  value: {{ $statusPieCounts['Pending'] ?? 0 }} },
        { label: 'Overdue',  value: {{ $statusPieCounts['Overdue'] ?? 0 }} },
        { label: 'Rejected', value: {{ $statusPieCounts['Rejected'] ?? 0 }} },
        { label: 'Approved', value: {{ $statusPieCounts['Approved'] ?? 0 }} }
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
                    backgroundColor: protoColors[6],
                    borderWidth: 0,
                    borderRadius: 5,
                    maxBarThickness: 40,
                    stack: 'status'
                },
                {
                    label: 'Pending',
                    data: monthlyPending,
                    backgroundColor: protoColors[2],
                    borderWidth: 0,
                    borderRadius: 5,
                    maxBarThickness: 40,
                    stack: 'status'
                },
                {
                    label: 'Overdue',
                    data: monthlyOverdue,
                    backgroundColor: protoColors[1],
                    borderWidth: 0,
                    borderRadius: 5,
                    maxBarThickness: 40,
                    stack: 'status'
                },
                {
                    label: 'Rejected',
                    data: monthlyRejected,
                    backgroundColor: protoColors[5],
                    borderWidth: 0,
                    borderRadius: 5,
                    maxBarThickness: 40,
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
            plugins: { legend: { display: false } },
            scales: {
                x: { stacked: true, grid: { display: false }, ticks: window.getMonthAxisTicks() },
                y: { stacked: true, beginAtZero: true, grid: { color: '#d9d9d9' }, border: { display: false }, ticks: { precision: 0 } }
            }
        })
    });
    window.registerMonthAxisChart(monthlyStatusChart);

    const payStatusLabels = ['Paid', 'Unpaid'];
    const payStatusData = @json(array_values($payStatusCounts ?? ['Paid' => 0, 'Unpaid' => 0]));
    const payStatusPct = @json($payStatusPercentages ?? []);
    const payStatusColors = [protoColors[0], protoColors[1]];

    new Chart(document.getElementById('payStatusPolar'), {
        type: 'polarArea',
        data: {
            labels: payStatusLabels,
            datasets: [{
                data: payStatusData,
                backgroundColor: payStatusColors,
                borderWidth: 0
            }]
        },
        options: mergeChartOptions({
            layout: { padding: 10 },
            scales: {
                r: {
                    beginAtZero: true,
                    ticks: {
                        display: false,
                        backdropColor: 'transparent',
                        showLabelBackdrop: false,
                    },
                    grid: { color: 'rgba(148, 163, 184, 0.35)', circular: true },
                    angleLines: { color: 'rgba(148, 163, 184, 0.35)' },
                    pointLabels: {
                        display: true,
                        color: '#253141',
                        font: { family: "'Poppins', sans-serif", size: 11, weight: 'bold' },
                        padding: 6,
                    },
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const pct = (payStatusPct[ctx.label] ?? 0).toFixed(1);
                            return `${ctx.label}: ${ctx.raw} (${pct}%)`;
                        }
                    }
                }
            }
        })
    });

    window.replaceEmptyDashboardCharts([
        { id: 'leaveDoughnut', values: leaveTypeData, message: 'No leave requests by type for this filter.' },
        { id: 'statusPie', values: statusPieData, message: 'No leave requests by status for this filter.' },
        { id: 'monthlyLine', values: monthlyTotals, message: 'No applications submitted in this leave year.' },
        { id: 'departmentBar', values: @json($departmentData ?? []), message: 'No leave requests by department for this filter.' },
        { id: 'monthlyStatusStackedBar', values: monthlyStatusDatasets, message: 'No monthly status data for this leave year.' },
        { id: 'payStatusPolar', values: payStatusData, message: 'No paid or unpaid leave for this filter.' },
    ]);

});
</script>
@endsection
