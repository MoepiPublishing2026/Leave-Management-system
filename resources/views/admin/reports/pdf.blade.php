@extends('layouts.pdf')

@section('title', $title ?? 'Leave Report')

@section('styles')
    @include('partials.reports-pdf-styles')
@endsection

@section('content')
    <div class="header">
        <p class="title">{{ $title ?? 'Leave Requests Report' }}</p>
        <p class="subtitle">Summary and recent activity for the selected period{{ !empty($subtitle) ? ' — '.$subtitle : '' }}.</p>
        <p class="meta">Generated: {{ optional($generatedAt)->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
    </div>

    @include('partials.reports-pdf-cards')
    @include('partials.reports-pdf-table', [
        'nameHeading' => 'Applicant',
        'showRole' => true,
    ])
@endsection
