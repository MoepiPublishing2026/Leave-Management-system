@extends('layouts.app')

@section('title', 'Company Leave Policies')

@section('content')
@include('partials.content-page-styles')
<style>
    .policy-card h2 {
        margin: 0 0 10px;
        font-size: 1.2rem;
        line-height: 1.3;
        font-weight: 700;
        color: #273345;
        letter-spacing: 0;
    }
    .policy-card p {
        margin: 0;
        color: #080808;
        line-height: 1.5;
        font-size: 0.95rem;
        font-weight: 400;
    }
</style>

<div class="app-page">
    <div class="app-page-header">
        <h1>Company Leave Policies</h1>
        <a href="{{ route('policies.download') }}" class="app-page-btn app-page-btn--secondary" target="_blank" rel="noopener noreferrer">Download PDF</a>
    </div>
    <p class="app-page-lead">Guidelines for each leave type, including entitlements and documentation requirements.</p>

    @foreach ($leaveTypes as $type)
        @php
            $policy = $policies[$type] ?? null;
        @endphp
        <article class="app-page-panel policy-card">
            <h2>{{ $type }} Leave</h2>

            @if ($policy)
                <div class="app-badge-row">
                    <span class="app-badge">{{ (int) $policy->default_days }} days per year</span>
                    @if ($policy->max_carry_over)
                        <span class="app-badge app-badge--muted">Carry over: {{ (int) $policy->max_carry_over }} days</span>
                    @endif
                    @if ($policy->allow_half_days)
                        <span class="app-badge app-badge--muted">Half days allowed</span>
                    @endif
                    @if ($policy->requires_proof)
                        <span class="app-badge">Proof required</span>
                    @endif
                </div>

                <p>@include('partials.policy-leave-type-text', ['type' => $type, 'policy' => $policy])</p>
            @else
                <p>Policy details are currently unavailable.</p>
            @endif
        </article>
    @endforeach
<div class="leave-info-grid">
    <div class="app-page-panel-1">
        <h2 class="app-page-section-title">Leave application process</h2>
        <div class="app-info-box">
            <ul>
                <li>Submit leave applications through the system with all required details.</li>
                <li>Approvals are made by the employee's direct manager.</li>
                <li>Leave balances are maintained and visible on your dashboard.</li>
            </ul>
        </div>
    </div>

    <div class="app-page-panel-1">
        <h2 class="app-page-section-title">Return to work</h2>
        <div class="app-info-box">
            <p>Upon return from extended leave, employees must provide certification if applicable and coordinate with HR for any necessary updates.</p>
        </div>
    </div>
</div>

</div>
@endsection
