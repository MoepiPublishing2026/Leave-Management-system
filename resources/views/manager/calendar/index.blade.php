@extends('layouts.manager')

@section('title', $calendarTitle ?? 'Leave Calendar')

@section('content')
@include('partials.calendar-page-styles')

@php
    $isPersonalCalendar = ($calendarRoute ?? '') === 'manager.myCalendar';
    $calQuery = array_filter(['month' => request('month'), 'year' => request('year')]);
@endphp

<div class="mgr-page-header calendar-page-header">
    <div class="mgr-page-header__main">
        <h1 id="calendar-heading">{{ $calendarTitle }}</h1>
    </div>
    @include('partials.manager-scope-switch', [
        'scope' => $isPersonalCalendar ? 'personal' : 'team',
        'personalUrl' => route('manager.myCalendar', $calQuery),
        'teamUrl' => route('manager.calendar', $calQuery),
        'personalLabel' => 'My calendar',
        'teamLabel' => 'Calendar',
    ])
</div>

@include('partials.calendar-page-shell', [
    'pageClass' => 'manager-calendar-page',
    'hideTitle' => true,
    'calendarTitle' => $calendarTitle,
    'calendarRoute' => $calendarRoute,
    'month' => $month,
    'year' => $year,
    'startDay' => $startDay,
    'daysInMonth' => $daysInMonth,
    'events' => $events,
    'publicHolidays' => $publicHolidays,
    'today' => $today,
    'interactive' => $interactive,
    'showUserInModal' => $showUserInModal,
    'summary' => $summary,
])
@endsection
