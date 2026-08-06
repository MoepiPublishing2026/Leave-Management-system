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
        if (isOverdue) return 'request-badge--overdue';
        const key = String(statusKey || '').toLowerCase();
        if (key === 'approved') return 'request-badge--approved';
        if (key === 'rejected') return 'request-badge--rejected';
        if (key === 'withdrawn') return 'request-badge--withdrawn';
        if (key === 'pending' || key === 'pending_senior') return 'request-badge--pending';
        return 'request-badge--pending';
    }

    function statusDisplayLabel(statusKey, isOverdue) {
        if (isOverdue) return 'Overdue';
        const key = String(statusKey || '').toLowerCase();
        if (key === 'pending' || key === 'pending_senior') return 'Pending';
        if (!key) return 'Unknown';
        return key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
    }

    function hasText(value) {
        return String(value ?? '').trim() !== '';
    }

    function renderFileActions(file) {
        const label = escapeHtml(file.label || 'File');
        const name = escapeHtml(file.name || file.label || 'Attachment');
        const previewUrl = escapeHtml(file.url || '');
        const downloadUrl = escapeHtml(file.downloadUrl || file.url || '');
        const extension = escapeHtml(file.extension || '');

        return `<li class="rd-file-item">
            <span class="rd-file-name">${fileIconSvg}${label}</span>
            <span class="rd-file-actions">
                <button type="button" class="rd-file-btn rd-file-btn--preview attachment-preview-btn" data-preview-url="${previewUrl}" data-download-url="${downloadUrl}" data-extension="${extension}" data-label="${label}" data-filename="${name}" data-previewable="1">Preview</button>
                <a class="rd-file-btn rd-file-btn--download" href="${downloadUrl}">Download</a>
            </span>
        </li>`;
    }

    function loadMammoth() {
        if (window.mammoth) {
            return Promise.resolve(window.mammoth);
        }
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/mammoth@1.8.0/mammoth.browser.min.js';
            script.async = true;
            script.onload = () => resolve(window.mammoth);
            script.onerror = () => reject(new Error('Could not load Word preview library.'));
            document.head.appendChild(script);
        });
    }

    window.openAttachmentPreview = async function (file) {
        const previewUrl = file.previewUrl || file.url || '';
        const downloadUrl = file.downloadUrl || previewUrl;
        const extension = String(file.extension || '').toLowerCase();
        const label = file.label || 'Attachment';
        const filename = file.filename || file.name || label;
        const isMobile = window.matchMedia('(max-width: 768px)').matches;

        const loadingHtml = `
            <div class="attachment-preview-shell">
                <div class="attachment-preview-meta">${escapeHtml(filename)}</div>
                <div class="attachment-preview-wrap">
                    <p class="attachment-preview-note">Loading preview…</p>
                </div>
            </div>
        `;

        Swal.fire({
            title: escapeHtml(label),
            width: isMobile ? 'auto' : 920,
            html: loadingHtml,
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Download',
            showDenyButton: true,
            denyButtonText: 'Open in tab',
            customClass: {
                popup: 'attachment-preview-popup',
                container: 'attachment-preview-container',
                confirmButton: 'attachment-preview-btn-download',
                denyButton: 'attachment-preview-btn-open',
            },
            didOpen: (popup) => {
                const container = popup?.closest('.swal2-container');
                if (container) container.style.zIndex = '10200';
            },
        }).then((result) => {
            if (result.isConfirmed) {
                window.open(downloadUrl, '_blank', 'noopener');
            } else if (result.isDenied) {
                window.open(previewUrl, '_blank', 'noopener');
            }
        });

        const wrap = document.querySelector('.attachment-preview-popup .attachment-preview-wrap');
        if (!wrap || !previewUrl) {
            if (wrap) {
                wrap.innerHTML = `<p class="attachment-preview-note attachment-preview-note--error">Preview link is missing. Use Download instead.</p>`;
            }
            return;
        }

        try {
            const response = await fetch(previewUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': '*/*' },
            });

            if (!response.ok) {
                const missing = response.status === 404;
                wrap.innerHTML = `<p class="attachment-preview-note attachment-preview-note--error">${
                    missing
                        ? 'This file is no longer on the server (it may have been deleted or never saved). Ask the employee to upload it again, or use Download if available.'
                        : 'Could not load this file for preview (error ' + response.status + '). Try Download or Open in tab.'
                }</p>`;
                return;
            }

            const blob = await response.blob();
            const objectUrl = URL.createObjectURL(blob);

            if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(extension)) {
                wrap.innerHTML = `<img class="attachment-preview-image" src="${objectUrl}" alt="${escapeHtml(label)} preview">`;
            } else if (extension === 'pdf') {
                wrap.innerHTML = `<iframe class="attachment-preview-frame" src="${objectUrl}" title="${escapeHtml(label)} preview"></iframe>`;
            } else if (extension === 'txt') {
                const text = await blob.text();
                wrap.innerHTML = `<pre class="attachment-preview-text">${escapeHtml(text)}</pre>`;
            } else if (extension === 'docx') {
                wrap.innerHTML = `<p class="attachment-preview-note">Preparing Word preview…</p>`;
                try {
                    const mammoth = await loadMammoth();
                    const arrayBuffer = await blob.arrayBuffer();
                    const result = await mammoth.convertToHtml({ arrayBuffer });
                    wrap.innerHTML = `<div class="attachment-preview-doc">${result.value || '<p class="attachment-preview-note">No readable content found in this document.</p>'}</div>`;
                } catch (err) {
                    wrap.innerHTML = `<p class="attachment-preview-note attachment-preview-note--error">Word preview failed. Use Download or Open in tab.</p>`;
                }
            } else if (extension === 'doc') {
                wrap.innerHTML = `<p class="attachment-preview-note">Legacy <strong>.doc</strong> files cannot be previewed in the browser. Please Download the file, or re-upload as <strong>.docx</strong> or <strong>PDF</strong>.</p>`;
            } else {
                wrap.innerHTML = `<p class="attachment-preview-note">Preview is not available for <strong>.${escapeHtml(extension || 'this')}</strong> files. Use Download or Open in tab.</p>`;
            }
        } catch (err) {
            wrap.innerHTML = `<p class="attachment-preview-note attachment-preview-note--error">Preview failed to load. Check your connection, then try Download.</p>`;
        }
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
            filename: previewBtn.getAttribute('data-filename') || '',
            previewable: true,
        });
    }, true);

    window.openRequestDetails = function (details) {
        const isOverdue = !!details.isOverdue;
        const statusKey = isOverdue ? 'overdue' : String(details.status || '').toLowerCase();
        const statusLabel = statusDisplayLabel(details.status, isOverdue);

        function row(label, valueHtml) {
            if (!valueHtml) return '';
            return `<div class="rd-row">
                <div class="rd-row-label">${escapeHtml(label)}</div>
                <div class="rd-row-value">${valueHtml}</div>
            </div>`;
        }

        let employeeHtml = '';
        if (pageConfig.showEmployee) {
            const employeeLine = [details.department, details.email].filter(Boolean).join(' · ');
            employeeHtml = `
                <div class="rd-employee">
                    <p class="rd-employee-name">${escapeHtml(details.employee || 'Employee')}</p>
                    <p class="rd-employee-meta">${escapeHtml(employeeLine || 'N/A')}</p>
                </div>
            `;
        }

        const rows = [];
        rows.push(row('Leave type', `<span class="rd-row-value--type">${escapeHtml(details.type || 'N/A')}</span>`));
        rows.push(row('Start date', escapeHtml(details.start || 'N/A')));
        rows.push(row('End date', escapeHtml(details.end || 'N/A')));
        if (pageConfig.showDays && details.days !== '' && details.days !== null && details.days !== undefined) {
            rows.push(row('Working days', escapeHtml(String(details.days))));
        }

        if (pageConfig.showHandledMeta) {
            if (hasText(details.handledBy)) rows.push(row('Handled by', escapeHtml(details.handledBy)));
            if (hasText(details.handledAt)) rows.push(row('Handled at', escapeHtml(details.handledAt)));
            if (hasText(details.handledAction)) rows.push(row('Action', escapeHtml(details.handledAction)));
            if (hasText(details.lateReasonBy)) rows.push(row('Late reason by', escapeHtml(details.lateReasonBy)));
        }

        if (hasText(details.comment)) {
            rows.push(row('Comment', `<span class="rd-row-value--block">${escapeHtml(details.comment)}</span>`));
        }

        const files = Array.isArray(details.files) ? details.files : [];
        rows.push(row(
            'Attachments',
            files.length
                ? `<ul class="rd-files">${files.map(renderFileActions).join('')}</ul>`
                : '<span class="rd-row-value--muted">None</span>'
        ));

        if (details.proofDeferred) {
            const proofStatus = details.proofAttached
                ? 'Doctor\'s letter uploaded'
                : (details.certificateNote || details.proofStatusLabel || 'Doctor\'s letter required after approval');
            let extra = escapeHtml(proofStatus);
            if (details.proofOpensAt && !details.canUploadProofNow && !details.proofAttached) {
                extra += `<div class="rd-row-note">Upload from ${escapeHtml(details.proofOpensAt)} via Actions.</div>`;
            }
            if (details.proofDeadline) {
                extra += `<div class="rd-row-note">Deadline: ${escapeHtml(details.proofDeadline)}</div>`;
            }
            rows.push(row('Doctor\'s letter', extra));
        }

        if (details.convertedToUnpaidAt) {
            rows.push(row(
                'Converted',
                `<strong>${escapeHtml(details.originalLeaveType || 'Sick')} → Unpaid</strong>` +
                `<div class="rd-row-note">${escapeHtml(details.convertedToUnpaidAt)}</div>` +
                `<div class="rd-row-note">${escapeHtml(details.conversionReason || 'Required documents were not submitted by the deadline.')}</div>`
            ));
        }

        if (hasText(details.managerRecommendation)) {
            rows.push(row(
                'Recommendation',
                `<strong>${escapeHtml(details.managerRecommendation)}</strong>` +
                (hasText(details.managerRecommendationComment)
                    ? `<div class="rd-row-note">${escapeHtml(details.managerRecommendationComment)}</div>`
                    : '')
            ));
        }

        if (details.lateReasonSent && hasText(details.lateReason)) {
            rows.push(row('Late reason', `<span class="rd-row-value--block">${escapeHtml(details.lateReason)}</span>`));
        }

        if (hasText(details.rejectionReason)) {
            rows.push(row('Rejection', `<span class="rd-row-value--block">${escapeHtml(details.rejectionReason)}</span>`));
        }

        let messagesHtml = '';
        if (details.messagesUrl) {
            messagesHtml = `<div class="rd-messages"><a href="${escapeHtml(details.messagesUrl)}">Open messages</a></div>`;
        }

        const html = `
            <div class="rd-modal">
                <div class="rd-header">
                    <div class="rd-header-id">Request <span>#${escapeHtml(details.id)}</span></div>
                    <span class="request-badge ${statusBadgeClass(statusKey, isOverdue)}">${escapeHtml(statusLabel)}</span>
                </div>
                ${employeeHtml}
                <div class="rd-body">
                    ${rows.join('')}
                    ${messagesHtml}
                </div>
            </div>
        `;

        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        Swal.fire({
            width: isMobile ? 'auto' : 620,
            position: 'center',
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
                        ? `<p class="proof-upload-modal-note">Upload is not open yet. You can upload from <strong>${escapeHtml(opensAt)}</strong>, by <strong>${escapeHtml(deadline)}</strong> at the latest.</p>`
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
                ? `<p class="proof-upload-modal-note">Upload by <strong>${escapeHtml(deadline)}</strong> at the latest${opensAt ? ` (window opens on <strong>${escapeHtml(opensAt)}</strong>, your leave start date)` : ''}.</p>`
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
