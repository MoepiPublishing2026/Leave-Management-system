<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reject leave request</title>
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background:#f8fafc; color:#0f172a; padding:40px 20px; }
        .card { max-width:560px; margin:0 auto; background:#fff; border-radius:12px; padding:28px; border:1px solid #e2e8f0; }
        h1 { font-size:1.35rem; margin:0 0 8px; }
        p { color:#475569; line-height:1.5; }
        label { display:block; font-weight:700; margin:16px 0 8px; font-size:0.9rem; }
        textarea { width:100%; min-height:110px; padding:12px; border-radius:8px; border:1px solid #cbd5e1; box-sizing:border-box; font:inherit; }
        button {
            margin-top:16px; background:#dc2626; color:#fff; border:0; border-radius:10px;
            padding:12px 18px; font-weight:700; cursor:pointer; font-size:1rem;
        }
        .meta { background:#f8fafc; border-radius:8px; padding:12px 14px; font-size:0.92rem; margin-bottom:12px; }
        .files a {
            display:inline-block; margin:4px 8px 4px 0; padding:6px 10px; border-radius:8px;
            background:#eef2ff; color:#1e3a8a; font-weight:700; text-decoration:none; font-size:0.85rem;
        }
    </style>
</head>
<body>
@php
    $user = $leaveRequest->user;
    $name = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: 'Staff member';
    $isManagerApplicant = $leaveRequest->isManagerApplicant();
    $attachments = $attachments ?? [];
@endphp
    <div class="card">
        <h1>Reject leave request #{{ $leaveRequest->id }}</h1>
        <div class="meta">
            <div><strong>{{ $name }}</strong> · {{ $leaveRequest->leave_type }}</div>
            <div>Role: {{ \App\Support\RoleLabels::display($user->role ?? null) }}</div>
            <div>{{ $leaveRequest->start_date }} → {{ $leaveRequest->end_date }}</div>
            <div>Working days: {{ \App\Support\LeaveDaysFormatter::format($workingDays ?? 0) }} · Balance: {{ $remainingBalance ?? '—' }}</div>
            @if (! $isManagerApplicant)
                <div>Manager: {{ $recommenderName ?? '—' }}</div>
            @else
                <div>Approval path: Direct Senior Manager decision</div>
            @endif
            @if (count($attachments) > 0)
                <div style="margin-top:8px;">
                    @foreach ($attachments as $file)
                        <a href="{{ $file['viewUrl'] }}" target="_blank" rel="noopener">View {{ $file['name'] }}</a>
                        <a href="{{ $file['downloadUrl'] }}">Download</a>
                    @endforeach
                </div>
            @endif
        </div>
        <form method="POST" action="{{ $submitUrl }}">
            @csrf
            <label for="rejection_reason">Rejection reason (required)</label>
            <textarea id="rejection_reason" name="rejection_reason" required maxlength="500">{{ old('rejection_reason') }}</textarea>
            @error('rejection_reason')
                <p style="color:#991b1b;">{{ $message }}</p>
            @enderror
            <button type="submit">Confirm rejection</button>
        </form>
    </div>
</body>
</html>
