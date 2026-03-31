<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
     <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
            color: #333333;
            padding: 20px;
        }
        h2 {
            color: #1b1e40;
            font-weight: 700;
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 15px;
            color: #4b4b4b;
        }
        ul {
            background-color: #f7fcef;
            border: 1px solid #b2d12f;
            max-width: 400px;
            padding: 15px 25px;
            border-radius: 8px;
            list-style-type: none;
            color: #4a7523;
            font-weight: 600;
        }
        ul li {
            margin-bottom: 8px;
            font-size: 16px;
        }
        a.dashboard-button {
            display: inline-block;
            background-color: #a2d109;
            padding: 12px 30px;
            margin-top: 25px;
            color: white !important;
            font-weight: 700;
            border-radius: 25px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        a.dashboard-button:hover {
            background-color: #87b20a;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h2>New Leave Request Submitted</h2>

    <p>Employee <strong>{{ $leaveRequest->user->first_name }} {{ $leaveRequest->user->last_name }}</strong> has submitted a leave request.</p>

    <ul>
        <li><b>Leave Type:</b> {{ $leaveRequest->leave_type }}</li>
        <li><b>Start Date:</b> {{ $leaveRequest->start_date }}</li>
        <li><b>End Date:</b> {{ $leaveRequest->end_date }}</li>
        <li><b>Number of Days:</b> {{ $workingDays }}</li>
        <li><b>Comment:</b> {{ $leaveRequest->important_comment ?? 'N/A' }}</li>
    </ul>

    <a href="{{ url('/login') }}" class="dashboard-button">Go to Dashboard</a>

    <p style="margin-top: 30px; font-size: 12px; color: #7b7b7b;">&copy; {{ date('Y') }} Moepi Publishing. All rights reserved.</p>
</body>
</html>
