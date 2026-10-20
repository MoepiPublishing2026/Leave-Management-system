@php
    $editable = $editable ?? false;
    $showStatus = $showStatus ?? false;
    $employeeName = $employeeName ?? ($review->employee_name ?? '');
    $reviewerName = $reviewerName ?? '';
    $statusLabel = $statusLabel ?? '';
    $monthNames = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];
    $periodMonth = (int) old('period_month', $review->period_month ?: now()->month);
    $periodYear = (int) old('period_year', $review->period_year ?: now()->year);
    $reviewDate = old('review_date', optional($review->review_date)->format('Y-m-d') ?: now()->format('Y-m-d'));
@endphp

<div class="pr-card">
    <h2>Employee Information</h2>
    <div class="pr-row">
        <div class="pr-label">Employee</div>
        @if ($editable)
            <input type="hidden" name="employee_id" value="{{ $employeeId ?? $review->employee_id }}">
        @endif
        <input type="text" value="{{ $employeeName }}" readonly>
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
        @if ($editable)
            <input type="text" name="review_period" value="{{ old('review_period', $review->review_period ?: '1 Month') }}" required>
        @else
            <input type="text" value="{{ $review->review_period ?: '—' }}" readonly>
        @endif
    </div>
    <div class="pr-row">
        <div class="pr-label">Date of Review</div>
        @if ($editable)
            <input type="date" name="review_date" value="{{ $reviewDate }}" required>
        @else
            <input type="date" value="{{ $review->review_date?->format('Y-m-d') }}" readonly>
        @endif
    </div>
    <div class="pr-row">
        <div class="pr-label">Month</div>
        @if ($editable)
            <select name="period_month" required>
                @foreach ($monthNames as $num => $label)
                    <option value="{{ $num }}" @selected($periodMonth === $num)>{{ $label }}</option>
                @endforeach
            </select>
        @else
            <input type="text" value="{{ $monthNames[$periodMonth] ?? $periodMonth }}" readonly>
        @endif
    </div>
    <div class="pr-row">
        <div class="pr-label">Year</div>
        @if ($editable)
            <select name="period_year" required>
                @for ($year = now()->year - 1; $year <= now()->year + 1; $year++)
                    <option value="{{ $year }}" @selected($periodYear === $year)>{{ $year }}</option>
                @endfor
            </select>
        @else
            <input type="text" value="{{ $periodYear ?: '—' }}" readonly>
        @endif
    </div>
    @if ($showStatus)
        <div class="pr-row">
            <div class="pr-label">Status</div>
            <input type="text" value="{{ $statusLabel !== '' ? $statusLabel : $review->statusLabel() }}" readonly>
        </div>
    @endif
</div>
