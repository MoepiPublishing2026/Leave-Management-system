@once
<style>
    .balance-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 4px 32px;
        color: #1e293b;
    }
    .balance-page h1 {
        font-weight: 700;
        font-size: 40px;
        color: #2c3e50;
        margin: 0 0 8px;
        padding-left: 15px;
        border-left: 6px solid #138056;
        letter-spacing: 0.04em;
    }
    .balance-page-lead {
        margin: 0 0 18px;
        color: #64748b;
        font-size: 0.92rem;
        line-height: 1.5;
    }
    .balance-page-lead strong {
        color: #138056;
    }
    .balance-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
        padding: 14px 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }
    .balance-toolbar-left,
    .balance-toolbar-right {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }
    .balance-toolbar-right {
        margin-left: auto;
    }
    .balance-year-form {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    .balance-year-form label {
        font-weight: 700;
        font-size: 0.84rem;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin: 0;
    }
    .balance-year-form select {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.92rem;
        font-weight: 700;
        color: #334155;
        background: #fff;
        min-width: 110px;
    }
    .balance-year-form select:focus {
        outline: none;
        border-color: #138056;
        box-shadow: 0 0 0 3px rgba(19, 128, 86, 0.12);
    }
    .balance-btn {
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
    .balance-btn--primary {
        background: #138056;
        color: #fff;
    }
    .balance-btn--primary:hover {
        background: #0f6b47;
        color: #fff;
    }
    .balance-btn--apply {
        background: #b2cd16;
        color: #0a1347;
        border: 1px solid #91bf14;
    }
    .balance-btn--apply:hover {
        background: #9eb812;
        color: #0a1347;
    }
    .balance-btn--secondary {
        background: #fff;
        color: #334155;
        border: 1px solid #cbd5e1;
    }
    .balance-btn--secondary:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    /* Dashboard-matched leave type cards */
    .balance-annual-strip {
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        box-shadow: 0 6px 22px rgba(15, 61, 46, 0.06);
        padding: 18px 22px;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .balance-annual-strip .strip-label {
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #680523;
    }
    .balance-annual-strip .strip-value {
        font-size: 2rem;
        font-weight: 800;
        color: #850621;
        line-height: 1.1;
    }
    .balance-annual-strip .strip-caption {
        font-size: 0.88rem;
        font-weight: 600;
        color: #64748b;
        margin-top: 4px;
    }
    .balance-annual-strip .strip-meter {
        flex: 1 1 220px;
        max-width: 360px;
    }
    .balance-type-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 18px 24px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .balance-type-card {
        background-color: rgb(252, 213, 234);
        border-radius: 16px;
        padding: 18px 26px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex: 1 1 230px;
        max-width: 320px;
        min-width: 230px;
        font-size: 1.05rem;
        font-weight: 600;
        color: #050505;
        box-shadow: 0 3px 7px rgba(13, 59, 53, 0.12);
        text-decoration: none;
        border: 1px solid #f00e65;
        border-left-width: 5px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .balance-type-card:hover:not(.is-disabled) {
        box-shadow: 0 8px 20px rgba(13, 59, 53, 0.25);
        transform: translateY(-2px);
    }
    .balance-type-card.is-disabled {
        opacity: 0.55;
        pointer-events: none;
        filter: grayscale(0.2);
    }
    .balance-type-card .type-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding-right: 12px;
    }
    .balance-type-card .type-days {
        font-weight: 800;
        color: #850621;
        white-space: nowrap;
    }
    .balance-type-card.is-disabled .type-days {
        color: #6b7280;
    }

    .balance-panel {
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        box-shadow: 0 6px 22px rgba(15, 61, 46, 0.06);
        padding: 22px 24px;
        margin-bottom: 18px;
    }
    .balance-hero {
        background: rgb(252, 213, 234);
        border-radius: 16px;
        padding: 22px 26px;
        margin-bottom: 18px;
        border: 1px solid #f00e65;
        border-left-width: 5px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .balance-hero .hero-label {
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #680523;
        letter-spacing: 0.06em;
    }
    .balance-hero .hero-value {
        font-size: 3rem;
        font-weight: 800;
        color: #850621;
        line-height: 1.1;
        margin-top: 4px;
    }
    .balance-hero .hero-caption {
        font-weight: 600;
        color: #374151;
        margin-top: 6px;
        font-size: 0.92rem;
    }
    .balance-stat-row {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        flex: 1 1 280px;
    }
    .balance-stat {
        background: #fff;
        border-radius: 12px;
        padding: 14px 18px;
        min-width: 120px;
        flex: 1 1 120px;
        box-shadow: 0 4px 12px rgba(8, 8, 8, 0.06);
        border: 1px solid #e5e7eb;
        border-left-width: 5px;
        border-left-color: #f00e65;
    }
    .balance-stat--used { border-left-color: #dab00c; }
    .balance-stat--alloc { border-left-color: #53e21a; }
    .balance-stat .stat-k {
        font-size: 0.75rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .balance-stat .stat-v {
        font-size: 1.65rem;
        font-weight: 800;
        color: #2c3e50;
        margin-top: 4px;
    }

    .balance-usage {
        margin-bottom: 18px;
    }
    .balance-usage-label {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 8px;
        font-size: 0.88rem;
    }
    .balance-usage-track {
        height: 10px;
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
    }
    .balance-usage-fill {
        height: 100%;
        background: #f00e65;
        border-radius: 999px;
        transition: width 0.35s ease;
    }
    .balance-usage-fill.is-warn { background: #dab00c; }
    .balance-usage-fill.is-danger { background: #eb0e8f; }

    .balance-facts {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }
    .balance-fact {
        padding: 12px 14px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        font-weight: 600;
        color: #475569;
        font-size: 0.88rem;
    }
    .balance-fact strong {
        display: block;
        color: #0f172a;
        font-size: 1rem;
        margin-top: 4px;
    }

    .balance-alert {
        padding: 12px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.92rem;
        margin-bottom: 14px;
        border-left: 4px solid;
        line-height: 1.45;
    }
    .balance-alert--warn {
        background: #fffbeb;
        border-color: #dab00c;
        color: #92400e;
    }
    .balance-alert--info {
        background: #eff6ff;
        border-color: #0d6efd;
        color: #1e40af;
    }
    .balance-alert--danger {
        background: #fff1f2;
        border-color: #eb0e8f;
        color: #881f54;
    }

    .balance-section-title {
        font-weight: 800;
        font-size: 0.92rem;
        color: #334155;
        margin: 0 0 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .balance-table-wrap {
        overflow-x: auto;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        background: #fff;
    }
    .balance-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.92rem;
        min-width: 480px;
    }
    .balance-table th,
    .balance-table td {
        padding: 12px 14px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }
    .balance-table th {
        background: #f8fafc;
        font-weight: 700;
        color: #334155;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .balance-table tbody tr:last-child td {
        border-bottom: none;
    }
    .balance-table tbody tr:hover {
        background: #f8fafc;
    }
    .balance-empty {
        text-align: center;
        color: #64748b;
        font-style: italic;
        padding: 28px 16px !important;
    }

    .balance-badge {
        display: inline-block;
        padding: 5px 11px;
        border-radius: 999px;
        font-weight: 800;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .balance-badge--approved {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #22c55e;
    }
    .balance-badge--pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fbbf24;
    }

    .balance-back {
        margin-top: 8px;
    }
    .balance-back a {
        font-weight: 700;
        color: #138056;
        text-decoration: none;
    }
    .balance-back a:hover {
        text-decoration: underline;
        color: #0f6b47;
    }

    @media (max-width: 768px) {
        .balance-page h1 { font-size: 28px; }
        .balance-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .balance-toolbar-right {
            margin-left: 0;
            width: 100%;
        }
        .balance-hero .hero-value { font-size: 2.25rem; }
        .balance-type-card {
            max-width: none;
            min-width: 0;
            width: 100%;
        }
    }
</style>
@endonce
