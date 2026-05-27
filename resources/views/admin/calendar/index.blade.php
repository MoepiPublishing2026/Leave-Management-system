@extends('layouts.admin')

@section('content')
@include('partials.calendar-page-styles')

@include('partials.calendar-page-shell', [
    'pageClass' => 'admin-calendar-page',
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
