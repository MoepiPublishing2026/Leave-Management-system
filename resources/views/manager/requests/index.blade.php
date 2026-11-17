@extends('layouts.manager')

@section('title', 'Team Leave Requests - ' . ucfirst($status))

@section('content')
@include('partials.requests-page-styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
html body .team-requests-page { font-family:'Poppins',sans-serif; }
html body .team-requests-page h1 { color:#263248; font-weight:700; }

/* Title stays on the left. The switch ends on the same right edge as the list. */
html body main .team-requests-page > .mgr-page-header {
    width: 100% !important;
    max-width: none !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    padding-right: max(0px, calc((100% - 1040px) / 2)) !important;
    position: relative !important;
}
html body main .team-requests-page .mgr-scope-switch {
    position: static !important;
    top: auto !important;
    right: auto !important;
    margin: 0 0 8px auto !important;
}

/* Mint toolbar: + New Request, search, Search, Clear */
/* Match My Leave Requests search toolbar sizing */
html body main .team-requests-page .requests-toolbar {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 16px !important;
    background: #d8e3e1 !important;
    border: 0 !important;
    border-radius: 12px !important;
    padding: 14px 22px !important;
    box-shadow: none !important;
    min-height: 0 !important;
    margin-bottom: 10px !important;
}
html body main .team-requests-page .requests-toolbar-left { flex: 0 0 auto !important; }
html body main .team-requests-page .requests-toolbar-right {
    flex: 0 1 auto !important;
    margin-left: auto !important;
    min-width: 0 !important;
}
html body main .team-requests-page .requests-search-form {
    position: static !important;
    top: auto !important;
    right: auto !important;
    left: auto !important;
    width: auto !important;
    max-width: none !important;
    margin: 0 !important;
    display: flex !important;
    align-items: center !important;
    gap: 16px !important;
}
html body main .team-requests-page .requests-search-form input[type="text"] {
    flex: 0 1 auto !important;
    width: auto !important;
    min-width: 250px !important;
    height: auto !important;
    box-sizing: border-box !important;
    padding: 12px 16px 12px 46px !important;
    border: 0 !important;
    border-radius: 10px !important;
    background: #fff url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23a2b222' stroke-width='2.4' stroke-linecap='round'><circle cx='11' cy='11' r='6.5'/><path d='M16 16l5 5'/></svg>") no-repeat 16px center / 20px 20px !important;
    font-size: 12px !important;
    line-height: 1.1 !important;
    color: #333 !important;
    box-shadow: none !important;
}
html body main .team-requests-page .requests-search-form input[type="text"]::placeholder {
    color: #777 !important;
}
html body main .team-requests-page .requests-btn--primary,
html body main .team-requests-page .requests-btn--search,
html body main .team-requests-page .requests-btn--secondary {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: auto !important;
    min-height: 0 !important;
    padding: 12px 14px !important;
    background: #d4e21a !important;
    color: #111 !important;
    border: 0 !important;
    border-radius: 10px !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    line-height: 1.1 !important;
    text-decoration: none !important;
    box-shadow: none !important;
    white-space: nowrap !important;
    text-transform: none !important;
    cursor: pointer !important;
}
html body main .team-requests-page .requests-btn--search,
html body main .team-requests-page .requests-btn--secondary {
    padding: 12px 32px !important;
}
html body main .team-requests-page .requests-btn--primary:hover,
html body main .team-requests-page .requests-btn--search:hover,
html body main .team-requests-page .requests-btn--secondary:hover {
    background: #c3d010 !important;
    color: #111 !important;
}

/* tabs and payment filter */
html body .team-requests-page .requests-filter-bar {
    display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;
    background:transparent !important; border:0 !important; box-shadow:none !important; padding:0 !important;
}
html body .team-requests-page .requests-filters {
    display:inline-flex; gap:20px; background:#d8e3e1 !important; border:0 !important;
    border-radius:14px; padding:12px 20px;
}
html body .team-requests-page .requests-filter {
    background:transparent !important; color:#263248 !important; font-weight:700 !important;
    padding:0 !important; border:0 !important; box-shadow:none !important;
}
html body .team-requests-page .requests-filter.is-active { color:#2379b0 !important; }
html body .team-requests-page .requests-filter-bar select { background:#fff !important; border:0 !important; border-radius:12px !important; font-weight:700; }

/* each row becomes a card */
html body .team-requests-page .requests-table-wrap { background:transparent !important; border:0 !important; box-shadow:none !important; overflow:visible !important; }
html body .team-requests-page .requests-table,
html body .team-requests-page .requests-table tbody { display:block; width:100%; min-width:0 !important; }
html body .team-requests-page .requests-table thead { display:none; }
html body .team-requests-page .requests-table tr.row-click {
    position:relative; display:grid; grid-template-columns:auto auto 1fr; column-gap:0; row-gap:4px;
    background:#fff !important; border:1px solid #ddd; border-radius:14px; padding:18px 24px 18px 30px;
    margin:0 0 26px; box-shadow:6px 8px 14px rgba(0,0,0,.22); cursor:pointer;
}
html body .team-requests-page .requests-table tr.row-click td {
    display:block; border:0 !important; background:transparent !important; padding:2px 0 !important; text-align:left !important; color:#444;
}
html body .team-requests-page .requests-table td:nth-child(1) { position:absolute; top:6px; left:12px; padding:0 !important; font-size:.8rem; font-weight:800 !important; color:#2379b0 !important; }
html body .team-requests-page .requests-table td:nth-child(2) { grid-column:1 / 3; grid-row:1; font-size:1.5rem; font-weight:700; color:#555; }
html body .team-requests-page .requests-table td:nth-child(7) { grid-column:3; grid-row:1; justify-self:end; }
html body .team-requests-page .requests-table td:nth-child(3) { grid-column:1 / -1; grid-row:2; font-weight:700; }
html body .team-requests-page .requests-table td:nth-child(3)::before { content:'Requested by:\00a0'; font-weight:400; }
html body .team-requests-page .requests-table td:nth-child(6) { grid-column:1 / -1; grid-row:3; }
html body .team-requests-page .requests-table td:nth-child(6)::before { content:'Submitted\00a0'; }
html body .team-requests-page .requests-table td:nth-child(4) { grid-column:1; grid-row:4; }
html body .team-requests-page .requests-table td:nth-child(4)::after { content:'\00a0–\00a0'; }
html body .team-requests-page .requests-table td:nth-child(5) { grid-column:2; grid-row:4; }
html body .team-requests-page .requests-table td:nth-child(8) { grid-column:3; grid-row:4; justify-self:end; }
html body .team-requests-page .requests-table tr:not(.row-click),
html body .team-requests-page .requests-table tr:not(.row-click) td { display:block; }

/* action buttons */
html body .team-requests-page .request-actions,
html body .team-requests-page .request-actions-inline { display:flex; flex-wrap:wrap; gap:14px; align-items:center; justify-content:flex-end; }
html body .team-requests-page .request-btn--approve,
html body .team-requests-page .request-actions-submitted {
    background:#dfe98c !important; color:#3b7a1e !important; border:2px solid #d4e21a !important;
    border-radius:14px !important; padding:10px 22px !important; font-weight:700 !important;
}
html body .team-requests-page .request-btn--reject {
    background:#e41e3f !important; color:#fff !important; border:2px solid #e41e3f !important;
    border-radius:14px !important; padding:10px 22px !important; font-weight:700 !important;
}
html body .team-requests-page .request-link {
    color:#2379b0 !important; font-weight:700; text-decoration:none !important;
    border-bottom:3px solid #8fa4f5; padding-bottom:2px;
}
html body .team-requests-page .requests-hint { color:#777 !important; font-size:.75rem; }
/* card title shadow */
html body .team-requests-page .requests-table td:nth-child(2) { text-shadow: none; }

/* icons */
html body .team-requests-page .requests-table td:nth-child(3)::before {
    content:'Requested by:\00a0'; display:inline-block; padding-left:28px; margin-right:4px; font-weight:400;
    background:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='1.8' stroke-linecap='round'><circle cx='12' cy='8' r='4'/><path d='M4 21c0-4 4-6 8-6s8 2 8 6'/></svg>") no-repeat left center / 20px 20px;
}
html body .team-requests-page .requests-table td:nth-child(6)::before {
    content:'Submitted\00a0'; display:inline-block; padding-left:28px; margin-right:4px;
    background:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='1.8' stroke-linecap='round'><rect x='3' y='5' width='18' height='16' rx='2'/><path d='M3 10h18M8 3v4M16 3v4'/></svg>") no-repeat left center / 20px 20px;
}
html body .team-requests-page .requests-table td:nth-child(4)::before {
    content:''; display:inline-block; width:20px; height:20px; margin-right:8px; vertical-align:-4px;
    background:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='1.8' stroke-linecap='round'><circle cx='12' cy='12' r='9'/><path d='M12 7v5l3 2'/></svg>") no-repeat center / 20px 20px;
}
html body .team-requests-page .requests-table td:nth-child(4)::after { content:'\00a0-\00a0'; }

/* status pills */
html body .team-requests-page .request-badge {
    border:2px solid #666; border-radius:14px; padding:4px 14px; font-weight:700; font-size:.8rem;
}
html body .team-requests-page .request-badge--approved { background:#9db4ff !important; border-color:#2a6fc0; color:#1f3f8f !important; }
html body .team-requests-page .request-badge--pending { background:#cebaf6 !important; border-color:#a33d86; color:#7a2a7a !important; }
html body .team-requests-page .request-badge--overdue { background:#cebaf6 !important; border-color:#a33d86; color:#fff !important; }
html body .team-requests-page .request-badge--rejected { background:#f9c3c9 !important; border-color:#d5253a; color:#a31b2e !important; }

html body .team-requests-page .requests-table td.requests-empty {
    position:static !important; display:block !important; padding:26px 0 !important;
    font-size:1rem !important; font-weight:700 !important; font-style:italic !important;
    color:#263248 !important; text-align:left !important;
}
html body .team-requests-page .requests-filter-bar label { white-space:nowrap !important; }
/* remove green strip + teal tint */
html body .team-requests-page .requests-table-wrap,
html body .team-requests-page .requests-table,
html body .team-requests-page .requests-table tbody {
    border: 0 !important;
    border-image: none !important;
    background: transparent !important;
    background-image: none !important;
    box-shadow: none !important;
}
html body .team-requests-page .requests-table-wrap::before,
html body .team-requests-page .requests-table-wrap::after,
html body .team-requests-page .requests-table::before,
html body .team-requests-page .requests-table::after,
html body .team-requests-page .requests-table tbody::before,
html body .team-requests-page .requests-table tbody::after {
    content: none !important;
    display: none !important;
}
html body .team-requests-page .requests-table tr:not(.row-click),
html body .team-requests-page .requests-table td.requests-empty {
    background: transparent !important;
    border: 0 !important;
}

</style>

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
        body.overdue-reason-locked header.navbar,
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
        /* ===== Prototype Match: Team Requests Toolbar & Filters ===== */
    
    /* Toolbar Search area */
    html body .requests-page .requests-btn {
        background: #d4e21a !important;
        color: #0a1347 !important;
        border: 1px solid #c3d010 !important;
        border-radius: 6px !important;
        padding: 0 16px !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        height: 40px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    html body .requests-page .requests-btn:hover {
        background: #c3d010 !important;
    }

    /* Filter Tabs & Payment */
    html body .requests-page .requests-filter-bar {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        background: transparent !important;
        border: 0 !important;
        padding: 0 !important;
        margin-bottom: 24px !important;
        flex-wrap: wrap;
        gap: 12px;
    }
    html body .requests-page .requests-filters {
        background: transparent !important;
        border: 0 !important;
        padding: 0 !important;
        display: inline-flex !important;
        gap: 20px !important;
    }
    html body .requests-page .requests-filter {
        background: transparent !important;
        color: #263248 !important;
        font-weight: 700 !important;
        padding: 0 !important;
        border: 0 !important;
        font-size: 14px !important;
    }
    html body .requests-page .requests-filter.is-active {
        color: #2379b0 !important;
    }
    html body .requests-page .requests-pay-filter {
        background: #dbe4e6 !important;
        border: 0 !important;
        border-radius: 12px !important;
        padding: 6px 16px !important;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    html body .requests-page .requests-pay-filter label {
        font-size: 13px !important;
        font-weight: 500 !important;
        color: #475569 !important;
        text-transform: none !important;
        margin: 0 !important;
    }
    html body .requests-page .requests-pay-filter select {
        background: #fff !important;
        border: 0 !important;
        border-radius: 20px !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        color: #1e293b !important;
        padding: 8px 16px !important;
        min-width: 140px !important;
    }

        /* ===== Fix: Payment Dropdown ===== */
    html body .team-requests-page .requests-pay-filter {
        background: #dbe4e6 !important; 
        border: 0 !important; 
        border-radius: 12px !important;
        padding: 6px 16px !important; 
        display: flex; 
        align-items: center; 
        gap: 12px; 
        box-shadow: none !important;
    }
    html body .team-requests-page .requests-pay-filter label {
        font-size: 14px !important; 
        font-weight: 500 !important; 
        color: #475569 !important;
        text-transform: none !important; 
        margin: 0 !important;
    }
    html body .team-requests-page .requests-pay-filter select {
        background: #fff !important; 
        border: 0 !important; 
        border-radius: 20px !important; 
        font-weight: 600 !important;
        font-size: 14px !important;
        color: #1e293b !important;
        padding: 8px 16px !important;
        min-width: 140px !important;
    }

        /* ===== Force Prototype Match: Team Requests Filters & Tabs ===== */
    
    /* 1. Restore the grey background behind the tabs */
    html body .team-requests-page .requests-filters {
        background: #dbe4e6 !important; 
        border-radius: 12px !important;
        padding: 10px 24px !important;
        display: inline-flex !important;
        gap: 24px !important;
        border: 0 !important;
    }

    /* 2. Make the tab text look like the prototype */
    html body .team-requests-page .requests-filter {
        color: #263248 !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        background: transparent !important;
        padding: 0 !important;
        border: 0 !important;
    }
    html body .team-requests-page .requests-filter.is-active {
        color: #2379b0 !important;
    }

    /* 3. Style the Payment Dropdown container */
    html body .team-requests-page .requests-pay-filter {
        background: #dbe4e6 !important; 
        border-radius: 12px !important;
        padding: 6px 16px !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        border: 0 !important;
    }
    html body .team-requests-page .requests-pay-filter label {
        font-size: 14px !important;
        font-weight: 500 !important;
        color: #475569 !important;
        text-transform: none !important;
        margin: 0 !important;
    }
    html body .team-requests-page .requests-pay-filter select {
        background: #fff !important;
        border: 0 !important;
        border-radius: 20px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        color: #1e293b !important;
        padding: 8px 16px !important;
        min-width: 140px !important;
    }

    /* 4. Action Buttons - Exact Match */
    /* Submitted Button */
    html body .team-requests-page .request-actions-submitted {
        background: #dfe98c !important; 
        color: #3b7a1e !important; 
        border: 2px solid #d4e21a !important;
        border-radius: 20px !important; 
        padding: 8px 20px !important; 
        font-weight: 700 !important;
        font-size: 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }
    
    /* Recommend Approve Button */
    html body .team-requests-page .request-btn--approve {
        background: #dfe98c !important; 
        color: #3b7a1e !important; 
        border: 2px solid #d4e21a !important;
        border-radius: 20px !important; 
        padding: 8px 20px !important; 
        font-weight: 700 !important;
        font-size: 14px !important;
    }
    html body .team-requests-page .request-btn--approve:hover {
        background: #d0db72 !important;
    }

    /* Recommend Reject Button */
    html body .team-requests-page .request-btn--reject {
        background: #e41e3f !important; 
        color: #fff !important; 
        border: 2px solid #e41e3f !important;
        border-radius: 20px !important; 
        padding: 8px 20px !important; 
        font-weight: 700 !important;
        font-size: 14px !important;
    }
    html body .team-requests-page .request-btn--reject:hover {
        background: #c91634 !important;
    }

    @media (max-width: 768px) {
        html body .team-requests-page .requests-filter-bar,
        html body .team-requests-page .requests-toolbar {
            flex-direction: column !important;
            align-items: stretch !important;
        }
        html body .team-requests-page .requests-filters,
        html body .team-requests-page .requests-pay-filter,
        html body .team-requests-page .requests-search-form {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        html body .team-requests-page .requests-table tr.row-click {
            grid-template-columns: 1fr !important;
            padding: 28px 12px 14px !important;
        }
        html body .team-requests-page .requests-table tr.row-click td {
            grid-column: 1 / -1 !important;
            grid-row: auto !important;
            justify-self: start !important;
            position: static !important;
            white-space: normal !important;
        }
        html body .team-requests-page .request-actions {
            width: 100% !important;
            justify-content: flex-start !important;
        }
    }

    </style>
    <script>document.body.classList.add('overdue-reason-locked');</script>
@endif

<div class="requests-page team-requests-page" @if($overdueReasonCount > 0) data-overdue-reason-lock="1" @endif>    <div class="mgr-page-header">
        <div class="mgr-page-header__main">
            <h1>Team Requests</h1>
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
        'payFilter' => $payFilter ?? 'all',
        'search' => $search ?? '',
        'monthFilter' => $monthFilter ?? null,
        'employeeFilter' => $employeeFilter ?? null,
        'showSearchActions' => true,
        'searchPlaceholder' => 'Search name, type, status, dates...',
        'statusFilters' => [
            'all' => 'All',
            'pending' => 'Pending',
            'pending_senior' => 'Submitted',
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
                        'approvalComment' => (string) ($req->approval_comment ?? ''),
                        'originalLeaveType' => (string) ($req->original_leave_type ?? ''),
                        'convertedToUnpaidAt' => $req->converted_to_unpaid_at?->format('d M Y H:i') ?? '',
                        'conversionReason' => (string) ($req->converted_to_unpaid_reason ?? ''),
                        'managerRecommendation' => match (strtolower((string) ($req->manager_recommendation ?? ''))) {
                            'approve' => 'Recommend Approve',
                            'reject' => 'Recommend Reject',
                            default => '',
                        },
                        'managerRecommendationComment' => (string) ($req->manager_recommendation_comment ?? ''),
                    ];
                    $details = array_merge($details, \App\Support\LeaveReviewChecklist::forRequest($req), $req->popupMeta());
                @endphp
                <tr class="row-click {{ $req->isOverdue() ? 'overdue-row' : '' }}" data-details='@json($details)'>
                    <td>{{ $requests->firstItem() + $index }}</td>
                    <td>
                        {{ $req->leave_type }}
                        @if (!empty($req->is_half_day))
                            <div class="request-half-slot">Half day{{ ($details['halfDayPeriodLabel'] ?? '') !== '' ? ': '.$details['halfDayPeriodLabel'] : '' }}</div>
                        @endif
                    </td>
                    <td>{{ $employeeFull }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->start_date)->format('d M') }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->end_date)->format('d M') }}</td>
                    <td>{{ $req->created_at ? \Carbon\Carbon::parse($req->created_at)->format('d M Y') : '—' }}</td>
                    <td class="request-status-cell">
                        @php
                            $statusLabel = \App\Support\LeaveStatusLabels::display($rowStatus, $req->isOverdue());
                            $statusBadge = \App\Support\LeaveStatusLabels::badgeClass($rowStatus, $req->isOverdue());
                        @endphp
                        <span class="request-badge request-badge--{{ $statusBadge }}">@include('partials.request-status-icon', ['icon' => $statusBadge]){{ $statusLabel }}</span>
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
                                        <button type="button" class="request-btn request-btn--escalate" onclick="openLateReasonPopup({{ $req->id }})">@include('partials.request-action-icon', ['name' => 'edit']) Submit Reason</button>
                                    @endif
                                @else
                                    <div class="request-actions-inline">
                                        @if ($canApproveWithProof)
                                            <form class="recommend-approve-form" action="{{ route('manager.requests.recommend', $req) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="recommendation" value="approve" />
                                                <input type="hidden" name="manager_recommendation_comment" class="approval-comment-input" value="" />
                                                <button type="submit" class="request-btn request-btn--approve">@include('partials.request-action-icon', ['name' => 'check']) Recommend Approve</button>
                                            </form>
                                        @else
                                            <span class="request-actions-note" title="{{ $proofBlockReason }}">Awaiting proof</span>
                                        @endif
                                        <button type="button" class="request-btn request-btn--reject" onclick="openRejectPopup({{ $req->id }})">@include('partials.request-action-icon', ['name' => 'x']) Recommend Reject</button>
                                    </div>
                                @endif
                            @elseif ($rowStatus === 'pending_senior')
                                <span class="request-actions-submitted" title="Recommendation sent to Senior Manager">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                                    Submitted
                                </span>
                            @elseif ($rowStatus === 'rejected')
                                <a href="{{ route('manager.requests.show_reason', $req) }}" class="request-link">@include('partials.request-action-icon', ['name' => 'view']) View Reason</a>
                            @endif
                            @if($canMessage)
                                <a href="{{ route('messages.leave', $req) }}" class="request-link">@include('partials.request-action-icon', ['name' => 'message']) Messages</a>
                            @endif
                            @if($canDelete)
                                <form class="delete-request-form" method="POST" action="{{ route('manager.requests.destroy', $req) }}">
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
    <input type="hidden" name="recommendation" value="reject" />
    <input type="hidden" name="manager_recommendation_comment" id="reject-hidden-reason" />
</form>

<form id="late-reason-hidden-form" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="overdue_escalation_reason" id="late-reason-hidden-text" />
</form>

@include('partials.requests-page-scripts', [
    'showEmployee' => true,
    'showDays' => true,
    'showHandledMeta' => false,
    'backLabel' => '← Back to requests',
    'deleteWarning' => 'It will be permanently removed, including messages and attachments.',
])

<script>
const rejectUrlTemplate = @json(url('/manager/requests/__ID__/recommend'));
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
        confirmButtonText: 'Send to Senior Manager',
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
        const controller = new AbortController();
        const timeoutId = window.setTimeout(() => controller.abort(), 20000);
        const response = await fetch(lateReasonUrlTemplate.replace('__ID__', String(requestId)), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ overdue_escalation_reason: reason }),
            signal: controller.signal,
        });
        window.clearTimeout(timeoutId);

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
        const timedOut = error?.name === 'AbortError';
        Swal.fire({
            icon: 'error',
            title: 'Could not send reason',
            text: timedOut
                ? 'The senior manager email is taking too long. The reason may still have been saved — retry, or ask them to check Team Requests.'
                : (error.message || 'Please try again.'),
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
        icon: false,
        title: 'Recommend Reject',
        html: '<p class="swal-approve-copy">Add an optional comment for the senior manager. You can leave this blank.</p>',
        input: 'textarea',
        inputPlaceholder: 'Optional comment…',
        inputAttributes: { 'aria-label': 'Recommendation comments', maxlength: 1000 },
        showCancelButton: true,
        confirmButtonText: 'Send to Senior Manager',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#94a3b8',
        reverseButtons: true,
        customClass: {
            popup: 'swal-approve-popup',
            title: 'swal-approve-title',
            htmlContainer: 'swal-approve-html',
            input: 'swal-approve-input',
            cancelButton: 'swal-approve-cancel',
        },
        preConfirm: (value) => {
            const v = (value || '').trim();
            if (v.length > 1000) {
                Swal.showValidationMessage('Comments must be 1000 characters or less.');
                return false;
            }
            return v;
        }
    }).then((result) => {
        if (!result.isConfirmed) return;
        const form = document.getElementById('reject-hidden-form');
        form.action = rejectUrlTemplate.replace('__ID__', String(requestId));
        document.getElementById('reject-hidden-reason').value = result.value || '';
        form.submit();
    });
}
</script>
@endsection