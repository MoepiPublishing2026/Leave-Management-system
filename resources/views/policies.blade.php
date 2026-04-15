@extends('layouts.app')

@section('title', 'Company Leave Policies')

@section('content')
<div style="position: relative;">
      <h1>Company Leave Policies</h1>
    <!-- <a href="{{ route('policies.download') }}" class="download-pdf-btn" target="_blank" rel="noopener noreferrer">Download Policies PDF</a> -->
    <div class="policy-container">
      

        @foreach ($leaveTypes as $type)
        @php
            $policy = $policies[$type] ?? null;
        @endphp

        <h2>{{ $type }} Leave</h2>
        @if ($policy)
            <p>
                @if($type === 'Annual')
                Annual leave is the primary leave balance for the year. Any approved leave taken reduces the employee’s annual balance, because these absences count against the annual allocation. Once the annual balance reaches 0, the system disables leave applications for other leave types, but still allows Sick leave (with medical certificate), Bereavement leave (with proof), and Maternity leave (with proof).
                Maternity leave is treated as a special case and does not reduce annual leave.
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
            <p>Policy details currently unavailable.</p>
        @endif
        @endforeach

        <h2>Leave Application Process</h2>
        <ul>
            <li>Submit leave application through the system with all required details.</li>
            <li>Approvals are made by the employee's direct manager.</li>
            <li>Leave balances are maintained and visible to employees on their dashboard.</li>
        </ul>

        <h2>Return to Work</h2>
        <p>Upon return from extended leave, employees must provide certification if applicable and coordinate with HR for any necessary updates.</p>
    </div>
</div>

<style>
    .policy-container {
        font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
        max-width: 1500px;
        margin: 40px auto 80px;
        background: white;
        padding: 30px 40px;
        border-radius: 20px;
         box-shadow: 0 0 10px #a7bd8dff;
    }
    h1 {
        color: #2c3e50;
        border-left: 6px solid #138056ff;
        padding-left: 15px;
        font-weight: 700;
        font-size: 36px;
        margin-bottom: 20px;
        letter-spacing: 0.04em;
    }
    .download-pdf-btn {
        position: absolute;
        top: 20px;
        right: 40px;
        background-color: #138056ff;
        color: white;
        font-weight: 700;
        padding: 10px 18px;
        border-radius: 8px;
        text-decoration: none;
        box-shadow: 0 0 10px #a7bd8dff;
        transition: background-color 0.25s;
        z-index: 10000;
    }
    .download-pdf-btn:hover {
        background-color: #0e6b3f;
    }
    h2 {
        font-weight: 600;
        color: #138056ff;
        margin-top: 30px;
        margin-bottom: 10px;
    }
    p, ul {
        font-size: 18px;
        line-height: 1.6;
        color: #333333;
        margin-bottom: 15px;
    }
    ul {
        list-style-type: disc;
        padding-left: 20px;
    }
</style>
@endsection
