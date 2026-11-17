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
        font-size: 52px;
        color: #263248;
        margin: 0 0 8px;
        letter-spacing: 0.02em;
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
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        width: 100%;
        box-sizing: border-box;
        margin: 0 0 14px;
        padding: 0;
        background: none;
        border: none;
        border-radius: 0;
        box-shadow: none;
    }
    .requests-toolbar-left {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        flex-shrink: 0;
    }

    .requests-toolbar-right {
        display: flex;
        align-items: center;
        margin-left: auto;
        min-width: 0;
    }
    .requests-search-form {
        position: absolute;
        top: 30px;
        right: 200px;
        z-index: 5;
        display: flex;
        align-items: center;
        width: 360px;
        max-width: calc(100% - 40px);
        margin: 0;
        padding: 0;
        gap: 0;
        
    }
    .requests-search-form input[type="text"] {
        width: 100%;
        min-width: 0;
        height: 45px;
        box-sizing: border-box;
        padding: 12px 18px 12px 68px;
        border: none;
        border-radius: 24px;
        background-color: #e7f2f2;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23a2b222' stroke-width='2.2' stroke-linecap='round'><circle cx='11' cy='11' r='7'/><path d='M16 16l5 5'/></svg>");
        background-repeat: no-repeat;
        background-position: 24px center;
        background-size: 28px 28px;
        font-size: 18px;
        line-height: 1.4;
    }
    .requests-search-form input[type="text"]:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(19, 128, 86, 0.12);
    }
  
    .requests-toolbar-1 {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
        min-height: 76px;
        box-sizing: border-box;
        padding: 10px 20px 10px 12px;
        margin-bottom: 16px;
        background: #d9e2e2;
        border-radius: 15px;
    }

    .requests-toolbar-1 .requests-toolbar-left-1 {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        flex-shrink: 0;
    }

    .requests-toolbar-1 .requests-btn--primary-1 {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 176px;
        height: 54px;
        padding: 0 24px;
        box-sizing: border-box;
        background: #D5E11B;
        color: #000;
        border: none;
        border-radius: 16px;
        font-size: 15px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        visibility: visible;
        opacity: 1;
    }

    .requests-toolbar-right-1 {
        display: flex;
        align-items: center;
        margin-left: auto;
        min-width: 0;
    }

    .requests-search-form-1 {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 15px;
        width: 100%;
        margin: 0;
    }

    .requests-search-form-1 input[type="text"] {
        width: 316px;
        height: 54px;
        box-sizing: border-box;
        padding: 0 14px;
        border: none;
        outline: none;
        border-radius: 15px;
        background: #fff;
        color: #333;
        font-family: inherit;
        font-size: 16px;
    }

    .requests-search-form-1 input[type="text"]::placeholder {
        color: #777;
        opacity: 1;
    }

    .requests-search-form-1 input[type="text"]:focus {
        box-shadow: 0 0 0 2px rgba(216, 230, 0, 0.45);
    }

    .requests-toolbar-1 .requests-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 54px;
        box-sizing: border-box;
        padding: 0 24px;
        border: none;
        border-radius: 16px;
        background: #D5E11B;
        color: #000;
        font-family: inherit;
        font-size: 15px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .requests-toolbar-1 .requests-btn:hover {
        background: #D5E11B;
        color: #000;
    }
   
    .requests-toolbar-1 .requests-btn--primary {
        min-width: 176px;
        height: 54px;;
    }

    .requests-toolbar-1 .requests-btn--search {
        min-width: 120px;
         height: 54px;
    }

    .requests-toolbar-1 .requests-btn--secondary {
        min-width: 90px;
         height: 54px;
    }

   
    .requests-toolbar-1 .requests-btn:focus-visible {
        outline: 2px solid #333;
        outline-offset: 3px;
    }
    


    html body main .requests-btn,
    html body main form .requests-btn,
    html body main form button.requests-btn[type="submit"] {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 14px !important;
        min-height: 48px !important;
        min-width: 0 !important;
        height: auto !important;
        font-weight: 700;
        font-size: 13px !important;
        line-height: 1.1 !important;
        border-radius: 10px !important;
        cursor: pointer;
        border: none;
        text-decoration: none;
        background: #a2b222;
        color: #000;
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
        gap: 20px;
        padding: 12px 20px;
        background: #d8e3e1;
        border: none;
        border-radius: 14px;
        max-width: 100%;
    }
    .requests-filter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.92rem;
        text-decoration: none;
        color: #263248;
        background: transparent;
        border: none;
        box-shadow: none;
        transition: color 0.15s ease;
        white-space: nowrap;
    }
    .requests-filter:hover {
        color: #2379b0;
        background: transparent;
    }
    .requests-filter.is-active {
        color: #2379b0;
        box-shadow: none;
        background: transparent;
    }
    .requests-filter--all.is-active,
    .requests-filter--pending_manager.is-active,
    .requests-filter--pending_senior.is-active {
        background: transparent;
        color: #2379b0 !important;
    }
    .requests-pay-filter {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 4px 4px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #d8e3e1;
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
        overflow: visible;
        border: none;
        border-radius: 0;
        box-shadow: none;
        background: transparent;
    }
    .requests-table-wrap::before,
    .requests-table-wrap::after {
        display: none;
    }
    .requests-table,
    .requests-table tbody {
        display: block;
        width: 100%;
        min-width: 0;
        border: none;
        background: transparent;
        box-shadow: none;
    }
    .requests-table thead {
        display: none;
    }
    .requests-table tr.row-click {
        position: relative;
        display: grid;
        grid-template-columns: max-content max-content minmax(0, 1fr);
        column-gap: 0;
        row-gap: 4px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 14px;
        padding: 18px 24px 18px 30px;
        margin: 0 0 26px;
        box-shadow: 6px 8px 14px rgba(0, 0, 0, 0.22);
        cursor: pointer;
    }
    .requests-table tr.row-click td {
        display: block;
        border: none;
        background: transparent;
        padding: 2px 0;
        text-align: left;
        color: #444;
        vertical-align: middle;
    }
    html body .requests-page .requests-table tr.row-click td:nth-child(1) {
        position: absolute;
        top: 6px;
        left: 12px;
        padding: 0;
        font-size: 0.8rem;
        font-weight: 800 !important;
        color: #2379b0 !important;
    }
    .requests-table td:nth-child(2) {
        grid-column: 1 / 3;
        grid-row: 1;
        font-size: 1.5rem;
        font-weight: 700;
        color: #555;
        text-shadow: none;
    }
    .requests-table td:nth-child(7) {
        grid-column: 3;
        grid-row: 1;
        justify-self: end;
    }
    .requests-table td:nth-child(3) {
        grid-column: 1 / -1;
        grid-row: 2;
        font-weight: 700;
    }
    .requests-table td:nth-child(3)::before {
        content: 'Requested by:\00a0';
        display: inline-block;
        padding-left: 28px;
        margin-right: 4px;
        font-weight: 400;
        background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='1.8' stroke-linecap='round'><circle cx='12' cy='8' r='4'/><path d='M4 21c0-4 4-6 8-6s8 2 8 6'/></svg>") no-repeat left center / 20px 20px;
    }
    .requests-table td:nth-child(6) {
        grid-column: 1 / -1;
        grid-row: 3;
    }
    .requests-table td:nth-child(6)::before {
        content: 'Submitted\00a0';
        display: inline-block;
        padding-left: 28px;
        margin-right: 4px;
        background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='1.8' stroke-linecap='round'><rect x='3' y='5' width='18' height='16' rx='2'/><path d='M3 10h18M8 3v4M16 3v4'/></svg>") no-repeat left center / 20px 20px;
    }
    .requests-table td:nth-child(4) {
        grid-column: 1;
        grid-row: 4;
    }
    .requests-table td:nth-child(4)::before {
        content: '';
        display: inline-block;
        width: 20px;
        height: 20px;
        margin-right: 8px;
        vertical-align: -4px;
        background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='1.8' stroke-linecap='round'><circle cx='12' cy='12' r='9'/><path d='M12 7v5l3 2'/></svg>") no-repeat center / 20px 20px;
    }
    .requests-table td:nth-child(4)::after {
        content: '\00a0-\00a0';
    }
    .requests-table td:nth-child(5) {
        grid-column: 2;
        grid-row: 4;
    }
    .requests-table td:nth-child(8) {
        grid-column: 3;
        grid-row: 4;
        justify-self: stretch;
        width: 100%;
        min-width: 0;
        text-align: right;
    }
    .requests-table tr:not(.row-click),
    .requests-table tr:not(.row-click) td {
        display: block;
        background: transparent;
        border: none;
    }
    .requests-table tbody tr.row-click:hover {
        transform: translateY(-1px);
    }
    .requests-empty,
    .requests-table td.requests-empty {
        position: static;
        display: block;
        padding: 26px 0;
        font-size: 1rem;
        font-weight: 700;
        font-style: italic;
        color: #263248;
        text-align: left;
        background: transparent;
        border: none;
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
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
        padding: 4px 14px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 0.8rem;
        text-align: center;
        color: #263248;
        border: 2px solid #666;
        letter-spacing: 0;
        line-height: 1.25;
        box-shadow: none;
    }
    html body .requests-page .request-badge svg,
    html body .requests-page .request-btn svg,
    html body .requests-page .request-link svg,
    html body .requests-page .request-actions-submitted svg {
        width: 16px;
        height: 16px;
        flex: none;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    html body .requests-page .request-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .request-badge--approved {
        background: #9db4ff !important;
        border-color: #2a6fc0 !important;
        color: #1f3f8f !important;
    }
    .request-badge--pending {
        background: #cebaf6 !important;
        border-color: #a33d86 !important;
        color: #7a2a7a !important;
    }
    .request-badge--overdue {
        background: #cebaf6 !important;
        border-color: #a33d86 !important;
        color: #fff !important;
    }
    .request-badge--rejected {
        background: #f9c3c9 !important;
        border-color: #d5253a !important;
        color: #a31b2e !important;
    }
    .request-badge--withdrawn {
        background: #e2e2e2 !important;
        border-color: #767672 !important;
        color: #555 !important;
    }
    .requests-table td.request-actions-cell {
        vertical-align: middle;
        min-width: 220px;
    }
    .request-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        min-height: 0;
        width: 100%;
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
        color: #2379b0;
        text-decoration: none;
        border-bottom: 3px solid #8fa4f5;
        padding-bottom: 2px;
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
        background: #c91919;
        border: 2px solid #f70606;
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
    .swal2-popup.swal-approve-popup {
        background: #f8fafc !important;
        border-radius: 16px !important;
        padding: 28px 24px 22px !important;
        color: #1e293b !important;
    }
    .swal2-popup.swal-approve-popup .swal-approve-title {
        color: #0f766e !important;
        font-size: 1.35rem !important;
        font-weight: 700 !important;
    }
    .swal2-popup.swal-approve-popup .swal-approve-html {
        color: #475569 !important;
        font-size: 0.95rem !important;
        margin: 0 0 8px !important;
    }
    .swal-approve-copy {
        margin: 0 0 4px;
        color: #475569;
        text-align: left;
        line-height: 1.5;
    }
    .swal-approve-notes {
        margin: 0 0 12px;
        padding: 10px 12px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: #334155;
        text-align: left;
    }
    .swal-approve-notes p {
        margin: 0 0 6px;
        line-height: 1.45;
    }
    .swal-approve-notes p:last-child {
        margin-bottom: 0;
    }
    .swal2-popup.swal-approve-popup .swal-approve-input {
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        box-shadow: none !important;
        background: #ffffff !important;
        color: #1e293b !important;
        min-height: 92px;
    }
    .swal2-popup.swal-approve-popup .swal-approve-confirm {
        background: #16a34a !important;
        color: #ffffff !important;
        box-shadow: none !important;
        font-weight: 700 !important;
    }
    .swal2-popup.swal-approve-popup .swal-approve-cancel {
        background: #e2e8f0 !important;
        color: #334155 !important;
        box-shadow: none !important;
        font-weight: 600 !important;
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
        .requests-search-form {
            position: static;
            top: auto;
            right: auto;
            left: auto;
            width: 100%;
            max-width: 100%;
            flex-wrap: wrap;
        }
        .requests-search-form input[type="text"] {
            flex: 1 1 140px;
            width: 100%;
            min-width: 0;
        }
        .requests-toolbar-1,
        .requests-filter-bar,
        .requests-toolbar-right,
        .requests-toolbar-right-1,
        .requests-search-form-1 {
            flex-direction: column;
            align-items: stretch;
            width: 100%;
            margin-left: 0;
        }
        .requests-search-form-1 input[type="text"] {
            width: 100%;
        }
        .requests-filters,
        .requests-pay-filter {
            width: 100%;
            box-sizing: border-box;
        }
        .requests-table tr.row-click {
            grid-template-columns: 1fr;
            padding: 28px 12px 14px;
        }
        .requests-table tr.row-click td,
        .requests-table td:nth-child(2),
        .requests-table td:nth-child(3),
        .requests-table td:nth-child(4),
        .requests-table td:nth-child(5),
        .requests-table td:nth-child(6),
        .requests-table td:nth-child(7),
        .requests-table td:nth-child(8) {
            grid-column: 1 / -1;
            grid-row: auto;
            justify-self: start;
            min-width: 0;
        }
        .requests-table td.request-actions-cell,
        .request-actions {
            min-width: 0;
            width: 100%;
        }
    }
</style>
@endonce
