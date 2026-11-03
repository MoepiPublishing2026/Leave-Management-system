@extends($layout ?? 'layouts.app')

@section('title', 'Monthly Performance Reviews')

@section('content')
@include('partials.requests-page-styles')
@include('partials.performance-review-styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
html body .pr-reviews-page { font-family:'Poppins',sans-serif; }
html body .pr-reviews-page h1 { color:#263248; font-weight:700; letter-spacing:0; }
html body .pr-reviews-page .requests-page-lead { color:#000; }
html body .pr-reviews-page .requests-page-lead strong { color:#a2b222; }

html body .pr-reviews-page .requests-toolbar {
    background:#d8e3e1 !important; border:0 !important; border-radius:14px !important;
    padding:14px 16px !important; box-shadow:none !important; align-items:center;
}
html body .pr-reviews-page .requests-btn--primary,
html body .pr-reviews-page .requests-btn--search,
html body .pr-reviews-page .requests-btn--secondary {
    background:#D5E11B !important; color:#111 !important; border:0 !important;
    border-radius:8px !important; padding:8px 14px !important; font-weight:700 !important;
    min-height: 54px !important; font-size:13px !important;
}
html body .pr-reviews-page .requests-search-form input[type="text"] {
    background:#fff url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23a2b222' stroke-width='2.2' stroke-linecap='round'><circle cx='11' cy='11' r='7'/><path d='M16 16l5 5'/></svg>") no-repeat 14px center / 20px 20px !important;
    border:0 !important; border-radius:12px !important;
    padding:14px 18px 14px 44px !important; min-width:260px;
}

html body .pr-reviews-page .requests-filter-bar {
    display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;
    background:transparent !important; border:0 !important; box-shadow:none !important; padding:0 !important;
}
html body .pr-reviews-page .requests-filters {
    display:inline-flex; gap:20px; background:#d8e3e1 !important; border:0 !important;
    border-radius:14px; padding:12px 20px;
}
html body .pr-reviews-page .requests-filter {
    background:transparent !important; color:#263248 !important; font-weight:700 !important;
    padding:0 !important; border:0 !important; box-shadow:none !important;
}
html body .pr-reviews-page .requests-filter.is-active { background:transparent !important; color:#2379b0 !important; }

html body .pr-reviews-page .pr-reviews-scope-header {
    max-width: none;
    margin: 0 0 10px;
    padding: 0;
    align-items: flex-start;
}
html body .pr-reviews-page .pr-reviews-scope-header h1 { margin: 0; }

.pr-review-cards { display:flex; flex-direction:column; gap:22px; margin-top:8px; }
.pr-review-card {
    display:flex; align-items:center; gap:16px; flex-wrap:wrap;
    background:#fff; border:1px solid #ddd; border-radius:16px;
    box-shadow:6px 8px 14px rgba(0,0,0,.18);
    padding:18px 22px; cursor:pointer;
}
.pr-review-card:hover { box-shadow:8px 10px 18px rgba(0,0,0,.22); }
.pr-review-card__avatar {
    width:56px; height:56px; border-radius:50%; flex:none;
    display:inline-flex; align-items:center; justify-content:center;
    background:#c5d931; color:#111; font-weight:800; font-size:1.05rem;
}
.pr-review-card__main { min-width:0; flex:1 1 220px; }
.pr-review-card__name { margin:0; font-size:1.15rem; font-weight:800; color:#1e293b; }
.pr-review-card__sub { margin:4px 0 0; font-size:.9rem; color:#6b7280; font-weight:500; }
.pr-review-card__date { color:#334155; font-weight:600; font-size:.95rem; white-space:nowrap; }
.pr-review-card__aside {
    display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-left:auto;
}
.pr-status {
    display:inline-flex; align-items:center; gap:6px;
    border:1.5px solid #94a3b8; border-radius:999px;
    padding:6px 14px; font-size:.82rem; font-weight:700; background:#fff; color:#475569;
}
.pr-status svg { width:14px; height:14px; }
.pr-status--submitted { background:#e8f4ff; border-color:#7eb6e0; color:#2379b0; }
.pr-status--draft { background:#eee; border-color:#9ca3af; color:#4b5563; }
.pr-status--reviewed { background:#eaf6c8; border-color:#a2b222; color:#4b5c0e; }
.pr-status--awaiting { background:#e8f4ff; border-color:#7eb6e0; color:#2379b0; }
.pr-card-btn {
    display:inline-flex; align-items:center; gap:6px;
    background:#fff; border:2px solid #c5d931; color:#6b7a14;
    border-radius:999px; padding:6px 16px; font-size:.85rem; font-weight:600;
    text-decoration:none;
}
.pr-card-btn:hover { background:#f6fadf; color:#5a6810; }
.pr-card-btn svg { width:14px; height:14px; }
.pr-reviews-empty {
    text-align: center;
    padding: 80px;
    color: #263248;
    font-weight: 700;
    background: #fff;
    border: 2px solid #cfd6d4;
    border-radius: 14px;
}
@media (max-width: 700px) {
    .pr-review-card { align-items:flex-start; }
    .pr-review-card__aside { margin-left:0; width:100%; }
    .pr-review-card__date { width:100%; }
}
</style>

@php
    use App\Support\PerformanceReviews;
    $user = $user ?? auth()->user();
    $isSenior = strtolower((string) ($user->role ?? '')) === 'admin';
    $indexRoute = PerformanceReviews::routeName($user, 'index');
    $filters = $isSenior
        ? ['all' => 'All', 'submitted' => 'Awaiting review', 'reviewed' => 'Reviewed']
        : ['all' => 'All', 'draft' => 'Draft', 'submitted' => 'Submitted', 'reviewed' => 'Reviewed'];
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
                <p class="requests-page-lead">{{ $lead }}</p>
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
        <p class="requests-page-lead">{{ $lead }}</p>
    @endif

    <div class="requests-toolbar-1">
        <div class="requests-toolbar-left-1">
                @if ($canCreate)
                    <a href="{{ route(PerformanceReviews::routeName($user, 'create')) }}" class="requests-btn requests-btn--primary">+ New Request</a>
                @endif
        </div>
        <div class="requests-toolbar-right-1">
            <form method="GET" action="{{ route($indexRoute) }}" class="requests-search-form-1">
                <input type="hidden" name="status" value="{{ $status }}" />
                @if ($isManagerList)
                    <input type="hidden" name="scope" value="{{ $reviewScope }}" />
                @endif
                <input type="text" name="search" placeholder="Search name, type, status, dates..." value="{{ $search }}" aria-label="Search reviews" />
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

    @if ($reviews->isEmpty())
        <div class="pr-reviews-empty">{{ $empty }}</div>
    @else
        <div class="pr-review-cards">
            @foreach ($reviews as $item)
                @php
                    $canEditCard = $item->isDraft() && ! $isSenior && (int) $item->employee_id === (int) $user->id;
                    $openUrl = route(PerformanceReviews::routeName($user, 'show'), $item);
                    $editUrl = $canEditCard
                        ? route(PerformanceReviews::routeName($user, 'edit'), $item)
                        : $openUrl;
                    $reviewer = PerformanceReviews::displayName(PerformanceReviews::finalRater($item->employee))
                        ?: ($item->reviewer_name ?: '—');
                    $statusKey = strtolower(trim((string) $item->status));
                    $statusText = $item->statusLabelFor($user);
                    $statusClass = match ($statusKey) {
                        'submitted' => $isSenior || $reviewScope === 'team' ? 'awaiting' : 'submitted',
                        'reviewed' => 'reviewed',
                        default => 'draft',
                    };
                    $initials = collect(preg_split('/\s+/', trim((string) $item->employee_name)))
                        ->filter()
                        ->take(2)
                        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                        ->implode('');
                @endphp
                <article class="pr-review-card" onclick="window.location='{{ $openUrl }}'">
                    <div class="pr-review-card__avatar" aria-hidden="true">{{ $initials ?: '—' }}</div>
                    <div class="pr-review-card__main">
                        <h3 class="pr-review-card__name">{{ $item->employee_name }}</h3>
                        <p class="pr-review-card__sub">Reviewed by {{ $reviewer }} · Period {{ $item->periodLabel() }}</p>
                    </div>
                    <div class="pr-review-card__date">{{ $item->review_date?->format('j M Y') ?: '—' }}</div>
                    <div class="pr-review-card__aside" onclick="event.stopPropagation();">
                        <span class="pr-status pr-status--{{ $statusClass }}">
                            @if ($statusKey === 'submitted' || $statusKey === 'reviewed')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16.5 9"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><rect x="5" y="3.5" width="14" height="17" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
                            @endif
                            {{ $statusText }}
                        </span>
                        <a class="pr-card-btn" href="{{ $canEditCard ? $editUrl : $openUrl }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                            {{ $canEditCard ? 'Edit' : 'View' }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    @if ($reviews->hasPages())
        <div class="requests-pagination">{{ $reviews->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
