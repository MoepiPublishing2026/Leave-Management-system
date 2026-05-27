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
        padding-left: 15px;
        border-left: 6px solid #138056;
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
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        box-shadow: 0 6px 22px rgba(15, 61, 46, 0.06);
        padding: 10px 10px 12px;
    }
    .calendar-page .calendar-meta {
        margin-top: 10px;
        padding: 10px 12px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
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
        border-radius: 3px;
        border: 1px solid rgba(15, 23, 42, 0.1);
        flex-shrink: 0;
    }
    .calendar-page .calendar-legend-swatch--leave {
        background: #d4edda;
        border-left: 3px solid #138056;
    }
    .calendar-page .calendar-legend-swatch--holiday { background: #fcd9a3; }
    .calendar-page .calendar-legend-swatch--weekend { background: #e2e8f0; }
    .calendar-page .calendar-legend-swatch--today {
        background: #fff;
        box-shadow: inset 0 0 0 2px #4c1d95;
    }
    .calendar-page .calendar-legend-hint {
        margin: 6px 0 0;
        font-size: 0.74rem;
        color: #94a3b8;
    }
    .calendar-page .calendar {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 5px;
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
        padding: 8px 3px;
        border-radius: 7px;
        font-size: 0.78rem;
        letter-spacing: 0.02em;
    }
    .calendar-page .day {
        border: 1px solid #dbe3ef;
        border-radius: 8px;
        padding: 6px 6px 5px;
        min-height: 88px;
        background: #fff;
        position: relative;
        box-sizing: border-box;
        overflow: hidden;
        transition: box-shadow 0.15s ease;
    }
    .calendar-page .day-number {
        display: block;
        font-weight: 700;
        font-size: 0.88rem;
        color: #334155;
        line-height: 1.2;
    }
    .calendar-page--interactive .day:not(.day-weekend):not(.day-holiday) {
        cursor: pointer;
    }
    .calendar-page--interactive .day:not(.day-weekend):not(.day-holiday):hover {
        box-shadow: 0 3px 12px rgba(19, 128, 86, 0.14);
    }
    .calendar-page .day:focus-visible {
        outline: 2px solid #138056;
        outline-offset: 2px;
    }
    .calendar-page .day.has-leave {
        background: #d4edda !important;
        border-color: #6aab82;
        border-left-width: 4px;
        border-left-color: #138056;
        padding-left: 4px;
    }
    .calendar-page .day.has-leave .day-number {
        color: #0f3d2e;
    }
    .calendar-page .day.day-today {
        /* Keep Today distinct from leave styling */
        box-shadow: inset 0 0 0 2px #4c1d95;
    }
    .calendar-page .day.day-today .day-number {
        color: #4c1d95;
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
        background: #eef2f7 !important;
        border-color: #cbd5e1;
    }
    .calendar-page .day.day-weekend .day-number {
        color: #94a3b8;
    }
    .calendar-page .day.day-holiday {
        background: #fff3d6 !important;
        border-color: #e8b84a;
        border-left-width: 4px;
        border-left-color: #e8b84a;
        padding-left: 4px;
    }
    .calendar-page .day.day-holiday .day-number {
        color: #92400e;
    }
    .calendar-page .leave-count {
        position: absolute;
        left: 4px;
        right: 4px;
        bottom: 4px;
        font-weight: 700;
        color: #fff;
        font-size: 0.65rem;
        background: #138056;
        border: 1px solid #0f6644;
        border-radius: 6px;
        padding: 3px 5px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
    }
    .calendar-page .leave-count-short {
        display: none;
    }
    .calendar-page .holiday-label {
        position: absolute;
        left: 4px;
        right: 4px;
        bottom: 4px;
        padding: 3px 5px;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.9);
        font-size: 0.58rem;
        font-weight: 700;
        color: #92400e;
        text-align: center;
        line-height: 1.15;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
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
        padding: 8px 12px;
        border-radius: 8px;
        background: #f0faf4;
        border: 1px solid #c5e6d4;
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
    }
</style>
@endonce
