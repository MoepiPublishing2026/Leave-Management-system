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
    $monthNames = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];
@endphp

<div class="pr-page">
    <div class="pr-toolbar">
        <a class="pr-btn pr-btn--ghost" href="{{ route(PerformanceReviews::routeName($user, 'index'), $indexParams ?? []) }}">Back to list</a>
    </div>

    @if ($errors->any() || session('error'))
        <div class="form-alert" style="margin-bottom:14px;padding:12px 14px;border-radius:8px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;">
            @if (session('error'))
                <strong>{{ session('error') }}</strong>
            @endif
            @if ($errors->any())
                <strong>Please fix the following:</strong>
                <ul style="margin:6px 0 0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
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


        <div class="pr-card">
    <h2>Employee Information</h2>
    <div class="pr-row">
        <div class="pr-label">Employee</div>
        <input type="hidden" name="employee_id" value="{{ $user->id }}">
        <input type="text" value="{{ PerformanceReviews::displayName($user) }}" readonly>
    </div>
    <div class="pr-row">
        <div class="pr-label">Reviewer</div>
        <input type="text" value="{{ $reviewerName }}" readonly>
    </div>
</div>

<div class="pr-card">
    <h2>Review Details</h2>
    <div class="pr-row">
        <div class="pr-label">Review Period</div>
        <input type="text" name="review_period" value="{{ old('review_period', $review->review_period ?: '1 Month') }}" required>
    </div>
    <div class="pr-row">
        <div class="pr-label">Date of Review</div>
        <input type="date" name="review_date" value="{{ old('review_date', optional($review->review_date)->format('Y-m-d') ?: now()->format('Y-m-d')) }}" required>
    </div>
    <div class="pr-row">
        <div class="pr-label">Month</div>
        <select name="period_month" required>
            @foreach ($monthNames as $num => $label)
                <option value="{{ $num }}" @selected((int) old('period_month', $review->period_month) === $num)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="pr-row">
        <div class="pr-label">Year</div>
        <select name="period_year" required>
            @for ($year = now()->year - 1; $year <= now()->year + 1; $year++)
                <option value="{{ $year }}" @selected((int) old('period_year', $review->period_year) === $year)>{{ $year }}</option>
            @endfor
        </select>
    </div>
</div>

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
