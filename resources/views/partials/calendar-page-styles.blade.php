@once
<style>
    .calendar-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        color: #1e293b;
    }
    .calendar-page h1,
    .calendar-page #calendar-heading {
        font-weight: 700;
        font-size: 40px;
        color: #2c3e50;
        margin: 0;
        letter-spacing: 0.04em;
        text-align: left;
    }
    .calendar-page .calendar-shell {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 4px 24px;
    }
    .calendar-page .calendar-panel {
        background: #eef2f6;
        border: none;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        padding: 14px;
    }
    .calendar-page .calendar-meta {
        margin-top: 14px;
        padding: 14px 16px;
        border-radius: 12px;
        background: #e2e8f0;
        border: none;
    }
    .calendar-page .calendar-summary {
        margin: 0 0 8px;
        padding: 0;
        border: none;
        background: transparent;
        color: #475569;
        font-size: 0.84rem;
    }
    .calendar-page .calendar-summary strong {
        color: #138056;
    }
    .calendar-page .calendar-legend {
        padding: 0;
        border: none;
        background: transparent;
    }
    .calendar-page .calendar-legend-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 12px;
        align-items: center;
    }
    .calendar-page .calendar-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.76rem;
        color: #64748b;
        white-space: nowrap;
    }
    .calendar-page .calendar-legend-swatch {
        width: 14px;
        height: 14px;
        border-radius: 4px;
        border: none;
        flex-shrink: 0;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.08);
    }
    .calendar-page .calendar-legend-swatch--leave {
        background: #b8dfc8;
    }
    .calendar-page .calendar-legend-swatch--holiday { background: #fcd9a3; }
    .calendar-page .calendar-legend-swatch--weekend { background: #cbd5e1; }
    .calendar-page .calendar-legend-swatch--today {
        background: #f0dff5;
        box-shadow: inset 0 0 0 2px #C154C1;
    }
    .calendar-page .calendar-legend-hint {
        margin: 6px 0 0;
        font-size: 0.74rem;
        color: #94a3b8;
    }
    .calendar-page .calendar {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 7px;
        align-items: stretch;
    }
    .calendar-page .calendar-spacer {
        min-height: 0;
        height: 0;
    }
    .calendar-page .day-header {
        font-weight: 700;
        text-align: center;
        background: #138056;
        color: #fff;
        padding: 9px 4px;
        border-radius: 8px;
        font-size: 0.76rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .calendar-page .day {
        border: none;
        border-radius: 10px;
        padding: 7px 7px 6px;
        min-height: 92px;
        background: #e2e8f0;
        position: relative;
        box-sizing: border-box;
        overflow: hidden;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45);
        transition: transform 0.12s ease, box-shadow 0.15s ease;
    }
    .calendar-page .day-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.6rem;
        height: 1.6rem;
        font-weight: 700;
        font-size: 0.86rem;
        color: #334155;
        line-height: 1;
    }
    .calendar-page--interactive .day:not(.day-weekend):not(.day-holiday) {
        cursor: pointer;
    }
    .calendar-page--interactive .day:not(.day-weekend):not(.day-holiday):hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(19, 128, 86, 0.16), inset 0 1px 0 rgba(255, 255, 255, 0.45);
    }
    .calendar-page .day:focus-visible {
        outline: 2px solid #138056;
        outline-offset: 2px;
    }
    .calendar-page .day.has-leave {
        background: #b8dfc8 !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
    }
    .calendar-page .day.has-leave .day-number {
        color: #0f3d2e;
    }
    .calendar-page .day.day-today:not(.day-holiday):not(.has-leave) {
        background: #f0dff5 !important;
        box-shadow: inset 0 0 0 2px #C154C1, 0 2px 10px rgba(193, 84, 193, 0.12);
    }
    .calendar-page .day.day-today.has-leave {
        box-shadow: inset 0 0 0 2px #C154C1, 0 2px 10px rgba(193, 84, 193, 0.12);
    }
    .calendar-page .day.day-today .day-number {
        color: #9b3d9b;
        background: rgba(193, 84, 193, 0.14);
        border-radius: 999px;
    }
    .calendar-page .no-apply-icon {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 16px;
        height: 16px;
        color: #ef4444;
        opacity: 0;
        transform: scale(0.92);
        transition: opacity 0.12s ease, transform 0.12s ease;
        pointer-events: none;
        z-index: 2;
    }
    .calendar-page .day:hover .no-apply-icon,
    .calendar-page .day:focus-visible .no-apply-icon {
        opacity: 0.95;
        transform: scale(1);
    }
    .calendar-page .no-apply-icon svg {
        width: 100%;
        height: 100%;
        display: block;
        stroke: currentColor;
        fill: none;
        stroke-width: 2.4;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .calendar-page .day.day-weekend {
        background: #cbd5e1 !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35);
    }
    .calendar-page .day.day-weekend .day-number {
        color: #64748b;
    }
    .calendar-page .day.day-holiday {
        background: #fde8b8 !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
    }
    .calendar-page .day.day-holiday .day-number {
        color: #92400e;
        background: rgba(255, 255, 255, 0.45);
        border-radius: 999px;
    }
    .calendar-page .leave-count {
        position: absolute;
        left: 5px;
        right: 5px;
        bottom: 5px;
        font-weight: 700;
        color: #fff;
        font-size: 0.64rem;
        background: #138056;
        border: none;
        border-radius: 999px;
        padding: 4px 6px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
        box-shadow: 0 2px 6px rgba(19, 128, 86, 0.25);
    }
    .calendar-page .leave-count-short {
        display: none;
    }
    .calendar-page .no-leave-today-label {
        position: absolute;
        left: 5px;
        right: 5px;
        bottom: 5px;
        font-weight: 700;
        color: #9b3d9b;
        font-size: 0.62rem;
        background: rgba(193, 84, 193, 0.16);
        border: none;
        border-radius: 999px;
        padding: 4px 6px;
        text-align: center;
        line-height: 1.2;
        letter-spacing: 0.01em;
    }
    .calendar-page .holiday-label {
        position: absolute;
        left: 5px;
        right: 5px;
        bottom: 5px;
        padding: 4px 6px;
        border-radius: 999px;
        background: #fff;
        font-size: 0.6rem;
        font-weight: 700;
        color: #b45309;
        text-align: center;
        line-height: 1.15;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        box-shadow: 0 2px 6px rgba(180, 83, 9, 0.12);
    }
    .calendar-page .day.has-leave .holiday-label {
        display: none;
    }
    .calendar-page ul.leave-details-list {
        padding-left: 20px;
        margin-bottom: 0;
    }
    .calendar-page .calendar-modal-group + .calendar-modal-group {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
    }
    .calendar-page .calendar-modal-type {
        margin: 0 0 6px;
        font-size: 0.9rem;
        font-weight: 700;
        color: #138056;
    }
    .calendar-page .calendar-modal-body {
        max-height: min(70vh, 480px);
        overflow-y: auto;
    }
    .calendar-page .calendar-modal-summary {
        margin: 0 0 12px;
        padding: 10px 14px;
        border-radius: 10px;
        background: #dcfce7;
        border: none;
        font-size: 0.9rem;
        color: #334155;
    }

    @media (max-width: 768px) {
        .calendar-page h1,
        .calendar-page #calendar-heading {
            font-size: 1.55rem !important;
            padding-left: 10px !important;
        }
        .calendar-page .calendar-shell {
            padding: 0;
        }
        .calendar-page .calendar-panel {
            padding: 6px 4px 8px;
            border-radius: 10px;
        }
        .calendar-page .calendar-meta {
            padding: 8px 10px;
        }
        .calendar-page .day-header {
            font-size: 0.58rem;
            padding: 5px 1px;
        }
        .calendar-page .day {
            min-height: 52px;
            padding: 3px 2px 2px;
        }
        .calendar-page .day-number {
            font-size: 0.72rem;
        }
        .calendar-page .leave-count {
            left: auto;
            right: 2px;
            bottom: 2px;
            min-width: 16px;
            padding: 2px 4px;
            font-size: 0.55rem;
        }
        .calendar-page .leave-count-full {
            display: none;
        }
        .calendar-page .leave-count-short {
            display: inline;
        }
        .calendar-page .holiday-label {
            font-size: 0.48rem;
        }
        .calendar-page .no-leave-today-label {
            font-size: 0.5rem;
            padding: 2px 4px;
        }
        .calendar-page .calendar {
            gap: 4px;
        }
    }
</style>
@endonce
