@once
<style>
    /* Match the full Calendar page: 7 equal columns + green / grey cells */
    .flatpickr-calendar {
        width: 332px;
        box-sizing: border-box;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 14px 36px rgba(15, 23, 42, 0.16);
        padding: 8px 8px 10px;
        overflow: hidden;
    }
    .flatpickr-calendar.arrowTop:before,
    .flatpickr-calendar.arrowTop:after,
    .flatpickr-calendar.arrowBottom:before,
    .flatpickr-calendar.arrowBottom:after {
        display: none;
    }
    .flatpickr-calendar .flatpickr-innerContainer,
    .flatpickr-calendar .flatpickr-rContainer,
    .flatpickr-calendar .flatpickr-days,
    .flatpickr-calendar .dayContainer {
        width: 316px;
        max-width: 316px;
        min-width: 316px;
        box-sizing: border-box;
    }
    .flatpickr-calendar .dayContainer {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
        min-height: 0;
        padding: 0;
        border: none;
        outline: none;
    }
    .flatpickr-weekdays {
        height: auto;
        background: transparent;
        margin-top: 4px;
        padding: 0;
    }
    .flatpickr-weekdaycontainer {
        display: flex;
        flex-wrap: nowrap;
        gap: 4px;
        justify-content: flex-start;
        width: 316px;
        max-width: 316px;
    }
    .flatpickr-calendar .dayContainer {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        justify-content: flex-start;
    }
    .flatpickr-calendar span.flatpickr-weekday {
        flex: 1 1 0;
        min-width: 0;
        max-width: none;
        width: auto;
        height: auto;
        margin: 0;
        padding: 7px 0;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #0c5b83;
        color: #fff !important;
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        line-height: 1.2;
        border-radius: 8px;
    }
    .flatpickr-calendar .flatpickr-days {
        padding-top: 6px;
        border: none;
    }
    .flatpickr-calendar .flatpickr-day {
        flex: 0 0 calc((100% - 24px) / 7);
        max-width: calc((100% - 24px) / 7);
        width: calc((100% - 24px) / 7);
        height: 38px;
        line-height: 38px;
        margin: 0;
        box-sizing: border-box;
        border: none;
        border-radius: 10px;
        background: #c8d1da;
        color: #53606b;
        font-weight: 700;
        font-size: 0.86rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45);
    }
    .flatpickr-calendar .flatpickr-day:nth-child(7n+1),
    .flatpickr-calendar .flatpickr-day:nth-child(7n) {
        background: #a9c3c5;
        color: #53686b;
    }
    .flatpickr-calendar .flatpickr-day.prevMonthDay,
    .flatpickr-calendar .flatpickr-day.nextMonthDay {
        background: #dce3e8;
        color: #94a3b8;
        box-shadow: none;
    }
    .flatpickr-calendar .flatpickr-day:hover,
    .flatpickr-calendar .flatpickr-day:focus {
        background: #b7c4ce;
        border-color: transparent;
        color: #3d4a54;
    }
    .flatpickr-calendar .flatpickr-day.today {
        background: #8fc7cd;
        color: #1776a5;
        box-shadow: none;
        font-weight: 800;
    }
    .flatpickr-calendar .flatpickr-day.today:hover,
    .flatpickr-calendar .flatpickr-day.today:focus {
        background: #7fbcc4;
        color: #0c5b83;
    }
    .flatpickr-calendar .flatpickr-day.selected,
    .flatpickr-calendar .flatpickr-day.startRange,
    .flatpickr-calendar .flatpickr-day.endRange,
    .flatpickr-calendar .flatpickr-day.selected:hover,
    .flatpickr-calendar .flatpickr-day.selected:focus {
        background: #0c5b83 !important;
        color: #fff !important;
        border-color: #0c5b83 !important;
        box-shadow: none;
    }
    .flatpickr-calendar .flatpickr-day.selected.today {
        background: #0c5b83 !important;
        color: #fff !important;
    }
    .flatpickr-calendar .flatpickr-day.flatpickr-disabled,
    .flatpickr-calendar .flatpickr-day.flatpickr-disabled:hover {
        background: #cbd5e1 !important;
        color: #64748b !important;
        cursor: not-allowed;
        box-shadow: none;
    }
    .flatpickr-calendar .flatpickr-day.inRange,
    .flatpickr-calendar .flatpickr-day.prevMonthDay.inRange,
    .flatpickr-calendar .flatpickr-day.nextMonthDay.inRange {
        background: #b8ddec;
        box-shadow: none;
        border-color: transparent;
        color: #0c5b83;
    }

    .flatpickr-months {
        display: flex;
        align-items: center;
        position: relative;
        height: 44px;
        background: transparent;
    }
    .flatpickr-months .flatpickr-month {
        height: 44px;
        overflow: visible;
        background: transparent;
        color: #2c3e50;
    }
    .flatpickr-months .flatpickr-prev-month,
    .flatpickr-months .flatpickr-next-month {
        position: absolute;
        top: 2px;
        height: 40px;
        width: 40px;
        padding: 0;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        box-sizing: border-box;
        border-radius: 50%;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #0c5b83;
        fill: #0c5b83;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
    }
    .flatpickr-months .flatpickr-prev-month:hover,
    .flatpickr-months .flatpickr-next-month:hover {
        background: rgba(12, 91, 131, 0.12);
        border-color: #0c5b83;
        color: #0c5b83;
        fill: #0c5b83;
    }
    .flatpickr-months .flatpickr-prev-month {
        left: 4px;
    }
    .flatpickr-months .flatpickr-next-month {
        right: 4px;
    }
    .flatpickr-months .flatpickr-prev-month svg,
    .flatpickr-months .flatpickr-next-month svg {
        width: 16px;
        height: 16px;
        display: block;
        vertical-align: middle;
    }
    .flatpickr-current-month {
        position: absolute !important;
        left: 48px !important;
        width: calc(100% - 96px) !important;
        height: 44px !important;
        padding: 0 !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
        line-height: 1 !important;
        transform: none !important;
        color: #2c3e50;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months,
    .flatpickr-current-month input.cur-year {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.2;
        vertical-align: middle;
        height: auto;
        padding: 6px 8px;
        margin: 0;
        color: #2c3e50;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        appearance: none;
        -webkit-appearance: none;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months:hover,
    .flatpickr-current-month input.cur-year:hover,
    .flatpickr-current-month .flatpickr-monthDropdown-months:focus,
    .flatpickr-current-month input.cur-year:focus {
        border-color: #0c5b83;
        box-shadow: 0 0 0 3px rgba(12, 91, 131, 0.22);
        outline: none;
        background: #fff;
    }
    .flatpickr-current-month .numInputWrapper {
        display: inline-flex;
        align-items: center;
        height: auto;
        width: auto;
    }
    .flatpickr-current-month .numInputWrapper span {
        display: none;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months option {
        font-weight: 700;
        color: #334155;
        background: #fff;
    }
</style>
@endonce
