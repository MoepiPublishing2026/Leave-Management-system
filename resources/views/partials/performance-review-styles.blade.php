@once
<style>
    .pr-page {
        font-family: Inter, "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        max-width: 980px;
        margin: 0 auto;
        padding: 8px 12px 48px;
        color: #334155;
    }
    .pr-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        justify-content: flex-end;
        margin-bottom: 16px;
    }
    .pr-toolbar h1 {
        margin: 0;
        font-size: 28px;
        color: #1e293b;
        font-weight: 700;
    }
    .pr-doc {
        background: #fff;
        border: 1px solid #dbe3ea;
        box-shadow: 0 0 12px rgba(148, 163, 184, 0.25);
        padding: 28px 32px 36px;
    }
    .pr-brand {
        display: flex;
        align-items: center;
        margin-bottom: 18px;
    }
    .pr-brand img {
        height: 64px;
        width: auto;
        max-width: 280px;
        object-fit: contain;
    }
    .pr-title {
        text-align: center;
        font-size: 32px;
        font-weight: 800;
        color: #334155;
        margin: 0 0 22px;
        letter-spacing: 0.02em;
    }
    .pr-meta {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
    }
    .pr-meta th,
    .pr-meta td {
        border: 1px solid #cbd5e1;
        padding: 0;
        vertical-align: stretch;
    }
    .pr-meta th {
        width: 22%;
        background: #4F81BD;
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        padding: 10px 12px;
        text-align: left;
        white-space: nowrap;
    }
    .pr-meta td {
        background: #fff;
        padding: 4px 8px;
    }
    .pr-meta input,
    .pr-meta select {
        width: 100%;
        border: 0;
        outline: none;
        padding: 8px 6px;
        font-size: 14px;
        background: transparent;
        font-family: inherit;
        color: #1e293b;
    }
    .pr-section {
        margin-bottom: 18px;
        border: 1px solid #cbd5e1;
    }
    .pr-section h2 {
        margin: 0;
        background: #4F81BD;
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        padding: 10px 14px;
    }
    .pr-section textarea,
    .pr-section .pr-readonly {
        width: 100%;
        min-height: 110px;
        border: 0;
        outline: none;
        padding: 12px 14px;
        font-size: 14px;
        line-height: 1.5;
        resize: vertical;
        font-family: inherit;
        box-sizing: border-box;
        white-space: pre-wrap;
    }
    .pr-split {
        display: grid;
        grid-template-columns: 1fr 220px;
        gap: 16px;
        align-items: start;
        margin: 22px 0 12px;
    }
    .pr-id-table,
    .pr-legend {
        width: 100%;
        border-collapse: collapse;
    }
    .pr-id-table th,
    .pr-legend th,
    .pr-id-table td,
    .pr-legend td {
        border: 1px solid #cbd5e1;
        padding: 8px 10px;
        font-size: 13px;
    }
    .pr-id-table th,
    .pr-legend th {
        background: #4F81BD;
        color: #fff;
        font-weight: 700;
        text-align: left;
        width: 38%;
    }
    .pr-legend th[colspan] {
        width: auto;
        text-align: center;
    }
    .pr-score-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }
    .pr-score-table th,
    .pr-score-table td {
        border: 1px solid #64748b;
        padding: 8px 6px;
        text-align: center;
        font-size: 13px;
    }
    .pr-score-table th {
        background: #4F81BD;
        color: #fff;
        font-weight: 700;
    }
    .pr-score-table td:first-child,
    .pr-score-table th:first-child {
        text-align: left;
        padding-left: 12px;
        width: 46%;
    }
    .pr-score-table input[type="radio"] {
        width: 18px;
        height: 18px;
        accent-color: #1e3a5f;
        cursor: pointer;
    }
    .pr-score-readonly {
        pointer-events: none;
        user-select: none;
    }
    .pr-score-readonly input[type="radio"] {
        cursor: default;
        accent-color: #1e3a5f;
        opacity: 1;
        filter: none;
        color-scheme: light;
    }
    .pr-submit-hint {
        margin: 16px 0 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.45;
    }
    .pr-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 22px;
    }
    .pr-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 8px;
        padding: 10px 16px;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
        cursor: pointer;
        color: #fff;
    }
    .pr-btn--primary { background: #b2cd16; color: #0a1347; border: 1px solid #91bf14; }
    .pr-btn--primary:hover { background: #9eb812; color: #0a1347; }
    .pr-btn--secondary { background: #64748b; color: #fff; }
    .pr-btn--secondary:hover { background: #475569; color: #fff; }
    .pr-btn--ghost { background: #b2cd16; color: #0a1347; border: 1px solid #91bf14; }
    .pr-btn--ghost:hover { background: #9eb812; color: #0a1347; }
    .pr-btn--draft { background: #850621; color: #fff; border: 1px solid #680523; }
    .pr-btn--draft:hover { background: #680523; color: #fff; }
    .pr-footer {
        margin-top: 28px;
        text-align: center;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.6;
    }
    .pr-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
    }
    .pr-table th,
    .pr-table td {
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 10px;
        text-align: left;
        font-size: 14px;
    }
    .pr-table th {
        color: #64748b;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .pr-empty {
        padding: 28px;
        text-align: center;
        color: #64748b;
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
    }
    .pr-filters a {
        display: inline-block;
        margin-right: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        background: #f1f5f9;
    }
    .pr-filters a.active {
        background: #138056;
        color: #fff;
    }
    .request-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }
    .request-badge--pending { background: #e0f2fe; color: #075985; }
    .request-badge--approved { background: #dcfce7; color: #166534; }
    .request-badge--withdrawn { background: #f1f5f9; color: #475569; }
    .pr-score-note {
        margin: 8px 0 0;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        color: #475569;
        font-size: 14px;
    }
    .pr-comment {
        width: 100%;
        min-height: 80px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 12px;
        font-family: inherit;
        font-size: 14px;
    }
        @media (max-width: 760px) {
        .pr-page { padding: 4px 0 32px; }
        .pr-doc { padding: 16px 12px 24px; }
        .pr-split { grid-template-columns: 1fr; }
        .pr-title { font-size: 22px; }
        .pr-brand img { height: 48px; max-width: min(200px, 70vw); }
        .pr-meta,
        .pr-meta tbody { display: block; width: 100%; }
        .pr-meta tr {
            display: block;
            width: 100%;
            margin: 0 0 10px;
            border: 1px solid #cbd5e1;
        }
        .pr-meta th,
        .pr-meta td {
            display: block;
            width: 100% !important;
            white-space: normal !important;
            box-sizing: border-box;
        }
        .pr-actions .pr-btn,
        .pr-toolbar { width: 100%; }
        .pr-btn { width: 100%; justify-content: center; box-sizing: border-box; }
    }
    @media print {
        .pr-toolbar, .pr-actions, .pr-filters, nav, header, .navbar, .mobile-menu-btn { display: none !important; }
        .pr-doc { box-shadow: none; border: 0; }
        .pr-page { max-width: none; }
    }
    .pr-reviews-page .requests-filter--draft.is-active { background: #D2B55B; }
    .pr-reviews-page .requests-filter--submitted.is-active { background: #C291A4; }
    .pr-reviews-page .requests-filter--reviewed.is-active { background: #E27A53; }
</style>
@endonce
