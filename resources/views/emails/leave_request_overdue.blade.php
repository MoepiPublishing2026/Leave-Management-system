@extends('emails.layouts.client-safe', [
    'title' => 'Leave request #'.($leaveRequest->id ?? '').' overdue',
    'preheader' => $messageBody ?? 'A leave request start date has passed.',
])

@section('content')
@php
    $type = $leaveRequest->leave_type ?: 'leave';
    $buttons = [
        [
            'url' => $reviewUrl,
            'label' => 'Open in app',
            'bg' => '#a2d109',
            'color' => '#0a1347',
        ],
    ];
    $links = [
        ['url' => $reviewUrl, 'label' => 'Open in app', 'color' => '#0a1347'],
    ];
@endphp

    <p style="margin:0 0 16px;">{{ \App\Support\EmailGreeting::hello($recipientFirstName ?? null) }}</p>
    <p style="margin:0 0 16px;">{{ $messageBody }}</p>
    <p style="margin:0 0 16px;">
        Request <strong>#{{ $leaveRequest->id }}</strong> —
        {{ $type }} leave from {{ $leaveRequest->start_date }} to {{ $leaveRequest->end_date }}.
    </p>

    @include('emails.partials.action-button-row', ['buttons' => $buttons])
    @include('emails.partials.action-fallback-links', ['links' => $links])
@endsection
