@extends($layout ?? 'layouts.app')

@section('title', 'My Leave Requests')

@section('content')
@include('partials.requests-page-styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

@php
    use App\Support\LeaveProofRules;
    use App\Support\LeaveAttachmentLinks;

    $balanceRoute = \App\Support\LeaveApplicant::balanceRoute();

    // "01 Sept 2026" style dates, as in the design
    $fmtLong = function ($date) {
        if (!$date) return '—';
        return str_replace(' Sep ', ' Sept ', \Carbon\Carbon::parse($date)->format('d M Y'));
    };
    $fmtShort = fn ($date) => $date ? \Carbon\Carbon::parse($date)->format('d M') : '—';
@endphp

<style>
    /* ================= Page ================= */
    html body .my-requests-page {
        font-family: 'Poppins', 'Segoe UI', sans-serif;
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 16px 30px;
        box-sizing: border-box;
    }
    html body .my-requests-page h1 {
        font-family: 'Century Gothic', 'Poppins', sans-serif;
        font-size: 36px;
        font-weight: 700;
        color: #263248;
        margin: 26px 0 8px 6px;
    }
    html body .my-requests-page .requests-page-lead {
        font-size: 15px;
        color: #000;
        margin: 0 0 20px;
    }
    html body .my-requests-page .requests-page-lead strong { color: #a2b222; font-weight: 700; }
    html body .my-requests-page .requests-page-lead .lead-dot { margin: 0 4px 0 8px; }

    /* ================= Toolbar: New Request · Search · Clear ================= */
    html body .my-requests-page .requests-toolbar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        background: #d8e3e1 !important;
        border: 0 !important;
        border-radius: 12px !important;
        padding: 14px 22px !important;
        box-shadow: none !important;
        margin-bottom: 10px;
    }
    html body .my-requests-page .requests-btn--primary,
    html body .my-requests-page .requests-btn--search,
    html body .my-requests-page .requests-btn--secondary {
        background: #d4e21a !important;
        color: #111 !important;
        border: 0 !important;
        border-radius: 10px !important;
        padding: 12px 14px !important;
        font-family: inherit;
        font-size: 13px !important;
        font-weight: 700 !important;
        line-height: 1.1 !important;
        min-height: 0 !important;
        box-shadow: none !important;
        text-transform: none !important;
        cursor: pointer;
    }
    html body .my-requests-page .requests-btn--search,
    html body .my-requests-page .requests-btn--secondary { padding: 12px 32px !important; }
    html body .my-requests-page .requests-btn--primary:hover,
    html body .my-requests-page .requests-btn--search:hover,
    html body .my-requests-page .requests-btn--secondary:hover { background: #c3d010 !important; }

    html body .my-requests-page .requests-search-form input[type="text"] {
        background: #fff url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23a2b222' stroke-width='2.4' stroke-linecap='round'><circle cx='11' cy='11' r='6.5'/><path d='M16 16l5 5'/></svg>") no-repeat 16px center / 20px 20px !important;
        border: 0 !important;
        border-radius: 10px !important;
        padding: 12px 16px 12px 46px !important;
        min-width: 250px;
        font-family: inherit;
        font-size: 12px !important;
        color: #333;
        box-shadow: none !important;
    }
    html body .my-requests-page .requests-search-form input[type="text"]::placeholder { color: #777; }

    /* ================= Status tabs + Payment filter ================= */
    html body .my-requests-page .requests-filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin-bottom: 30px;
    }
    html body .my-requests-page .requests-filters {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: #d8e3e1 !important;
        border: 0 !important;
        border-radius: 12px !important;
        padding: 14px 20px !important;
    }
    html body .my-requests-page .requests-filter {
        background: transparent !important;
        color: #263248 !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        padding: 0 !important;
        border: 0 !important;
        box-shadow: none !important;
        text-decoration: none;
    }
    html body .my-requests-page .requests-filter.is-active { color: #2379b0 !important; }

    /* Payment box */
    html body .my-requests-page .requests-filter-bar > *:not(.requests-filters) {
        display: flex;
        align-items: center;
        gap: 16px;
        background: #d8e3e1 !important;
        border: 0 !important;
        border-radius: 12px !important;
        
        box-shadow: none !important;
    }
    html body .my-requests-page .requests-filter-bar label {
        margin: 0;
        text-transform: none !important;
        letter-spacing: 0 !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        color: #263248 !important;
        white-space: nowrap;
    }
    html body .my-requests-page .requests-filter-bar select {
        appearance: none;
        -webkit-appearance: none;
        min-width: 165px;
        padding: 9px 40px 9px 18px !important;
        background: #fff url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23222' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'><path d='M6 9l6 6 6-6'/></svg>") no-repeat right 14px center / 18px 18px !important;
        border: 0 !important;
        border-radius: 10px !important;
        font-family: inherit;
        font-size: 14px !important;
        font-weight: 700 !important;
        color: #111;
        cursor: pointer;
    }

    /* ================= Request cards ================= */
    .leave-cards { display: flex; flex-direction: column; gap: 28px; }

    html body .my-requests-page .leave-card {
        position: relative;
        background: #fff;
        border: 1px solid #dcdcdc;
        border-radius: 14px;
        padding: 16px 16px 14px 34px;
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.25);
        cursor: pointer;
        transition: box-shadow .15s ease, transform .15s ease;
    }
    html body .my-requests-page .leave-card:hover { box-shadow: 0 8px 18px rgba(0, 0, 0, 0.3); transform: translateY(-1px); }
    html body .my-requests-page .leave-card.overdue-row { border-color: #e0a3a3; }

    html body .my-requests-page .leave-card__num {
        position: absolute;
        top: 6px;
        left: 10px;
        font-size: 14px;
        font-weight: 800;
        font-style: normal;                              /* straight, not slanted */
        color: #2379b0 !important;
        text-shadow: 0 2px 3px rgba(0, 0, 0, 0.35);      /* soft shadow under the number */
    }

    .leave-card__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
    html body .my-requests-page .leave-card__title {
        margin: 4px 0 0;
        font-size: 22px;
        font-weight: 700;
        color: #555;
        text-shadow: none;
    }
    .leave-card__badges { display: flex; flex-wrap: wrap; gap: 6px; justify-content: flex-end; }

    .leave-card__meta {
        margin-top: 10px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        color: #444;
        font-size: 15px;
    }
    .leave-card__meta-row { display: flex; align-items: center; gap: 8px; }
    .leave-card__meta-row svg {
        width: 18px; height: 18px; flex: none;
        stroke: #333; fill: none; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round;
    }

    /* ================= Status pills ================= */
    html body .my-requests-page .request-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: 2px solid #767672;
        border-radius: 12px;
        padding: 3px 12px;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.3;
        background: #e2e2e2;
        color: #555;
    }
    html body .my-requests-page .request-badge svg {
        width: 14px; height: 14px; flex: none;
        fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
    }
    html body .my-requests-page .request-badge--pending  { background: #cebaf6 !important; border-color: #a33d86 !important; color: #a33d86 !important; }
    html body .my-requests-page .request-badge--approved { background: #9db4ff !important; border-color: #2a6fc0 !important; color: #1f3f8f !important; }
    html body .my-requests-page .request-badge--overdue  { background: #cebaf6 !important; border-color: #a33d86 !important; color: #7a1f5f !important; }
    html body .my-requests-page .request-badge--rejected { background: #f9c3c9 !important; border-color: #d5253a !important; color: #a31b2e !important; }
    html body .my-requests-page .request-badge--letter-required { background: #fff4cc !important; border-color: #c99a00 !important; color: #7a5c00 !important; }
    html body .my-requests-page .request-badge--letter-missing  { background: #f9c3c9 !important; border-color: #d5253a !important; color: #a31b2e !important; }

    /* ================= Card buttons ================= */
    html body .my-requests-page .leave-card__actions {
        margin-top: 4px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: flex-end;
        align-items: center;
    }
    .leave-card__actions form { margin: 0; display: inline-flex; }
    .leave-card__actions .request-existing-files { display: inline-flex; flex-wrap: wrap; gap: 6px; }
    .leave-card__actions .proof-upload-input { display: none; }

    html body .my-requests-page .leave-card .request-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #fff !important;
        border: 1.5px solid #c5d931 !important;
        color: #8d9a1a !important;
        border-radius: 12px !important;
        padding: 6px 12px !important;
        font-family: inherit;
        font-size: 13px !important;
        font-weight: 500 !important;
        line-height: 1.2;
        text-decoration: none;
        text-transform: none !important;
        box-shadow: none !important;
        cursor: pointer;
    }
    html body .my-requests-page .leave-card .request-btn svg {
        width: 14px; height: 14px; flex: none;
        fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
    }
    html body .my-requests-page .leave-card .request-btn:hover { background: #f6fadf !important; }
    html body .my-requests-page .leave-card .proof-upload-btn { min-width: 188px; }
    html body .my-requests-page .leave-card .request-btn--check { padding: 8px 26px !important; }

    html body .my-requests-page .leave-card .request-btn--withdraw,
    html body .my-requests-page .leave-card .request-btn--delete {
        background: #f69ea6 !important;
        border: 1px solid #ff0000 !important;
        color: #e00000 !important;
    }
    html body .my-requests-page .leave-card .request-btn--withdraw:hover,
    html body .my-requests-page .leave-card .request-btn--delete:hover { background: #f48a94 !important; }

    /* ================= Empty state + footnote ================= */
    .requests-empty-card {
        text-align: center; padding: 28px; color: #6b7280;
        background: #fff; border: 1px dashed #cfd6d4; border-radius: 14px;
    }
    .requests-footnote {
        max-width: 820px;
        margin: 22px auto 0;
        text-align: center;
        font-size: 11px;
        line-height: 1.45;
        color: #6f6f6f;
    }
    

    /* ================= All icons black ================= */
    html body .my-requests-page .leave-card__meta-row svg,
    html body .my-requests-page .request-badge svg,
    html body .my-requests-page .leave-card .request-btn svg {
        width: 18px;
        height: 18px;
        stroke: currentColor !important;
    }

    @media (max-width: 768px) {
        html body .my-requests-page { padding: 0 0 24px; max-width: 100%; }
        html body .my-requests-page h1 { font-size: 28px; margin-left: 0; }
        html body .my-requests-page .requests-toolbar,
        html body .my-requests-page .requests-filter-bar {
            flex-direction: column !important;
            align-items: stretch !important;
            padding: 12px !important;
        }
        html body .my-requests-page .requests-search-form {
            position: static !important;
            width: 100% !important;
            max-width: 100% !important;
            flex-wrap: wrap !important;
        }
        html body .my-requests-page .requests-search-form input[type="text"] {
            min-width: 0 !important;
            width: 100% !important;
            flex: 1 1 100% !important;
        }
        html body .my-requests-page .requests-filters,
        html body .my-requests-page .requests-pay-filter {
            width: 100% !important;
            box-sizing: border-box !important;
            flex-wrap: wrap !important;
        }
        html body .my-requests-page .requests-filter-bar select { min-width: 0 !important; width: 100% !important; }
        html body .my-requests-page .leave-card { padding: 28px 12px 14px; }
        html body .my-requests-page .leave-card__top { flex-direction: column; align-items: flex-start; }
        html body .my-requests-page .leave-card__badges,
        html body .my-requests-page .leave-card__actions { justify-content: flex-start; }
        html body .my-requests-page .leave-card .proof-upload-btn { min-width: 0 !important; }
        html body .my-requests-page .leave-card__title { font-size: 18px; }
    }
</style>

<div class="requests-page my-requests-page">
    @if(($layout ?? '') === 'layouts.manager')
        <div class="mgr-page-header">
            <div class="mgr-page-header__main">
                <h1>My Leave Requests</h1>
            </div>
            @include('partials.manager-scope-switch', [
                'scope' => 'personal',
                'teamUrl' => route('manager.requests.index', ['status' => 'all']),
                'teamLabel' => 'Requests',
            ])
        </div>
    @else
        <h1>My Leave Requests</h1>
    @endif

    @include('partials.requests-toolbar', [
        'formRoute' => $formRoute ?? 'leave.myRequests',
        'filterRoute' => $filterRoute ?? 'leave.myRequests',
        'newRequestRoute' => $newRequestRoute ?? 'apply',
        'status' => $status,
        'payFilter' => $payFilter ?? 'all',
        'search' => $search ?? '',
        'showNewRequest' => true,
        'searchPlaceholder' => 'Search type, status, dates…',
        'statusFilters' => [
            'all' => 'All',
            'pending' => 'Pending',
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
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

                $badgeKey = $req->isOverdue()
                    ? 'overdue'
                    : \App\Support\LeaveStatusLabels::badgeClass($rowStatus, $req->isOverdue());
                $badgeText = $req->isOverdue()
                    ? 'Overdue'
                    : \App\Support\LeaveStatusLabels::display($rowStatus, $req->isOverdue());

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

                $typeName = trim((string) $req->leave_type);
                $displayType = preg_match('/\bleave\s*$/i', $typeName) ? $typeName : $typeName.' Leave';
            @endphp

            <div class="leave-card row-click {{ $req->isOverdue() ? 'overdue-row' : '' }}" data-details='@json($details)'>
                <span class="leave-card__num">{{ $leaveRequests->firstItem() + $loop->index }}</span>

                <div class="leave-card__top">
                    <h3 class="leave-card__title">{{ $displayType }}</h3>

                    <div class="leave-card__badges request-status-cell">
                        <span class="request-badge request-badge--{{ $badgeKey }}">
                            @include('partials.request-status-icon', ['icon' => $badgeKey])
                            {{ $badgeText }}
                        </span>
                        @if ($proofStatusLabel)
                            <span class="request-badge {{ $doctorsLetterMissing ? 'request-badge--letter-missing' : 'request-badge--letter-required' }}">{{ $proofStatusLabel }}</span>
                        @endif
                    </div>
                </div>

                <div class="leave-card__meta">
                    <div class="leave-card__meta-row">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15.5" rx="1.5"/><path d="M8 3v4M16 3v4M3.5 9.5h17M7.5 13h1M11.5 13h1M15.5 13h1M7.5 16.5h1M11.5 16.5h1M15.5 16.5h1"/></svg>
                        <span>Submitted {{ $fmtLong($req->created_at) }}</span>
                    </div>
                    <div class="leave-card__meta-row">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
                        <span>{{ $fmtShort($req->start_date) }} - {{ $fmtShort($req->end_date) }}</span>
                    </div>
                </div>

                <div class="leave-card__actions request-actions request-actions-cell" onclick="event.stopPropagation();">
                    @if ($canEdit)
                        <a href="{{ route(\App\Support\LeaveApplicant::editRoute(auth()->user()), $req->id) }}" class="request-btn request-btn--edit">
                            @include('partials.request-action-icon', ['name' => 'edit'])
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
                                        @include('partials.request-action-icon', ['name' => 'view'])
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
                                    @include('partials.request-action-icon', ['name' => 'upload'])
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
                                @include('partials.request-action-icon', ['name' => 'trash'])
                                Withdraw
                            </button>
                        </form>
                    @endif

                    @if ($canDelete)
                        <form class="delete-request-form" method="POST" action="{{ route('leave.destroy', $req) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="request-btn request-btn--delete delete-request-btn" data-request-id="{{ $req->id }}">
                                @include('partials.request-action-icon', ['name' => 'trash'])
                                Delete
                            </button>
                        </form>
                    @endif

                    @if ($rowStatus === 'approved')
                        <a href="{{ route($balanceRoute, ['type' => $req->leave_type]) }}" class="request-btn request-btn--check">Check remaining days</a>
                    @endif
                </div>
            </div>
        @empty
            <div class="requests-empty-card requests-empty">No leave requests found for this filter.</div>
        @endforelse
    </div>

    <p class="requests-footnote">
        Click any row to view full request details. While a request is still pending, use Edit to correct details or replace documents.
        Approved leave may be withdrawn until its final leave day; the balance is then restored. For approved sick leave, upload your
        doctor's letter using Upload doctor's letter in the Actions column by the day after your leave ends at the latest.
    </p>

    @if (method_exists($leaveRequests, 'links') && $leaveRequests->hasPages())
        <div class="requests-pagination">{{ $leaveRequests->withQueryString()->links() }}</div>
    @endif
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
