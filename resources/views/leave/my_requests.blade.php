@extends('layouts.app')

@section('title', 'My Leave Requests')

@section('content')
@include('partials.requests-page-styles')

<div class="requests-page">
    <h1>My Leave Requests</h1>
    <p class="requests-page-lead">
        Showing <strong>{{ ucfirst($status) }}</strong>
        @if ($leaveRequests->total() > 0)
            · {{ $leaveRequests->total() }} {{ Str::plural('request', $leaveRequests->total()) }}
        @endif
    </p>

    @include('partials.requests-toolbar', [
        'formRoute' => 'leave.myRequests',
        'filterRoute' => 'leave.myRequests',
        'status' => $status,
        'search' => $search ?? '',
        'showNewRequest' => true,
        'searchPlaceholder' => 'Search type, status, dates, comment...',
        'statusFilters' => [
            'all' => 'All',
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'withdrawn' => 'Withdrawn',
            'overdue' => 'Overdue',
        ],
    ])

    <div class="requests-table-wrap">
        <table class="requests-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>Start</th>
                <th>End</th>
                <th>Submitted</th>
                <th>Status</th>
                <th style="min-width: 140px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($leaveRequests as $index => $req)
                @php
                    $files = [];
                    if (!empty($req->file_uploads)) {
                        $decoded = json_decode($req->file_uploads, true);
                        $files = is_array($decoded) ? $decoded : [$req->file_uploads];
                    }

                    $fileLinks = [];
                    foreach ($files as $i => $file) {
                        $fileLinks[] = [
                            'label' => 'File ' . ($i + 1),
                            'url' => route('leave.attachments.show', ['leaveRequest' => $req->id, 'attachment' => $i + 1]),
                        ];
                    }

                    $handledName = trim(($req->handledBy->first_name ?? '') . ' ' . ($req->handledBy->last_name ?? ''));
                    $lateReasonName = trim(($req->overdueEscalatedBy->first_name ?? '') . ' ' . ($req->overdueEscalatedBy->last_name ?? ''));
                    $rowStatus = strtolower(trim((string) ($req->status ?? '')));
                    $canWithdraw = $rowStatus === 'pending';
                    $currentYear = (int) now()->year;
                    $requestYear = $req->start_date ? (int) \Carbon\Carbon::parse($req->start_date)->year : $currentYear;
                    $isPreviousYear = $requestYear === ($currentYear - 1);
                    $canDelete = $isPreviousYear;

                    $details = [
                        'id' => $req->id,
                        'type' => (string) ($req->leave_type ?? ''),
                        'start' => $req->start_date ? \Carbon\Carbon::parse($req->start_date)->format('d M Y') : '',
                        'end' => $req->end_date ? \Carbon\Carbon::parse($req->end_date)->format('d M Y') : '',
                        'days' => $req->days ?? '',
                        'status' => $rowStatus,
                        'isOverdue' => $req->isOverdue(),
                        'comment' => (string) ($req->important_comment ?? ''),
                        'files' => $fileLinks,
                        'lateReasonSent' => (bool) $req->overdue_escalated_at,
                        'lateReason' => (string) ($req->overdue_escalation_reason ?? ''),
                        'lateReasonBy' => $lateReasonName,
                        'handledBy' => $handledName,
                        'handledAt' => $req->handled_at ? \Carbon\Carbon::parse($req->handled_at)->format('d M Y H:i') : '',
                        'handledAction' => (string) ($req->handled_action ?? ''),
                        'rejectionReason' => (string) ($req->rejection_reason ?? ''),
                    ];
                @endphp
                <tr class="row-click {{ $req->isOverdue() ? 'overdue-row' : '' }}" data-details='@json($details)'>
                    <td>{{ $leaveRequests->firstItem() + $index }}</td>
                    <td>{{ $req->leave_type }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->start_date)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</td>
                    <td>{{ $req->created_at ? \Carbon\Carbon::parse($req->created_at)->format('d M Y H:i') : '—' }}</td>
                    <td>
                        @if ($req->isOverdue())
                            <span class="request-badge request-badge--overdue">Overdue</span>
                        @else
                            <span class="request-badge request-badge--{{ $rowStatus === 'withdrawn' ? 'withdrawn' : $rowStatus }}">{{ ucfirst($rowStatus) }}</span>
                        @endif
                    </td>
                    <td class="request-actions-cell" onclick="event.stopPropagation();">
                        <div class="request-actions">
                            @if ($canWithdraw)
                                <form class="withdraw-form" method="POST" action="{{ route('leave.withdraw', $req->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="button" class="request-btn request-btn--withdraw withdraw-request-btn">Withdraw</button>
                                </form>
                            @endif
                            @if ($canDelete)
                                <form class="delete-request-form" method="POST" action="{{ route('leave.destroy', $req) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="request-btn request-btn--delete delete-request-btn" data-request-id="{{ $req->id }}">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="requests-empty">No leave requests found for this filter.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <p class="requests-hint">Click any row to view full request details.</p>

    <div class="requests-pagination">
        {{ $leaveRequests->links('pagination::bootstrap-5') }}
    </div>
</div>

@include('partials.requests-page-scripts', [
    'showEmployee' => false,
    'showDays' => true,
    'showHandledMeta' => true,
    'enableWithdraw' => true,
    'deleteWarning' => 'It will be permanently removed, including any attachments.',
])
@endsection
