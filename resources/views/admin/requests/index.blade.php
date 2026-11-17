@extends('layouts.admin')

@section('title', 'All Leave Requests')

@section('content')
@include('partials.requests-page-styles')

<style>
    /* Keep Messages / actions flush to the right of each request card */
    html body main .requests-page .requests-table tr.row-click {
        grid-template-columns: max-content max-content minmax(0, 1fr) !important;
    }
    html body main .requests-page .requests-table td:nth-child(8),
    html body main .requests-page .requests-table td.request-actions-cell {
        grid-column: 3 !important;
        grid-row: 4 !important;
        justify-self: stretch !important;
        width: 100% !important;
        min-width: 0 !important;
        text-align: right !important;
    }
    html body main .requests-page .request-actions {
        width: 100% !important;
        justify-content: flex-end !important;
        margin-left: auto !important;
    }
</style>

@php
    use App\Support\LeaveProofRules;
    use App\Support\LeaveAttachmentLinks;
@endphp

<div class="requests-page">
    <h1>All Leave Requests</h1>

    @include('partials.requests-toolbar', [
        'formRoute' => 'admin.requests',
        'filterRoute' => 'admin.requests',
        'status' => $status,
        'payFilter' => $payFilter ?? 'all',
        'search' => $search ?? '',
        'monthFilter' => $monthFilter ?? null,
        'employeeFilter' => $employeeFilter ?? null,
        'searchPlaceholder' => 'Search name, type, status, dates...',
        'statusFilters' => [
            'all' => 'All',
            'pending' => 'Awaiting Decision',
            'pending_manager' => 'With Manager',
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
                <th>Requested By</th>
                <th>Start</th>
                <th>End</th>
                <th>Submitted</th>
                <th>Status</th>
                <th style="min-width: 200px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($requests as $index => $req)
                @php
                    $fileLinks = LeaveAttachmentLinks::forRequest($req);
                    $isManager = $req->isManagerApplicant();

                    $applicantFirst = $req->user->first_name ?? '';
                    $applicantLast = $req->user->last_name ?? '';
                    $applicantFull = trim($applicantFirst . ' ' . $applicantLast) ?: 'User';

                    $handledName = trim(($req->handledBy->first_name ?? '') . ' ' . ($req->handledBy->last_name ?? ''));
                    $lateReasonName = trim(($req->overdueEscalatedBy->first_name ?? '') . ' ' . ($req->overdueEscalatedBy->last_name ?? ''));
                    $rowStatus = strtolower(trim((string) ($req->status ?? '')));
                    $awaitingSenior = $rowStatus === 'pending_senior'
                        || ($rowStatus === 'pending' && $isManager);
                    $canDecide = $awaitingSenior;
                    $canApproveWithProof = $canDecide ? LeaveProofRules::canApprove($req) : false;
                    $proofBlockReason = $canDecide ? LeaveProofRules::approvalBlockedReason($req) : null;
                    $certificateStatusLabel = LeaveProofRules::doctorsLetterStatusLabel($req);
                    $doctorsLetterMissing = LeaveProofRules::isProofPastDeadline($req);
                    $currentYear = (int) now()->year;
                    $requestYear = $req->start_date ? (int) \Carbon\Carbon::parse($req->start_date)->year : $currentYear;
                    $isPreviousYear = $requestYear === ($currentYear - 1);
                    $canDelete = $isPreviousYear;
                    $statusLabel = \App\Support\LeaveStatusLabels::display($rowStatus, $req->isOverdue());
                    $statusBadge = \App\Support\LeaveStatusLabels::badgeClass($rowStatus, $req->isOverdue());
                    $recLabel = match (strtolower((string) ($req->manager_recommendation ?? ''))) {
                        'approve' => 'Manager recommended approval',
                        'reject' => 'Manager recommended rejection',
                        default => '',
                    };

                    $details = [
                        'id' => $req->id,
                        'employee' => $applicantFull,
                        'designation' => (string) ($req->user->designation ?? ''),
                        'email' => $req->user->email ?? '',
                        'department' => $req->user->department ?? '',
                        'type' => (string) ($req->leave_type ?? ''),
                        'start' => $req->start_date ? \Carbon\Carbon::parse($req->start_date)->format('d M Y') : '',
                        'end' => $req->end_date ? \Carbon\Carbon::parse($req->end_date)->format('d M Y') : '',
                        'status' => $rowStatus,
                        'isOverdue' => $req->isOverdue(),
                        'comment' => (string) ($req->important_comment ?? ''),
                        'files' => $fileLinks,
                        'messagesUrl' => route('messages.leave', $req),
                        'lateReasonSent' => (bool) $req->overdue_escalated_at,
                        'lateReason' => (string) ($req->overdue_escalation_reason ?? ''),
                        'lateReasonBy' => $lateReasonName,
                        'handledBy' => $handledName,
                        'handledAt' => $req->handled_at ? \Carbon\Carbon::parse($req->handled_at)->format('d M Y H:i') : '',
                        'handledAction' => (string) ($req->handled_action ?? ''),
                        'rejectionReason' => (string) ($req->rejection_reason ?? ''),
                        'approvalComment' => (string) ($req->approval_comment ?? ''),
                        'originalLeaveType' => (string) ($req->original_leave_type ?? ''),
                        'convertedToUnpaidAt' => $req->converted_to_unpaid_at?->format('d M Y H:i') ?? '',
                        'conversionReason' => (string) ($req->converted_to_unpaid_reason ?? ''),
                        'managerRecommendation' => $recLabel,
                        'managerRecommendationComment' => (string) ($req->manager_recommendation_comment ?? ''),
                    ];
                    $details = array_merge($details, \App\Support\LeaveReviewChecklist::forRequest($req), $req->popupMeta());

                    if ($canDecide || LeaveProofRules::requiresProof($req->leave_type)) {
                        $details['proofDeferred'] = LeaveProofRules::allowsDeferredProof($req->leave_type) && LeaveProofRules::requiresProof($req->leave_type);
                        $details['proofAttached'] = LeaveProofRules::hasProofAttached($req);
                        $details['proofDeadline'] = LeaveProofRules::proofDeadlineAt($req) ? LeaveProofRules::proofDeadlineAt($req)->format('d M Y') : '';
                        $details['certificateNote'] = (string) (LeaveProofRules::managerCertificateNote($req) ?? '');
                        $details['proofStatusLabel'] = (string) ($certificateStatusLabel ?? '');
                        $details['doctorsLetterMissing'] = $doctorsLetterMissing;
                        $details['proofBlockReason'] = (string) ($proofBlockReason ?? '');
                    }
                @endphp
                <tr class="row-click {{ $req->isOverdue() ? 'overdue-row' : '' }}" data-details='@json($details)'>
                    <td>{{ $requests->firstItem() + $index }}</td>
                    <td>
                        {{ $req->leave_type }}
                        @if (!empty($req->is_half_day))
                            <div class="request-half-slot">Half day{{ ($details['halfDayPeriodLabel'] ?? '') !== '' ? ': '.$details['halfDayPeriodLabel'] : '' }}</div>
                        @endif
                    </td>
                    <td>{{ $applicantFull }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->start_date)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</td>
                    <td>{{ $req->created_at ? \Carbon\Carbon::parse($req->created_at)->format('d M Y H:i') : '—' }}</td>
                    <td class="request-status-cell">
                        <span class="request-badge request-badge--{{ $statusBadge }}">@include('partials.request-status-icon', ['icon' => $statusBadge]){{ $statusLabel }}</span>
                        @if ($recLabel !== '')
                            <span class="request-badge request-badge--pending" title="{{ $req->manager_recommendation_comment }}">{{ $recLabel }}</span>
                        @endif
                        @if ($certificateStatusLabel)
                            <span class="request-badge {{ $doctorsLetterMissing ? 'request-badge--letter-missing' : 'request-badge--letter-required' }}">{{ $certificateStatusLabel }}</span>
                        @endif
                    </td>
                    <td class="request-actions-cell" onclick="event.stopPropagation();">
                        <div class="request-actions">
                            @if ($canDecide && ! $req->isOverdue())
                                <div class="request-actions-inline">
                                    @if ($canApproveWithProof)
                                        <form class="senior-approve-form" action="{{ route('admin.requests.approve', $req) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="approval_comment" class="approval-comment-input" value="" />
                                            <button type="submit" class="request-btn request-btn--approve">@include('partials.request-action-icon', ['name' => 'check']) Approve</button>
                                        </form>
                                    @else
                                        <span class="request-actions-note" title="{{ $proofBlockReason }}">Awaiting proof</span>
                                    @endif
                                    <button type="button" class="request-btn request-btn--reject" onclick="openRejectPopup({{ $req->id }})">@include('partials.request-action-icon', ['name' => 'x']) Reject</button>
                                </div>
                            @elseif ($canDecide && $req->isOverdue())
                                <span class="request-actions-note">Start date has passed, so this request can no longer be approved or rejected.</span>
                            @elseif ($rowStatus === 'rejected')
                                <a href="{{ route('admin.manager-requests.show_reason', $req) }}" class="request-link">@include('partials.request-action-icon', ['name' => 'view']) View Reason</a>
                            @endif
                            <a href="{{ route('messages.leave', $req) }}" class="request-link">@include('partials.request-action-icon', ['name' => 'message']) Messages</a>
                            @if ($canDelete)
                                <form class="delete-request-form" method="POST" action="{{ route('admin.requests.destroy', $req) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="request-btn request-btn--delete delete-request-btn" data-request-id="{{ $req->id }}">@include('partials.request-action-icon', ['name' => 'trash']) Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="requests-empty">No leave requests found for this filter.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <p class="requests-hint">Click any row to review the request, leave balance, supporting documents, and policy. Approved sick leave without a doctor's letter by the deadline is changed to unpaid automatically.</p>

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
    'seniorLayout' => true,
    'deleteWarning' => 'It will be permanently removed, including messages and attachments.',
])

<script>
const rejectUrlTemplate = @json(url('/admin/requests/__ID__/reject'));

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
