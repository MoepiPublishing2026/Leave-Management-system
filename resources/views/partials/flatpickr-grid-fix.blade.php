@once
<style>
    /* Keep Flatpickr weekdays and dates in 7 equal columns (fixes 8-per-row wrap) */
    .flatpickr-calendar {
        width: 307.875px;
        box-sizing: border-box;
    }
    .flatpickr-calendar .flatpickr-innerContainer,
    .flatpickr-calendar .flatpickr-rContainer,
    .flatpickr-calendar .flatpickr-days,
    .flatpickr-calendar .dayContainer {
        width: 307.875px;
        max-width: 307.875px;
        box-sizing: border-box;
    }
    .flatpickr-calendar .dayContainer {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
    }
    .flatpickr-calendar span.flatpickr-weekday,
    .flatpickr-calendar .flatpickr-day {
        flex: 0 0 14.2857143%;
        max-width: 14.2857143%;
        width: 14.2857143%;
        box-sizing: border-box;
    }
    .flatpickr-calendar .flatpickr-day {
        height: 39px;
        line-height: 39px;
    }

    /* Vertically center month arrows with the month/year label */
    .flatpickr-months {
        display: flex;
        align-items: center;
        position: relative;
        height: 40px;
    }
    .flatpickr-months .flatpickr-month {
        height: 40px;
        overflow: visible;
    }
    .flatpickr-months .flatpickr-prev-month,
    .flatpickr-months .flatpickr-next-month {
        position: absolute;
        top: 0;
        height: 40px;
        width: 34px;
        padding: 0;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        box-sizing: border-box;
    }
    .flatpickr-months .flatpickr-prev-month {
        left: 0;
    }
    .flatpickr-months .flatpickr-next-month {
        right: 0;
    }
    .flatpickr-months .flatpickr-prev-month svg,
    .flatpickr-months .flatpickr-next-month svg {
        width: 14px;
        height: 14px;
        display: block;
        vertical-align: middle;
    }
    .flatpickr-current-month {
        position: absolute !important;
        left: 34px !important;
        width: calc(100% - 68px) !important;
        height: 40px !important;
        padding: 0 !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
        line-height: 1 !important;
        transform: none !important;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months,
    .flatpickr-current-month input.cur-year {
        font-size: 15px;
        font-weight: 600;
        line-height: 1;
        vertical-align: middle;
        height: auto;
        padding: 0;
        margin: 0;
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
</style>
@endonce
