@extends('layouts.manager')

@section('title', 'Leave Reports')

@section('content')
@include('partials.reports-page-styles')
@include('partials.reports-index-content', [
    'filterRoute' => 'manager.reports',
    'exportRoute' => 'manager.reports.export_pdf',
    'pageTitle' => 'Leave Requests Report',
])
@endsection
