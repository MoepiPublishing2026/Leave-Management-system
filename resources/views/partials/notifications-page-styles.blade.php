@once
<style>
    .notifications-page {
        font-family: "Century Gothic", "Segoe UI", sans-serif;
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 4px 32px;
        color: #263244;
    }

    .notifications-page h1 {
        font-weight: 700;
        font-size: 52px;
        color: #263244;
        margin: 0 0 8px;
        letter-spacing: -1px;
        line-height: 1.2;
    }

    .notifications-lead {
        margin: 0 0 36px;
        color: #111111;
        font-size: 25px;
        font-weight: 400;
        line-height: 1.5;
    }
    .notifications-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 34px;
        padding: 0;
        background: transparent;
        border: none;
        border-radius: 0;
        box-shadow: none;
    }

    .notifications-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 17px 30px;
        min-height: 64px;
        font-weight: 700;
        font-size: 25px;
        border-radius: 14px;
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
        gap: 32px;
        align-items: center;
        padding: 20px 24px 20px 19px;
        min-height: 72px;
        text-decoration: none;
        color: inherit;
        transition: background-color 0.12s ease;
    }

    .notification-link:hover {
        background: #fafbea;
    }
    .notification-dot {
        width: 27px;
        height: 27px;
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
        font-size: 22px;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .notification-meta {
        margin-top: 0;
        color: #777777;
        font-size: 20px;
        font-weight: 700;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .notifications-empty {
        padding: 30px 20px;
        text-align: center;
        color: #64748b;
        font-size: 18px;
        border: 2px solid #d4e500;
        border-radius: 20px;
        background: #ffffff;
    }

    .notifications-pagination {
        margin-top: 24px;
    }

    @media (max-width: 768px) {
        .notifications-page h1 { font-size: 28px; }
        .notifications-toolbar { flex-direction: column; align-items: stretch; }
    }
</style>
@endonce

