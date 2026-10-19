@extends('layouts.manager')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    /* ===== Theme tokens () ===== */
    .manager-analytics-page {
        --ma-text: #1f2430;
        --ma-muted: #4a4f5a;
        --ma-lime: #c3d62b;
        --ma-lime-dark: #a9bb1c;
        --ma-green: #25c27b;
        --ma-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        --ma-shadow-hover: 0 12px 28px rgba(0, 0, 0, 0.16);

        font-family: "Poppins", "Segoe UI", sans-serif;
        background: #fff;
        color: var(--ma-text);
        max-width: 1100px;
        margin: 0 auto;
        
        padding: 40px 8px 48px;
    }

    /* ===== Header + filters ===== */
    .manager-analytics-page .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 24px;
        
    }
    .manager-analytics-page .page-header h2 {
        font-weight: 600;
        font-size: 39px;
        color: var(--ma-text);
        margin: 0;
        letter-spacing: 0;
       
        
    }
    .manager-analytics-page .filter-form {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        margin-top: 26px;
         /* border: 2px solid var(--ma-lime);
        border-radius: 30px; */
    }
    .manager-analytics-page .filter-select {
        appearance: none;
        -webkit-appearance: none;
        font-family: inherit;
        font-size: 16px;
        color: var(--ma-text);
        background-color: #fff;
        /* chevron on the left */
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231f2430' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 16px center;
        background-size: 18px;
        border: 2px solid var(--ma-lime);
        border-radius: 3px;
        padding: 11px 22px 11px 44px;
        cursor: pointer;
        outline: none;
        transition: box-shadow 0.15s ease;
    }
    .manager-analytics-page .filter-form select.filter-select {
    /* appearance: none;
    -webkit-appearance: none;
    padding: 11px 16px 11px 44px;
    background-color: #fff;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231f2430' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 14px center;
    background-size: 18px;
    cursor: pointer; */
     text-align: right;
    text-align-last: right;
    padding-left: 44px;  /* space for the left-side chevron */
    padding-right: 16px;
}
    .manager-analytics-page .filter-select:focus {
        box-shadow: 0 0 0 3px rgba(195, 214, 43, 0.35);
    }
    
    /* This is clear button */
    .manager-analytics-page .filter-clear {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 26px;
        border: 2px solid var(--ma-lime-dark);
        border-radius: 20px;
        background: var(--ma-lime-dark);
        color: #fff;
        font-size: 16px;
        font-weight: 500;
        text-decoration: none;
        white-space: nowrap;
        transition: background-color 0.15s ease;
    }
    /* Add border and border-radius to the select and clear button to match the design */
    .manager-analytics-page .filter-select,
.manager-analytics-page .filter-clear {
    border: 2px solid var(--ma-lime);
    border-radius: 30px;
}
    .manager-analytics-page .filter-clear:hover {
        background: #95a617;
        border-color: #95a617;
        color: #fff;
    }

    /* ===== Generic card ===== */
    .manager-analytics-page .card {
        background: #fff;
        border: 1px solid #C0C0C0;  /* Add a subtle border to the card */
        border-radius: 22px;
        box-shadow: var(--ma-shadow);
        padding: 18px 22px 20px;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.25s ease, transform 0.25s ease;
    }
     .manager-analytics-page .card:hover {
        box-shadow: var(--ma-shadow-hover);
        transform: translateY(-3px);
     } 
     
       /* Remove box-shadow and border from all cards to match the design */
       .manager-analytics-page .section-card,
.manager-analytics-page .chart-card,
.manager-analytics-page .analytics-card,
.manager-analytics-page .leave-type-card,
.manager-analytics-page .department-card {
    border: none !important;
    box-shadow: none !important;
}
       

    /* ===== Metric cards ===== */
    .manager-analytics-page .metrics-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 26px;

    }
    .manager-analytics-page .card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        background: none;
        border: none;
        /* Adding a space between the title and the icon for better alignment */
        padding: 0;
        margin: 0;
       
     } 
     .manager-analytics-page .card-header {
    display: flex;
    align-items: center;
}
.manager-analytics-page .card-icon {
    margin-left: auto;  /* eats all free space on its left, pushing it right */
    font-size: 24px;
    line-height: 1;
    color: var(--ma-lime-dark);
    position: absolute;
    right: 16px;
    top: 16px; 
}
.manager-analytics-page .metric-card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;   /* aligns children to the left edge */
    text-align: left;
    padding: 16px;
    position: relative;  /* allows absolute positioning of the icon */
}
    .manager-analytics-page .card-header h5 {
        font-weight: 600;
        font-size: 17px;
        color: var(--ma-text);
        margin: 5px 2 2 -30px; /* Adjusted margin to align with the icon */
        margin-right: 41px; /* Add some space between the title and the icon */
    }
    /* .manager-analytics-page .page-header .filter-form .filter-select {
    border: 2px solid var(--ma-lime);
} */

.manager-analytics-page .page-header .filter-form .filter-clear {
    border: 3px solid var(--ma-lime);
}
    .manager-analytics-page .card-icon {
          font-size: 24px;
    line-height: 1;
    color: var(--ma-lime-dark);
    position: absolute;
    right: 16px;
    top: 16px;
    }
   .manager-analytics-page .metric-value {
    font-weight: 700;
    font-size: 20px;
    color: var(--ma-text);
    margin: 6px 0 0 0;
    line-height: 1.2;
}

.manager-analytics-page .metric-note {
    font-size: 14.5px;
    font-weight: 400;
    color: var(--ma-text);
    margin: 4px 0 0 0;
}
.manager-analytics-page .metrics-row .metric-value,
.manager-analytics-page .metrics-row .metric-note {
    align-self: flex-start;
    text-align: left;
    margin-left: 12px;
}

    /* ===== Chart cards ===== */
    .manager-analytics-page .stats-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 24px;
    
        
    }
    .manager-analytics-page .chart-card .card {
        padding: 20px 28px;
        min-height: 230px;
        height: 100%;
    }
    .manager-analytics-page .section-heading {
        font-weight: 700;
    font-size: 17px;
    color: var(--ma-text);
    padding-bottom: 10px;
    margin: 0 0 22px;
    border-bottom: 1.4px solid #8a8f99;
    display: block;
    width: 100%;              /* full width of parent */
    box-sizing: border-box;   /* include padding in width */
    text-align: left; 
    }
    .manager-analytics-page .leave-type-list {
        flex: 1;
        overflow-y: auto;
        padding-right: 6px;
        width: 100%;
        padding-left: 0;
    }
    .manager-analytics-page .leave-type-entry { margin-bottom: 16px;
    width: 100%; }
    .manager-analytics-page .leave-type-label {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        font-weight: 600;
        font-size: 17px;
        margin-bottom: 4px;
    }
    .manager-analytics-page .leave-type-name {
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--ma-text);
    }
    .manager-analytics-page .leave-type-icon {
        width: 9px;
        height: 12px;
        border-radius: 6px;
        display: inline-block;
    }
    .manager-analytics-page .leave-type-count {
        font-size: 14px;
        font-weight: 600;
    }
    .manager-analytics-page .progress {
        height: 13px;
        border-radius: 8px;
        background: transparent;
        box-shadow: none;
        width: 100%;
        overflow: hidden;
    }
    .manager-analytics-page .progress-bar {
        height: 100%;
        border-radius: 8px;
        transition: width 0.4s ease;
    }

    /* ===== Top leave takers (card rows) ===== */
    .manager-analytics-page .takers-title {
        font-weight: 700;
        font-size: 17px;
        color: var(--ma-text);
        margin: 0 0 12px 12px;
    }
   .manager-analytics-page .taker-row {
    display: grid;
    grid-template-columns: 32px minmax(180px, 1fr) minmax(300px, 42%);
    align-items: center;
    gap: 16px;
    padding: 12px 24px;
    margin-bottom: 12px;
    border-radius: 28px;
      color: #1f2430;
}

.manager-analytics-page .taker-rank {
    align-self: start;
    font-weight: 700;
    font-size: 17px;
}

.manager-analytics-page .taker-info {
    min-width: 0;
}

.manager-analytics-page .taker-name {
    display: block;
    font-weight: 700;
    font-size: 17px;
    color: #1f2430;
    text-align: left;
}

.manager-analytics-page .taker-count {
    display: block;
    margin: 6px 0 0 10px;
    font-size: 14.5px;
      color: #1f2430;
      text-align: left;
}

.manager-analytics-page .taker-progress {
    width: 100%;
    max-width: none;
}

.manager-analytics-page .taker-progress .progress {
    height: 16px;
    margin: 0;
    border-radius: 999px;
    background: #e8ecef;
}

.manager-analytics-page .taker-progress .progress-bar {
    border-radius: 999px;
    background: var(--ma-green);
}

.manager-analytics-page .taker-pct {
    display: block;
    margin-top: 6px;
    color: var(--ma-green);
    font-weight: 700;
    font-size: 16px;
    text-align: left;
    margin-left: 0;
}
    .manager-analytics-page .empty-state {
        text-align: center;
        color: var(--ma-muted);
        padding: 24px;
    }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .manager-analytics-page .metrics-row { grid-template-columns: 1fr 1fr; }
        .manager-analytics-page .stats-row { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
    .manager-analytics-page .taker-row {
        grid-template-columns: 32px minmax(0, 1fr);
    }

    .manager-analytics-page .taker-progress {
        grid-column: 2;
    }
}
    @media (max-width: 768px) {
        .manager-analytics-page { padding-top: 24px; }
        .manager-analytics-page .page-header h2 { font-size: 30px; }
        .manager-analytics-page .filter-form { width: 100%; margin-top: 0; }
        .manager-analytics-page .filter-select,
        .manager-analytics-page .filter-clear { width: 100%; justify-content: center; }
        .manager-analytics-page .metrics-row { grid-template-columns: 1fr; }
        .manager-analytics-page .taker-row { flex-wrap: wrap; }
        .manager-analytics-page .taker-progress { flex: 1 1 100%; max-width: none; }
    }
    /* Taking out the background color of the filter form to match the design (This Year,All Employees, Clear) */
    .manager-analytics-page .page-header > .filter-form {
    background: #fff !important;
        border: none !important;
    box-shadow: none !important;
}

/*Additionally, adding a border to the filter select to match the design (This Year, All Employees) */
.manager-analytics-page .filter-form .filter-select {
    border: 2px solid var(--ma-lime);
     border-radius: 30px;
      /* border: 2px solid #c3d62b ; */

}
.manager-analytics-page .metrics-row > .card {
    position: relative;
}

.manager-analytics-page .metrics-row .card-header {
    display: flex;
    justify-content: flex-start;
    width: 100%;
    padding-right: 48px;
}

.manager-analytics-page .metrics-row .card-header h5 {
    margin: 0;
    text-align: left;
}

.manager-analytics-page .metrics-row .card-icon {
    position: absolute;
    top: 16px;
    right: 16px;
    margin: 0;
}

</style>

<div class="manager-analytics-page">
    <!-- Header + Filters -->
    <div class="page-header">
        <h2>Analytics &amp; Insights</h2>
        <form method="GET" class="filter-form" id="monthFilterForm">
            <select name="month" class="filter-select" onchange="document.getElementById('monthFilterForm').submit()">
                <option value="this_year" {{ $monthFilter == 'this_year' ? 'selected' : '' }}>This year</option>
                <option value="this_month" {{ $monthFilter == 'this_month' ? 'selected' : '' }}>This month</option>
                <option value="this_quarter" {{ $monthFilter == 'this_quarter' ? 'selected' : '' }}>This quarter</option>
                @foreach(($years ?? []) as $year)
                    <option value="{{ $year }}" {{ (string) $monthFilter === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
            <select name="staff" class="filter-select" onchange="document.getElementById('monthFilterForm').submit()">
                <option value="all" {{ (string) ($staff ?? 'all') === 'all' ? 'selected' : '' }}>All employees</option>
                @foreach(($staffOptions ?? []) as $u)
                    <option value="{{ $u->id }}" {{ (string) ($staff ?? 'all') === (string) $u->id ? 'selected' : '' }}>
                        {{ trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ('User #' . $u->id) }}
                    </option>
                @endforeach
            </select>
            <a href="{{ route('manager.analytics', ['month' => 'this_year', 'staff' => 'all']) }}" class="filter-clear">Clear</a>
        </form>
    </div>

    <!-- Metric Cards -->
    <div class="metrics-row">
        <div class="card">
            <div class="card-header">
                <h5>Total Leaves</h5>
                <i class="fas fa-calendar-days card-icon"></i>
            </div>
            <div class="metric-value">{{ $totalLeaves }}</div>
            <div class="metric-note">Requested</div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Pending Requests</h5>
                <i class="fas fa-hourglass-half card-icon"></i>
            </div>
            <div class="metric-value">{{ $pendingRequests }}</div>
            <div class="metric-note">Awaiting approval</div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Active Employees</h5>
                <i class="fa-solid fa-user card-icon"></i>
            </div>
            <div class="metric-value">{{ $employeesWhoApplied }}</div>
            <div class="metric-note">of {{ $totalEmployees }} total</div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Approved</h5>
                <i class="fa-regular fa-circle-check card-icon"></i>
            </div>
            <div class="metric-value">{{ $approvedCount }}</div>
            <div class="metric-note">{{ $approvalRate }}% approval rate</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="stats-row">
        <!-- Leaves by Type -->
        <div class="chart-card">
            <div class="card">
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
                                <span class="leave-type-count" style="color: {{ $color }};"></span>
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

        <!-- Leaves by Department -->
        <div class="chart-card">
            <div class="card">
                <h5 class="section-heading">Leaves by Department</h5>
                <div class="chart-body chart-body--ring">
                    <canvas id="designationPieChart"></canvas>
                </div>
                <ul class="chart-html-legend" id="designationPieLegend" aria-label="Department legend"></ul>
            </div>
        </div>
    </div>

    <!-- Top Leave Takers -->
    <h5 class="takers-title">Top Leave Takers</h5>
    @forelse ($topLeaveTakers as $index => $taker)
        @php $percentage = $taker->percentage ?? 0; @endphp
        <div class="card taker-row">
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
        <div class="card empty-state">No data available</div>
    @endforelse
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@include('partials.dashboard-charts-styles')
@include('partials.dashboard-chart-vars')
<script>
const ctx = document.getElementById('designationPieChart').getContext('2d');
window.applyBoldChartDefaults();
window.finalizeChartDefaults();
@include('partials.analytics-designation-chart-script')
</script>
@endsection