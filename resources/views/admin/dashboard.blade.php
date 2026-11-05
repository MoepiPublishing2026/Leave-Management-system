@extends('layouts.admin')

@section('title', 'Senior Manager Dashboard')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Open+Sans:wght@700&display=swap" rel="stylesheet">
<style>
    html body { font-family: 'Inter', 'Segoe UI', sans-serif; }
    body, html {
        margin: 0;
        padding: 0;
    }
    
    .admin-dash-top {
        width: 100%;
        max-width: 1410px;
        margin: 0 auto 18px;
        padding: 0 30px;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
    }
    .admin-dash-tools {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
        position: relative;
        z-index: 20;
    }
    .menu-button {
        width: 54px;
        height: 50px;
        padding: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 6px;
        background-color: #263142;
        border: 3px solid #d4e21a;
        border-radius: 14px;
        cursor: pointer;
        box-sizing: border-box;
        flex-shrink: 0;
    }

    .menu-button span {
        display: block;
        width: 35px;
        height: 4px;
        background-color: #ffffff;
        border-radius: 3px;
        flex-shrink: 0;
    }

    .menu-button:hover {
        background-color: #34465a;
    }

    .menu-button:focus-visible {
        outline: 2px solid #263445;
        outline-offset: 3px;
    }

    .dashboard-filter-position {
        display: none;
        width: 210px;
    }
    .dashboard-filter-position.show {
        display: block;
    }

    .dashboard-filter-position .dashboard-period-filter {
        margin: 0;
        justify-content: stretch;
    }
    .dashboard-filter-position .dashboard-period-filter__form {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: stretch;
        gap: 4px;
        text-align: center;
        background: #ffffff;
        border: 3px solid #d4e21a;
        border-radius: 22px;
        padding: 10px 16px 12px;
        box-shadow: 0 10px 24px rgba(38, 49, 66, 0.12);
        width: 100%;
    }

    .dashboard-filter-position .filter-select__trigger {
        min-height: 54px;
        width: 100%;
        background: #ffffff;
        border: none;
        border-radius: 14px;
        color: #111111;
        font-family: 'Inter', 'Segoe UI', sans-serif;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 0;
        box-shadow: none;
    }
    .admin-container {
        font-family: 'Inter', 'Segoe UI', sans-serif;
        width: 100%;
        max-width: 1410px;
        box-sizing: border-box;
        padding: 36px 22px 22px;
        background: #ffffff;
        border: 1px solid #e7edf2;
        border-radius: 28px;
        box-shadow: 0 12px 32px rgba(38, 49, 66, 0.06);
        margin: 0 auto 72px;
    }
    .admin-page-header {
        min-width: 0;
        margin: 0;
        padding: 0 6px 18px;
        box-sizing: border-box;
    }

    .admin-page-header h1 {
        font-family: 'Open Sans', 'Segoe UI', sans-serif;
        font-weight: 700;
        font-size: 38px;
        color: #1a2332;
        margin: 0;
        padding: 0;
        letter-spacing: 0;
        line-height: 1.2;
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
    .admin-container .cards .card.card-all      { background: #4fe8c6 !important; }
    .admin-container .cards .card.card-pending  { background: #b090f6 !important; }
    .admin-container .cards .card.card-overdue  { background: #f0b868 !important; }
    .admin-container .cards .card.card-approved { background: #a04088 !important; }
    .admin-container .cards .card.card-rejected { background: #90c8c8 !important; }
    .admin-container .cards .card.card-onleave  { background: #b8d850 !important; }
    .card-title {
        font-family: 'Inter', 'Segoe UI', sans-serif;
        font-size: 0.78rem;
        font-weight: 400;
        margin: 0 0 2px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
        text-align: center;
        color: #ffffff;
        line-height: 1.2;
    }
    .card-value {
        font-family: 'Inter', 'Segoe UI', sans-serif;
        font-weight: 700;
        font-size: 1.65rem;
        margin-top: 0;
        color: #ffffff;
        line-height: 1.1;
    }
    .dash-charts-grid {
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
        margin: 0 0 8px 0;
        font-family: 'Inter', 'Segoe UI', sans-serif;
        font-weight: 700;
        font-size: 0.92rem;
        letter-spacing: 0;
        color: #1a2332;
    }
    .chart-body {
        position: relative;
        width: 100%;
        overflow: visible;
    }
    .admin-container .chart-body--ring { height: 214px; }
    .admin-container .chart-body--ring canvas { max-height: 214px; }
    .admin-container .chart-body--plot { height: 186px; }
    .dashboard-filter-position .filter-select-wrap {
        min-width: 0;
        width: 100%;
    }
    .dashboard-filter-position .filter-select__trigger {
        min-height: 40px;
        width: 100%;
        justify-content: center;
        background: #ffffff;
        border: none;
        border-radius: 12px;
        color: #30302d;
        font-family: 'Inter', 'Segoe UI', sans-serif;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: 0;
        color: #111111;
        box-shadow: none;
        padding: 6px 8px;
    }
    .dashboard-filter-position .filter-select__label {
        text-align: center;
    }
    .dashboard-filter-position .filter-select__option {
        text-align: center;
        font-weight: 700;
    }
    .dashboard-filter-position .filter-select__option.is-selected {
        background: #d4e21a;
        color: #263142;
    }
    @media (max-width: 1200px) {
        .admin-dash-top { flex-wrap: wrap; }
        .admin-dash-tools { margin-bottom: 0; }
        .admin-page-header h1 { font-size: 32px; }
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

<div class="admin-dash-top">
    <div class="admin-page-header">
        <h1>Senior Manager Dashboard</h1>
    </div>
    <div class="admin-dash-tools">
        <div class="dashboard-filter-position show">
            <div class="dashboard-filter-dropdown">
                @include('partials.dashboard-period-filter', [
                    'monthFilter' => $monthFilter ?? 'this_year',
                    'employeeFilter' => $employeeFilter ?? null,
                    'employees' => $employees ?? collect(),
                ])
            </div>
        </div>
        <button class="menu-button" type="button" aria-label="Show or hide dashboard filters" aria-expanded="true">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</div>

@php
    $cardFilters = $dashboardFilters ?? ['month' => $monthFilter ?? 'this_year'];
@endphp


<div class="admin-container">

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
            <div class="card-title">On Leave Requests</div>
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
            <h3>Monthly Application Submitted ({{ \App\Support\LeaveYear::label((int) ($chartYear ?? now()->year)) }})</h3>
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
    const menuButton = document.querySelector('.menu-button');
    const dashboardFilter = document.querySelector('.dashboard-filter-position');

    menuButton.addEventListener('click', function () {
        dashboardFilter.classList.toggle('show');

        const isOpen = dashboardFilter.classList.contains('show');

        menuButton.setAttribute('aria-expanded', isOpen);
    });

    
    document.addEventListener('click', function (event) {
        if (
            !menuButton.contains(event.target) &&
            !dashboardFilter.contains(event.target)
        ) {
            dashboardFilter.classList.remove('show');
            menuButton.setAttribute('aria-expanded', 'false');
        }
    });
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

    window.chartTextColor = '#3d4450';
    window.chartBoldFontSpec = { family: "'Inter', 'Segoe UI', sans-serif", size: 10, weight: '500', style: 'normal' };
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

    const protoColors = ['#4a7ff7', '#b08cf5', '#f5b96b', '#f0d24a', '#4fe8c6', '#90c8c8', '#a04088', '#b8d850'];
    const statusSliceColors = {
        Pending: '#105078',
        Overdue: '#f4a0ab',
        Approved: '#b08cf5',
        Rejected: '#f07a86'
    };
    const monthlyStatusColors = {
        Approved: '#7ebade',
        Pending: '#105078',
        Overdue: '#f4a0ab',
        Rejected: '#d4e04a',
        Withdrawn: '#fbe8b0'
    };
    const blue = '#5b86ea';

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
    const monthlyWithdrawn = padMonthlySeries(@json($monthlyWithdrawnData ?? []));
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
                borderWidth: 2,
                tension: 0.45,
                cubicInterpolationMode: 'monotone',
                fill: false,
                pointRadius: 0,
                pointHoverRadius: 3,
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
                    backgroundColor: monthlyStatusColors.Approved,
                    borderWidth: 0,
                    borderRadius: window.stackedBarTopRadius(8),
                    borderSkipped: false,
                    maxBarThickness: 28,
                    stack: 'status'
                },
                {
                    label: 'Pending',
                    data: monthlyPending,
                    backgroundColor: monthlyStatusColors.Pending,
                    borderWidth: 0,
                    borderRadius: window.stackedBarTopRadius(8),
                    borderSkipped: false,
                    maxBarThickness: 28,
                    stack: 'status'
                },
                {
                    label: 'Overdue',
                    data: monthlyOverdue,
                    backgroundColor: monthlyStatusColors.Overdue,
                    borderWidth: 0,
                    borderRadius: window.stackedBarTopRadius(8),
                    borderSkipped: false,
                    maxBarThickness: 28,
                    stack: 'status'
                },
                {
                    label: 'Rejected',
                    data: monthlyRejected,
                    backgroundColor: monthlyStatusColors.Rejected,
                    borderWidth: 0,
                    borderRadius: window.stackedBarTopRadius(8),
                    borderSkipped: false,
                    maxBarThickness: 28,
                    stack: 'status'
                },
                {
                    label: 'Withdrawn',
                    data: monthlyWithdrawn,
                    backgroundColor: monthlyStatusColors.Withdrawn,
                    borderWidth: 0,
                    borderRadius: window.stackedBarTopRadius(8),
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
                        boxWidth: 10,
                        boxHeight: 10,
                        padding: 12,
                        color: '#263142',
                        font: { family: "'Inter', 'Segoe UI', sans-serif", size: 10, weight: '500' }
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

    
    const payStatusData = @json(array_values($payStatusCounts ?? ['Paid' => 0, 'Unpaid' => 0]));
    const payStatusPct = @json($payStatusPercentages ?? []);

    const paidPercentage = Number(payStatusPct['Paid'] ?? 0);
    const unpaidPercentage = Number(payStatusPct['Unpaid'] ?? 0);

    new Chart(document.getElementById('leaveChart'), {
        type: 'bar',
        data: {
            labels: ['Leave'],
            datasets: [
                {
                    label: 'Paid',
                    data: [paidPercentage],
                    backgroundColor: '#4a7ff7',
                    borderWidth: 0,
                    stack: 'leave'
                },
                {
                    label: 'Unpaid',
                    data: [unpaidPercentage],
                    backgroundColor: '#c4b0f2',
                    borderWidth: 0,
                    stack: 'leave'
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => `${ctx.dataset.label}: ${ctx.raw}%`
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    min: 0,
                    max: 100,
                    ticks: {
                        stepSize: 20,
                        callback: value => value + '%'
                    }
                },
                y: {
                    stacked: true,
                    display: false
                }
            }
        }
    });

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
