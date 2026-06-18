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
</style>
@endonce
