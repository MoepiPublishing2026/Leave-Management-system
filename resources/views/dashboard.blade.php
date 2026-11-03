@extends($layout ?? 'layouts.app')

@section('title', $pageTitle ?? 'Employee Dashboard')

@section('content')
@include('partials.dashboard-status-card-styles')
@php
    $user = auth()->user();
    $balanceRoute = \App\Support\LeaveApplicant::balanceRoute();
    $statusRoute = 'leave.status';
    $myRequestsRoute = \App\Support\LeaveApplicant::myRequestsRoute($user);
    $applyRoute = \App\Support\LeaveApplicant::applyRoute($user);
    $calendarRoute = \App\Support\LeaveApplicant::personalCalendarRoute($user);
@endphp
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  html body { font-family: 'Poppins', 'Segoe UI', sans-serif; }
  body, html {
    margin: 0;
    padding: 0;
  }
  .dashboard-container {
    font-family: 'Poppins', 'Segoe UI', sans-serif;
    width: 100%;
    max-width: 1200px;
    box-sizing: border-box;
    padding: 20px 22px 18px;
    background: white;
    border: 1px solid #d3d8de;
    border-radius: 14px;
    box-shadow: 0 0 14px rgba(0, 0, 0, 0.16);
    margin: 0 auto 56px;
  }
  @media (max-width: 768px) {
    .dashboard-container {
      padding: 14px 12px 22px;
    }
    h1 {
      font-size: 28px;
      padding-left: 8px;
      margin-bottom: 18px;
    }
    .leave-balance-card {
      padding: 18px 16px 20px;
    }
    .leave-balance-title {
      font-size: 1.2rem;
    }
    .leave-balance-list.open {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 14px;
    }
    .leave-balance-item {
      padding: 16px 12px;
      font-size: 0.95rem;
      min-height: 82px;
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
  @media (max-width: 420px) {
    .leave-balance-list.open {
      grid-template-columns: 1fr;
      gap: 10px;
    }
    .leave-balance-item {
      padding: 12px 14px;
      font-size: 0.95rem;
    }
  }
  h1 {
    font-weight: 700;
    font-size: 38px;
    font-family: 'Century Gothic', 'Poppins', sans-serif;
    color: #253141;
    margin: 0 0 22px 0;
    padding: 0;
    letter-spacing: 0.04em;
  }

  .leave-balance-card {
    background: #e6ecef;
    border-radius: 22px;
    padding: 22px 24px 26px;
    box-shadow: none;
    color: #4d5d68;
    cursor: pointer;
    max-width: 1200px;
    margin-bottom: 22px;
    border: none;
  }
  .leave-balance-card:hover {
    box-shadow: none;
  }
  .leave-balance-title {
    font-size: 1.35rem;
    font-weight: 600;
    margin-bottom: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #5a6a74;
    letter-spacing: 0;
  }
  .leave-balance-title .expand-icon {
    font-size: 1.2rem;
    color: #263041;
    line-height: 1;
  }
  .leave-balance-list {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s ease;
    padding-left: 0;
    list-style: none;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px 16px;
    align-items: stretch;
    grid-auto-rows: 1fr;
  }
  .leave-balance-list.open {
    max-height: 1200px;
    margin-top: 18px;
    overflow: visible;
    padding-top: 2px;
  }
  .leave-balance-item {
    background-color: #fff;
    border-radius: 16px;
    padding: 20px 14px;
    display: flex;
    justify-content: center;
    align-items: center;
    min-width: 0;
    min-height: 88px;
    height: 100%;
    box-sizing: border-box;
    font-size: 0.98rem;
    font-weight: 500;
    color: #4a5560;
    box-shadow: 0 10px 22px rgba(90, 110, 125, 0.16);
    border: 1px solid #c5ccd4;
  }
  .leave-balance-item:hover {
    box-shadow: 0 12px 24px rgba(90, 110, 125, 0.2);
  }
  .leave-balance-item.disabled {
    background: #d4dbe0;
    color: #6b767e;
    box-shadow: 0 4px 10px rgba(90, 110, 125, 0.08);
    border: 1px solid #b7c0c8;
    cursor: not-allowed;
  }
  .leave-balance-item.disabled:hover {
    cursor: not-allowed;
    box-shadow: 0 4px 10px rgba(90, 110, 125, 0.08);
    transform: none;
  }
  .leave-type {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    text-align: center;
    white-space: normal;
    overflow-wrap: break-word;
    word-break: normal;
    line-height: 1.35;
    hyphens: auto;
    max-width: 100%;
    padding: 0 4px;
  }
  .leave-type-unavailable {
    display: block;
    font-size: 0.78rem;
    font-weight: 700;
    color: #8a5a5a;
    letter-spacing: 0.02em;
  }
  @media (max-width: 1100px) {
    .leave-balance-list.open {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 14px;
    }
  }

  .cards {
    display: flex;
    flex-wrap: nowrap;
    align-items: stretch;
    gap: 12px;
    margin-bottom: 18px;
    width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    padding: 0 0 8px;
  }
  .dashboard-container .cards .card {
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
    cursor: pointer;
  }
  .dashboard-container .cards .card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25) !important;
    transform: none;
  }
  .dashboard-container .cards .card.card-all      { background: #4fe6c5 !important; }
  .dashboard-container .cards .card.card-pending  { background: #f2b96b !important; }
  .dashboard-container .cards .card.card-overdue  { background: #ab8cf5 !important; }
  .dashboard-container .cards .card.card-approved { background: #a03d85 !important; }
  .dashboard-container .cards .card.card-rejected  { background: #94c4c8 !important; }
  .dashboard-container .cards .card.card-withdrawn { background: #b9d84a !important; }
  .card .card-title svg,
  .card .card-title .icon {
    display: none;
  }
  .card .card-title {
    justify-content: center;
    text-align: center;
  }
  .card-title {
    font-size: 0.85rem;
    font-weight: 400;
    margin: 2px 0 6px 0;
    min-height: 0;
    line-height: 1.2;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 8px;
  }
  .card-value {
    font-weight: bold;
    font-size: 1.4rem;
    line-height: 1;
    margin: 0;
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
  .dashboard-container .chart-body--ring { height: 214px; }
  .dashboard-container .chart-body--ring canvas { max-height: 214px; }
  .dashboard-container .chart-body--plot { height: 186px; }
  @media (max-width: 1200px) {
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
    font-size: 0.95rem;
    color: #253141;
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
    font-weight: 700;
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
    font-weight: 700;
    color: #0f172a;
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
  .dashboard-top-bar {
    max-width: 1200px;
    margin: 0 auto 22px;
    padding: 0 30px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    box-sizing: border-box;
  }
  .dashboard-top-bar h1 {
    margin: 0;
  }
  .dashboard-top-bar h1 {
    margin: 0;
  }
    /* ===== Prototype match: My Leave Dashboard tabs ===== */
  .dashboard-container .mgr-quick-links {
    display: flex !important;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 14px 36px;
    margin: 6px 0 26px !important;
    padding: 0 !important;
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
  }
  .dashboard-container .mgr-quick-links .mgr-quick-link {
    font-family: 'Poppins', 'Segoe UI', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    color: #253141 !important;
    text-decoration: none !important;
    background: transparent !important;
    border: 0 !important;
    border-radius: 8px;
    padding: 6px 16px;
    box-shadow: none !important;
    transition: background-color 0.15s ease, color 0.15s ease;
  }
  .dashboard-container .mgr-quick-links .mgr-quick-link:hover,
  .dashboard-container .mgr-quick-links .mgr-quick-link:focus,
  .dashboard-container .mgr-quick-links .mgr-quick-link.active {
    background: #d9253c !important;
    color: #fff !important;
  }
</style>

@if(!empty($teamDashboardUrl))
<div class="mgr-page-header">
  <div class="mgr-page-header__main">
    <h1>{{ $pageTitle ?? 'Employee Dashboard' }}</h1>
  </div>
  @include('partials.manager-scope-switch', [
      'scope' => 'personal',
      'teamUrl' => $teamDashboardUrl,
      'teamLabel' => 'Dashboard',
  ])
</div>
@else
<div class="dashboard-top-bar">
  <h1>{{ $pageTitle ?? 'Employee Dashboard' }}</h1>
</div>
@endif
<div class="dashboard-container">

  @if(\App\Support\LeaveApplicant::isManager($user))
      <nav class="mgr-quick-links" aria-label="My leave pages">
      <a href="{{ route($myRequestsRoute, ['status' => 'all']) }}" class="mgr-quick-link {{ request()->routeIs($myRequestsRoute) ? 'active' : '' }}">My requests</a>
      <a href="{{ route($calendarRoute) }}" class="mgr-quick-link {{ request()->routeIs($calendarRoute) ? 'active' : '' }}">My calendar</a>
      <a href="{{ route($applyRoute) }}" class="mgr-quick-link {{ request()->routeIs($applyRoute) ? 'active' : '' }}">Apply for leave</a>
      <a href="{{ route($balanceRoute) }}" class="mgr-quick-link {{ request()->routeIs($balanceRoute) ? 'active' : '' }}">Leave balances</a>
    </nav>
  @endif

  <div id="leaveBalanceCard" class="leave-balance-card open" role="button" tabindex="0" aria-expanded="true" aria-controls="leaveBalanceList">
    <div class="leave-balance-title">
      Your Leave Balances
      <span class="expand-icon">&#9650;</span>
    </div>
    <ul id="leaveBalanceList" class="leave-balance-list open" aria-hidden="false">
      @php
        $annualKey = array_key_exists('Annual', $leaveBalances) ? 'Annual' : (array_key_exists('Annual Leave', $leaveBalances) ? 'Annual Leave' : 'Annual');
        $annualBalance = $leaveBalances[$annualKey] ?? 0;
      @endphp
      @foreach ($leaveBalances as $type => $daysLeft)
        @php
          $isParentalBlocked = \App\Support\ParentalLeaveRules::isBlockedForUser((int) auth()->id(), (string) $type);
          $isTypeExhausted = (float) $daysLeft <= 0;
          $isAnnualDisabled = $annualBalance <= 0 && \App\Support\AnnualLeaveRules::countsAgainstAnnual((string) $type);
          $isDisabled = $isTypeExhausted || $isAnnualDisabled || $isParentalBlocked;
          $typeName = trim((string) $type);
          $displayType = preg_match('/\bleave\s*$/i', $typeName) ? $typeName : $typeName.' Leave';
        @endphp
        <li class="leave-balance-item {{ $isDisabled ? 'disabled' : '' }}"
            title="{{ $displayType }} — {{ $daysLeft }} day{{ $daysLeft == 1 ? '' : 's' }} remaining{{ $isDisabled ? ' (Unavailable)' : '' }}"
            aria-label="{{ $displayType }}, {{ $daysLeft }} days remaining{{ $isDisabled ? ', unavailable' : '' }}"
            @if($isDisabled) aria-disabled="true" @endif
            onclick="{{ $isDisabled ? 'event.stopPropagation(); return false;' : 'event.stopPropagation(); window.location=\'' . route($balanceRoute, ['type' => $type]) . '\'' }}"
            style="cursor: {{ $isDisabled ? 'not-allowed' : 'pointer' }};">
          <span class="leave-type">
            {{ $displayType }}
            @if($isDisabled)
              <span class="leave-type-unavailable">Unavailable</span>
            @endif
          </span>
        </li>
      @endforeach
    </ul>
  </div>

  <div class="cards" id="cardsContainer" role="list">
    <a href="{{ route($statusRoute, ['status' => 'all']) }}" role="listitem" aria-label="All leave requests" class="card card-all">
      <div class="card-title">All Requests</div>
      <div class="card-value">{{ $allRequestsCount ?? 0 }}</div>
    </a>

    <a href="{{ route($statusRoute, ['status' => 'pending']) }}" role="listitem" aria-label="Pending requests count" class="card card-pending">
      <div class="card-title">Pending Requests</div>
      <div class="card-value">{{ $pendingCount }}</div>
    </a>

    <a href="{{ route($statusRoute, ['status' => 'overdue']) }}" role="listitem" aria-label="Overdue requests count" class="card card-overdue">
      <div class="card-title">Overdue Requests</div>
      <div class="card-value">{{ $overduePendingCount ?? 0 }}</div>
    </a>

    <a href="{{ route($statusRoute, ['status' => 'approved']) }}" role="listitem" aria-label="Approved requests count" class="card card-approved">
      <div class="card-title">Approved Requests</div>
      <div class="card-value">{{ $approvedCount }}</div>
    </a>

    <a href="{{ route($statusRoute, ['status' => 'rejected']) }}" role="listitem" aria-label="Rejected requests count" class="card card-rejected">
      <div class="card-title">Rejected Requests</div>
      <div class="card-value">{{ $rejectedCount }}</div>
    </a>

    <a href="{{ route($statusRoute, ['status' => 'withdrawn']) }}" role="listitem" aria-label="Withdrawn requests count" class="card card-withdrawn">
      <div class="card-title">Withdrawn Requests</div>
      <div class="card-value">{{ $withdrawnCount ?? 0 }}</div>
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
      <h3>Monthly Applications Submitted ({{ \App\Support\LeaveYear::label((int) ($year ?? now()->year)) }})</h3>
      <div class="chart-body chart-body--plot">
        <canvas id="monthlyLine"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <h3>Paid vs Unpaid Leave</h3>
      <div class="chart-body chart-body--ring">
        <canvas id="payStatusPolar"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <h3>Monthly Requests by Status ({{ \App\Support\LeaveYear::label((int) ($year ?? now()->year)) }})</h3>
      <div class="chart-body chart-body--plot">
        <canvas id="monthlyStatusStackedBar"></canvas>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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

    window.chartBoldFontSpec = { family: "'Poppins', sans-serif", size: 12, weight: 'bold', style: 'normal' };
    window.chartAxisTicksBold = { color: window.chartTextColor, font: { ...window.chartBoldFontSpec } };
    window.applyBoldChartDefaults();
    window.finalizeChartDefaults();

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

    const leaveTypes = @json(array_keys($leaveTypeCounts));
    const leaveTypeData = @json(array_values($leaveTypeCounts));
    const leaveTypePct = @json($leaveTypePercentages ?? []);
    const leaveTypeColors = leaveTypes.map((t, i) => protoColors[i % protoColors.length]);

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
    const monthlyWithdrawn = padMonthlySeries(@json($monthlyWithdrawnData));

    new Chart(document.getElementById('leaveDoughnut'), {
      type: 'doughnut',
      data: {
        labels: leaveTypes,
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
                const perc = (leaveTypePct[ctx.label] ?? 0).toFixed(1);
                return `${ctx.label}: ${ctx.parsed} (${perc}%)`;
              }
            }
          }
        }
      })
    });

    const statusPct = @json($statusPercentages ?? []);
    let statusList = [
      { label: 'Pending',  value: {{ (int) $pendingCount }} },
      { label: 'Overdue',  value: {{ (int) ($overduePendingCount ?? 0) }} },
      { label: 'Rejected', value: {{ (int) $rejectedCount }} },
      { label: 'Approved', value: {{ (int) $approvedCount }} }
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
                const perc = (statusPct[ctx.label] ?? 0).toFixed(1);
                return `${ctx.label}: ${ctx.parsed} (${perc}%)`;
              }
            }
          }
        }
      })
    });

    const monthlyLineChart = new Chart(document.getElementById('monthlyLine'), {
      type: 'line',
      data: {
        labels: monthlyLabels,
        datasets: [{
          label: 'Total Requests',
          data: monthlyTotals,
          backgroundColor: hexToRgba(blue, 0.2),
          borderColor: blue,
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
                const perc = (payStatusPct[ctx.label] ?? 0).toFixed(1);
                return `${ctx.label}: ${ctx.raw} (${perc}%)`;
              }
            }
          }
        }
      })
    });

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

    const leaveBalances = @json($chartLeaveBalances ?? $leaveBalances);
    const balanceEntries = Object.entries(leaveBalances).map(([type, days]) => ({
      type,
      days: Number(days) || 0
    }));
    const maxDays = balanceEntries.reduce((m, it) => Math.max(m, it.days), 0) || 1;

    const pyramidRoot = document.getElementById('leaveBalancePyramid');
    const pyramidLegendRoot = document.getElementById('leaveBalancePyramidLegend');

    if (pyramidRoot && pyramidLegendRoot) {
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
    }

    window.replaceEmptyDashboardCharts([
      { id: 'leaveDoughnut', values: leaveTypeData, message: 'No leave requests by type yet.' },
      { id: 'statusPie', values: statusPieData, message: 'No leave requests by status yet.' },
      { id: 'monthlyLine', values: monthlyTotals, message: 'No applications submitted in this leave year.' },
      { id: 'payStatusPolar', values: payStatusData, message: 'No paid or unpaid leave yet.' },
      { id: 'monthlyStatusStackedBar', values: monthlyStatusDatasets, message: 'No monthly status data for this leave year.' },
    ]);

    const leaveBalanceCard = document.getElementById('leaveBalanceCard');
    const leaveBalanceList = document.getElementById('leaveBalanceList');

    const expandIcon = leaveBalanceCard.querySelector('.expand-icon');
    const syncBalanceToggle = (isOpen) => {
      leaveBalanceCard.classList.toggle('open', isOpen);
      leaveBalanceCard.setAttribute('aria-expanded', isOpen);
      leaveBalanceList.setAttribute('aria-hidden', !isOpen);
      if (expandIcon) {
        expandIcon.innerHTML = isOpen ? '&#9650;' : '&#9660;';
      }
    };
    leaveBalanceCard.addEventListener('click', () => {
      const isOpen = leaveBalanceList.classList.toggle('open');
      syncBalanceToggle(isOpen);
    });

    leaveBalanceCard.addEventListener('keydown', (event) => {
      if(event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        const isOpen = leaveBalanceList.classList.toggle('open');
        syncBalanceToggle(isOpen);
      }
    });
  });
</script>
@endsection