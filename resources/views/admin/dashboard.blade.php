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
    padding: 16px 12px;
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
    min-height: 120px;
    min-width: 220px;
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
        font-size: 1.2rem;
        font-weight: 600;
        margin: 6px 0 12px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
    }
    .card-value {
        font-weight: bold;
        font-size: 2.0rem;
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
        padding: 12px 12px;
        border-radius: 24px;
        box-shadow: 0 6px 22px rgba(67, 83, 255, 0.12);
        border: 1px solid #dbe3f0;
        color: #222;
    }
    .chart-card h3 {
        margin: 0 0 10px 0;
        font-weight: 700;
        font-size: 1rem;
        color: #2c3e50;
    }
    canvas {
        width: 100% !important;
        max-height: 240px;
    }
    @media (max-width: 768px) {
        canvas {
            max-height: 220px;
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
    <div class="cards" id="cardsContainer" role="list">

        <a href="{{ route('admin.requests', ['status' => 'all']) }}" role="listitem" aria-label="All leave requests" class="card card-all" draggable="true">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                All Requests
            </div>
            <div class="card-value">{{ $allRequestsCount ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.requests', ['status' => 'pending']) }}" role="listitem" aria-label="Pending leave requests" class="card card-pending" draggable="true">
            <div class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                </svg>
                Pending Requests
            </div>
            <div class="card-value">{{ $pendingRequestsCount }}</div>
        </a>

        <a href="{{ route('admin.requests', ['status' => 'overdue']) }}" role="listitem" aria-label="Overdue leave requests" class="card card-overdue" draggable="true">
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
            <canvas id="departmentPolar"></canvas>
        </div>
        <div class="chart-card">
            <h3>Top Leave Types</h3>
            <canvas id="topTypesBar"></canvas>
        </div>
        <div class="chart-card">
            <h3>Status Radar</h3>
            <canvas id="statusRadar"></canvas>
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

    const chartPalette = {
        blue: '#3b82f6',
        purple: '#8b5cf6',
        teal: '#06b6d4',
        orange: '#f59e0b',
        coral: '#F08080',
        pink: '#ec4899',
        magenta: '#d946ef',
        slate: '#64748b',
        green: '#2ecc71'
    };
    const fallbackColors = [
        chartPalette.blue,
        chartPalette.purple,
        chartPalette.teal,
        chartPalette.orange,
        chartPalette.magenta,
        chartPalette.coral,
        chartPalette.slate
    ];
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
    const fixedTypeColors = {
        annual: chartPalette.teal,
        sick: chartPalette.magenta,
        maternity: chartPalette.coral,
        paternity: chartPalette.orange,
        bereavement: chartPalette.purple,
        unpaid: chartPalette.slate,
        compassionate: chartPalette.blue,
        study: '#0ea5e9',
        urgent: '#fb923c'
    };
    const colorForKey = (key) => {
        if (!key) return chartPalette.slate;
        return fallbackColors[hashString(key) % fallbackColors.length];
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
                legend: { position: 'bottom', labels: { font: { size: 14, weight: '600' }, color: '#2c3e50' } },
                tooltip: { bodyFont: { size: 14 }, titleFont: { weight: '700' } }
            }
        }
    });

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
                legend: { position: 'bottom', labels: { font: { size: 14, weight: '600' }, color: '#2c3e50' } },
                tooltip: { bodyFont: { size: 14 }, titleFont: { weight: '700' } }
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

    new Chart(document.getElementById('departmentPolar'), {
        type: 'polarArea',
        data: {
            labels: @json($departmentLabels),
            datasets: [{
                data: @json($departmentData),
                backgroundColor: @json($departmentLabels).map((d) => hexToRgba(colorForKey(String(d || '').trim().toLowerCase()), 0.65)),
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 14, weight: '600' }, color: '#2c3e50' } },
                tooltip: { bodyFont: { size: 14 }, titleFont: { weight: '700' } }
            }
        }
    });

    const topBarColor = chartPalette.purple;
    new Chart(document.getElementById('topTypesBar'), {
        type: 'bar',
        data: {
            labels: leaveTypeLabels,
            datasets: [{
                label: 'Requests',
                data: leaveTypeData,
                backgroundColor: topBarColor,
                borderColor: topBarColor,
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { bodyFont: { size: 14 }, titleFont: { weight: '700' } }
            },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    const statusRadarColor = chartPalette.blue;
    new Chart(document.getElementById('statusRadar'), {
        type: 'radar',
        data: {
            labels: ['Pending', 'Rejected', 'Approved'],
            datasets: [{
                label: 'Requests',
                data: [
                    {{ $statusCounts['pending'] ?? $statusCounts['Pending'] ?? 0 }},
                    {{ $statusCounts['rejected'] ?? $statusCounts['Rejected'] ?? 0 }},
                    {{ $statusCounts['approved'] ?? $statusCounts['Approved'] ?? 0 }}
                ],
                borderColor: statusRadarColor,
                backgroundColor: hexToRgba(statusRadarColor, 0.25),
                pointBackgroundColor: statusRadarColor,
                pointBorderColor: '#fff',
                pointHoverBorderColor: '#fff',
                pointHoverBackgroundColor: statusRadarColor,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { bodyFont: { size: 14 }, titleFont: { weight: '700' } }
            },
            scales: {
                r: {
                    beginAtZero: true,
                    ticks: { display: false }
                }
            }
        }
    });
});
</script>
@endsection
