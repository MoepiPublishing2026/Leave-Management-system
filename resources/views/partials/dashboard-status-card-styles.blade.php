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
        min-width: 5.5rem;
        padding: 5px 10px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.78rem;
        text-align: center;
        border: 1px solid transparent;
        letter-spacing: 0.01em;
        line-height: 1.25;
        color: #fff;
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
    .reports-page .request-badge--pending,
    .rd-badge--pending,
    .requests-filter--pending.is-active {
        background: {{ $statusCardColors['pending'] ?? '#C291A4' }} !important;
        color: #fff !important;
        border-color: {{ $statusCardColors['pending'] ?? '#C291A4' }} !important;
    }
    .request-badge--approved,
    .reports-page .request-badge--approved,
    .rd-badge--approved,
    .requests-filter--approved.is-active {
        background: {{ $statusCardColors['approved'] ?? '#E27A53' }} !important;
        color: #fff !important;
        border-color: {{ $statusCardColors['approved'] ?? '#E27A53' }} !important;
    }
    .request-badge--rejected,
    .reports-page .request-badge--rejected,
    .rd-badge--rejected,
    .requests-filter--rejected.is-active {
        background: {{ $statusCardColors['rejected'] ?? '#87AFC7' }} !important;
        color: #fff !important;
        border-color: {{ $statusCardColors['rejected'] ?? '#87AFC7' }} !important;
    }
    .request-badge--withdrawn,
    .reports-page .request-badge--withdrawn,
    .rd-badge--withdrawn,
    .requests-filter--withdrawn.is-active {
        background: {{ $statusCardColors['withdrawn'] ?? '#D2B55B' }} !important;
        color: #fff !important;
        border-color: {{ $statusCardColors['withdrawn'] ?? '#D2B55B' }} !important;
    }
    .request-badge--overdue,
    .reports-page .request-badge--overdue,
    .rd-badge--overdue,
    .requests-filter--overdue.is-active {
        background: {{ $statusCardColors['overdue'] ?? '#C154C1' }} !important;
        color: #fff !important;
        border-color: {{ $statusCardColors['overdue'] ?? '#C154C1' }} !important;
    }
</style>
