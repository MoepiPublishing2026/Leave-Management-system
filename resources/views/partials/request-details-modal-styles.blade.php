@once
<style>
    .request-details-container.swal2-container {
        z-index: 10150 !important;
        padding: 28px 14px !important;
        align-items: center !important;
        justify-content: center !important;
        overflow-y: auto !important;
    }
    .request-details-container .swal2-popup.request-details-popup {
        margin: 0 auto !important;
    }
    .request-details-popup.swal2-popup {
        padding: 0 !important;
        border-radius: 18px !important;
        border: 2px solid #8fb3a8 !important;
        box-shadow:
            0 22px 48px rgba(15, 61, 46, 0.16),
            0 2px 8px rgba(15, 23, 42, 0.06) !important;
        overflow: hidden !important;
        max-width: 680px !important;
        width: 100%;
        background: #fff !important;
        position: relative !important;
    }
    .request-details-popup.swal2-popup::before {
        content: "";
        display: block;
        
    }
    .request-details-popup .swal2-icon,
    .request-details-popup .swal2-close,
    .request-details-popup .swal2-title {
        display: none !important;
    }
    .request-details-popup .swal2-html-container {
        margin: 0 !important;
        padding: 0 !important;
        text-align: left !important;
        overflow: visible !important;
        max-height: none !important;
    }
    .request-details-popup .swal2-actions {
        margin: 0 !important;
        padding: 14px 22px 18px !important;
        border-top: 1px solid #c5d9d1;
        background: linear-gradient(180deg, #f4faf7 0%, #eef6f3 100%);
        width: 100%;
        box-sizing: border-box;
    }
    .request-details-popup .swal2-confirm {
        background: #b2cd16 !important;
        color: #0a1347 !important;
        border: 1px solid #91bf14 !important;
        border-radius: 10px !important;
        font-weight: 700 !important;
        font-size: 0.92rem !important;
        padding: 10px 26px !important;
        min-width: 120px;
        box-shadow: 0 2px 6px rgba(15, 61, 46, 0.12) !important;
    }
    .request-details-popup .swal2-confirm:hover {
        background: #9eb812 !important;
        color: #0a1347 !important;
    }
    .request-details-popup .swal2-confirm:focus {
        box-shadow: 0 0 0 3px rgba(19, 128, 86, 0.2) !important;
    }

    .rd-modal {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        color: #1e293b;
        line-height: 1.45;
    }
    .rd-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        padding: 18px 22px;
        background:
            radial-gradient(circle at top right, rgba(178, 205, 22, 0.18), transparent 42%),
            linear-gradient(135deg, #e7f4ef 0%, #f3faf7 55%, #ffffff 100%);
        border-bottom: 1px solid #c5d9d1;
    }
    .rd-header-id {
        font-size: 1.28rem;
        font-weight: 750;
        color: #1b4d42;
        margin: 0;
        letter-spacing: 0.01em;
    }
    .rd-header-id span {
        color: #0f766e;
    }
    .rd-employee {
        padding: 14px 22px;
        border-bottom: 1px solid #d7e5df;
        background: #f7fbf9;
    }
    .rd-employee-name {
        margin: 0 0 3px;
        font-weight: 750;
        font-size: 1rem;
        color: #1b4d42;
    }
    .rd-employee-meta {
        margin: 0;
        font-size: 0.84rem;
        color: #5f857a;
    }
    .rd-review {
        margin: 14px 22px 4px;
        border: 1px solid #c5d9d1;
        border-radius: 12px;
        overflow: hidden;
        background: #f7fbf9;
    }
    .rd-review-title {
        padding: 10px 14px;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #1b4d42;
        background: #e7f4ef;
        border-bottom: 1px solid #c5d9d1;
    }
    .rd-check {
        display: grid;
        grid-template-columns: 22px minmax(0, 1fr);
        gap: 10px;
        padding: 10px 14px;
        border-top: 1px solid #e6efeb;
        align-items: start;
    }
    .rd-check:first-of-type {
        border-top: 0;
    }
    .rd-check-mark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        font-size: 0.75rem;
        font-weight: 800;
        margin-top: 1px;
    }
    .rd-check-mark--ok {
        background: #dcfce7;
        color: #166534;
    }
    .rd-check-mark--warn {
        background: #ffedd5;
        color: #9a3412;
    }
    .rd-check-label {
        font-size: 0.86rem;
        font-weight: 750;
        color: #1b4d42;
        margin-bottom: 2px;
    }
    .rd-check-body {
        font-size: 0.84rem;
        font-weight: 500;
        color: #334155;
        line-height: 1.4;
    }
    .rd-check-note {
        margin-top: 6px;
        font-size: 0.8rem;
        font-weight: 500;
        color: #5f857a;
        line-height: 1.4;
    }
    .rd-check-alert {
        margin-top: 8px;
        padding: 8px 10px;
        border-radius: 8px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        font-size: 0.82rem;
        font-weight: 650;
        line-height: 1.4;
    }
    .rd-body {
        padding: 10px 0 12px;
        background: #fff;
    }
    .rd-row {
        display: grid;
        grid-template-columns: 140px minmax(0, 1fr);
        gap: 14px;
        align-items: start;
        padding: 12px 22px;
        border-bottom: 1px solid #e6efeb;
    }
    .rd-row:nth-child(even) {
        background: #f8fbfa;
    }
    .rd-row:last-child {
        border-bottom: 0;
    }
    .rd-row-label {
        font-size: 0.78rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #5f857a;
        padding-top: 2px;
    }
    .rd-row-value {
        font-size: 0.98rem;
        font-weight: 650;
        color: #1e293b;
        word-break: break-word;
    }
    .rd-row-value--type {
        color: #0f766e;
        font-weight: 800;
        font-size: 1.05rem;
    }
    .rd-row-value--muted {
        color: #8aa399;
        font-weight: 500;
        font-style: italic;
    }
    .rd-row-value--block {
        white-space: pre-wrap;
        font-weight: 500;
        color: #334155;
        line-height: 1.5;
    }
    .rd-row-note {
        margin-top: 5px;
        font-size: 0.86rem;
        font-weight: 500;
        color: #5f857a;
    }
    .rd-messages {
        padding: 10px 22px 6px;
    }
    .rd-messages a {
        display: inline-flex;
        align-items: center;
        padding: 8px 14px;
        border-radius: 8px;
        background: #ecfdf5;
        border: 1px solid #86efac;
        color: #047857;
        font-weight: 700;
        font-size: 0.88rem;
        text-decoration: none;
    }
    .rd-messages a:hover {
        background: #d1fae5;
        color: #065f46;
    }

    /* Prototype leave-request popup */
    .request-details-popup--leave.swal2-popup {
        padding: 22px 28px 26px !important;
        border: 0 !important;
        border-radius: 22px !important;
        box-shadow: 0 18px 46px rgba(15, 23, 42, 0.22) !important;
        overflow: visible !important;
        max-width: 640px !important;
        background: #fff !important;
    }
    .request-details-popup--leave.swal2-popup::before {
        display: none !important;
    }
    .request-details-popup--leave .swal2-actions {
        display: none !important;
    }
    .request-details-popup--leave .swal2-html-container {
        overflow: visible !important;
    }

    html body .request-details-popup--senior.swal2-popup {
        width: min(92vw, 600px) !important;
        max-width: min(92vw, 600px) !important;
        padding: 28px 34px 20px !important;
        border-radius: 2px !important;
        overflow: visible !important;
        background: #fff !important;
    }
    html body .request-details-popup--senior .swal2-actions {
        display: flex !important;
        position: static !important;
        width: 100% !important;
        margin: 16px 0 2px !important;
        padding: 0 !important;
        background: #fff !important;
        justify-content: center !important;
    }
    html body .request-details-container--senior .swal2-confirm.rd-senior-close {
        background: #d4e21a !important;
        color: #111111 !important;
        border: 0 !important;
        border-radius: 8px !important;
        box-shadow: none !important;
        font-weight: 700 !important;
        font-size: 15px !important;
        min-width: 118px !important;
        min-height: 0 !important;
        height: auto !important;
        padding: 10px 28px !important;
    }
    html body .request-details-container--senior .swal2-confirm.rd-senior-close:hover {
        background: #c3d010 !important;
        color: #111111 !important;
    }
    html body .rd-modal--senior {
        color: #6b7280;
        font-family: "Poppins", "Segoe UI", sans-serif;
        font-weight: 400;
    }
    html body .rd-senior-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    html body .rd-senior-head h2 {
        margin: 0;
        font-family: "Century Gothic", "Questrial", "Poppins", sans-serif;
        font-size: 30px;
        line-height: 1.1;
        font-weight: 700;
        color: #243044;
        letter-spacing: 0;
    }
    html body .rd-senior-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex: none;
        margin-top: 0;
        padding: 6px 14px;
        border-radius: 999px;
        background: #243044;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        line-height: 1;
    }
    .rd-senior-status--approved { background: #2a6fc0; }
    .rd-senior-status--rejected { background: #c62828; }
    .rd-senior-status svg {
        width: 16px;
        height: 16px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    html body .rd-senior-who {
        margin: 0 0 18px;
        font-size: 13px;
        line-height: 1.35;
        font-weight: 400;
        color: #9aa3af;
    }
    html body .rd-senior-name {
        font-size: 15px;
        font-weight: 400;
        color: #6f7884;
    }
    html body .rd-senior-meta {
        margin-top: 2px;
        font-size: 13px;
        font-weight: 400;
        color: #8b95a3;
    }
    html body .rd-senior-rule {
        height: 3px;
        margin: 14px 0 14px;
        background: #e4ef3a;
    }
    html body .rd-senior-box {
        border: 2px solid #e4ef3a;
        border-radius: 2px;
        padding: 18px 18px 20px;
    }
    html body .rd-senior-kicker {
        margin: 0 0 16px;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        text-decoration: underline;
        text-underline-offset: 3px;
        color: #243044;
    }
    html body .rd-senior-label {
        margin: 18px 0 8px;
        font-size: 15px;
        font-weight: 400;
        color: #6f7884;
    }
    html body .rd-senior-kicker + .rd-senior-label {
        margin-top: 0;
    }
    html body .rd-senior-stats,
    html body .rd-senior-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
    }
    html body .rd-senior-stats span,
    html body .rd-senior-pills span {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border: 1px solid #e1e6ec;
        border-radius: 999px;
        background: #f7f8fa;
        color: #8b95a3;
        font-size: 13px;
        font-weight: 400;
    }
    html body .rd-senior-request,
    html body .rd-senior-copy,
    html body .rd-senior-policy {
        margin: 14px 0 0;
        font-size: 13.5px;
        line-height: 1.5;
        font-weight: 400;
        color: #8b95a3;
    }
    html body .rd-senior-alert {
        margin-top: 16px;
        padding: 12px 14px;
        border-radius: 2px;
        background: #243044;
        color: #e4ef3a;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.4;
    }
    .rd-senior-pills { margin-top: 8px; }
    .rd-senior-table {
        width: 100%;
        margin-top: 18px;
        border-collapse: collapse;
    }
    .rd-senior-table th,
    .rd-senior-table td {
        padding: 9px 0;
        border-bottom: 1px solid #e6eaf0;
        vertical-align: top;
    }
    .rd-senior-table th {
        width: 46%;
        padding-right: 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #9aa3af;
    }
    .rd-senior-table td {
        text-align: right;
        font-size: 14px;
        font-weight: 800;
        color: #243044;
    }
    .rd-senior-messages {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 22px auto 4px;
        width: min(100%, 420px);
        min-height: 46px;
        border-radius: 24px;
        background: #2c3848;
        color: #fff;
        font-weight: 700;
        font-size: 16px;
        text-decoration: none;
    }
    .rd-senior-messages:hover {
        background: #1c2633;
        color: #fff;
    }
    html body .swal2-popup.calendar-day-popup,
    html body .swal2-popup:has(.cal-day-modal) {
        width: 560px !important;
        max-width: calc(100vw - 32px) !important;
        padding: 20px 26px 16px !important;
        border: 0 !important;
        border-radius: 16px !important;
        box-shadow: 0 16px 44px rgba(15, 23, 42, 0.2) !important;
        background: #fff !important;
    }
    html body .swal2-popup.calendar-day-popup .swal2-title,
    html body .swal2-popup:has(.cal-day-modal) .swal2-title,
    html body .swal2-popup.calendar-day-popup .swal2-icon,
    html body .swal2-popup.calendar-day-popup .swal2-close {
        display: none !important;
    }
    html body .swal2-popup.calendar-day-popup .swal2-html-container,
    html body .swal2-popup:has(.cal-day-modal) .swal2-html-container {
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        max-height: none !important;
        text-align: left !important;
        color: inherit !important;
        font-size: inherit !important;
        line-height: inherit !important;
    }
    html body .cal-day-title {
        width: 100%;
        margin: 0 !important;
        padding: 2px 2px 14px !important;
        border-bottom: 1px solid #d0d0d0 !important;
        color: #111 !important;
        font-size: 15px !important;
        font-weight: 600 !important;
        text-align: left !important;
        text-transform: none !important;
        line-height: 1.35 !important;
        letter-spacing: 0 !important;
    }
    html body .cal-day-label {
        color: #111 !important;
        font-weight: 600 !important;
    }
    html body .cal-day-date {
        color: #3b7ec4 !important;
        font-weight: 800 !important;
    }
    html body .swal2-popup.calendar-day-popup .cal-day-content {
        margin: 0;
        padding: 22px 8px;
        border-bottom: 1px solid #d0d0d0;
        text-align: center;
    }
    html body .swal2-popup.calendar-day-popup .cal-day-content--leave {
        text-align: left;
        padding: 16px 8px 18px;
    }
    html body .swal2-popup.calendar-day-popup .cal-day-empty {
        color: #8b8b8b;
        font-size: 14px;
        font-style: italic;
        font-weight: 400;
    }
    html body .swal2-popup.calendar-day-popup .cal-day-group {
        margin: 0 0 12px;
    }
    html body .swal2-popup.calendar-day-popup .cal-day-group:last-child {
        margin-bottom: 0;
    }
    html body .swal2-popup.calendar-day-popup .cal-day-group-title {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin: 0 0 6px;
    }
    html body .swal2-popup.calendar-day-popup .cal-day-list {
        margin: 0;
        padding-left: 1.2rem;
        color: #334155;
        font-size: 14px;
        font-style: normal;
    }
    html body .swal2-popup.calendar-day-popup .swal2-actions {
        display: flex !important;
        justify-content: center !important;
        margin: 0 !important;
        padding: 16px 0 2px !important;
        border: 0 !important;
        background: transparent !important;
        width: 100%;
    }
    html body .swal2-popup.calendar-day-popup .swal2-confirm,
    html body .swal2-popup:has(.cal-day-modal) .swal2-confirm,
    html body .swal2-popup.calendar-day-popup button.swal2-confirm.calendar-day-close,
    html body button.swal2-confirm.calendar-day-close,
    html body .cal-day-close,
    html body button.cal-day-close {
        background: #a2b222 !important;
        color: #263041 !important;
        font-weight: 700 !important;
        font-size: 18px !important;
        border-radius: 10px !important;
        padding: 10px 22px !important;
        min-height: 0 !important;
        min-width: 0 !important;
        height: auto !important;
        width: auto !important;
        line-height: 1.2 !important;
        text-transform: none !important;
        box-shadow: none !important;
        outline: none !important;
        border-width: 3px !important;
        border-style: solid !important;
        border-top-color: #00ffff !important;
        border-left-color: #00ffff !important;
        border-right-color: #ff40d4 !important;
        border-bottom-color: #ff40d4 !important;
    }
    html body .swal2-popup.calendar-day-popup .swal2-confirm:hover,
    html body .swal2-popup:has(.cal-day-modal) .swal2-confirm:hover,
    html body .swal2-popup.calendar-day-popup button.swal2-confirm.calendar-day-close:hover,
    html body button.swal2-confirm.calendar-day-close:hover,
    html body .cal-day-close:hover,
    html body button.cal-day-close:hover {
        background: #8d9c1c !important;
        color: #263041 !important;
    }
    .rd-modal--proto {
        font-family: "Poppins", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        color: #1e293b;
    }
    .rd-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #c5d931;
        color: #111;
        border: 0;
        border-radius: 12px;
        padding: 8px 16px;
        font-weight: 700;
        font-size: 0.92rem;
        cursor: pointer;
        box-shadow: none;
        line-height: 1.2;
    }
    .rd-back:hover,
    .rd-back:focus {
        background: #c5d931;
        color: #111;
        outline: none;
        box-shadow: none;
    }
    .rd-title {
        margin: 18px 0 6px;
        font-size: 1.55rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.01em;
    }
    .rd-timeline {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0;
        margin: 18px 8px 26px;
    }
    .rd-tl-step {
        flex: 0 0 auto;
        width: 96px;
        text-align: center;
    }
    .rd-tl-dot {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        margin: 0 auto;
        box-sizing: border-box;
    }
    .rd-tl-dot--lime {
        background: #c5d931;
        border: 3px solid #c5d931;
    }
    .rd-tl-dot--orange {
        background: #ef8c2a;
        border: 3px solid #ef8c2a;
    }
    .rd-tl-dot--red {
        background: #e5394d;
        border: 3px solid #e5394d;
    }
    .rd-tl-dot--empty {
        background: #fff;
        border: 3px solid #d5d9c8;
    }
    .rd-tl-line {
        flex: 1;
        height: 3px;
        background: #c5d931;
        margin-top: 10px;
        min-width: 24px;
    }
    .rd-tl-line--muted {
        background: #e4e8d8;
    }
    .rd-tl-label {
        margin-top: 8px;
        font-size: 0.86rem;
        font-weight: 700;
        color: #1e293b;
    }
    .rd-tl-date {
        margin-top: 2px;
        font-size: 0.72rem;
        font-weight: 500;
        color: #64748b;
        white-space: nowrap;
    }
    .rd-fields {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .rd-fields--extra {
        margin-top: 12px;
    }
    .rd-chip-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .rd-chip {
        border: 2px solid #c5d931;
        border-radius: 16px;
        padding: 12px 16px;
        background: #fff;
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.35;
        min-width: 0;
    }
    
  .rd-chip-k {
    font-size: 0.72rem;       /* ← smaller label text */
    font-weight: 500;
    color: #1e293b;
    margin-right: 6px;
}
.rd-chip-v {
    font-size: 0.72rem;       /* ← value text size */
    font-weight: 500;
    word-break: break-word;
}
    .rd-chip-muted {
        color: #94a3b8;
        font-weight: 500;
        font-style: italic;
    }
    .rd-chip-file {
        display: inline;
        padding: 0;
        border: 0;
        background: none;
        color: #a2b222;
        font-weight: 700;
        font-size: inherit;
        cursor: pointer;
        text-decoration: none;
        word-break: break-all;
    }
    .rd-chip-file:hover {
        color: #7d8e14;
        text-decoration: underline;
    }
    .rd-chip-note {
        margin-top: 4px;
        font-size: 0.8rem;
        font-weight: 500;
        color: #5f857a;
    }
    .rd-modal--proto .rd-review {
        margin: 14px 0 0;
    }
    .rd-modal--proto .rd-messages {
        padding: 12px 0 0;
    }

    /* Calendar modal compatibility */
    .rd-badge {
        display: inline-block;
        min-width: 5.5rem;
        padding: 6px 11px;
        border-radius: 999px;
        font-weight: 750;
        font-size: 0.76rem;
        text-align: center;
        color: #fff;
    }
    .rd-badge--pending { background: #C291A4 !important; }
    .rd-badge--approved { background: #138056 !important; }
    .rd-badge--rejected { background: #b91c1c !important; }
    .rd-badge--withdrawn { background: #D2B55B !important; }
    .rd-badge--overdue { background: #cebaf6 !important; border: 2px solid #a33d86; color: #fff !important; }
    .rd-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 14px;
    }
    .rd-stat {
        background: #f4faf7;
        border: 1px solid #d5e6df;
        border-radius: 10px;
        padding: 10px 12px;
    }
    .rd-stat-label {
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b8f84;
        margin-bottom: 3px;
    }
    .rd-stat-value {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e293b;
        word-break: break-word;
    }
    .rd-stat-value--type { color: #0f766e; }
    .rd-section { margin-bottom: 12px; }
    .rd-section-title {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin: 0 0 6px;
    }
    .rd-box {
        font-size: 0.9rem;
        color: #334155;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .rd-box--muted { color: #94a3b8; font-style: italic; }
    .rd-box--rejection {
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 10px 12px;
        color: #166534;
    }
    .rd-box--info {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 8px;
        padding: 10px 12px;
        color: #92400e;
    }

    .rd-files {
        display: flex;
        flex-direction: column;
        gap: 8px;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .rd-file-item {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 12px;
        background: #f4faf7;
        border: 1px solid #d5e6df;
        border-radius: 10px;
    }
    .rd-file-name {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 700;
        font-size: 0.86rem;
        color: #1e293b;
    }
    .rd-file-name svg {
        width: 16px;
        height: 16px;
        color: #0f766e;
        flex-shrink: 0;
    }
    .rd-file-actions {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .rd-file-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 11px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.78rem;
        text-decoration: none;
        border: 1px solid #86efac;
        cursor: pointer;
        background: #ecfdf5;
        color: #047857;
    }
    .rd-file-btn:hover {
        background: #d1fae5;
        color: #065f46;
    }
    .rd-file-btn--download {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #1d4ed8;
    }
    .rd-file-btn--download:hover {
        background: #dbeafe;
        color: #1e3a8a;
    }

    .attachment-preview-popup.swal2-popup {
        padding: 0 !important;
        border-radius: 18px !important;
        border: 2px solid #8fb3a8 !important;
        box-shadow: 0 22px 48px rgba(15, 61, 46, 0.16) !important;
        overflow: hidden !important;
        max-width: 920px !important;
        width: 100% !important;
    }
    .attachment-preview-popup.swal2-popup::before {
        content: "";
        display: block;
        height: 4px;
        background: linear-gradient(90deg, #0f766e 0%, #16a085 45%, #b2cd16 100%);
    }
    .attachment-preview-popup .swal2-title {
        padding: 16px 20px 8px !important;
        margin: 0 !important;
        font-size: 1.15rem !important;
        font-weight: 750 !important;
        color: #1b4d42 !important;
        text-align: left !important;
    }
    .attachment-preview-popup .swal2-html-container {
        margin: 0 !important;
        padding: 0 18px 12px !important;
        overflow: visible !important;
    }
    .attachment-preview-popup .swal2-actions {
        margin: 0 !important;
        padding: 12px 18px 16px !important;
        border-top: 1px solid #c5d9d1;
        background: linear-gradient(180deg, #f4faf7 0%, #eef6f3 100%);
        gap: 10px;
    }
    .attachment-preview-popup .swal2-close {
        top: 12px !important;
        right: 12px !important;
        color: #5f857a !important;
    }
    .attachment-preview-popup .attachment-preview-btn-download,
    .attachment-preview-popup .swal2-confirm {
        background: #b2cd16 !important;
        color: #0a1347 !important;
        border: 1px solid #91bf14 !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        box-shadow: none !important;
    }
    .attachment-preview-popup .attachment-preview-btn-open,
    .attachment-preview-popup .swal2-deny {
        background: #0f766e !important;
        color: #fff !important;
        border: 1px solid #0d9488 !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        box-shadow: none !important;
    }
    .attachment-preview-shell { width: 100%; }
    .attachment-preview-meta {
        margin: 0 0 10px;
        font-size: 0.82rem;
        color: #5f857a;
        word-break: break-all;
    }
    .attachment-preview-wrap {
        width: 100%;
        min-height: 280px;
        background: #f7fbf9;
        border: 1px solid #d5e6df;
        border-radius: 12px;
        overflow: auto;
        padding: 8px;
    }
    .attachment-preview-frame {
        width: 100%;
        height: min(70vh, 640px);
        border: 0;
        border-radius: 8px;
        background: #fff;
    }
    .attachment-preview-image {
        display: block;
        max-width: 100%;
        max-height: min(70vh, 640px);
        margin: 0 auto;
        border-radius: 8px;
    }
    .attachment-preview-text {
        margin: 0;
        padding: 14px;
        white-space: pre-wrap;
        word-break: break-word;
        font-family: Consolas, "Courier New", monospace;
        font-size: 0.88rem;
        color: #1e293b;
        background: #fff;
        border-radius: 8px;
    }
    .attachment-preview-doc {
        padding: 16px 18px;
        background: #fff;
        border-radius: 8px;
        color: #1e293b;
        line-height: 1.55;
        font-size: 0.95rem;
    }
    .attachment-preview-doc img {
        max-width: 100%;
        height: auto;
    }
    .attachment-preview-note {
        margin: 0;
        padding: 18px 16px;
        color: #475569;
        font-size: 0.94rem;
        line-height: 1.5;
        text-align: center;
    }
    .attachment-preview-note--error {
        color: #991b1b;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
    }
    .attachment-preview-container { z-index: 10200 !important; }
    /* Gap above the Attachment box */
.rd-fields > .rd-chip-row + .rd-chip--full {
    margin-top: 16px;         /* ← space above Attachment (increase for more) */
}

    @media (max-width: 768px) {
        .request-details-popup.swal2-popup {
            max-width: calc(100vw - 16px) !important;
            width: calc(100vw - 16px) !important;
            border-radius: 14px !important;
        }
        .attachment-preview-popup.swal2-popup {
            max-width: calc(100vw - 16px) !important;
            width: calc(100vw - 16px) !important;
        }
        .rd-header,
        .rd-employee {
            padding-left: 16px;
            padding-right: 16px;
        }
        .rd-row {
            grid-template-columns: 1fr;
            gap: 3px;
            padding-left: 16px;
            padding-right: 16px;
        }
        .request-details-popup--leave.swal2-popup {
            padding: 18px 16px 20px !important;
        }
        .rd-chip-row {
            grid-template-columns: 1fr;
        }
        .rd-title {
            font-size: 1.28rem;
        }
        .rd-tl-step {
            width: 72px;
        }
        .rd-tl-date {
            white-space: normal;
        }
        .rd-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endonce
