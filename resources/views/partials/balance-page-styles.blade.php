@php
    $balanceStatusColors = config('leave_status_colors.colors', []);
    $balanceMaroon = '#850621';
    $balanceMaroonDark = '#680523';
    $balanceMeterPink = '#f00e65';
    $balanceApplyGreen = '#b2cd16';
    $balanceApplyGreenBorder = '#91bf14';
    $balanceApplyGreenText = '#0a1347';
    $balanceColorPending = $balanceStatusColors['pending'] ?? '#C291A4';
    $balanceColorApproved = $balanceStatusColors['approved'] ?? '#E27A53';
    $balanceColorRejected = $balanceStatusColors['rejected'] ?? '#87AFC7';
@endphp
@once
<style>
    .balance-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 4px 32px;
        background-color: #fff;
        color: #1e293b;
    }
    .balance-page h1 {
        font-weight: 700;
        font-size: 40px;
        color: #2c3e50;
        margin: 0 0 8px;
        letter-spacing: 0.04em;
    }
    .balance-page-lead {
        margin: 0 0 20px;
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 600;
        line-height: 1.5;
    }
    .balance-page-lead strong {
        color: #475569;
        font-weight: 600;
    }
    .balance-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 22px;
        padding: 14px 18px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
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
        font-weight: 600;
        font-size: 0.92rem;
        color: #334155;
        text-transform: none;
        letter-spacing: normal;
        margin: 0;
    }
    .balance-year-form select {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.92rem;
        font-weight: 600;
        color: #334155;
        background: #fff;
        min-width: 140px;
    }
    .balance-year-form select:focus {
        outline: none;
        border-color: {{ $balanceMaroon }};
        box-shadow: 0 0 0 3px rgba(133, 6, 33, 0.12);
    }
    .balance-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 9px 16px;
        font-weight: 700;
        font-size: 0.88rem;
        border-radius: 8px;
        cursor: pointer;
        border: 1px solid {{ $balanceMaroonDark }};
        text-decoration: none;
        background: {{ $balanceMaroon }};
        color: #fff;
        transition: background-color 0.15s ease;
        white-space: nowrap;
    }
    .balance-btn:hover {
        background: {{ $balanceMaroonDark }};
        color: #fff;
    }
    .balance-btn--secondary,
    .balance-btn--primary {
        background: {{ $balanceMaroon }};
        border-color: {{ $balanceMaroonDark }};
        color: #fff;
    }
    .balance-btn--apply {
        background: {{ $balanceApplyGreen }};
        border-color: {{ $balanceApplyGreenBorder }};
        color: {{ $balanceApplyGreenText }};
        box-shadow: 0 4px 14px rgba(178, 205, 22, 0.2);
    }
    .balance-btn--apply:hover {
        background: #9eb812;
        color: {{ $balanceApplyGreenText }};
    }

    /* Top summary row — 4 cards */
    .balance-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 18px;
    }
    .balance-summary-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 0 10px rgba(167, 189, 141, 0.85);
        padding: 16px 18px;
        min-height: 168px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
    .balance-summary-k {
        font-size: 1.1rem;
        font-weight: 600;
        text-transform: none;
        letter-spacing: normal;
        color: {{ $balanceMaroonDark }};
        margin-bottom: 0;
        min-height: 2.6rem;
        line-height: 1.25;
        flex-shrink: 0;
    }
    .balance-summary-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 96px;
    }
    .balance-summary-body--meter {
        justify-content: flex-end;
        min-height: 96px;
        margin-top: auto;
    }
    .balance-summary-v {
        font-size: 2.2rem;
        font-weight: 700;
        color: {{ $balanceMaroon }};
        line-height: 1.1;
        margin: 0;
    }
    .balance-summary-v--black {
        color: #0f172a;
        font-size: 2.5rem;
    }
    .balance-summary-cap {
        margin-top: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        color: {{ $balanceMeterPink }};
        min-height: 1.35rem;
        line-height: 1.35;
    }
    .balance-summary-cap--spacer {
        visibility: hidden;
    }
    .balance-summary-card--used,
    .balance-summary-card--remaining-meter {
        padding: 16px 18px 0 13px;
        justify-content: flex-start;
        overflow: hidden;
        border-left: 5px solid {{ $balanceMeterPink }};
        min-height: 168px;
    }
    .balance-summary-card--used .balance-summary-k,
    .balance-summary-card--remaining-meter .balance-summary-k {
        padding: 0;
        margin-bottom: 0;
    }
    .balance-summary-card--used .balance-summary-meter,
    .balance-summary-card--remaining-meter .balance-summary-meter {
        width: calc(100% + 31px);
        margin-left: -13px;
        margin-right: -18px;
        margin-top: 0;
        margin-bottom: 0;
        height: 72px;
        flex-shrink: 0;
        background: #e5e7eb;
        border-radius: 0 12px 0 0;
        overflow: hidden;
    }
    .balance-summary-card--remaining-meter .balance-summary-meter {
        background: #cbd5e1;
    }
    .balance-summary-meter-fill {
        height: 100%;
        min-width: 48px;
        background: {{ $balanceMeterPink }};
        border-radius: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 1rem;
        padding: 0 12px;
        box-sizing: border-box;
    }
    .balance-summary-card--used .balance-summary-meter-fill,
    .balance-summary-card--remaining-meter .balance-summary-meter-fill {
        border-radius: 0 12px 12px 0;
    }
    .balance-status-bars {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 12px;
        height: 58px;
        margin: 4px 0 8px;
    }
    .balance-status-bar {
        width: 24px;
        border-radius: 0;
        min-height: 10px;
    }

    .balance-usage-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 0 10px rgba(167, 189, 141, 0.85);
        padding: 18px 20px;
        margin-bottom: 18px;
    }
    .balance-usage-label {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }
    .balance-usage-track {
        height: 10px;
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
    }
    .balance-usage-fill {
        height: 100%;
        background: {{ $balanceMeterPink }};
        border-radius: 999px;
        transition: width 0.35s ease;
    }

    .balance-detail-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }
    .balance-detail-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 0 10px rgba(167, 189, 141, 0.85);
        padding: 16px 18px;
        min-height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
    .balance-detail-card .detail-k {
        font-size: 0.9rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
        min-height: 2.2rem;
        line-height: 1.25;
    }
    .balance-detail-card .detail-v {
        font-size: 1.1rem;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.35;
        margin-top: auto;
    }
    .balance-detail-card--status .detail-v--status-meta {
        margin-top: 0;
        font-size: 0.88rem;
        color: #64748b;
        font-weight: 600;
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
        background: rgb(252, 213, 234);
        border-radius: 16px;
        padding: 18px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1.05rem;
        font-weight: 600;
        color: #050505;
        box-shadow: 0 3px 7px rgba(13, 59, 53, 0.12);
        text-decoration: none;
        border: 1px solid {{ $balanceMeterPink }};
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
    }
    .balance-type-card .type-days {
        font-weight: 600;
        color: {{ $balanceMaroon }};
        white-space: nowrap;
    }

    .balance-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
        padding: 20px 22px;
        margin-bottom: 18px;
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
        border-color: #eab308;
        color: #92400e;
    }
    .balance-alert--info {
        background: #eff6ff;
        border-color: #2563eb;
        color: #1e40af;
    }
    .balance-alert--danger {
        background: #fff1f2;
        border-color: {{ $balanceMaroon }};
        color: #881337;
    }
    .balance-section-title {
        font-weight: 600;
        font-size: 1.1rem;
        color: #334155;
        margin: 0 0 12px;
        text-transform: none;
        letter-spacing: normal;
    }
    .balance-table-wrap {
        overflow-x: auto;
        border: 1px solid #e2e8f0;
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
        font-weight: 600;
        color: #334155;
        font-size: 0.9rem;
        text-transform: none;
        letter-spacing: normal;
    }
    .balance-table tbody tr:last-child td { border-bottom: none; }
    .balance-table tbody tr:hover { background: #f8fafc; }
    .balance-empty {
        text-align: center;
        color: #64748b;
        font-style: italic;
        padding: 28px 16px !important;
    }
    .balance-back { margin-top: 8px; }
    .balance-back a {
        font-weight: 600;
        color: {{ $balanceMaroon }};
        text-decoration: none;
    }
    .balance-back a:hover { text-decoration: underline; }

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
</style>
@endonce
