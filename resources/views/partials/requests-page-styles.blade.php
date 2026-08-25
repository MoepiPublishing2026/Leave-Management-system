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
    .requests-filter-bar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .requests-filters {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 4px;
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
    .requests-filter--pending_manager.is-active {
        background: #7c3aed;
        color: #fff !important;
    }
    .requests-filter--pending_senior.is-active {
        background: #6d28d9;
        color: #fff !important;
    }
    .requests-pay-filter {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 4px 4px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
    }
    .requests-pay-filter label {
        margin: 0;
        color: #475569;
        font-size: 0.82rem;
        font-weight: 700;
    }
    .requests-pay-filter select {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #334155;
        padding: 7px 28px 7px 10px;
        font-size: 0.84rem;
        font-weight: 600;
        cursor: pointer;
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
        position: relative;
        overflow-x: auto;
        border: 2px solid #8fb3a8;
        border-radius: 18px;
        box-shadow:
            0 18px 45px rgba(15, 61, 46, 0.10),
            0 2px 8px rgba(15, 23, 42, 0.05);
        background: #d3e5df;
    }
    .requests-table-wrap::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        z-index: 3;
        background: linear-gradient(90deg, #0f766e 0%, #16a085 45%, #b2cd16 100%);
    }
    .requests-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.92rem;
        min-width: 760px;
    }
    .requests-table th,
    .requests-table td {
        padding: 15px 16px;
        text-align: left;
        border-bottom: 1px solid #a9c6bc;
        border-right: 1px solid #a9c6bc;
        vertical-align: middle;
    }
    .requests-table th:last-child,
    .requests-table td:last-child {
        border-right: none;
    }
    .requests-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: linear-gradient(135deg, #123f3a 0%, #0f766e 100%);
        font-weight: 800;
        color: #fff;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.075em;
        white-space: nowrap;
        border-bottom-color: #0b5f58;
        border-right-color: rgba(255, 255, 255, 0.22);
        box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.08);
    }
    .requests-table th:first-child {
        padding-left: 20px;
    }
    .requests-table td:first-child {
        padding-left: 20px;
        color: #0f766e;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }
    .requests-table td:nth-child(2) {
        color: #173f3a;
        font-weight: 700;
    }
    .requests-table tbody tr {
        background: #d3e5df;
    }
    .requests-table tbody tr:nth-child(even):not(.overdue-row) {
        background: #d3e5df;
    }
    .requests-table tbody tr:last-child td {
        border-bottom: none;
    }
    .requests-table tbody tr.row-click {
        cursor: pointer;
        transition: background-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
    }
    .requests-table tbody tr.row-click:hover {
        position: relative;
        z-index: 1;
        background: #bfd9d0;
        box-shadow: inset 4px 0 0 #16a085, 0 8px 22px rgba(15, 118, 110, 0.08);
        transform: translateY(-1px);
    }
    .requests-table tbody tr.row-click:focus-within {
        background: #bfd9d0;
        box-shadow: inset 4px 0 0 #16a085;
    }
    .requests-table tbody tr.overdue-row {
        background: #f2d7e6;
    }
    .requests-table tbody tr.overdue-row:hover {
        background: #eac3d8;
        box-shadow: inset 4px 0 0 #be185d, 0 8px 22px rgba(190, 24, 93, 0.08);
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
        padding: 6px 11px;
        border-radius: 999px;
        font-weight: 750;
        font-size: 0.76rem;
        text-align: center;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.28);
        letter-spacing: 0.01em;
        line-height: 1.25;
        box-shadow: 0 3px 8px rgba(15, 23, 42, 0.12);
    }
    .requests-table td.request-actions-cell {
        vertical-align: middle;
        min-width: 220px;
    }
    .request-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        min-height: 0;
    }
    .request-actions > a,
    .request-actions > form,
    .request-actions > button,
    .request-actions > span {
        flex: 0 0 auto;
        margin: 0;
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
    .request-actions-submitted {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 800;
        color: #166534;
        background: #dcfce7;
        border: 1px solid #86efac;
        border-radius: 999px;
        padding: 6px 12px;
        line-height: 1.2;
        font-size: 0.85rem;
    }
    .request-actions-submitted svg {
        flex-shrink: 0;
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
        padding: 7px 14px;
        font-weight: 700;
        font-size: 0.82rem;
        border-radius: 999px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        vertical-align: middle;
        line-height: 1.2;
        white-space: nowrap;
        box-sizing: border-box;
        box-shadow: 0 3px 8px rgba(15, 23, 42, 0.08);
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
    }
    .request-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.14);
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
    .request-btn--edit {
        border: 1px solid #91bf14;
        background: #b2cd16;
        color: #0a1347;
    }
    .request-btn--edit:hover {
        background: #9eb812;
        color: #0a1347;
    }
    .request-existing-files {
        display: contents;
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
        display: inline-flex;
        align-items: center;
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
