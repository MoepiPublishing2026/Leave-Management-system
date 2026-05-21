@extends('layouts.manager')

@section('title', 'Leave Requests - ' . ucfirst($status))

@section('content')
<style>
.manager-container { font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif; max-width: 1500px; margin: 20px auto 40px; background: white; border-radius: 20px; padding: 20px 30px; box-shadow: 0 6px 20px rgba(163, 71, 71, 0.08);}
h1 { font-weight: 700; font-size: 36px; color: #2c3e50; border-left: 6px solid #138056ff; padding-left: 15px; margin-bottom: 24px; letter-spacing: 0.04em;}
.filter-links { margin-bottom: 16px; }
.filter-links a { margin-right: 12px; text-decoration: none; color: #138056ff; font-weight: 800; }
.filter-links a.active { color: #0a382b; text-decoration: underline; }
.search-row { display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 14px; }
.search-row input { border: 2px solid #138056ff; border-radius: 6px; padding: 8px 14px; font-size: 1rem; width: 260px; }
.search-row button { padding: 9px 18px; font-weight: 800; font-size: 1rem; border-radius: 6px; cursor: pointer; border: none; background: #0d6efd; color: white; }
.search-row a { padding: 9px 14px; font-weight: 800; font-size: 1rem; border-radius: 6px; cursor: pointer; border: 1px solid #cbd5e1; background: #f8f9fa; color: #0f172a; text-decoration: none; display: inline-flex; align-items: center; }
table {width: 100%; border-collapse: collapse; font-size: 1rem;}
th, td {border: 1px solid #ddd; padding: 12px 15px; text-align: left;}
th {background-color: #f4f4f4; font-weight: 700; color: #2c3e50;}
tr:hover {background-color: #f9f9f9;}
.row-click { cursor: pointer; }
.overdue-row { background-color: #fff1f2; }
.badge { display: inline-block; padding: 6px 12px; border-radius: 999px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; }
.badge-pending { background: #fef3c7; color: #92400e; border: 1px solid #fbbf24; }
.badge-approved { background: #dcfce7; color: #166534; border: 1px solid #22c55e; }
.badge-rejected { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
.badge-withdrawn { background: #ffedd5; color: #7c2d12; border: 1px solid #fb923c; }
.badge-overdue { background: #fce7f3; color: #9d174d; border: 1px solid #f472b6; margin-left: 8px; }
.btn-approve, .btn-reject {padding: 8px 18px; font-weight: 600; border: none; border-radius: 12px; cursor: pointer; transition: background-color 0.3s ease;}
.btn-approve {background-color: #10b981; color: white;}
.btn-approve:hover {background-color: #0e9b6e;}
.btn-reject {background-color: #ef4444; color: white; margin-left: 10px;}
.btn-reject:hover {background-color: #d03838;}
.btn-escalate {padding: 8px 18px; font-weight: 700; border: none; border-radius: 12px; cursor: pointer; background-color: #f472b6; color: white;}
.btn-escalate:hover {background-color: #ec4899;}
a.view-reason {font-weight: 700; color: #ef4444; cursor: pointer; text-decoration: underline;}
a.view-reason:hover {color: #c53030;}
.pagination { margin-bottom: 0; }
.pagination .page-link { padding: 6px 10px; font-weight: 700; font-size: 0.9rem; }
.request-details-popup { border: 2px solid #138056ff !important; border-radius: 16px !important; }
.btn-delete { padding: 6px 14px; font-weight: 800; font-size: 0.85rem; border: none; border-radius: 8px; cursor: pointer; background: #7f1d1d; color: #fff; }
.btn-delete:hover { background: #991b1b; }
.actions-stack { display: flex; flex-direction: column; gap: 8px; align-items: flex-start; }
</style>

<div class="manager-container">
    <h1>Leave Requests - {{ ucfirst($status) }}</h1>
    <form method="GET" action="{{ route('manager.requests.index') }}" class="search-row">
        <input type="hidden" name="status" value="{{ $status }}" />
        @if(!empty($monthFilter))
            <input type="hidden" name="month" value="{{ $monthFilter }}" />
        @endif
        <input type="text" name="search" placeholder="Search (name, type, status, dates, comment...)" value="{{ $search ?? '' }}" />
        <button type="submit">Search</button>
        <a href="{{ route('manager.requests.index', ['status' => $status, 'month' => $monthFilter]) }}">Clear</a>
    </form>
    <div class="filter-links">
        <a href="{{ route('manager.requests.index', ['status' => 'all', 'month' => $monthFilter]) }}" class="{{ $status === 'all' ? 'active' : '' }}">All</a>
        <a href="{{ route('manager.requests.index', ['status' => 'pending', 'month' => $monthFilter]) }}" class="{{ $status === 'pending' ? 'active' : '' }}">Pending</a>
        <a href="{{ route('manager.requests.index', ['status' => 'approved', 'month' => $monthFilter]) }}" class="{{ $status === 'approved' ? 'active' : '' }}">Approved</a>
        <a href="{{ route('manager.requests.index', ['status' => 'rejected', 'month' => $monthFilter]) }}" class="{{ $status === 'rejected' ? 'active' : '' }}">Rejected</a>
        <a href="{{ route('manager.requests.index', ['status' => 'overdue', 'month' => $monthFilter]) }}" class="{{ $status === 'overdue' ? 'active' : '' }}">Overdue</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>Requested By</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th style="min-width: 140px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($requests as $index => $req)
            @php
                $files = [];
                if (!empty($req->file_uploads)) {
                    $decoded = json_decode($req->file_uploads, true);
                    if (is_array($decoded)) {
                        $files = $decoded;
                    } else {
                        $files = [$req->file_uploads];
                    }
                }

                $employeeFirst = $req->user->first_name ?? '';
                $employeeLast = $req->user->last_name ?? '';
                $employeeFull = trim($employeeFirst . ' ' . $employeeLast);
                if ($employeeFull === '') {
                    $employeeFull = 'User';
                }

                $fileLinks = [];
                foreach ($files as $i => $file) {
                    $fileLinks[] = [
                        'label' => 'File ' . ($i + 1),
                        'url' => route('leave.attachments.show', ['leaveRequest' => $req->id, 'attachment' => $i + 1]),
                    ];
                }

                $details = [
                    'id' => $req->id,
                    'employee' => $employeeFull,
                    'email' => $req->user->email ?? '',
                    'department' => $req->user->department ?? '',
                    'designation' => $req->user->designation ?? '',
                    'type' => (string) ($req->leave_type ?? ''),
                    'start' => $req->start_date ? \Carbon\Carbon::parse($req->start_date)->format('d M Y') : '',
                    'end' => $req->end_date ? \Carbon\Carbon::parse($req->end_date)->format('d M Y') : '',
                    'status' => strtolower(trim((string) ($req->status ?? ''))),
                    'isOverdue' => $req->isOverdue(),
                    'comment' => (string) ($req->important_comment ?? ''),
                    'files' => $fileLinks,
                    'messagesUrl' => route('messages.leave', $req),
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
                <td>
                    @php $rowStatus = strtolower(trim($req->status)); @endphp
                    @if ($req->isOverdue())
                        <span class="badge badge-overdue">Overdue</span>
                    @else
                        <span class="badge badge-{{ $rowStatus === 'withdrawn' ? 'withdrawn' : $rowStatus }}">{{ ucfirst($rowStatus) }}</span>
                    @endif
                </td>
                <td class="actions-stack" onclick="event.stopPropagation();">
                @if(strtolower($req->status) === 'pending')
                    @if ($req->isOverdue())
                        @if ($req->overdue_escalated_at)
                            <span style="font-weight:800; color:#9d174d;" title="{{ $req->overdue_escalation_reason ?? '' }}">Reason Sent</span>
                        @else
                            <button class="btn-escalate" onclick="openLateReasonPopup({{ $req->id }})">Submit Reason</button>
                        @endif
                    @else
                        <form action="{{ route('manager.requests.approve', $req) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-approve">Approve</button>
                        </form>
                        <button class="btn-reject" onclick="openRejectPopup({{ $req->id }})">Reject</button>
                    @endif
                @elseif(strtolower($req->status) === 'rejected')
                    <a href="{{ route('manager.requests.show_reason', $req) }}" class="view-reason">View Reason</a>
                @else
                    &mdash;
                @endif
                <div style="margin-top:10px;">
                    <a href="{{ route('messages.leave', $req) }}" style="font-weight:800; color:#0a1347; text-decoration:underline;">Messages</a>
                </div>
                <form class="delete-request-form" method="POST" action="{{ route('manager.requests.destroy', $req) }}" style="margin:0;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn-delete delete-request-btn" data-request-id="{{ $req->id }}">Delete</button>
                </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align:center; font-style:italic; color:#555;">No leave requests found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top: 20px;">
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

<script>
const rejectUrlTemplate = @json(url('/manager/requests/__ID__/reject'));
const lateReasonUrlTemplate = @json(url('/manager/requests/__ID__/escalate'));

function escapeHtml(text) {
    return String(text || '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    }[c]));
}

function openDetails(details) {
    const isOverdue = !!details.isOverdue;
    const statusLabel = isOverdue ? 'Overdue' : (details.status ? (details.status.charAt(0).toUpperCase() + details.status.slice(1)) : '');
    const employeeLine = [details.employee, details.designation, details.department].filter(Boolean).join(' • ');
    const files = Array.isArray(details.files) ? details.files : [];
    const filesHtml = files.length
        ? `<ul style="margin:8px 0 0 18px;">${files.map(f => `<li><a href="${escapeHtml(f.url)}" target="_blank" rel="noopener noreferrer">${escapeHtml(f.label)}</a></li>`).join('')}</ul>`
        : `<span>None</span>`;

    const commentHtml = details.comment ? `<div style="white-space: pre-wrap; margin-top: 6px;">${escapeHtml(details.comment)}</div>` : `<span>N/A</span>`;
    const lateReasonHtml = details.lateReasonSent ? `<div style="white-space: pre-wrap; margin-top: 6px;">${escapeHtml(details.lateReason || '')}</div>` : `<span>N/A</span>`;
    const rejectionReasonHtml = details.rejectionReason ? `<div style="white-space: pre-wrap; margin-top: 6px;">${escapeHtml(details.rejectionReason)}</div>` : `<span>N/A</span>`;

    Swal.fire({
        title: `Request #${escapeHtml(details.id)}`,
        width: 780,
        customClass: { popup: 'request-details-popup' },
        html: `
            <div style="text-align:left; line-height:1.45;">
                <div style="font-weight:800; margin-bottom:6px;">${escapeHtml(employeeLine)}</div>
                <div style="color:#475569; margin-bottom:12px;">${escapeHtml(details.email || 'N/A')}</div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom:12px;">
                    <div><div style="font-weight:800;">Leave Type</div><div>${escapeHtml(details.type || 'N/A')}</div></div>
                    <div><div style="font-weight:800;">Status</div><div>${escapeHtml(statusLabel || 'N/A')}</div></div>
                    <div><div style="font-weight:800;">Start</div><div>${escapeHtml(details.start || 'N/A')}</div></div>
                    <div><div style="font-weight:800;">End</div><div>${escapeHtml(details.end || 'N/A')}</div></div>
                </div>

                <div style="margin-bottom:10px;">
                    <div style="font-weight:800;">Comment</div>
                    ${commentHtml}
                </div>

                <div style="margin-bottom:10px;">
                    <div style="font-weight:800;">Files</div>
                    ${filesHtml}
                </div>

                <div style="margin-bottom:10px;">
                    <div style="font-weight:800;">Late Response Reason</div>
                    ${lateReasonHtml}
                </div>

                <div style="margin-bottom:4px;">
                    <div style="font-weight:800;">Rejection Reason</div>
                    ${rejectionReasonHtml}
                </div>

                <div style="margin-top:12px;">
                    <a href="${escapeHtml(details.messagesUrl)}" style="font-weight:800; color:#0a1347; text-decoration:underline;">Open Messages</a>
                </div>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: 'Close'
    });
}

document.querySelectorAll('.delete-request-btn').forEach((btn) => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const form = btn.closest('form');
        if (!form) return;
        const requestId = btn.getAttribute('data-request-id') || '';
        Swal.fire({
            title: 'Delete leave request?',
            html: `Request <strong>#${escapeHtml(requestId)}</strong> will be permanently removed, including messages and attachments.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#7f1d1d',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it',
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});

document.addEventListener('click', (e) => {
    const interactive = e.target.closest('a,button,form,input,textarea,select,label');
    if (interactive) return;
    const row = e.target.closest('tr[data-details]');
    if (!row) return;
    const raw = row.getAttribute('data-details');
    if (!raw) return;
    try {
        openDetails(JSON.parse(raw));
    } catch (err) {
    }
});

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
