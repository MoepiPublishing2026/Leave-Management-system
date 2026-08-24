@extends('emails.layouts.client-safe', [
    'title' => 'New leave request submitted',
    'preheader' => 'A leave request is waiting for review.',
])

@section('content')
@php
    $applicant = trim(($leaveRequest->user->first_name ?? '').' '.($leaveRequest->user->last_name ?? '')) ?: 'An employee';
@endphp

    <p style="margin:0 0 16px;">{{ \App\Support\EmailGreeting::hello($recipientFirstName ?? null) }}</p>
    <p style="margin:0 0 16px;"><strong>{{ $applicant }}</strong> has submitted a leave request.</p>
    <p style="margin:0 0 16px;">
        {{ $leaveRequest->leave_type }} leave from {{ $leaveRequest->start_date }} to {{ $leaveRequest->end_date }}
        ({{ \App\Support\LeaveDaysFormatter::format($workingDays) }} working day(s)).
    </p>

    @include('emails.partials.primary-button', ['url' => url('/login')])
@endsection
