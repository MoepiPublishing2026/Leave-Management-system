@extends('layouts.manager')

@section('title', 'Manager Dashboard')

@section('content')
@include('partials.dashboard-status-card-styles')
<style>
    body, html { margin: 0; padding: 0; }
    .manager-container {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        max-width: 1200px;
        padding: 16px 18px 28px;
        min-height: 100vh;
        background: white;
        border-radius: 20px;
        box-shadow: 0 6px 20px rgba(163, 71, 71, 0.08);
        margin: 0 auto;
    }
    h1 {
        font-weight: 700;
        font-size: 36px;
        color: #0f172a;
        margin-bottom: 24px;
        padding: 0;
        border: none;
        letter-spacing: 0.04em;
    }
    .cards {
        display: flex;
        flex-wrap: nowrap;
        gap: 12px;
        margin-bottom: 40px;
        width: 100%;
        overflow-x: auto;
        padding-bottom: 8px;
    }
    .card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 11px 8px 10px;
        box-shadow: 0 6px 16px rgba(8, 8, 8, 0.15);
        border: 1px solid #e5e7eb;
        cursor: pointer;
        transition: box-shadow 0.3s ease;
        text-align: center;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        justify-content: center;
        flex: 1 1 0;
        min-width: 0;
        height: 94px;
        min-height: 94px;
        color: #fff;
        box-sizing: border-box;
    }
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
        font-size: 1rem;
        font-weight: 600;
        margin: 2px 0 6px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
    }
    .card-value {
        font-weight: bold;
        font-size: 1.65rem;
        margin-top: 0;
    }
    .charts-container {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-top: 10px;
        align-items: stretch;
    }
    .chart-card {
        background: white;
        padding: 10px 10px;
        border-radius: 24px;
        box-shadow: 0 6px 22px rgba(67, 83, 255, 0.12);
        border: 1px solid #dbe3f0;
        color: #222;
        display: flex;
        flex-direction: column;
        min-height: 240px;
    }
    .chart-card h3 {
        margin: 0 0 10px 0;
        font-weight: 700;
        font-size: 1rem;
        color: #0f172a;
    }
    .chart-body {
        position: relative;
        width: 100%;
        overflow: visible;
    }
    @media (max-width: 992px) {
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
        'personalLabel' => 'My leave dashboard',
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
            <ul class="chart-html-legend" id="leaveDoughnutLegend" aria-label="Leave types legend"></ul>
        </div>
        <div class="chart-card">
            <h3>Leave Requests by Status</h3>
            <div class="chart-body chart-body--ring">
                <canvas id="statusPie"></canvas>
            </div>
            <ul class="chart-html-legend" id="statusPieLegend" aria-label="Leave status legend"></ul>
        </div>
        <div class="chart-card">
            <h3>Monthly Applications Submitted ({{ $chartYear }})</h3>
            <div class="chart-body chart-body--plot">
                <canvas id="monthlyLine"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3>Requests by Department</h3>
            @if(empty($departmentLabels))
                <p style="margin:0; color:#64748b; font-size:14px;">No leave requests by department for this period.</p>
            @else
                <div class="chart-body chart-body--ring">
                    <canvas id="departmentPolar"></canvas>
                </div>
                <ul class="chart-html-legend" id="departmentPolarLegend" aria-label="Departments legend"></ul>
            @endif
        </div>
        <div class="chart-card">
            <h3>Requests by Designation</h3>
            <div class="chart-body chart-body--plot">
                <canvas id="designationBar"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3>Monthly Requests by Status ({{ $chartYear }})</h3>
            <div class="chart-body chart-body--plot">
                <canvas id="monthlyStatusStackedBar"></canvas>
            </div>
            <ul class="chart-html-legend" id="monthlyStatusStackedBarLegend" aria-label="Monthly status legend"></ul>
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

    const leaveTypeLabels = @json($leaveTypeCounts->keys()->toArray());
    const leaveTypeData = @json($leaveTypeCounts->values()->toArray());
    const leaveTypePct = @json($leaveTypePercentages ?? []);
    const leaveTypeColors = leaveTypeLabels.map((t) => getColorForType(t));

    new Chart(document.getElementById('leaveDoughnut'), {
        type: 'doughnut',
        data: {
            labels: leaveTypeLabels,
            datasets: [{
                data: leaveTypeData,
                backgroundColor: leaveTypeColors,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: mergeChartOptions({
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
    renderChartHtmlLegend('leaveDoughnutLegend', leaveTypeLabels, leaveTypeColors, leaveTypePct);

    const statusPct = @json($statusPercentages ?? []);
    const statusPieLabels = window.leaveStatusPieLabels;
    const statusPieColors = window.statusColorsForLabels(statusPieLabels);

    new Chart(document.getElementById('statusPie'), {
        type: 'pie',
        data: {
            labels: statusPieLabels,
            datasets: [{
                data: [
                    {{ $statusCounts['Pending'] ?? 0 }},
                    {{ $statusCounts['Overdue'] ?? 0 }},
                    {{ $statusCounts['Rejected'] ?? 0 }},
                    {{ $statusCounts['Approved'] ?? 0 }}
                ],
                backgroundColor: statusPieColors,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: mergeChartOptions({
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
    renderChartHtmlLegend('statusPieLegend', statusPieLabels, statusPieColors, statusPct);

    const allMonthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
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
    const monthlyColor = window.leaveMonthlyApplicationsColor;
    const monthlyLineChart = new Chart(document.getElementById('monthlyLine'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Total Requests',
                data: monthlyTotals,
                backgroundColor: hexToRgba(monthlyColor, 0.2),
                borderColor: monthlyColor,
                tension: 0.3,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: mergeChartOptions({
            layout: window.getMonthChartLayout(),
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: window.getMonthAxisTicks() },
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        })
    });
    window.registerMonthAxisChart(monthlyLineChart);

    const deptPolarCanvas = document.getElementById('departmentPolar');
    if (deptPolarCanvas) {
        const deptLabels = @json($departmentLabels);
        const deptData = @json($departmentData);
        const deptPct = @json($departmentPercentages ?? []);
        const polarLegendGen = Chart.overrides?.polarArea?.plugins?.legend?.labels?.generateLabels
            || Chart.overrides?.doughnut?.plugins?.legend?.labels?.generateLabels;
        const deptColors = deptLabels.map((d) => colorForGroupLabel(d));
        new Chart(deptPolarCanvas, {
            type: 'polarArea',
            data: {
                labels: deptLabels,
                datasets: [{
                    data: deptData,
                    backgroundColor: deptLabels.map((d) => hexToRgba(colorForGroupLabel(d), 0.65)),
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: mergeChartOptions({
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const pct = (deptPct[ctx.label] ?? 0).toFixed(1);
                                return `${ctx.label}: ${ctx.formattedValue} (${pct}%)`;
                            }
                        }
                    }
                },
                scales: window.polarAreaChartScales,
            })
        });
        renderChartHtmlLegend('departmentPolarLegend', deptLabels, deptColors, deptPct);
    }

    const designationLabels = @json($designationLabels ?? []);
    const designationData = @json($designationData ?? []);

    new Chart(document.getElementById('designationBar'), {
        type: 'bar',
        data: {
            labels: designationLabels,
            datasets: [{
                label: 'Requests',
                data: designationData,
                backgroundColor: designationLabels.map((l) => hexToRgba(colorForGroupLabel(l), 0.8)),
                borderColor: designationLabels.map((l) => colorForGroupLabel(l)),
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: window.categoryBarChartOptions({ precision: 0 }),
    });

    const monthlyStatusDatasets = [
                {
                    label: 'Approved',
                    data: monthlyApproved,
                    backgroundColor: colorForStatus('Approved'),
                    borderColor: colorForStatus('Approved'),
                    borderWidth: 1,
                    borderRadius: 4,
                    stack: 'status'
                },
                {
                    label: 'Pending',
                    data: monthlyPending,
                    backgroundColor: colorForStatus('Pending'),
                    borderColor: colorForStatus('Pending'),
                    borderWidth: 1,
                    borderRadius: 4,
                    stack: 'status'
                },
                {
                    label: 'Overdue',
                    data: monthlyOverdue,
                    backgroundColor: colorForStatus('Overdue'),
                    borderColor: colorForStatus('Overdue'),
                    borderWidth: 1,
                    borderRadius: 4,
                    stack: 'status'
                },
                {
                    label: 'Rejected',
                    data: monthlyRejected,
                    backgroundColor: colorForStatus('Rejected'),
                    borderColor: colorForStatus('Rejected'),
                    borderWidth: 1,
                    borderRadius: 4,
                    stack: 'status'
                }
            ];

    const monthlyStatusChart = new Chart(document.getElementById('monthlyStatusStackedBar'), {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: monthlyStatusDatasets
        },
        options: window.statusStackedBarChartOptions()
    });
    window.registerMonthAxisChart(monthlyStatusChart);
    window.renderDatasetLegend('monthlyStatusStackedBarLegend', monthlyStatusDatasets);

});
</script>
@endsection
