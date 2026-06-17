@if(!empty($isToday) && empty($hasLeave))
    <span class="no-leave-today-label">No leave today</span>
@elseif(!empty($hasLeave))
    <span class="leave-count" title="{{ $leaveCountTitle ?? '' }}">
        <span class="leave-count-full">{{ $leaveCountFull ?? '' }}</span>
        <span class="leave-count-short">{{ $leaveCountShort ?? '' }}</span>
    </span>
@endif

@if(!empty($holidayName))
    <span class="holiday-label" title="{{ $holidayName }}">{{ $holidayName }}</span>
@endif

@if(!empty($isDisabled))
    <span class="no-apply-icon" title="Cannot apply: {{ $disabledReason ?? 'Disabled' }}" aria-hidden="true">
        <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
            <circle cx="12" cy="12" r="9"></circle>
            <path d="M7.6 7.6l8.8 8.8"></path>
        </svg>
    </span>
@endif
