@extends($layout ?? 'layouts.app')

@section('title', 'Monthly Performance Reviews')

@section('content')
@include('partials.requests-page-styles')
@include('partials.performance-review-styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
html body .pr-reviews-page { font-family:'Poppins',sans-serif; }
html body .pr-reviews-page h1 { color:#263248; font-weight:700; letter-spacing:0; }
html body .pr-reviews-page .requests-page-lead { color:#64748b; }
html body .pr-reviews-page .requests-page-lead strong { color:#a2b222; }

html body .pr-reviews-page .requests-toolbar {
    display:flex !important; align-items:center !important; gap:16px !important;
    background:#e7eeed !important; border:0 !important; border-radius:14px !important;
    padding:14px 16px !important; box-shadow:none !important;
}
html body .pr-reviews-page .requests-toolbar-right {
    flex:1 1 auto !important; margin-left:0 !important; min-width:0 !important;
}
html body main .pr-reviews-page .requests-search-form {
    position:static !important; top:auto !important; right:auto !important; left:auto !important;
    display:flex !important; align-items:center !important; gap:14px !important;
    width:100% !important; max-width:none !important; margin:0 !important; z-index:auto !important;
}
html body .pr-reviews-page .requests-btn--primary,
html body .pr-reviews-page .requests-btn--search,
html body .pr-reviews-page .requests-btn--secondary {
    background:#dce84a !important; color:#1c1c1c !important; border:0 !important;
    border-radius:12px !important; padding:0 22px !important; height:48px !important;
    font-weight:700 !important; min-height:0 !important; font-size:15px !important;
    white-space:nowrap !important; display:inline-flex !important; align-items:center !important;
}
/* Reviewed pill + View/Edit button: oval */
html body .pr-reviews-page .pr-status,
html body .pr-reviews-page .pr-card-btn {
    border-radius: 10px !important;    /* ← rounded corners, not fully round ends */
    padding: 6px 16px !important;
}
html body .pr-reviews-page .requests-search-form input[type="text"] {
    flex:1 1 auto !important; width:auto !important; min-width:0 !important; height:48px !important;
    background:#fff !important; background-image:none !important;
    border:0 !important; border-radius:12px !important;
    padding:12px 18px !important; font-size:15px !important; color:#333 !important;
}

html body .pr-reviews-page .requests-filter-bar {
    display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;
    background:transparent !important; border:0 !important; box-shadow:none !important; padding:0 !important;
}
html body .pr-reviews-page .requests-filters {
    display:inline-flex; gap:20px; background:#e7eeed !important; border:0 !important;
    border-radius:14px; padding:12px 20px;
}
html body .pr-reviews-page .requests-filter {
    background:transparent !important; color:#8b939c !important; font-weight:600 !important;
    padding:0 !important; border:0 !important; box-shadow:none !important;
}
html body .pr-reviews-page .requests-filter.is-active { background:transparent !important; color:#253141 !important; font-weight:700 !important; }

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
    background:#dce84a; color:#1c1c1c; font-weight:800; font-size:1.05rem;
}
.pr-review-card__main { min-width:0; flex:1 1 220px; }
.pr-review-card__name { margin:0; font-size:1.15rem; font-weight:800; color:#1e293b; }
.pr-review-card__sub { margin:4px 0 0; font-size:.9rem; color:#6b7280; font-weight:500; }
.pr-review-card__date {
    color: #6e7174;
    font-weight: 600;
    font-size: 0.79rem;     /* ← date size (was .95rem) */
    white-space: nowrap;
     margin-right: 2cm; 
}
.pr-review-card__aside {
    display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-left:auto;
}
.pr-status {
    display:inline-flex; align-items:center; gap:6px;
    border:1.5px solid #94a3b8; border-radius:999px;
    padding:6px 14px; font-size:.82rem; font-weight:700; background:#fff; color:#475569;
}
.pr-status svg { width:14px; height:14px; }
.pr-status--submitted { background:#e7f4fc; border-color:#7eb6e0; color:#2f86c5; }
.pr-status--draft { background:#fff; border-color:#c5c9ce; color:#5c656e; }
.pr-status--reviewed { background:#f4f8d8; border-color:#dce84a; color:#3d4a12; }
.pr-status--awaiting { background:#e7f4fc; border-color:#7eb6e0; color:#2f86c5; }
.pr-card-btn {
    display:inline-flex; align-items:center; gap:6px;
    background:#fff; border:2px solid #dce84a; color:#3a3a3a;
    border-radius:999px; padding:6px 16px; font-size:.85rem; font-weight:600;
    text-decoration:none;
}
.pr-card-btn:hover { background:#f7fadf; color:#1c1c1c; }
.pr-card-btn svg { width:14px; height:14px; }
.pr-status svg,
.pr-card-btn svg {
    stroke: currentColor !important;
}
.pr-reviews-empty {
    text-align:center; padding:28px; color:#6b7280;
    background:#fff; border:1px dashed #cfd6d4; border-radius:14px;
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
        ? ['all' => 'All', 'submitted' => 'Awaiting Review', 'reviewed' => 'Reviewed']
        : ['all' => 'All', 'draft' => 'Draft', 'submitted' => 'Submitted', 'reviewed' => 'Reviewed'];
    $empty = $isSenior
        ? 'No reviews have been sent yet.'
        : 'No performance reviews yet.';
    $search = $search ?? '';
    $reviewScope = $reviewScope ?? 'all';
    $isManagerList = in_array($reviewScope, ['mine', 'team'], true);
    $scopeQuery = $isManagerList ? ['scope' => $reviewScope] : [];
    $clearParams = array_filter(['status' => $status] + $scopeQuery);
    if ($reviewScope === 'team') {
        $empty = 'No team reviews yet.';
    } elseif ($reviewScope === 'mine') {
        $empty = 'No performance reviews yet.';
    }
@endphp

<div class="requests-page pr-reviews-page">
    @if ($isManagerList)
        <div class="mgr-page-header pr-reviews-scope-header">
            <div class="mgr-page-header__main">
                <h1>Monthly Performance Reviews</h1>
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
                <input type="text" name="search" placeholder="Search employee, reviewer, status" value="{{ $search }}" aria-label="Search reviews" />
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
