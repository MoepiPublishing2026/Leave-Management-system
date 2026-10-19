@once
<style>
    .reports-page {
        --rp-text: #243044;
        --rp-muted: #6b7280;
        --rp-lime: #b2cd16;
        --rp-lime-dark: #9eb812;
        --rp-mint: #e6f4f2;
        --rp-border: #d5dbe3;
        font-family: "Poppins", "Segoe UI", sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px 4px 32px;
        color: var(--rp-text);
        box-sizing: border-box;
        overflow: visible;
    }
    .reports-page h1 {
        font-weight: 700;
        font-size: 36px;
        color: var(--rp-text);
        margin: 0 0 6px;
        letter-spacing: -0.02em;
        line-height: 1.15;
    }
    .reports-page-lead {
        margin: 0 0 22px;
        color: var(--rp-muted);
        font-size: 14px;
        font-weight: 400;
    }

    .reports-page .reports-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 16px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 22px;
        padding: 16px 20px;
        background: var(--rp-mint);
        border: 0;
        border-radius: 16px;
        overflow: visible;
        position: relative;
        z-index: 4;
    }
    .reports-page .reports-toolbar-forms {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0;
    }
    .reports-page .reports-filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        flex: 1 1 auto;
    }

    html body main .reports-page .reports-filter-form .filter-select-wrap {
        min-width: 132px !important;
        width: auto !important;
        flex: 0 0 auto !important;
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        overflow: visible;
    }
    html body main .reports-page .reports-filter-form .filter-select__trigger {
        width: auto !important;
        min-width: 132px !important;
        height: 38px !important;
        min-height: 38px !important;
        padding: 0 14px !important;
        border: 0 !important;
        border-radius: 10px !important;
        background: transparent !important;
        box-shadow: none !important;
        color: var(--rp-text) !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        flex-direction: row;
        justify-content: space-between;
        gap: 10px;
    }
    html body main .reports-page .reports-filter-form .filter-select__trigger:hover,
    html body main .reports-page .reports-filter-form .filter-select-wrap.is-open .filter-select__trigger {
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    html body main .reports-page .reports-filter-form .filter-select__menu {
        z-index: 40;
        min-width: 100%;
        width: max-content;
        max-width: 240px;
    }
    html body main .reports-page .reports-filter-form select.filter-select.filter-select__native {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        border: 0 !important;
        min-width: 0 !important;
        background: none !important;
    }

    html body main .reports-page .reports-filter-form .reports-date-input,
    html body main .reports-page .reports-filter-form .reports-date-input.flatpickr-input,
    html body main .reports-page .reports-filter-form .reports-date-input:hover,
    html body main .reports-page .reports-filter-form .reports-date-input:focus,
    html body main .reports-page .reports-filter-form .reports-date-input.flatpickr-input:hover,
    html body main .reports-page .reports-filter-form .reports-date-input.flatpickr-input:focus {
        width: 140px !important;
        min-width: 140px !important;
        height: 38px !important;
        margin: 0;
        padding: 0 14px !important;
        border: 0 !important;
        border-radius: 10px !important;
        background: #fff !important;
        font-family: inherit;
        font-size: 14px !important;
        font-weight: 500 !important;
        color: var(--rp-text) !important;
        line-height: 38px;
        box-shadow: none !important;
        outline: none !important;
        cursor: pointer;
        box-sizing: border-box;
    }
    .reports-page .reports-date-input::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    html body main .reports-page .reports-btn,
    html body main .reports-page form button.reports-btn,
    html body main .reports-page form button[type="submit"].reports-btn,
    html body main .reports-page form a.reports-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        min-width: 0 !important;
        width: auto !important;
        height: 38px !important;
        padding: 0 16px !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        border-radius: 8px !important;
        cursor: pointer;
        border: 0 !important;
        text-decoration: none !important;
        background: var(--rp-lime) !important;
        color: #0a1347 !important;
        box-shadow: none !important;
        box-sizing: border-box;
    }
    html body main .reports-page .reports-btn:hover,
    html body main .reports-page form button.reports-btn:hover,
    html body main .reports-page form a.reports-btn:hover {
        background: var(--rp-lime-dark) !important;
        color: #0a1347 !important;
    }
    html body main .reports-page .reports-btn--export,
    html body main .reports-page form button.reports-btn--export {
        background: #fff !important;
        color: var(--rp-text) !important;
        border: 0 !important;
        font-weight: 700 !important;
    }
    html body main .reports-page .reports-btn--export:hover,
    html body main .reports-page form button.reports-btn--export:hover {
        background: #fff !important;
        color: var(--rp-text) !important;
    }

    .reports-page .cards {
        display: flex;
        flex-wrap: nowrap;
        gap: 12px;
        margin-bottom: 28px;
        width: 100%;
    }
    html body main .reports-page .cards .card {
        border-radius: 16px !important;
        padding: 16px 10px 14px !important;
        text-align: center;
        border: 0 !important;
        display: flex;
        flex-direction: column;
        justify-content: center;
        flex: 1 1 0;
        min-width: 0;
        height: 92px;
        min-height: 92px;
        box-sizing: border-box;
        box-shadow: none !important;
        color: #fff !important;
    }
    html body main .reports-page .cards .card .card-title {
        font-size: 15px !important;
        font-weight: 600 !important;
        margin: 0 0 6px !important;
        color: #fff !important;
        line-height: 1.25;
    }
    html body main .reports-page .cards .card .card-value {
        font-weight: 700 !important;
        font-size: 26px !important;
        margin: 0 !important;
        color: #fff !important;
        line-height: 1.1;
    }
    html body main .reports-page .cards .card.card-all {
        background: #d2c0fa !important;
        border-color: #d2c0fa !important;
    }
    html body main .reports-page .cards .card.card-pending {
        background: #bad550 !important;
        border-color: #bad550 !important;
    }
    html body main .reports-page .cards .card.card-overdue {
        background: #9cacf4 !important;
        border-color: #9cacf4 !important;
    }
    html body main .reports-page .cards .card.card-approved {
        background: #25c280 !important;
        border-color: #25c280 !important;
    }
    html body main .reports-page .cards .card.card-rejected {
        background: #f3b86b !important;
        border-color: #f3b86b !important;
    }

    .reports-section-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--rp-text);
        margin: 0 0 12px;
        letter-spacing: 0;
    }
    .reports-table-wrap {
        position: relative;
        overflow-x: auto;
        border: 1.9px solid #C0C0C0;
        border-radius: 18px;
        box-shadow:
            0 18px 45px rgba(15, 61, 46, 0.10),
            0 2px 8px rgba(15, 23, 42, 0.05);
        background: #d4dfdc;
    }
    .reports-table-wrap::before {
        display: none;
    }
    .reports-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.92rem;
        min-width: 720px;
    }
    .reports-table th,
    .reports-table td {
        padding: 15px 16px;
        text-align: left;
        border-bottom: 1.8px solid #C0C0C0;
        border-right: 1.8px solid #C0C0C0;
        vertical-align: middle;
        color: #1e293b;
        background-color: #fff;
    }
    .reports-table th:last-child,
    .reports-table td:last-child {
        border-right: none;
    }
    .reports-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        font-weight: 800;
        color: #1e293b;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.075em;
        white-space: nowrap;
        background-color: #fff;
    }
    .reports-table th:first-child,
    .reports-table td:first-child {
        padding-left: 20px;
    }
    .reports-table td:first-child,
    .reports-table td:nth-child(2) {
        color: #1e293b;
    }
    .reports-table tbody tr,
    .reports-table tbody tr:hover {
        background-color: #fff;
    }
    .reports-table tbody tr:last-child td {
        border-bottom: none;
    }
    .reports-table td.reports-empty,
    .reports-table td:first-child.reports-empty {
        color: #475569;
        font-weight: 600;
        font-style: italic;
        text-align: center;
    }
    .reports-page .request-badge {
        display: inline-block;
        min-width: 5.5rem;
        padding: 6px 11px;
        border-radius: 999px;
        font-weight: 750;
        font-size: 0.76rem;
        text-align: center;
        letter-spacing: 0.01em;
        font-family: inherit;
        background-color: #740755;
        color: #fff;
        border: 2px solid #4a0336;
    }

    @media (max-width: 768px) {
        .reports-page {
            padding: 16px 4px 24px;
        }
        .reports-page h1 {
            font-size: 28px;
        }
        .reports-page .reports-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .reports-page .reports-toolbar-forms,
        .reports-page .reports-filter-form {
            width: 100%;
        }
        html body main .reports-page .reports-filter-form .filter-select-wrap,
        html body main .reports-page .reports-filter-form .reports-date-input,
        html body main .reports-page .reports-filter-form .reports-date-input.flatpickr-input,
        html body main .reports-page .reports-btn,
        html body main .reports-page form button.reports-btn {
            width: 100% !important;
            min-width: 0 !important;
        }
        .reports-page .cards {
            flex-wrap: wrap;
            overflow-x: visible;
        }
        html body main .reports-page .cards .card {
            flex: 1 1 calc(50% - 10px);
            height: auto;
            min-height: 88px;
        }
    }
    @media (max-width: 420px) {
        html body main .reports-page .cards .card {
            flex: 1 1 100%;
        }
    }
</style>
@endonce
