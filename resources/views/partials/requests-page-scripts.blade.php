@php
    $showEmployee = $showEmployee ?? false;
    $showDays = $showDays ?? false;
    $showHandledMeta = $showHandledMeta ?? false;
    $enableWithdraw = $enableWithdraw ?? false;
    $enableProofUpload = $enableProofUpload ?? false;
    $deleteWarning = $deleteWarning ?? 'This request will be permanently removed.';
    $backLabel = $backLabel ?? '← Back to My requests';
    $seniorLayout = $seniorLayout ?? false;
@endphp
@include('partials.request-details-modal-styles')
<script>
(function () {
    const pageConfig = {
        showEmployee: @json($showEmployee),
        showDays: @json($showDays),
        showHandledMeta: @json($showHandledMeta),
        backLabel: @json($backLabel),
        seniorLayout: @json($seniorLayout),
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

    function checkIcon(ok) {
        if (ok) {
            return '<span class="rd-check-mark rd-check-mark--ok" aria-hidden="true">✓</span>';
        }
        return '<span class="rd-check-mark rd-check-mark--warn" aria-hidden="true">!</span>';
    }

    function renderReviewChecklist(review) {
        if (!review || typeof review !== 'object') {
            return '';
        }

        const remainingLine = `Allocated ${escapeHtml(review.allocated || '0')} · Taken ${escapeHtml(review.taken || '0')} · Remaining ${escapeHtml(review.remaining || '0')}`;
        const requestLine = `This request: ${escapeHtml(review.requested || '0')} working day(s)` +
            (review.fitsBalance ? '' : ' — more than remaining balance');
        const annualLine = review.countsAgainstAnnual && review.annualRemaining !== ''
            ? `<div class="rd-check-note">Also counts against Annual (remaining ${escapeHtml(review.annualRemaining)} days).</div>`
            : '';

        let documentsBody;
        if (review.proofAttached) {
            documentsBody = 'Supporting document is attached. Preview it in Attachments below.';
        } else if (review.proofDeferred) {
            documentsBody = 'Doctor\'s letter is not attached yet. Sick leave may be recommended before the doctor visit.';
            if (review.proofDeadline) {
                documentsBody += ` Upload deadline: ${escapeHtml(review.proofDeadline)}.`;
            }
        } else if (review.proofRequired) {
            documentsBody = 'Supporting documentation is required and is not attached.';
        } else {
            documentsBody = 'No supporting document is required for this leave type.';
        }

        const unpaidHtml = review.willConvertToUnpaid && review.unpaidNote
            ? `<div class="rd-check-alert">${escapeHtml(review.unpaidNote)}</div>`
            : '';

        const policyBits = [];
        if (review.policyDefaultDays) policyBits.push(`${escapeHtml(review.policyDefaultDays)} days per year`);
        policyBits.push(review.proofRequired ? 'proof required' : 'proof not required');
        policyBits.push(review.allowHalfDays ? 'half days allowed' : 'half days not allowed');

        return `
            <div class="rd-review">
                <div class="rd-review-title">Manager review</div>
                <div class="rd-check ${review.fitsBalance ? 'rd-check--ok' : 'rd-check--warn'}">
                    ${checkIcon(!!review.fitsBalance)}
                    <div>
                        <div class="rd-check-label">Verify leave balance</div>
                        <div class="rd-check-body">${escapeHtml(remainingLine)}</div>
                        <div class="rd-check-body">${escapeHtml(requestLine)}</div>
                        ${annualLine}
                    </div>
                </div>
                <div class="rd-check ${review.documentsOk ? 'rd-check--ok' : 'rd-check--warn'}">
                    ${checkIcon(!!review.documentsOk)}
                    <div>
                        <div class="rd-check-label">Verify supporting documents</div>
                        <div class="rd-check-body">${escapeHtml(documentsBody)}</div>
                        ${unpaidHtml}
                    </div>
                </div>
                <div class="rd-check rd-check--ok">
                    ${checkIcon(true)}
                    <div>
                        <div class="rd-check-label">Check leave policy</div>
                        <div class="rd-check-body">${policyBits.join(' · ')}</div>
                        ${review.policyText ? `<div class="rd-check-note">${escapeHtml(review.policyText)}</div>` : ''}
                    </div>
                </div>
            </div>
        `;
    }

    function shortDate(value, fallback) {
        if (hasText(fallback)) return escapeHtml(fallback);
        const stripped = String(value || '').replace(/\s+\d{4}$/, '').trim();
        return escapeHtml(stripped || 'N/A');
    }

    function chip(label, valueHtml, extraClass) {
        return `<div class="rd-chip${extraClass ? ' ' + extraClass : ''}">
            <span class="rd-chip-k">${escapeHtml(label)} :</span>
            <span class="rd-chip-v">${valueHtml}</span>
        </div>`;
    }

    function renderTimeline(details) {
        const status = String(details.status || '').toLowerCase();
        const submittedAt = details.submittedAt || '';
        const reviewedAt = details.reviewedAt || '';
        const outcomeAt = details.outcomeAt || details.handledAt || '';
        const reviewedDone = hasText(reviewedAt) || (['approved', 'rejected'].includes(status) && hasText(outcomeAt));
        const reviewedDate = reviewedAt || (reviewedDone ? outcomeAt : '');

        let third = { label: 'Pending', date: '', tone: 'empty' };
        if (status === 'withdrawn') {
            third = { label: 'Withdrawn', date: outcomeAt, tone: 'orange' };
        } else if (status === 'approved') {
            third = { label: 'Approved', date: outcomeAt, tone: 'lime' };
        } else if (status === 'rejected') {
            third = { label: 'Rejected', date: outcomeAt, tone: 'red' };
        }

        const steps = [
            { label: 'Submitted', date: submittedAt, tone: hasText(submittedAt) ? 'lime' : 'empty' },
            { label: 'Reviewed', date: reviewedDate, tone: reviewedDone ? 'lime' : 'empty' },
            third,
        ];

        const parts = [];
        steps.forEach((step, i) => {
            if (i > 0) {
                const prevDone = steps[i - 1].tone !== 'empty';
                const thisDone = step.tone !== 'empty';
                parts.push(`<div class="rd-tl-line${prevDone && thisDone ? '' : ' rd-tl-line--muted'}"></div>`);
            }
            parts.push(`
                <div class="rd-tl-step">
                    <div class="rd-tl-dot rd-tl-dot--${step.tone}"></div>
                    <div class="rd-tl-label">${escapeHtml(step.label)}</div>
                    <div class="rd-tl-date${hasText(step.date) ? '' : ' rd-tl-date--empty'}">${hasText(step.date) ? escapeHtml(step.date) : '&nbsp;'}</div>
                </div>
            `);
        });

        return `<div class="rd-timeline">${parts.join('')}</div>`;
    }

    function renderAttachmentChip(details) {
        const files = Array.isArray(details.files) ? details.files : [];
        if (!files.length) {
            return chip('Attachment', '<span class="rd-chip-muted">None</span>', 'rd-chip--full');
        }

        const names = files.map((file) => {
            const name = escapeHtml(file.name || file.label || 'Attachment');
            const previewUrl = escapeHtml(file.url || '');
            const downloadUrl = escapeHtml(file.downloadUrl || file.url || '');
            const extension = escapeHtml(file.extension || '');
            const label = escapeHtml(file.label || 'File');
            return `<button type="button" class="rd-chip-file attachment-preview-btn" data-preview-url="${previewUrl}" data-download-url="${downloadUrl}" data-extension="${extension}" data-label="${label}" data-filename="${name}" data-previewable="1">${name}</button>`;
        }).join('<span class="rd-chip-sep">, </span>');

        return chip('Attachment', names, 'rd-chip--full');
    }

    function seniorStatusIcon(statusKey, isOverdue) {
        const key = isOverdue ? 'overdue' : String(statusKey || '').toLowerCase();
        if (key === 'approved') {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.7 2.7L16 9.8"/></svg>';
        }
        if (key === 'rejected') {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/></svg>';
        }
        return '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>';
    }

    function renderSeniorPopup(details) {
        const review = details.review && typeof details.review === 'object' ? details.review : {};
        const statusKey = details.isOverdue ? 'overdue' : String(details.status || 'pending');
        const statusLabel = statusDisplayLabel(details.status, details.isOverdue);
        const deadline = review.proofDeadline || details.proofDeadline || '';
        const requested = review.requested || details.days || '0';

        let documents = 'No supporting document is required for this leave type.';
        if (review.proofAttached || details.proofAttached) {
            documents = 'Supporting document is attached.';
        } else if (review.proofDeferred || details.proofDeferred) {
            documents = "The doctor's letter is not attached yet. The doctor may recommend sick leave before the visit.";
            if (deadline) documents += ` Upload deadline: ${deadline}.`;
        } else if (review.proofRequired) {
            documents = 'Supporting documentation is required and is not attached.';
        }

        const alertHtml = review.willConvertToUnpaid && review.unpaidNote
            ? `<div class="rd-senior-alert">${escapeHtml(review.unpaidNote)}</div>`
            : '';

        const pills = [];
        if (review.policyDefaultDays) pills.push(`${escapeHtml(review.policyDefaultDays)} days per year`);
        pills.push(review.proofRequired ? 'proof required' : 'proof not required');
        pills.push(review.allowHalfDays ? 'half days allowed' : 'half days not allowed');

        const whoBits = [details.designation, details.email].filter((value) => hasText(value));
        const files = Array.isArray(details.files) ? details.files : [];
        const attachmentText = files.length
            ? files.map((file) => escapeHtml(file.label || file.name || 'File')).join(', ')
            : 'None';

        let letterText = 'Not required';
        if (review.proofAttached || details.proofAttached) {
            letterText = 'Uploaded';
        } else if (review.proofDeferred || details.proofDeferred || review.proofRequired) {
            letterText = "Doctor's letter required after approval";
            if (deadline) letterText += `. Deadline: ${deadline}`;
        }

        const dateText = (value) => escapeHtml(String(value || '—').toUpperCase());
        let recommendation = '—';
        if (hasText(details.managerRecommendation)) {
            recommendation = escapeHtml(String(details.managerRecommendation).toUpperCase());
            if (hasText(details.managerRecommendationComment)) {
                recommendation += `<div class="rd-chip-note">${escapeHtml(details.managerRecommendationComment)}</div>`;
            }
        }
        const messagesHtml = details.messagesUrl
            ? `<a class="rd-senior-messages" href="${escapeHtml(details.messagesUrl)}">Open Messages</a>`
            : '';
        const rows = [
            ['Leave type', escapeHtml(details.type || '—')],
        ];
        if (details.convertedToUnpaidAt) {
            rows.push([
                'Converted',
                `<strong>${escapeHtml(details.originalLeaveType || 'Sick')} → Unpaid</strong>` +
                `<div class="rd-chip-note">${escapeHtml(details.convertedToUnpaidAt)}</div>` +
                `<div class="rd-chip-note">${escapeHtml(details.conversionReason || 'Required documents were not submitted by the deadline.')}</div>`,
            ]);
        }
        rows.push(
            ['Start date', dateText(details.start)],
            ['End date', dateText(details.end)],
            ['Working days', escapeHtml(String(requested))],
            ['Comment', hasText(details.comment) ? escapeHtml(details.comment) : 'None'],
            ['Attachments', attachmentText],
            ["Doctor's letter", escapeHtml(letterText).toUpperCase()],
            ['Recommendation', recommendation],
        );

        return `
            <div class="rd-modal rd-modal--senior">
                <div class="rd-senior-head">
                    <h2>Request #${escapeHtml(details.id)}</h2>
                    <span class="rd-senior-status rd-senior-status--${escapeHtml(statusKey)}">${seniorStatusIcon(statusKey, details.isOverdue)}${escapeHtml(statusLabel)}</span>
                </div>
                <div class="rd-senior-rule"></div>
                <div class="rd-senior-who">
                    <div class="rd-senior-name">${escapeHtml(details.employee || 'Unknown')}</div>
                    ${whoBits.length ? `<div class="rd-senior-meta">${whoBits.map((value) => escapeHtml(value)).join(' - ')}</div>` : ''}
                </div>
                <div class="rd-senior-box">
                    <div class="rd-senior-kicker">Manager review</div>
                    <div class="rd-senior-label">Verify leave balance</div>
                    <div class="rd-senior-stats">
                        <span>Allocated: ${escapeHtml(review.allocated || '0')}</span>
                        <span>Taken: ${escapeHtml(review.taken || '0')}</span>
                        <span>Remaining: ${escapeHtml(review.remaining || '0')}</span>
                    </div>
                    <p class="rd-senior-request">This request: ${escapeHtml(String(requested))} working day(s)</p>
                    <div class="rd-senior-label">Verify supporting documents</div>
                    <p class="rd-senior-copy">${escapeHtml(documents)}</p>
                    ${alertHtml}
                    <div class="rd-senior-label">Check leave policy</div>
                    <div class="rd-senior-pills">${pills.map((pill) => `<span>${pill}</span>`).join('')}</div>
                    ${review.policyText ? `<p class="rd-senior-policy">${escapeHtml(review.policyText)}</p>` : ''}
                    <table class="rd-senior-table">
                        <tbody>
                            ${rows.map(([label, value]) => `<tr><th>${label}</th><td>${value}</td></tr>`).join('')}
                        </tbody>
                    </table>
                    ${messagesHtml}
                </div>
            </div>
        `;
    }

    window.openRequestDetails = function (details) {
        if (pageConfig.seniorLayout) {
            const isMobileSenior = window.matchMedia('(max-width: 768px)').matches;
            Swal.fire({
                width: isMobileSenior ? 'auto' : 600,
                position: 'center',
                showCloseButton: false,
                showIcon: false,
                customClass: {
                    popup: 'request-details-popup request-details-popup--leave request-details-popup--senior',
                    container: 'request-details-container request-details-container--senior',
                    confirmButton: 'rd-senior-close',
                },
                html: renderSeniorPopup(details),
                showConfirmButton: true,
                confirmButtonText: 'Close',
                focusConfirm: false,
            });
            return;
        }

        let daysText = 'N/A';
        if (details.days !== '' && details.days !== null && details.days !== undefined) {
            daysText = details.isHalfDay
                ? `${escapeHtml(String(details.days))} Days (half day)`
                : `${escapeHtml(String(details.days))} Days`;
        }

        const extraChips = [];
        if (pageConfig.showEmployee) {
            const employeeLine = [details.employee, details.department, details.email].filter(Boolean).join(' · ');
            extraChips.push(chip('Employee', escapeHtml(employeeLine || 'N/A'), 'rd-chip--full'));

            if (pageConfig.showHandledMeta) {
                if (hasText(details.handledBy)) extraChips.push(chip('Handled by', escapeHtml(details.handledBy)));
                if (hasText(details.handledAction)) extraChips.push(chip('Action', escapeHtml(details.handledAction)));
                if (hasText(details.lateReasonBy)) extraChips.push(chip('Late reason by', escapeHtml(details.lateReasonBy)));
            }

            if (details.proofDeferred) {
                const proofStatus = details.proofAttached
                    ? 'Doctor\'s letter uploaded'
                    : (details.certificateNote || details.proofStatusLabel || 'Doctor\'s letter required after approval');
                let extra = escapeHtml(proofStatus);
                if (details.proofOpensAt && !details.canUploadProofNow && !details.proofAttached) {
                    extra += `<div class="rd-chip-note">Upload from ${escapeHtml(details.proofOpensAt)} via Actions.</div>`;
                }
                if (details.proofDeadline) {
                    extra += `<div class="rd-chip-note">Deadline: ${escapeHtml(details.proofDeadline)}</div>`;
                }
                extraChips.push(chip('Doctor\'s letter', extra, 'rd-chip--full'));
            }

            if (details.convertedToUnpaidAt) {
                extraChips.push(chip(
                    'Converted',
                    `<strong>${escapeHtml(details.originalLeaveType || 'Sick')} → Unpaid</strong>` +
                    `<div class="rd-chip-note">${escapeHtml(details.convertedToUnpaidAt)}</div>` +
                    `<div class="rd-chip-note">${escapeHtml(details.conversionReason || 'Required documents were not submitted by the deadline.')}</div>`,
                    'rd-chip--full'
                ));
            }

            if (hasText(details.managerRecommendation)) {
                extraChips.push(chip(
                    'Recommendation',
                    `<strong>${escapeHtml(details.managerRecommendation)}</strong>` +
                    (hasText(details.managerRecommendationComment)
                        ? `<div class="rd-chip-note">${escapeHtml(details.managerRecommendationComment)}</div>`
                        : ''),
                    'rd-chip--full'
                ));
            }

            if (details.lateReasonSent && hasText(details.lateReason)) {
                extraChips.push(chip('Late reason', escapeHtml(details.lateReason), 'rd-chip--full'));
            }

            if (hasText(details.rejectionReason)) {
                extraChips.push(chip('Rejection', escapeHtml(details.rejectionReason), 'rd-chip--full'));
            }

            if (hasText(details.approvalComment)) {
                extraChips.push(chip('Approval note', escapeHtml(details.approvalComment), 'rd-chip--full'));
            }
        } else if (hasText(details.rejectionReason)) {
            extraChips.push(chip('Rejection', escapeHtml(details.rejectionReason), 'rd-chip--full'));
        }

        let messagesHtml = '';
        if (details.messagesUrl) {
            messagesHtml = `<div class="rd-messages"><a href="${escapeHtml(details.messagesUrl)}">Open messages</a></div>`;
        }

        const reviewHtml = renderReviewChecklist(details.review);
        const extraHtml = extraChips.length
            ? `<div class="rd-fields rd-fields--extra">${extraChips.join('')}</div>`
            : '';

        const html = `
            <div class="rd-modal rd-modal--proto">
                <button type="button" class="rd-back" onclick="Swal.close()">${escapeHtml(pageConfig.backLabel)}</button>
                <h2 class="rd-title">Request #${escapeHtml(details.id)}</h2>
                ${renderTimeline(details)}
                <div class="rd-fields">
                    ${chip('Leave Type', escapeHtml(details.type || 'N/A'), 'rd-chip--full')}
                    <div class="rd-chip-row">
                        ${chip('Start Date', shortDate(details.start, details.startShort))}
                        ${chip('End Date', shortDate(details.end, details.endShort))}
                    </div>
                    <div class="rd-chip-row">
                        ${chip('Number of days', daysText)}
                        ${chip('Comment', hasText(details.comment) ? escapeHtml(details.comment) : '<span class="rd-chip-muted">None</span>')}
                    </div>
                    ${renderAttachmentChip(details)}
                </div>
                ${extraHtml}
                ${reviewHtml}
                ${messagesHtml}
            </div>
        `;

        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        Swal.fire({
            width: isMobile ? 'auto' : (reviewHtml ? 680 : 580),
            position: 'center',
            showCloseButton: false,
            showIcon: false,
            customClass: {
                popup: 'request-details-popup request-details-popup--leave',
                container: 'request-details-container',
            },
            html: html,
            showConfirmButton: false,
            focusConfirm: false,
            didOpen: (popup) => {
                popup.querySelector('.rd-back')?.addEventListener('click', () => Swal.close());
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
        const row = e.target.closest('[data-details]');
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

        const rows = document.querySelectorAll('[data-details]');
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

    function bindDecisionConfirms() {
        document.querySelectorAll('.recommend-approve-form, .senior-approve-form').forEach((form) => {
            form.addEventListener('submit', function (e) {
                if (form.dataset.confirmed === '1') {
                    return;
                }

                e.preventDefault();

                const row = form.closest('tr');
                let details = {};
                try {
                    details = JSON.parse(row?.getAttribute('data-details') || '{}');
                } catch (err) {
                    details = {};
                }

                const review = details.review || {};
                const notes = [];
                if (review.willConvertToUnpaid && review.unpaidNote) {
                    notes.push(review.unpaidNote);
                }
                if (review.fitsBalance === false) {
                    notes.push('This request is more working days than the remaining balance for this leave type.');
                }

                const isSenior = form.classList.contains('senior-approve-form');
                const notesHtml = notes.length
                    ? `<div class="swal-approve-notes">${notes.map((note) => `<p>${escapeHtml(note)}</p>`).join('')}</div>`
                    : '';

                Swal.fire({
                    icon: false,
                    title: isSenior ? 'Approve this leave request?' : 'Recommend approve?',
                    html: `${notesHtml}<p class="swal-approve-copy">Add an optional note. You can leave this blank.</p>`,
                    input: 'textarea',
                    inputPlaceholder: 'Optional note…',
                    inputAttributes: {
                        maxlength: 1000,
                        'aria-label': 'Optional note',
                    },
                    showCancelButton: true,
                    confirmButtonText: isSenior ? 'Approve' : 'Recommend Approve',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#94a3b8',
                    reverseButtons: true,
                    focusConfirm: false,
                    customClass: {
                        popup: 'swal-approve-popup',
                        title: 'swal-approve-title',
                        htmlContainer: 'swal-approve-html',
                        confirmButton: 'swal-approve-confirm',
                        cancelButton: 'swal-approve-cancel',
                        input: 'swal-approve-input',
                    },
                    preConfirm: (value) => {
                        const note = (value || '').trim();
                        if (note.length > 1000) {
                            Swal.showValidationMessage('Note must be 1000 characters or less.');
                            return false;
                        }
                        return note;
                    },
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    const input = form.querySelector('.approval-comment-input');
                    if (input) {
                        input.value = result.value || '';
                    }
                    form.dataset.confirmed = '1';
                    form.submit();
                });
            });
        });
    }

    bindDecisionConfirms();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', openRequestFromQuery);
    } else {
        openRequestFromQuery();
    }
})();
</script>
