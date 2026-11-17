@extends('emails.layouts.client-safe', [
    'title' => 'Pending leave review reminder',
    'preheader' => 'A leave request is still awaiting your review.',
])

@section('content')
@php
    $applicant = trim(($leaveRequest->user->first_name ?? '').' '.($leaveRequest->user->last_name ?? '')) ?: 'an employee';
    $reviewUrl = $reviewUrl ?? url('/manager/requests?status=pending&search='.$leaveRequest->id);
@endphp

    <p style="margin:0 0 16px;">{{ \App\Support\EmailGreeting::hello($recipientFirstName ?? null) }}</p>
    <p style="margin:0 0 16px;">Leave request <strong>#{{ $leaveRequest->id }}</strong> for <strong>{{ $applicant }}</strong> is still awaiting your review.</p>
    <p style="margin:0 0 16px;">
        {{ $leaveRequest->leave_type }} leave from {{ $leaveRequest->start_date }} to {{ $leaveRequest->end_date }}
        ({{ \App\Support\LeaveDaysFormatter::format($workingDays) }} working day(s))@include('emails.partials.half-day-slot', ['leaveRequest' => $leaveRequest, 'asInline' => true]).
    </p>

    @include('emails.partials.primary-button', ['url' => $reviewUrl])
@endsection
