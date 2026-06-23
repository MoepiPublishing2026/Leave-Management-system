@extends('layouts.app')

@section('title', 'My Leave Requests')

@section('content')
@include('partials.requests-page-styles')

@php
    use App\Support\LeaveProofRules;
    use App\Support\LeaveAttachmentLinks;
@endphp

<div class="requests-page">
    <h1>My Leave Requests</h1>
    <p class="requests-page-lead">
        Showing <strong>{{ ucfirst($status) }}</strong>
        @if ($leaveRequests->total() > 0)
            · {{ $leaveRequests->total() }} {{ Str::plural('request', $leaveRequests->total()) }}
        @endif
    </p>

    @include('partials.requests-toolbar', [
        'formRoute' => 'leave.myRequests',
        'filterRoute' => 'leave.myRequests',
        'status' => $status,
        'search' => $search ?? '',
        'showNewRequest' => true,
        'searchPlaceholder' => 'Search type, status, dates, comment...',
        'statusFilters' => [
            'all' => 'All',
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'withdrawn' => 'Withdrawn',
            'overdue' => 'Overdue',
        ],
    ])

    <div class="requests-table-wrap">
        <table class="requests-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>Start</th>
                <th>End</th>
                <th>Submitted</th>
                <th>Status</th>
                <th style="min-width: 180px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($leaveRequests as $index => $req)
                @php
                    $fileLinks = LeaveAttachmentLinks::forRequest($req);

                    $handledName = trim(($req->handledBy->first_name ?? '') . ' ' . ($req->handledBy->last_name ?? ''));
                    $lateReasonName = trim(($req->overdueEscalatedBy->first_name ?? '') . ' ' . ($req->overdueEscalatedBy->last_name ?? ''));
                    $rowStatus = strtolower(trim((string) ($req->status ?? '')));
                    $canWithdraw = $req->canBeWithdrawnByEmployee();
                    $canDelete = $req->canBeDeletedByEmployee();
                    $showAttachmentControls = LeaveProofRules::employeeCanManageAttachments($req);
                    $canSubmitAttachment = LeaveProofRules::employeeCanSubmitAttachmentNow($req);
                    $attachmentActionLabel = LeaveProofRules::employeeAttachmentActionLabel($req);
                    $canUploadProof = $canSubmitAttachment;
                    $showCertificateUpload = $showAttachmentControls;
                    $proofWindowOpens = LeaveProofRules::proofWindowOpensAt($req);
                    $proofDeadline = LeaveProofRules::proofDeadlineAt($req);
                    $proofStatusLabel = LeaveProofRules::employeeProofStatusLabel($req);
                    $doctorsLetterMissing = LeaveProofRules::isProofPastDeadline($req);

                    $details = [
                        'id' => $req->id,
                        'type' => (string) ($req->leave_type ?? ''),
                        'start' => $req->start_date ? \Carbon\Carbon::parse($req->start_date)->format('d M Y') : '',
                        'end' => $req->end_date ? \Carbon\Carbon::parse($req->end_date)->format('d M Y') : '',
                        'days' => $req->days ?? '',
                        'status' => $rowStatus,
                        'isOverdue' => $req->isOverdue(),
                        'comment' => (string) ($req->important_comment ?? ''),
                        'files' => $fileLinks,
                        'proofDeferred' => LeaveProofRules::allowsDeferredProof($req->leave_type) && LeaveProofRules::requiresProof($req->leave_type),
                        'proofAttached' => LeaveProofRules::hasProofAttached($req),
                        'proofDeadline' => $proofDeadline ? $proofDeadline->format('d M Y') : '',
                        'proofStatusLabel' => (string) ($proofStatusLabel ?? ''),
                        'doctorsLetterMissing' => $doctorsLetterMissing,
                        'lateReasonSent' => (bool) $req->overdue_escalated_at,
                        'lateReason' => (string) ($req->overdue_escalation_reason ?? ''),
                        'lateReasonBy' => $lateReasonName,
                        'handledBy' => $handledName,
                        'handledAt' => $req->handled_at ? \Carbon\Carbon::parse($req->handled_at)->format('d M Y H:i') : '',
                        'handledAction' => (string) ($req->handled_action ?? ''),
                        'rejectionReason' => (string) ($req->rejection_reason ?? ''),
                    ];
                @endphp
                <tr class="row-click {{ $req->isOverdue() ? 'overdue-row' : '' }}" data-details='@json($details)'>
                    <td>{{ $leaveRequests->firstItem() + $index }}</td>
                    <td>{{ $req->leave_type }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->start_date)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</td>
                    <td>{{ $req->created_at ? \Carbon\Carbon::parse($req->created_at)->format('d M Y H:i') : '—' }}</td>
                    <td class="request-status-cell">
                        @if ($req->isOverdue())
                            <span class="request-badge request-badge--overdue">Overdue</span>
                        @else
                            <span class="request-badge request-badge--{{ $rowStatus === 'withdrawn' ? 'withdrawn' : $rowStatus }}">{{ ucfirst($rowStatus) }}</span>
                        @endif
                        @if ($proofStatusLabel)
                            <span class="request-badge {{ $doctorsLetterMissing ? 'request-badge--letter-missing' : 'request-badge--letter-required' }}">{{ $proofStatusLabel }}</span>
                        @endif
                    </td>
                    <td class="request-actions-cell" onclick="event.stopPropagation();">
                        <div class="request-actions">
                            @if ($showAttachmentControls)
                                @if (count($fileLinks) > 0)
                                    <div class="request-existing-files">
                                        @foreach ($fileLinks as $fileLink)
                                            <button
                                                type="button"
                                                class="request-btn request-btn--view attachment-preview-btn"
                                                data-preview-url="{{ $fileLink['url'] }}"
                                                data-download-url="{{ $fileLink['downloadUrl'] }}"
                                                data-extension="{{ $fileLink['extension'] }}"
                                                data-label="{{ $fileLink['name'] }}"
                                                data-previewable="{{ $fileLink['previewable'] ? '1' : '0' }}"
                                            >View {{ $fileLink['label'] }}</button>
                                        @endforeach
                                    </div>
                                @endif
                                @if ($canSubmitAttachment)
                                <form class="proof-upload-form" method="POST" action="{{ route('leave.uploadProof', $req) }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="file_uploads[]" class="proof-upload-input" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple required>
                                    <button
                                        type="button"
                                        class="request-btn request-btn--approve proof-upload-btn"
                                        data-can-upload="1"
                                        data-is-replace="{{ count($fileLinks) > 0 ? '1' : '0' }}"
                                        data-opens-at="{{ $proofWindowOpens ? $proofWindowOpens->format('d M Y') : '' }}"
                                        data-deadline="{{ $proofDeadline ? $proofDeadline->format('d M Y') : '' }}"
                                        data-action-label="{{ $attachmentActionLabel }}"
                                    >{{ $attachmentActionLabel }}</button>
                                </form>
                                @endif
                            @endif
                            @if ($canWithdraw)
                                <form class="withdraw-form" method="POST" action="{{ route('leave.withdraw', $req->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="button" class="request-btn request-btn--withdraw withdraw-request-btn">Withdraw</button>
                                </form>
                            @endif
                            @if ($canDelete)
                                <form class="delete-request-form" method="POST" action="{{ route('leave.destroy', $req) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="request-btn request-btn--delete delete-request-btn" data-request-id="{{ $req->id }}">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="requests-empty">No leave requests found for this filter.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <p class="requests-hint">Click any row to view full request details.</p>

    <div class="requests-pagination">
        {{ $leaveRequests->links('pagination::bootstrap-5') }}
    </div>
</div>

@include('partials.requests-page-scripts', [
    'showEmployee' => false,
    'showDays' => true,
    'showHandledMeta' => true,
    'enableWithdraw' => true,
    'enableProofUpload' => true,
    'deleteWarning' => 'It will be permanently removed, including any attachments.',
])
@endsection
