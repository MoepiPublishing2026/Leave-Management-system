@php
    $showEmployee = $showEmployee ?? false;
    $showDays = $showDays ?? false;
    $showHandledMeta = $showHandledMeta ?? false;
    $enableWithdraw = $enableWithdraw ?? false;
    $deleteWarning = $deleteWarning ?? 'This request will be permanently removed.';
@endphp
@include('partials.request-details-modal-styles')
<script>
(function () {
    const pageConfig = {
        showEmployee: @json($showEmployee),
        showDays: @json($showDays),
        showHandledMeta: @json($showHandledMeta),
    };

    const fileIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2 5 5h-4a1 1 0 0 1-1-1V4zM8 13h8v2H8v-2zm0-4h8v2H8V9z"/></svg>';

    function escapeHtml(text) {
        return String(text ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        }[c]));
    }

    function statusBadgeClass(statusKey, isOverdue) {
        if (isOverdue) return 'rd-badge--overdue';
        const key = String(statusKey || '').toLowerCase();
        if (key === 'approved') return 'rd-badge--approved';
        if (key === 'rejected') return 'rd-badge--rejected';
        if (key === 'withdrawn') return 'rd-badge--withdrawn';
        if (key === 'pending') return 'rd-badge--pending';
        return 'rd-badge--pending';
    }

    function statCard(label, value, extraClass) {
        return `<div class="rd-stat">
            <span class="rd-stat-label">${escapeHtml(label)}</span>
            <span class="rd-stat-value${extraClass ? ' ' + extraClass : ''}">${value}</span>
        </div>`;
    }

    function sectionBlock(title, innerHtml, boxClass) {
        const boxCls = boxClass ? ' rd-box--' + boxClass : '';
        return `<div class="rd-section">
            <h4 class="rd-section-title">${escapeHtml(title)}</h4>
            <div class="rd-box${boxCls}">${innerHtml}</div>
        </div>`;
    }

    window.openRequestDetails = function (details) {
        const isOverdue = !!details.isOverdue;
        const statusKey = isOverdue ? 'overdue' : String(details.status || '').toLowerCase();
        const statusLabel = isOverdue
            ? 'Overdue'
            : (details.status ? details.status.charAt(0).toUpperCase() + details.status.slice(1) : 'Unknown');

        let employeeHtml = '';
        if (pageConfig.showEmployee) {
            const employeeLine = [details.employee, details.designation, details.department].filter(Boolean).join(' · ');
            employeeHtml = `
                <div class="rd-employee">
                    <p class="rd-employee-name">${escapeHtml(details.employee || 'Employee')}</p>
                    <p class="rd-employee-meta">${escapeHtml(employeeLine || 'N/A')}${details.email ? ' · ' + escapeHtml(details.email) : ''}</p>
                </div>
            `;
        }

        const stats = [
            statCard('Leave type', escapeHtml(details.type || 'N/A'), 'rd-stat-value--type'),
            statCard('Start date', escapeHtml(details.start || 'N/A')),
            statCard('End date', escapeHtml(details.end || 'N/A')),
        ];

        if (pageConfig.showDays) {
            const daysVal = details.days !== '' && details.days !== null && details.days !== undefined
                ? escapeHtml(String(details.days))
                : 'N/A';
            stats.push(statCard('Working days', daysVal));
        }

        let handledHtml = '';
        if (pageConfig.showHandledMeta) {
            handledHtml = `
                <div class="rd-handled">
                    <div class="rd-handled-item"><strong>Handled by</strong>${details.handledBy ? escapeHtml(details.handledBy) : '—'}</div>
                    <div class="rd-handled-item"><strong>Handled at</strong>${details.handledAt ? escapeHtml(details.handledAt) : '—'}</div>
                    <div class="rd-handled-item"><strong>Action</strong>${details.handledAction ? escapeHtml(details.handledAction) : '—'}</div>
                    <div class="rd-handled-item"><strong>Late reason by</strong>${details.lateReasonBy ? escapeHtml(details.lateReasonBy) : '—'}</div>
                </div>
            `;
        }

        const files = Array.isArray(details.files) ? details.files : [];
        const filesHtml = files.length
            ? `<ul class="rd-files">${files.map(f => `<li><a class="rd-file-link" href="${escapeHtml(f.url)}" target="_blank" rel="noopener noreferrer">${fileIconSvg}${escapeHtml(f.label)}</a></li>`).join('')}</ul>`
            : '<span class="rd-box rd-box--muted" style="display:block;border:none;padding:0;background:transparent;">No attachments</span>';

        const commentInner = details.comment
            ? escapeHtml(details.comment)
            : '<span class="rd-box--muted">No comment provided</span>';

        const lateInner = details.lateReasonSent && details.lateReason
            ? escapeHtml(details.lateReason)
            : '<span class="rd-box--muted">Not applicable</span>';

        const rejectionInner = details.rejectionReason
            ? escapeHtml(details.rejectionReason)
            : '<span class="rd-box--muted">Not applicable</span>';

        const rejectionBoxClass = details.rejectionReason ? 'rejection' : '';
        const lateBoxClass = details.lateReasonSent && details.lateReason ? 'info' : '';

        let messagesHtml = '';
        if (details.messagesUrl) {
            messagesHtml = `
                <div class="rd-section" style="margin-bottom:8px;">
                    <a href="${escapeHtml(details.messagesUrl)}" class="rd-footer-link">Open messages</a>
                </div>
            `;
        }

        const html = `
            <div class="rd-modal">
                <div class="rd-header">
                    <div class="rd-header-id">Request <span>#${escapeHtml(details.id)}</span></div>
                    <span class="rd-badge ${statusBadgeClass(statusKey, isOverdue)}">${escapeHtml(statusLabel)}</span>
                </div>
                ${employeeHtml}
                <div class="rd-body">
                    <div class="rd-stats">${stats.join('')}</div>
                    ${handledHtml}
                    ${sectionBlock('Important comment', commentInner)}
                    <div class="rd-section">
                        <h4 class="rd-section-title">Attachments</h4>
                        ${filesHtml}
                    </div>
                    ${sectionBlock('Late response reason', lateInner, lateBoxClass)}
                    ${sectionBlock('Rejection reason', rejectionInner, rejectionBoxClass)}
                    ${messagesHtml}
                </div>
            </div>
        `;

        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        Swal.fire({
            width: isMobile ? 'auto' : 640,
            position: 'top',
            showCloseButton: false,
            showIcon: false,
            customClass: {
                popup: 'request-details-popup',
                container: 'request-details-container',
            },
            html: html,
            showConfirmButton: true,
            confirmButtonText: 'Close',
            focusConfirm: false,
            didOpen: (popup) => {
                const container = popup?.closest('.swal2-container');
                if (container) {
                    container.style.zIndex = '10150';
                    container.scrollTop = 0;
                }
                const htmlContainer = Swal.getHtmlContainer();
                if (htmlContainer) {
                    htmlContainer.scrollTop = 0;
                }
            },
        });
    };

    document.querySelectorAll('.delete-request-btn').forEach((btn) => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = btn.closest('form');
            if (!form) return;
            const requestId = btn.getAttribute('data-request-id') || '';
            Swal.fire({
                title: 'Delete leave request?',
                html: `Request <strong>#${escapeHtml(requestId)}</strong> — {{ $deleteWarning }}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#991b1b',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it',
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });

    @if ($enableWithdraw)
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
    @endif

    document.addEventListener('click', (e) => {
        const interactive = e.target.closest('a,button,form,input,textarea,select,label');
        if (interactive) return;
        const row = e.target.closest('tr[data-details]');
        if (!row) return;
        const raw = row.getAttribute('data-details');
        if (!raw) return;
        try {
            openRequestDetails(JSON.parse(raw));
        } catch (err) {
        }
    });
})();
</script>
