@extends('layouts.manager')

@section('title', 'Team Leave Calendar')

@section('content')
@include('partials.calendar-page-styles')

@include('partials.calendar-page-shell', [
    'pageClass' => 'manager-calendar-page',
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
