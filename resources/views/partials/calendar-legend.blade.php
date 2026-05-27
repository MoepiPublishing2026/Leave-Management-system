<div class="calendar-legend calendar-legend--compact" aria-label="Calendar legend">
    <div class="calendar-legend-row">
        <span class="calendar-legend-item">
            <span class="calendar-legend-swatch calendar-legend-swatch--leave"></span>
            Approved leave
        </span>
        <span class="calendar-legend-item">
            <span class="calendar-legend-swatch calendar-legend-swatch--holiday"></span>
            Public holiday
        </span>
        <span class="calendar-legend-item">
            <span class="calendar-legend-swatch calendar-legend-swatch--weekend"></span>
            Weekend
        </span>
        <span class="calendar-legend-item">
            <span class="calendar-legend-swatch calendar-legend-swatch--today"></span>
            Today
        </span>
    </div>
    <p class="calendar-legend-hint">
        @if($legendHint ?? null)
            {{ $legendHint }}
        @elseif($showUserInModal ?? true)
            Click a working day to see who is on leave and their leave types.
        @else
            Click a working day with approved leave to see your leave details.
        @endif
    </p>
</div>
