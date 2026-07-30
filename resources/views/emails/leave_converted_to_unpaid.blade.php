<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <style>
        body { font-family: "Segoe UI", Tahoma, sans-serif; background:#f8fafc; color:#334155; padding:24px; }
        .card { max-width:560px; margin:auto; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:28px; }
        h2 { color:#991b1b; margin-top:0; }
        .details { background:#fff7ed; border:1px solid #fdba74; border-radius:8px; padding:14px 18px; }
        .details p { margin:6px 0; }
        a { color:#0a1347; font-weight:700; }
    </style>
</head>
<body>
@php
    $user = $leaveRequest->user;
    $name = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: 'Staff member';
@endphp
    <div class="card">
        <h2>Leave changed to Unpaid</h2>
        <p>Leave request <strong>#{{ $leaveRequest->id }}</strong> for <strong>{{ $name }}</strong> was automatically changed to Unpaid leave because the required supporting documents were not submitted by the agreed deadline.</p>

        <div class="details">
            <p><strong>Original type:</strong> {{ $leaveRequest->original_leave_type ?: 'Sick' }}</p>
            <p><strong>New type:</strong> Unpaid</p>
            <p><strong>Dates:</strong> {{ $leaveRequest->start_date }} to {{ $leaveRequest->end_date }}</p>
            <p><strong>Proof deadline:</strong> {{ $proofDeadline ?: 'Required deadline passed' }}</p>
            <p><strong>Status:</strong> {{ \App\Support\LeaveStatusLabels::display($leaveRequest->status) }}</p>
        </div>

        <p><a href="{{ url('/login') }}">Open the Leave Management System</a></p>
        <p style="font-size:12px;color:#64748b;">This is an automatic policy action recorded in the request audit history.</p>
    </div>
</body>
</html>
