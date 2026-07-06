@once
<style>
    .requests-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 4px 32px;
        color: #1e293b;
    }
    .requests-page h1 {
        font-weight: 700;
        font-size: 40px;
        color: #2c3e50;
        margin: 0 0 8px;
        letter-spacing: 0.04em;
    }
    .requests-page-lead {
        margin: 0 0 18px;
        color: #64748b;
        font-size: 0.92rem;
    }
    .requests-page-lead strong {
        color: #138056;
    }
    .requests-flash {
        margin-bottom: 16px;
        padding: 12px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.92rem;
    }
    .requests-flash--success {
        background: #ecfdf5;
        border: 1px solid #86efac;
        color: #065f46;
    }
    .requests-flash--error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .requests-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding: 14px 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }
    .requests-toolbar-left {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }
    .requests-toolbar-right {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-left: auto;
    }
    .requests-search-form {
        display: flex;
        flex-wrap: nowrap;
        gap: 8px;
        align-items: center;
    }
    .requests-search-form input[type="text"] {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.92rem;
        width: auto;
        flex: 1 1 280px;
        min-width: 240px;
        background: #fff;
    }
    .requests-search-form input[type="text"]:focus {
        outline: none;
        border-color: #138056;
        box-shadow: 0 0 0 3px rgba(19, 128, 86, 0.12);
    }
    .requests-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px;
        font-weight: 700;
        font-size: 0.88rem;
        border-radius: 8px;
        cursor: pointer;
        border: 1px solid #91bf14;
        text-decoration: none;
        background: #b2cd16;
        color: #0a1347;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .requests-btn:hover,
    .requests-btn--primary:hover,
    .requests-btn--secondary:hover,
    .requests-btn--search:hover {
        background: #9eb812;
        color: #0a1347;
    }
    .requests-filters {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-bottom: 16px;
        padding: 4px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        max-width: 100%;
    }
    .requests-filter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.86rem;
        text-decoration: none;
        color: #64748b;
        background: transparent;
        border: none;
        transition: background-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
        white-space: nowrap;
    }
    .requests-filter:hover {
        color: #334155;
        background: rgba(255, 255, 255, 0.65);
    }
    .requests-filter.is-active {
        color: #fff;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.12);
    }
    .requests-filter--all.is-active { background: #00C0A3; }
    .requests-month-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px 6px 12px;
        border-radius: 999px;
        background: #ecfdf5;
        border: 1px solid #86efac;
        color: #065f46;
        font-size: 0.82rem;
        font-weight: 700;
    }
    .requests-month-chip a {
        color: #047857;
        text-decoration: none;
        font-size: 1rem;
        line-height: 1;
        padding: 0 2px;
    }
    .requests-month-chip a:hover {
        color: #065f46;
    }
    .requests-table-wrap {
        overflow-x: auto;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        box-shadow: 0 6px 22px rgba(15, 61, 46, 0.06);
        background: #fff;
    }
    .requests-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.92rem;
        min-width: 760px;
    }
    .requests-table th,
    .requests-table td {
        padding: 12px 14px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .requests-table th {
        background: #f8fafc;
        font-weight: 700;
        color: #334155;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .requests-table tbody tr:last-child td {
        border-bottom: none;
    }
    .requests-table tbody tr.row-click {
        cursor: pointer;
        transition: background-color 0.12s ease;
    }
    .requests-table tbody tr.row-click:hover {
        background: #f8fafc;
    }
    .requests-table tbody tr.overdue-row {
        background: #faf5fb;
    }
    .requests-table tbody tr.overdue-row:hover {
        background: #f3e8f8;
    }
    .requests-empty {
        text-align: center;
        padding: 36px 20px;
        color: #64748b;
        font-style: italic;
    }
    .requests-hint {
        margin: 12px 0 0;
        color: #94a3b8;
        font-size: 0.82rem;
    }
    .requests-pagination {
        margin-top: 18px;
    }
    .requests-pagination .pagination {
        margin-bottom: 0;
    }
    .requests-pagination .page-link {
        padding: 6px 10px;
        font-weight: 700;
        font-size: 0.88rem;
    }
    .request-badge {
        display: inline-block;
        min-width: 5.5rem;
        padding: 5px 10px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.78rem;
        text-align: center;
        color: #fff;
        border: none;
        letter-spacing: 0.01em;
        line-height: 1.25;
    }
    .requests-table td.request-actions-cell {
        vertical-align: middle;
    }
    .request-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        min-height: 0;
    }
    .request-actions form {
        display: inline-flex;
        margin: 0;
        align-items: center;
    }
    .request-actions-note {
        font-weight: 800;
        color: #9d174d;
        line-height: 1.35;
    }
    .request-actions-inline {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    .request-actions-inline form {
        display: inline-flex;
        margin: 0;
        align-items: center;
    }
    .request-link {
        font-weight: 700;
        color: #0f766e;
        text-decoration: underline;
    }
    .request-link:hover {
        color: #0d655e;
    }
    .request-btn {
        padding: 6px 14px;
        font-weight: 700;
        font-size: 0.82rem;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        vertical-align: middle;
        line-height: 1.35;
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }
    .request-btn--approve,
    .request-btn--escalate {
        border: 1px solid #91bf14;
        background: #b2cd16;
        color: #0a1347;
    }
    .request-btn--approve:hover,
    .request-btn--escalate:hover {
        background: #9eb812;
        color: #0a1347;
    }
    .request-btn--reject {
        background: #dc2626;
        border: 1px solid #b91c1c;
        color: #fff;
    }
    .request-btn--reject:hover {
        background: #b91c1c;
        color: #fff;
    }
    .request-btn--delete {
        background: #dc2626;
        border-color: #b91c1c;
        color: #fff;
    }
    .request-btn--delete:hover {
        background: #b91c1c;
        color: #fff;
    }
    .request-btn--withdraw {
        background: #b45309;
        border-color: #92400e;
        color: #fff;
    }
    .request-btn--withdraw:hover {
        background: #92400e;
        color: #fff;
    }
    .request-btn--view {
        background: #ecfdf5;
        border-color: #86efac;
        color: #047857;
    }
    .request-btn--view:hover {
        background: #d1fae5;
        color: #065f46;
    }
    .request-existing-files {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 6px;
        width: 100%;
    }
    .request-status-cell {
        min-width: 120px;
    }
    .request-status-cell .request-badge {
        display: inline-block;
        margin-top: 0;
        margin-bottom: 4px;
    }
    .request-status-cell .request-badge:last-child {
        margin-bottom: 0;
    }
    .request-badge--letter-required {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }
    .request-badge--letter-missing {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
    .request-badge--manager {
        background: #ede9fe;
        color: #5b21b6;
        border: 1px solid #c4b5fd;
        font-size: 0.72rem;
        vertical-align: middle;
        min-width: 0;
        margin-left: 6px;
        padding: 3px 8px;
    }
    .proof-upload-form {
        margin: 0;
    }
    .proof-upload-input {
        display: none;
    }
    .proof-upload-modal-note {
        margin: 0 0 8px;
        font-size: 0.92rem;
        color: #475569;
        line-height: 1.45;
    }
    @media (max-width: 768px) {
        .requests-page h1 {
            font-size: 28px;
        }
        .requests-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .requests-toolbar-right {
            margin-left: 0;
            width: 100%;
        }
        .requests-search-form {
            width: 100%;
            flex-wrap: wrap;
        }
        .requests-search-form input[type="text"] {
            flex: 1;
            min-width: 0;
        }
    }
</style>
@endonce
