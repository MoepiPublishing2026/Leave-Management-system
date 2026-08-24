@extends('layouts.pdf')

@section('title', 'Viewer Dashboard Report')

@section('styles')
<style>
    h1 {
        color: #2c3e50;
        font-weight: 700;
        font-size: 24px;
        margin: 0 0 16px;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, DejaVu Sans, sans-serif;
    }
    h2 {
        font-weight: 600;
        color: #138056;
        margin-top: 18px;
        margin-bottom: 8px;
        font-size: 16px;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, DejaVu Sans, sans-serif;
    }
    p, ul {
        font-size: 12px;
        line-height: 1.55;
        color: #333333;
        margin-bottom: 12px;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, DejaVu Sans, sans-serif;
    }
    ul { padding-left: 18px; }
</style>
@endsection

@section('content')
    <h1>Company Leave Policies</h1>

    <h2>Annual Leave</h2>
    <p>All full-time employees are entitled to 24 days of paid annual leave per annum which accrues monthly pro-rata. Leave should be requested at least 30 days in advance and approved by line management.</p>

    <h2>Sick Leave</h2>
    <p>Sick leave is granted for illness or injury. Employees must notify their manager as early as possible and provide a medical certificate for absences exceeding 2 days.</p>

    <h2>Maternity / Paternity Leave</h2>
    <p>Maternity leave of 12 weeks and paternity leave of 5 days are provided according to the applicable legislation. Proper documentation and notice are required.</p>

    <h2>Bereavement Leave</h2>
    <p>Employees may take up to 5 days of paid bereavement leave for the death of immediate family members.</p>

    <h2>Unpaid Leave</h2>
    <p>Employees may request unpaid leave for personal reasons. Approval is at the discretion of management and based on operational requirements.</p>

    <h2>Leave Application Process</h2>
    <ul>
        <li>Submit leave application through the system with all required details.</li>
        <li>Approvals are made by the employee's direct manager.</li>
        <li>Leave balances are maintained and visible to employees on their dashboard.</li>
    </ul>

    <h2>Return to Work</h2>
    <p>Upon return from extended leave, employees must provide certification if applicable and coordinate with HR for any necessary updates.</p>
@endsection
