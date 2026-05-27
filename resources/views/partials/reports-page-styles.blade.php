@once
<style>
    .reports-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 4px 32px;
        color: #1e293b;
    }
    .reports-page h1 {
        font-weight: 700;
        font-size: 40px;
        color: #2c3e50;
        margin: 0 0 8px;
        padding-left: 3px;
        border-left: 6px solid #138056;
        letter-spacing: 0.04em;
    }
    .reports-page-lead {
        margin: 0 0 20px;
        color: #64748b;
        font-size: 0.92rem;
    }
    .reports-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding: 14px 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }
    .reports-toolbar-forms {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }
    .reports-filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    .reports-filter-form select,
    .reports-filter-form input[type="date"] {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.9rem;
        background: #fff;
        color: #334155;
    }
    .reports-filter-form select:focus,
    .reports-filter-form input[type="date"]:focus {
        outline: none;
        border-color: #138056;
        box-shadow: 0 0 0 3px rgba(19, 128, 86, 0.12);
    }
    .reports-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        font-weight: 700;
        font-size: 0.88rem;
        border-radius: 8px;
        cursor: pointer;
        border: none;
        text-decoration: none;
        transition: background-color 0.15s ease;
    }
    .reports-btn--primary {
        background: #138056;
        color: #fff;
    }
    .reports-btn--primary:hover {
        background: #0f6b47;
        color: #fff;
    }
    .reports-btn--secondary {
        background: #fff;
        color: #334155;
        border: 1px solid #cbd5e1;
    }
    .reports-btn--secondary:hover {
        background: #f1f5f9;
    }
    .reports-btn--export {
        background: #0f766e;
        color: #fff;
    }
    .reports-btn--export:hover {
        background: #0d655e;
        color: #fff;
    }
    .reports-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }
    .reports-stat {
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 6px 22px rgba(15, 61, 46, 0.06);
        display: flex;
        flex-direction: column;
        gap: 6px;
        border-left: 5px solid var(--stat-accent, #64748b);
    }
    .reports-stat--total { --stat-accent: #64748b; }
    .reports-stat--pending { --stat-accent: #facc15; }
    .reports-stat--overdue { --stat-accent: #d946ef; }
    .reports-stat--approved { --stat-accent: #10b981; }
    .reports-stat--rejected { --stat-accent: #2fcfc5; }
    .reports-stat-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }
    .reports-stat-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }
    .reports-stat-icon {
        font-size: 1.1rem;
        color: var(--stat-accent, #64748b);
        opacity: 0.9;
    }
    .reports-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 12px;
    }
    .reports-table-wrap {
        overflow-x: auto;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        box-shadow: 0 6px 22px rgba(15, 61, 46, 0.06);
        background: #fff;
    }
    .reports-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
        min-width: 720px;
    }
    .reports-table th,
    .reports-table td {
        padding: 12px 14px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }
    .reports-table th {
        background: #f8fafc;
        font-weight: 700;
        color: #334155;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .reports-table tbody tr:last-child td {
        border-bottom: none;
    }
    .reports-table tbody tr:hover {
        background: #f8fafc;
    }
    .reports-empty {
        text-align: center;
        padding: 32px 16px;
        color: #64748b;
        font-style: italic;
    }
    .report-status {
        display: inline-block;
        padding: 5px 11px;
        border-radius: 999px;
        font-weight: 800;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .report-status--pending { background: #fef3c7; color: #92400e; border: 1px solid #fbbf24; }
    .report-status--overdue { background: #fce7f3; color: #9d174d; border: 1px solid #f472b6; }
    .report-status--approved { background: #dcfce7; color: #166534; border: 1px solid #22c55e; }
    .report-status--rejected { background: #ccfbf1; color: #0f766e; border: 1px solid #2fcfc5; }
    .report-status--withdrawn { background: #ffedd5; color: #7c2d12; border: 1px solid #fb923c; }
    .report-status--default { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    @media (max-width: 768px) {
        .reports-page h1 { font-size: 28px; }
        .reports-toolbar { flex-direction: column; align-items: stretch; }
    }
</style>
@endonce
