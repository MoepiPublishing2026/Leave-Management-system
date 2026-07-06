@extends('layouts.manager')

@section('title', 'Leave Requests - ' . ucfirst($status))

@section('content')
@include('partials.requests-page-styles')

@php
    use App\Support\LeaveProofRules;
    use App\Support\LeaveAttachmentLinks;
    $overdueReasonCount = ($overdueNeedingReason ?? collect())->count();
@endphp

@if($overdueReasonCount > 0)
    <div id="overdueReasonLockOverlay" class="overdue-reason-lock-overlay" aria-hidden="true"></div>
    <style>
        body.overdue-reason-locked {
            overflow: hidden;
        }
        body.overdue-reason-locked nav.navbar,
        body.overdue-reason-locked main.content {
            pointer-events: none;
            user-select: none;
        }
        .overdue-reason-lock-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 19990;
            background: rgba(15, 23, 42, 0.45);
            pointer-events: auto;
        }
        body.overdue-reason-locked .overdue-reason-lock-overlay {
            display: block;
        }
    </style>
    <script>document.body.classList.add('overdue-reason-locked');</script>
@endif

<div class="requests-page" @if($overdueReasonCount > 0) data-overdue-reason-lock="1" @endif>
    <div class="mgr-page-header">
        <div class="mgr-page-header__main">
            <h1>Leave Requests</h1>
            <p class="requests-page-lead">
                Showing <strong>{{ ucfirst($status) }}</strong>
                @if ($requests->total() > 0)
                    · {{ $requests->total() }} {{ Str::plural('request', $requests->total()) }}
                @endif
            </p>
        </div>
        @include('partials.manager-scope-switch', [
            'scope' => 'team',
            'personalUrl' => route('manager.myLeave', ['status' => 'all']),
            'personalLabel' => 'My leave requests',
        ])
    </div>

    @include('partials.requests-toolbar', [
        'formRoute' => 'manager.requests.index',
        'filterRoute' => 'manager.requests.index',
        'status' => $status,
        'search' => $search ?? '',
        'monthFilter' => $monthFilter ?? null,
        'employeeFilter' => $employeeFilter ?? null,
        'searchPlaceholder' => 'Search name, type, status, dates...',
        'statusFilters' => [
            'all' => 'All',
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'overdue' => 'Overdue',
        ],
    ])

    @if($overdueReasonCount > 0)
        <div class="requests-flash requests-flash--error" id="overdueReasonBanner" role="alert">
            You have {{ $overdueReasonCount }} overdue {{ Str::plural('request', $overdueReasonCount) }} without a late response reason. Submit the reason for each one before you can continue.
        </div>
    @endif

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
                <th style="min-width: 180px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($requests as $index => $req)
                @php
                    $fileLinks = LeaveAttachmentLinks::forRequest($req);

                    $employeeFirst = $req->user->first_name ?? '';
                    $employeeLast = $req->user->last_name ?? '';
                    $employeeFull = trim($employeeFirst . ' ' . $employeeLast) ?: 'User';

                    $rowStatus = strtolower(trim((string) ($req->status ?? '')));
                    $currentYear = (int) now()->year;
                    $requestYear = $req->start_date ? (int) \Carbon\Carbon::parse($req->start_date)->year : $currentYear;
                    $isPreviousYear = $requestYear === ($currentYear - 1);
                    $canDelete = $isPreviousYear;

                    $canMessage = $req->managerCanOpenChat(auth()->user());
                    $canApproveWithProof = LeaveProofRules::canApprove($req);
                    $proofBlockReason = LeaveProofRules::approvalBlockedReason($req);
                    $proofDeferred = LeaveProofRules::allowsDeferredProof($req->leave_type) && LeaveProofRules::requiresProof($req->leave_type);
                    $certificateNote = LeaveProofRules::managerCertificateNote($req);
                    $certificateStatusLabel = LeaveProofRules::doctorsLetterStatusLabel($req);
                    $doctorsLetterMissing = LeaveProofRules::isProofPastDeadline($req);

                    $details = [
                        'id' => $req->id,
                        'employee' => $employeeFull,
                        'email' => $req->user->email ?? '',
                        'department' => $req->user->department ?? '',
                        'designation' => $req->user->designation ?? '',
                        'type' => (string) ($req->leave_type ?? ''),
                        'start' => $req->start_date ? \Carbon\Carbon::parse($req->start_date)->format('d M Y') : '',
                        'end' => $req->end_date ? \Carbon\Carbon::parse($req->end_date)->format('d M Y') : '',
                        'status' => $rowStatus,
                        'isOverdue' => $req->isOverdue(),
                        'comment' => (string) ($req->important_comment ?? ''),
                        'files' => $fileLinks,
                        'proofDeferred' => $proofDeferred,
                        'proofAttached' => LeaveProofRules::hasProofAttached($req),
                        'proofDeadline' => LeaveProofRules::proofDeadlineAt($req) ? LeaveProofRules::proofDeadlineAt($req)->format('d M Y') : '',
                        'certificateNote' => (string) ($certificateNote ?? ''),
                        'proofStatusLabel' => (string) ($certificateStatusLabel ?? ''),
                        'doctorsLetterMissing' => $doctorsLetterMissing,
                        'proofBlockReason' => (string) ($proofBlockReason ?? ''),
                        'messagesUrl' => $canMessage ? route('messages.leave', $req) : null,
                        'lateReasonSent' => (bool) $req->overdue_escalated_at,
                        'lateReason' => (string) ($req->overdue_escalation_reason ?? ''),
                        'rejectionReason' => (string) ($req->rejection_reason ?? ''),
                    ];
                @endphp
                <tr class="row-click {{ $req->isOverdue() ? 'overdue-row' : '' }}" data-details='@json($details)'>
                    <td>{{ $requests->firstItem() + $index }}</td>
                    <td>{{ $req->leave_type }}</td>
                    <td>{{ $employeeFull }}</td>
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
                            @if ($rowStatus === 'pending')
                                @if ($req->isOverdue())
                                    @if ($req->overdue_escalated_at)
                                        <span class="request-actions-note" title="{{ $req->overdue_escalation_reason ?? '' }}">Reason Sent</span>
                                    @else
                                        <button type="button" class="request-btn request-btn--escalate" onclick="openLateReasonPopup({{ $req->id }})">Submit Reason</button>
                                    @endif
                                @else
                                    <div class="request-actions-inline">
                                        @if ($canApproveWithProof)
                                            <form action="{{ route('manager.requests.approve', $req) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="request-btn request-btn--approve">Approve</button>
                                            </form>
                                        @else
                                            <span class="request-actions-note" title="{{ $proofBlockReason }}">Awaiting proof</span>
                                        @endif
                                        <button type="button" class="request-btn request-btn--reject" onclick="openRejectPopup({{ $req->id }})">Reject</button>
                                    </div>
                                @endif
                            @elseif ($rowStatus === 'rejected')
                                <a href="{{ route('manager.requests.show_reason', $req) }}" class="request-link">View Reason</a>
                            @endif
                            @if($canMessage)
                                <a href="{{ route('messages.leave', $req) }}" class="request-link">Messages</a>
                            @endif
                            @if($canDelete)
                                <form class="delete-request-form" method="POST" action="{{ route('manager.requests.destroy', $req) }}">
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
                    <td colspan="8" class="requests-empty">No leave requests found for this filter.</td>
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

<form id="late-reason-hidden-form" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="overdue_escalation_reason" id="late-reason-hidden-text" />
</form>

@include('partials.requests-page-scripts', [
    'showEmployee' => true,
    'showDays' => false,
    'showHandledMeta' => false,
    'deleteWarning' => 'It will be permanently removed, including messages and attachments.',
])

<script>
const rejectUrlTemplate = @json(url('/manager/requests/__ID__/reject'));
const lateReasonUrlTemplate = @json(url('/manager/requests/__ID__/escalate'));
let overdueReasonQueue = @json(($overdueNeedingReason ?? collect())->values());
const overdueReasonTotal = @json($overdueReasonCount ?? 0);
const csrfToken = document.querySelector('#late-reason-hidden-form input[name="_token"]')?.value || '';

function lockOverdueReasonPage() {
    document.body.classList.add('overdue-reason-locked');
}

function unlockOverdueReasonPage() {
    document.body.classList.remove('overdue-reason-locked');
}

function updateOverdueReasonBanner() {
    const banner = document.getElementById('overdueReasonBanner');
    if (!banner) return;
    const count = overdueReasonQueue.length;
    if (count <= 0) {
        banner.remove();
        return;
    }
    const label = count === 1 ? 'request' : 'requests';
    banner.textContent = `You have ${count} overdue ${label} without a late response reason. Submit the reason for each one before you can continue.`;
}

function openLateReasonPopup(requestId, options = {}) {
    const forced = options.forced === true;
    const meta = options.meta || {};
    const queuePosition = options.queuePosition || null;
    const queueTotal = options.queueTotal || null;
    const queueLabel = queuePosition && queueTotal
        ? `<p style="margin:0 0 10px;color:#64748b;font-size:0.9rem;">Overdue request ${queuePosition} of ${queueTotal}</p>`
        : '';
    const detailHtml = meta.employee
        ? `<div style="text-align:left;margin:0 0 12px;padding:12px 14px;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;">
                <strong>${meta.employee}</strong><br>
                ${meta.type || 'Leave'} leave · ${meta.start || ''}${meta.end ? ' to ' + meta.end : ''}
           </div>`
        : '';

    Swal.fire({
        icon: 'warning',
        title: forced ? 'Overdue request — reason required' : 'Late Response Reason',
        html: forced
            ? `${queueLabel}${detailHtml}<p style="margin:0;text-align:left;">This request is overdue. Explain why it was left unattended. You must complete all ${queueTotal} before continuing.</p>`
            : undefined,
        input: 'textarea',
        inputLabel: 'Why was this request left unattended?',
        inputPlaceholder: 'Type the reason...',
        inputAttributes: { 'aria-label': 'Late response reason' },
        showCancelButton: !forced,
        showCloseButton: !forced,
        confirmButtonText: 'Send to Admin',
        cancelButtonText: 'Cancel',
        allowOutsideClick: !forced,
        allowEscapeKey: !forced,
        preConfirm: (value) => {
            const v = (value || '').trim();
            if (!v) {
                Swal.showValidationMessage('Reason is required.');
                return false;
            }
            if (v.length > 1000) {
                Swal.showValidationMessage('Reason must be 1000 characters or less.');
                return false;
            }
            return v;
        }
    }).then((result) => {
        if (!result.isConfirmed) {
            if (forced) {
                promptForcedOverdueReasons();
            }
            return;
        }

        if (forced) {
            submitForcedOverdueReason(requestId, result.value);
            return;
        }

        const form = document.getElementById('late-reason-hidden-form');
        form.action = lateReasonUrlTemplate.replace('__ID__', String(requestId));
        document.getElementById('late-reason-hidden-text').value = result.value;
        form.submit();
    });
}

async function submitForcedOverdueReason(requestId, reason) {
    lockOverdueReasonPage();

    Swal.fire({
        title: 'Sending reason...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        const response = await fetch(lateReasonUrlTemplate.replace('__ID__', String(requestId)), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ overdue_escalation_reason: reason }),
        });

        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            const message = payload.message || payload.errors?.overdue_escalation_reason?.[0] || 'Unable to send reason. Please try again.';
            throw new Error(message);
        }

        overdueReasonQueue = overdueReasonQueue.filter((item) => Number(item.id) !== Number(requestId));
        updateOverdueReasonBanner();

        if (overdueReasonQueue.length > 0) {
            Swal.close();
            promptForcedOverdueReasons();
            return;
        }

        unlockOverdueReasonPage();
        await Swal.fire({
            icon: 'success',
            title: 'All reasons submitted',
            text: 'You can now continue reviewing requests.',
            confirmButtonText: 'Continue',
            allowOutsideClick: false,
            allowEscapeKey: false,
        });
        window.location.reload();
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Could not send reason',
            text: error.message || 'Please try again.',
            confirmButtonText: 'Retry',
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then(() => promptForcedOverdueReasons());
    }
}

function promptForcedOverdueReasons() {
    if (!Array.isArray(overdueReasonQueue) || overdueReasonQueue.length === 0) {
        unlockOverdueReasonPage();
        return;
    }

    lockOverdueReasonPage();
    const first = overdueReasonQueue[0];
    const queuePosition = Math.max(1, overdueReasonTotal - overdueReasonQueue.length + 1);

    openLateReasonPopup(first.id, {
        forced: true,
        meta: first,
        queuePosition,
        queueTotal: overdueReasonTotal,
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (overdueReasonQueue.length > 0) {
        lockOverdueReasonPage();
        promptForcedOverdueReasons();
    }
});

function openRejectPopup(requestId) {
    Swal.fire({
        icon: 'warning',
        title: 'Reject Request',
        input: 'textarea',
        inputLabel: 'Rejection reason',
        inputPlaceholder: 'Type the rejection reason...',
        inputAttributes: { 'aria-label': 'Rejection reason' },
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#dc2626',
        cancelButtonText: 'Cancel',
        preConfirm: (value) => {
            const v = (value || '').trim();
            if (!v) {
                Swal.showValidationMessage('Rejection reason is required.');
                return false;
            }
            if (v.length > 500) {
                Swal.showValidationMessage('Rejection reason must be 500 characters or less.');
                return false;
            }
            return v;
        }
    }).then((result) => {
        if (!result.isConfirmed) return;
        const form = document.getElementById('reject-hidden-form');
        form.action = rejectUrlTemplate.replace('__ID__', String(requestId));
        document.getElementById('reject-hidden-reason').value = result.value;
        form.submit();
    });
}
</script>
@endsection
