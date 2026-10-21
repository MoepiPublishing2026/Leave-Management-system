<style>
    .pdf-content {
        font-family: "Segoe UI", DejaVu Sans, sans-serif;
        color: #243044;
    }
    .header { margin: 0 0 10px; }
    .title {
        font-size: 22px;
        font-weight: 700;
        margin: 0;
        color: #243044;
        letter-spacing: -0.02em;
        line-height: 1.15;
    }
    .subtitle {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: 11px;
        font-weight: 400;
    }
    .meta {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: 10px;
    }
    .pdf-cards {
        width: 100%;
        border-collapse: separate;
        border-spacing: 8px;
        margin: 4px 0 12px;
    }
    .pdf-card {
        width: 20%;
        text-align: center;
        vertical-align: middle;
        color: #fff;
        padding: 12px 6px;
        border-radius: 12px;
        height: 64px;
    }
    .pdf-card-title {
        font-size: 10px;
        font-weight: 600;
        color: #fff;
        margin: 0 0 5px;
        line-height: 1.25;
    }
    .pdf-card-value {
        font-size: 20px;
        font-weight: 700;
        color: #fff;
        line-height: 1.1;
    }
    .reports-section-title {
        font-size: 13px;
        font-weight: 700;
        color: #243044;
        margin: 4px 0 8px;
    }
    .reports-table-shell {
        border: 1.5px solid #C0C0C0;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }
    table.data {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }
    table.data th {
        background: #fff;
        text-align: left;
        padding: 8px 10px;
        color: #1e293b;
        font-weight: 800;
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        border-bottom: 1.5px solid #C0C0C0;
        border-right: 1.5px solid #C0C0C0;
    }
    table.data td {
        padding: 8px 10px;
        vertical-align: middle;
        color: #1e293b;
        background: #fff;
        border-bottom: 1.5px solid #C0C0C0;
        border-right: 1.5px solid #C0C0C0;
    }
    table.data th:last-child,
    table.data td:last-child {
        border-right: 0;
    }
    table.data tr:last-child td {
        border-bottom: 0;
    }
    .muted { color: #6b7280; }
    .reports-empty {
        text-align: center;
        font-style: italic;
        font-weight: 600;
        color: #475569;
    }
    .pdf-badge {
        display: inline-block;
        min-width: 62px;
        padding: 3px 8px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 8px;
        text-align: center;
        letter-spacing: 0.02em;
        border: 1.5px solid #767672;
        background: #e2e2e2;
        color: #555;
    }
    .pdf-badge--pending {
        background: #cebaf6;
        border-color: #a33d86;
        color: #7a2a7a;
    }
    .pdf-badge--overdue {
        background: #cebaf6;
        border-color: #a33d86;
        color: #fff;
    }
    .pdf-badge--approved {
        background: #9db4ff;
        border-color: #2a6fc0;
        color: #1f3f8f;
    }
    .pdf-badge--rejected {
        background: #f9c3c9;
        border-color: #d5253a;
        color: #a31b2e;
    }
    .pdf-badge--withdrawn {
        background: #e2e2e2;
        border-color: #767672;
        color: #555;
    }
</style>
