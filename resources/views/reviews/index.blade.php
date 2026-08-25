@extends($layout ?? 'layouts.app')

@section('title', 'Monthly Performance Reviews')

@section('content')
@include('partials.requests-page-styles')
@include('partials.performance-review-styles')

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
    $clearParams = ['status' => $status];
@endphp

<div class="requests-page pr-reviews-page">
    <h1>Monthly Performance Reviews</h1>
    <p class="requests-page-lead">
        {{ $lead }}
        @if ($reviews->total() > 0)
            · Showing <strong>{{ $reviews->total() }}</strong> {{ Str::plural('review', $reviews->total()) }}
        @endif
    </p>

    <div class="requests-toolbar">
        <div class="requests-toolbar-left">
            @if ($canCreate)
                <a href="{{ route(PerformanceReviews::routeName($user, 'create')) }}" class="requests-btn requests-btn--primary">+ New review</a>
            @endif
        </div>
        <div class="requests-toolbar-right">
            <form method="GET" action="{{ route($indexRoute) }}" class="requests-search-form">
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
                <a href="{{ route($indexRoute, array_filter(['status' => $key, 'search' => $search])) }}"
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
