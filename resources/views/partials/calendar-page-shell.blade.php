@php
    $pageClass = $pageClass ?? 'calendar-page';
    $daysWithLeave = (int) ($summary['days_with_leave'] ?? 0);
@endphp

<div class="{{ $pageClass }} calendar-page calendar-page--interactive">
    <h1 id="calendar-heading">{{ $calendarTitle }}</h1>

    <div class="calendar-shell mt-3">
        @include('partials.calendar-month-picker', [
            'routeName' => $calendarRoute,
            'month' => $month,
            'year' => $year,
        ])

        <div class="calendar-panel">
            @include('partials.calendar-grid', [
                'month' => $month,
                'year' => $year,
                'startDay' => $startDay,
                'daysInMonth' => $daysInMonth,
                'events' => $events,
                'publicHolidays' => $publicHolidays,
                'today' => $today,
                'interactive' => $interactive,
                'showUserInModal' => $showUserInModal,
            ])
        </div>

        <div class="calendar-meta">
            @if($daysWithLeave === 0)
                <p class="calendar-summary">
                    @if($showUserInModal ?? true)
                        No approved leave scheduled this month.
                    @else
                        You have no approved leave scheduled this month.
                    @endif
                </p>
            @else
                <p class="calendar-summary">
                    @if($showUserInModal ?? true)
                        <strong>{{ $daysWithLeave }}</strong> working {{ Str::plural('day', $daysWithLeave) }} with approved leave
                        @if(($summary['total_entries'] ?? 0) > $daysWithLeave)
                            · <strong>{{ $summary['total_entries'] }}</strong> total entries
                        @endif
                    @else
                        <strong>{{ $daysWithLeave }}</strong> working {{ Str::plural('day', $daysWithLeave) }} with your approved leave.
                    @endif
                </p>
            @endif

            @include('partials.calendar-legend', ['showUserInModal' => $showUserInModal])
        </div>
    </div>

    @include('partials.calendar-details-modal', [
        'events' => $events,
        'showUserInModal' => $showUserInModal,
    ])
</div>
