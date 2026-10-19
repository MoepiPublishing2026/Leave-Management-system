@extends('layouts.manager')

@section('content')
@include('partials.analytics-insights-page', [
    'indexRoute' => 'manager.analytics',
    'allPeopleLabel' => 'All employees',
    'activePeopleLabel' => 'Active Employees',
    'showManagerRole' => false,
])
@endsection
