@extends($layout ?? 'layouts.app')

@section('title', 'Monthly Performance Review')

@section('content')
@include('partials.performance-review-styles')
@include('partials.letterhead-document-styles')

@php
    use App\Support\PerformanceReviews;
    $user = $user ?? auth()->user();
    $showSelfScores = $showSelfScores ?? filled($review->self_scores);
    $showFinalScores = $showFinalScores ?? ($canAcknowledge || $review->isReviewed());
@endphp

<div class="pr-page">
    <div class="pr-toolbar">
        <a class="pr-btn pr-btn--ghost" href="{{ route(PerformanceReviews::routeName($user, 'index'), $indexParams ?? []) }}">Back to list</a>
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
                <td style="padding:10px 12px;" colspan="3">{{ $review->statusLabelFor($user) }}</td>
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

        @if ($showSelfScores || $showFinalScores)
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
        @endif

        @if ($showSelfScores)
            <div class="pr-score-block">
                <h2>Self-rating</h2>
                @include('reviews.partials.score-table', [
                    'name' => 'self_scores',
                    'values' => $review->self_scores ?? [],
                    'editable' => false,
                    'criteria' => $criteria,
                    'scoreLabels' => $scoreLabels,
                ])
                @if ($review->averageSelfScore())
                    <p style="margin-top:12px; font-weight:600;">Average self-rating: {{ $review->averageSelfScore() }} / 5</p>
                @endif
            </div>
        @endif

        @if ($showFinalScores)
            @if ($canAcknowledge)
                <form method="POST" action="{{ route(PerformanceReviews::routeName($user, 'acknowledge'), $review) }}">
                    @csrf
            @endif

            <div class="pr-score-block">
                <h2>Final rating</h2>
                @include('reviews.partials.score-table', [
                    'name' => 'scores',
                    'values' => old('scores', $review->scores ?? []),
                    'editable' => $canAcknowledge,
                    'criteria' => $criteria,
                    'scoreLabels' => $scoreLabels,
                ])
            </div>

            @if ($review->averageScore() && ! $canAcknowledge)
                <p style="margin-top:12px; font-weight:600;">Average final rating: {{ $review->averageScore() }} / 5</p>
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
                        <button type="submit" class="pr-btn pr-btn--primary">Save final rating and mark as reviewed</button>
                    </div>
                </form>
            @endif
        @endif
        </div>

        @include('partials.letterhead-document-footer')
    </div>
</div>
@endsection
