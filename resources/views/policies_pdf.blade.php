@extends('layouts.pdf')

@section('title', 'Company Leave Policies')

@section('styles')
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, DejaVu Sans, sans-serif;
            color: #333;
        }
        h1 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 24px;
            margin: 0 0 12px;
        }
        h2 {
            font-weight: 600;
            color: #138056;
            font-size: 16px;
            margin: 18px 0 6px;
        }
        p {
            font-size: 12px;
            line-height: 1.55;
            margin: 0 0 10px;
        }
        .policy-badges { margin: 0 0 8px; }
        .policy-badge {
            display: inline-block;
            background: #e8f5ef;
            color: #138056;
            font-size: 10px;
            font-weight: 600;
            padding: 3px 7px;
            border-radius: 6px;
            margin: 0 6px 6px 0;
        }
        .policy-badge--muted {
            background: #f1f5f9;
            color: #475569;
        }
        ul {
            font-size: 12px;
            line-height: 1.55;
            margin: 0 0 12px;
            padding-left: 18px;
        }
        .section-title {
            font-size: 15px;
            font-weight: 600;
            color: #334155;
            margin: 22px 0 6px;
        }
    </style>
@endsection

@section('content')
    <h1>Company Leave Policies</h1>
    <p style="font-size:11px;color:#64748b;margin-bottom:16px;">Generated {{ now()->format('d M Y') }} — reflects current policy settings.</p>

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
@endsection
