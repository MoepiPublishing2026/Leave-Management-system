@once
<style>
    /* Above fixed navbar (z-index 10000+) */
    .request-details-container.swal2-container {
        z-index: 10150 !important;
        padding: 88px 12px 16px !important;
        align-items: flex-start !important;
        justify-content: center !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    .request-details-container .swal2-popup.request-details-popup {
        transform: none !important;
        margin: 0 auto 16px !important;
    }
    .request-details-popup.swal2-popup {
        padding: 0 !important;
        border-radius: 18px !important;
        border: 1px solid #dbe3ef !important;
        box-shadow: 0 24px 48px rgba(15, 61, 46, 0.18) !important;
        overflow: visible !important;
        max-width: 640px !important;
        width: 100%;
        display: flex !important;
        flex-direction: column !important;
    }
    .request-details-popup .swal2-icon,
    .request-details-popup .swal2-close {
        display: none !important;
    }
    .request-details-popup .swal2-title {
        display: none !important;
    }
    .request-details-popup .swal2-html-container {
        margin: 0 !important;
        padding: 0 !important;
        text-align: left !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
        flex: 1 1 auto !important;
        max-height: min(72vh, 720px) !important;
    }
    .request-details-popup .swal2-actions {
        margin: 0 !important;
        padding: 14px 20px 18px !important;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        width: 100%;
        box-sizing: border-box;
        flex-shrink: 0;
    }
    .request-details-popup .swal2-confirm {
        background: #138056 !important;
        border-radius: 10px !important;
        font-weight: 700 !important;
        padding: 10px 28px !important;
        box-shadow: none !important;
    }
    .request-details-popup .swal2-confirm:focus {
        box-shadow: 0 0 0 3px rgba(19, 128, 86, 0.25) !important;
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
        gap: 12px;
        flex-wrap: wrap;
        padding: 20px 22px 16px;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f8fafc 100%);
        border-bottom: 1px solid #d1fae5;
    }
    .rd-header-id {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
    }
    .rd-header-id span {
        color: #138056;
    }
    .rd-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .rd-badge--pending { background: #fef9c3; color: #854d0e; border: 1px solid #facc15; }
    .rd-badge--approved { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
    .rd-badge--rejected { background: #ccfbf1; color: #0f766e; border: 1px solid #2fcfc5; }
    .rd-badge--withdrawn { background: #ccfbf1; color: #0f766e; border: 1px solid #2fcfc5; }
    .rd-badge--overdue { background: #fce7f3; color: #9d174d; border: 1px solid #d946ef; }

    .rd-employee {
        padding: 14px 22px;
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
    }
    .rd-employee-name {
        font-weight: 800;
        font-size: 1rem;
        color: #0f172a;
        margin: 0 0 4px;
    }
    .rd-employee-meta {
        font-size: 0.86rem;
        color: #64748b;
        margin: 0;
    }

    .rd-body {
        padding: 18px 22px 6px;
    }
    .rd-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }
    @media (min-width: 520px) {
        .rd-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .rd-stats--employee {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    .rd-stat {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
    }
    .rd-stat-label {
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94a3b8;
        margin-bottom: 4px;
    }
    .rd-stat-value {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        word-break: break-word;
    }
    .rd-stat-value--type {
        color: #138056;
    }

    .rd-section {
        margin-bottom: 14px;
    }
    .rd-section-title {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin: 0 0 8px;
    }
    .rd-box {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 0.9rem;
        color: #334155;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .rd-box--muted {
        color: #94a3b8;
        font-style: italic;
    }
    .rd-box--rejection {
        background: #ecfdf5;
        border-color: #99f6e4;
        color: #0f766e;
    }
    .rd-box--info {
        background: #fff7ed;
        border-color: #fed7aa;
        color: #7c2d12;
    }

    .rd-files {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .rd-file-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: #ecfdf5;
        border: 1px solid #86efac;
        border-radius: 10px;
        color: #047857;
        font-weight: 700;
        font-size: 0.84rem;
        text-decoration: none;
        transition: background 0.15s ease;
    }
    .rd-file-link:hover {
        background: #d1fae5;
        color: #065f46;
    }
    .rd-file-link svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    .rd-handled {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 14px;
        padding: 12px;
        background: #f1f5f9;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
    .rd-handled-item {
        font-size: 0.82rem;
    }
    .rd-handled-item strong {
        display: block;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        margin-bottom: 2px;
    }

    .rd-footer-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 4px;
        padding: 10px 16px;
        background: #138056;
        color: #fff !important;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.88rem;
        text-decoration: none !important;
        transition: background 0.15s ease;
    }
    .rd-footer-link:hover {
        background: #0f6b47;
        color: #fff !important;
    }

    @media (max-width: 768px) {
        .request-details-container.swal2-container {
            padding: 76px 8px 12px !important;
        }
        .request-details-popup.swal2-popup {
            max-width: calc(100vw - 16px) !important;
            width: calc(100vw - 16px) !important;
            border-radius: 14px !important;
        }
        .request-details-popup .swal2-html-container {
            max-height: min(65vh, 560px) !important;
        }
        .request-details-popup .swal2-actions {
            padding: 12px 16px 14px !important;
        }
        .rd-header {
            padding: 16px 16px 12px;
        }
        .rd-employee,
        .rd-body {
            padding-left: 16px;
            padding-right: 16px;
        }
        .rd-handled {
            grid-template-columns: 1fr;
        }
    }
</style>
@endonce
