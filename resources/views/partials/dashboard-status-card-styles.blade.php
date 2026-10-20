@php
    $statusCardColors = config('leave_status_colors.colors', []);
@endphp
<style>
    /* Card colours are the source of truth — charts/badges follow config/leave_status_colors.php */
    .cards .card,
    .reports-page .cards .card {
        box-shadow: 0 0 10px rgba(167, 189, 141, 0.85) !important;
    }
    .cards .card:hover,
    .reports-page .cards .card:hover {
        box-shadow: 0 0 14px rgba(167, 189, 141, 1) !important;
    }
    .request-badge,
    .rd-badge {
        display: inline-block;
        min-width: 0;
        padding: 4px 14px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 0.8rem;
        text-align: center;
        border: 2px solid #666;
        letter-spacing: 0;
        line-height: 1.25;
        color: #263248;
        box-shadow: none;
    }
    .card.card-all,
    a.card.card-all,
    .reports-page .card.card-all {
        background: {{ $statusCardColors['all'] ?? '#00C0A3' }} !important;
        border-color: {{ $statusCardColors['all'] ?? '#00C0A3' }} !important;
        color: #fff !important;
    }
    .card.card-pending,
    a.card.card-pending,
    .reports-page .card.card-pending {
        background: {{ $statusCardColors['pending'] ?? '#C291A4' }} !important;
        border-color: {{ $statusCardColors['pending'] ?? '#C291A4' }} !important;
        color: #fff !important;
    }
    .card.card-approved,
    a.card.card-approved,
    .reports-page .card.card-approved {
        background: {{ $statusCardColors['approved'] ?? '#E27A53' }} !important;
        border-color: {{ $statusCardColors['approved'] ?? '#E27A53' }} !important;
        color: #fff !important;
    }
    .card.card-rejected,
    a.card.card-rejected,
    .reports-page .card.card-rejected {
        background: {{ $statusCardColors['rejected'] ?? '#87AFC7' }} !important;
        border-color: {{ $statusCardColors['rejected'] ?? '#87AFC7' }} !important;
        color: #fff !important;
    }
    .card.card-withdrawn,
    a.card.card-withdrawn,
    .reports-page .card.card-withdrawn {
        background: {{ $statusCardColors['withdrawn'] ?? '#D2B55B' }} !important;
        border-color: {{ $statusCardColors['withdrawn'] ?? '#D2B55B' }} !important;
        color: #fff !important;
    }
    .card.card-overdue,
    a.card.card-overdue,
    .reports-page .card.card-overdue {
        background: {{ $statusCardColors['overdue'] ?? '#C154C1' }} !important;
        border-color: {{ $statusCardColors['overdue'] ?? '#C154C1' }} !important;
        color: #fff !important;
    }
    .card.card-onleave,
    a.card.card-onleave,
    .reports-page .card.card-onleave {
        background: {{ $statusCardColors['on_leave'] ?? '#D2B55B' }} !important;
        border-color: {{ $statusCardColors['on_leave'] ?? '#D2B55B' }} !important;
        color: #fff !important;
    }
    .card.card-all .card-title,
    .card.card-all .card-value,
    .card.card-pending .card-title,
    .card.card-pending .card-value,
    .card.card-approved .card-title,
    .card.card-approved .card-value,
    .card.card-rejected .card-title,
    .card.card-rejected .card-value,
    .card.card-withdrawn .card-title,
    .card.card-withdrawn .card-value,
    .card.card-overdue .card-title,
    .card.card-overdue .card-value,
    .card.card-onleave .card-title,
    .card.card-onleave .card-value,
    .reports-page .card.card-all .card-title,
    .reports-page .card.card-all .card-value,
    .reports-page .card.card-pending .card-title,
    .reports-page .card.card-pending .card-value,
    .reports-page .card.card-approved .card-title,
    .reports-page .card.card-approved .card-value,
    .reports-page .card.card-rejected .card-title,
    .reports-page .card.card-rejected .card-value,
    .reports-page .card.card-withdrawn .card-title,
    .reports-page .card.card-withdrawn .card-value,
    .reports-page .card.card-overdue .card-title,
    .reports-page .card.card-overdue .card-value,
    .reports-page .card.card-onleave .card-title,
    .reports-page .card.card-onleave .card-value {
        color: #fff !important;
    }
    .request-badge--pending,
    .request-badge--overdue,
    .rd-badge--pending,
    .rd-badge--overdue {
        background: #cebaf6 !important;
        border-color: #a33d86 !important;
        color: #7a2a7a !important;
    }
    .request-badge--approved,
    .rd-badge--approved {
        background: #9db4ff !important;
        border-color: #2a6fc0 !important;
        color: #1f3f8f !important;
    }
    .request-badge--rejected,
    .rd-badge--rejected {
        background: #f9c3c9 !important;
        border-color: #d5253a !important;
        color: #a31b2e !important;
    }
    .request-badge--withdrawn,
    .rd-badge--withdrawn {
        background: #e2e2e2 !important;
        border-color: #767672 !important;
        color: #555 !important;
    }
</style>
