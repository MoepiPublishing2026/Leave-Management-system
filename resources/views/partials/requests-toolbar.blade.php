@php
    $statusFilters = $statusFilters ?? [
        'all' => 'All',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'overdue' => 'Overdue',
    ];
    $monthFilter = $monthFilter ?? null;
    $monthLabels = [
        'this_month' => 'This month',
        'this_quarter' => 'This quarter',
        'this_year' => 'This year',
    ];
    $monthLabel = null;
    if (!empty($monthFilter)) {
        $monthLabel = $monthLabels[$monthFilter] ?? (ctype_digit((string) $monthFilter) ? 'Year ' . $monthFilter : ucfirst(str_replace('_', ' ', (string) $monthFilter)));
    }
    $filterParams = $filterParams ?? [];
    if (!empty($monthFilter)) {
        $filterParams['month'] = $monthFilter;
    }
    $employeeFilter = $employeeFilter ?? null;
    if (!empty($employeeFilter)) {
        $filterParams['employee'] = $employeeFilter;
    }
    $activeSearch = trim((string) ($search ?? ''));
    if ($activeSearch !== '') {
        $filterParams['search'] = $activeSearch;
    }
    $clearSearchParams = array_merge(['status' => $status], $filterParams);
    unset($clearSearchParams['search']);
    $resultTotal = $resultTotal ?? null;
@endphp

@if (session('success'))
    <div class="requests-flash requests-flash--success" role="status">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="requests-flash requests-flash--error" role="alert">{{ session('error') }}</div>
@endif

<div class="requests-toolbar">
    <div class="requests-toolbar-left">
        @if (!empty($showNewRequest))
            <a href="{{ route($newRequestRoute ?? 'apply') }}" class="requests-btn requests-btn--primary">+ New Request</a>
        @endif
        @if ($monthLabel)
            <span class="requests-month-chip">
                Period: {{ $monthLabel }}
                @php
                    $clearMonthParams = array_merge(['status' => $status], $filterParams);
                    unset($clearMonthParams['month']);
                @endphp
                <a href="{{ route($filterRoute, $clearMonthParams) }}" title="Clear period filter" aria-label="Clear period filter">&times;</a>
            </span>
        @endif
    </div>
    <div class="requests-toolbar-right">
        <form method="GET" action="{{ route($formRoute) }}" class="requests-search-form">
            <input type="hidden" name="status" value="{{ $status }}" />
            @if (!empty($monthFilter))
                <input type="hidden" name="month" value="{{ $monthFilter }}" />
            @endif
            @if (!empty($employeeFilter))
                <input type="hidden" name="employee" value="{{ $employeeFilter }}" />
            @endif
            <input type="text" name="search" placeholder="{{ $searchPlaceholder ?? 'Search requests...' }}" value="{{ $search ?? '' }}" aria-label="Search requests" />
            <button type="submit" class="requests-btn requests-btn--search">Search</button>
            <a href="{{ route($filterRoute, $clearSearchParams) }}" class="requests-btn requests-btn--secondary">Clear</a>
        </form>
    </div>
</div>

<div class="requests-filters" role="tablist" aria-label="Filter by status">
    @foreach ($statusFilters as $filterKey => $filterLabel)
        @php
            $linkParams = array_merge(['status' => $filterKey], $filterParams);
        @endphp
        <a href="{{ route($filterRoute, $linkParams) }}"
           class="requests-filter requests-filter--{{ $filterKey }} {{ $status === $filterKey ? 'is-active' : '' }}"
           role="tab"
           aria-selected="{{ $status === $filterKey ? 'true' : 'false' }}">
            {{ $filterLabel }}
        </a>
    @endforeach
</div>
