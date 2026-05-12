@extends('layouts.manager')

@section('title', 'Manager Dashboard')

@section('content')
<style>
    body, html { margin: 0; padding: 0; }
    .manager-container {
        font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
        max-width: 1200px;
        padding: 16px 18px 28px;
        min-height: 100vh;
        background: white;
        border-radius: 20px;
        box-shadow: 0 6px 20px rgba(163, 71, 71, 0.08);
        margin: 0 auto;
        user-select: none;
    }
    h1 {
        font-weight: 700;
        font-size: 36px;
        color: #2c3e50;
        margin-bottom: 24px;
        padding-left: 15px;
        letter-spacing: 0.04em;
    }
    .cards {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 40px;
        overflow-x: hidden;
        padding-bottom: 8px;
    }
    .card {
        background: white;
        border-radius: 16px;
        padding: 11px 12px 10px;
        box-shadow: 0 6px 16px rgba(8, 8, 8, 0.15);
        border: 1px solid #e5e7eb;
        cursor: pointer;
        transition: box-shadow 0.3s ease;
        user-select: none;
        text-align: center;
        border-left: 5px solid;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 210px;
        height: 96px;
        min-height: 96px;
        min-width: 210px;
        color: black;
        flex: 0 0 210px;
        max-width: 210px;
        box-sizing: border-box;
    }
    .card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    }
     .card-all {
        border-color: #eb0e8fff;
        color: #881f54ff;
    }
    .card-approved {
        border-color: #53e21aff;
        color: #1c7737ff;
    }
    .card-pending {
        border-color: #dab00cff;
        color: #44380aff;
    }
    .card-overdue {
        border-color: #d946ef;
        color: #7a135d;
    }
    .card-rejected {
        border-color: #2fcfc5ff;
        color: #0a1347;
    }
    .card-title {
        font-size: 1rem;
        font-weight: 600;
        margin: 3px 0 6px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
    }
    .card-value {
        font-weight: bold;
        font-size: 1.6rem;
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
        color: #2c3e50;
    }
    canvas {
        width: 100% !important;
        max-height: 210px;
        height: auto !important;
    }
    @media (max-width: 768px) {
        canvas { max-height: 190px; }
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
  <h1>Manager Dashboard</h1>

<div class="manager-container">
    <div style="display:flex; justify-content:flex-end; margin-bottom: 10px;">
        <form method="GET" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <label style="font-weight:800; color:#334155;">Filter</label>
            <select name="month" onchange="this.form.submit()" style="padding:8px 10px; border-radius:10px; border:2px solid #a5d817; font-weight:800;">
                <option value="this_year" {{ ($monthFilter ?? 'this_year') === 'this_year' ? 'selected' : '' }}>This Year</option>
                <option value="this_quarter" {{ ($monthFilter ?? '') === 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                <option value="this_month" {{ ($monthFilter ?? '') === 'this_month' ? 'selected' : '' }}>This Month</option>
                @php $nowY = (int) \Illuminate\Support\Carbon::now()->year; @endphp
                @foreach(range($nowY, $nowY - 5) as $y)
                    <option value="{{ $y }}" {{ (string) ($monthFilter ?? '') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <noscript><button type="submit">Apply</button></noscript>
        </form>
    </div>
  
    
    <div class="cards">
    <a href="{{ route('manager.requests.index', ['status' => 'all', 'month' => ($monthFilter ?? 'this_year')]) }}" class="card card-all">
        <div class="card-title">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
            </svg>
            All Requests
        </div>
        <div class="card-value">{{ $allRequestsCount }}</div>
    </a>

    <a href="{{ route('manager.requests.index', ['status' => 'pending', 'month' => ($monthFilter ?? 'this_year')]) }}" class="card card-pending">
        <div class="card-title">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Pending Requests
        </div>
        <div class="card-value">{{ $pendingRequestsCount }}</div>
    </a>

    <a href="{{ route('manager.requests.index', ['status' => 'overdue', 'month' => ($monthFilter ?? 'this_year')]) }}" class="card card-overdue">
        <div class="card-title">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Overdue Requests
        </div>
        <div class="card-value">{{ $overduePendingCount ?? 0 }}</div>
    </a>

    <a href="{{ route('manager.requests.index', ['status' => 'approved', 'month' => ($monthFilter ?? 'this_year')]) }}" class="card card-approved">
        <div class="card-title">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Approved Requests
        </div>
        <div class="card-value">{{ $approvedRequestsCount }}</div>
    </a>

    <a href="{{ route('manager.requests.index', ['status' => 'rejected', 'month' => ($monthFilter ?? 'this_year')]) }}" class="card card-rejected">
        <div class="card-title">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Rejected Requests
        </div>
        <div class="card-value">{{ $rejectedRequestsCount }}</div>
    </a>
</div>

    <div class="charts-container">
        <div class="chart-card">
            <h3>Leave Requests by Type</h3>
            <canvas id="leaveDoughnut"></canvas>
        </div>
        <div class="chart-card">
            <h3>Leave Requests by Status</h3>
            <canvas id="statusPie"></canvas>
        </div>
        <div class="chart-card">
            <h3>Monthly Leave Requests</h3>
            <canvas id="monthlyLine"></canvas>
        </div>
        <div class="chart-card">
            <h3>Requests by Department</h3>
            @if(empty($departmentLabels))
                <p style="margin:0; color:#64748b; font-size:14px;">No leave requests by department for this period.</p>
            @else
                <canvas id="departmentPolar"></canvas>
            @endif
        </div>
        <div class="chart-card">
            <h3>Requests by Designation</h3>
            <canvas id="designationBar"></canvas>
        </div>
        <div class="chart-card">
            <h3>Submission Pattern by Weekday</h3>
            <canvas id="weekdayLine"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    const getColorForLabel = (label) => hashToColor(normalizeKey(label) || String(label || '').trim().toLowerCase());

    Chart.defaults.font.family = '"Century Gothic", CenturyGothic, AppleGothic, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#2c3e50';
    Chart.defaults.maintainAspectRatio = false;

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
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { size: 14, weight: '600' },
                        color: '#2c3e50',
                        generateLabels: (chart) => {
                            const base = Chart.overrides.doughnut.plugins.legend.labels.generateLabels(chart);
                            return base.map((item) => {
                                const pct = (leaveTypePct[item.text] ?? 0).toFixed(1);
                                return { ...item, text: `${item.text} (${pct}%)` };
                            });
                        }
                    }
                },
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

    const statusPct = @json($statusPercentages ?? []);

    new Chart(document.getElementById('statusPie'), {
        type: 'pie',
        data: {
            labels: ['Pending', 'Rejected', 'Approved'],
            datasets: [{
                data: [
                    {{ $statusCounts['Pending'] ?? 0 }},
                    {{ $statusCounts['Rejected'] ?? 0 }},
                    {{ $statusCounts['Approved'] ?? 0 }}
                ],
                backgroundColor: [chartPalette.teal, chartPalette.coral, chartPalette.green],
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { size: 14, weight: '600' },
                        color: '#2c3e50',
                        generateLabels: (chart) => {
                            const base = Chart.overrides.pie.plugins.legend.labels.generateLabels(chart);
                            return base.map((item) => {
                                const pct = (statusPct[item.text] ?? 0).toFixed(1);
                                return { ...item, text: `${item.text} (${pct}%)` };
                            });
                        }
                    }
                },
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

    const monthlyColor = chartPalette.pink;
    new Chart(document.getElementById('monthlyLine'), {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Total Requests',
                data: @json($barLabels).map((label, i) => ({ x: label, y: @json($barData)[i] })),
                backgroundColor: monthlyColor,
                borderColor: monthlyColor,
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
                x: { type: 'category', ticks: { maxRotation: 0, minRotation: 0 } },
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
        new Chart(deptPolarCanvas, {
            type: 'polarArea',
            data: {
                labels: deptLabels,
                datasets: [{
                    data: deptData,
                    backgroundColor: deptLabels.map((d) => hexToRgba(getColorForLabel(d), 0.65)),
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 14, weight: '600' },
                            color: '#2c3e50',
                            generateLabels: (chart) => {
                                const base = polarLegendGen ? polarLegendGen(chart) : [];
                                return base.map((item) => {
                                    const pct = (deptPct[item.text] ?? 0).toFixed(1);
                                    return { ...item, text: `${item.text} (${pct}%)` };
                                });
                            }
                        }
                    },
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

    new Chart(document.getElementById('weekdayLine'), {
        type: 'line',
        data: {
            labels: @json($weekdayLabels),
            datasets: [{
                label: 'Requests Submitted',
                data: @json($weekdayData),
                backgroundColor: hexToRgba(chartPalette.blue, 0.2),
                borderColor: chartPalette.blue,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { bodyFont: { size: 14 }, titleFont: { weight: '700' } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

});
</script>
@endsection
