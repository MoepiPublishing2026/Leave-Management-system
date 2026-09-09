<style>
    .lh-page-body {
        margin: 0;
        padding: 24px 12px 40px;
        background: #ececec;
        color: #1b1e40;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        overflow-x: hidden;
        -webkit-text-size-adjust: 100%;
    }
    .lh-sheet {
        position: relative;
        max-width: 920px;
        margin: 0 auto;
        background: #ffffff;
        border: 0;
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.12);
        overflow: hidden;
        box-sizing: border-box;
    }
    .lh-accent {
        position: absolute;
        top: 0;
        left: 0;
        width: 18px;
        height: 228px;
        background: #d6e05a;
        border-bottom-right-radius: 24px;
        z-index: 2;
    }
    .lh-header {
        display: flex;
        align-items: center;
        gap: 22px;
        padding: 36px 24px 36px 48px;
        min-height: 228px;
        box-sizing: border-box;
    }
    .lh-emblem {
        height: 156px;
        width: auto;
        max-width: none;
        object-fit: contain;
        object-position: center;
        display: block;
        flex: 0 0 auto;
    }
    .lh-lockup {
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 18px;
        padding-right: 0;
    }
    .lh-wordmark {
        flex: 0 0 auto;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        font-size: 46px;
        font-weight: 700;
        color: #1b1e40;
        letter-spacing: 0.06em;
        line-height: 1;
        margin: 0;
        white-space: nowrap;
    }
    .lh-rule {
        flex: 1 1 auto;
        height: 8px;
        border: 0;
        margin: 0;
        background: #1b1e40;
        align-self: center;
    }
    .lh-body,
    .lh-doc-body {
        padding: 8px 32px 28px;
    }
    .lh-doc-title {
        text-align: center;
        font-size: 28px;
        font-weight: 800;
        color: #1b1e40;
        margin: 8px 0 22px;
        letter-spacing: 0.02em;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
    .lh-sub {
        text-align: center;
        color: #64748b;
        margin: -12px 0 20px;
        font-size: 14px;
    }
    .lh-meta,
    .lh-id-table,
    .lh-legend,
    .lh-scores {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 16px;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
    .lh-meta th,
    .lh-meta td,
    .lh-id-table th,
    .lh-id-table td,
    .lh-legend th,
    .lh-legend td,
    .lh-scores th,
    .lh-scores td {
        border: 1px solid #cbd5e1;
        padding: 8px 10px;
        font-size: 14px;
        vertical-align: top;
        text-align: left;
    }
    .lh-meta th,
    .lh-id-table th,
    .lh-legend th,
    .lh-scores th {
        background: #4F81BD;
        color: #fff;
        font-weight: 700;
    }
    .lh-meta th { width: 32%; white-space: nowrap; }
    .lh-meta td { word-break: break-word; overflow-wrap: anywhere; }
    .lh-scores td { text-align: center; }
    .lh-scores td:first-child,
    .lh-scores th:first-child { text-align: left; }
    .lh-section {
        border: 1px solid #cbd5e1;
        margin-bottom: 16px;
    }
    .lh-section h2 {
        margin: 0;
        background: #4F81BD;
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        padding: 10px 12px;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
    .lh-section-body {
        padding: 12px 14px;
        white-space: pre-wrap;
        line-height: 1.5;
        color: #1e293b;
        font-size: 14px;
    }
    .lh-split {
        display: grid;
        grid-template-columns: 1fr 220px;
        gap: 16px;
        margin: 16px 0;
    }
    .lh-footer {
        position: relative;
        width: calc(100% - 64px);
        max-width: 920px;
        box-sizing: border-box;
        margin: 18px auto 0;
        background: #808388;
        color: #ffffff;
        text-align: center;
        padding: 20px 44px 18px;
        min-height: 0;
        overflow: hidden;
        font-size: 15px;
        font-weight: 500;
        line-height: 1.25;
        letter-spacing: 0.01em;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        border-radius: 999px 999px 0 0;
        box-shadow: none;
    }
    .lh-footer-shape { display: none; }
    .lh-footer-text {
        position: relative;
        z-index: 2;
        padding: 0;
        display: block;
        width: 100%;
    }
    .lh-footer-row { margin: 0 0 12px; }
    .lh-footer-row:last-child { margin-bottom: 0; }
    .lh-footer-row--contacts {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 8px 28px;
        width: 100%;
    }
    .lh-footer-row--address {
        display: flex;
        justify-content: center;
        width: 100%;
    }
    .lh-footer-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        font-weight: 500;
        color: #ffffff;
    }
    .lh-footer-item--address {
        justify-content: center;
        white-space: normal;
        text-align: center;
        max-width: 100%;
    }
    .lh-footer-icon {
        width: 19px;
        height: 19px;
        flex: 0 0 auto;
        display: block;
        color: #e3c44d;
        overflow: visible;
    }
    .lh-footer-icon--phone {
        width: 20px;
        height: 20px;
    }
    .lh-footer-sep { color: #ffffff; padding: 0 8px; }
    .lh-footer a { color: #ffffff; text-decoration: none; font-weight: 700; }
    .lh-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: flex-start;
        margin-top: 20px;
    }
    .lh-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 15px;
        text-decoration: none;
        border: 0;
        cursor: pointer;
        font-family: inherit;
    }
    .lh-btn--approve { background: #16a34a; color: #fff; }
    .lh-btn--reject { background: #dc2626; color: #fff; }
    .lh-btn--primary { background: #4F81BD; color: #fff; }
    .lh-btn--disabled { background: #94a3b8; color: #fff; cursor: not-allowed; }
    .lh-flash {
        margin: 0 0 16px;
        padding: 12px 14px;
        border-radius: 8px;
        font-weight: 600;
        line-height: 1.45;
    }
    .lh-flash--ok { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .lh-flash--err { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .lh-note { color: #9a3412; font-weight: 600; margin-top: 8px; }
    .lh-files a {
        display: inline-block;
        margin: 0 8px 8px 0;
        padding: 8px 12px;
        background: #eef2ff;
        color: #1e3a8a;
        font-weight: 700;
        text-decoration: none;
        font-size: 0.9rem;
    }
    .lh-reject-box {
        margin-top: 18px;
        padding: 16px;
        border: 1px solid #fecaca;
        background: #fff7f7;
    }
    .lh-reject-box label { display: block; font-weight: 700; margin: 0 0 8px; }
    .lh-reject-box textarea,
    .lh-comment {
        width: 100%;
        min-height: 96px;
        padding: 12px;
        border: 1px solid #cbd5e1;
        box-sizing: border-box;
        font: inherit;
    }
    .lh-pill {
        display: inline-block;
        padding: 4px 10px;
        font-weight: 700;
        font-size: 0.85rem;
        background: #dbeafe;
        color: #1e3a8a;
    }
    .lh-pill--ok { background: #dcfce7; color: #166534; }
    .lh-center { text-align: center; }
    .pr-doc {
        position: relative;
        overflow: hidden;
        padding: 0;
        border: 0;
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.12);
    }
    .pr-doc > .lh-header {
        margin: 0;
    }
    .pr-doc > .lh-footer {
        margin: 18px auto 0;
        width: calc(100% - 64px);
    }
    @media (max-width: 760px) {
        .lh-page-body { padding: 8px 6px 20px; }
        .lh-sheet { max-width: 100%; box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08); }
        .pr-doc { padding: 0; }
        .lh-body,
        .lh-doc-body { padding: 4px 12px 20px; }
        .lh-header {
            padding: 24px 12px 24px 36px;
            gap: 16px;
            min-height: 160px;
        }
        .lh-emblem {
            height: 112px;
            width: auto;
            max-width: none;
        }
        .lh-lockup {
            gap: 14px;
        }
        .lh-rule {
            height: 7px;
            margin: 0;
        }
        .lh-wordmark {
            font-size: 32px;
        }
        .lh-accent { width: 14px; height: 172px; border-bottom-right-radius: 18px; }
        .lh-split { grid-template-columns: 1fr; }
        .lh-doc-title { font-size: 20px; margin: 4px 0 14px; }
        .lh-meta,
        .lh-id-table {
            display: block;
            width: 100%;
        }
        .lh-meta tbody,
        .lh-id-table tbody {
            display: block;
            width: 100%;
        }
        .lh-meta tr,
        .lh-id-table tr {
            display: block;
            width: 100%;
            margin: 0 0 10px;
            border: 1px solid #cbd5e1;
            box-sizing: border-box;
        }
        .lh-meta th,
        .lh-meta td,
        .lh-id-table th,
        .lh-id-table td {
            display: block;
            width: 100% !important;
            max-width: 100%;
            box-sizing: border-box;
            white-space: normal !important;
            word-break: break-word;
            overflow-wrap: anywhere;
            border: 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .lh-meta td:last-child,
        .lh-id-table td:last-child { border-bottom: 0; }
        .lh-actions {
            flex-direction: column;
            align-items: stretch;
        }
        .lh-btn { width: 100%; box-sizing: border-box; }
        .lh-files a {
            display: block;
            margin: 0 0 8px;
            text-align: center;
        }
        .lh-footer {
            width: calc(100% - 24px);
            max-width: none;
            min-height: 0;
            font-size: 12px;
            line-height: 1.3;
            border-radius: 36px 36px 0 0;
            padding: 14px 22px 12px;
        }
        .lh-footer-row--contacts {
            display: flex;
            justify-content: center;
            position: static;
            flex-wrap: wrap;
            gap: 6px 16px;
        }
        .lh-footer-row--contacts .lh-footer-item:nth-child(2) {
            position: static;
            left: auto;
            transform: none;
        }
        .lh-footer-icon {
            width: 14px;
            height: 14px;
        }
        .lh-footer-icon--phone {
            width: 16px;
            height: 16px;
        }
        .lh-footer-text { padding: 0; width: 100%; }
        .lh-footer-item { white-space: nowrap; }
        .lh-footer-item--address { white-space: normal; }
        .lh-footer-sep { display: inline; padding: 0 6px; }
        .lh-footer-row {
            margin-bottom: 8px;
        }
        .lh-scores,
        .lh-legend {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }
</style>
