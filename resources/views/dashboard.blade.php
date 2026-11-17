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
    $leaveYearLabel = \App\Support\LeaveYear::label((int) ($year ?? now()->year));
@endphp
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  html body { font-family: 'Inter', 'Segoe UI', sans-serif; }
  body, html { margin: 0; padding: 0; }

  /* ---------- Page title ---------- */
  .dashboard-top-bar {
    max-width: 1200px;
    margin: 0 auto 18px;
    padding: 0 6px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    box-sizing: border-box;
  }
  h1 {
    font-family: 'Century Gothic', 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 36px;
    color: #253141;
    margin: 0;
    padding: 0;
    letter-spacing: 0.01em;
  }

  /* ---------- Main white panel ---------- */
  .dashboard-container {
    font-family: 'Inter', 'Segoe UI', sans-serif;
    width: 100%;
    max-width: 1200px;
    box-sizing: border-box;
    padding: 20px 18px 28px;
    background: #fff;
    border: 1px solid #d3d8de;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.10);
    margin: 0 auto 56px;
  }

  /* ---------- Leave balances (collapsible) ---------- */
  .leave-balance-card {
    background: #d8e4e4;
    border-radius: 14px;
    padding: 22px 24px;
    color: #253141;
    cursor: pointer;
    margin-bottom: 24px;
    border: none;
    transition: box-shadow 0.25s ease;
  }
  .leave-balance-card.open {
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.18);
  }
  .leave-balance-title {
    font-size: 1.35rem;
    font-weight: 700;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #253141;
  }
  .leave-balance-title .expand-icon {
    font-size: 1.7rem;
    color: #1b5e83;
    line-height: 1;
  }
  .leave-balance-list {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s ease, margin-top 0.35s ease;
    padding: 0;
    list-style: none;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 22px 22px;
    grid-auto-rows: 1fr;
  }
  .leave-balance-list.open {
    max-height: 1200px;
    margin-top: 20px;
    overflow: visible;
    padding: 2px 4px 6px;
  }
  .leave-balance-item {
    background: #fff;
    border-radius: 14px;
    padding: 18px 12px;
    display: flex;
    justify-content: center;
    align-items: center;
    min-width: 0;
    min-height: 84px;
    box-sizing: border-box;
    font-size: 0.98rem;
    font-weight: 400;
    color: #3d4650;
    text-shadow: none;
    box-shadow: none;
    border: 1px solid #e3e7ea;
  }
  .leave-balance-item:hover { box-shadow: none; }
  .leave-balance-item.disabled {
    background: #e4e9ec;
    color: #7a848c;
    box-shadow: none;
    cursor: not-allowed;
  }
  .leave-type {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    text-align: center;
    line-height: 1.3;
    overflow-wrap: break-word;
    max-width: 100%;
  }
  .leave-type-unavailable {
    font-size: 0.75rem;
    font-weight: 700;
    color: #8a5a5a;
    text-shadow: none;
  }

  /* ---------- Status cards ---------- */
  .cards {
    display: flex;
    flex-wrap: nowrap;
    gap: 12px;
    margin-bottom: 36px;
    width: 100%;
    overflow-x: auto;
    padding: 0 0 6px;
  }
  .dashboard-container .cards .card {
    flex: 1 1 0;
    min-width: 120px;
    height: 88px;
    border: none !important;
    border-radius: 14px;
    box-shadow: none !important;
    padding: 10px 6px;
    text-align: center;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    justify-content: center;
    color: #fff;
    box-sizing: border-box;
  }
  .dashboard-container .cards .card:hover { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25) !important; }
  .dashboard-container .cards .card.card-all      { background: #4ee5c5 !important; }
  .dashboard-container .cards .card.card-pending  { background: #f3b86b !important; }
  .dashboard-container .cards .card.card-overdue  { background: #ae8df5 !important; }
  .dashboard-container .cards .card.card-approved { background: #a33d86 !important; }
  .dashboard-container .cards .card.card-rejected { background: #8fc4c7 !important; }
  .dashboard-container .cards .card.card-onleave  { background: #bad550 !important; }
  .dashboard-container .cards .card.card-onleave  { background: #b9d84a !important; }
  .card .card-title svg, .card .card-title .icon { display: none; }
  .card-title {
    font-size: 0.82rem;
    font-weight: 300;
    margin: 0 0 6px;
    line-height: 1.2;
    text-align: center;
  }
  .card-value {
    font-weight: 700;
    font-size: 1.35rem;
    line-height: 1;
    margin: 0;
  }

  /* ---------- Charts ---------- */
  .dash-charts-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px 14px;
    align-items: stretch;
  }
  .chart-card {
    background: #fff;
    padding: 16px 16px 12px;
    border-radius: 18px;
    box-shadow: 0 8px 22px rgba(38, 49, 66, 0.08);
    border: 1px solid #eef2f6;
    display: flex;
    flex-direction: column;
    min-height: 250px;
  }
  .chart-card h3 {
    margin: 0 0 8px;
    font-weight: 700;
    font-size: 0.92rem;
    color: #253141;
  }
  .chart-body { position: relative; width: 100%; }
  .dashboard-container .chart-body--ring { height: 180px; }
  .dashboard-container .chart-body--plot { height: 170px; }
  .dashboard-container .chart-body--plot-legend { height: 140px; }
  .chart-body canvas { width: 100% !important; height: 100% !important; }

  /* Custom legend (label then swatch, as in the design) */
  .status-legend {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 4px 22px;
    margin-top: 6px;
    font-size: 0.62rem;
    font-weight: 700;
    color: #253141;
  }
  .status-legend-item { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; }
  .status-legend-swatch { width: 12px; height: 12px; border-radius: 0; }

  /* Pyramid — same shape as My Leave Dashboard */
  html body .dashboard-container .pyramid-body {
    display: flex !important;
    flex-direction: column !important;
    gap: 16px !important;
    flex: 1 !important;
    width: 100% !important;
  }
  html body .dashboard-container .pyramid {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    gap: 7px !important;
    width: 100% !important;
    padding: 10px 6px 2px !important;
  }
  html body .dashboard-container .pyramid-row {
    height: 14px !important;
    border-radius: 8px !important;
    max-width: 100% !important;
    flex: 0 0 auto !important;
  }
  html body .dashboard-container .pyramid-legend {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 8px 28px !important;
    width: 100% !important;
    margin: 0 !important;
    font-family: 'Inter', 'Segoe UI', sans-serif !important;
    font-size: 0.72rem !important;
    font-weight: 700 !important;
    color: #253141 !important;
  }
  html body .dashboard-container .pyramid-legend-item {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    min-width: 0 !important;
  }
  html body .dashboard-container .pyramid-swatch {
    width: 10px !important;
    height: 10px !important;
    border-radius: 2px !important;
    flex-shrink: 0 !important;
  }
  html body .dashboard-container .pyramid-legend-text {
    display: flex !important;
    justify-content: space-between !important;
    gap: 8px !important;
    width: 100% !important;
    min-width: 0 !important;
  }
  html body .dashboard-container .pyramid-legend-type {
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
  }
  html body .dashboard-container .pyramid-legend-value {
    flex-shrink: 0 !important;
    padding-right: 0 !important;
  }

  /* ---------- Responsive ---------- */
  @media (max-width: 1100px) {
    .leave-balance-list.open { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
  }
  @media (max-width: 1000px) {
    .dash-charts-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }
  @media (max-width: 768px) {
    .dashboard-container { padding: 14px 12px 22px; }
    h1 { font-size: 28px; padding-left: 8px; }
    .leave-balance-card { padding: 18px 16px; }
    .leave-balance-title { font-size: 1.2rem; }
    .dash-charts-grid { grid-template-columns: 1fr; }
  }
  @media (max-width: 420px) {
    .leave-balance-list.open { grid-template-columns: 1fr; gap: 10px; }
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
      <a href="{{ route($myRequestsRoute, ['status' => 'all']) }}" class="mgr-quick-link">My requests</a>
      <a href="{{ route($calendarRoute) }}" class="mgr-quick-link">My calendar</a>
      <a href="{{ route($applyRoute) }}" class="mgr-quick-link">Apply for leave</a>
      <a href="{{ route($balanceRoute) }}" class="mgr-quick-link">Leave balances</a>
    </nav>
  @endif

  {{-- Leave balances: collapsed by default, like the design --}}
  <div id="leaveBalanceCard" class="leave-balance-card" role="button" tabindex="0" aria-expanded="false" aria-controls="leaveBalanceList">
    <div class="leave-balance-title">
      Your Leave Balances
      <span class="expand-icon">&#9660;</span>
    </div>
    <ul id="leaveBalanceList" class="leave-balance-list" aria-hidden="true">
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
            @if($isDisabled) aria-disabled="true" @else data-href="{{ route($balanceRoute, ['type' => $type]) }}" @endif
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

  {{-- Status cards --}}
  <div class="cards" id="cardsContainer" role="list">
    <a href="{{ route($statusRoute, ['status' => 'all']) }}" role="listitem" aria-label="All leave requests" class="card card-all">
      <div class="card-title">All Requests</div>
      <div class="card-value">{{ $allRequestsCount ?? 0 }}</div>
    </a>
    <a href="{{ route($statusRoute, ['status' => 'pending']) }}" role="listitem" aria-label="Pending requests count" class="card card-pending">
      <div class="card-title">Pending Requests</div>
      <div class="card-value">{{ $pendingCount ?? 0 }}</div>
    </a>
    <a href="{{ route($statusRoute, ['status' => 'overdue']) }}" role="listitem" aria-label="Overdue requests count" class="card card-overdue">
      <div class="card-title">Overdue Requests</div>
      <div class="card-value">{{ $overduePendingCount ?? 0 }}</div>
    </a>
    <a href="{{ route($statusRoute, ['status' => 'approved']) }}" role="listitem" aria-label="Approved requests count" class="card card-approved">
      <div class="card-title">Approved Requests</div>
      <div class="card-value">{{ $approvedCount ?? 0 }}</div>
    </a>
    <a href="{{ route($statusRoute, ['status' => 'rejected']) }}" role="listitem" aria-label="Rejected requests count" class="card card-rejected">
      <div class="card-title">Rejected Requests</div>
      <div class="card-value">{{ $rejectedCount ?? 0 }}</div>
    </a>
    <a href="{{ route($statusRoute, ['status' => 'withdrawn']) }}" role="listitem" aria-label="Withdrawn requests count" class="card card-onleave">
      <div class="card-title">Withdrawn Requests</div>
      <div class="card-value">{{ $withdrawnCount ?? 0 }}</div>
    </a>
  </div>

  {{-- Charts: row 1 = Type / Monthly line / Monthly status; row 2 = Status / Utilization / Pyramid --}}
  <div class="dash-charts-grid">
    <div class="chart-card">
      <h3>Leave Requests by Type</h3>
      <div class="chart-body chart-body--ring">
        <canvas id="leaveDoughnut"></canvas>
      </div>
    </div>

    <div class="chart-card">
      <h3>Monthly Application Submitted ({{ $leaveYearLabel }})</h3>
      <div class="chart-body chart-body--plot">
        <canvas id="monthlyLine"></canvas>
      </div>
    </div>

    <div class="chart-card">
      <h3>Monthly Requests by Status ({{ $leaveYearLabel }})</h3>
      <div class="chart-body chart-body--plot-legend">
        <canvas id="monthlyStatusStackedBar"></canvas>
      </div>
      <div id="monthlyStatusLegend" class="status-legend" aria-label="Monthly status legend"></div>
    </div>

    <div class="chart-card">
      <h3>Leave Requests by Status</h3>
      <div class="chart-body chart-body--ring">
        <canvas id="statusPie"></canvas>
      </div>
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
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@include('partials.dashboard-charts-styles')
@include('partials.dashboard-chart-vars')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // ---------- Helpers ----------
    const normalizeKey = (value) => {
      const v = String(value || '').trim().toLowerCase();
      if (!v) return '';
      return v.endsWith(' leave') ? v.slice(0, -5).trim() : v;
    };
    const displayLeaveName = (value) => {
      const v = String(value || '').trim();
      return /\bleave\s*$/i.test(v) ? v : `${v} Leave`;
    };

    const fixedTypeColors = @json(config('leave_type_colors.colors')) || {};
    const fallbackColors = @json(config('leave_type_colors.fallback_palette')) || ['#64748b'];
    const hashString = (str) => {
      let h = 0;
      for (let i = 0; i < str.length; i += 1) {
        h = ((h << 5) - h) + str.charCodeAt(i);
        h |= 0;
      }
      return Math.abs(h);
    };
    const configColorForType = (type) => {
      const key = normalizeKey(type);
      return fixedTypeColors[key] || fallbackColors[hashString(key || 'default') % fallbackColors.length];
    };

    window.chartBoldFontSpec = { family: "'Inter', 'Segoe UI', sans-serif", size: 10, weight: '500', style: 'normal' };
    window.chartAxisTicksBold = { color: window.chartTextColor, font: { ...window.chartBoldFontSpec } };
    window.applyBoldChartDefaults();
    window.finalizeChartDefaults();

    // ---------- Palettes (type / utilization unchanged; status pie from dict) ----------
    const protoColors = ['#4a7ff7', '#b08cf5', '#f5b96b', '#ffdc50', '#4fe6c5', '#94c4c8', '#a03d85', '#b9d84a'];
    const statusColors = Object.assign(
      { Withdrawn: '#fbe8b0' },
      @json(config('leave_status_colors.status_pie_personal'))
    );
    const monthlyLineColor = @json(config('leave_status_colors.monthly_applications_line'));

    const monthlyStatusColors = {
      Approved:  '#7ebade',
      Pending:   '#105078',
      Overdue:   '#f4a0ab',
      Rejected:  '#d4e04a',
      Withdrawn: '#fbe8b0',
    };

    const utilizationColors = {
      'sick': '#4a7ff7',
      'maternity': '#b08cf5',
      'paternity': '#f5b96b',
      'bereavement': '#ffdc50',
      'unpaid': '#f4a3ad',
      'family responsibility': '#f48bb8',
      'study': '#4fe6c5',
      'annual': '#7dce6a',
    };
    const utilizationOrder = [
      'sick',
      'maternity',
      'paternity',
      'bereavement',
      'unpaid',
      'family responsibility',
      'study',
      'annual',
    ];

    const pyramidColors = {
      'maternity': '#f5b96b',
      'paternity': '#94c4c8',
      'study': '#a03d85',
      'unpaid': '#f4a3ad',
      'annual': '#4fe6c5',
      'sick': '#b4570f',
      'family responsibility': '#f07a7a',
      'bereavement': '#b9d84a',
    };

    // ---------- Outside slice labels: "Name" + "xx.x%" ----------
    const sliceLabels = {
      id: 'sliceLabels',
      afterDatasetsDraw(chart) {
        const ctx = chart.ctx;
        const values = chart.data.datasets[0].data.map(Number);
        const names = chart.data.labels;
        const slices = chart.getDatasetMeta(0).data;
        const total = values.reduce((sum, v) => sum + v, 0);
        if (total === 0) return;

        ctx.save();
        ctx.font = '500 9px Inter, sans-serif';
        ctx.fillStyle = '#253141';
        ctx.textBaseline = 'middle';
        slices.forEach((slice, i) => {
          if (!values[i]) return;
          const mid = (slice.startAngle + slice.endAngle) / 2;
          const cos = Math.cos(mid);
          const x = slice.x + cos * (slice.outerRadius + 8);
          const y = slice.y + Math.sin(mid) * (slice.outerRadius + 14);
          ctx.textAlign = cos > 0.25 ? 'left' : (cos < -0.25 ? 'right' : 'center');
          const pct = (Math.round(values[i] / total * 1000) / 10) + '%';
          ctx.fillText(String(names[i]), x, y - 5);
          ctx.fillText(pct, x, y + 6);
        });
        ctx.restore();
      }
    };
    const roomForLabels = { padding: { top: 22, bottom: 22, left: 48, right: 48 } };

    const smallTicks = { font: { family: "'Inter', 'Segoe UI', sans-serif", size: 10, weight: '500' }, color: '#253141' };

    // ---------- Data from the controller ----------
    const leaveBalances = @json($chartLeaveBalances ?? $leaveBalances);
    const leaveTypeCounts = @json($leaveTypeCounts ?? []);
    const leaveTypes = Object.keys(leaveTypeCounts);
    const leaveTypeData = Object.values(leaveTypeCounts).map(Number);

    const allMonthLabels = @json($barLabels ?? \App\Support\LeaveYear::barLabels());
    const padMonthlySeries = (values) => {
      const series = Array.isArray(values) ? values.map(Number) : [];
      while (series.length < 12) series.push(0);
      return series.slice(0, 12);
    };
    const monthlyTotals    = padMonthlySeries(@json($barData ?? []));
    const monthlyApproved  = padMonthlySeries(@json($monthlyApprovedData ?? []));
    const monthlyPending   = padMonthlySeries(@json($monthlyPendingData ?? []));
    const monthlyOverdue   = padMonthlySeries(@json($monthlyOverdueData ?? []));
    const monthlyRejected  = padMonthlySeries(@json($monthlyRejectedData ?? []));
    const monthlyWithdrawn = padMonthlySeries(@json($monthlyWithdrawnData ?? []));

    // Days used per leave type. $leaveUtilization is a list of { type, used }.
    const utilizationRaw = @json($leaveUtilization ?? []);
    const utilizationByKey = {};
    (Array.isArray(utilizationRaw) ? utilizationRaw : Object.entries(utilizationRaw)).forEach((item) => {
      if (item && typeof item === 'object' && !Array.isArray(item) && item.type != null) {
        utilizationByKey[normalizeKey(item.type)] = Number(item.used) || 0;
        return;
      }
      if (Array.isArray(item)) {
        utilizationByKey[normalizeKey(item[0])] = Number(item[1]) || 0;
      }
    });

    // ---------- 1. Leave Requests by Type (doughnut) ----------
    new Chart(document.getElementById('leaveDoughnut'), {
      type: 'doughnut',
      data: {
        labels: leaveTypes,
        datasets: [{
          data: leaveTypeData,
          backgroundColor: leaveTypes.map((t, i) => protoColors[i % protoColors.length]),
          borderWidth: 0
        }]
      },
      plugins: [sliceLabels],
      options: mergeChartOptions({
        cutout: '50%',
        layout: roomForLabels,
        plugins: { legend: { display: false } }
      })
    });

    // ---------- 2. Monthly Application Submitted (line) ----------
    const monthlyLineChart = new Chart(document.getElementById('monthlyLine'), {
      type: 'line',
      data: {
        labels: allMonthLabels,
        datasets: [{
          label: 'Applications',
          data: monthlyTotals,
          borderColor: monthlyLineColor,
          backgroundColor: monthlyLineColor,
          borderWidth: 2,
          tension: 0.45,
          cubicInterpolationMode: 'monotone',
          fill: false,
          pointRadius: 4,
          pointHoverRadius: 5,
          pointBackgroundColor: monthlyLineColor,
          pointBorderColor: monthlyLineColor,
          pointBorderWidth: 0,
          capBezierPoints: true
        }]
      },
      options: mergeChartOptions({
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: smallTicks },
          y: { beginAtZero: true, grid: { color: '#d9d9d9' }, border: { display: false }, ticks: { ...smallTicks, precision: 0 } }
        }
      })
    });
    if (window.registerMonthAxisChart) window.registerMonthAxisChart(monthlyLineChart);

    // ---------- 3. Monthly Requests by Status (stacked bar + custom legend) ----------
    const monthlySeries = {
      Approved: monthlyApproved,
      Pending: monthlyPending,
      Overdue: monthlyOverdue,
      Rejected: monthlyRejected,
      Withdrawn: monthlyWithdrawn,
    };
    const monthlyStatusDatasets = Object.entries(monthlySeries).map(([label, data]) => ({
      label,
      data,
      backgroundColor: monthlyStatusColors[label],
      borderWidth: 0,
      borderRadius: 0,
      borderSkipped: false,
      maxBarThickness: 28,
      stack: 'status'
    }));

    const monthlyStatusChart = new Chart(document.getElementById('monthlyStatusStackedBar'), {
      type: 'bar',
      data: { labels: allMonthLabels, datasets: monthlyStatusDatasets },
      options: mergeChartOptions({
        plugins: { legend: { display: false } },
        scales: {
          x: { stacked: true, grid: { display: false }, ticks: smallTicks },
          y: { stacked: true, beginAtZero: true, grid: { color: '#d9d9d9' }, border: { display: false }, ticks: { ...smallTicks, precision: 0 } }
        }
      })
    });
    if (window.registerMonthAxisChart) window.registerMonthAxisChart(monthlyStatusChart);

    const legendRoot = document.getElementById('monthlyStatusLegend');
    const monthlyHasData = monthlyStatusDatasets.some((ds) => ds.data.some((v) => v > 0));
    if (legendRoot && monthlyHasData) {
      Object.keys(monthlySeries).forEach((label) => {
        const item = document.createElement('span');
        item.className = 'status-legend-item';
        const text = document.createElement('span');
        text.textContent = label;
        const swatch = document.createElement('span');
        swatch.className = 'status-legend-swatch';
        swatch.style.background = monthlyStatusColors[label];
        item.appendChild(swatch);
        item.appendChild(text);
        legendRoot.appendChild(item);
      });
    }

    // ---------- 4. Leave Requests by Status (pie) ----------
    const statusList = [
      { label: 'Pending',  value: {{ (int) ($pendingCount ?? 0) }} },
      { label: 'Approved', value: {{ (int) ($approvedCount ?? 0) }} },
      { label: 'Overdue',  value: {{ (int) ($overduePendingCount ?? 0) }} },
      { label: 'Rejected', value: {{ (int) ($rejectedCount ?? 0) }} }
    ];
    const statusPieLabels = statusList.map((s) => s.label);
    const statusPieData = statusList.map((s) => s.value);

    new Chart(document.getElementById('statusPie'), {
      type: 'pie',
      data: {
        labels: statusPieLabels,
        datasets: [{
          data: statusPieData,
          backgroundColor: statusPieLabels.map((l) => statusColors[l]),
          borderWidth: 0
        }]
      },
      plugins: [sliceLabels],
      options: mergeChartOptions({
        layout: roomForLabels,
        plugins: { legend: { display: false } }
      })
    });

    // ---------- 5. Leave Utilization by Type (bar) ----------
    const utilizationLabelByKey = {};
    Object.keys(leaveBalances).forEach((type) => {
      utilizationLabelByKey[normalizeKey(type)] = type;
    });
    Object.keys(utilizationByKey).forEach((key) => {
      if (!utilizationLabelByKey[key]) utilizationLabelByKey[key] = key;
    });
    const utilizationTypes = utilizationOrder
      .filter((key) => utilizationLabelByKey[key])
      .map((key) => utilizationLabelByKey[key]);
    Object.keys(utilizationLabelByKey).forEach((key) => {
      const type = utilizationLabelByKey[key];
      if (!utilizationTypes.includes(type)) utilizationTypes.push(type);
    });
    const utilizationData = utilizationTypes.map((t) => utilizationByKey[normalizeKey(t)] || 0);

    new Chart(document.getElementById('utilizationBar'), {
      type: 'bar',
      data: {
        labels: utilizationTypes,
        datasets: [{
          label: 'Days used',
          data: utilizationData,
          backgroundColor: utilizationTypes.map((t) => utilizationColors[normalizeKey(t)] || configColorForType(t)),
          borderWidth: 0,
          borderRadius: window.barTopRadius || { topLeft: 5, topRight: 5, bottomLeft: 0, bottomRight: 0 },
          borderSkipped: false,
          minBarLength: 8,
          maxBarThickness: 34,
          categoryPercentage: 0.72,
          barPercentage: 0.9
        }]
      },
      options: mergeChartOptions({
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { ...smallTicks, maxRotation: 45, minRotation: 45, autoSkip: false } },
          y: { beginAtZero: true, grid: { color: '#e6e6e6' }, border: { display: false }, ticks: { ...smallTicks, precision: 0, stepSize: 10 } }
        }
      })
    });

    // ---------- 6. Leave Balances Pyramid ----------
    const balanceEntries = Object.entries(leaveBalances).map(([type, days]) => ({ type, days: Number(days) || 0 }));
    const maxDays = balanceEntries.reduce((m, it) => Math.max(m, it.days), 0) || 1;
    const pyramidRoot = document.getElementById('leaveBalancePyramid');
    const pyramidLegendRoot = document.getElementById('leaveBalancePyramidLegend');

    if (pyramidRoot && pyramidLegendRoot) {
      balanceEntries.sort((a, b) => b.days - a.days).forEach((it) => {
        const color = pyramidColors[normalizeKey(it.type)] || configColorForType(it.type);
        const widthPct = Math.max(12, Math.round((it.days / maxDays) * 100));
        const label = `${displayLeaveName(it.type)}: ${it.days} days`;

        const row = document.createElement('div');
        row.className = 'pyramid-row';
        row.style.setProperty('width', `${widthPct}%`, 'important');
        row.style.background = color;
        row.title = label;
        row.setAttribute('aria-label', label);
        pyramidRoot.appendChild(row);

        const item = document.createElement('div');
        item.className = 'pyramid-legend-item';
        const swatch = document.createElement('span');
        swatch.className = 'pyramid-swatch';
        swatch.style.background = color;
        const text = document.createElement('div');
        text.className = 'pyramid-legend-text';
        const typeEl = document.createElement('span');
        typeEl.className = 'pyramid-legend-type';
        typeEl.textContent = it.type;
        const valueEl = document.createElement('span');
        valueEl.className = 'pyramid-legend-value';
        valueEl.textContent = String(it.days);
        text.appendChild(typeEl);
        text.appendChild(valueEl);
        item.appendChild(swatch);
        item.appendChild(text);
        pyramidLegendRoot.appendChild(item);
      });
    }

    // ---------- Empty states ----------
    window.replaceEmptyDashboardCharts([
      { id: 'leaveDoughnut', values: leaveTypeData, message: 'No leave requests by type yet.' },
      { id: 'monthlyLine', values: monthlyTotals, message: 'No applications submitted in this leave year.' },
      { id: 'monthlyStatusStackedBar', values: monthlyStatusDatasets, message: 'No monthly status data for this leave year.' },
      { id: 'statusPie', values: statusPieData, message: 'No leave requests by status yet.' },
    ]);

    // ---------- Leave balance tiles: navigate on click ----------
    document.querySelectorAll('.leave-balance-item[data-href]').forEach((tile) => {
      tile.addEventListener('click', (event) => {
        event.stopPropagation();
        window.location.href = tile.dataset.href;
      });
    });
    document.querySelectorAll('.leave-balance-item.disabled').forEach((tile) => {
      tile.addEventListener('click', (event) => event.stopPropagation());
    });

    // ---------- Collapse / expand balances ----------
    const leaveBalanceCard = document.getElementById('leaveBalanceCard');
    const leaveBalanceList = document.getElementById('leaveBalanceList');
    const expandIcon = leaveBalanceCard.querySelector('.expand-icon');

    const toggleBalances = () => {
      const isOpen = leaveBalanceList.classList.toggle('open');
      leaveBalanceCard.classList.toggle('open', isOpen);
      leaveBalanceCard.setAttribute('aria-expanded', String(isOpen));
      leaveBalanceList.setAttribute('aria-hidden', String(!isOpen));
      if (expandIcon) expandIcon.innerHTML = isOpen ? '&#9650;' : '&#9660;';
    };

    leaveBalanceCard.addEventListener('click', toggleBalances);
    leaveBalanceCard.addEventListener('keydown', (event) => {
      if (event.target !== leaveBalanceCard) return;
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        toggleBalances();
      }
    });
  });
</script>
@endsection