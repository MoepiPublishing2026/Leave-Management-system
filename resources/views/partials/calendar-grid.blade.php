@php
    $today = $today ?? now()->toDateString();
    $interactive = $interactive ?? true;
    $showUserInModal = $showUserInModal ?? true;
@endphp

<div class="calendar" role="grid" aria-labelledby="calendar-heading">
    @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dayName)
        <div class="day-header" role="columnheader">{{ $dayName }}</div>
    @endforeach

    @for ($i = 0; $i < $startDay; $i++)
        <div class="calendar-spacer" aria-hidden="true"></div>
    @endfor

    @for ($day = 1; $day <= $daysInMonth; $day++)
        @php
            $carbonDate = \Carbon\Carbon::create($year, $month, $day);
            $date = $carbonDate->toDateString();
            $isWeekend = $carbonDate->isWeekend();
            $holidayName = $publicHolidays[$date] ?? null;
            $isHoliday = $holidayName !== null;
            $isToday = $date === $today;
            $isDisabled = $isWeekend || $isHoliday;
            $leaveEntries = $events[$date] ?? [];
            $hasLeave = count($leaveEntries) > 0;
            $leaveCount = count($leaveEntries);
            $disabledReason = null;
            if ($isHoliday) {
                $disabledReason = 'Public holiday';
            } elseif ($isWeekend) {
                $disabledReason = 'Weekend';
            }

            $dayClasses = ['day'];
            if ($hasLeave) {
                $dayClasses[] = 'has-leave';
            }
            if ($isToday) {
                $dayClasses[] = 'day-today';
            }
            if ($isHoliday) {
                $dayClasses[] = 'day-holiday';
            } elseif ($isWeekend) {
                $dayClasses[] = 'day-weekend';
            }

            if ($showUserInModal) {
                $leaveCountFull = $leaveCount === 1 ? '1 on leave' : $leaveCount . ' on leave';
            } else {
                $leaveCountFull = $leaveCount === 1 ? 'On leave' : $leaveCount . ' on leave';
            }

            $ariaParts = [$carbonDate->format('l, F j, Y')];
            if ($isToday) {
                $ariaParts[] = 'Today';
            }
            if ($holidayName) {
                $ariaParts[] = 'Public holiday: ' . $holidayName;
            }
            if ($hasLeave) {
                $ariaParts[] = $leaveCountFull;
            } elseif ($isToday) {
                $ariaParts[] = 'No leave today';
            }
            if ($isWeekend && !$isHoliday) {
                $ariaParts[] = 'Weekend';
            }
        @endphp
        <div
            class="{{ implode(' ', $dayClasses) }}"
            role="gridcell"
            tabindex="{{ ($interactive && !$isDisabled) ? '0' : '-1' }}"
            aria-label="{{ implode('. ', $ariaParts) }}"
            @if($interactive && !$isDisabled)
                data-calendar-date="{{ $date }}"
            @endif
        >
            <span class="day-number">{{ $day }}</span>
            @include('partials.calendar-day-labels', [
                'hasLeave' => $hasLeave,
                'isToday' => $isToday,
                'leaveCountTitle' => $leaveCountFull,
                'leaveCountFull' => $leaveCountFull,
                'leaveCountShort' => $leaveCount,
                'holidayName' => $holidayName,
                'isDisabled' => $isDisabled,
                'disabledReason' => $disabledReason,
            ])
        </div>
    @endfor
</div>

@if($interactive)
    @once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-calendar-date]').forEach(function (cell) {
                cell.addEventListener('click', function () {
                    if (typeof window.showCalendarDetails === 'function') {
                        window.showCalendarDetails(cell.dataset.calendarDate);
                    }
                });
                cell.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        if (typeof window.showCalendarDetails === 'function') {
                            window.showCalendarDetails(cell.dataset.calendarDate);
                        }
                    }
                });
            });
        });
    </script>
    @endonce
@endif
