@extends('layouts.manager')

@section('title', 'Rejection Reason')

@section('content')
<style>
    .manager-container {
        font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
        max-width: 1500px;
        margin: 40px auto;
        background: white;
        border-radius: 20px;
        padding: 30px 40px;
         box-shadow: 0 0 10px #a7bd8dff;
        user-select: none;
    }
    h2 {
        color: #2c3e50;
        font-weight: 700;
        border-left: 6px solid #ef4444;
        padding-left: 15px;
        font-size: 32px;
        margin-bottom: 24px;
        letter-spacing: 0.04em;
    }
    p {
        font-size: 1.1rem;
        color: #333;
        min-height: 100px;
        padding: 15px 20px;
        background-color: #fbdcdc;
        border-radius: 16px;
        border: 1px solid #ef4444;
        white-space: pre-line;
    }
    a.back-link {
        display: inline-block;
        margin-top: 30px;
        padding: 10px 24px;
        font-weight: 600;
        font-size: 1rem;
        color: white;
        background-color: #138056;
        border-radius: 16px;
        text-decoration: none;
        user-select: none;
        transition: background-color 0.3s ease;
    }
    a.back-link:hover {
        background-color: #0f6e47;
        text-decoration: none;
    }
</style>

<div class="manager-container">
    <h2>Rejection Reason</h2>
    <p>{{ $request->rejection_reason ?? 'No reason provided' }}</p>
    <a href="{{ route('manager.dashboard') }}" class="back-link">Back to Dashboard</a>
</div>
@endsection
