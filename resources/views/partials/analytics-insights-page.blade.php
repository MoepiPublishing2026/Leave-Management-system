@php
    $indexRoute = $indexRoute ?? 'manager.analytics';
    $allPeopleLabel = $allPeopleLabel ?? 'All employees';
    $activePeopleLabel = $activePeopleLabel ?? 'Active Employees';
    $showManagerRole = $showManagerRole ?? false;
@endphp
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
    .analytics-insights-page {
        --ai-text: #243044;
        --ai-muted: #5b6170;
        --ai-lime: #b2cd16;
        --ai-lime-fill: #b2cd16;
        --ai-green: #3dcc7a;
        --ai-border: #d9dde3;
        --ai-card-shadow: 0 2px 10px rgba(36, 48, 68, 0.08);
        font-family: "Poppins", "Segoe UI", sans-serif;
        background: #fff;
        color: var(--ai-text);
        max-width: 1120px;
        margin: 0 auto;
        padding: 28px 12px 48px;
    }

    .analytics-insights-page .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px 20px;
        margin-bottom: 28px;
    }
    .analytics-insights-page .page-header h2 {
        font-weight: 700;
        font-size: 34px;
        line-height: 1.15;
        color: var(--ai-text);
        margin: 0;
        letter-spacing: -0.02em;
        flex: 0 1 auto;
        white-space: nowrap;
    }

    .analytics-insights-page,
    .analytics-insights-page .page-header,
    html body main .analytics-insights-page .filter-form {
        overflow: visible;
    }
    html body main .analytics-insights-page .filter-form,
    html body main .analytics-insights-page .page-header > .filter-form {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        width: auto !important;
        max-width: none !important;
        flex: 0 0 auto !important;
        margin: 0;
        padding: 0 !important;
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        position: relative;
        z-index: 5;
    }
    html body main .analytics-insights-page .filter-form .filter-select-wrap {
        position: relative;
        min-width: 154px !important;
        width: auto !important;
        flex: 0 0 auto !important;
        overflow: visible;
    }
    html body main .analytics-insights-page .filter-form .filter-select-wrap:has(select.filter-select--staff) {
        min-width: 190px !important;
    }
    html body main .analytics-insights-page .filter-form .filter-select__trigger {
        width: 100% !important;
        min-width: 154px !important;
        max-width: none !important;
        height: 40px !important;
        min-height: 40px !important;
        padding: 0 16px 0 14px !important;
        border: 2px solid var(--ai-lime) !important;
        border-radius: 999px !important;
        background: #fff !important;
        box-shadow: none !important;
        font-family: inherit;
        font-size: 14px !important;
        font-weight: 500 !important;
        color: var(--ai-text) !important;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 10px;
        white-space: nowrap;
        box-sizing: border-box;
    }
    .analytics-insights-page .filter-form .filter-select__label {
        flex: 1 1 auto;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-weight: 500;
        text-align: left;
    }
    .analytics-insights-page .filter-form .filter-select__chevron {
        width: 8px;
        height: 8px;
        border-right: 2px solid #64748b;
        border-bottom: 2px solid #64748b;
        transform: rotate(45deg) translateY(-2px);
        flex-shrink: 0;
    }
    html body main .analytics-insights-page .filter-form .filter-select-wrap.is-open .filter-select__trigger {
        border-color: var(--ai-lime) !important;
        box-shadow: 0 0 0 3px rgba(178, 205, 22, 0.28) !important;
    }
    html body main .analytics-insights-page .filter-form .filter-select__menu {
        z-index: 40;
        min-width: 100%;
        width: max-content;
        max-width: 280px;
        right: auto;
        left: 0;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 14px 36px rgba(15, 23, 42, 0.16);
    }
    .analytics-insights-page .filter-form .filter-select__option.is-selected {
        background: #b2cd16;
        color: #0a1347;
    }
    html body main .analytics-insights-page .filter-form select.filter-select.filter-select__native {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
        background: none !important;
        min-width: 0 !important;
        max-width: none !important;
    }

    html body main .analytics-insights-page .filter-clear {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex: 0 0 auto !important;
        width: auto !important;
        min-width: 0 !important;
        height: 40px !important;
        min-height: 40px !important;
        padding: 0 16px 0 14px !important;
        box-sizing: border-box;
        border: 2px solid var(--ai-lime-fill) !important;
        border-radius: 999px !important;
        background: var(--ai-lime-fill) !important;
        color: #fff !important;
        font-family: inherit;
        font-size: 14px !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        white-space: nowrap;
        box-shadow: none !important;
        margin: 0 !important;
    }
    .analytics-insights-page .filter-clear-chevron {
        width: 8px;
        height: 8px;
        border-right: 2px solid #fff;
        border-bottom: 2px solid #fff;
        transform: rotate(45deg) translateY(-1px);
        flex-shrink: 0;
    }
    html body main .analytics-insights-page .filter-clear:hover {
        background: #9eb812 !important;
        border-color: #9eb812 !important;
        color: #fff !important;
    }

    .analytics-insights-page .metrics-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }
    .analytics-insights-page .metric-card {
        position: relative;
        background: #fff;
        border: 1px solid var(--ai-border);
        border-radius: 20px;
        box-shadow: var(--ai-card-shadow);
        padding: 16px 18px 18px;
        min-height: 118px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }
    .analytics-insights-page .metric-card .card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        width: 100%;
        background: none;
        border: none;
        padding: 0;
        margin: 0;
    }
    .analytics-insights-page .metric-card h5 {
        font-weight: 600;
        font-size: 16px;
        color: var(--ai-text);
        margin: 0;
        padding-right: 36px;
        line-height: 1.3;
    }
    .analytics-insights-page .metric-card .card-icon {
        position: absolute;
        top: 16px;
        right: 16px;
        margin: 0;
        font-size: 20px;
        line-height: 1;
        color: #b7c922;
    }
    .analytics-insights-page .metric-value {
        font-weight: 700;
        font-size: 22px;
        color: var(--ai-text);
        margin: 10px 0 0;
        line-height: 1.1;
    }
    .analytics-insights-page .metric-note {
        font-size: 14px;
        font-weight: 400;
        color: var(--ai-text);
        margin: 4px 0 0;
    }

    .analytics-insights-page .stats-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 28px;
    }
    .analytics-insights-page .chart-card {
        min-width: 0;
        overflow: visible;
    }
    .analytics-insights-page .chart-panel {
        background: #fff;
        border: 1px solid var(--ai-border);
        border-radius: 20px;
        box-shadow: var(--ai-card-shadow);
        padding: 18px 22px 22px;
        min-height: 250px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .analytics-insights-page .section-heading {
        font-weight: 700;
        font-size: 16px;
        color: var(--ai-text);
        padding-bottom: 10px;
        margin: 0 0 18px;
        border-bottom: 1px solid #8b919c;
        display: block;
        width: 100%;
        text-align: left;
    }
    .analytics-insights-page .leave-type-list {
        flex: 1;
        width: 100%;
        padding: 0;
    }
    .analytics-insights-page .leave-type-entry {
        margin-bottom: 16px;
        width: 100%;
    }
    .analytics-insights-page .leave-type-label {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        margin-bottom: 8px;
    }
    .analytics-insights-page .leave-type-name {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        font-size: 15px;
        color: var(--ai-text);
    }
    .analytics-insights-page .leave-type-icon {
        width: 9px;
        height: 13px;
        border-radius: 4px;
        display: inline-block;
        flex-shrink: 0;
    }
    .analytics-insights-page .progress {
        height: 12px;
        border-radius: 999px;
        background: transparent;
        box-shadow: none;
        width: 100%;
        overflow: hidden;
    }
    .analytics-insights-page .progress-bar {
        height: 100%;
        border-radius: 999px;
        transition: width 0.4s ease;
    }
    .analytics-insights-page .chart-body--ring {
        height: 180px;
        min-height: 160px;
        flex-shrink: 0;
    }
    .analytics-insights-page .chart-html-legend {
        justify-content: center;
        font-weight: 500 !important;
        margin-top: 8px;
    }
    .analytics-insights-page .chart-html-legend-swatch {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 0;
    }
    .analytics-insights-page .chart-html-legend-label,
    .analytics-insights-page .chart-html-legend-item {
        font-weight: 500 !important;
        color: var(--ai-text);
        font-size: 13px;
    }

    .analytics-insights-page .takers-title {
        font-weight: 700;
        font-size: 16px;
        color: var(--ai-text);
        margin: 0 0 14px 6px;
        text-align: left;
    }
    .analytics-insights-page .taker-row {
        display: grid;
        grid-template-columns: 42px minmax(140px, 1fr) minmax(220px, 46%);
        align-items: center;
        gap: 8px 16px;
        padding: 14px 22px;
        margin-bottom: 12px;
        background: #fff;
        border: 1px solid var(--ai-border);
        border-radius: 28px;
        box-shadow: var(--ai-card-shadow);
        color: var(--ai-text);
    }
    .analytics-insights-page .taker-rank {
        align-self: start;
        font-weight: 700;
        font-size: 15px;
        padding-top: 2px;
    }
    .analytics-insights-page .taker-name {
        display: block;
        font-weight: 700;
        font-size: 16px;
        color: var(--ai-text);
        text-align: left;
    }
    .analytics-insights-page .taker-count {
        display: block;
        margin: 4px 0 0;
        font-size: 13px;
        font-weight: 400;
        color: var(--ai-text);
        text-align: left;
    }
    .analytics-insights-page .taker-progress .progress {
        height: 10px;
        margin: 0;
        background: #eef1f4;
    }
    .analytics-insights-page .taker-progress .progress-bar {
        background: var(--ai-green);
    }
    .analytics-insights-page .taker-pct {
        display: block;
        margin-top: 6px;
        color: var(--ai-green);
        font-weight: 700;
        font-size: 14px;
        text-align: left;
    }
    .analytics-insights-page .empty-state {
        text-align: center;
        color: var(--ai-muted);
        padding: 24px;
        background: #fff;
        border: 1px solid var(--ai-border);
        border-radius: 20px;
    }

    @media (max-width: 992px) {
        .analytics-insights-page .metrics-row { grid-template-columns: 1fr 1fr; }
        .analytics-insights-page .stats-row { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .analytics-insights-page { padding: 16px 4px 32px; }
        .analytics-insights-page .page-header h2 { font-size: 26px; }
        html body main .analytics-insights-page .filter-form {
            flex-wrap: wrap !important;
            width: 100% !important;
            justify-content: flex-start;
        }
        html body main .analytics-insights-page .page-header h2 {
            white-space: normal;
        }
        .analytics-insights-page .metrics-row { grid-template-columns: 1fr; }
        .analytics-insights-page .taker-row {
            grid-template-columns: 36px minmax(0, 1fr);
        }
        .analytics-insights-page .taker-progress {
            grid-column: 2;
        }
    }
</style>

<div class="analytics-insights-page">
    <div class="page-header">
        <h2>Analytics &amp; Insights</h2>
        <form method="GET" class="filter-form" id="monthFilterForm">
            <select name="month" class="filter-select filter-select--period" aria-label="Period" onchange="document.getElementById('monthFilterForm').submit()">
                <option value="this_year" {{ $monthFilter == 'this_year' ? 'selected' : '' }}>This year</option>
                <option value="this_month" {{ $monthFilter == 'this_month' ? 'selected' : '' }}>This month</option>
                <option value="this_quarter" {{ $monthFilter == 'this_quarter' ? 'selected' : '' }}>This quarter</option>
                @foreach(($years ?? []) as $year)
                    <option value="{{ $year }}" {{ (string) $monthFilter === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
            <select name="staff" class="filter-select filter-select--staff" aria-label="{{ $allPeopleLabel }}" onchange="document.getElementById('monthFilterForm').submit()">
                <option value="all" {{ (string) ($staff ?? 'all') === 'all' ? 'selected' : '' }}>{{ $allPeopleLabel }}</option>
                @foreach(($staffOptions ?? []) as $u)
                    <option value="{{ $u->id }}" {{ (string) ($staff ?? 'all') === (string) $u->id ? 'selected' : '' }}>
                        {{ trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ('User #' . $u->id) }}
                        @if ($showManagerRole && ($u->role ?? '') === 'manager') (Manager) @endif
                    </option>
                @endforeach
            </select>
            <a href="{{ route($indexRoute, ['month' => 'this_year', 'staff' => 'all']) }}" class="filter-clear">
                <span class="filter-clear-chevron" aria-hidden="true"></span>
                This year
            </a>
        </form>
    </div>

    <div class="metrics-row">
        <div class="metric-card">
            <div class="card-header">
                <h5>Total Leaves</h5>
                <i class="fas fa-calendar-days card-icon" aria-hidden="true"></i>
            </div>
            <div class="metric-value">{{ $totalLeaves }}</div>
            <div class="metric-note">Requested</div>
        </div>
        <div class="metric-card">
            <div class="card-header">
                <h5>Pending Requests</h5>
                <i class="fas fa-hourglass-half card-icon" aria-hidden="true"></i>
            </div>
            <div class="metric-value">{{ $pendingRequests }}</div>
            <div class="metric-note">Awaiting approval</div>
        </div>
        <div class="metric-card">
            <div class="card-header">
                <h5>{{ $activePeopleLabel }}</h5>
                <i class="fa-solid fa-user card-icon" aria-hidden="true"></i>
            </div>
            <div class="metric-value">{{ $employeesWhoApplied }}</div>
            <div class="metric-note">Awaiting approval</div>
        </div>
        <div class="metric-card">
            <div class="card-header">
                <h5>Approved</h5>
                <i class="fa-regular fa-circle-check card-icon" aria-hidden="true"></i>
            </div>
            <div class="metric-value">{{ $approvedCount }}</div>
            <div class="metric-note">Awaiting approval</div>
        </div>
    </div>

    <div class="stats-row">
        <div class="chart-card">
            <div class="chart-panel">
                <h5 class="section-heading">Leaves by Type</h5>
                <div class="leave-type-list">
                    @php
                        $normalizeKey = function ($value) {
                            $v = strtolower(trim((string) $value));
                            if ($v === '') return '';
                            if (str_ends_with($v, ' leave')) {
                                $v = trim(substr($v, 0, -5));
                            }
                            return $v;
                        };

                        $palette = config('leave_type_colors.fallback_palette', []);
                        $fixedTypeColors = config('leave_type_colors.colors', []);

                        $hashString = function ($str) {
                            $s = (string) $str;
                            $h = 0;
                            $len = strlen($s);
                            for ($i = 0; $i < $len; $i++) {
                                $h = (($h << 5) - $h) + ord($s[$i]);
                                $h &= 0xFFFFFFFF;
                            }
                            return abs((int) $h);
                        };

                        $colorForType = function ($type) use ($normalizeKey, $fixedTypeColors, $palette, $hashString) {
                            $key = $normalizeKey($type);
                            if ($key !== '' && array_key_exists($key, $fixedTypeColors)) {
                                return $fixedTypeColors[$key];
                            }
                            $idx = ($key === '' ? 0 : ($hashString($key) % max(count($palette), 1)));
                            return $palette[$idx] ?? '#a43b8c';
                        };
                    @endphp

                    @forelse ($leavesByType as $type)
                        @php
                            $percentage = $typePercentages[$type->leave_type] ?? 0;
                            $color = $colorForType($type->leave_type);
                        @endphp
                        <div class="leave-type-entry">
                            <div class="leave-type-label">
                                <div class="leave-type-name">
                                    <span class="leave-type-icon" style="background-color: {{ $color }}"></span>
                                    {{ ucfirst($type->leave_type) }}
                                </div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $percentage }}%; background-color: {{ $color }};"></div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">No data available</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-panel">
                <h5 class="section-heading">Leaves by Department</h5>
                <div class="chart-body chart-body--ring">
                    <canvas id="designationPieChart"></canvas>
                </div>
                <ul class="chart-html-legend" id="designationPieLegend" aria-label="Department legend"></ul>
            </div>
        </div>
    </div>

    <h5 class="takers-title">Leaves by Type</h5>
    @forelse ($topLeaveTakers as $index => $taker)
        @php $percentage = $taker->percentage ?? 0; @endphp
        <div class="taker-row">
            <span class="taker-rank">#{{ $index + 1 }}</span>
            <div class="taker-info">
                <span class="taker-name">
                    {{ trim(($taker->user?->first_name ?? '') . ' ' . ($taker->user?->last_name ?? '')) ?: 'Unknown' }}
                </span>
                <span class="taker-count">{{ $taker->total }} {{ $taker->total == 1 ? 'leave' : 'leaves' }}</span>
            </div>
            <div class="taker-progress">
                <div class="progress">
                    <div class="progress-bar" style="width: {{ $percentage }}%;"></div>
                </div>
                <span class="taker-pct">{{ $percentage }}%</span>
            </div>
        </div>
    @empty
        <div class="empty-state">No data available</div>
    @endforelse
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@include('partials.dashboard-charts-styles')
@include('partials.dashboard-chart-vars')
<script>
const ctx = document.getElementById('designationPieChart').getContext('2d');
window.applyBoldChartDefaults();
window.finalizeChartDefaults();
@include('partials.analytics-designation-chart-script')
</script>
