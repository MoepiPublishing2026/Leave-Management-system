@extends('layouts.app')

@section('title', 'Employee Dashboard')

@section('content')
<style>
  body, html {
    margin: 0;
    padding: 0;
  }
  .dashboard-container {
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
  @media (max-width: 768px) {
    .dashboard-container {
      padding: 14px 12px 22px;
    }
    .dash-charts-grid {
      grid-template-columns: 1fr;
    }
    .chart-card {
      min-height: 320px;
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
  
  /* Leave Balance Card */
  .leave-balance-card {
    background: white;
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: 0 6px 20px rgba(32, 50, 93, 0.1);
    color: #680523;
    cursor: pointer;
    user-select: none;
    max-width: 1200px;
    margin-bottom: 24px;
    transition: box-shadow 0.3s ease;
    border-left: 5px solid #94068d;  /* Left border is thicker */
    border-top: 1px solid #94068d;
    border-right: 1px solid #94068d;
    border-bottom: 1px solid #94068d;
  }
  .leave-balance-card:hover {
    box-shadow: 0 10px 40px rgba(32, 80, 110, 0.2);
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
  }
  .leave-balance-item {
    background-color: rgb(252, 213, 234);
    
    border-radius: 16px;
    padding: 18px 26px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-grow: 1;
    min-width: 230px;
    max-width: 320px;
    font-size: 1.25rem;
    font-weight: 600;
    color: #050505ff;
    box-shadow: 0 3px 7px rgba(13, 59, 53, 0.12);
    user-select: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden; border-left: 5px solid #f00e65;  /* Left border is thicker */
    border-top: 1px solid #f00e65;
    border-right: 1px solid #f00e65;
    border-bottom: 1px solid #f00e65;
  }
  .leave-balance-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(13, 59, 53, 0.25);
  }
  .leave-balance-item.disabled {
    opacity: 0.55;
    filter: grayscale(0.25);
  }
  .leave-balance-item.disabled .leave-days {
    color: #6b7280;
  }
  .leave-type {
    flex-basis: 60%;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }
  .leave-days {
    flex-basis: 40%;
    font-weight: 700;
    color: #850621;
    text-align: right;
    padding-left: 12px;
  }

  /* Status Cards */
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
    padding: 14px 10px 10px;
    box-shadow: 0 6px 16px rgba(8, 8, 8, 0.15);
    border: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 110px;
    min-width: 190px;
    flex: 1 1 190px;
    cursor: move;
    user-select: none;
    position: relative;
    transition: box-shadow 0.3s ease;
    border-left: 5px solid;
    text-decoration: none;
  }
  .card:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
  }
  .card-pending {
    border-color: #dab00cff;
    color: #44380aff;
  }
  .card-approved {
    border-color: #53e21aff;
    color: #1c7737ff;
  }
  .card-rejected {
    border-color: #eb0e8fff;
    color: #881f54ff;
  }
  .card-overdue {
    border-color: #d946ef;
    color: #7a135d;
  }
  .card-withdrawn {
    border-color: #2fcfc5ff;
    color: #0a1347;
  }
  .card-title {
    font-size: 1.08rem;
    font-weight: 600;
    margin: 4px 0 10px 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .card-value {
    font-weight: bold;
    font-size: 1.85rem;
    margin-top: 0;
  }

  /* Charts */
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
    height: 220px;
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
    background: linear-gradient(135deg, #b2cd16 0%, #0a1347 100%);
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
        $notDisabledWhenAnnualExhausted = ['Annual', 'Annual Leave', 'Sick', 'Bereavement', 'Maternity'];
      @endphp
      @foreach ($leaveBalances as $type => $daysLeft)
        @php
          $isDisabled = $annualBalance <= 0 && !in_array($type, $notDisabledWhenAnnualExhausted, true);
        @endphp
        <li class="leave-balance-item {{ $isDisabled ? 'disabled' : '' }}" title="{{ $type }} Leave" onclick="{{ $isDisabled ? '' : 'event.stopPropagation(); window.location=\'' . route('apply', ['leave_type' => $type]) . '\'' }}" style="{{ $isDisabled ? '' : 'cursor: pointer;' }}">
          <span class="leave-type">{{ $type }} Leave</span>
          <span class="leave-days">
            {{ $daysLeft }} days left{{ $isDisabled ? ' (Disabled)' : '' }}
          </span>
        </li>
      @endforeach
    </ul>
  </div>

  <div class="cards" id="cardsContainer" role="list">
    <a href="{{ route('leave.status', ['status' => 'pending']) }}" role="listitem" aria-label="Pending Leaves count" class="card card-pending" draggable="true" style="color:#a16207;">
      <div class="card-title">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="#facc15" viewBox="0 0 24 24" stroke-width="2" stroke="#facc15" stroke-linejoin="round" stroke-linecap="round">
          <circle cx="12" cy="12" r="10" />
          <path d="M12 8v4l3 3" />
        </svg>
        Pending Leaves
      </div>
      <div class="card-value">{{ $pendingCount }}</div>
    </a>

    <a href="{{ route('leave.status', ['status' => 'overdue']) }}" role="listitem" aria-label="Overdue Leaves count" class="card card-overdue" draggable="true">
      <div class="card-title">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="#d946ef" viewBox="0 0 24 24" stroke-width="2" stroke="#d946ef" stroke-linejoin="round" stroke-linecap="round">
          <circle cx="12" cy="12" r="10" />
          <path d="M12 8v4" />
          <path d="M12 16h.01" />
        </svg>
        Overdue Leaves
      </div>
      <div class="card-value">{{ $overduePendingCount ?? 0 }}</div>
    </a>

    <a href="{{ route('leave.status', ['status' => 'approved']) }}" role="listitem" aria-label="Approved Leaves count" class="card card-approved" draggable="true" style="color:#064e3b;">
      <div class="card-title">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="#10b981" viewBox="0 0 24 24" stroke-width="2" stroke="#10b981" stroke-linejoin="round" stroke-linecap="round">
          <path d="M5 13l4 4L19 7" />
        </svg>
        Approved Leaves
      </div>
      <div class="card-value">{{ $approvedCount }}</div>
    </a>

    <a href="{{ route('leave.status', ['status' => 'rejected']) }}" role="listitem" aria-label="Rejected Leaves count" class="card card-rejected" draggable="true" style="color:#7f1d1d;">
      <div class="card-title">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="#ef4444" viewBox="0 0 24 24" stroke-width="2" stroke="#ef4444" stroke-linejoin="round" stroke-linecap="round">
          <path d="M6 18L18 6M6 6l12 12" />
        </svg>
        Rejected Leaves
      </div>
      <div class="card-value">{{ $rejectedCount }}</div>
    </a>

    <a href="{{ route('leave.status', ['status' => 'withdrawn']) }}" role="listitem" aria-label="Withdrawn Leaves count" class="card card-withdrawn" draggable="true">
      <div class="card-title">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="#2fcfc5ff" viewBox="0 0 24 24" stroke-width="2" stroke="#2fcfc5ff" stroke-linejoin="round" stroke-linecap="round">
          <path d="M6 18L18 6M6 6l12 12" />
        </svg>
        Withdrawn Leaves
      </div>
      <div class="card-value">{{ $withdrawnCount ?? 0 }}</div>
    </a>
  </div>

  <div class="dash-charts-grid">
    <div class="chart-card">
      <h3>Leave Requests by Type</h3>
      <div class="chart-body">
        <canvas id="leaveDoughnut"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <h3>Monthly Leave Requests</h3>
      <div class="chart-body">
        <canvas id="monthlyBar"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <h3>Leave Requests by Status</h3>
      <div class="chart-body">
        <canvas id="statusRadar"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <h3>Top Leave Types</h3>
      <div class="chart-body">
        <canvas id="topTypesBar"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <h3>Leave Types Polar</h3>
      <div class="chart-body">
        <canvas id="leaveTypePolar"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <h3>Leave Balances Pyramid</h3>
      <div class="chart-body pyramid-body">
        <div id="leaveBalancePyramid" class="pyramid" role="img" aria-label="Leave balances pyramid"></div>
        <div id="leaveBalancePyramidLegend" class="pyramid-legend" aria-label="Leave balances legend"></div>
      </div>
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
    const hashString = (str) => {
      let h = 0;
      for (let i = 0; i < str.length; i += 1) {
        h = ((h << 5) - h) + str.charCodeAt(i);
        h |= 0;
      }
      return Math.abs(h);
    };
    const hashToColor = (key) => {
      if (!key) return chartPalette.slate;
      return fallbackColors[hashString(key) % fallbackColors.length];
    };
    const getColorForType = (type) => {
      const key = normalizeKey(type);
      return fixedTypeColors[key] || hashToColor(key);
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

    const leaveTypes = @json(array_keys($leaveTypeCounts));
    const leaveTypeColors = leaveTypes.map(t => getColorForType(t));

    new Chart(document.getElementById('leaveDoughnut'), {
      type: 'doughnut',
      data: {
        labels: leaveTypes,
        datasets: [{
          data: @json(array_values($leaveTypeCounts)),
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

    const statusRadarColor = chartPalette.blue;
    new Chart(document.getElementById('statusRadar'), {
      type: 'radar',
      data: {
        labels: ['Pending', 'Approved', 'Rejected', 'Withdrawn'],
        datasets: [{
          label: 'Requests',
          data: [
            {{ $pendingCount }},
            {{ $approvedCount }},
            {{ $rejectedCount }},
            {{ $withdrawnCount ?? 0 }}
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
          r: { beginAtZero: true, ticks: { display: false } }
        }
      }
    });

    const leaveTypeLabels = @json(array_keys($leaveTypeCounts));
    const leaveTypeData = @json(array_values($leaveTypeCounts));
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

    new Chart(document.getElementById('leaveTypePolar'), {
      type: 'polarArea',
      data: {
        labels: leaveTypes,
        datasets: [{
          data: @json(array_values($leaveTypeCounts)),
          backgroundColor: leaveTypeColors.map(c => hexToRgba(c, 0.7)),
          borderColor: leaveTypeColors,
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

    const leaveBalances = @json($leaveBalances);
    const pyramidItems = Object.entries(leaveBalances)
      .map(([type, days]) => ({ type, days: Number(days) || 0 }))
      .sort((a, b) => b.days - a.days);

    const pyramidRoot = document.getElementById('leaveBalancePyramid');
    const pyramidLegendRoot = document.getElementById('leaveBalancePyramidLegend');
    const maxDays = pyramidItems.reduce((m, it) => Math.max(m, it.days), 0) || 1;

    pyramidItems.forEach((it) => {
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
