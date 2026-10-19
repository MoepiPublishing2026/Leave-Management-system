@extends('layouts.admin')

@section('title', 'Manager Leave Requests')

@section('content')
@include('partials.requests-page-styles')

@php
    use App\Support\LeaveProofRules;
    use App\Support\LeaveAttachmentLinks;
@endphp

<div class="requests-page">
    <h1>Manager Leave Requests</h1>
    <p class="requests-page-lead">
        Review and approve leave submitted by managers.
        @if ($requests->total() > 0)
            · {{ $requests->total() }} {{ Str::plural('request', $requests->total()) }}
        @endif
    </p>

    @include('partials.requests-toolbar', [
        'formRoute' => 'admin.manager-requests',
        'filterRoute' => 'admin.manager-requests',
        'status' => $status,
        'search' => $search ?? '',
        'searchPlaceholder' => 'Search manager, type, status, dates...',
        'statusFilters' => [
            'all' => 'All',
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'overdue' => 'Overdue',
        ],
    ])

    <div class="requests-table-wrap">
        <table class="requests-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>Manager</th>
                <th>Start</th>
                <th>End</th>
                <th>Submitted</th>
                <th>Status</th>
                <th style="min-width: 180px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($requests as $index => $req)
                @php
                    $fileLinks = LeaveAttachmentLinks::forRequest($req);
                    $managerName = trim(($req->user->first_name ?? '') . ' ' . ($req->user->last_name ?? '')) ?: 'Manager';
                    $rowStatus = strtolower(trim((string) ($req->status ?? '')));
                    $canApproveWithProof = LeaveProofRules::canApprove($req);
                    $proofBlockReason = LeaveProofRules::approvalBlockedReason($req);
                    $certificateStatusLabel = LeaveProofRules::doctorsLetterStatusLabel($req);
                    $doctorsLetterMissing = LeaveProofRules::isProofPastDeadline($req);

                    $details = [
                        'id' => $req->id,
                        'employee' => $managerName,
                        'email' => $req->user->email ?? '',
                        'department' => $req->user->department ?? '',
                        'type' => (string) ($req->leave_type ?? ''),
                        'start' => $req->start_date ? \Carbon\Carbon::parse($req->start_date)->format('d M Y') : '',
                        'end' => $req->end_date ? \Carbon\Carbon::parse($req->end_date)->format('d M Y') : '',
                        'status' => $rowStatus,
                        'isOverdue' => $req->isOverdue(),
                        'comment' => (string) ($req->important_comment ?? ''),
                        'files' => $fileLinks,
                        'proofDeferred' => LeaveProofRules::allowsDeferredProof($req->leave_type) && LeaveProofRules::requiresProof($req->leave_type),
                        'proofAttached' => LeaveProofRules::hasProofAttached($req),
                        'proofDeadline' => LeaveProofRules::proofDeadlineAt($req) ? LeaveProofRules::proofDeadlineAt($req)->format('d M Y') : '',
                        'certificateNote' => (string) (LeaveProofRules::managerCertificateNote($req) ?? ''),
                        'proofStatusLabel' => (string) ($certificateStatusLabel ?? ''),
                        'doctorsLetterMissing' => $doctorsLetterMissing,
                        'proofBlockReason' => (string) ($proofBlockReason ?? ''),
                        'messagesUrl' => route('messages.leave', $req),
                        'rejectionReason' => (string) ($req->rejection_reason ?? ''),
                        'approvalComment' => (string) ($req->approval_comment ?? ''),
                    ];
                    $details = array_merge($details, \App\Support\LeaveReviewChecklist::forRequest($req), $req->popupMeta());
                @endphp
                <tr class="row-click {{ $req->isOverdue() ? 'overdue-row' : '' }}" data-details='@json($details)'>
                    <td>{{ $requests->firstItem() + $index }}</td>
                    <td>{{ $req->leave_type }}</td>
                    <td>{{ $managerName }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->start_date)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</td>
                    <td>{{ $req->created_at ? \Carbon\Carbon::parse($req->created_at)->format('d M Y H:i') : '—' }}</td>
                    <td class="request-status-cell">
                        @if ($req->isOverdue())
                            <span class="request-badge request-badge--overdue">Overdue</span>
                        @else
                            <span class="request-badge request-badge--{{ $rowStatus === 'withdrawn' ? 'withdrawn' : $rowStatus }}">{{ ucfirst($rowStatus) }}</span>
                        @endif
                        @if ($certificateStatusLabel)
                            <span class="request-badge {{ $doctorsLetterMissing ? 'request-badge--letter-missing' : 'request-badge--letter-required' }}">{{ $certificateStatusLabel }}</span>
                        @endif
                    </td>
                    <td class="request-actions-cell" onclick="event.stopPropagation();">
                        <div class="request-actions">
                            @if (in_array($rowStatus, ['pending', 'pending_senior'], true) && ! $req->isOverdue())
                                <div class="request-actions-inline">
                                    @if ($canApproveWithProof)
                                        <form class="senior-approve-form" action="{{ route('admin.manager-requests.approve', $req) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="approval_comment" class="approval-comment-input" value="" />
                                            <button type="submit" class="request-btn request-btn--approve">Approve</button>
                                        </form>
                                    @else
                                        <span class="request-actions-note" title="{{ $proofBlockReason }}">Awaiting proof</span>
                                    @endif
                                    <button type="button" class="request-btn request-btn--reject" onclick="openRejectPopup({{ $req->id }})">Reject</button>
                                </div>
                            @elseif ($req->isOverdue() && in_array($rowStatus, ['pending', 'pending_senior'], true))
                                <span class="request-actions-note">Start date has passed, so this request can no longer be approved or rejected.</span>
                            @elseif ($rowStatus === 'rejected')
                                <a href="{{ route('admin.manager-requests.show_reason', $req) }}" class="request-link">View Reason</a>
                            @endif
                            <a href="{{ route('messages.leave', $req) }}" class="request-link">Messages</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="requests-empty">No manager leave requests found for this filter.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <p class="requests-hint">Click any row to view full request details.</p>

    <div class="requests-pagination">
        {{ $requests->links('pagination::bootstrap-5') }}
    </div>
</div>

<form id="reject-hidden-form" method="POST" style="display:none;">
    @csrf
    @method('PATCH')
    <input type="hidden" name="rejection_reason" id="reject-hidden-reason" />
</form>

@include('partials.requests-page-scripts', [
    'showEmployee' => true,
    'showDays' => true,
    'showHandledMeta' => true,
    'backLabel' => '← Back to requests',
])

<script>
const rejectUrlTemplate = @json(url('/admin/manager-requests/__ID__/reject'));

function openRejectPopup(requestId) {
    Swal.fire({
        title: 'Reject leave request?',
        input: 'textarea',
        inputLabel: 'Reason for rejection',
        inputPlaceholder: 'Enter the reason...',
        inputAttributes: { maxlength: 500 },
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Reject',
        inputValidator: (value) => {
            if (!value || !String(value).trim()) {
                return 'A rejection reason is required.';
            }
            return null;
        },
    }).then((result) => {
        if (!result.isConfirmed) return;
        const form = document.getElementById('reject-hidden-form');
        const reason = document.getElementById('reject-hidden-reason');
        form.action = rejectUrlTemplate.replace('__ID__', String(requestId));
        reason.value = String(result.value).trim();
        form.submit();
    });
}
</script>
@endsection
