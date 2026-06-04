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
        padding-left: 15px;
        border-left: 6px solid #138056;
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
        border: none;
        text-decoration: none;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .requests-btn--primary {
        background: #138056;
        color: #fff;
    }
    .requests-btn--primary:hover {
        background: #0f6b47;
        color: #fff;
    }
    .requests-btn--secondary {
        background: #fff;
        color: #334155;
        border: 1px solid #cbd5e1;
    }
    .requests-btn--secondary:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .requests-btn--search {
        background: #0f766e;
        color: #fff;
    }
    .requests-btn--search:hover {
        background: #0d655e;
        color: #fff;
    }
    .requests-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }
    .requests-filter {
        display: inline-flex;
        align-items: center;
        padding: 7px 14px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.84rem;
        text-decoration: none;
        color: #475569;
        background: #fff;
        border: 1px solid #dbe3ef;
        transition: all 0.15s ease;
    }
    .requests-filter:hover {
        border-color: #138056;
        color: #138056;
    }
    .requests-filter.is-active {
        background: #138056;
        border-color: #138056;
        color: #fff;
    }
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
        min-width: 680px;
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
        background: #fff1f2;
    }
    .requests-table tbody tr.overdue-row:hover {
        background: #ffe4e6;
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
        padding: 5px 11px;
        border-radius: 999px;
        font-weight: 800;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .request-badge--pending { background: #fef3c7; color: #92400e; border: 1px solid #fbbf24; }
    .request-badge--approved { background: #dcfce7; color: #166534; border: 1px solid #22c55e; }
    .request-badge--rejected { background: #ccfbf1; color: #0f766e; border: 1px solid #2fcfc5; }
    .request-badge--withdrawn { background: #ffedd5; color: #7c2d12; border: 1px solid #fb923c; }
    .request-badge--overdue { background: #fce7f3; color: #9d174d; border: 1px solid #f472b6; }
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
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        vertical-align: middle;
        line-height: 1.35;
    }
    .request-btn--delete { background: #991b1b; color: #fff; }
    .request-btn--delete:hover { background: #7f1d1d; }
    .request-btn--withdraw { background: #b45309; color: #fff; }
    .request-btn--withdraw:hover { background: #92400e; }
    .request-btn--approve { background: #10b981; color: #fff; }
    .request-btn--approve:hover { background: #0e9b6e; }
    .request-btn--reject { background: #ef4444; color: #fff; }
    .request-btn--reject:hover { background: #dc2626; }
    .request-btn--escalate { background: #ec4899; color: #fff; }
    .request-btn--escalate:hover { background: #db2777; }
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
