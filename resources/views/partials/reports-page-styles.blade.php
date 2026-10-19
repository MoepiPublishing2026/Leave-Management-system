@once
<style>
    .reports-page {
        font-family: "Poppins", "Segoe UI", sans-serif;
        max-width: 1200px;
        margin: auto;
        padding: 24px 0px 32px;
        color: #273449;
        box-sizing: border-box;


    }
    .reports-page h1 {
        font-weight: 700;
        font-size: 44px;
        color: #3c3c53;
        margin-bottom: 4px;
        letter-spacing: 0;
    }
    .reports-page-lead {
        margin: 0 0 32px;
        color: #616161;
        font-size: 13.4px;
        font-weight: 200;
    }
    .reports-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
        padding: 23px 32px;
        background: #e6f3f2;
        border: 0;
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
        gap: 15px;
        align-items: center;
    }
       .reports-filter-form .filter-select-wrap {
        min-width: 148px;
        flex: 0 0 auto;
        border: none !important;
        box-shadow: none !important;
        background: #e6f3f2 !important;
    }
    .reports-filter-form .filter-select-wrap select,
    .reports-filter-form .filter-select,
    .reports-filter-form .filter-select:hover,
    .reports-filter-form .filter-select:focus {
        color: #273449;
        background-color: #e6f3f2 !important;
        border: 0 !important;
        box-shadow: none !important;
        outline: none !important;
        border-radius: 11px;
        padding: 10px 14px;
    }

    .reports-filter-form .reports-date-input,
    .reports-filter-form .reports-date-input.flatpickr-input {
        width: 148px;
        min-width: 148px;
        flex: 0 0 auto;
        margin: 0;
        padding: 10px 14px;
        border: none;
        border-radius: 11px;
        background: #fff;
        font-family: inherit;
        font-size: 0.92rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.2;
        box-shadow:none;
        /* box-sizing: border-box; */
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        cursor: pointer;
    }
    /* Make sure the date input fields have no border, outline, or box-shadow on hover or focus */
       .reports-page .reports-filter-form input.reports-date-input,
    .reports-page .reports-filter-form input.reports-date-input.flatpickr-input,
    .reports-page .reports-filter-form input.reports-date-input:hover,
    .reports-page .reports-filter-form input.reports-date-input:focus,
    .reports-page .reports-filter-form input.reports-date-input.flatpickr-input:hover,
    .reports-page .reports-filter-form input.reports-date-input.flatpickr-input:focus {
        border: 0 !important;
        outline: none !important;
        box-shadow: none !important;
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
        border: 0;
        text-decoration: none;
        background: #9eb812;
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
        color: #123f3a;
        margin: 0 0 12px;
        letter-spacing: 0.02em;
    }
.reports-table-wrap {
    position: relative;
    overflow-x: auto;
    border: 1.9px solid #C0C0C0; /* Outer table border */
    border-radius: 18px;
    box-shadow:
        0 18px 45px rgba(15, 61, 46, 0.10),
        0 2px 8px rgba(15, 23, 42, 0.05);
    background: #d4dfdc; /* Outer table background */
}

/* Remove the decorative colored line across the top */
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
    border-bottom: 1.8px solid #C0C0C0; /* Horizontal cell lines */
    border-right: 1.8px solid #C0C0C0;  Vertical cell lines
    vertical-align: middle;
    color: #1e293b; /* Table text */
    background: #d3e5df; /* Cell background */
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
    background: #d3e5df; /* Same background as other cells */
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

/* Remove special teal text colors */
.reports-table td:first-child,
.reports-table td:nth-child(2) {
    color: #1e293b;
}

.reports-table tbody tr,
.reports-table tbody tr:hover {
    background: #d3e5df; Keep row background unchanged
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
    border: 2px solid #4a0336;   /* the border */
}
.reports-page .request-badge.badge-approved { background-color: #dcfce7; color: #15803d; border-color: #15803d; }
.reports-page .request-badge.badge-pending  { background-color: #fef3c7; color: #b45309; border-color: #b45309; }
.reports-page .request-badge.badge-rejected { background-color: #fee2e2; color: #b91c1c; border-color: #b91c1c; }
.reports-page .request-badge.badge-overdue  { background-color: #e0e7ff; color: #4338ca; border-color: #4338ca; }
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
        .reports-page .reports-filter-form input.reports-date-input,
.reports-page .reports-filter-form input.reports-date-input.flatpickr-input {
    border: 0 !important;
    outline: none;
    box-shadow: none;
}

.reports-page .reports-filter-form input.reports-date-input:hover,
.reports-page .reports-filter-form input.reports-date-input:focus,
.reports-page .reports-filter-form input.reports-date-input.flatpickr-input:hover,
.reports-page .reports-filter-form input.reports-date-input.flatpickr-input:focus {
    border: 0 !important;
    outline: none;
    box-shadow: none;
}

.reports-page .reports-btn,
.reports-page .reports-btn:hover {
    background-color: #b2cd16;
    color: #0a1347;
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

    .reports-page .card-all {
    background-color: #AE8DF5; /* All Requests: lavender */
}

.reports-page .card-pending {
    background-color: #BAD550; /* Pending Requests: lime */
}

.reports-page .card-overdue {
    background-color: #9cacf4; /* Overdue Requests: blue-purple */
}

.reports-page .card-approved {
    background-color: #25c280; /* Approved Requests: green */
}

.reports-page .card-rejected {
    background-color: #f3b86b; /* Rejected Requests: orange */
}

.reports-page .card .card-title,
.reports-page .card .card-value {
    color: #fff;
}
</style>
@endonce
