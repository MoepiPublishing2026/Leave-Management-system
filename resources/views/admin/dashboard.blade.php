@extends('layouts.admin')

@section('title', 'Senior Manager Dashboard')

@section('content')
<style>
    body, html {
        margin: 0;
        padding: 0;
    }
    .admin-container {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        width: 100%;
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
        font-size: 40px;
        color: #0f172a;
        margin: 0 0 24px 0;
    
        padding-left: 15px;
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
    padding: 12px 8px;
    box-shadow: 0 6px 16px rgba(8, 8, 8, 0.15);
    color: #fff;
    cursor: pointer;
    transition: box-shadow 0.3s ease;
    text-align: center;
    border: 1px solid #e5e7eb;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    justify-content: center;
    flex: 1 1 0;
    min-width: 0;
    height: 94px;
    min-height: 94px;
    box-sizing: border-box;
}

    .card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
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
    .dash-charts-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-top: 10px;
    }
    .chart-card {
        background: white;
        padding: 10px 10px;
        border-radius: 24px;
        box-shadow: 0 6px 22px rgba(67, 83, 255, 0.12);
        border: 1px solid #dbe3f0;
        color: #222;
        min-height: 240px;
        display: flex;
        flex-direction: column;
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
        .dash-charts-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 600px) {
        .dash-charts-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<h1>Senior Manager Dashboard</h1>
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
            <ul class="chart-html-legend" id="leaveDoughnutLegend" aria-label="Leave types legend"></ul>
        </div>
        <div class="chart-card">
            <h3>Monthly Applications Submitted ({{ $chartYear }})</h3>
            <div class="chart-body chart-body--plot">
                <canvas id="monthlyLine"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3>Leave Requests by Status</h3>
            <div class="chart-body chart-body--ring">
                <canvas id="statusPie"></canvas>
            </div>
            <ul class="chart-html-legend" id="statusPieLegend" aria-label="Leave status legend"></ul>
        </div>
        <div class="chart-card">
            <h3>Monthly Requests by Status ({{ $chartYear }})</h3>
            <div class="chart-body chart-body--plot">
                <canvas id="monthlyStatusStackedBar"></canvas>
            </div>
            <ul class="chart-html-legend" id="monthlyStatusStackedBarLegend" aria-label="Monthly status legend"></ul>
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
            <h3>Paid vs Unpaid Leave</h3>
            <div class="chart-body chart-body--ring">
                <canvas id="payStatusPolar"></canvas>
            </div>
            <ul class="chart-html-legend" id="payStatusPolarLegend" aria-label="Paid and unpaid leave legend"></ul>
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

    const leaveTypeLabels = @json(array_keys($leaveTypeCounts));
    const leaveTypeData = @json(array_values($leaveTypeCounts));
    const leaveTypePct = @json($leaveTypePercentages ?? []);
    const leaveTypeColors = leaveTypeLabels.map((t) => getColorForType(t));

    // Leave Requests by Type doughnut chart
    new Chart(document.getElementById('leaveDoughnut'), {
        type: 'doughnut',
        data: {
            labels: leaveTypeLabels,
            datasets: [{
                data: leaveTypeData,
                backgroundColor: leaveTypeColors,
                borderColor: '#fff',
                borderWidth: 2,
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

    const payStatusLabels = ['Paid', 'Unpaid'];
    const payStatusData = @json(array_values($payStatusCounts ?? ['Paid' => 0, 'Unpaid' => 0]));
    const payStatusPct = @json($payStatusPercentages ?? []);
    const payStatusColors = [chartPalette.green, chartPalette.orange];

    new Chart(document.getElementById('payStatusPolar'), {
        type: 'polarArea',
        data: {
            labels: payStatusLabels,
            datasets: [{
                data: payStatusData,
                backgroundColor: payStatusColors,
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
                            const pct = (payStatusPct[ctx.label] ?? 0).toFixed(1);
                            return `${ctx.label}: ${ctx.raw} (${pct}%)`;
                        }
                    }
                }
            }
        })
    });
    renderChartHtmlLegend('payStatusPolarLegend', payStatusLabels, payStatusColors, payStatusPct);

    const statusPct = @json($statusPercentages ?? []);
    const statusPieLabels = window.leaveStatusPieLabels;
    const statusPieColors = window.statusColorsForLabels(statusPieLabels);

    // Leave Requests by Status pie chart
    new Chart(document.getElementById('statusPie'), {
        type: 'pie',
        data: {
            labels: statusPieLabels,
            datasets: [{
                data: [
                    {{ $statusPieCounts['Pending'] ?? 0 }},
                    {{ $statusPieCounts['Overdue'] ?? 0 }},
                    {{ $statusPieCounts['Rejected'] ?? 0 }},
                    {{ $statusPieCounts['Approved'] ?? 0 }}
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
                    backgroundColor: deptLabels.map((label) => hexToRgba(colorForGroupLabel(label), 0.8)),
                    borderColor: deptLabels.map((label) => colorForGroupLabel(label)),
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: window.categoryBarChartOptions({ precision: 0 }),
        });
    }

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
