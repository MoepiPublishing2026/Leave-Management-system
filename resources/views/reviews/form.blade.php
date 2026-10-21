@extends($layout ?? 'layouts.app')

@section('title', $editing ? 'Edit Monthly Performance Review' : 'Monthly Performance Review')

@section('content')
@include('partials.performance-review-styles')
@include('partials.letterhead-document-styles')

@php
    use App\Support\PerformanceReviews;
    $user = $user ?? auth()->user();
    $action = $editing
        ? route(PerformanceReviews::routeName($user, 'update'), $review)
        : route(PerformanceReviews::routeName($user, 'store'));
@endphp

<div class="pr-page">
    <div class="pr-toolbar">
        <a class="pr-btn pr-btn--ghost" href="{{ route(PerformanceReviews::routeName($user, 'index'), $indexParams ?? []) }}">Back to list</a>
    </div>

    @if ($errors->any())
        <div class="form-alert" style="margin-bottom:14px;padding:12px 14px;border-radius:8px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;">
            <strong>Please fix the following:</strong>
            <ul style="margin:6px 0 0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="pr-doc">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="lh-accent" aria-hidden="true"></div>
        @include('partials.letterhead-document-header')

        <div class="lh-doc-body">
        <div class="pr-title">Monthly Performance Review</div>

        @include('reviews.partials.review-header-cards', [
            'editable' => true,
            'review' => $review,
            'employeeName' => PerformanceReviews::displayName($user),
            'employeeId' => $user->id,
            'reviewerName' => $reviewerName,
        ])

        <div class="pr-section">
            <h2>Goals achieved</h2>
            <textarea name="goals_achieved" required placeholder="List the goals achieved this month">{{ old('goals_achieved', $review->goals_achieved) }}</textarea>
        </div>

        <div class="pr-section">
            <h2>Quality of work</h2>
            <textarea name="quality_of_work" required placeholder="Comment on quality of work">{{ old('quality_of_work', $review->quality_of_work) }}</textarea>
        </div>

        <div class="pr-section">
            <h2>Examples of taking initiative and being creative</h2>
            <textarea name="initiative_examples" required placeholder="Add examples">{{ old('initiative_examples', $review->initiative_examples) }}</textarea>
        </div>

        <div class="pr-section">
            <h2>Areas for improvement</h2>
            <textarea name="areas_for_improvement" required placeholder="Add areas for improvement">{{ old('areas_for_improvement', $review->areas_for_improvement) }}</textarea>
        </div>

        <table class="pr-legend" style="max-width:280px;margin:22px 0 12px;">
            <tr><th colspan="2">Numerical values</th></tr>
            @foreach ($scoreLabels as $value => $label)
                <tr><th>{{ $value }}</th><td>{{ $label }}</td></tr>
            @endforeach
        </table>

        <div class="pr-score-block">
            <h2>Self-rating</h2>
            @include('reviews.partials.score-table', [
                'name' => 'self_scores',
                'values' => old('self_scores', $review->self_scores ?? []),
                'editable' => true,
                'criteria' => $criteria,
                'scoreLabels' => $scoreLabels,
            ])
        </div>

        <p class="pr-submit-hint">Complete every written section and rate yourself on every criterion before you submit. You can still save a draft if something is incomplete.</p>

        <div class="pr-actions">
            <button type="submit" name="intent" value="draft" class="pr-btn pr-btn--draft" formnovalidate>Save draft</button>
            <button type="submit" name="intent" value="submit" class="pr-btn pr-btn--primary">Submit review</button>
        </div>
        </div>

        @include('partials.letterhead-document-footer')
    </form>
</div>
@endsection
