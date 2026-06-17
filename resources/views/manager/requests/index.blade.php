@extends('layouts.manager')

@section('title', 'Leave Requests - ' . ucfirst($status))

@section('content')
@include('partials.requests-page-styles')

<div class="requests-page">
    <h1>Leave Requests</h1>
    <p class="requests-page-lead">
        Showing <strong>{{ ucfirst($status) }}</strong>
        @if ($requests->total() > 0)
            · {{ $requests->total() }} {{ Str::plural('request', $requests->total()) }}
        @endif
    </p>

    @include('partials.requests-toolbar', [
        'formRoute' => 'manager.requests.index',
        'filterRoute' => 'manager.requests.index',
        'status' => $status,
        'search' => $search ?? '',
        'monthFilter' => $monthFilter ?? null,
        'searchPlaceholder' => 'Search name, type, status, dates...',
        'statusFilters' => [
            'all' => 'All',
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'overdue' => 'Overdue',
        ],
    ])

    <div class="requests-table-wrap">
        <table class="requests-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>Requested By</th>
                <th>Start</th>
                <th>End</th>
                <th>Submitted</th>
                <th>Status</th>
                <th style="min-width: 180px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($requests as $index => $req)
                @php
                    $files = [];
                    if (!empty($req->file_uploads)) {
                        $decoded = json_decode($req->file_uploads, true);
                        $files = is_array($decoded) ? $decoded : [$req->file_uploads];
                    }

                    $employeeFirst = $req->user->first_name ?? '';
                    $employeeLast = $req->user->last_name ?? '';
                    $employeeFull = trim($employeeFirst . ' ' . $employeeLast) ?: 'User';

                    $fileLinks = [];
                    foreach ($files as $i => $file) {
                        $fileLinks[] = [
                            'label' => 'File ' . ($i + 1),
                            'url' => route('leave.attachments.show', ['leaveRequest' => $req->id, 'attachment' => $i + 1]),
                        ];
                    }

                    $rowStatus = strtolower(trim((string) ($req->status ?? '')));
                    $currentYear = (int) now()->year;
                    $requestYear = $req->start_date ? (int) \Carbon\Carbon::parse($req->start_date)->year : $currentYear;
                    $isPreviousYear = $requestYear === ($currentYear - 1);
                    $canDelete = $isPreviousYear;

                    $canMessage = $req->managerCanOpenChat(auth()->user());

                    $details = [
                        'id' => $req->id,
                        'employee' => $employeeFull,
                        'email' => $req->user->email ?? '',
                        'department' => $req->user->department ?? '',
                        'designation' => $req->user->designation ?? '',
                        'type' => (string) ($req->leave_type ?? ''),
                        'start' => $req->start_date ? \Carbon\Carbon::parse($req->start_date)->format('d M Y') : '',
                        'end' => $req->end_date ? \Carbon\Carbon::parse($req->end_date)->format('d M Y') : '',
                        'status' => $rowStatus,
                        'isOverdue' => $req->isOverdue(),
                        'comment' => (string) ($req->important_comment ?? ''),
                        'files' => $fileLinks,
                        'messagesUrl' => $canMessage ? route('messages.leave', $req) : null,
                        'lateReasonSent' => (bool) $req->overdue_escalated_at,
                        'lateReason' => (string) ($req->overdue_escalation_reason ?? ''),
                        'rejectionReason' => (string) ($req->rejection_reason ?? ''),
                    ];
                @endphp
                <tr class="row-click {{ $req->isOverdue() ? 'overdue-row' : '' }}" data-details='@json($details)'>
                    <td>{{ $requests->firstItem() + $index }}</td>
                    <td>{{ $req->leave_type }}</td>
                    <td>{{ $employeeFull }}</td>
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
                            @if ($rowStatus === 'pending')
                                @if ($req->isOverdue())
                                    @if ($req->overdue_escalated_at)
                                        <span class="request-actions-note" title="{{ $req->overdue_escalation_reason ?? '' }}">Reason Sent</span>
                                    @else
                                        <button type="button" class="request-btn request-btn--escalate" onclick="openLateReasonPopup({{ $req->id }})">Submit Reason</button>
                                    @endif
                                @else
                                    <div class="request-actions-inline">
                                        <form action="{{ route('manager.requests.approve', $req) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="request-btn request-btn--approve">Approve</button>
                                        </form>
                                        <button type="button" class="request-btn request-btn--reject" onclick="openRejectPopup({{ $req->id }})">Reject</button>
                                    </div>
                                @endif
                            @elseif ($rowStatus === 'rejected')
                                <a href="{{ route('manager.requests.show_reason', $req) }}" class="request-link">View Reason</a>
                            @endif
                            @if($canMessage)
                                <a href="{{ route('messages.leave', $req) }}" class="request-link">Messages</a>
                            @endif
                            @if($canDelete)
                                <form class="delete-request-form" method="POST" action="{{ route('manager.requests.destroy', $req) }}">
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
                    <td colspan="8" class="requests-empty">No leave requests found for this filter.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <p class="requests-hint">Click any row to view full request details.</p>

    <div class="requests-pagination">
        {{ $requests->links('pagination::bootstrap-5') }}
    </div>
</div>

<form id="reject-hidden-form" method="POST" style="display:none;">
    @csrf
    @method('PATCH')
    <input type="hidden" name="rejection_reason" id="reject-hidden-reason" />
</form>

<form id="late-reason-hidden-form" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="overdue_escalation_reason" id="late-reason-hidden-text" />
</form>

@include('partials.requests-page-scripts', [
    'showEmployee' => true,
    'showDays' => false,
    'showHandledMeta' => false,
    'deleteWarning' => 'It will be permanently removed, including messages and attachments.',
])

<script>
const rejectUrlTemplate = @json(url('/manager/requests/__ID__/reject'));
const lateReasonUrlTemplate = @json(url('/manager/requests/__ID__/escalate'));

function openRejectPopup(requestId) {
    Swal.fire({
        icon: 'warning',
        title: 'Reject Request',
        input: 'textarea',
        inputLabel: 'Rejection reason',
        inputPlaceholder: 'Type the rejection reason...',
        inputAttributes: { 'aria-label': 'Rejection reason' },
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#dc2626',
        cancelButtonText: 'Cancel',
        preConfirm: (value) => {
            const v = (value || '').trim();
            if (!v) {
                Swal.showValidationMessage('Rejection reason is required.');
                return false;
            }
            if (v.length > 500) {
                Swal.showValidationMessage('Rejection reason must be 500 characters or less.');
                return false;
            }
            return v;
        }
    }).then((result) => {
        if (!result.isConfirmed) return;
        const form = document.getElementById('reject-hidden-form');
        form.action = rejectUrlTemplate.replace('__ID__', requestId);
        document.getElementById('reject-hidden-reason').value = result.value;
        form.submit();
    });
}

function openLateReasonPopup(requestId) {
    Swal.fire({
        icon: 'info',
        title: 'Late Response Reason',
        input: 'textarea',
        inputLabel: 'Why was this request left unattended?',
        inputPlaceholder: 'Type the reason...',
        inputAttributes: { 'aria-label': 'Late response reason' },
        showCancelButton: true,
        confirmButtonText: 'Send to Admin',
        cancelButtonText: 'Cancel',
        preConfirm: (value) => {
            const v = (value || '').trim();
            if (!v) {
                Swal.showValidationMessage('Reason is required.');
                return false;
            }
            if (v.length > 1000) {
                Swal.showValidationMessage('Reason must be 1000 characters or less.');
                return false;
            }
            return v;
        }
    }).then((result) => {
        if (!result.isConfirmed) return;
        const form = document.getElementById('late-reason-hidden-form');
        form.action = lateReasonUrlTemplate.replace('__ID__', requestId);
        document.getElementById('late-reason-hidden-text').value = result.value;
        form.submit();
    });
}
</script>
@endsection
