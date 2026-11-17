@extends('emails.layouts.client-safe', [
    'title' => 'Leave changed to Unpaid',
    'preheader' => 'A leave request was changed to unpaid.',
])

@section('content')
@php
    $applicant = trim(($leaveRequest->user->first_name ?? '').' '.($leaveRequest->user->last_name ?? '')) ?: 'a staff member';
@endphp

    <p style="margin:0 0 16px;">{{ \App\Support\EmailGreeting::hello($recipientFirstName ?? null) }}</p>
    <p style="margin:0 0 16px;">
        Leave request <strong>#{{ $leaveRequest->id }}</strong> for <strong>{{ $applicant }}</strong> was changed to Unpaid leave because the required supporting documents were not submitted on time.
    </p>
    <p style="margin:0 0 16px;">
        Original type: {{ $leaveRequest->original_leave_type ?: 'Sick' }}.
        Dates: {{ $leaveRequest->start_date }} to {{ $leaveRequest->end_date }}@include('emails.partials.half-day-slot', ['leaveRequest' => $leaveRequest, 'asInline' => true]).
    </p>

    @include('emails.partials.primary-button', ['url' => $portalUrl ?? url('/login')])
@endsection
