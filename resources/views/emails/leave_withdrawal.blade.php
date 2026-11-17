@extends('emails.layouts.client-safe', [
    'title' => 'Leave request withdrawn',
    'preheader' => 'A leave request has been withdrawn.',
])

@section('content')
@php
    $applicant = trim(($leaveRequest->user->first_name ?? '').' '.($leaveRequest->user->last_name ?? '')) ?: 'an employee';
    $wasApproved = strtolower(trim((string) ($previousStatus ?? ''))) === 'approved';
@endphp

    <p style="margin:0 0 16px;">{{ \App\Support\EmailGreeting::hello($recipientFirstName ?? null) }}</p>
    <p style="margin:0 0 16px;">
        The {{ $wasApproved ? 'approved ' : '' }}leave request for <strong>{{ $applicant }}</strong> has been withdrawn.
    </p>
    <p style="margin:0 0 16px;">
        {{ $leaveRequest->leave_type }} leave from
        {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('d M Y') }}
        to
        {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('d M Y') }}@include('emails.partials.half-day-slot', ['leaveRequest' => $leaveRequest, 'asInline' => true]).
    </p>
    @if ($wasApproved)
        <p style="margin:0 0 16px;">The leave balance previously used by this approval is now available to the applicant again.</p>
    @endif

    @include('emails.partials.primary-button', ['url' => $portalUrl ?? url('/login')])
@endsection
