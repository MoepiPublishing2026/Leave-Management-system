@once
<style>
    .calendar-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        color: #1e293b;
    }
    .calendar-page h1,
    .calendar-page #calendar-heading {
        font-weight: 700;
        font-size: 28px;
        color: #29384d;
        margin: 0;
        letter-spacing: 0.01em;
        text-align: left;
    }
    .calendar-page .calendar-shell {
        width: 100%;
        max-width: 725px;
        margin: 0 auto;
        padding: 0 0 24px;
    }
    .calendar-page .calendar-panel {
        background: #fff;
        border: 1px solid #cfd5da;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        padding: 7px;
    }
    .calendar-page .calendar-meta {
        display: block;
        visibility: visible;
        margin-top: 14px;
        padding: 14px 16px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }
    .calendar-page .calendar-meta-title {
        margin: 0 0 8px;
        padding: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: #334155;
        line-height: 1.35;
    }
    .calendar-page .calendar-summary {
        margin: 0 0 10px;
        padding: 0;
        border: none;
        background: transparent;
        color: #111;
        font-size: 0.72rem;
        line-height: 1.45;
    }
    .calendar-page .calendar-summary strong {
        color: #0f766e;
    }
    .calendar-page .calendar-legend {
        display: block;
        visibility: visible;
        border: none;
        padding: 8px 0 0;
        border-top: 2px solid #d5dce2;
        background: transparent;
    }
    .calendar-page .calendar-legend-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 16px;
        align-items: center;
    }
    .calendar-page .calendar-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.72rem;
        font-weight: 400;
        color: #333;
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
        background: #ae3b8e;
    }
    .calendar-page .calendar-legend-swatch--holiday { background: #f5dfa8; }
    .calendar-page .calendar-legend-swatch--weekend { background: #a9c3c5; }
    .calendar-page .calendar-legend-swatch--today {
        background: #79b7df;
        
    }
    .calendar-page .calendar-legend-hint {
        margin: 8px 0 0;
        margin-top: 6px;
        font-size: 0.68rem;
        color: #777;
        line-height: 1.45;
    }
    .calendar-page .calendar {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 4px;
        align-items: stretch;
    }
    .calendar-page .calendar-spacer {
        min-height: 0;
        height: 0;
    }
    .calendar-page .day-header {
        font-weight: 700;
        text-align: center;
        background: #0c5b83;
        color: #fff;
        padding: 10px 4px;
        border-radius: 10px;
        font-size: 0.70rem;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }
    .calendar-page .day {
        border: none;
        border-radius: 11px;
        padding: 7px 7px 6px;
        min-height: 69px;
        background:  #c8d1da;
        position: relative;
        box-sizing: border-box;
        overflow: hidden;
        box-shadow: none;
        transition: transform 0.12s ease, box-shadow 0.15s ease;
    }
    .calendar-page .day-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.25rem;
        height: 1.25rem;
        font-weight: 700;
        font-size: 0.62rem;
        color: #53606b;
        line-height: 1;
    }
    .calendar-page--interactive .day:not(.day-weekend):not(.day-holiday) {
        cursor: pointer;
    }
    .calendar-page--interactive .day:not(.day-weekend):not(.day-holiday):hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(15, 118, 110, 0.16), inset 0 1px 0 rgba(255, 255, 255, 0.45);
    }
    .calendar-page .day:focus-visible {
        outline: 2px solid #0f766e;
        outline-offset: 2px;
    }
    .calendar-page .day.has-leave {
        background:  #c8d1da !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
    }
    .calendar-page .day.has-leave .day-number {
        color: #53606b;
    }
    .calendar-page .day.day-today:not(.day-holiday):not(.has-leave) {
        background: #8fc7cd !important;
        
    }
    .calendar-page .day.day-today.has-leave {
        box-shadow: inset 0 0 0 2px #0f766e, 0 2px 10px rgba(15, 118, 110, 0.12);
    }
    .calendar-page .day.day-today .day-number {
        color: #1776a5;
        background: #b8ddec;
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
        background: #a9c3c5 !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35);
    }
    .calendar-page .day.day-weekend .day-number {
        color: #53686b;
    }
    .calendar-page .day.day-holiday {
        background: #f8e6b5 !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
    }
    .calendar-page .day.day-holiday .day-number {
        color: #92400e;
        background: rgba(255, 255, 255, 0.45);
        border-radius: 999px;
    }
    .calendar-page .leave-count {
        position: absolute;
        left: 9px;
        right: 9px;
        bottom: 8px;
        font-weight: 700;
        color: #fff;
        font-size: 0.60rem;
        background:  #ae3b8e;
        border: none;
        border-radius: 999px;
        padding: 4px 6px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
        box-shadow: none;
    }
    .calendar-page .leave-count-short {
        display: none;
    }
    .calendar-page .no-leave-today-label {
        position: absolute;
        left: 7px;
        right: 7px;
        bottom: 8px;
        font-weight: 700;
        color: #0875a5;
        font-size: 0.58rem;
        background: #b8ddec;
        border: none;
        border-radius: 999px;
        padding: 4px 5px;
        text-align: center;
        line-height: 1.2;
        letter-spacing: 0.01em;
    }
    .calendar-page .holiday-label {
        position: absolute;
        left: 7px;
        right: 7px;
        bottom: 8px;
        padding: 4px 6px;
        border-radius: 999px;
        background: #fff;
        font-size: 0.6rem;
        font-weight: 700;
        color: #c49400;
        text-align: center;
        line-height: 1.15;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        box-shadow: none;
    }
    .calendar-page .day.has-leave .holiday-label {
        display: none;
        background: #c8d1da
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
        color: #0f766e;
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
            max-width: 1000px;
        }
        .calendar-page .calendar-panel {
            margin: 12px 4px 0;
            padding: 6px;
            border-radius: 17px;
            background: #fff;
            border: 2px solid #cbd5df;
            box-shadow: none;
        }
        .calendar-page .calendar-meta {
            margin: 12px 4px 0;
            padding: 10px;
            border-radius: 17px;
            background: #fff;
            border: 2px solid #cbd5df;
            box-shadow: none;
        }
        .calendar-page .calendar-meta-title {
            margin-bottom: 7px;
            font-size: 0.76rem;
            color: #111;
        }
        .calendar-page .calendar-legend-item {
            font-size: 0.78rem;
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
