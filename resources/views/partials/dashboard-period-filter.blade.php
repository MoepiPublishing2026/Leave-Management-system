@php
    $monthFilter = $monthFilter ?? 'this_year';
    $employeeFilter = $employeeFilter ?? null;
    $employees = $employees ?? collect();
    $isSeniorManager = strtolower((string) (auth()->user()->role ?? '')) === 'admin';
    $personLabel = $isSeniorManager ? 'Staff member' : 'Employee';
    $allPeopleLabel = $isSeniorManager ? 'All Staff' : 'All Employees';
    $nowY = \App\Support\LeaveYear::current();
    $yearOptions = $yearOptions ?? range($nowY - 1, $nowY - 5);
@endphp

@include('partials.dashboard-period-filter-styles')

<div class="dashboard-period-filter">
    <form method="GET" class="dashboard-period-filter__form">
        <div class="dashboard-period-filter__row dashboard-period-filter__row--staff">
            <label class="dashboard-period-filter__label-1" for="dashboardEmployeeSelect">{{ $personLabel }}</label>
            <select
                id="dashboardEmployeeSelect"
                name="employee"
                class="dashboard-period-filter__select dashboard-period-filter__select--employee filter-select"
                onchange="this.form.submit()"
                aria-label="Filter dashboard by {{ strtolower($personLabel) }}"
            >
                <option value="" {{ empty($employeeFilter) ? 'selected' : '' }}>{{ $allPeopleLabel }}</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" {{ (int) $employeeFilter === (int) $employee->id ? 'selected' : '' }}>
                        {{ trim($employee->first_name . ' ' . $employee->last_name) }}
                        @if ($isSeniorManager && ($employee->role ?? '') === 'manager')
                            (Manager)
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="dashboard-period-filter__row dashboard-period-filter__row--period">
            <label class="dashboard-period-filter__label-1" for="dashboardPeriodSelect">Period</label>
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
                    <option value="{{ $y }}" {{ (string) $monthFilter === (string) $y ? 'selected' : '' }}>{{ \App\Support\LeaveYear::label($y) }}</option>
                @endforeach
            </select>
        </div>
        <noscript>
            <button type="submit" class="dashboard-period-filter__btn">Apply</button>
        </noscript>
    </form>
</div>
