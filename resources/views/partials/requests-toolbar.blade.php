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
    $requestedPayFilter = $payFilter ?? 'all';
    $payFilter = in_array($requestedPayFilter, ['paid', 'unpaid'], true) ? $requestedPayFilter : 'all';
    if ($payFilter !== 'all') {
        $filterParams['pay'] = $payFilter;
    }
    $activeSearch = trim((string) ($search ?? ''));
    if ($activeSearch !== '') {
        $filterParams['search'] = $activeSearch;
    }
    $clearSearchParams = array_merge(['status' => $status], $filterParams);
    unset($clearSearchParams['search']);
    $resultTotal = $resultTotal ?? null;
@endphp

<div class="requests-toolbar" style="background: #dbe4e6 !important; border-radius: 12px !important; padding: 12px 16px !important; display: flex !important; justify-content: space-between !important; align-items: center !important; margin-bottom: 20px !important; border: 0 !important;">
    <div class="requests-toolbar-left" style="display: flex; align-items: center; gap: 10px;">
        @if (!empty($showNewRequest))
            <a href="{{ route($newRequestRoute ?? 'apply') }}" class="requests-btn requests-btn--primary" style="background: #cbe212 !important; color: #0a1347 !important; border: 1px solid #b5cb0e !important; border-radius: 6px !important; padding: 8px 16px !important; font-weight: 700 !important; font-size: 14px !important; text-decoration: none !important;">+ New Request</a>
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
    <div class="requests-toolbar-right" style="display: flex; align-items: center;">
        <form method="GET" action="{{ route($formRoute) }}" class="requests-search-form" style="display: flex !important; gap: 8px !important; align-items: center !important; margin: 0 !important;">
            <input type="hidden" name="status" value="{{ $status }}" />
            @if (!empty($monthFilter))
                <input type="hidden" name="month" value="{{ $monthFilter }}" />
            @endif
            @if (!empty($employeeFilter))
                <input type="hidden" name="employee" value="{{ $employeeFilter }}" />
            @endif
            @if ($payFilter !== 'all')
                <input type="hidden" name="pay" value="{{ $payFilter }}" />
            @endif
            <input type="text" name="search" placeholder="{{ $searchPlaceholder ?? 'Search requests...' }}" value="{{ $search ?? '' }}" aria-label="Search requests" style="background: #fff !important; border: 0 !important; border-radius: 20px !important; padding: 10px 18px 10px 44px !important; font-size: 14px !important; min-width: 280px !important; height: 40px !important; box-shadow: none !important; background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a2b222\' stroke-width=\'2\' stroke-linecap=\'round\'><circle cx=\'11\' cy=\'11\' r=\'7\'/><path d=\'M16 16l5 5\'/></svg>') !important; background-repeat: no-repeat !important; background-position: 16px center !important; background-size: 18px !important;" />
            <button type="submit" class="requests-btn requests-btn--search" style="background: #cbe212 !important; color: #0a1347 !important; border: 1px solid #b5cb0e !important; border-radius: 6px !important; padding: 0 16px !important; font-weight: 700 !important; font-size: 14px !important; height: 40px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important;">Search</button>
            <a href="{{ route($filterRoute, $clearSearchParams) }}" class="requests-btn requests-btn--secondary" style="background: #cbe212 !important; color: #0a1347 !important; border: 1px solid #b5cb0e !important; border-radius: 6px !important; padding: 0 16px !important; font-weight: 700 !important; font-size: 14px !important; height: 40px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; text-decoration: none !important;">Clear</a>
        </form>
    </div>
</div>

<div class="requests-filter-bar">
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

<form method="GET" action="{{ route($filterRoute) }}" class="requests-pay-filter" style="background: #dbe4e6 !important; border: 0 !important; border-radius: 12px !important; padding: 6px 16px !important; display: flex !important; align-items: center !important; gap: 12px !important; box-shadow: none !important; margin: 0 !important;">
    <input type="hidden" name="status" value="{{ $status }}" />
    @if (!empty($monthFilter))
        <input type="hidden" name="month" value="{{ $monthFilter }}" />
    @endif
    @if (!empty($employeeFilter))
        <input type="hidden" name="employee" value="{{ $employeeFilter }}" />
    @endif
    @if ($activeSearch !== '')
        <input type="hidden" name="search" value="{{ $activeSearch }}" />
    @endif
    
    <!-- FORCED INLINE STYLE FOR LABEL -->
    <label for="requestsPayFilter" style="text-transform: none !important; font-weight: 500 !important; font-size: 14px !important; color: #475569 !important; margin: 0 !important; display: inline-block !important; letter-spacing: normal !important; border: 0 !important; background: transparent !important; padding: 0 !important;">Payment</label>
    
    <!-- FORCED INLINE STYLE FOR SELECT -->
    <select id="requestsPayFilter" name="pay" onchange="this.form.submit()" style="background-color: #fff !important; border: 0 !important; border-radius: 20px !important; font-weight: 600 !important; font-size: 14px !important; color: #1e293b !important; padding: 8px 16px !important; min-width: 140px !important; box-shadow: none !important; appearance: auto !important; margin: 0 !important;">
        <option value="all" {{ $payFilter === 'all' ? 'selected' : '' }}>All Leave</option>
        <option value="paid" {{ $payFilter === 'paid' ? 'selected' : '' }}>Paid Leave</option>
        <option value="unpaid" {{ $payFilter === 'unpaid' ? 'selected' : '' }}>Unpaid Leave</option>
    </select>
</form>
</div>