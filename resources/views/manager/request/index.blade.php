@extends('layouts.manager')

@section('title', 'All Leave Requests')

@php
    use App\Support\LeaveAttachmentLinks;
@endphp

@section('content')
<style>
    body {
        font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
        background-color: white;
    }
    .page-container {
        background: white;
        border-radius: 20px;
     box-shadow: 0 0 10px #a7bd8dff;
        padding: 30px;
        margin: 20px auto;
        max-width: 1300px;
    }
    h1 {
        font-weight: 700;
        font-size: 36px;
        color: #2c3e50;
        margin: 0 0 24px 0;
        letter-spacing: 0.04em;
    }
    .filter-links a {
        margin-right: 12px;
        text-decoration: none;
        color: #138056ff;
        font-weight: bold;
    }
    .filter-links a.active {
        color: #0a382b;
        text-decoration: underline;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    th, td {
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        vertical-align: middle;
    }
    th {
        background-color: #b9c7c2ff;
        color: black;
        font-weight: bold;
    }
    tr:hover {
        background-color: #f2f2f2;
    }
</style>

<h1>All Leave Requests</h1>
<form method="GET" action="{{ route('manager.request') }}" class="mb-3 d-flex align-items-center" style="max-width: 600px; justify-content: flex-end; gap: 10px; margin-left: auto;">
    <input
        type="text"
        name="search"
        placeholder="Search"
        value="{{ request('search') }}"
        class="form-control"
        style="border: 2px solid #138056ff; border-radius: 6px; padding: 8px 14px; font-size: 1rem; width: 250px;"
    />
    <button
        type="submit"
        class="btn btn-primary"
        style="padding: 9px 24px; font-weight: 700; font-size: 1rem; border-radius: 6px; cursor: pointer;"
    >
        Search
    </button>
</form>

<div class="page-container">
    <!-- Filters -->
    <div class="filter-links mb-3">
        <a href="{{ route('manager.request') }}" class="{{ !$status ? 'active' : '' }}">All</a>
        <a href="{{ route('manager.request', ['status' => 'Pending']) }}" class="{{ $status == 'Pending' ? 'active' : '' }}">Pending</a>
        <a href="{{ route('manager.request', ['status' => 'Approved']) }}" class="{{ $status == 'Approved' ? 'active' : '' }}">Approved</a>
        <a href="{{ route('manager.request', ['status' => 'Rejected']) }}" class="{{ $status == 'Rejected' ? 'active' : '' }}">Rejected</a>
        <!-- Withdrawn link REMOVED -->
    </div>
   
    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Important Comment</th>
                <th>File</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $index => $request)
                <tr>
                    <td>{{ $requests->firstItem() + $index }}</td>
                    <td>{{ $request->user->first_name ?? 'N/A' }}</td>
                    <td>{{ ucfirst($request->leave_type) }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->start_date)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->end_date)->format('d M Y') }}</td>
                    <td>
                        <span class="request-badge request-badge--{{ strtolower($request->status) }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                    <td>{{ $request->important_comment ?? '-' }}</td>
                    <td>
                        @php $fileLinks = LeaveAttachmentLinks::forRequest($request); @endphp
                        @if(count($fileLinks) > 0)
                            @foreach($fileLinks as $fileLink)
                                <button
                                    type="button"
                                    class="attachment-preview-btn"
                                    data-preview-url="{{ $fileLink['url'] }}"
                                    data-download-url="{{ $fileLink['downloadUrl'] }}"
                                    data-extension="{{ $fileLink['extension'] }}"
                                    data-label="{{ $fileLink['name'] }}"
                                    data-previewable="{{ $fileLink['previewable'] ? '1' : '0' }}"
                                    style="margin-right:6px;padding:4px 10px;border:1px solid #86efac;background:#ecfdf5;border-radius:8px;font-weight:700;color:#047857;cursor:pointer;"
                                >Preview {{ $fileLink['label'] }}</button>
                            @endforeach
                        @else
                            &mdash;
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:30px;">
                        No leave requests found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $requests->links('pagination::bootstrap-4') }}
    </div>
</div>

@include('partials.requests-page-scripts', [
    'showEmployee' => false,
    'showDays' => false,
    'showHandledMeta' => false,
    'enableWithdraw' => false,
    'enableProofUpload' => false,
])
@endsection
