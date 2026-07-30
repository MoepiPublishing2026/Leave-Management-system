<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background:#fff; color:#333; padding:20px; }
        h2 { color:#1b1e40; }
        ul { background:#f7fcef; border:1px solid #b2d12f; max-width:420px; padding:15px 25px; border-radius:8px; list-style:none; }
        a.btn { display:inline-block; background:#a2d109; color:#0a1347 !important; padding:12px 24px; border-radius:25px; font-weight:700; text-decoration:none; margin-top:20px; }
    </style>
</head>
<body>
@php
    $user = $leaveRequest->user;
    $name = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: 'Employee';
@endphp
    <h2>Pending leave review reminder ({{ $stageLabel }})</h2>
    <p>Leave request <strong>#{{ $leaveRequest->id }}</strong> for <strong>{{ $name }}</strong> is still awaiting your recommendation.</p>
    <ul>
        <li><b>Type:</b> {{ $leaveRequest->leave_type }}</li>
        <li><b>Dates:</b> {{ $leaveRequest->start_date }} → {{ $leaveRequest->end_date }}</li>
        <li><b>Working days:</b> {{ \App\Support\LeaveDaysFormatter::format($workingDays) }}</li>
    </ul>
    <a class="btn" href="{{ url('/manager/requests?status=pending&search='.$leaveRequest->id) }}">Review in app</a>
    <p style="margin-top:28px;font-size:12px;color:#7b7b7b;">&copy; {{ date('Y') }} Moepi Publishing. All rights reserved.</p>
</body>
</html>
