@extends('layouts.app')

@section('title', 'Employee Dashboard')

@section('content')
<style>
  body, html {
    margin: 0;
    padding: 0;
  }
  .dashboard-container {
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
  @media (max-width: 768px) {
    .dashboard-container {
      padding: 14px 12px 22px;
    }
    .dash-charts-grid {
      grid-template-columns: 1fr;
    }
    .chart-card {
      min-height: auto;
      overflow: visible;
    }
    .cards {
      padding-bottom: 12px;
    }
  }
  h1 {
    font-weight: 700;
    font-size: 36px;
    color: #2c3e50;
    margin: 0 0 24px 0;
    padding-left: 15px;
    letter-spacing: 0.04em;
  }

  .leave-balance-card {
    background: white;
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: 0 6px 20px rgba(32, 50, 93, 0.1);
    color: #680523;
    cursor: pointer;
    max-width: 1200px;
    margin-bottom: 24px;
    transition: box-shadow 0.3s ease;
    border-left: 5px solid #94068d;
    border-top: 1px solid #94068d;
    border-right: 1px solid #94068d;
    border-bottom: 1px solid #94068d;
  }
  .leave-balance-card:hover {
    box-shadow: 0 10px 40px rgba(32, 80, 110, 0.2);
    position: relative;
    z-index: 1;
  }
  .leave-balance-title {
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .leave-balance-list {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s ease;
    padding-left: 0;
    list-style: none;
    margin: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 18px 24px;
  }
  .leave-balance-list.open {
    max-height: 800px;
    margin-top: 12px;
    overflow: visible;
    padding-top: 4px;
  }
  .leave-balance-item {
    background-color: rgb(252, 213, 234);
    border-radius: 16px;
    padding: 14px 18px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-grow: 1;
    min-width: 200px;
    max-width: 280px;
    font-size: 1.1rem;
    font-weight: 600;
    color: #050505ff;
    box-shadow: 0 3px 7px rgba(13, 59, 53, 0.12);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-left: 5px solid #f00e65;
    border-top: 1px solid #f00e65;
    border-right: 1px solid #f00e65;
    border-bottom: 1px solid #f00e65;
  }
  .leave-balance-item:hover {
    box-shadow: 0 8px 20px rgba(13, 59, 53, 0.25);
  }
  .leave-balance-item.disabled {
    opacity: 0.55;
    filter: grayscale(0.25);
  }
  .leave-type {
    text-align: center;
    white-space: nowrap;
  }

  .cards {
    display: flex;
    flex-wrap: nowrap;
    gap: 12px;
    margin-bottom: 40px;
    width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    padding: 4px 2px 8px;
  }
  .card {
    background: #f8fafc;
    border-radius: 16px;
    padding: 12px 8px 10px;
    box-shadow: 0 6px 16px rgba(8, 8, 8, 0.15);
    border: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex: 1 1 0;
    min-width: 0;
    min-height: 96px;
    cursor: move;
    position: relative;
    transition: box-shadow 0.3s ease;
    text-decoration: none;
    box-sizing: border-box;
  }
  .card:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    z-index: 2;
  }
  .card-all { background: #00C0A3; border-color: #00C0A3; color: #fff; }
  .card-pending { background: #C291A4; border-color: #C291A4; color: #fff; }
  .card-approved { background: #E27A53; border-color: #E27A53; color: #fff; }
  .card-rejected { background: #87AFC7; border-color: #87AFC7; color: #fff; }
  .card-overdue { background: #C154C1; border-color: #C154C1; color: #fff; }
  .card-withdrawn { background: #D2B55B; border-color: #D2B55B; color: #fff; }
  .card .card-title,
  .card .card-value {
    color: #fff;
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
    align-items: stretch;
  }
  .chart-card {
    background: white;
    padding: 12px 12px;
    border-radius: 24px;
    box-shadow: 0 6px 22px rgba(67, 83, 255, 0.12);
    border: 1px solid #dbe3f0;
    color: #222;
    display: flex;
    flex-direction: column;
    min-height: 280px;
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
  .chart-body.pyramid-body {
    height: auto;
    flex: 1;
  }
  .chart-body canvas {
    width: 100% !important;
    height: 100% !important;
    max-height: none;
  }
  .pyramid {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    height: 100%;
    gap: 6px;
    padding: 6px 4px;
  }
  .pyramid-row {
    height: 16px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }
  .pyramid-legend {
    margin-top: 0;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px 12px;
    font-size: 0.8rem;
  }
  .pyramid-legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
  }
  .pyramid-swatch {
    width: 10px;
    height: 10px;
    border-radius: 3px;
    flex-shrink: 0;
  }
  .pyramid-legend-text {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    width: 100%;
    min-width: 0;
  }
  .pyramid-legend-type {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 600;
    color: #2c3e50;
  }
  .pyramid-legend-value {
    font-weight: 800;
    color: #0a1347;
    flex-shrink: 0;
  }
  .pyramid-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  svg.icon {
    width: 28px;
    height: 28px;
    flex-shrink: 0;
  }
</style>

<h1>Employee Dashboard</h1>
<div class="dashboard-container">

  <div id="leaveBalanceCard" class="leave-balance-card" role="button" tabindex="0" aria-expanded="false" aria-controls="leaveBalanceList">
    <div class="leave-balance-title">
      Your Leave Balances
      <span class="expand-icon">&#9660;</span>
    </div>
    <ul id="leaveBalanceList" class="leave-balance-list" aria-hidden="true">
      @php
        $annualKey = array_key_exists('Annual', $leaveBalances) ? 'Annual' : (array_key_exists('Annual Leave', $leaveBalances) ? 'Annual Leave' : 'Annual');
        $annualBalance = $leaveBalances[$annualKey] ?? 0;
        $notDisabledWhenAnnualExhausted = \App\Support\AnnualLeaveRules::notDisabledWhenAnnualExhaustedLabels();
      @endphp
      @foreach ($leaveBalances as $type => $daysLeft)
        @php
          $isDisabled = $annualBalance <= 0 && !in_array($type, $notDisabledWhenAnnualExhausted, true);
        @endphp
        <li class="leave-balance-item {{ $isDisabled ? 'disabled' : '' }}"
            title="{{ $type }} Leave — {{ $daysLeft }} day{{ $daysLeft == 1 ? '' : 's' }} remaining{{ $isDisabled ? ' (Disabled)' : '' }}"
            aria-label="{{ $type }} Leave, {{ $daysLeft }} days remaining{{ $isDisabled ? ', disabled' : '' }}"
            onclick="{{ $isDisabled ? '' : 'event.stopPropagation(); window.location=\'' . route('leave.balance', ['type' => $type]) . '\'' }}"
            style="{{ $isDisabled ? '' : 'cursor: pointer;' }}">
          <span class="leave-type">{{ $type }} Leave{{ $isDisabled ? ' (Disabled)' : '' }}</span>
        </li>
      @endforeach
    </ul>
  </div>

  <div class="cards" id="cardsContainer" role="list">
    <a href="{{ route('leave.status', ['status' => 'all']) }}" role="listitem" aria-label="All leave requests" class="card card-all">
      <div class="card-title">All Requests</div>
      <div class="card-value">{{ $allRequestsCount ?? 0 }}</div>
    </a>

    <a href="{{ route('leave.status', ['status' => 'pending']) }}" role="listitem" aria-label="Pending Leaves count" class="card card-pending">
      <div class="card-title">Pending Leaves</div>
      <div class="card-value">{{ $pendingCount }}</div>
    </a>

    <a href="{{ route('leave.status', ['status' => 'overdue']) }}" role="listitem" aria-label="Overdue Leaves count" class="card card-overdue">
      <div class="card-title">Overdue Leaves</div>
      <div class="card-value">{{ $overduePendingCount ?? 0 }}</div>
    </a>

    <a href="{{ route('leave.status', ['status' => 'approved']) }}" role="listitem" aria-label="Approved Leaves count" class="card card-approved">
      <div class="card-title">Approved Leaves</div>
      <div class="card-value">{{ $approvedCount }}</div>
    </a>

    <a href="{{ route('leave.status', ['status' => 'rejected']) }}" role="listitem" aria-label="Rejected Leaves count" class="card card-rejected">
      <div class="card-title">Rejected Leaves</div>
      <div class="card-value">{{ $rejectedCount }}</div>
    </a>

    <a href="{{ route('leave.status', ['status' => 'withdrawn']) }}" role="listitem" aria-label="Withdrawn Leaves count" class="card card-withdrawn">
      <div class="card-title">Withdrawn Leaves</div>
      <div class="card-value">{{ $withdrawnCount ?? 0 }}</div>
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
      <h3>Monthly Applications Submitted ({{ $year }})</h3>
      <div class="chart-body chart-body--plot">
        <canvas id="monthlyLine"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <h3>Monthly Requests by Status ({{ $year }})</h3>
      <div class="chart-body chart-body--plot">
        <canvas id="monthlyStatusStackedBar"></canvas>
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
      <h3>Leave Utilization by Type</h3>
      <div class="chart-body chart-body--plot">
        <canvas id="utilizationBar"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <h3>Leave Balances Pyramid</h3>
      <div class="chart-body pyramid-body">
        <div id="leaveBalancePyramid" class="pyramid" role="img" aria-label="Leave balances pyramid"></div>
        <div id="leaveBalancePyramidLegend" class="pyramid-legend" aria-label="Leave balances legend"></div>
      </div>
    </div>
    <!-- <div class="chart-card">
      <h3>Leave Balance Share</h3>
      <div class="chart-body">
        <canvas id="balanceSharePie"></canvas>
      </div>
    </div> -->
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@include('partials.dashboard-charts-styles')
@include('partials.dashboard-chart-vars')
<script>
  document.addEventListener('DOMContentLoaded', () => {
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
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#2c3e50';
    Chart.defaults.maintainAspectRatio = false;

    const leaveTypes = @json(array_keys($leaveTypeCounts));
    const leaveTypeData = @json(array_values($leaveTypeCounts));
    const leaveTypePct = @json($leaveTypePercentages ?? []);
    const leaveTypeColors = leaveTypes.map(t => getColorForType(t));

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
    const monthlyWithdrawn = padMonthlySeries(@json($monthlyWithdrawnData));
    const monthAxisTicks = { autoSkip: false, maxRotation: 0, minRotation: 0 };

    new Chart(document.getElementById('leaveDoughnut'), {
      type: 'doughnut',
      data: {
        labels: leaveTypes,
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
          legend: { display: false },
          tooltip: {
            bodyFont: { size: 14 },
            titleFont: { weight: '700' },
            callbacks: {
              label: (ctx) => {
                const perc = (leaveTypePct[ctx.label] ?? 0).toFixed(1);
                return `${ctx.label}: ${ctx.parsed} (${perc}%)`;
              }
            }
          }
        }
      }
    });
    renderChartHtmlLegend('leaveDoughnutLegend', leaveTypes, leaveTypeColors, leaveTypePct);

    const monthlyColor = window.leaveMonthlyApplicationsColor;
    const statusPieLabels = window.leaveStatusPieLabels;
    const statusPieColors = window.statusColorsForLabels(statusPieLabels);
    const statusPct = @json($statusPercentages ?? []);
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
          },
          {
            label: 'Withdrawn',
            data: monthlyWithdrawn,
            backgroundColor: colorForStatus('Withdrawn'),
            borderColor: colorForStatus('Withdrawn'),
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

    new Chart(document.getElementById('statusPie'), {
      type: 'pie',
      data: {
        labels: statusPieLabels,
        datasets: [{
          data: [
            {{ $pendingCount }},
            {{ $overduePendingCount ?? 0 }},
            {{ $rejectedCount }},
            {{ $approvedCount }}
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

    const leaveUtilization = @json($leaveUtilization ?? []);
    const utilEntries = Array.isArray(leaveUtilization) ? leaveUtilization : [];

    const utilizationLabels = utilEntries.map(x => x.type);
    const utilizationUsed = utilEntries.map(x => Number(x.used) || 0);
    const utilizationRemaining = utilEntries.map(x => Number(x.remaining) || 0);
    const utilColors = utilizationLabels.map(t => getColorForType(t));

    new Chart(document.getElementById('utilizationBar'), {
      type: 'bar',
      data: {
        labels: utilizationLabels,
        datasets: [
          {
            label: 'Used',
            data: utilizationUsed,
            backgroundColor: utilColors.map(c => hexToRgba(c, 0.75)),
            borderWidth: 1
          },
          {
            label: 'Remaining',
            data: utilizationRemaining,
            backgroundColor: utilColors.map(c => hexToRgba(c, 0.35)),
            borderWidth: 1
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' },
          tooltip: { bodyFont: { size: 14 }, titleFont: { weight: '700' } }
        },
        scales: {
          x: { stacked: true },
          y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } }
        }
      }
    });

    const leaveBalances = @json($leaveBalances);
    const balanceEntries = Object.entries(leaveBalances).map(([type, days]) => ({
      type,
      days: Number(days) || 0
    }));
    const maxDays = balanceEntries.reduce((m, it) => Math.max(m, it.days), 0) || 1;

    const pyramidRoot = document.getElementById('leaveBalancePyramid');
    const pyramidLegendRoot = document.getElementById('leaveBalancePyramidLegend');

    balanceEntries.sort((a, b) => b.days - a.days).forEach((it) => {
      const widthPct = Math.max(12, Math.round((it.days / maxDays) * 100));
      const color = getColorForType(it.type);

      const row = document.createElement('div');
      row.className = 'pyramid-row';
      row.style.width = `${widthPct}%`;
      row.style.background = color;
      row.title = `${it.type} Leave: ${it.days} days`;
      row.setAttribute('aria-label', `${it.type} Leave: ${it.days} days`);
      pyramidRoot.appendChild(row);

      const item = document.createElement('div');
      item.className = 'pyramid-legend-item';

      const swatch = document.createElement('span');
      swatch.className = 'pyramid-swatch';
      swatch.style.background = color;
      item.appendChild(swatch);

      const text = document.createElement('div');
      text.className = 'pyramid-legend-text';

      const type = document.createElement('span');
      type.className = 'pyramid-legend-type';
      type.textContent = it.type;
      text.appendChild(type);

      const value = document.createElement('span');
      value.className = 'pyramid-legend-value';
      value.textContent = String(it.days);
      text.appendChild(value);

      item.appendChild(text);
      pyramidLegendRoot.appendChild(item);
    });

    const balanceShareLabels = balanceEntries.map(x => x.type);
    const balanceShareData = balanceEntries.map(x => x.days);

    new Chart(document.getElementById('balanceSharePie'), {
      type: 'pie',
      data: {
        labels: balanceShareLabels,
        datasets: [{
          data: balanceShareData,
          backgroundColor: balanceShareLabels.map(t => hexToRgba(getColorForType(t), 0.85)),
          borderColor: '#ffffff',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' },
          tooltip: { bodyFont: { size: 14 }, titleFont: { weight: '700' } }
        }
      }
    });

    const leaveBalanceCard = document.getElementById('leaveBalanceCard');
    const leaveBalanceList = document.getElementById('leaveBalanceList');

    leaveBalanceCard.addEventListener('click', () => {
      const isOpen = leaveBalanceList.classList.toggle('open');
      leaveBalanceCard.classList.toggle('open', isOpen);
      leaveBalanceCard.setAttribute('aria-expanded', isOpen);
      leaveBalanceList.setAttribute('aria-hidden', !isOpen);
    });

    leaveBalanceCard.addEventListener('keydown', (event) => {
      if(event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        const isOpen = leaveBalanceList.classList.toggle('open');
        leaveBalanceCard.classList.toggle('open', isOpen);
        leaveBalanceCard.setAttribute('aria-expanded', isOpen);
        leaveBalanceList.setAttribute('aria-hidden', !isOpen);
      }
    });
  });
</script>
@endsection