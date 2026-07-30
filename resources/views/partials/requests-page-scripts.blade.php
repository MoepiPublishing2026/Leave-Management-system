@php
    $showEmployee = $showEmployee ?? false;
    $showDays = $showDays ?? false;
    $showHandledMeta = $showHandledMeta ?? false;
    $enableWithdraw = $enableWithdraw ?? false;
    $enableProofUpload = $enableProofUpload ?? false;
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
        if (key === 'pending' || key === 'pending_senior') return 'rd-badge--pending';
        return 'rd-badge--pending';
    }

    function statusDisplayLabel(statusKey, isOverdue) {
        if (isOverdue) return 'Overdue';
        const key = String(statusKey || '').toLowerCase();
        if (key === 'pending' || key === 'pending_senior') return 'Pending';
        if (!key) return 'Unknown';
        return key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
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

    function renderFileActions(file) {
        const label = escapeHtml(file.label || 'File');
        const name = escapeHtml(file.name || file.label || 'Attachment');
        const previewUrl = escapeHtml(file.url || '');
        const downloadUrl = escapeHtml(file.downloadUrl || file.url || '');
        const extension = escapeHtml(file.extension || '');
        const previewable = file.previewable === true || file.previewable === 1 || file.previewable === '1';

        return `<li class="rd-file-item">
            <span class="rd-file-name">${fileIconSvg}${label}</span>
            <span class="rd-file-actions">
                ${previewable
                    ? `<button type="button" class="rd-file-btn rd-file-btn--preview attachment-preview-btn" data-preview-url="${previewUrl}" data-download-url="${downloadUrl}" data-extension="${extension}" data-label="${name}" data-previewable="1">Preview</button>`
                    : `<button type="button" class="rd-file-btn rd-file-btn--preview attachment-preview-btn" data-preview-url="${previewUrl}" data-download-url="${downloadUrl}" data-extension="${extension}" data-label="${name}" data-previewable="0">View info</button>`
                }
                <a class="rd-file-btn rd-file-btn--download" href="${downloadUrl}">Download</a>
            </span>
        </li>`;
    }

    window.openAttachmentPreview = function (file) {
        const previewUrl = file.previewUrl || file.url || '';
        const downloadUrl = file.downloadUrl || previewUrl;
        const extension = String(file.extension || '').toLowerCase();
        const label = file.label || 'Attachment';
        const previewable = file.previewable === true || file.previewable === 1 || file.previewable === '1';

        let bodyHtml = '';
        if (previewable && extension === 'pdf') {
            bodyHtml = `<iframe class="attachment-preview-frame" src="${escapeHtml(previewUrl)}" title="${escapeHtml(label)} preview"></iframe>`;
        } else if (previewable && ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
            bodyHtml = `<img class="attachment-preview-image" src="${escapeHtml(previewUrl)}" alt="${escapeHtml(label)} preview">`;
        } else {
            bodyHtml = `<p class="attachment-preview-note">Preview is not available for this file type in the browser. Download the file to open it.</p>`;
        }

        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        Swal.fire({
            title: escapeHtml(label),
            width: isMobile ? 'auto' : 900,
            html: `<div class="attachment-preview-wrap">${bodyHtml}</div>`,
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Download',
            showDenyButton: previewable,
            denyButtonText: 'Open in tab',
            customClass: {
                popup: 'attachment-preview-popup',
                container: 'attachment-preview-container',
            },
            didOpen: (popup) => {
                const container = popup?.closest('.swal2-container');
                if (container) {
                    container.style.zIndex = '10200';
                }
            },
        }).then((result) => {
            if (result.isConfirmed) {
                window.open(downloadUrl, '_blank', 'noopener');
            } else if (result.isDenied && previewable) {
                window.open(previewUrl, '_blank', 'noopener');
            }
        });
    };

    // Capture phase: action cells call stopPropagation on bubble, which blocked preview clicks.
    document.addEventListener('click', (e) => {
        const previewBtn = e.target.closest('.attachment-preview-btn');
        if (!previewBtn) return;
        e.preventDefault();
        e.stopPropagation();
        window.openAttachmentPreview({
            previewUrl: previewBtn.getAttribute('data-preview-url') || '',
            downloadUrl: previewBtn.getAttribute('data-download-url') || '',
            extension: previewBtn.getAttribute('data-extension') || '',
            label: previewBtn.getAttribute('data-label') || 'Attachment',
            previewable: previewBtn.getAttribute('data-previewable') === '1',
        });
    }, true);

    window.openRequestDetails = function (details) {
        const isOverdue = !!details.isOverdue;
        const statusKey = isOverdue ? 'overdue' : String(details.status || '').toLowerCase();
        const statusLabel = statusDisplayLabel(details.status, isOverdue);

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
            ? `<ul class="rd-files">${files.map(renderFileActions).join('')}</ul>`
            : '<span class="rd-box rd-box--muted" style="display:block;border:none;padding:0;background:transparent;">No attachments</span>';

        let proofHtml = '';
        if (details.proofDeferred) {
            const proofStatus = details.proofAttached
                ? 'Doctor\'s letter uploaded'
                : (details.certificateNote || details.proofStatusLabel || 'Doctor\'s letter required after approval');
            const proofBoxClass = details.doctorsLetterMissing ? 'warning' : (details.proofAttached ? '' : 'info');
            const opensNote = details.proofOpensAt && !details.canUploadProofNow && !details.proofAttached
                ? `<div class="rd-box rd-box--muted" style="margin-top:6px;">Upload from <strong>${escapeHtml(details.proofOpensAt)}</strong> using the Actions column on this page.</div>`
                : '';
            const deadlineNote = details.proofDeadline
                ? `<div class="rd-box rd-box--muted" style="margin-top:6px;">Upload deadline: ${escapeHtml(details.proofDeadline)}</div>`
                : '';
            proofHtml = `
                <div class="rd-section">
                    <h4 class="rd-section-title">Doctor's letter</h4>
                    <div class="rd-box ${proofBoxClass}">${escapeHtml(proofStatus)}</div>
                    ${opensNote}
                    ${deadlineNote}
                </div>
            `;
        }

        const commentInner = details.comment
            ? escapeHtml(details.comment)
            : '<span class="rd-box--muted">No comment provided</span>';

        const lateInner = details.lateReasonSent && details.lateReason
            ? escapeHtml(details.lateReason)
            : '<span class="rd-box--muted">Not applicable</span>';

        const rejectionInner = details.rejectionReason
            ? escapeHtml(details.rejectionReason)
            : '<span class="rd-box--muted">Not applicable</span>';

        const recommendationInner = details.managerRecommendation
            ? `<div><strong>${escapeHtml(details.managerRecommendation)}</strong></div>` +
              (details.managerRecommendationComment
                  ? `<div style="margin-top:6px;">${escapeHtml(details.managerRecommendationComment)}</div>`
                  : '')
            : '<span class="rd-box--muted">Not yet recommended</span>';

        const conversionHtml = details.convertedToUnpaidAt
            ? sectionBlock(
                'Automatic unpaid conversion',
                `<div><strong>${escapeHtml(details.originalLeaveType || 'Sick')} → Unpaid</strong></div>` +
                `<div style="margin-top:6px;">Converted: ${escapeHtml(details.convertedToUnpaidAt)}</div>` +
                `<div style="margin-top:6px;">${escapeHtml(details.conversionReason || 'Required supporting documents were not submitted by the agreed deadline.')}</div>`,
                'rejection'
            )
            : '';

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
                    ${proofHtml}
                    ${conversionHtml}
                    ${sectionBlock('Manager recommendation', recommendationInner, 'info')}
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
            const approved = btn.getAttribute('data-approved') === '1';
            Swal.fire({
                title: approved ? 'Withdraw approved leave?' : 'Withdraw leave request?',
                text: approved
                    ? 'Your manager and senior managers will be notified, and the approved days will return to your leave balance.'
                    : 'Do you really want to withdraw this leave request?',
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

    @if ($enableProofUpload)
    document.querySelectorAll('.proof-upload-btn').forEach((btn) => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = btn.closest('form');
            const input = form ? form.querySelector('.proof-upload-input') : null;
            if (!form || !input) return;

            const canUpload = btn.getAttribute('data-can-upload') === '1';
            const isReplace = btn.getAttribute('data-is-replace') === '1';
            const actionLabel = btn.getAttribute('data-action-label') || (isReplace ? 'Replace attachment' : 'Upload attachment');
            const opensAt = btn.getAttribute('data-opens-at') || '';
            const deadline = btn.getAttribute('data-deadline') || '';

            if (!canUpload) {
                Swal.fire({
                    title: 'Upload not yet available',
                    html: opensAt && deadline
                        ? `<p class="proof-upload-modal-note">Upload is not open yet. You can upload on <strong>${escapeHtml(opensAt)}</strong>, or by <strong>${escapeHtml(deadline)}</strong> at the latest.</p>`
                        : '<p class="proof-upload-modal-note">Upload will be available when allowed for this request.</p>',
                    icon: 'info',
                    confirmButtonColor: '#65a30d',
                    confirmButtonText: 'OK',
                });
                return;
            }

            const replaceNote = isReplace
                ? '<p class="proof-upload-modal-note">This will replace your current file(s) with the new upload.</p>'
                : '';
            const deadlineNote = deadline
                ? `<p class="proof-upload-modal-note">Upload by <strong>${escapeHtml(deadline)}</strong> at the latest${opensAt ? ` (you may upload on <strong>${escapeHtml(opensAt)}</strong>, your leave start date)` : ''}.</p>`
                : '';

            Swal.fire({
                title: escapeHtml(actionLabel),
                html: `${replaceNote}${deadlineNote}<p class="proof-upload-modal-note">PDF, JPG, PNG, or Word — up to 10 MB per file.</p>`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#65a30d',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Choose file',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    input.click();
                }
            });
        });
    });

    document.querySelectorAll('.proof-upload-input').forEach((input) => {
        input.addEventListener('change', function () {
            const form = input.closest('form');
            if (!form || !input.files || input.files.length === 0) return;

            const count = input.files.length;
            const label = count === 1 ? '1 file' : `${count} files`;
            const uploadBtn = form.querySelector('.proof-upload-btn');
            const isReplace = uploadBtn?.getAttribute('data-is-replace') === '1';
            const actionLabel = uploadBtn?.getAttribute('data-action-label') || (isReplace ? 'Replace attachment' : 'Upload attachment');

            Swal.fire({
                title: isReplace ? 'Replace now?' : 'Upload now?',
                text: `${label} selected. ${isReplace ? 'Replace' : 'Upload'} the document for this leave request?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#65a30d',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Upload',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                } else {
                    input.value = '';
                }
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

    function openRequestFromQuery() {
        const params = new URLSearchParams(window.location.search);
        const requestId = params.get('request_id');
        if (!requestId) return;

        const rows = document.querySelectorAll('tr[data-details]');
        for (const row of rows) {
            try {
                const details = JSON.parse(row.getAttribute('data-details') || '{}');
                if (String(details.id) === String(requestId)) {
                    openRequestDetails(details);
                    row.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    break;
                }
            } catch (err) {
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', openRequestFromQuery);
    } else {
        openRequestFromQuery();
    }
})();
</script>
