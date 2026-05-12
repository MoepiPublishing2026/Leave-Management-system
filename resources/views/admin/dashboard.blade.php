@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<style>
    body, html {
        margin: 0;
        padding: 0;
    }
    .admin-container {
        font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
        width: 100%;
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
        font-size: 40px;
        color: #2c3e50;
        margin: 0 0 24px 0;
    
        padding-left: 15px;
        letter-spacing: 0.04em;
    }
    .cards {
        display: flex;
        flex-wrap: nowrap;
        gap: 16px;
        margin-bottom: 40px;
        overflow-x: auto;
        padding-bottom: 8px;
    }
   .card {
    background: white;
    border-radius: 16px;
    padding: 12px 10px;
    box-shadow: 0 6px 16px rgba(8, 8, 8, 0.15);
    color: black;
    cursor: pointer;
    transition: box-shadow 0.3s ease;
    user-select: none;
    text-align: center;
    border-left: 5px solid; /* keep left border */
    border: 1px solid transparent; /* default transparent border */
    text-decoration: none;
    display: flex;
    flex-direction: column;
    justify-content: center;
    width: 210px;
    height: 96px;
    min-height: 96px;
    min-width: 210px;
    flex: 0 0 210px;
    box-sizing: border-box;
}

/* Override the border color for each card color class, using the same color for the 1px border */
.card-pending {
    border-left: 6px solid #dab00cff;
    border-top: 1px solid #dab00cff;
    border-right: 1px solid #dab00cff;
    border-bottom: 1px solid #dab00cff;
    color: #44380aff;
}

.card-approved {
    border-left: 6px solid #53e21aff;
    border-top: 1px solid #53e21aff;
    border-right: 1px solid #53e21aff;
    border-bottom: 1px solid #53e21aff;
    color: #1c7737ff;
}

.card-rejected {
    border-left: 5px solid #eb0e8fff;
    border-top: 1px solid #eb0e8fff;
    border-right: 1px solid #eb0e8fff;
    border-bottom: 1px solid #eb0e8fff;
    color: #881f54ff;
}

.card-active {
    border-left: 5px solid #2fcfc5ff;
    border-top: 1px solid #2fcfc5ff;
    border-right: 1px solid #2fcfc5ff;
    border-bottom: 1px solid #2fcfc5ff;
    color: #4b1010ff;
}
 .card-all {
    border-left: 6px solid #eb0e8fff;
    border-top: 1px solid #eb0e8fff;
    border-right: 1px solid #eb0e8fff;
    border-bottom: 1px solid #eb0e8fff;
    color: #881f54ff;
}
 .card-overdue {
    border-left: 6px solid #d946ef;
    border-top: 1px solid #d946ef;
    border-right: 1px solid #d946ef;
    border-bottom: 1px solid #d946ef;
    color: #7a135d;
}

    .card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    }
   
    .card-title {
        font-size: 1.05rem;
        font-weight: 600;
        margin: 4px 0 8px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
    }
    .card-value {
        font-weight: bold;
        font-size: 1.7rem;
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
    canvas {
        width: 100% !important;
        max-height: 210px;
    }
    @media (max-width: 768px) {
        canvas {
            max-height: 190px;
        }
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
    <div class="cards" id="cardsContainer" role="list">

        <a href="{{ route('admin.requests', ['status' => 'all', 'month' => ($monthFilter ?? 'this_year')]) }}" role="listitem" aria-label="All leave requests" class="card card-all" draggable="true">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                All Requests
            </div>
            <div class="card-value">{{ $allRequestsCount ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.requests', ['status' => 'pending', 'month' => ($monthFilter ?? 'this_year')]) }}" role="listitem" aria-label="Pending leave requests" class="card card-pending" draggable="true">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                </svg>
                Pending Requests
            </div>
            <div class="card-value">{{ $pendingRequestsCount }}</div>
        </a>

        <a href="{{ route('admin.requests', ['status' => 'overdue', 'month' => ($monthFilter ?? 'this_year')]) }}" role="listitem" aria-label="Overdue leave requests" class="card card-overdue" draggable="true">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Overdue Requests
            </div>
            <div class="card-value">{{ $overduePendingCount ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.calendar') }}" role="listitem" aria-label="Employees on leave today" class="card card-approved" draggable="true">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="24" height="24">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                On Leave Today
            </div>
            <div class="card-value">{{ $onLeaveTodayCount }}</div>
        </a>

        <a href="{{ route('admin.employees.index') }}" role="listitem" aria-label="Active employees count" class="card card-active" draggable="true">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="24" height="24">
                    <circle cx="12" cy="7" r="4" />
                    <path d="M5.5 21h13a2 2 0 0 0 2-2 7.5 7.5 0 0 0-15 0 2 2 0 0 0 2 2z" />
                </svg>
                Active Employees
            </div>
            <div class="card-value">{{ $activeEmployeesCount }}</div>
        </a>

    </div>

    <div class="dash-charts-grid">
        <div class="chart-card">
            <h3>Leave Requests by Type</h3>
            <canvas id="leaveDoughnut"></canvas>
        </div>
        <div class="chart-card">
            <h3>Monthly Leave Requests</h3>
            <canvas id="monthlyBar"></canvas>
        </div>
        <div class="chart-card">
            <h3>Leave Requests by Status</h3>
            <canvas id="statusPie"></canvas>
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

    Chart.defaults.font.family = '"Century Gothic", CenturyGothic, AppleGothic, sans-serif';
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

    // Leave Requests by Status pie chart
    new Chart(document.getElementById('statusPie'), {
        type: 'pie',
        data: {
            labels: ['Pending', 'Rejected', 'Approved'],
            datasets: [{
                data: [
                    {{ $statusCounts['pending'] ?? $statusCounts['Pending'] ?? 0 }},
                    {{ $statusCounts['rejected'] ?? $statusCounts['Rejected'] ?? 0 }},
                    {{ $statusCounts['approved'] ?? $statusCounts['Approved'] ?? 0 }}
                ],
                backgroundColor: [chartPalette.teal, chartPalette.coral, chartPalette.green],
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

    const monthlyLabels = @json($barLabels);
    const monthlyTotals = @json($barData);
    const monthlyColor = chartPalette.pink;
    const monthlyScatter = monthlyLabels.map((label, i) => ({ x: label, y: monthlyTotals[i] }));

    new Chart(document.getElementById('monthlyBar'), {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Total Requests',
                data: monthlyScatter,
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
                    backgroundColor: deptLabels.map((d) => hexToRgba(colorForKey(String(d || '').trim().toLowerCase()), 0.65)),
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
