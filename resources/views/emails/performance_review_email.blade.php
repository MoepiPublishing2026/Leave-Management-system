@extends('layouts.letterhead-document')

@section('title', 'Monthly Performance Review — '.$review->employee_name)

@section('styles')
@include('partials.performance-review-styles')
@endsection

@section('content')
@php
    $awaiting = $awaiting ?? false;
@endphp

    <h1 class="lh-doc-title">Monthly Performance Review</h1>

    @if (!empty($flashSuccess))
        <div class="lh-flash lh-flash--ok">{{ $flashSuccess }}</div>
    @endif
    @if (!empty($flashError))
        <div class="lh-flash lh-flash--err">{{ $flashError }}</div>
    @endif
    @if ($errors->any())
        <div class="lh-flash lh-flash--err">Please score every criterion (1–5) before submitting.</div>
    @endif

    @include('reviews.partials.review-header-cards', [
        'editable' => false,
        'review' => $review,
        'employeeName' => $review->employee_name,
        'reviewerName' => $reviewerName,
        'showStatus' => true,
        'statusLabel' => $awaiting ? 'Awaiting review' : 'Reviewed',
    ])

    <div class="lh-section"><h2>Goals achieved</h2><div class="lh-section-body">{{ $review->goals_achieved ?: '—' }}</div></div>
    <div class="lh-section"><h2>Quality of work</h2><div class="lh-section-body">{{ $review->quality_of_work ?: '—' }}</div></div>
    <div class="lh-section"><h2>Examples of taking initiative and being creative</h2><div class="lh-section-body">{{ $review->initiative_examples ?: '—' }}</div></div>
    <div class="lh-section"><h2>Areas for improvement</h2><div class="lh-section-body">{{ $review->areas_for_improvement ?: '—' }}</div></div>

    @if ($awaiting)
        <form method="POST" action="{{ $submitUrl }}">
            @csrf
    @endif

    <div class="lh-split">
        <table class="lh-id-table">
            <tr><th>Employee</th><td>{{ $review->employee_name }}</td></tr>
            <tr><th>Position</th><td>{{ $review->employee_position ?: '—' }}</td></tr>
            <tr><th>Reviewer</th><td>{{ $reviewerName ?: '—' }}</td></tr>
            <tr><th>Date of review</th><td>{{ $review->review_date?->format('d M Y') ?: '—' }}</td></tr>
        </table>
        <table class="lh-legend">
            <tr><th colspan="2">Numerical values</th></tr>
            @foreach ($scoreLabels as $value => $label)
                <tr><th>{{ $value }}</th><td>{{ $label }}</td></tr>
            @endforeach
        </table>
    </div>

    @if (filled($review->self_scores))
        <h2 style="margin:22px 0 10px;font-size:18px;color:#253141;">Self rating</h2>
        <table class="lh-scores">
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
                @php $selected = (int) $review->selfScoreFor($key); @endphp
                <tr>
                    <td>{{ $label }}</td>
                    @foreach ($scoreLabels as $value => $scoreLabel)
                        <td>{{ $selected === (int) $value ? '●' : '' }}</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
        @if ($review->averageSelfScore())
            <p style="font-weight:700;">Average self-rating: {{ $review->averageSelfScore() }} / 5</p>
        @endif
    @endif

    <h2 style="margin:22px 0 10px;font-size:18px;color:#253141;">Final rating</h2>
    <table class="lh-scores">
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
                        @if ($awaiting)
                            <input type="radio" name="scores[{{ $key }}]" value="{{ $value }}" @checked($selected === (int) $value) title="{{ $scoreLabel }}" required>
                        @else
                            {{ $selected === (int) $value ? '●' : '' }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>

    @if ($awaiting)
            <p style="margin:16px 0 6px; font-weight:700;">Comment (optional)</p>
            <textarea name="senior_comment" class="lh-comment">{{ old('senior_comment', $review->senior_comment) }}</textarea>
            <p style="margin-top:16px;"><button type="submit" class="lh-btn lh-btn--primary">Save final rating and mark as reviewed</button></p>
        </form>
    @else
        @if ($review->averageScore())
            <p style="font-weight:700;">Average final rating: {{ $review->averageScore() }} / 5</p>
        @endif
        @if ($review->senior_comment)
            <div class="lh-section"><h2>Comment</h2><div class="lh-section-body">{{ $review->senior_comment }}</div></div>
        @endif
    @endif

    <p class="lh-sub" style="margin-top:22px;">This secure link does not require login and expires in 7 days.</p>
@endsection
