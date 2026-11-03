@extends($layout ?? 'layouts.app')

@section('title', 'My Leave Requests')

@section('content')
@include('partials.requests-page-styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

@php
    use App\Support\LeaveProofRules;
    use App\Support\LeaveAttachmentLinks;
@endphp

<style>
    .leave-cards { display: flex; flex-direction: column; gap: 18px; margin-top: 18px; }

    .leave-card {
        position: relative;
        background: #fff;
        border: 1px solid #d9dedc;
        border-radius: 14px;
        padding: 16px 20px 14px 34px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, .12);
        cursor: pointer;
        transition: box-shadow .15s ease, transform .15s ease;
    }
    .leave-card:hover { box-shadow: 0 6px 16px rgba(0, 0, 0, .18); transform: translateY(-1px); }
    .leave-card.overdue-row { border-color: #e0a3a3; }

    .leave-card__num {
        position: absolute; top: 8px; left: 12px;
        font-size: .8rem; font-weight: 800; color: #0d5c63;
    }

    .leave-card__top {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
    }
    .leave-card__title { margin: 0; font-size: 1.25rem; font-weight: 800; color: #4a4f52; }

    .leave-card__badges { display: flex; flex-wrap: wrap; gap: 6px; justify-content: flex-end; }

    .leave-card__meta { margin-top: 8px; display: flex; flex-direction: column; gap: 6px; color: #4a4f52; font-size: .95rem; }
    .leave-card__meta-row { display: flex; align-items: center; gap: 8px; }
    .leave-card__meta-row svg { width: 16px; height: 16px; flex: none; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    .leave-card__actions {
        margin-top: 10px;
        display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; align-items: center;
    }
    .leave-card__actions form { margin: 0; display: inline-flex; }
    .leave-card__actions .request-existing-files { display: inline-flex; flex-wrap: wrap; gap: 8px; }

    .requests-empty-card {
        text-align: center; padding: 28px; color: #6b7280;
        background: #fff; border: 1px dashed #cfd6d4; border-radius: 14px;
    }

    @media (max-width: 576px) {
        .leave-card { padding-left: 30px; }
        .leave-card__actions { justify-content: flex-start; }
    }

  /* ===== My Leave Requests: match prototype ===== */
html body .my-requests-page { font-family:'Poppins',sans-serif; }
html body .my-requests-page h1 { color:#263248; font-weight:700; }
html body .my-requests-page .requests-page-lead { color:#000; }
html body .my-requests-page .requests-page-lead strong { color:#a2b222; }

/* toolbar */
html body .my-requests-page .requests-toolbar {
    background:#d8e3e1 !important; border:0 !important; border-radius:14px !important;
    padding:14px 16px !important; box-shadow:none !important; align-items:center;
}
html body .my-requests-page .requests-btn--primary,
html body .my-requests-page .requests-btn--search,
html body .my-requests-page .requests-btn--secondary {
    background:#a2b222 !important; color:#000 !important; border:0 !important;
    border-radius:8px !important; padding:8px 14px !important; font-weight:700 !important;
    min-height:0 !important; font-size:13px !important;
}
html body .my-requests-page .requests-search-form input[type="text"] {
    background:#fff url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23a2b222' stroke-width='2.2' stroke-linecap='round'><circle cx='11' cy='11' r='7'/><path d='M16 16l5 5'/></svg>") no-repeat 14px center / 20px 20px !important;
    border:0 !important; border-radius:12px !important;
    padding:14px 18px 14px 44px !important; min-width:260px;
}

/* tabs and payment filter */
html body .my-requests-page .requests-filter-bar {
    display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;
    background:transparent !important; border:0 !important; box-shadow:none !important; padding:0 !important;
}
html body .my-requests-page .requests-filters {
    display:inline-flex; gap:20px; background:#d8e3e1 !important; border:0 !important;
    border-radius:14px; padding:12px 20px;
}
html body .my-requests-page .requests-filter {
    background:transparent !important; color:#263248 !important; font-weight:700 !important;
    padding:0 !important; border:0 !important; box-shadow:none !important;
}
html body .my-requests-page .requests-filter.is-active { color:#2379b0 !important; }
html body .my-requests-page .requests-filter-bar select {
    background:#fff !important; border:0 !important; border-radius:12px !important; font-weight:700;
}

/* cards */
html body .my-requests-page .leave-cards { gap:26px; }
html body .my-requests-page .leave-card {
    background:#fff; border:1px solid #ddd; border-radius:14px;
    box-shadow:6px 8px 14px rgba(0,0,0,.22);
    padding:18px 24px 16px 34px;
}
html body .my-requests-page .leave-card__num { color:#2379b0 !important; font-weight:800 !important; }
html body .my-requests-page .leave-card__title {
    color:#555; font-size:1.5rem; text-shadow:1px 2px 3px rgba(0,0,0,.35);
}

/* status pills */
html body .my-requests-page .request-badge {
    border:2px solid #666; border-radius:14px; padding:4px 14px; font-weight:700; font-size:.8rem;
}
html body .my-requests-page .request-badge--approved { background:#9db4ff !important; border-color:#2a6fc0; color:#1f3f8f !important; }
html body .my-requests-page .request-badge--pending { background:#cebaf6 !important; border-color:#a33d86; color:#7a2a7a !important; }
html body .my-requests-page .request-badge--overdue { background:#cebaf6 !important; border-color:#a33d86; color:#fff !important; }
html body .my-requests-page .request-badge--rejected { background:#f9c3c9 !important; border-color:#d5253a; color:#a31b2e !important; }
html body .my-requests-page [class*="request-badge--withdrawn"] { background:#e2e2e2 !important; border-color:#767672; color:#555 !important; }

/* buttons: outlined lime, Withdraw pink */
html body .my-requests-page .leave-card .request-btn {
    background:#fff !important; border:2px solid #c5d931 !important; color:#8d9a1a !important;
    border-radius:999px !important; padding:6px 16px !important; font-size:.85rem !important; font-weight:600 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-shadow: none !important;
    line-height: 1 !important;
}
html body .my-requests-page .leave-card .request-btn:hover { background:#f6fadf !important; }
html body .my-requests-page .leave-card .request-btn--withdraw,
html body .my-requests-page .leave-card .request-btn--delete {
    background:#f69ea6 !important; border:1px solid #f00 !important; color:#e00 !important;
}
html body .my-requests-page .leave-card .request-btn--withdraw:hover,
html body .my-requests-page .leave-card .request-btn--delete:hover { background:#f48a94 !important; }  

/* ===== Fine-tune: buttons, Payment bar, tabs, pills ===== */

/* toolbar buttons: smaller, like the prototype */
html body .my-requests-page .requests-btn--primary,
html body .my-requests-page .requests-btn--search,
html body .my-requests-page .requests-btn--secondary {
    padding:8px 14px !important; font-size:13px !important; line-height:1.2 !important; min-height:0 !important;
}
html body .my-requests-page .requests-search-form input[type="text"] {
    padding-top:11px !important; padding-bottom:11px !important; font-size:13px !important; min-width:250px;
}
html body .my-requests-page .requests-toolbar { padding:12px 16px !important; gap:10px; }

/* tabs: bigger text, more space */
html body .my-requests-page .requests-filters { gap:16px; padding:12px 22px; }
html body .my-requests-page .requests-filter { font-size:15px !important; }

/* Payment bar: same grey-teal, bold label, white pill dropdown */
html body .my-requests-page .requests-filter-bar > *:not(.requests-filters) {
    background:#d8e3e1 !important; border:0 !important; border-radius:14px !important;
    padding:12px 18px !important; display:flex; align-items:center; gap:14px; box-shadow:none !important;
}
html body .my-requests-page .requests-filter-bar label {
    text-transform:none !important; letter-spacing:0 !important;
    font-size:15px !important; font-weight:700 !important; color:#263248 !important; white-space:nowrap;
}
html body .my-requests-page .requests-filter-bar select {
    min-width:170px; padding:10px 16px !important; font-size:15px !important;
}

/* status pills: anything not approved/pending/overdue/rejected becomes grey outlined (Withdrawn) */
html body .my-requests-page .leave-card__badges .request-badge:not(.request-badge--approved):not(.request-badge--pending):not(.request-badge--overdue):not(.request-badge--rejected) {
    background:#e2e2e2 !important; border:2px solid #767672 !important; color:#555 !important;
    border-radius:14px; padding:4px 14px; font-weight:700; font-size:.8rem;
}

/* card buttons: bottom right, same spacing as prototype */
html body .my-requests-page .leave-card__actions { justify-content:flex-end; gap:10px; margin-top:12px; }

/* ===== Fix: Payment Dropdown ===== */
html body .my-requests-page .requests-pay-filter {
    background: #dbe4e6 !important; /* Grey background box */
    border: 0 !important; 
    border-radius: 12px !important;
    padding: 6px 16px !important; 
    display: flex; 
    align-items: center; 
    gap: 12px; 
    box-shadow: none !important;
}
html body .my-requests-page .requests-pay-filter label {
    font-size: 14px !important; 
    font-weight: 500 !important; /* Not bold */
    color: #475569 !important;
    text-transform: none !important; /* Sentence case */
    margin: 0 !important;
}
html body .my-requests-page .requests-pay-filter select {
    background: #fff !important; 
    border: 0 !important; 
    border-radius: 20px !important; 
    font-weight: 600 !important;
    font-size: 14px !important;
    color: #1e293b !important;
    padding: 8px 16px !important;
    min-width: 140px !important;
}    

</style>

<div class="requests-page my-requests-page">
    @if(($layout ?? '') === 'layouts.manager')
        <div class="mgr-page-header">
            <div class="mgr-page-header__main">
                <h1>My Leave Requests</h1>
                <p class="requests-page-lead">
                    Showing <strong>{{ ucfirst($status) }}</strong>
                    @if ($leaveRequests->total() > 0)
                        · {{ $leaveRequests->total() }} {{ Str::plural('request', $leaveRequests->total()) }}
                    @endif
                </p>
            </div>
            @include('partials.manager-scope-switch', [
                'scope' => 'personal',
                'teamUrl' => route('manager.requests.index', ['status' => 'all']),
                'teamLabel' => 'Requests',
            ])
        </div>
    @else
        <h1>My Leave Requests</h1>
        <p class="requests-page-lead">
            Showing <strong>{{ ucfirst($status) }}</strong>
            @if ($leaveRequests->total() > 0)
                · {{ $leaveRequests->total() }} {{ Str::plural('request', $leaveRequests->total()) }}
            @endif
        </p>
    @endif

    @include('partials.requests-toolbar', [
        'formRoute' => $formRoute ?? 'leave.myRequests',
        'filterRoute' => $filterRoute ?? 'leave.myRequests',
        'newRequestRoute' => $newRequestRoute ?? 'apply',
        'status' => $status,
        'payFilter' => $payFilter ?? 'all',
        'search' => $search ?? '',
        'showNewRequest' =>  false,
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

    <div class="leave-cards">
        @forelse($leaveRequests as $req)
            @php
                $fileLinks = LeaveAttachmentLinks::forRequest($req);

                $handledName = trim(($req->handledBy->first_name ?? '') . ' ' . ($req->handledBy->last_name ?? ''));
                $lateReasonName = trim(($req->overdueEscalatedBy->first_name ?? '') . ' ' . ($req->overdueEscalatedBy->last_name ?? ''));
                $rowStatus = strtolower(trim((string) ($req->status ?? '')));
                $canWithdraw = $req->canBeWithdrawnByEmployee();
                $canEdit = $req->canBeEditedByApplicant();
                $canDelete = $req->canBeDeletedByEmployee();
                $showAttachmentControls = LeaveProofRules::employeeCanManageAttachments($req);
                $canSubmitAttachment = LeaveProofRules::employeeCanSubmitAttachmentNow($req);
                $attachmentActionLabel = LeaveProofRules::employeeAttachmentActionLabel($req);
                $proofWindowOpens = LeaveProofRules::proofWindowOpensAt($req);
                $proofDeadline = LeaveProofRules::proofDeadlineAt($req);
                $proofStatusLabel = LeaveProofRules::employeeProofStatusLabel($req);
                $doctorsLetterMissing = LeaveProofRules::isProofPastDeadline($req);
                $proofUploadPending = LeaveProofRules::isProofUploadWindowPending($req);
                $certificateNote = LeaveProofRules::managerCertificateNote($req);

                $details = [
                    'id' => $req->id,
                    'type' => (string) ($req->leave_type ?? ''),
                    'start' => $req->start_date ? \Carbon\Carbon::parse($req->start_date)->format('d M Y') : '',
                    'end' => $req->end_date ? \Carbon\Carbon::parse($req->end_date)->format('d M Y') : '',
                    'days' => \App\Support\LeaveDaysFormatter::format($req->days ?? ''),
                    'status' => $rowStatus,
                    'isOverdue' => $req->isOverdue(),
                    'comment' => (string) ($req->important_comment ?? ''),
                    'files' => $fileLinks,
                    'proofDeferred' => LeaveProofRules::allowsDeferredProof($req->leave_type) && LeaveProofRules::requiresProof($req->leave_type),
                    'proofAttached' => LeaveProofRules::hasProofAttached($req),
                    'proofOpensAt' => $proofWindowOpens ? $proofWindowOpens->format('d M Y') : '',
                    'proofDeadline' => $proofDeadline ? $proofDeadline->format('d M Y') : '',
                    'proofStatusLabel' => (string) ($proofStatusLabel ?? ''),
                    'certificateNote' => (string) ($certificateNote ?? ''),
                    'canUploadProofNow' => $canSubmitAttachment,
                    'doctorsLetterMissing' => $doctorsLetterMissing,
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
                ];
                $details = array_merge($details, $req->popupMeta());
            @endphp

            <div class="leave-card row-click {{ $req->isOverdue() ? 'overdue-row' : '' }}" data-details='@json($details)'>
                <span class="leave-card__num">{{ $leaveRequests->firstItem() + $loop->index }}</span>

                <div class="leave-card__top">
                    <h3 class="leave-card__title">{{ $req->leave_type }}</h3>

                    <div class="leave-card__badges request-status-cell">
                        @if ($req->isOverdue())
                            <span class="request-badge request-badge--overdue">Overdue</span>
                        @else
                            <span class="request-badge request-badge--{{ \App\Support\LeaveStatusLabels::badgeClass($rowStatus, $req->isOverdue()) }}">{{ \App\Support\LeaveStatusLabels::display($rowStatus, $req->isOverdue()) }}</span>
                        @endif
                        @if ($proofStatusLabel)
                            <span class="request-badge {{ $doctorsLetterMissing ? 'request-badge--letter-missing' : 'request-badge--letter-required' }}">{{ $proofStatusLabel }}</span>
                        @endif
                    </div>
                </div>

                <div class="leave-card__meta">
                    <div class="leave-card__meta-row">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        <span>Submitted {{ $req->created_at ? \Carbon\Carbon::parse($req->created_at)->format('j M Y') : '—' }}</span>
                    </div>
                    <div class="leave-card__meta-row">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        <span>
                            {{ $req->start_date ? \Carbon\Carbon::parse($req->start_date)->format('d M') : '—' }}
                            -
                            {{ $req->end_date ? \Carbon\Carbon::parse($req->end_date)->format('d M') : '—' }}
                        </span>
                    </div>
                </div>

                <div class="leave-card__actions request-actions request-actions-cell" onclick="event.stopPropagation();">
                    @if ($canEdit)
                        <a href="{{ route(\App\Support\LeaveApplicant::editRoute(auth()->user()), $req->id) }}" class="request-btn request-btn--edit">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/></svg>
                            Edit
                        </a>
                    @endif

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
                                        data-label="{{ $fileLink['label'] }}"
                                        data-filename="{{ $fileLink['name'] }}"
                                        data-previewable="1"
                                    >
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        View {{ $fileLink['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if ($canSubmitAttachment || $proofUploadPending)
                            <form class="proof-upload-form" method="POST" action="{{ route('leave.uploadProof', $req) }}" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="file_uploads[]" class="proof-upload-input" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple {{ $canSubmitAttachment ? 'required' : '' }}>
                                <button
                                    type="button"
                                    class="request-btn request-btn--approve proof-upload-btn"
                                    data-can-upload="{{ $canSubmitAttachment ? '1' : '0' }}"
                                    data-is-replace="{{ count($fileLinks) > 0 ? '1' : '0' }}"
                                    data-opens-at="{{ $proofWindowOpens ? $proofWindowOpens->format('d M Y') : '' }}"
                                    data-deadline="{{ $proofDeadline ? $proofDeadline->format('d M Y') : '' }}"
                                    data-action-label="{{ $attachmentActionLabel }}"
                                >
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                    {{ $attachmentActionLabel }}
                                </button>
                            </form>
                        @endif
                    @endif

                    @if ($canWithdraw)
                        <form class="withdraw-form" method="POST" action="{{ route('leave.withdraw', $req->id) }}">
                            @csrf
                            @method('PATCH')
                            <button
                                type="button"
                                class="request-btn request-btn--withdraw withdraw-request-btn"
                                data-approved="{{ $rowStatus === 'approved' ? '1' : '0' }}"
                            >
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                Withdraw
                            </button>
                        </form>
                    @endif

                    @if ($canDelete)
                        <form class="delete-request-form" method="POST" action="{{ route('leave.destroy', $req) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="request-btn request-btn--delete delete-request-btn" data-request-id="{{ $req->id }}">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                Delete
                            </button>
                        </form>
                    @endif
                </div>            
            </div>
        @empty
            <div class="requests-empty-card requests-empty">No leave requests found for this filter.</div>
        @endforelse
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