@extends('layouts.app')

@section('title', 'My Leave Requests')

@section('content')
<style>
    .admin-container {
        font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
        max-width: 1500px;
        margin: 20px auto 40px;
        background: white;
        border-radius: 20px;
        padding: 20px 30px;
        box-shadow: 0 6px 20px rgba(163, 71, 71, 0.08);
    }
    h1 {
        font-weight: 700;
        font-size: 36px;
        color: #2c3e50;
        border-left: 6px solid #138056ff;
        padding-left: 15px;
        margin-bottom: 16px;
        letter-spacing: 0.04em;
    }
    .filter-links { margin-bottom: 14px; }
    .filter-links a { margin-right: 12px; text-decoration: none; color: #138056ff; font-weight: 800; }
    .filter-links a.active { color: #0a382b; text-decoration: underline; }
    .search-row { display: flex; justify-content: flex-end; align-items: center; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
    .search-row input { border: 2px solid #138056ff; border-radius: 6px; padding: 8px 14px; font-size: 1rem; width: 260px; }
    .search-row button { padding: 9px 18px; font-weight: 800; font-size: 1rem; border-radius: 6px; cursor: pointer; border: none; background: #0d6efd; color: white; }
    .search-row a.btn-clear { padding: 9px 14px; font-weight: 800; font-size: 1rem; border-radius: 6px; cursor: pointer; border: 1px solid #cbd5e1; background: #f8f9fa; color: #0f172a; text-decoration: none; display: inline-flex; align-items: center; }
    .search-row a.btn-new-request { margin-right: auto; padding: 9px 18px; font-weight: 800; font-size: 1rem; border-radius: 6px; cursor: pointer; border: none; background: #138056ff; color: white; text-decoration: none; display: inline-flex; align-items: center; }
    .search-row a.btn-new-request:hover { background: #0a382b; }
    table { width: 100%; border-collapse: collapse; font-size: 1rem; }
    th, td { border: 1px solid #ddd; padding: 12px 15px; text-align: left; }
    th { background-color: #f4f4f4; font-weight: 700; color: #2c3e50; }
    tr:hover { background-color: #f9f9f9; }
    .row-click { cursor: pointer; }
    .overdue-row { background-color: #fff1f2; }
    .badge { display: inline-block; padding: 6px 12px; border-radius: 999px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; }
    .badge-pending { background: #fde68a; color: #92400e; border: 1px solid #f59e0b; }
    .badge-approved { background: #bbf7d0; color: #065f46; border: 1px solid #22c55e; }
    .badge-rejected { background: #fecaca; color: #7f1d1d; border: 1px solid #ef4444; }
    .badge-withdrawn { background: #fef3c7; color: #92400e; border: 1px solid #9b5b03; }
    .badge-overdue { background: #fce7f3; color: #9d174d; border: 1px solid #f472b6; }
    .pagination { margin-bottom: 0; }
    .pagination .page-link { padding: 6px 10px; font-weight: 700; font-size: 0.9rem; }
    .request-details-popup { border: 2px solid #138056ff !important; border-radius: 16px !important; }
    .btn-delete { padding: 6px 14px; font-weight: 800; font-size: 0.85rem; border: none; border-radius: 8px; cursor: pointer; background: #7f1d1d; color: #fff; }
    .btn-delete:hover { background: #991b1b; }
    .btn-withdraw { padding: 6px 14px; font-weight: 800; font-size: 0.85rem; border: none; border-radius: 8px; cursor: pointer; background: #b45309; color: #fff; }
    .btn-withdraw:hover { background: #92400e; }
    .actions-stack { display: flex; flex-direction: column; gap: 6px; align-items: flex-start; }
</style>

<div class="admin-container">
    <h1>My Leave Requests - {{ ucfirst($status) }}</h1>
    <form method="GET" action="{{ route('leave.myRequests') }}" class="search-row">
        <a href="{{ route('apply') }}" class="btn-new-request">+ New Request</a>
        <input type="hidden" name="status" value="{{ $status }}" />
        <input type="text" name="search" placeholder="Search (type, status, dates, comment...)" value="{{ $search ?? '' }}" />
        <button type="submit">Search</button>
        <a href="{{ route('leave.myRequests', ['status' => $status]) }}" class="btn-clear">Clear</a>
    </form>

    <div class="filter-links">
        <a href="{{ route('leave.myRequests', ['status' => 'all']) }}" class="{{ $status === 'all' ? 'active' : '' }}">All</a>
        <a href="{{ route('leave.myRequests', ['status' => 'pending']) }}" class="{{ $status === 'pending' ? 'active' : '' }}">Pending</a>
        <a href="{{ route('leave.myRequests', ['status' => 'approved']) }}" class="{{ $status === 'approved' ? 'active' : '' }}">Approved</a>
        <a href="{{ route('leave.myRequests', ['status' => 'rejected']) }}" class="{{ $status === 'rejected' ? 'active' : '' }}">Rejected</a>
        <a href="{{ route('leave.myRequests', ['status' => 'withdrawn']) }}" class="{{ $status === 'withdrawn' ? 'active' : '' }}">Withdrawn</a>
        <a href="{{ route('leave.myRequests', ['status' => 'overdue']) }}" class="{{ $status === 'overdue' ? 'active' : '' }}">Overdue</a>
    </div>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Type</th>
            <th>Start</th>
            <th>End</th>
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
                    if (is_array($decoded)) {
                        $files = $decoded;
                    } else {
                        $files = [$req->file_uploads];
                    }
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
                $canDelete = in_array($rowStatus, ['pending', 'withdrawn'], true);

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
                <td>
                    @if ($req->isOverdue())
                        <span class="badge badge-overdue">Overdue</span>
                    @else
                        <span class="badge badge-{{ $rowStatus === 'withdrawn' ? 'withdrawn' : $rowStatus }}">{{ ucfirst($rowStatus) }}</span>
                    @endif
                </td>
                <td class="actions-stack" onclick="event.stopPropagation();">
                    @if ($canWithdraw)
                        <form class="withdraw-form" method="POST" action="{{ route('leave.withdraw', $req->id) }}" style="margin:0;">
                            @csrf
                            @method('PATCH')
                            <button type="button" class="btn-withdraw withdraw-request-btn">Withdraw</button>
                        </form>
                    @endif
                    @if ($canDelete)
                        <form class="delete-request-form" method="POST" action="{{ route('leave.destroy', $req) }}" style="margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn-delete delete-request-btn" data-request-id="{{ $req->id }}">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center; font-style:italic; color:#555;">No leave requests found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $leaveRequests->links('pagination::bootstrap-5') }}
    </div>
</div>

<script>
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
    const files = Array.isArray(details.files) ? details.files : [];
    const filesHtml = files.length
        ? `<ul style="margin:8px 0 0 18px;">${files.map(f => `<li><a href="${escapeHtml(f.url)}" target="_blank" rel="noopener noreferrer">${escapeHtml(f.label)}</a></li>`).join('')}</ul>`
        : `<span>None</span>`;

    const commentHtml = details.comment ? `<div style="white-space: pre-wrap; margin-top: 6px;">${escapeHtml(details.comment)}</div>` : `<span>N/A</span>`;
    const lateReasonHtml = details.lateReasonSent ? `<div style="white-space: pre-wrap; margin-top: 6px;">${escapeHtml(details.lateReason || '')}</div>` : `<span>N/A</span>`;
    const rejectionReasonHtml = details.rejectionReason ? `<div style="white-space: pre-wrap; margin-top: 6px;">${escapeHtml(details.rejectionReason)}</div>` : `<span>N/A</span>`;
    const handledByHtml = details.handledBy ? escapeHtml(details.handledBy) : 'N/A';
    const handledAtHtml = details.handledAt ? escapeHtml(details.handledAt) : 'N/A';
    const handledActionHtml = details.handledAction ? escapeHtml(details.handledAction) : 'N/A';
    const lateReasonByHtml = details.lateReasonBy ? escapeHtml(details.lateReasonBy) : 'N/A';
    const daysHtml = details.days !== '' && details.days !== null && details.days !== undefined
        ? escapeHtml(String(details.days))
        : 'N/A';

    Swal.fire({
        title: `Request #${escapeHtml(details.id)}`,
        width: 820,
        customClass: { popup: 'request-details-popup' },
        html: `
            <div style="text-align:left; line-height:1.45;">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom:12px;">
                    <div><div style="font-weight:800;">Leave Type</div><div>${escapeHtml(details.type || 'N/A')}</div></div>
                    <div><div style="font-weight:800;">Status</div><div>${escapeHtml(statusLabel || 'N/A')}</div></div>
                    <div><div style="font-weight:800;">Start</div><div>${escapeHtml(details.start || 'N/A')}</div></div>
                    <div><div style="font-weight:800;">End</div><div>${escapeHtml(details.end || 'N/A')}</div></div>
                    <div><div style="font-weight:800;">Working Days</div><div>${daysHtml}</div></div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom:12px;">
                    <div><div style="font-weight:800;">Handled By</div><div>${handledByHtml}</div></div>
                    <div><div style="font-weight:800;">Handled At</div><div>${handledAtHtml}</div></div>
                    <div><div style="font-weight:800;">Handled Action</div><div>${handledActionHtml}</div></div>
                    <div><div style="font-weight:800;">Late Reason By</div><div>${lateReasonByHtml}</div></div>
                </div>

                <div style="margin-bottom:10px;">
                    <div style="font-weight:800;">Important Comment</div>
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
            html: `Request <strong>#${escapeHtml(requestId)}</strong> will be permanently removed, including any attachments.`,
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

document.querySelectorAll('.withdraw-request-btn').forEach((btn) => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const form = btn.closest('form');
        if (!form) return;
        Swal.fire({
            title: 'Withdraw leave request?',
            text: 'Do you really want to withdraw this leave request?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#b45309',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, withdraw it',
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
</script>
@endsection
