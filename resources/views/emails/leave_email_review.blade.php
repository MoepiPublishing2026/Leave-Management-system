@extends('layouts.letterhead-document')

@section('title', 'Review leave request #'.$leaveRequest->id)

@section('content')
@php
    $user = $leaveRequest->user;
    $name = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: 'Staff member';
    $isManagerApplicant = $leaveRequest->isManagerApplicant();
    $flashSuccess = $flashSuccess ?? null;
    $flashError = $flashError ?? null;
@endphp

    <h1 class="lh-doc-title">Leave Request Decision</h1>
    <p class="lh-sub">
        Status:
        <span class="lh-pill {{ $awaiting ? '' : 'lh-pill--ok' }}">{{ $statusLabel }}</span>
    </p>

    @if ($flashSuccess)
        <div class="lh-flash lh-flash--ok">{{ $flashSuccess }}</div>
    @endif
    @if ($flashError)
        <div class="lh-flash lh-flash--err">{{ $flashError }}</div>
    @endif

    <table class="lh-meta">
        <tr><th>Request #</th><td>{{ $leaveRequest->id }}</td></tr>
        <tr><th>Applicant</th><td>{{ $name }}</td></tr>
        <tr><th>Role</th><td>{{ \App\Support\RoleLabels::display($user->role ?? null) }}</td></tr>
        <tr><th>Department</th><td>{{ $user->department ?? '—' }}</td></tr>
        <tr><th>Email</th><td>{{ $user->email ?? '—' }}</td></tr>
        <tr><th>Leave type</th><td>{{ $leaveRequest->leave_type }}</td></tr>
        <tr><th>Start</th><td>{{ $leaveRequest->start_date }}</td></tr>
        <tr><th>End</th><td>{{ $leaveRequest->end_date }}</td></tr>
        <tr><th>Working days</th><td>{{ \App\Support\LeaveDaysFormatter::format($workingDays) }}</td></tr>
        <tr><th>Remaining balance</th><td>{{ $remainingBalance }}</td></tr>
        @if (! $isManagerApplicant)
            <tr><th>Manager</th><td>{{ $recommenderName }}</td></tr>
            <tr><th>Recommendation</th><td>{{ $recLabel }}</td></tr>
        @else
            <tr><th>Approval path</th><td>Direct Senior Manager decision</td></tr>
        @endif
    </table>

    <div class="lh-section">
        <h2>Applicant comment</h2>
        <div class="lh-section-body">{{ $leaveRequest->important_comment ?: 'None' }}</div>
    </div>

    @if (! $isManagerApplicant)
        <div class="lh-section">
            <h2>Manager comments</h2>
            <div class="lh-section-body">{{ $leaveRequest->manager_recommendation_comment ?: 'None' }}</div>
        </div>
    @endif

    <div class="lh-section">
        <h2>Supporting documents</h2>
        <div class="lh-section-body">
            <div class="lh-files">
                @forelse ($attachments as $file)
                    <a href="{{ $file['viewUrl'] }}" target="_blank" rel="noopener">View {{ $file['name'] }}</a>
                    <a href="{{ $file['downloadUrl'] }}">Download</a>
                @empty
                    None attached
                @endforelse
            </div>
        </div>
    </div>

    @if ($awaiting && empty($isOverdue))
        @if (! $canApproveWithProof && $proofBlockReason)
            <p class="lh-note">{{ $proofBlockReason }}</p>
        @endif
        <div class="lh-actions" style="flex-direction:column;align-items:stretch;">
            @if ($canApproveWithProof)
                <form method="POST" action="{{ $approveUrl }}">
                    @csrf
                    <label for="approval_comment" style="display:block;font-weight:700;margin:0 0 8px;">Note for the employee (optional)</label>
                    <textarea id="approval_comment" name="approval_comment" class="lh-comment" maxlength="1000" placeholder="You can leave this blank.">{{ old('approval_comment') }}</textarea>
                    @error('approval_comment')
                        <p class="lh-note">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="lh-btn lh-btn--approve" style="margin-top:12px;">Approve</button>
                </form>
            @else
                <span class="lh-btn lh-btn--disabled">Approve (proof required)</span>
            @endif
        </div>
        <div class="lh-reject-box">
            <form method="POST" action="{{ $rejectUrl }}">
                @csrf
                <label for="rejection_reason">Reject with reason</label>
                <textarea id="rejection_reason" name="rejection_reason" required maxlength="500" placeholder="Enter rejection reason…">{{ old('rejection_reason') }}</textarea>
                @error('rejection_reason')
                    <p class="lh-note">{{ $message }}</p>
                @enderror
                <button type="submit" class="lh-btn lh-btn--reject" style="margin-top:12px;">Reject</button>
            </form>
        </div>
    @elseif ($awaiting && ! empty($isOverdue))
        <p class="lh-note">The requested start date has passed, so this request can no longer be approved or rejected.</p>
    @elseif (strtolower(trim((string) ($leaveRequest->status ?? ''))) === 'withdrawn')
        <p class="lh-note" style="color:#166534;">This request was withdrawn. No decision is needed.</p>
    @else
        <p class="lh-note" style="color:#166534;">This decision has already been submitted. Approve and Reject are no longer available.</p>
    @endif
@endsection
