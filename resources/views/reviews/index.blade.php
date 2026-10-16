@extends($layout ?? 'layouts.app')

@section('title', 'Monthly Performance Reviews')

@section('content')
@include('partials.requests-page-styles')
@include('partials.performance-review-styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
html body .pr-reviews-page { font-family:'Poppins',sans-serif; }
html body .pr-reviews-page h1 { color:#263248; font-weight:700; letter-spacing:.04em; }
html body .pr-reviews-page .requests-page-lead { color:#777; }

/* grey-teal toolbar with olive buttons */
html body .pr-reviews-page .requests-toolbar {
    background:#d8e3e1 !important; border:0 !important; border-radius:14px !important;
    padding:14px 16px !important; box-shadow:none !important; align-items:center;
}
html body .pr-reviews-page .requests-btn--primary,
html body .pr-reviews-page .requests-btn--search,
html body .pr-reviews-page .requests-btn--secondary {
    background:#a2b222 !important; color:#000 !important; border:0 !important;
    border-radius:14px !important; padding:14px 30px !important; font-weight:700 !important;
}
html body .pr-reviews-page .requests-search-form input[type="text"] {
    background:#fff !important; border:0 !important; border-radius:12px !important;
    padding:14px 18px !important; min-width:260px;
}

/* tabs: All / Drafts / Sent / Reviewed */
html body .pr-reviews-page .requests-filter-bar {
    background:transparent !important; border:0 !important; box-shadow:none !important; padding:0 !important;
}
html body .pr-reviews-page .requests-filters {
    display:inline-flex; gap:22px; background:#d8e3e1 !important; border:0 !important;
    border-radius:14px; padding:12px 20px;
}
html body .pr-reviews-page .requests-filter {
    background:transparent !important; color:#263248 !important; font-weight:700 !important;
    padding:0 !important; border:0 !important; box-shadow:none !important;
}
html body .pr-reviews-page .requests-filter.is-active { background:transparent !important; color:#2379b0 !important; }

/* white table card with shadow */
html body .pr-reviews-page .requests-table-wrap {
    background:#fff !important; border:1px solid #d5d9e0 !important; border-radius:16px !important;
    box-shadow:6px 8px 14px rgba(0,0,0,.25) !important; overflow:hidden;
}
html body .pr-reviews-page .requests-table th {
    background:#fff !important; color:#263248 !important; font-size:.95rem !important;
    font-weight:700 !important; text-transform:none !important; letter-spacing:0 !important;
    border-bottom:2px solid #999 !important; border-right:1px solid #999 !important; padding:18px 14px !important;
}
html body .pr-reviews-page .requests-table th:last-child { border-right:0 !important; }
html body .pr-reviews-page .requests-empty {
    color:#263248 !important; font-weight:700 !important; font-style:italic !important; text-align:left !important;
}
html body .pr-reviews-page .requests-hint { color:#777 !important; }

/* remove the green/teal strip along the top of the table */
html body .pr-reviews-page .requests-table-wrap {
    border-image:none !important; background-image:none !important;
    border-top:1px solid #d5d9e0 !important;
}
html body .pr-reviews-page .requests-table,
html body .pr-reviews-page .requests-table thead,
html body .pr-reviews-page .requests-table thead tr,
html body .pr-reviews-page .requests-table thead th {
    border-top:0 !important; border-image:none !important; background-image:none !important;
}
html body .pr-reviews-page .requests-table-wrap::before,
html body .pr-reviews-page .requests-table-wrap::after,
html body .pr-reviews-page .requests-table::before,
html body .pr-reviews-page .requests-table::after,
html body .pr-reviews-page .requests-table thead::before,
html body .pr-reviews-page .requests-table thead::after {
    content:none !important; display:none !important;
}

/* white (not teal) body rows, including the empty-state row */
html body .pr-reviews-page .requests-table tbody tr,
html body .pr-reviews-page .requests-table tbody td,
html body .pr-reviews-page .requests-table td.requests-empty {
    background:#fff !important; background-image:none !important;
}
html body .pr-reviews-page .pr-reviews-scope-header {
    max-width: none;
    margin: 0 0 10px;
    padding: 0;
    align-items: flex-start;
}
html body .pr-reviews-page .pr-reviews-scope-header h1 {
    margin: 0;
}
</style>

@php
    use App\Support\PerformanceReviews;
    $user = $user ?? auth()->user();
    $isSenior = strtolower((string) ($user->role ?? '')) === 'admin';
    $indexRoute = PerformanceReviews::routeName($user, 'index');
    $filters = $isSenior
        ? ['all' => 'All', 'submitted' => 'Awaiting review', 'reviewed' => 'Reviewed']
        : ['all' => 'All', 'draft' => 'Drafts', 'submitted' => 'Sent', 'reviewed' => 'Reviewed'];
    $lead = $isSenior
        ? 'Reviews appear here after an employee or manager sends them.'
        : 'Fill in your monthly performance review, then submit it.';
    $empty = $isSenior
        ? 'No reviews have been sent yet.'
        : 'No performance reviews yet.';
    $search = $search ?? '';
    $reviewScope = $reviewScope ?? 'all';
    $isManagerList = in_array($reviewScope, ['mine', 'team'], true);
    $scopeQuery = $isManagerList ? ['scope' => $reviewScope] : [];
    $clearParams = array_filter(['status' => $status] + $scopeQuery);
    if ($reviewScope === 'team') {
        $lead = 'Reviews your team has sent. Use My reviews to write and submit your own.';
        $empty = 'No team reviews yet.';
    } elseif ($reviewScope === 'mine') {
        $lead = 'Fill in your monthly performance review, then submit it.';
        $empty = 'No performance reviews yet.';
    }
@endphp

<div class="requests-page pr-reviews-page">
    @if ($isManagerList)
        <div class="mgr-page-header pr-reviews-scope-header">
            <div class="mgr-page-header__main">
                <h1>Monthly Performance Reviews</h1>
                <p class="requests-page-lead">
                    {{ $lead }}
                    @if ($reviews->total() > 0)
                        · Showing <strong>{{ $reviews->total() }}</strong> {{ Str::plural('review', $reviews->total()) }}
                    @endif
                </p>
            </div>
            @include('partials.manager-scope-switch', [
                'scope' => $reviewScope === 'mine' ? 'personal' : 'team',
                'personalUrl' => route($indexRoute, array_filter(['scope' => 'mine', 'status' => $status, 'search' => $search])),
                'teamUrl' => route($indexRoute, array_filter(['scope' => 'team', 'status' => $status, 'search' => $search])),
                'personalLabel' => 'My reviews',
                'teamLabel' => 'Team reviews',
            ])
        </div>
    @else
        <h1>Monthly Performance Reviews</h1>
        <p class="requests-page-lead">
            {{ $lead }}
            @if ($reviews->total() > 0)
                · Showing <strong>{{ $reviews->total() }}</strong> {{ Str::plural('review', $reviews->total()) }}
            @endif
        </p>
    @endif

    <div class="requests-toolbar">
        <div class="requests-toolbar-left">
            @if ($canCreate)
                <a href="{{ route(PerformanceReviews::routeName($user, 'create')) }}" class="requests-btn requests-btn--primary">+ New Review</a>
            @endif
        </div>
        <div class="requests-toolbar-right">
            <form method="GET" action="{{ route($indexRoute) }}" class="requests-search-form">
                <input type="hidden" name="status" value="{{ $status }}" />
                @if ($isManagerList)
                    <input type="hidden" name="scope" value="{{ $reviewScope }}" />
                @endif
                <input type="text" name="search" placeholder="Search employee, reviewer, status..." value="{{ $search }}" aria-label="Search reviews" />
                <button type="submit" class="requests-btn requests-btn--search">Search</button>
                <a href="{{ route($indexRoute, $clearParams) }}" class="requests-btn requests-btn--secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="requests-filter-bar">
        <div class="requests-filters" role="tablist" aria-label="Filter by status">
            @foreach ($filters as $key => $label)
                <a href="{{ route($indexRoute, array_filter(['status' => $key, 'search' => $search] + $scopeQuery)) }}"
                   class="requests-filter requests-filter--{{ $key }} {{ $status === $key ? 'is-active' : '' }}"
                   role="tab"
                   aria-selected="{{ $status === $key ? 'true' : 'false' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="requests-table-wrap">
        <table class="requests-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Reviewer</th>
                <th>Period</th>
                <th>Date</th>
                <th>Status</th>
                <th style="min-width: 180px;">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($reviews as $index => $item)
                @php
                    $openUrl = route(PerformanceReviews::routeName($user, 'show'), $item);
                    $reviewer = PerformanceReviews::displayName(PerformanceReviews::assignedSenior($item->employee))
                        ?: ($item->reviewer_name ?: '—');
                @endphp
                <tr class="row-click" onclick="window.location='{{ $openUrl }}'">
                    <td>{{ $reviews->firstItem() + $index }}</td>
                    <td>{{ $item->employee_name }}</td>
                    <td>{{ $reviewer }}</td>
                    <td>{{ $item->periodLabel() }}</td>
                    <td>{{ $item->review_date?->format('d M Y') ?: '—' }}</td>
                    <td class="request-status-cell">
                        <span class="request-badge request-badge--{{ PerformanceReviews::badgeClass($item->status) }}">
                            {{ $item->statusLabel($user->role ?? null) }}
                        </span>
                    </td>
                    <td class="request-actions-cell" onclick="event.stopPropagation();">
                        <div class="request-actions">
                            <a class="request-btn request-btn--view" href="{{ $openUrl }}">Open</a>
                            @if ($item->isDraft() && ! $isSenior)
                                <a class="request-btn request-btn--approve" href="{{ route(PerformanceReviews::routeName($user, 'edit'), $item) }}">Edit</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="requests-empty">{{ $empty }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <p class="requests-hint">Click any row to open the full monthly performance review.</p>

    @if ($reviews->hasPages())
        <div class="requests-pagination">{{ $reviews->links() }}</div>
    @endif
</div>
@endsection