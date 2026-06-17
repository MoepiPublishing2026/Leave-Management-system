@php
    $monthFilter = $monthFilter ?? 'this_year';
    $nowY = (int) \Illuminate\Support\Carbon::now()->year;
    $yearOptions = $yearOptions ?? range($nowY - 1, $nowY - 5);
@endphp

@include('partials.dashboard-period-filter-styles')

<div class="dashboard-period-filter">
    <form method="GET" class="dashboard-period-filter__form">
        <label class="dashboard-period-filter__label" for="dashboardPeriodSelect">Period</label>
        <select
            id="dashboardPeriodSelect"
            name="month"
            class="dashboard-period-filter__select filter-select"
            onchange="this.form.submit()"
            aria-label="Filter dashboard by period"
        >
            <option value="this_year" {{ $monthFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
            <option value="this_quarter" {{ $monthFilter === 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
            <option value="this_month" {{ $monthFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
            @foreach ($yearOptions as $y)
                <option value="{{ $y }}" {{ (string) $monthFilter === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        <noscript>
            <button type="submit" class="dashboard-period-filter__btn">Apply</button>
        </noscript>
    </form>
</div>
