@extends('layouts.manager')

@section('title', 'Rejection Reason')

@section('content')
@include('partials.requests-page-styles')

<div class="requests-page">
    <h1>Rejection Reason</h1>
    <p class="requests-page-lead">Request #{{ $request->id }} · {{ $request->leave_type }} · {{ \Carbon\Carbon::parse($request->start_date)->format('d M Y') }}</p>

    <div style="padding: 18px 20px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; margin-bottom: 20px;">
        <p style="margin: 0; font-size: 1rem; color: #7f1d1d; white-space: pre-line; line-height: 1.5;">{{ $request->rejection_reason ?? 'No reason provided' }}</p>
    </div>

    <a href="{{ route('manager.requests.index', ['status' => 'rejected']) }}" class="requests-btn requests-btn--primary">Back to Requests</a>
</div>
@endsection
