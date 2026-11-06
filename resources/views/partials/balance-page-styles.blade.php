@php
    $balanceStatusColors = config('leave_status_colors.colors', []);
    $balanceMaroon = '#850621';
    $balanceMaroonDark = '#680523';
    $balanceHeadingTeal = '#0f766e';
    $balanceHeadingTealDark = '#123f3a';
    $balanceHeadingTealSoft = '#d3e5df';
    $balanceApplyGreen = '#b2cd16';
    $balanceApplyGreenBorder = '#91bf14';
    $balanceApplyGreenText = '#111';
    $balanceColorPending = $balanceStatusColors['pending'] ?? '#C291A4';
    $balanceColorApproved = $balanceStatusColors['approved'] ?? '#E27A53';
    $balanceColorRejected = $balanceStatusColors['rejected'] ?? '#87AFC7';
@endphp
@once
<style>
    .balance-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 4px 32px;
        background-color: #fff;
        color: #1e293b;
    }
    .balance-page h1 {
        font-weight: 700;
        font-size: 28px;
        color: #263548;
        margin: 0 0 6px;
        letter-spacing: 0;
    }
    .balance-page-lead {
        margin: 0 0 16px;
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 400;
        line-height: 1.4;
    }
    .balance-page-lead strong {
        color: #475569;
        font-weight: 600;
    }
    .balance-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding: 0;
        background: transparent;
        border: none;
        border-radius: 0;
        box-shadow: none;
    }
    .balance-toolbar-left,
    .balance-toolbar-right {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    .balance-toolbar-right {
        margin-left: auto;
    }
    .balance-year-form {
        display: flex;
        align-items: center;
        padding: 0;
        background: transparent;
        border-radius: 0;
        gap: 10px;
    }
    .balance-page .balance-year-form label {
        display: inline-flex !important;
        align-items: center;
        font-weight: 600 !important;
        font-size: 0.92rem !important;
        color: #263548 !important;
        text-transform: none !important;
        letter-spacing: 0 !important;
        margin: 0 !important;
    }
    .balance-page .balance-year-form select {
        width: auto !important;
        min-width: 148px;
        height: 38px;
        border: 1.5px solid #c5cdd4 !important;
        border-radius: 10px;
        padding: 6px 12px;
        font-size: 0.88rem;
        font-weight: 600;
        color: #263548;
        background: #fff;
    }
    .balance-year-form select:focus {
        outline: none;
        border-color: {{ $balanceApplyGreen }};
        box-shadow: 0 0 0 3px rgba(178, 205, 22, 0.22);
    }
    .balance-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 18px;
        min-height: 38px;
        font-weight: 700;
        font-size: 0.88rem;
        border-radius: 999px;
        cursor: pointer;
        border: 2px solid {{ $balanceApplyGreen }};
        text-decoration: none;
        background: #fff;
        color: #111;
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        white-space: nowrap;
        box-shadow: none;
    }
    .balance-btn:hover {
        background: #f6fadf;
        color: #111;
        border-color: #8a9f12;
    }
    .balance-page .balance-btn--primary {
        background: #fff !important;
        border: 1.5px solid #c5cdd4 !important;
        border-radius: 10px !important;
        color: #263548 !important;
        min-width: 0 !important;
        height: 38px;
        padding: 6px 16px !important;
        font-weight: 600;
    }
    .balance-page .balance-btn--primary:hover {
        background: #f8fafc !important;
        color: #263548 !important;
        border-color: #94a3b8 !important;
    }
    .balance-btn--secondary {
        background: #fff;
        border-color: {{ $balanceApplyGreen }};
        color: #111;
        box-shadow: none;
    }
    .balance-page .balance-btn--apply {
        background: {{ $balanceApplyGreen }} !important;
        border-color: {{ $balanceApplyGreen }} !important;
        color: #111 !important;
        box-shadow: none;
    }
    .balance-btn--secondary:hover {
        background: #f6fadf;
        color: #111;
        border-color: #8a9f12;
    }
    .balance-page .balance-btn--apply:hover {
        background: #a2b222 !important;
        border-color: #a2b222 !important;
        color: #111 !important;
    }

    /* Top summary row — 4 cards */
    .balance-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 12px;
    }
    .balance-summary-card {
        background: #fff;
        border: 1px solid #d2d2d2;
        border-radius: 18px;
        box-shadow: 0 8px 14px rgba(0, 0, 0, 0.20);
        padding: 12px 10px;
        min-height: 105px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
    .balance-summary-k {
        font-size: 1rem;
        font-weight: 500;
        text-transform: none;
        letter-spacing: normal;
        color: #111;
        margin-bottom: 0;
        min-height: auto;
        line-height: 1.25;
        text-align: center;
        flex-shrink: 0;
    }
    .balance-summary-body {
        min-height: 70px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        
    }
    .balance-summary-body--meter {
        min-height: 70px;
        margin-top: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
    }
    .balance-summary-v,
    .balance-summary-v--black {
        font-size: 30px;
        font-weight: 400;
        line-height: 1.15;
        color: #050505;
        margin: 0;
    }
    .balance-summary-cap {
        margin-top: 4px;
        font-size: 0.75rem;
        color: #111;
        font-weight: 600;
        min-height: 1.35rem;
        line-height: 1.35;
    }
    .balance-summary-cap--spacer {
        visibility: hidden;
    }
    .balance-summary-card--used,
    .balance-summary-card--remaining-meter {
        min-height: 106px;
        padding: 12px 10px;
        justify-content: flex-start;
        align-items: stretch;
        text-align: center;
        overflow: hidden;
        background: #fff;
        border: 1px solid #d2d2d2;
        border-radius: 20px;
        box-shadow: 0 8px 14px rgba(0, 0, 0, 0.20);
        
    }
    .balance-summary-card--used .balance-summary-k,
    .balance-summary-card--remaining-meter .balance-summary-k {
        padding: 0;
        margin-bottom: 0;
    }
    .balance-summary-card--used .balance-summary-meter,
    .balance-summary-card--remaining-meter .balance-summary-meter {
        width: 100%;
        height: auto;
        margin: 0;
        padding: 0;
        background: transparent;
        border-radius: 0;
        overflow: visible;
        align-items: center;
        justify-content: center;
    }
    .balance-summary-card--remaining-meter {
        background: {{ $balanceApplyGreen }} !important;
        border-color: {{ $balanceApplyGreenBorder }};
    }
    .balance-summary-card--remaining-meter .balance-summary-k {
        color: {{ $balanceApplyGreenText }};
    }
    .balance-summary-card--remaining-meter .balance-summary-meter {
        background: transparent;
    }
    .balance-summary-meter-fill {
        height: 100%;
        min-width: 0;
        background: #fff;
        border-radius: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #000;
        font-size: 1rem;
        font-weight: 500;
        padding: 0;
        box-sizing: border-box;
    }

    .balance-summary-card--used .balance-summary-meter-fill,
    .balance-summary-card--remaining-meter .balance-summary-meter-fill {
        width: 100%;
        height: auto;
        min-width: 0;
        min-height: 0;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border-radius: 0;
        color: #050505;
        font-size: 28px;
        font-weight: 400;
        line-height: 1.2;
        text-align: center;
    }
    .balance-summary-card--remaining-meter .balance-summary-meter-fill {
        background: transparent;
        color: {{ $balanceApplyGreenText }};
    }

    .balance-status-bars {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        height: 45px;
        gap: 5px;
        margin: 4px 0 6px;
    }
    .balance-status-bar {
        width: 14px;
        border-radius: 2px;
        min-height: 10px;
    }

    .balance-usage-panel {
        background: #fff;
        background: #fff;
        border: 1px solid #d2d2d2;
        border-radius: 16px;
        box-shadow: 0 8px 14px rgba(0, 0, 0, 0.20);
        padding: 10px 14px;
        margin-bottom: 22px;
    }
    .balance-usage-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.3;
        color: #263548;
    }
    .balance-usage-track {
        height: 10px;
        background: #e7e7e7;
        border-radius: 999px;
    }
    .balance-usage-fill {
        height: 100%;
        background: #b2d43b;
        border-radius: 999px;
        transition: width 0.35s ease;
    }

    .balance-detail-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 22px;
    }
    .balance-detail-card {
        background: #fff;
        border: 1px solid #d0d0d0;
        border-radius: 20px;
        box-shadow: 0 9px 13px rgba(0, 0, 0, 0.22);
        padding: 14px 8px 10px;
        min-height: 127px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: stretch;
        box-sizing: border-box;
    }
    .balance-detail-card .detail-k {
        font-size: 12px;
        font-weight: 400;
        color: #050505;
        margin-bottom: 4px;
        min-height: 32px;
        line-height: 1.3;
        text-align: center;
    }
    .balance-detail-card .detail-v {
        font-size: 0.92rem;
        font-weight: 600;
        color: #000;
        line-height: 1.35;
        margin-top: auto;
        text-align: left;
    }
    .balance-detail-card--status .detail-v--status-meta {
        margin-top: 5px;
        font-size: 0.90rem;
        color: #000;
        font-weight: 600;
        text-align: left;
    }

    .balance-type-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 16px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .balance-type-card {
        background: #fff;
        border-radius: 16px;
        padding: 18px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        font-size: 1rem;
        font-weight: 600;
        color: #253141;
        box-shadow: 0 8px 18px rgba(38, 49, 66, 0.10);
        text-decoration: none;
        border: 1px solid #ececec;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .balance-type-card:hover:not(.is-disabled) {
        box-shadow: 0 8px 20px rgba(13, 59, 53, 0.25);
        transform: translateY(-2px);
    }
    .balance-type-card.is-disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }
    .balance-type-card.is-disabled:hover {
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    .balance-type-card .type-days {
        font-weight: 700;
        color: #253141;
        white-space: nowrap;
    }

    .balance-panel {
        background: #fff;
        border: 1px solid #d2d2d2;
        border-radius: 15px;
        box-shadow: 0 8px 14px rgba(0, 0, 0, 0.20);
        padding: 0;
        margin-bottom: 18px;
    }
    .balance-alert {
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 0.75rem;
        margin-bottom: 14px;
        border: 2px solid #c8d85c;
        background: #F3F48C;
        color: #A6AD3A;
        line-height: 1.4;
    }
    .balance-alert--warn {
        background: #eef59b;
        border-color: #c8d85c;
        color: #8a9a13;
    }
    .balance-alert--info {
        background:  #F3F48C;
        border-color: #8a9a13;
        color: #A6AD3A;
    }
    .balance-alert--danger {
        background: #fff1f2;
        border-color: {{ $balanceMaroon }};
        color: #881337;
    }
    .balance-section-title {
        font-weight: 500;
        font-size: 0.95rem;
        color: #111;
        margin: 0;
        padding: 6px 12px;
    }
    .balance-table-wrap {
        overflow-x: auto;
        border: none;
        border-top: 1px solid #ddd;
        border-radius: 0 0 15px 15px;
    }
    .balance-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.78rem;
        min-width: 480px;
    }
    .balance-table th,
    .balance-table td {
        padding: 8px 12px;
        border-bottom: 1px solid #e1e1e1;
        text-align: left; 
    }
    .balance-table th {
        background: #fff;
        font-weight: 500;
        color: #111;
        font-size: 0.9rem;
        text-transform: none;
        letter-spacing: normal;
    }
    .balance-table tbody tr:last-child td { border-bottom: none; }
    .balance-table tbody tr:hover { background: #f8fafc; }
    .balance-empty {
        text-align: center;
        color: #888;
        font-size: 0.7rem;
        padding: 12px 16px !important;
    }

    @media (max-width: 1100px) {
        .balance-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .balance-detail-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 768px) {
        .balance-page h1 { font-size: 1.75rem; }
        .balance-toolbar { flex-direction: column; align-items: stretch; }
        .balance-toolbar-right { margin-left: 0; width: 100%; }
        .balance-toolbar-right .balance-btn { flex: 1 1 auto; justify-content: center; }
        .balance-summary-grid,
        .balance-detail-grid { grid-template-columns: 1fr; }
        .balance-summary-v,
        .balance-summary-v--black { font-size: 1.85rem; }
    }
        /* ===== Prototype match: Annual Leave Page Buttons ===== */
    .balance-page .balance-btn--apply {
        background: {{ $balanceApplyGreen }} !important;
        border: 1px solid {{ $balanceApplyGreenBorder }} !important;
        color: {{ $balanceApplyGreenText }} !important;
        border-radius: 6px !important; 
        font-weight: 700 !important;
        padding: 6px 16px !important;
        box-shadow: none !important;
    }
    .balance-page .balance-btn--apply:hover {
        background: #a2b222 !important;
        border-color: #a2b222 !important;
        color: #111 !important;
    }

    /* The red "Choose another leave type" link */
    .balance-page .balance-btn--change-type {
        background: transparent !important;
        border: none !important;
        color: #d9253c !important; 
        font-weight: 700 !important;
        padding: 6px 12px !important;
        box-shadow: none !important;
        font-size: 0.88rem;
    }
    .balance-page .balance-btn--change-type:hover {
        background: transparent !important;
        color: #b01d30 !important;
        text-decoration: underline;
    }

        /* ===== Prototype Match: Year Filter Box ===== */
    .balance-page .balance-toolbar-left {
        background-color: #dbe4e6 !important; /* Grey background box */
        padding: 8px 16px !important;
        border-radius: 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 10px !important;
    }
    
    /* Make the Year text bolder and darker */
    .balance-page .balance-year-form label {
        font-weight: 700 !important;
        color: #1e293b !important;
    }

    /* Make the Update button look like the prototype (white with border) */
    .balance-page .balance-btn--primary {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        color: #0f172a !important;
        font-weight: 600 !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }
    .balance-page .balance-btn--primary:hover {
        background: #f8fafc !important;
    }

    /* ===== Prototype Match: Full-width Table ===== */
    .balance-panel {
        width: 100% !important; /* Forces the table container to stretch */
    }
    .balance-table {
        width: 100% !important;
    }
    .balance-table th, 
    .balance-table td {
        width: 25% !important; /* Spreads the columns evenly */
    }

    /* ===== Prototype Match: Vibrant Lime Green for Remaining ===== */
    .balance-summary-card--remaining-meter {
        background-color: #cbe212 !important; /* Brighter lime green */
        border-color: #b5cb0e !important;
    }

        /* ===== Fix: Remaining Card Text and "days" label ===== */
    .balance-page .balance-top-row .balance-summary-card--remaining-meter {
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        align-items: center !important;
        background-color: #cbe212 !important; /* The bright lime green */
        padding: 12px !important;
    }

    .balance-page .balance-top-row .balance-summary-card--remaining-meter .balance-summary-k {
        color: #111 !important;
        font-size: 1rem !important;
        font-weight: 500 !important;
        margin-bottom: 4px !important;
        text-align: center !important;
    }

    .balance-page .balance-top-row .balance-summary-card--remaining-meter .balance-summary-body--meter {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        width: 100% !important;
        min-height: auto !important;
    }

    .balance-page .balance-top-row .balance-summary-card--remaining-meter .balance-summary-meter {
        width: 100% !important;
        display: flex !important;
        justify-content: center !important;
        background: transparent !important;
    }

    .balance-page .balance-top-row .balance-summary-card--remaining-meter .balance-summary-meter-fill {
        background: transparent !important;
        color: #000 !important;
        font-size: 30px !important;
        font-weight: 400 !important;
        width: 100% !important;
        padding: 0 !important;
        line-height: 1.1 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important; /* Space between number and 'days' */
    }

    /* Force the word "days" to show up right next to the number */
    .balance-page .balance-top-row .balance-summary-card--remaining-meter .balance-summary-meter-fill::after {
        content: " days" !important;
        font-size: 1.2rem !important;
        font-weight: 400 !important;
        color: #000 !important;
        display: inline-block !important;
    }

    /* Detail page matches the leave-balance design */
    .balance-page .balance-toolbar-right {
        gap: 12px !important;
    }
    .balance-page .balance-btn--apply {
        background: #d4e21a !important;
        border: none !important;
        color: #1c1c1c !important;
        border-radius: 8px !important;
        min-height: 42px !important;
        padding: 8px 18px !important;
        font-weight: 700 !important;
        font-size: 0.95rem !important;
        box-shadow: none !important;
    }
    .balance-page .balance-btn--apply:hover {
        background: #c6d63a !important;
        border: none !important;
        color: #1c1c1c !important;
    }
    .balance-page .balance-summary-grid {
        gap: 18px !important;
        margin-bottom: 16px !important;
    }
    .balance-page .balance-summary-card {
        background: #fff !important;
        border: 1px solid #ececec !important;
        border-radius: 16px !important;
        box-shadow: 0 8px 18px rgba(38, 49, 66, 0.10) !important;
        min-height: 118px !important;
        padding: 18px 12px 16px !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
    }
    .balance-page .balance-summary-k {
        font-size: 1.05rem !important;
        font-weight: 500 !important;
        color: #1a1a1a !important;
        text-align: center !important;
        margin: 0 !important;
    }
    .balance-page .balance-summary-v {
        font-size: 34px !important;
        font-weight: 500 !important;
        line-height: 1.15 !important;
        color: #111 !important;
        text-align: center !important;
        margin-top: 10px !important;
    }
    .balance-page .balance-summary-card--highlight {
        background: #d4e21a !important;
        border: 1px solid #ececec !important;
    }
    .balance-page .balance-summary-card--highlight .balance-summary-k,
    .balance-page .balance-summary-card--highlight .balance-summary-v {
        color: #1a1a1a !important;
    }
    .balance-page .balance-summary-v span {
        font-size: 18px !important;
        font-weight: 500 !important;
    }
    .balance-page .balance-note {
        background: #eef6a8 !important;
        color: #8b9a28 !important;
        border: 2px solid #bdbdbd !important;
        border-radius: 999px !important;
        padding: 10px 18px !important;
        margin: 0 0 16px !important;
        font-size: 0.95rem !important;
        font-weight: 600 !important;
        line-height: 1.4 !important;
    }
    .balance-page .balance-usage-panel {
        border-radius: 16px !important;
        box-shadow: 0 8px 18px rgba(38, 49, 66, 0.10) !important;
        border: 1px solid #ececec !important;
        padding: 14px 16px 16px !important;
        margin-bottom: 18px !important;
    }
    .balance-page .balance-usage-label {
        font-size: 0.92rem !important;
        font-weight: 600 !important;
        color: #3a4654 !important;
    }
    .balance-page .balance-usage-fill {
        background: #c6d63a !important;
    }
    .balance-page .balance-detail-grid {
        gap: 18px !important;
        margin-bottom: 18px !important;
    }
    .balance-page .balance-detail-card {
        border-radius: 16px !important;
        border: 1px solid #ececec !important;
        box-shadow: 0 8px 18px rgba(38, 49, 66, 0.10) !important;
        min-height: 148px !important;
        padding: 16px 14px 14px !important;
        align-items: center !important;
        text-align: center !important;
    }
    .balance-page .balance-detail-card .detail-k {
        width: 100% !important;
        min-height: 0 !important;
        margin-bottom: 8px !important;
        text-align: center !important;
        font-size: 1rem !important;
        font-weight: 500 !important;
        color: #1a1a1a !important;
    }
    .balance-page .balance-detail-card .detail-v {
        width: 100% !important;
        margin-top: auto !important;
        text-align: center !important;
        font-size: 1.65rem !important;
        font-weight: 500 !important;
        color: #111 !important;
    }
    .balance-page .balance-detail-card--docs .detail-v {
        font-size: 0.98rem !important;
        font-weight: 500 !important;
        line-height: 1.45 !important;
    }
    .balance-page .balance-detail-card--status {
        align-items: stretch !important;
    }
    .balance-page .balance-detail-card--status .balance-status-bars {
        height: 56px !important;
        margin: 8px 0 10px !important;
    }
    .balance-page .balance-detail-card--status .detail-v--status-meta {
        margin-top: auto !important;
        text-align: left !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        align-self: flex-start !important;
    }
    .balance-page .balance-status-bar {
        width: 16px !important;
        border-radius: 3px 3px 0 0 !important;
    }
    .balance-page .balance-panel {
        border-radius: 16px !important;
        box-shadow: 0 8px 18px rgba(38, 49, 66, 0.10) !important;
        border: 1px solid #ececec !important;
    }
    .balance-page .balance-section-title {
        font-size: 1rem !important;
        font-weight: 600 !important;
        padding: 12px 16px !important;
    }
    .balance-page .balance-table th,
    .balance-page .balance-table td {
        padding: 12px 16px !important;
        font-size: 0.95rem !important;
        color: #1a1a1a !important;
    }
    html body main .balance-page > .balance-change-row {
        width: min(100%, 1040px) !important;
        max-width: 1040px !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding: 0 !important;
        text-align: left !important;
        box-sizing: border-box !important;
    }
    .balance-page .balance-change-type {
        display: inline-flex !important;
        align-items: center !important;
        width: auto !important;
        max-width: none !important;
        margin: 6px 0 12px !important;
        padding: 0 !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        color: #e23b4a !important;
        font-weight: 700 !important;
        font-size: 1.05rem !important;
        text-decoration: none !important;
        text-align: left !important;
    }
    .balance-page .balance-change-type:hover {
        color: #c42532 !important;
        background: transparent !important;
        text-decoration: none !important;
    }
    .balance-page .balance-summary-grid--single {
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        margin-bottom: 16px !important;
    }
    .balance-page .balance-type-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 18px !important;
        align-items: stretch !important;
    }
    .balance-page .balance-type-grid > li {
        display: flex !important;
        min-height: 96px !important;
    }
    .balance-page .balance-type-card {
        background: #fff !important;
        border: 1.5px solid #c6d63a !important;
        border-radius: 16px !important;
        box-shadow: 0 8px 18px rgba(38, 49, 66, 0.10) !important;
        color: #253141 !important;
        width: 100% !important;
        height: 100% !important;
        min-height: 96px !important;
        box-sizing: border-box !important;
    }
    .balance-page .balance-type-card:hover:not(.is-disabled) {
        border: 1.5px solid #263041 !important;
        box-shadow: 0 10px 22px rgba(38, 49, 66, 0.12) !important;
        transform: translateY(-2px);
    }
    .balance-page .balance-type-card .type-name {
        line-height: 1.3 !important;
    }
    .balance-page .balance-type-card .type-name {
        color: #253141 !important;
    }
    .balance-page .balance-type-card .type-days {
        color: #253141 !important;
        font-weight: 700 !important;
    }
    .balance-page .balance-type-card.is-disabled {
        background: #f8fafc !important;
        border: 1.5px solid #c5ced6 !important;
        color: #94a3b8 !important;
        box-shadow: none !important;
    }
    .balance-page .balance-type-card.is-disabled .type-name,
    .balance-page .balance-type-card.is-disabled .type-days {
        color: #94a3b8 !important;
    }
    @media (max-width: 1100px) {
        .balance-page .balance-type-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        .balance-page .balance-summary-grid--single { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
    }
    @media (max-width: 768px) {
        .balance-page .balance-type-grid,
        .balance-page .balance-summary-grid--single { grid-template-columns: 1fr !important; }
    }

</style>
@endonce
