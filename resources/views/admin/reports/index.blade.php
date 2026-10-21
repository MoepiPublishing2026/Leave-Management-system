@extends('layouts.admin')

@section('title', 'Leave Reports')

@section('content')
@include('partials.dashboard-status-card-styles')
@include('partials.reports-page-styles')
@include('partials.reports-index-content', [
    'filterRoute' => 'admin.reports',
    'exportRoute' => 'admin.reports.export_pdf',
    'pageTitle' => 'Leave Requests Report',
])
@endsection
