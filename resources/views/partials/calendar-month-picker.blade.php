@php
    $month = (int) $month;
    $year = (int) $year;
    $yearMin = (int) ($yearMin ?? now()->year - 5);
    $yearMax = (int) ($yearMax ?? now()->year + 2);
    $pickerId = 'cal-picker-' . preg_replace('/[^a-z0-9_-]/i', '-', $routeName);
    $monthName = \Carbon\Carbon::create()->month($month)->format('F');
    $todayMonth = (int) now()->month;
    $todayYear = (int) now()->year;
@endphp

@once
<style>
    .calendar-nav-bar {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 14px;
        margin: 14px auto 18px;
        flex-wrap: wrap;
    }
    .cal-nav-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: 2px solid #bfe6d1;
        background: #fff;
        color: #138056;
        text-decoration: none;
        font-size: 22px;
        line-height: 1;
        box-shadow: 0 6px 18px rgba(19, 128, 86, 0.18);
        transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        user-select: none;
    }
    .cal-nav-btn:hover {
        background: #e8f7ef;
        box-shadow: 0 4px 14px rgba(19, 128, 86, 0.16);
        transform: translateY(-1px);
        color: #0f6644;
    }
    .cal-nav-picker {
        position: relative;
    }
    .cal-nav-current {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 220px;
        padding: 10px 18px;
        border: 2px solid #bfe6d1;
        border-radius: 999px;
        background: linear-gradient(135deg, #ffffff 0%, #eaf8f1 100%);
        box-shadow: 0 10px 26px rgba(19, 128, 86, 0.20);
        cursor: pointer;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .cal-nav-current:hover,
    .cal-nav-current[aria-expanded="true"] {
        border-color: #138056;
        box-shadow: 0 12px 34px rgba(19, 128, 86, 0.26);
    }
    .cal-nav-current-text {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        line-height: 1.15;
        text-align: center;
        flex: 1;
    }
    .cal-nav-current-month {
        font-size: 1.24rem;
        font-weight: 700;
        color: #2c3e50;
        letter-spacing: 0.02em;
    }
    .cal-nav-current-year {
        font-size: 0.9rem;
        font-weight: 800;
        color: #0f6644;
    }
    .cal-nav-chevron {
        margin-left: auto;
        width: 18px;
        height: 18px;
        color: #138056;
        transition: transform 0.2s ease;
    }
    .cal-nav-current[aria-expanded="true"] .cal-nav-chevron {
        transform: rotate(180deg);
    }
    .cal-nav-panel {
        position: absolute;
        top: calc(100% + 10px);
        left: 50%;
        transform: translateX(-50%);
        z-index: 30;
        width: min(320px, 92vw);
        padding: 16px;
        border-radius: 18px;
        border: 2px solid #bfe6d1;
        background: #fff;
        box-shadow: 0 20px 55px rgba(44, 62, 80, 0.22);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
    }
    .cal-nav-panel.is-open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: translateX(-50%) translateY(0);
    }
    .cal-nav-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-bottom: 12px;
        border-bottom: 1px solid #eef3f0;
    }
    .cal-nav-year-btn {
        width: 34px;
        height: 34px;
        border: none;
        border-radius: 10px;
        background: #f3fbf6;
        color: #138056;
        font-size: 1.25rem;
        line-height: 1;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .cal-nav-year-btn:hover:not(:disabled) {
        background: #e1f5ed;
    }
    .cal-nav-year-btn:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }
    .cal-nav-panel-year {
        font-size: 1.05rem;
        font-weight: 700;
        color: #2c3e50;
        letter-spacing: 0.04em;
    }
    .cal-nav-month-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }
    .cal-nav-month {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px 6px;
        border-radius: 12px;
        border: 1px solid #e3eee8;
        background: #ffffff;
        color: #1f2937;
        font-size: 0.92rem;
        font-weight: 700;
        text-decoration: none;
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease, transform 0.12s ease;
    }
    .cal-nav-month:hover {
        background: #e8f7ef;
        border-color: #138056;
        color: #0f6644;
        transform: translateY(-1px);
    }
    .cal-nav-month.is-active {
        background: linear-gradient(135deg, #138056 0%, #1a9d6a 100%);
        border-color: #138056;
        color: #fff;
        box-shadow: 0 8px 20px rgba(19, 128, 86, 0.38);
    }
    .cal-nav-today {
        display: block;
        width: 100%;
        margin-top: 12px;
        padding: 9px 12px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #138056 0%, #1a9d6a 100%);
        color: #fff;
        font-size: 0.88rem;
        font-weight: 700;
        text-align: center;
        text-decoration: none;
        transition: filter 0.15s ease, transform 0.12s ease;
    }
    .cal-nav-today:hover {
        filter: brightness(1.05);
        transform: translateY(-1px);
        color: #fff;
    }
    @media (max-width: 480px) {
        .cal-nav-current {
            min-width: 0;
            padding: 8px 10px;
        }
        .cal-nav-btn {
            width: 34px;
            height: 34px;
        }
    }
</style>
@endonce

<div class="calendar-nav-bar" id="{{ $pickerId }}" data-year-min="{{ $yearMin }}" data-year-max="{{ $yearMax }}">
    <a href="{{ route($routeName, ['month' => $month === 1 ? 12 : $month - 1, 'year' => $month === 1 ? $year - 1 : $year]) }}"
       class="cal-nav-btn"
       aria-label="Previous month">&lsaquo;</a>

    <div class="cal-nav-picker">
        <button type="button"
                class="cal-nav-current"
                aria-expanded="false"
                aria-controls="{{ $pickerId }}-panel"
                aria-label="Choose month and year">
            <span class="cal-nav-current-text">
                <span class="cal-nav-current-month">{{ $monthName }}</span>
                <span class="cal-nav-current-year">{{ $year }}</span>
            </span>
            <svg class="cal-nav-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        <div class="cal-nav-panel" id="{{ $pickerId }}-panel" role="dialog" aria-label="Month picker">
            <div class="cal-nav-panel-head">
                <button type="button" class="cal-nav-year-btn" data-year-step="-1" aria-label="Previous year">&lsaquo;</button>
                <span class="cal-nav-panel-year">{{ $year }}</span>
                <button type="button" class="cal-nav-year-btn" data-year-step="1" aria-label="Next year">&rsaquo;</button>
            </div>
            <div class="cal-nav-month-grid">
                @foreach (range(1, 12) as $m)
                    @php $short = \Carbon\Carbon::create()->month($m)->format('M'); @endphp
                    <a href="{{ route($routeName, ['month' => $m, 'year' => $year]) }}"
                       class="cal-nav-month {{ $month === $m ? 'is-active' : '' }}"
                       data-month="{{ $m }}">{{ $short }}</a>
                @endforeach
            </div>
            <a href="{{ route($routeName, ['month' => $todayMonth, 'year' => $todayYear]) }}" class="cal-nav-today">Jump to today</a>
        </div>
    </div>

    <a href="{{ route($routeName, ['month' => $month === 12 ? 1 : $month + 1, 'year' => $month === 12 ? $year + 1 : $year]) }}"
       class="cal-nav-btn"
       aria-label="Next month">&rsaquo;</a>
</div>

@once
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[id^="cal-picker-"]').forEach((root) => {
        const trigger = root.querySelector('.cal-nav-current');
        const panel = root.querySelector('.cal-nav-panel');
        const yearLabel = root.querySelector('.cal-nav-panel-year');
        const yearDown = root.querySelector('[data-year-step="-1"]');
        const yearUp = root.querySelector('[data-year-step="1"]');
        const monthLinks = root.querySelectorAll('.cal-nav-month');
        const yearMin = parseInt(root.dataset.yearMin, 10);
        const yearMax = parseInt(root.dataset.yearMax, 10);
        let panelYear = parseInt(yearLabel.textContent, 10);
        const pageYear = panelYear;
        const pageMonth = parseInt(root.querySelector('.cal-nav-month.is-active')?.dataset.month || '1', 10);

        const closePanel = () => {
            panel.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        };

        const syncMonthLinks = () => {
            yearLabel.textContent = panelYear;
            yearDown.disabled = panelYear <= yearMin;
            yearUp.disabled = panelYear >= yearMax;

            monthLinks.forEach((link) => {
                const month = link.dataset.month;
                const url = new URL(link.href, window.location.href);
                url.searchParams.set('month', month);
                url.searchParams.set('year', panelYear);
                link.href = url.pathname + url.search;
                link.classList.toggle('is-active', panelYear === pageYear && parseInt(month, 10) === pageMonth);
            });
        };

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = panel.classList.toggle('is-open');
            trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (isOpen) {
                panelYear = pageYear;
                syncMonthLinks();
            }
        });

        yearDown.addEventListener('click', () => {
            if (panelYear > yearMin) {
                panelYear -= 1;
                syncMonthLinks();
            }
        });

        yearUp.addEventListener('click', () => {
            if (panelYear < yearMax) {
                panelYear += 1;
                syncMonthLinks();
            }
        });

        document.addEventListener('click', (event) => {
            if (!root.contains(event.target)) {
                closePanel();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closePanel();
            }
        });
    });
});
</script>
@endonce
