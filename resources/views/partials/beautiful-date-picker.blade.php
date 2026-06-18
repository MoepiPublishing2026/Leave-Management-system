@once
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr" defer></script>
<style>
    .app-date-picker-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
    }
    .app-date-picker-wrap::after {
        content: '';
        position: absolute;
        right: 12px;
        width: 18px;
        height: 18px;
        pointer-events: none;
        opacity: 0.55;
        background: center / contain no-repeat url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none'%3E%3Crect x='3' y='5' width='18' height='16' rx='2' stroke='%235c7212' stroke-width='1.8'/%3E%3Cpath d='M3 9h18M8 3v4M16 3v4' stroke='%235c7212' stroke-width='1.8' stroke-linecap='round'/%3E%3C/svg%3E");
    }
    .app-date-picker,
    .flatpickr-input.app-date-picker,
    input.flatpickr-input[data-input] {
        min-width: 148px;
        padding: 10px 38px 10px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
        font-family: inherit;
        font-size: 0.92rem;
        font-weight: 600;
        color: #1e293b;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        cursor: pointer;
    }
    .app-date-picker:hover,
    input.flatpickr-input[data-input]:hover {
        border-color: #94a3b8;
    }
    .app-date-picker:focus,
    .flatpickr-input.app-date-picker:focus,
    input.flatpickr-input[data-input]:focus {
        outline: none;
        border-color: #b2cd16;
        box-shadow: 0 0 0 3px rgba(178, 205, 22, 0.28);
    }
    .flatpickr-calendar {
        width: 307.875px !important;
        min-width: 307.875px !important;
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 14px 36px rgba(15, 23, 42, 0.16) !important;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        padding: 4px 0 8px;
    }
    .flatpickr-innerContainer,
    .flatpickr-rContainer,
    .flatpickr-days,
    .dayContainer {
        width: 100% !important;
    }
    .dayContainer {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
        box-sizing: border-box;
    }
    .flatpickr-months {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .flatpickr-months .flatpickr-month {
        height: 44px;
    }
    .flatpickr-current-month {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months,
    .flatpickr-current-month input.cur-year {
        font-weight: 700;
        color: #1e293b;
    }
    span.flatpickr-weekday {
        flex: 1 0 14.2857143%;
        max-width: 14.2857143%;
        box-sizing: border-box;
        color: #64748b;
        font-weight: 700;
        font-size: 0.78rem;
    }
    .flatpickr-day {
        flex: 1 0 14.2857143%;
        max-width: 14.2857143%;
        box-sizing: border-box;
        border-radius: 8px;
        font-weight: 600;
        color: #334155;
        height: 39px;
        line-height: 39px;
    }
    .flatpickr-day:hover,
    .flatpickr-day:focus {
        background: rgba(178, 205, 22, 0.2);
        border-color: rgba(178, 205, 22, 0.2);
        color: #0a1347;
    }
    .flatpickr-day.today {
        border-color: #b2cd16;
        color: #0a1347;
    }
    .flatpickr-day.today:hover {
        background: rgba(178, 205, 22, 0.25);
        color: #0a1347;
    }
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange,
    .flatpickr-day.selected.inRange,
    .flatpickr-day.startRange.inRange,
    .flatpickr-day.endRange.inRange {
        background: #b2cd16 !important;
        border-color: #b2cd16 !important;
        color: #0a1347 !important;
        box-shadow: 0 2px 8px rgba(178, 205, 22, 0.35);
    }
    .flatpickr-day.inRange {
        background: rgba(178, 205, 22, 0.14);
        border-color: transparent;
        box-shadow: none;
    }
    .flatpickr-day.flatpickr-disabled,
    .flatpickr-day.flatpickr-disabled:hover {
        color: #cbd5e1 !important;
        background: transparent !important;
        border-color: transparent !important;
        cursor: not-allowed;
    }
    .flatpickr-months .flatpickr-prev-month,
    .flatpickr-months .flatpickr-next-month {
        fill: #5c7212;
        padding: 8px;
    }
    .flatpickr-months .flatpickr-prev-month:hover svg,
    .flatpickr-months .flatpickr-next-month:hover svg {
        fill: #0a1347;
    }
</style>
<script>
    (function () {
        if (window.__beautifulDatePickerInit) return;
        window.__beautifulDatePickerInit = true;

        const defaultOptions = {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'M j, Y',
            allowInput: false,
            disableMobile: true,
            monthSelectorType: 'static',
        };

        function wrapInput(input) {
            if (input.closest('.app-date-picker-wrap')) return;
            const wrap = document.createElement('span');
            wrap.className = 'app-date-picker-wrap';
            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(input);
        }

        window.initBeautifulDatePickers = function initBeautifulDatePickers(root, options) {
            if (typeof flatpickr === 'undefined') return;

            const merged = Object.assign({}, defaultOptions, options || {});
            const scope = root || document;

            scope.querySelectorAll('input.app-date-picker:not([data-fp-enhanced]):not([data-fp-custom])').forEach((input) => {
                input.dataset.fpEnhanced = '1';
                wrapInput(input);
                flatpickr(input, merged);
            });

            scope.querySelectorAll('input[type="date"]:not([data-fp-enhanced]):not([data-fp-custom])').forEach((input) => {
                input.dataset.fpEnhanced = '1';
                const value = input.value;
                input.type = 'text';
                input.classList.add('app-date-picker');
                if (value) input.value = value;
                if (!input.placeholder) {
                    input.placeholder = input.getAttribute('aria-label') || 'Select date';
                }
                wrapInput(input);
                flatpickr(input, merged);
            });
        };

        const boot = () => {
            if (typeof flatpickr === 'undefined') {
                window.setTimeout(boot, 40);
                return;
            }
            window.initBeautifulDatePickers();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }
    })();
</script>
@endonce
