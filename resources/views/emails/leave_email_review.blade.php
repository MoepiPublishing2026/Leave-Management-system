<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Review leave request #{{ $leaveRequest->id }}</title>
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background:#f8fafc; color:#0f172a; padding:24px 16px; }
        .card { max-width:720px; margin:0 auto; background:#fff; border-radius:12px; padding:28px; border:1px solid #e2e8f0; }
        h1 { font-size:1.45rem; margin:0 0 6px; }
        .sub { color:#64748b; margin:0 0 20px; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:12px 18px; margin-bottom:18px; }
        .item label { display:block; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.04em; color:#64748b; font-weight:700; }
        .item div { font-weight:600; margin-top:4px; }
        .full { grid-column:1 / -1; }
        .files a {
            display:inline-block; margin:0 8px 8px 0; padding:8px 12px; border-radius:8px;
            background:#eef2ff; color:#1e3a8a; font-weight:700; text-decoration:none; font-size:0.9rem;
        }
        .actions { margin-top:22px; display:flex; flex-wrap:wrap; gap:10px; }
        .btn {
            display:inline-flex; align-items:center; justify-content:center; padding:12px 18px;
            border-radius:10px; font-weight:700; text-decoration:none; border:0; cursor:pointer; font-size:1rem;
        }
        .btn-approve { background:#16a34a; color:#fff; }
        .btn-reject { background:#dc2626; color:#fff; }
        .btn-disabled { background:#94a3b8; color:#fff; cursor:not-allowed; }
        .pill { display:inline-block; padding:4px 10px; border-radius:999px; background:#fce7f3; color:#9d174d; font-weight:700; font-size:0.85rem; }
        .pill-ok { background:#dcfce7; color:#166534; }
        .note { color:#9a3412; font-weight:600; margin-top:8px; }
        @media (max-width:640px) { .grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
@php
    $user = $leaveRequest->user;
    $name = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: 'Staff member';
    $isManagerApplicant = $leaveRequest->isManagerApplicant();
@endphp
    <div class="card">
        <h1>Leave request #{{ $leaveRequest->id }}</h1>
        <p class="sub">
            Status:
            <span class="pill {{ $awaiting ? '' : 'pill-ok' }}">{{ $statusLabel }}</span>
        </p>

        <div class="grid">
            <div class="item"><label>Applicant</label><div>{{ $name }}</div></div>
            <div class="item"><label>Role</label><div>{{ \App\Support\RoleLabels::display($user->role ?? null) }}</div></div>
            <div class="item"><label>Email</label><div>{{ $user->email ?? '—' }}</div></div>
            <div class="item"><label>Department</label><div>{{ $user->department ?? '—' }}</div></div>
            <div class="item"><label>Leave type</label><div>{{ $leaveRequest->leave_type }}</div></div>
            <div class="item"><label>Start</label><div>{{ $leaveRequest->start_date }}</div></div>
            <div class="item"><label>End</label><div>{{ $leaveRequest->end_date }}</div></div>
            <div class="item"><label>Working days</label><div>{{ \App\Support\LeaveDaysFormatter::format($workingDays) }}</div></div>
            <div class="item"><label>Remaining balance</label><div>{{ $remainingBalance }}</div></div>
            @if (! $isManagerApplicant)
                <div class="item"><label>Manager</label><div>{{ $recommenderName }}</div></div>
                <div class="item"><label>Recommendation</label><div>{{ $recLabel }}</div></div>
            @endif
            <div class="item full"><label>Applicant comment</label><div>{{ $leaveRequest->important_comment ?: 'None' }}</div></div>
            @if (! $isManagerApplicant)
                <div class="item full"><label>Manager comments</label><div>{{ $leaveRequest->manager_recommendation_comment ?: 'None' }}</div></div>
            @else
                <div class="item full"><label>Approval path</label><div>Direct Senior Manager decision</div></div>
            @endif
            <div class="item full">
                <label>Supporting documents</label>
                <div class="files" style="margin-top:8px;">
                    @forelse ($attachments as $file)
                        <a href="{{ $file['viewUrl'] }}" target="_blank" rel="noopener">View {{ $file['name'] }}</a>
                        <a href="{{ $file['downloadUrl'] }}">Download</a>
                    @empty
                        <div>None attached</div>
                    @endforelse
                </div>
            </div>
        </div>

        @if ($awaiting)
            @if (! $canApproveWithProof && $proofBlockReason)
                <p class="note">{{ $proofBlockReason }}</p>
            @endif
            <div class="actions">
                @if ($canApproveWithProof)
                    <a class="btn btn-approve" href="{{ $approveUrl }}">Approve</a>
                @else
                    <span class="btn btn-disabled">Approve (proof required)</span>
                @endif
                <a class="btn btn-reject" href="{{ $rejectUrl }}">Reject</a>
            </div>
        @else
            <p class="note" style="color:#166534;">This decision has already been submitted. No further action is needed.</p>
        @endif
    </div>
</body>
</html>
