@extends('layouts.admin')

@section('title', 'Admin Dashboard')

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
        color: #2c3e50;
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
    height: 88px;
    min-height: 88px;
    box-sizing: border-box;
}

.card-pending {
    background: #C291A4;
    border-color: #C291A4;
    color: #fff;
}

.card-approved {
    background: #E27A53;
    border-color: #E27A53;
    color: #fff;
}

.card-rejected {
    background: #87AFC7;
    border-color: #87AFC7;
    color: #fff;
}

.card-onleave {
    background: #87AFC7;
    border-color: #87AFC7;
    color: #fff;
}
.card-sixth {
    background: #D2B55B;
    border-color: #D2B55B;
    color: #fff;
}
.card-all {
    background: #00C0A3;
    border-color: #00C0A3;
    color: #fff;
}
.card-overdue {
    background: #C154C1;
    border-color: #C154C1;
    color: #fff;
}
.card .card-title,
.card .card-value {
    color: #fff;
}
.card .card-title svg {
    display: none;
}
.card .card-title {
    justify-content: center;
    text-align: center;
}

    .card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    }
   
    .card-title {
        font-size: 0.88rem;
        font-weight: 600;
        margin: 2px 0 6px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
    }
    .card-value {
        font-weight: bold;
        font-size: 1.45rem;
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
        color: #2c3e50;
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

<h1>Admin Dashboard</h1>
<div class="admin-container">
@include('partials.dashboard-period-filter', ['monthFilter' => $monthFilter ?? 'this_year'])
    <div class="cards" id="cardsContainer" role="list">

        <a href="{{ route('admin.requests', ['status' => 'all', 'month' => ($monthFilter ?? 'this_year')]) }}" role="listitem" aria-label="All leave requests" class="card card-all">
            <div class="card-title">All Requests</div>
            <div class="card-value">{{ $allRequestsCount ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.requests', ['status' => 'pending', 'month' => ($monthFilter ?? 'this_year')]) }}" role="listitem" aria-label="Pending leave requests" class="card card-pending">
            <div class="card-title">Pending Requests</div>
            <div class="card-value">{{ $pendingRequestsCount }}</div>
        </a>

        <a href="{{ route('admin.requests', ['status' => 'overdue', 'month' => ($monthFilter ?? 'this_year')]) }}" role="listitem" aria-label="Overdue leave requests" class="card card-overdue">
            <div class="card-title">Overdue Requests</div>
            <div class="card-value">{{ $overduePendingCount ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.requests', ['status' => 'approved', 'month' => ($monthFilter ?? 'this_year')]) }}" role="listitem" aria-label="Approved leave requests" class="card card-approved">
            <div class="card-title">Approved Requests</div>
            <div class="card-value">{{ $approvedRequestsCount ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.calendar') }}" role="listitem" aria-label="Employees on leave today" class="card card-onleave">
            <div class="card-title">On Leave Today</div>
            <div class="card-value">{{ $onLeaveTodayCount }}</div>
        </a>

        <a href="{{ route('admin.employees.index') }}" role="listitem" aria-label="Active employees count" class="card card-sixth">
            <div class="card-title">Active Employees</div>
            <div class="card-value">{{ $activeEmployeesCount }}</div>
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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#2c3e50';
    Chart.defaults.maintainAspectRatio = false;

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
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    bodyFont: { size: 14 },
                    titleFont: { weight: '700' },
                    callbacks: {
                        label: (ctx) => {
                            const pct = (leaveTypePct[ctx.label] ?? 0).toFixed(1);
                            return `${ctx.label}: ${ctx.parsed} (${pct}%)`;
                        }
                    }
                }
            }
        }
    });
    renderChartHtmlLegend('leaveDoughnutLegend', leaveTypeLabels, leaveTypeColors, leaveTypePct);

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
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    bodyFont: { size: 14 },
                    titleFont: { weight: '700' },
                    callbacks: {
                        label: (ctx) => {
                            const pct = (statusPct[ctx.label] ?? 0).toFixed(1);
                            return `${ctx.label}: ${ctx.parsed} (${pct}%)`;
                        }
                    }
                }
            }
        }
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
    const monthAxisTicks = { autoSkip: false, maxRotation: 0, minRotation: 0 };
    const monthlyColor = window.leaveMonthlyApplicationsColor;

    new Chart(document.getElementById('monthlyLine'), {
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
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { bodyFont: { size: 14 }, titleFont: { weight: '700' } }
            },
            scales: {
                x: { ticks: monthAxisTicks },
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    const deptPolarCanvas = document.getElementById('departmentPolar');
    if (deptPolarCanvas) {
        const deptLabels = @json($departmentLabels);
        const deptData = @json($departmentData);
        const deptPct = @json($departmentPercentages ?? []);
        const polarLegendGen = Chart.overrides?.polarArea?.plugins?.legend?.labels?.generateLabels
            || Chart.overrides?.doughnut?.plugins?.legend?.labels?.generateLabels;
        const deptColors = deptLabels.map((d) => colorForKey(String(d || '').trim().toLowerCase()));
        new Chart(deptPolarCanvas, {
            type: 'polarArea',
            data: {
                labels: deptLabels,
                datasets: [{
                    data: deptData,
                    backgroundColor: deptLabels.map((d) => hexToRgba(colorForKey(String(d || '').trim().toLowerCase()), 0.65)),
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        bodyFont: { size: 14 },
                        titleFont: { weight: '700' },
                        callbacks: {
                            label: (ctx) => {
                                const pct = (deptPct[ctx.label] ?? 0).toFixed(1);
                                return `${ctx.label}: ${ctx.formattedValue} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
        renderChartHtmlLegend('departmentPolarLegend', deptLabels, deptColors, deptPct);
    }

    const designationLabels = @json($designationLabels ?? []);
    const designationData = @json($designationData ?? []);
    const designationPalette = @json(config('designation_colors.palette'));
    const normalizeDesignation = (value) => String(value || '').trim().toLowerCase();
    const colorForDesignation = (label) => {
        const key = normalizeDesignation(label) || 'unknown';
        return designationPalette[hashString(key) % designationPalette.length];
    };

    new Chart(document.getElementById('designationBar'), {
        type: 'bar',
        data: {
            labels: designationLabels,
            datasets: [{
                label: 'Requests',
                data: designationData,
                backgroundColor: designationLabels.map((l) => hexToRgba(colorForDesignation(l), 0.8)),
                borderColor: designationLabels.map((l) => colorForDesignation(l)),
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { bodyFont: { size: 14 }, titleFont: { weight: '700' } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });

    new Chart(document.getElementById('monthlyStatusStackedBar'), {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [
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
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 13, weight: '600' }, color: '#2c3e50' }
                },
                tooltip: { bodyFont: { size: 14 }, titleFont: { weight: '700' } }
            },
            scales: {
                x: {
                    type: 'category',
                    stacked: true,
                    offset: true,
                    ticks: monthAxisTicks
                },
                y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

});
</script>
@endsection
