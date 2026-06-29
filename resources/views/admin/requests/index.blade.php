@extends('layouts.admin')

@section('title', 'All Leave Requests')

@section('content')
@include('partials.requests-page-styles')

@php
    use App\Support\LeaveAttachmentLinks;
@endphp

<div class="requests-page">
    <h1>Leave Requests</h1>
    <p class="requests-page-lead">
        Showing <strong>{{ ucfirst($status) }}</strong>
        @if ($requests->total() > 0)
            · {{ $requests->total() }} {{ Str::plural('request', $requests->total()) }}
        @endif
    </p>

    @include('partials.requests-toolbar', [
        'formRoute' => 'admin.requests',
        'filterRoute' => 'admin.requests',
        'status' => $status,
        'search' => $search ?? '',
        'monthFilter' => $monthFilter ?? null,
        'employeeFilter' => $employeeFilter ?? null,
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
                <th style="min-width: 140px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($requests as $index => $req)
                @php
                    $fileLinks = LeaveAttachmentLinks::forRequest($req);

                    $employeeFirst = $req->user->first_name ?? '';
                    $employeeLast = $req->user->last_name ?? '';
                    $employeeFull = trim($employeeFirst . ' ' . $employeeLast) ?: 'User';

                    $handledName = trim(($req->handledBy->first_name ?? '') . ' ' . ($req->handledBy->last_name ?? ''));
                    $lateReasonName = trim(($req->overdueEscalatedBy->first_name ?? '') . ' ' . ($req->overdueEscalatedBy->last_name ?? ''));
                    $rowStatus = strtolower(trim((string) ($req->status ?? '')));
                    $currentYear = (int) now()->year;
                    $requestYear = $req->start_date ? (int) \Carbon\Carbon::parse($req->start_date)->year : $currentYear;
                    $isPreviousYear = $requestYear === ($currentYear - 1);
                    $canDelete = $isPreviousYear;

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
                        'messagesUrl' => route('messages.leave', $req),
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
                            <a href="{{ route('messages.leave', $req) }}" class="request-link">Messages</a>
                            @if($canDelete)
                                <form class="delete-request-form" method="POST" action="{{ route('admin.requests.destroy', $req) }}">
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

@include('partials.requests-page-scripts', [
    'showEmployee' => true,
    'showDays' => false,
    'showHandledMeta' => true,
    'deleteWarning' => 'It will be permanently removed, including messages and attachments.',
])
@endsection
