@extends('layouts.pdf')

@section('title', 'Company Leave Policies')

@section('styles')
    <style>
        .pdf-content {
            font-family: "Segoe UI", DejaVu Sans, sans-serif;
            color: #273345;
        }
        h1 {
            color: #273345;
            font-weight: 700;
            font-size: 24px;
            margin: 0 0 6px;
            letter-spacing: 0.02em;
        }
        .pdf-lead {
            margin: 0 0 16px;
            color: #666;
            font-size: 11px;
            line-height: 1.5;
        }
        .policy-card {
            border: 1px solid #d4d4d4;
            border-radius: 14px;
            padding: 12px 14px 14px;
            margin: 0 0 12px;
            background: #fff;
        }
        .policy-card h2 {
            margin: 0 0 8px;
            font-size: 14px;
            line-height: 1.3;
            font-weight: 700;
            color: #273345;
        }
        .policy-card p {
            margin: 0;
            color: #080808;
            line-height: 1.5;
            font-size: 11px;
            font-weight: 400;
        }
        .policy-badges { margin: 0 0 8px; }
        .policy-badge {
            display: inline-block;
            background: #d4e510;
            color: #273345;
            font-size: 9px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            margin: 0 6px 6px 0;
        }
        .policy-badge--muted {
            background: #cecece;
            color: #273345;
        }
        .policy-info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin-top: 8px;
        }
        .policy-info-cell {
            width: 50%;
            vertical-align: top;
            border: 3px solid #d4e510;
            border-radius: 7px;
            padding: 12px 14px 14px;
            background: #fff;
        }
        .policy-info-title {
            font-size: 11px;
            font-weight: 800;
            color: #334155;
            margin: 0 0 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .policy-info-cell ul {
            margin: 0;
            padding-left: 16px;
            color: #080808;
            font-size: 11px;
            line-height: 1.5;
        }
        .policy-info-cell p {
            margin: 0;
            color: #080808;
            font-size: 11px;
            line-height: 1.5;
        }
    </style>
@endsection

@section('content')
    <h1>Company Leave Policies</h1>
    <p class="pdf-lead">Guidelines for each leave type, including entitlements and documentation requirements. Generated {{ now()->format('d M Y') }}.</p>

    @foreach ($leaveTypes as $type)
        @php $policy = $policies[$type] ?? null; @endphp
        <div class="policy-card">
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
                <p>@include('partials.policy-leave-type-text', ['type' => $type, 'policy' => $policy])</p>
            @else
                <p>Policy details are currently unavailable.</p>
            @endif
        </div>
    @endforeach

    <table class="policy-info-table">
        <tr>
            <td class="policy-info-cell">
                <div class="policy-info-title">Leave application process</div>
                <ul>
                    <li>Submit leave applications through the system with all required details.</li>
                    <li>Approvals are made by the employee's direct manager.</li>
                    <li>Leave balances are maintained and visible on your dashboard.</li>
                </ul>
            </td>
            <td class="policy-info-cell">
                <div class="policy-info-title">Return to work</div>
                <p>Upon return from extended leave, employees must provide certification if applicable and coordinate with HR for any necessary updates.</p>
            </td>
        </tr>
    </table>
@endsection
