@extends('layouts.app')

@section('title', 'Leaves - ' . ucfirst($status))

@section('content')
<style>
    .page-container {
        max-width: 1500px;
        margin: 30px auto;
        padding: 20px;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 6px 20px rgba(163, 71, 71, 0.08);
        font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
    }
    h1 {
        font-weight: 700;
        font-size: 36px;
        color: #2c3e50;
        margin: 0 0 18px 0;
        letter-spacing: 0.04em;
    }
    .filter-links { margin-bottom: 16px; }
    .filter-links a { margin-right: 12px; text-decoration: none; color: #138056ff; font-weight: 800; }
    .filter-links a.active { color: #0a382b; text-decoration: underline; }
</style>

@php
    $activeKey = strtolower(trim((string) $status));
@endphp

<div class="page-container">
    <h1>Leaves - {{ ucfirst($status) }}</h1>

    <div class="filter-links">
        <a href="{{ route('leave.myRequests') }}" class="{{ $activeKey === 'all' ? 'active' : '' }}">All</a>
        <a href="{{ route('leave.status', ['status' => 'pending']) }}" class="{{ $activeKey === 'pending' ? 'active' : '' }}">Pending</a>
        <a href="{{ route('leave.status', ['status' => 'overdue']) }}" class="{{ $activeKey === 'overdue' ? 'active' : '' }}">Overdue</a>
        <a href="{{ route('leave.status', ['status' => 'approved']) }}" class="{{ $activeKey === 'approved' ? 'active' : '' }}">Approved</a>
        <a href="{{ route('leave.status', ['status' => 'rejected']) }}" class="{{ $activeKey === 'rejected' ? 'active' : '' }}">Rejected</a>
        <a href="{{ route('leave.status', ['status' => 'withdrawn']) }}" class="{{ $activeKey === 'withdrawn' ? 'active' : '' }}">Withdrawn</a>
    </div>

    @if($leaves->isEmpty())
        <p style="color: #888; font-style: italic;">No leaves found with status "{{ $status }}".</p>
    @else
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd; font-family: Arial, sans-serif;">
            <thead style="background: #f9fafb;">
                <tr>
                    <th style="padding: 10px; border: 1px solid #ddd; font-weight: 700; text-align: center;">Type</th>
                    <th style="padding: 10px; border: 1px solid #ddd; font-weight: 700; text-align: center;">Start Date</th>
                    <th style="padding: 10px; border: 1px solid #ddd; font-weight: 700; text-align: center;">End Date</th>
                    <th style="padding: 10px; border: 1px solid #ddd; font-weight: 700; text-align: center;">Status</th>
                    <th style="padding: 10px; border: 1px solid #ddd; font-weight: 700; text-align: center;">Rejection Reason</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaves as $leave)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">{{ $leave->leave_type }}</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                        @php $statusLower = strtolower(trim($leave->status)); @endphp
                        @if ($leave->isOverdue())
                            <span class="request-badge request-badge--overdue">Overdue</span>
                        @elseif ($statusLower === 'withdrawn')
                            <span class="request-badge request-badge--withdrawn">Withdrawn</span>
                        @else
                            <span class="request-badge request-badge--{{ $statusLower }}">{{ ucfirst($leave->status) }}</span>
                        @endif
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center; color: #911111; font-style: italic;">
                        @if(strtolower($leave->status) === 'rejected' && $leave->rejection_reason)
                            {{ $leave->rejection_reason }}
                        @else
                            &mdash;
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
