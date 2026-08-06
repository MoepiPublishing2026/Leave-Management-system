<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background:#fff; color:#333; padding:20px; }
        h2 { color:#1b1e40; font-weight:700; margin-bottom:16px; }
        p { font-size:16px; line-height:1.5; margin-bottom:12px; color:#4b4b4b; }
        ul {
            background:#f7fcef; border:1px solid #b2d12f; max-width:560px;
            padding:15px 25px; border-radius:8px; list-style:none; color:#4a7523; font-weight:600;
        }
        ul li { margin-bottom:8px; font-size:15px; }
        .docs { margin:16px 0; max-width:560px; }
        .docs a {
            display:inline-block; margin:4px 8px 4px 0; padding:8px 14px; border-radius:8px;
            background:#eef2ff; color:#1e3a8a !important; font-weight:700; text-decoration:none; font-size:14px;
        }
        .actions { margin-top:28px; }
        a.btn {
            display:inline-block; padding:12px 22px; margin:0 8px 10px 0; border-radius:25px;
            font-weight:700; text-decoration:none; font-size:15px; color:#fff !important;
        }
        a.btn-review { background:#0f766e; }
        a.btn-view { background:#a2d109; color:#0a1347 !important; }
        .muted { font-size:12px; color:#7b7b7b; margin-top:28px; }
        .note { color:#9a3412; font-size:14px; font-weight:600; }
    </style>
</head>
<body>
@php
    $req = $leaveRequest;
    $user = $req->user;
    $fullName = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: 'Staff member';
    $isManagerApplicant = $req->isManagerApplicant();
    $recommendation = strtolower((string) ($req->manager_recommendation ?? ''));
    $recLabel = $recommendation === 'reject' ? 'Recommend Reject' : ($recommendation === 'approve' ? 'Recommend Approve' : '—');
    $attachments = $documentLinks ?? [];
@endphp

    <h2>Leave request awaiting your decision</h2>
    <p>Open the secure review page to approve or reject without logging in. After you decide, the action buttons are removed. Links expire in 7 days.</p>

    <ul>
        <li><b>Request #:</b> {{ $req->id }}</li>
        <li><b>Applicant:</b> {{ $fullName }}</li>
        <li><b>Role:</b> {{ \App\Support\RoleLabels::display($user->role ?? null) }}</li>
        <li><b>Email:</b> {{ $user->email ?? '—' }}</li>
        <li><b>Department:</b> {{ $user->department ?? '—' }}</li>
        <li><b>Leave Type:</b> {{ $req->leave_type }}</li>
        <li><b>Start Date:</b> {{ $req->start_date }}</li>
        <li><b>End Date:</b> {{ $req->end_date }}</li>
        <li><b>Working Days:</b> {{ \App\Support\LeaveDaysFormatter::format($workingDays) }}</li>
        <li><b>Remaining Balance:</b> {{ $remainingBalance }}</li>
        <li><b>Applicant Comment:</b> {{ $req->important_comment ?: 'N/A' }}</li>
        @if (! $isManagerApplicant)
            <li><b>Manager:</b> {{ $recommenderName }}</li>
            <li><b>Manager Recommendation:</b> {{ $recLabel }}</li>
            <li><b>Manager Comments:</b> {{ $req->manager_recommendation_comment ?: 'None' }}</li>
        @else
            <li><b>Approval Path:</b> Direct Senior Manager decision</li>
        @endif
    </ul>

    <div class="docs">
        <p style="margin-bottom:8px;"><b>Supporting documents</b></p>
        @if (count($attachments) > 0)
            @foreach ($attachments as $file)
                <a href="{{ $file['viewUrl'] }}">View {{ $file['name'] }}</a>
                <a href="{{ $file['downloadUrl'] }}">Download</a>
            @endforeach
        @else
            <p>None attached</p>
        @endif
    </div>

    @if (empty($canApproveWithProof) && !empty($proofBlockReason))
        <p class="note">{{ $proofBlockReason }}</p>
    @endif

    <div class="actions">
        <a href="{{ $reviewUrl }}" class="btn btn-review">Review &amp; decide</a>
        <a href="{{ $viewUrl }}" class="btn btn-view">Open in app</a>
    </div>

    <p class="muted">&copy; {{ date('Y') }} Moepi Publishing. All rights reserved.</p>
</body>
</html>
