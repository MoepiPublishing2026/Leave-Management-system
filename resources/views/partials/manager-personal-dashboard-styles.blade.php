<style>
    @php $statusCardColors = config('leave_status_colors.colors', []); @endphp
    .mgr-stat-card.card-all { background: {{ $statusCardColors['all'] ?? '#00C0A3' }}; }
    .mgr-stat-card.card-pending { background: {{ $statusCardColors['pending'] ?? '#C291A4' }}; }
    .mgr-stat-card.card-approved { background: {{ $statusCardColors['approved'] ?? '#E27A53' }}; }
    .mgr-stat-card.card-rejected { background: {{ $statusCardColors['rejected'] ?? '#87AFC7' }}; }
    .mgr-personal-page,
    .mgr-team-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 4px 32px;
        color: #0f172a;
    }
    .mgr-dash-header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    }
    .mgr-dash-header h1 {
        margin: 0;
        font-size: 34px;
        font-weight: 700;
        padding-left: 12px;
        border-left: 6px solid #65a30d;
        letter-spacing: 0.03em;
    }
    .mgr-dash-header p {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 0.95rem;
    }
    .mgr-dash-switch {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #dbe3f0;
        color: #166534;
        font-weight: 700;
        text-decoration: none;
        font-size: 0.92rem;
    }
    .mgr-dash-switch:hover {
        background: #f0fdf4;
        border-color: #86efac;
    }
    .mgr-personal-cards {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 22px;
    }
    .mgr-stat-card {
        border-radius: 14px;
        padding: 14px 12px;
        text-align: center;
        text-decoration: none;
        color: #fff;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.12);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .mgr-stat-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.16);
    }
    .mgr-stat-card__label {
        font-size: 0.88rem;
        font-weight: 600;
        margin-bottom: 6px;
        line-height: 1.25;
    }
    .mgr-stat-card__value {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1;
    }
    .mgr-personal-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
        gap: 18px;
        align-items: start;
    }
    .mgr-dash-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
        padding: 16px;
        min-width: 0;
    }
    .mgr-dash-panel__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }
    .mgr-dash-panel__head h2 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
    }
    .dashboard-section-link {
        font-size: 0.88rem;
        font-weight: 700;
        color: #4c8615;
        text-decoration: none;
        white-space: nowrap;
    }
    .dashboard-section-link:hover {
        text-decoration: underline;
    }
    .dashboard-calendar-embed .calendar-shell {
        margin-top: 0;
    }
    .dashboard-calendar-embed__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
    }
    .dashboard-calendar-embed__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
    }
    .dashboard-calendar-embed .calendar-page h1 {
        display: none;
    }
    .mgr-requests-mini {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    .mgr-requests-mini th,
    .mgr-requests-mini td {
        padding: 10px 8px;
        border-bottom: 1px solid #eef2f7;
        text-align: left;
        vertical-align: top;
    }
    .mgr-requests-mini th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }
    .mgr-requests-empty {
        padding: 28px 12px;
        text-align: center;
        color: #64748b;
    }
    @media (max-width: 992px) {
        .mgr-personal-grid {
            grid-template-columns: 1fr;
        }
        .mgr-personal-cards {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 520px) {
        .mgr-personal-cards {
            grid-template-columns: 1fr;
        }
        .mgr-dash-header h1 {
            font-size: 28px;
        }
    }
</style>
