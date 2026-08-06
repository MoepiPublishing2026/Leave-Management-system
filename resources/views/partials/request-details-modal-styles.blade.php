@once
<style>
    .request-details-container.swal2-container {
        z-index: 10150 !important;
        padding: 28px 14px !important;
        align-items: center !important;
        justify-content: center !important;
        overflow-y: auto !important;
    }
    .request-details-container .swal2-popup.request-details-popup {
        margin: 0 auto !important;
    }
    .request-details-popup.swal2-popup {
        padding: 0 !important;
        border-radius: 18px !important;
        border: 2px solid #8fb3a8 !important;
        box-shadow:
            0 22px 48px rgba(15, 61, 46, 0.16),
            0 2px 8px rgba(15, 23, 42, 0.06) !important;
        overflow: hidden !important;
        max-width: 620px !important;
        width: 100%;
        background: #fff !important;
        position: relative !important;
    }
    .request-details-popup.swal2-popup::before {
        content: "";
        display: block;
        height: 4px;
        background: linear-gradient(90deg, #0f766e 0%, #16a085 45%, #b2cd16 100%);
    }
    .request-details-popup .swal2-icon,
    .request-details-popup .swal2-close,
    .request-details-popup .swal2-title {
        display: none !important;
    }
    .request-details-popup .swal2-html-container {
        margin: 0 !important;
        padding: 0 !important;
        text-align: left !important;
        overflow: visible !important;
        max-height: none !important;
    }
    .request-details-popup .swal2-actions {
        margin: 0 !important;
        padding: 14px 22px 18px !important;
        border-top: 1px solid #c5d9d1;
        background: linear-gradient(180deg, #f4faf7 0%, #eef6f3 100%);
        width: 100%;
        box-sizing: border-box;
    }
    .request-details-popup .swal2-confirm {
        background: #b2cd16 !important;
        color: #0a1347 !important;
        border: 1px solid #91bf14 !important;
        border-radius: 10px !important;
        font-weight: 700 !important;
        font-size: 0.92rem !important;
        padding: 10px 26px !important;
        min-width: 120px;
        box-shadow: 0 2px 6px rgba(15, 61, 46, 0.12) !important;
    }
    .request-details-popup .swal2-confirm:hover {
        background: #9eb812 !important;
        color: #0a1347 !important;
    }
    .request-details-popup .swal2-confirm:focus {
        box-shadow: 0 0 0 3px rgba(19, 128, 86, 0.2) !important;
    }

    .rd-modal {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        color: #1e293b;
        line-height: 1.45;
    }
    .rd-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        padding: 18px 22px;
        background:
            radial-gradient(circle at top right, rgba(178, 205, 22, 0.18), transparent 42%),
            linear-gradient(135deg, #e7f4ef 0%, #f3faf7 55%, #ffffff 100%);
        border-bottom: 1px solid #c5d9d1;
    }
    .rd-header-id {
        font-size: 1.28rem;
        font-weight: 750;
        color: #1b4d42;
        margin: 0;
        letter-spacing: 0.01em;
    }
    .rd-header-id span {
        color: #0f766e;
    }
    .rd-employee {
        padding: 14px 22px;
        border-bottom: 1px solid #d7e5df;
        background: #f7fbf9;
    }
    .rd-employee-name {
        margin: 0 0 3px;
        font-weight: 750;
        font-size: 1rem;
        color: #1b4d42;
    }
    .rd-employee-meta {
        margin: 0;
        font-size: 0.84rem;
        color: #5f857a;
    }
    .rd-body {
        padding: 10px 0 12px;
        background: #fff;
    }
    .rd-row {
        display: grid;
        grid-template-columns: 140px minmax(0, 1fr);
        gap: 14px;
        align-items: start;
        padding: 12px 22px;
        border-bottom: 1px solid #e6efeb;
    }
    .rd-row:nth-child(even) {
        background: #f8fbfa;
    }
    .rd-row:last-child {
        border-bottom: 0;
    }
    .rd-row-label {
        font-size: 0.78rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #5f857a;
        padding-top: 2px;
    }
    .rd-row-value {
        font-size: 0.98rem;
        font-weight: 650;
        color: #1e293b;
        word-break: break-word;
    }
    .rd-row-value--type {
        color: #0f766e;
        font-weight: 800;
        font-size: 1.05rem;
    }
    .rd-row-value--muted {
        color: #8aa399;
        font-weight: 500;
        font-style: italic;
    }
    .rd-row-value--block {
        white-space: pre-wrap;
        font-weight: 500;
        color: #334155;
        line-height: 1.5;
    }
    .rd-row-note {
        margin-top: 5px;
        font-size: 0.86rem;
        font-weight: 500;
        color: #5f857a;
    }
    .rd-messages {
        padding: 10px 22px 6px;
    }
    .rd-messages a {
        display: inline-flex;
        align-items: center;
        padding: 8px 14px;
        border-radius: 8px;
        background: #ecfdf5;
        border: 1px solid #86efac;
        color: #047857;
        font-weight: 700;
        font-size: 0.88rem;
        text-decoration: none;
    }
    .rd-messages a:hover {
        background: #d1fae5;
        color: #065f46;
    }

    /* Calendar modal compatibility */
    .rd-badge {
        display: inline-block;
        min-width: 5.5rem;
        padding: 6px 11px;
        border-radius: 999px;
        font-weight: 750;
        font-size: 0.76rem;
        text-align: center;
        color: #fff;
    }
    .rd-badge--pending { background: #C291A4 !important; }
    .rd-badge--approved { background: #138056 !important; }
    .rd-badge--rejected { background: #b91c1c !important; }
    .rd-badge--withdrawn { background: #D2B55B !important; }
    .rd-badge--overdue { background: #C154C1 !important; }
    .rd-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 14px;
    }
    .rd-stat {
        background: #f4faf7;
        border: 1px solid #d5e6df;
        border-radius: 10px;
        padding: 10px 12px;
    }
    .rd-stat-label {
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b8f84;
        margin-bottom: 3px;
    }
    .rd-stat-value {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e293b;
        word-break: break-word;
    }
    .rd-stat-value--type { color: #0f766e; }
    .rd-section { margin-bottom: 12px; }
    .rd-section-title {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin: 0 0 6px;
    }
    .rd-box {
        font-size: 0.9rem;
        color: #334155;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .rd-box--muted { color: #94a3b8; font-style: italic; }
    .rd-box--rejection {
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 10px 12px;
        color: #166534;
    }
    .rd-box--info {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 8px;
        padding: 10px 12px;
        color: #92400e;
    }

    .rd-files {
        display: flex;
        flex-direction: column;
        gap: 8px;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .rd-file-item {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 12px;
        background: #f4faf7;
        border: 1px solid #d5e6df;
        border-radius: 10px;
    }
    .rd-file-name {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 700;
        font-size: 0.86rem;
        color: #1e293b;
    }
    .rd-file-name svg {
        width: 16px;
        height: 16px;
        color: #0f766e;
        flex-shrink: 0;
    }
    .rd-file-actions {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .rd-file-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 11px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.78rem;
        text-decoration: none;
        border: 1px solid #86efac;
        cursor: pointer;
        background: #ecfdf5;
        color: #047857;
    }
    .rd-file-btn:hover {
        background: #d1fae5;
        color: #065f46;
    }
    .rd-file-btn--download {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #1d4ed8;
    }
    .rd-file-btn--download:hover {
        background: #dbeafe;
        color: #1e3a8a;
    }

    .attachment-preview-popup.swal2-popup {
        padding: 0 !important;
        border-radius: 18px !important;
        border: 2px solid #8fb3a8 !important;
        box-shadow: 0 22px 48px rgba(15, 61, 46, 0.16) !important;
        overflow: hidden !important;
        max-width: 920px !important;
        width: 100% !important;
    }
    .attachment-preview-popup.swal2-popup::before {
        content: "";
        display: block;
        height: 4px;
        background: linear-gradient(90deg, #0f766e 0%, #16a085 45%, #b2cd16 100%);
    }
    .attachment-preview-popup .swal2-title {
        padding: 16px 20px 8px !important;
        margin: 0 !important;
        font-size: 1.15rem !important;
        font-weight: 750 !important;
        color: #1b4d42 !important;
        text-align: left !important;
    }
    .attachment-preview-popup .swal2-html-container {
        margin: 0 !important;
        padding: 0 18px 12px !important;
        overflow: visible !important;
    }
    .attachment-preview-popup .swal2-actions {
        margin: 0 !important;
        padding: 12px 18px 16px !important;
        border-top: 1px solid #c5d9d1;
        background: linear-gradient(180deg, #f4faf7 0%, #eef6f3 100%);
        gap: 10px;
    }
    .attachment-preview-popup .swal2-close {
        top: 12px !important;
        right: 12px !important;
        color: #5f857a !important;
    }
    .attachment-preview-popup .attachment-preview-btn-download,
    .attachment-preview-popup .swal2-confirm {
        background: #b2cd16 !important;
        color: #0a1347 !important;
        border: 1px solid #91bf14 !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        box-shadow: none !important;
    }
    .attachment-preview-popup .attachment-preview-btn-open,
    .attachment-preview-popup .swal2-deny {
        background: #0f766e !important;
        color: #fff !important;
        border: 1px solid #0d9488 !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        box-shadow: none !important;
    }
    .attachment-preview-shell { width: 100%; }
    .attachment-preview-meta {
        margin: 0 0 10px;
        font-size: 0.82rem;
        color: #5f857a;
        word-break: break-all;
    }
    .attachment-preview-wrap {
        width: 100%;
        min-height: 280px;
        background: #f7fbf9;
        border: 1px solid #d5e6df;
        border-radius: 12px;
        overflow: auto;
        padding: 8px;
    }
    .attachment-preview-frame {
        width: 100%;
        height: min(70vh, 640px);
        border: 0;
        border-radius: 8px;
        background: #fff;
    }
    .attachment-preview-image {
        display: block;
        max-width: 100%;
        max-height: min(70vh, 640px);
        margin: 0 auto;
        border-radius: 8px;
    }
    .attachment-preview-text {
        margin: 0;
        padding: 14px;
        white-space: pre-wrap;
        word-break: break-word;
        font-family: Consolas, "Courier New", monospace;
        font-size: 0.88rem;
        color: #1e293b;
        background: #fff;
        border-radius: 8px;
    }
    .attachment-preview-doc {
        padding: 16px 18px;
        background: #fff;
        border-radius: 8px;
        color: #1e293b;
        line-height: 1.55;
        font-size: 0.95rem;
    }
    .attachment-preview-doc img {
        max-width: 100%;
        height: auto;
    }
    .attachment-preview-note {
        margin: 0;
        padding: 18px 16px;
        color: #475569;
        font-size: 0.94rem;
        line-height: 1.5;
        text-align: center;
    }
    .attachment-preview-note--error {
        color: #991b1b;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
    }
    .attachment-preview-container { z-index: 10200 !important; }

    @media (max-width: 768px) {
        .request-details-popup.swal2-popup {
            max-width: calc(100vw - 16px) !important;
            width: calc(100vw - 16px) !important;
            border-radius: 14px !important;
        }
        .attachment-preview-popup.swal2-popup {
            max-width: calc(100vw - 16px) !important;
            width: calc(100vw - 16px) !important;
        }
        .rd-header,
        .rd-employee {
            padding-left: 16px;
            padding-right: 16px;
        }
        .rd-row {
            grid-template-columns: 1fr;
            gap: 3px;
            padding-left: 16px;
            padding-right: 16px;
        }
        .rd-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endonce
