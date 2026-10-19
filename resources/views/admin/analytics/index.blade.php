@extends('layouts.admin')

@section('content')
@include('partials.analytics-insights-page', [
    'indexRoute' => 'admin.analytics',
    'allPeopleLabel' => 'All employees',
    'activePeopleLabel' => 'Active Employees',
    'showManagerRole' => true,
])
@endsection
