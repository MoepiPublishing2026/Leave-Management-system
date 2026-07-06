@php
    $embedTitle = $embedTitle ?? 'Calendar';
    $calendarRoute = $calendarRoute ?? 'manager.dashboard';
    $routeParams = $routeParams ?? [];
@endphp
@include('partials.calendar-page-styles')
<div class="dashboard-calendar-embed">
    <div class="dashboard-calendar-embed__head">
        <h2 class="dashboard-calendar-embed__title">{{ $embedTitle }}</h2>
        @if(!empty($fullPageRoute))
            <a href="{{ route($fullPageRoute, $fullPageRouteParams ?? []) }}" class="dashboard-section-link">Open full calendar</a>
        @endif
    </div>
    <div class="calendar-shell">
        @include('partials.calendar-month-picker', [
            'routeName' => $calendarRoute,
            'routeParams' => $routeParams ?? [],
            'monthParam' => $monthParam ?? 'month',
            'yearParam' => $yearParam ?? 'year',
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
                'interactive' => $interactive ?? true,
                'showUserInModal' => $showUserInModal ?? false,
            ])
            <div class="calendar-meta" role="region" aria-label="Calendar summary">
                <p class="calendar-meta-title">Approved leave this month</p>
                @php $daysWithLeave = (int) ($summary['days_with_leave'] ?? 0); @endphp
                <p class="calendar-summary">
                    @if($daysWithLeave === 0)
                        No approved leave this month.
                    @else
                        <strong>{{ $daysWithLeave }}</strong> working {{ Str::plural('day', $daysWithLeave) }} with approved leave.
                    @endif
                </p>
                @include('partials.calendar-legend', ['showUserInModal' => $showUserInModal ?? false])
            </div>
        </div>
    </div>
    @include('partials.calendar-details-modal', [
        'events' => $events,
        'showUserInModal' => $showUserInModal ?? false,
        'today' => $today,
    ])
</div>
