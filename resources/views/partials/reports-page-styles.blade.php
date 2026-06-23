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
    .reports-filter-form .filter-select-wrap {
        min-width: 148px;
        flex: 0 0 auto;
    }
    .reports-filter-form .reports-date-input,
    .reports-filter-form .reports-date-input.flatpickr-input {
        width: 148px;
        min-width: 148px;
        flex: 0 0 auto;
        margin: 0;
        padding: 10px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
        font-family: inherit;
        font-size: 0.92rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.2;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        box-sizing: border-box;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        cursor: pointer;
    }
    .reports-filter-form .reports-date-input:hover,
    .reports-filter-form .reports-date-input.flatpickr-input:hover {
        border-color: #94a3b8;
    }
    .reports-filter-form .reports-date-input:focus,
    .reports-filter-form .reports-date-input.flatpickr-input:focus {
        outline: none;
        border-color: #b2cd16;
        box-shadow: 0 0 0 3px rgba(178, 205, 22, 0.28);
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
        border: 1px solid #91bf14;
        text-decoration: none;
        background: #b2cd16;
        color: #0a1347;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .reports-btn:hover,
    .reports-btn--primary:hover,
    .reports-btn--secondary:hover,
    .reports-btn--export:hover {
        background: #9eb812;
        color: #0a1347;
    }
    .reports-page .cards {
        display: flex;
        flex-wrap: nowrap;
        gap: 12px;
        margin-bottom: 24px;
        width: 100%;
        overflow-x: auto;
        padding-bottom: 8px;
    }
    .reports-page .card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 12px 8px;
        box-shadow: 0 6px 16px rgba(8, 8, 8, 0.15);
        color: #fff;
        text-align: center;
        border: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        justify-content: center;
        flex: 1 1 0;
        min-width: 0;
        height: 94px;
        min-height: 94px;
        box-sizing: border-box;
    }
    .reports-page .card-onleave {
        background: #87AFC7;
        border-color: #87AFC7;
        color: #fff;
    }
    .reports-page .card-sixth {
        background: #D2B55B;
        border-color: #D2B55B;
        color: #fff;
    }
    .reports-page .card-onleave .card-title,
    .reports-page .card-onleave .card-value,
    .reports-page .card-sixth .card-title,
    .reports-page .card-sixth .card-value {
        color: #fff;
    }
    .reports-page .card-title {
        font-size: 1rem;
        font-weight: 600;
        margin: 2px 0 6px 0;
        text-align: center;
        line-height: 1.3;
    }
    .reports-page .card-value {
        font-weight: bold;
        font-size: 1.65rem;
        margin-top: 0;
        line-height: 1.1;
    }
    .reports-section-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 12px;
        letter-spacing: 0.02em;
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
        font-size: 0.92rem;
        min-width: 720px;
    }
    .reports-table th,
    .reports-table td {
        padding: 12px 14px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .reports-table th {
        background: #f8fafc;
        font-weight: 700;
        color: #334155;
        font-size: 0.82rem;
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
    .reports-page .request-badge {
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
        font-family: inherit;
    }
    @media (max-width: 768px) {
        .reports-page {
            padding: 0 12px 24px;
        }
        .reports-page h1 {
            font-size: 28px;
        }
        .reports-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .reports-toolbar-forms,
        .reports-filter-form {
            width: 100%;
        }
        .reports-filter-form .filter-select-wrap,
        .reports-filter-form .reports-date-input,
        .reports-filter-form .reports-date-input.flatpickr-input {
            width: 100%;
            min-width: 0;
            flex: 1 1 100%;
        }
        .reports-btn {
            width: 100%;
            justify-content: center;
        }
        .reports-page .cards {
            display: flex;
            flex-wrap: wrap;
            overflow-x: visible;
            gap: 10px;
            padding-bottom: 4px;
            -webkit-overflow-scrolling: touch;
        }
        .reports-page .card {
            flex: 1 1 calc(50% - 10px);
            min-width: 0;
            width: auto;
            max-width: none;
            height: auto;
            min-height: 88px;
            padding: 12px 10px;
        }
        .reports-page .card:last-child:nth-child(odd) {
            flex-basis: 100%;
        }
        .reports-page .card-title {
            font-size: 0.92rem;
            line-height: 1.25;
        }
        .reports-page .card-value {
            font-size: 1.5rem;
        }
    }
    @media (max-width: 420px) {
        .reports-page .card {
            flex: 1 1 100%;
        }
        .reports-page .card:last-child:nth-child(odd) {
            flex-basis: 100%;
        }
    }
</style>
@endonce
