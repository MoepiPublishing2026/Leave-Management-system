@once
<style>
    .notifications-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 4px 32px;
        color: #1e293b;
    }
    .notifications-page h1 {
        font-weight: 700;
        font-size: 40px;
        color: #2c3e50;
        margin: 0 0 8px;
        padding-left: 15px;
        border-left: 6px solid #138056;
        letter-spacing: 0.04em;
    }
    .notifications-lead {
        margin: 0 0 18px;
        color: #64748b;
        font-size: 0.92rem;
    }
    .notifications-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding: 14px 16px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 6px 22px rgba(15, 61, 46, 0.06);
    }
    .notifications-btn {
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
    .notifications-btn--primary { background: #138056; color: #fff; }
    .notifications-btn--primary:hover { background: #0f6b47; color: #fff; }
    .notifications-btn--secondary {
        background: #fff;
        color: #334155;
        border: 1px solid #cbd5e1;
    }
    .notifications-btn--secondary:hover { background: #f1f5f9; color: #0f172a; }

    .notifications-list {
        list-style: none;
        padding: 0;
        margin: 0;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 6px 22px rgba(15, 61, 46, 0.06);
        overflow: hidden;
    }
    .notification-item {
        border-bottom: 1px solid #e2e8f0;
    }
    .notification-item:last-child { border-bottom: none; }
    .notification-link {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 14px 16px;
        text-decoration: none;
        color: inherit;
        transition: background-color 0.12s ease;
    }
    .notification-link:hover { background: #f8fafc; }
    .notification-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        margin-top: 5px;
        flex-shrink: 0;
        background: #94a3b8;
    }
    .notification-item.is-unread .notification-dot { background: #f59e0b; }
    .notification-body { min-width: 0; flex: 1; }
    .notification-message {
        font-weight: 700;
        color: #0f172a;
        line-height: 1.35;
    }
    .notification-meta {
        margin-top: 4px;
        color: #64748b;
        font-size: 0.84rem;
        font-weight: 600;
    }
    .notifications-empty {
        padding: 22px 16px;
        text-align: center;
        color: #64748b;
        font-style: italic;
    }
    .notifications-pagination {
        margin-top: 18px;
    }

    @media (max-width: 768px) {
        .notifications-page h1 { font-size: 28px; }
        .notifications-toolbar { flex-direction: column; align-items: stretch; }
    }
</style>
@endonce

