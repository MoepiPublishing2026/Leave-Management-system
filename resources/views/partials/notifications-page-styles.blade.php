@once
<style>
    .notifications-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        max-width: none;
        width: 100%;
        margin: 0;
        padding: 0 0 32px;
        color: #263244;
        box-sizing: border-box;
    }

    .notifications-page h1 {
        font-weight: 700;
        font-size: 34px;
        color: #263244;
        margin: 0 0 8px;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }

    .notifications-lead {
        margin: 0 0 18px;
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 400;
        line-height: 1.5;
    }
    .notifications-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
        padding: 0;
        background: transparent;
        border: none;
        border-radius: 0;
        box-shadow: none;
    }

    html body main .notifications-btn,
    html body main form .notifications-btn,
    html body main form button.notifications-btn[type="submit"] {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px !important;
        min-height: 0 !important;
        min-width: 0 !important;
        height: auto !important;
        font-weight: 700;
        font-size: 13px !important;
        line-height: 1.2 !important;
        border-radius: 8px !important;
        cursor: pointer;
        border: 1px solid #d4e500;
        text-decoration: none;
        background: #d4e514;
        color: #000000;
        transition: background-color 0.15s ease;
    }

    .notifications-btn:hover,
    .notifications-btn--primary:hover,
    .notifications-btn--secondary:hover {
        background: #c4d400;
        color: #000000;
    }


    .notifications-list {
        list-style: none;
        padding: 0;
        margin: 0;
        border: none;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
        overflow: visible;
        display: flex;
        flex-direction: column;
        gap: 11px;
    }

    .notification-item {
        border: 2px solid #d4e500;
        border-radius: 20px;
        background: #ffffff;
        overflow: hidden;
    }

    .notification-item:last-child {
        border-bottom: 2px solid #d4e500;
    }

    .notification-link {
        display: flex;
        gap: 14px;
        align-items: center;
        padding: 14px 16px;
        min-height: 0;
        text-decoration: none;
        color: inherit;
        transition: background-color 0.12s ease;
    }

    .notification-link:hover {
        background: #fafbea;
    }
    .notification-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-top: 0;
        flex-shrink: 0;
        background: #263244;
    }

    .notification-item.is-unread .notification-dot {
        background: #c4dc28;
    }

    .notification-body {
        min-width: 0;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .notification-message {
        font-weight: 400;
        color: #080808;
        font-size: 0.95rem;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .notification-meta {
        margin-top: 0;
        color: #777777;
        font-size: 0.82rem;
        font-weight: 700;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .notifications-empty {
        padding: 30px 20px;
        text-align: center;
        color: #64748b;
        font-size: 0.95rem;
        border: 2px solid #d4e500;
        border-radius: 20px;
        background: #ffffff;
    }

    .notifications-pagination {
        margin-top: 24px;
    }
    .notifications-pagination .pagination {
        margin: 0;
        flex-wrap: wrap;
        gap: 4px;
    }
    .notifications-pagination .page-link {
        padding: 6px 10px;
        font-size: 0.88rem;
        line-height: 1.2;
        color: #263244;
        border-color: #d4e500;
    }
    .notifications-pagination .page-item.active .page-link {
        background: #d4e514;
        border-color: #d4e500;
        color: #000;
    }
    .notifications-pagination svg {
        width: 16px;
        height: 16px;
    }

    @media (max-width: 768px) {
        .notifications-page h1 { font-size: 28px; }
        .notifications-toolbar { flex-direction: column; align-items: stretch; }
    }
</style>
@endonce

