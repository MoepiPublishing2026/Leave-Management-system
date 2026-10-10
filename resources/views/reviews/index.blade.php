@extends($layout ?? 'layouts.app')

@php
    use App\Support\PerformanceReviews;
    $user = $user ?? auth()->user();
    $role = strtolower((string) ($user->role ?? 'employee'));
    $isSenior = $role === 'admin';
    $reviewScope = $reviewScope ?? 'all';
    $isManagerTeam = $role === 'manager' && $reviewScope === 'team';
    $isManagerMine = $role === 'manager' && $reviewScope === 'mine';
    $indexRoute = PerformanceReviews::routeName($user, 'index');
    $scopeParam = $role === 'manager' ? ['scope' => $reviewScope] : [];
    $filters = match (true) {
        $isSenior, $isManagerTeam => ['all' => 'All', 'submitted' => 'Awaiting review', 'reviewed' => 'Reviewed'],
        $isManagerMine => ['all' => 'All', 'draft' => 'Drafts', 'submitted' => 'Sent', 'reviewed' => 'Reviewed'],
        default => ['all' => 'All', 'draft' => 'Drafts', 'submitted' => 'Sent', 'reviewed' => 'Reviewed'],
    };
    $pageTitle = match (true) {
        $isManagerTeam => 'Team Performance Reviews',
        $isManagerMine => 'My Performance Reviews',
        default => 'Monthly Performance Reviews',
    };
    $lead = match (true) {
        $isSenior => 'Manager reviews appear here after they are submitted. Give the final rating.',
        $isManagerTeam => 'Give the final rating after your team submits.',
        $isManagerMine => 'Rate yourself, then submit. Your senior manager will give the final rating.',
        default => 'Rate yourself, then submit. Your manager will give the final rating.',
    };
    $empty = match (true) {
        $isSenior => 'No manager reviews have been sent yet.',
        $isManagerTeam => 'No team reviews have been sent yet.',
        default => 'No performance reviews yet.',
    };
    $search = $search ?? '';
    $clearParams = array_merge($scopeParam, ['status' => $status]);
@endphp

@section('title', $pageTitle)

@section('content')
@include('partials.requests-page-styles')
@include('partials.performance-review-styles')

<div class="requests-page pr-reviews-page">
    @if ($role === 'manager')
        <div class="mgr-page-header">
            <div class="mgr-page-header__main">
                <h1>{{ $pageTitle }}</h1>
                <p class="requests-page-lead">
                    {{ $lead }}
                    @if ($reviews->total() > 0)
                        · Showing <strong>{{ $reviews->total() }}</strong> {{ Str::plural('review', $reviews->total()) }}
                    @endif
                </p>
            </div>
            @include('partials.manager-scope-switch', [
                'scope' => $isManagerMine ? 'personal' : 'team',
                'personalUrl' => route($indexRoute, ['scope' => 'mine']),
                'personalLabel' => 'My reviews',
                'teamUrl' => route($indexRoute, ['scope' => 'team']),
                'teamLabel' => 'Team reviews',
            ])
        </div>
    @else
        <h1>{{ $pageTitle }}</h1>
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
                <a href="{{ route(PerformanceReviews::routeName($user, 'create')) }}" class="requests-btn requests-btn--primary">+ New review</a>
            @endif
        </div>
        <div class="requests-toolbar-right">
            <form method="GET" action="{{ route($indexRoute) }}" class="requests-search-form">
                @if ($role === 'manager')
                    <input type="hidden" name="scope" value="{{ $reviewScope }}" />
                @endif
                <input type="hidden" name="status" value="{{ $status }}" />
                <input type="text" name="search" placeholder="Search employee, reviewer, status..." value="{{ $search }}" aria-label="Search reviews" />
                <button type="submit" class="requests-btn requests-btn--search">Search</button>
                <a href="{{ route($indexRoute, $clearParams) }}" class="requests-btn requests-btn--secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="requests-filter-bar">
        <div class="requests-filters" role="tablist" aria-label="Filter by status">
            @foreach ($filters as $key => $label)
                <a href="{{ route($indexRoute, array_filter(array_merge($scopeParam, ['status' => $key, 'search' => $search]))) }}"
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
                <th style="min-width: 180px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($reviews as $index => $item)
                @php
                    $openUrl = route(PerformanceReviews::routeName($user, 'show'), $item);
                    $reviewer = PerformanceReviews::displayName(PerformanceReviews::finalRater($item->employee))
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
                            {{ $item->statusLabelFor($user) }}
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
