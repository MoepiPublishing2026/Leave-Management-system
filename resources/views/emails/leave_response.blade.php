@extends('emails.layouts.client-safe', [
    'title' => 'Leave request update',
    'preheader' => 'Your leave request status has been updated.',
])

@section('content')
@php
    $status = \App\Support\LeaveStatusLabels::display($leaveRequest->status);
    $dashboardUrl = \App\Support\LeavePortalLinks::myRequestsUrl($leaveRequest->user, $leaveRequest->id);
@endphp

    <p style="margin:0 0 16px;">{{ \App\Support\EmailGreeting::helloUser($leaveRequest->user) }}</p>
    <p style="margin:0 0 16px;">Your leave request status has been updated to <strong>{{ $status }}</strong>.</p>
    <p style="margin:0 0 16px;">
        {{ $leaveRequest->leave_type }} leave from {{ $leaveRequest->start_date }} to {{ $leaveRequest->end_date }}@include('emails.partials.half-day-slot', ['leaveRequest' => $leaveRequest, 'asInline' => true]).
    </p>

    @if (strtolower((string) $leaveRequest->status) === 'rejected' && filled($leaveRequest->rejection_reason))
        <p style="margin:0 0 16px;">Reason for rejection: {{ $leaveRequest->rejection_reason }}</p>
    @endif

    @if (strtolower((string) $leaveRequest->status) === 'approved' && filled($leaveRequest->approval_comment))
        <p style="margin:0 0 16px;">Note: {{ $leaveRequest->approval_comment }}</p>
    @endif

    @if (strtolower((string) $leaveRequest->status) === 'approved' && \App\Support\LeaveProofRules::requiresDoctorsLetter($leaveRequest))
        @php $proofDeadline = \App\Support\LeaveProofRules::proofDeadlineAt($leaveRequest); @endphp
        <p style="margin:0 0 16px;">
            A doctor's letter is required for this sick leave. Please upload it from My Leave Requests by
            <strong>{{ $proofDeadline?->format('d M Y') ?? 'the deadline' }}</strong>
            (the day after the leave ends). If it is not uploaded in time, the leave will be changed to unpaid.
        </p>
    @endif

    @include('emails.partials.primary-button', ['url' => $dashboardUrl])
@endsection
