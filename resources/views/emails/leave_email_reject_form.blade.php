@extends('layouts.letterhead-document')

@section('title', 'Reject leave request')

@section('content')
@php
    $user = $leaveRequest->user;
    $name = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: 'Staff member';
    $isManagerApplicant = $leaveRequest->isManagerApplicant();
    $attachments = $attachments ?? [];
@endphp

    <h1 class="lh-doc-title">Reject Leave Request</h1>

    <table class="lh-meta">
        <tr><th>Request #</th><td>{{ $leaveRequest->id }}</td></tr>
        <tr><th>Applicant</th><td>{{ $name }}</td></tr>
        <tr><th>Role</th><td>{{ \App\Support\RoleLabels::display($user->role ?? null) }}</td></tr>
        <tr><th>Leave type</th><td>{{ $leaveRequest->leave_type }}</td></tr>
        <tr><th>Start</th><td>{{ $leaveRequest->start_date }}</td></tr>
        <tr><th>End</th><td>{{ $leaveRequest->end_date }}</td></tr>
        @include('emails.partials.half-day-slot', ['leaveRequest' => $leaveRequest, 'asMetaRow' => true])
        <tr><th>Working days</th><td>{{ \App\Support\LeaveDaysFormatter::format($workingDays ?? 0) }}</td></tr>
        <tr><th>Remaining balance</th><td>{{ $remainingBalance ?? '—' }}</td></tr>
        @if (! $isManagerApplicant)
            <tr><th>Manager</th><td>{{ $recommenderName ?? '—' }}</td></tr>
        @else
            <tr><th>Approval path</th><td>Direct Senior Manager decision</td></tr>
        @endif
    </table>

    @if (count($attachments) > 0)
        <div class="lh-section">
            <h2>Supporting documents</h2>
            <div class="lh-section-body">
                <div class="lh-files">
                    @foreach ($attachments as $file)
                        <a href="{{ $file['viewUrl'] }}" target="_blank" rel="noopener">View {{ $file['name'] }}</a>
                        <a href="{{ $file['downloadUrl'] }}">Download</a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="lh-reject-box">
        <form method="POST" action="{{ $submitUrl }}">
            @csrf
            <label for="rejection_reason">Rejection reason (required)</label>
            <textarea id="rejection_reason" name="rejection_reason" required maxlength="500">{{ old('rejection_reason') }}</textarea>
            @error('rejection_reason')
                <p class="lh-note">{{ $message }}</p>
            @enderror
            <button type="submit" class="lh-btn lh-btn--reject" style="margin-top:12px;">Confirm rejection</button>
        </form>
    </div>
@endsection
