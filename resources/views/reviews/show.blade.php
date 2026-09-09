@extends($layout ?? 'layouts.app')

@section('title', 'Monthly Performance Review')

@section('content')
@include('partials.performance-review-styles')
@include('partials.letterhead-document-styles')

@php
    use App\Support\PerformanceReviews;
    $user = $user ?? auth()->user();
    $isSenior = strtolower((string) ($user->role ?? '')) === 'admin';
    $showScoring = $isSenior || $review->isReviewed();
@endphp

<div class="pr-page">
    <div class="pr-toolbar">
        <a class="pr-btn pr-btn--ghost" href="{{ route(PerformanceReviews::routeName($user, 'index')) }}">Back to list</a>
        @if ($canEdit)
            <a class="pr-btn pr-btn--secondary" href="{{ route(PerformanceReviews::routeName($user, 'edit'), $review) }}">Edit</a>
        @endif
    </div>

    <div class="pr-doc">
        <div class="lh-accent" aria-hidden="true"></div>
        @include('partials.letterhead-document-header')

        <div class="lh-doc-body">
        <div class="pr-title">Monthly Performance Review</div>

        <table class="pr-meta">
            <tr>
                <th>Employee</th>
                <td style="padding:10px 12px;">{{ $review->employee_name }}</td>
                <th>Reviewer</th>
                <td style="padding:10px 12px;">{{ $reviewerName ?: '—' }}</td>
            </tr>
            <tr>
                <th>Review period</th>
                <td style="padding:10px 12px;">{{ $review->review_period }} ({{ $review->periodLabel() }})</td>
                <th>Date of the review</th>
                <td style="padding:10px 12px;">{{ $review->review_date?->format('d M Y') ?: '—' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td style="padding:10px 12px;" colspan="3">{{ $review->statusLabel($user->role ?? null) }}</td>
            </tr>
        </table>

        <div class="pr-section">
            <h2>Goals achieved</h2>
            <div class="pr-readonly">{{ $review->goals_achieved ?: '—' }}</div>
        </div>
        <div class="pr-section">
            <h2>Quality of work</h2>
            <div class="pr-readonly">{{ $review->quality_of_work ?: '—' }}</div>
        </div>
        <div class="pr-section">
            <h2>Examples of taking initiative and being creative</h2>
            <div class="pr-readonly">{{ $review->initiative_examples ?: '—' }}</div>
        </div>
        <div class="pr-section">
            <h2>Areas for improvement</h2>
            <div class="pr-readonly">{{ $review->areas_for_improvement ?: '—' }}</div>
        </div>

        @if ($showScoring)
            @if ($canAcknowledge)
                <form method="POST" action="{{ route(PerformanceReviews::routeName($user, 'acknowledge'), $review) }}">
                    @csrf
            @endif

            <div class="pr-split">
                <table class="pr-id-table">
                    <tr><th>Employee</th><td>{{ $review->employee_name }}</td></tr>
                    <tr><th>Position</th><td>{{ $review->employee_position ?: '—' }}</td></tr>
                    <tr><th>Reviewer</th><td>{{ $reviewerName ?: '—' }}</td></tr>
                    <tr><th>Date of review</th><td>{{ $review->review_date?->format('d M Y') ?: '—' }}</td></tr>
                </table>
                <table class="pr-legend">
                    <tr><th colspan="2">Numerical values</th></tr>
                    @foreach ($scoreLabels as $value => $label)
                        <tr><th>{{ $value }}</th><td>{{ $label }}</td></tr>
                    @endforeach
                </table>
            </div>

            <table class="pr-score-table{{ $canAcknowledge ? '' : ' pr-score-readonly' }}">
                <thead>
                <tr>
                    <th>Criteria</th>
                    @foreach ($scoreLabels as $value => $label)
                        <th title="{{ $label }}">{{ $value }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach ($criteria as $key => $label)
                    @php $selected = (int) old('scores.'.$key, $review->scoreFor($key)); @endphp
                    <tr>
                        <td>{{ $label }}</td>
                        @foreach ($scoreLabels as $value => $scoreLabel)
                            <td>
                                @if ($canAcknowledge)
                                    <input type="radio" name="scores[{{ $key }}]" value="{{ $value }}" @checked($selected === (int) $value) title="{{ $scoreLabel }}" required>
                                @else
                                    <input type="radio" tabindex="-1" @checked($selected === (int) $value) title="{{ $scoreLabel }}">
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>

            @if ($review->averageScore() && ! $canAcknowledge)
                <p style="margin-top:12px; font-weight:600;">Average score: {{ $review->averageScore() }} / 5</p>
            @endif

            @if ($review->senior_comment && ! $canAcknowledge)
                <div class="pr-section" style="margin-top:18px;">
                    <h2>Comment</h2>
                    <div class="pr-readonly">{{ $review->senior_comment }}</div>
                </div>
            @endif

            @if ($canAcknowledge)
                    <label for="senior_comment" style="display:block;font-weight:700;margin:18px 0 6px;">Comment (optional)</label>
                    <textarea id="senior_comment" name="senior_comment" class="pr-comment">{{ old('senior_comment', $review->senior_comment) }}</textarea>
                    <div class="pr-actions">
                        <button type="submit" class="pr-btn pr-btn--primary">Save scores and mark as reviewed</button>
                    </div>
                </form>
            @endif
        @endif
        </div>

        @include('partials.letterhead-document-footer')
    </div>
</div>
@endsection
