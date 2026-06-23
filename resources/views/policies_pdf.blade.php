<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Company Leave Policies</title>
    <style>
        body {
            font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
            color: #333;
            margin: 0;
            padding: 24px 32px 48px;
        }
        h1 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 28px;
            margin: 0 0 24px;
        }
        h2 {
            font-weight: 600;
            color: #138056;
            font-size: 18px;
            margin: 22px 0 8px;
        }
        p {
            font-size: 13px;
            line-height: 1.55;
            margin: 0 0 10px;
        }
        .policy-badges {
            margin: 0 0 10px;
        }
        .policy-badge {
            display: inline-block;
            background: #e8f5ef;
            color: #138056;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            margin: 0 6px 6px 0;
        }
        .policy-badge--muted {
            background: #f1f5f9;
            color: #475569;
        }
        ul {
            font-size: 13px;
            line-height: 1.55;
            margin: 0 0 14px;
            padding-left: 20px;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #334155;
            margin: 28px 0 8px;
        }
    </style>
</head>
<body>
    <h1>Company Leave Policies</h1>
    <p style="font-size:12px;color:#64748b;margin-bottom:20px;">Generated {{ now()->format('d M Y') }} — reflects current policy settings.</p>

    @foreach ($leaveTypes as $type)
        @php $policy = $policies[$type] ?? null; @endphp
        <h2>{{ $type }} Leave</h2>
        @if ($policy)
            <div class="policy-badges">
                <span class="policy-badge">{{ (int) $policy->default_days }} days per year</span>
                @if ($policy->max_carry_over)
                    <span class="policy-badge policy-badge--muted">Carry over: {{ (int) $policy->max_carry_over }} days</span>
                @endif
                @if ($policy->allow_half_days)
                    <span class="policy-badge policy-badge--muted">Half days allowed</span>
                @endif
                @if ($policy->requires_proof)
                    <span class="policy-badge">Proof required</span>
                @endif
            </div>
        @endif
        <p>@include('partials.policy-leave-type-text', ['type' => $type, 'policy' => $policy])</p>
    @endforeach

    <div class="section-title">Leave application process</div>
    <ul>
        <li>Submit leave applications through the system with all required details.</li>
        <li>Approvals are made by the employee's direct manager.</li>
        <li>Leave balances are maintained and visible on your dashboard.</li>
    </ul>

    <div class="section-title">Return to work</div>
    <p>Upon return from extended leave, employees must provide certification if applicable and coordinate with HR for any necessary updates.</p>
</body>
</html>
