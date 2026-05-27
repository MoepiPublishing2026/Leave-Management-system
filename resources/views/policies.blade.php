@extends('layouts.app')

@section('title', 'Company Leave Policies')

@section('content')
@include('partials.content-page-styles')
<style>
    .policy-card h2 {
        margin: 0 0 10px;
        font-size: 1.25rem;
        font-weight: 800;
        color: #138056;
    }
    .policy-card p {
        margin: 0;
        color: #475569;
        line-height: 1.65;
        font-size: 0.95rem;
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

                <p>
                    @if($type === 'Annual')
                        Annual leave is the primary leave balance for the year. Any approved leave taken reduces the employee's annual balance, because these absences count against the annual allocation. Once the annual balance reaches 0, the system disables leave applications for other leave types, but still allows Sick leave (with medical certificate), Bereavement leave (with proof), and Maternity leave (with proof). Maternity leave is treated as a special case and does not reduce annual leave.
                    @elseif($type === 'Sick')
                        Sick leave is granted up to {{ $policy->default_days }} days for illness or injury. Employees must notify their manager as early as possible and provide a medical certificate for absences exceeding 2 days.
                    @elseif(in_array($type, ['Maternity', 'Paternity']))
                        {{ $type }} leave of {{ $policy->default_days }} days is provided according to applicable legislation. Proper documentation and notice are required.
                    @elseif($type === 'Bereavement')
                        Employees may take up to {{ $policy->default_days }} days of paid bereavement leave for the death of immediate family members.
                    @elseif($type === 'Unpaid')
                        Employees may request unpaid leave for personal reasons. Approval is at the discretion of management and based on operational requirements.
                    @elseif($type === 'Study')
                        Study leave may be requested for approved training or educational programs. Supporting documentation is required, and approval is subject to operational requirements.
                    @else
                        {{ $type }} leave details are as per company policy. Employees are allowed {{ $policy->default_days }} days annually.
                    @endif
                </p>
            @else
                <p>Policy details are currently unavailable.</p>
            @endif
        </article>
    @endforeach

    <div class="app-page-panel">
        <h2 class="app-page-section-title">Leave application process</h2>
        <div class="app-info-box">
            <ul>
                <li>Submit leave applications through the system with all required details.</li>
                <li>Approvals are made by the employee's direct manager.</li>
                <li>Leave balances are maintained and visible on your dashboard.</li>
            </ul>
        </div>
    </div>

    <div class="app-page-panel">
        <h2 class="app-page-section-title">Return to work</h2>
        <div class="app-info-box">
            <p>Upon return from extended leave, employees must provide certification if applicable and coordinate with HR for any necessary updates.</p>
        </div>
    </div>
</div>
@endsection
