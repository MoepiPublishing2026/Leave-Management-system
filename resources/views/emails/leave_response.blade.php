<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body {
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f4f8;
    color: #333;
    padding: 20px;
    line-height: 1.6;
  }
  h2 {
    color: #1b1e40;
    font-weight: 700;
    text-align: left;
  }
  p {
    font-size: 16px;
    margin-bottom: 20px;
  }
  ul {
    max-width: 500px;
    margin: 20px auto;
    padding: 15px 20px;
    background-color: #ffffff;
    border-radius: 8px;
    border: 1px solid #d1d1d1;
    list-style: none;
    color: #4a4a4a;
  }
  ul li {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
  }
  ul li:last-child {
    border-bottom: none;
  }
  a.button {
    display: inline-block;
    margin: 30px auto;
    padding: 12px 30px;
    background-color: #4CAF50;
    color: #fff !important;
    font-weight: 700;
    border-radius: 50px;
    text-decoration: none;
    font-size: 14px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    transition: background 0.3s ease;
  }
  a.button:hover {
    background-color: #45a049;
  }
</style>
</head>
<body>

<h2>Hello {{ $leaveRequest->user->first_name }},</h2>

<p>Your leave request status has been updated to <strong>{{ ucfirst($leaveRequest->status) }}</strong>.</p>

@if($leaveRequest->status === 'rejected')
    <p>Reason for rejection: {{ $leaveRequest->rejection_reason }}</p>
@endif

<p>Please check your leave status in your <a href="{{ url('/login') }}" class="button">Account Dashboard</a>.</p>

<p>Thank you for using Moepi Publishing!</p>

<p style="margin-top: 30px; font-size: 12px; color: #6b7280;">&copy; {{ date('Y') }} Moepi Publishing. All rights reserved.</p>

</body>
</html>
