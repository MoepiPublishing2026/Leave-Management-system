@once
<style>
    .dashboard-period-filter {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 14px;
    }
    .dashboard-period-filter__form {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px 8px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.05);
        flex-wrap: wrap;
    }
    .dashboard-period-filter__label {
        font-size: 0.78rem;
        font-weight: 500;
        color: #64748b;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin: 0;
        white-space: nowrap;
    }
    .dashboard-period-filter__select {
        appearance: none;
        -webkit-appearance: none;
        padding: 8px 36px 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background-color: #fff;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8' fill='none'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%2364748b' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 12px 8px;
        font-family: inherit;
        font-size: 0.9rem;
        font-weight: 500;
        color: #334155;
        cursor: pointer;
        min-width: 148px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .dashboard-period-filter__select--employee {
        min-width: 190px;
        max-width: 240px;
    }
    .dashboard-period-filter__select:hover {
        border-color: #94a3b8;
    }
    .dashboard-period-filter__select:focus {
        outline: none;
        border-color: #138056;
        box-shadow: 0 0 0 3px rgba(19, 128, 86, 0.12);
    }
    .dashboard-period-filter__btn {
        padding: 8px 14px;
        border: 1px solid #c3d010;
        border-radius: 8px;
        background: #d4e21a;
        color: #0a1347;
        font-family: inherit;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .dashboard-period-filter__btn:hover {
        background: #c3d010;
        color: #0a1347;
    }
    @media (max-width: 600px) {
        .dashboard-period-filter {
            justify-content: stretch;
        }
        .dashboard-period-filter__form {
            width: 100%;
            justify-content: space-between;
        }
        .dashboard-period-filter__select {
            flex: 1;
            min-width: 0;
        }
    }
        /* ===== Prototype match: dashboard Employee / Period dropdowns ===== */
    .dashboard-period-filter__form {
        background: #d9e5e5;
        border: 0;
        border-radius: 20px;
        box-shadow: none;
        padding: 10px 14px;
        flex-direction: row;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px 14px;
    }
    /* Default / manager: horizontal rows (Employee + Period side by side) */
    .dashboard-period-filter__row {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        min-width: 0;
    }
    .dashboard-period-filter__label,
    .dashboard-period-filter__label-1 {
        color: #475569;
        font-size: 0.72rem;
        font-weight: 400;
        letter-spacing: 0.04em;
        text-transform: none;
        margin: 0;
        white-space: nowrap;
        flex: 0 0 auto;
    }
    .dashboard-period-filter .filter-select-wrap {
        min-width: 170px;
        flex: 0 0 auto;
        position: relative;
    }
    .dashboard-period-filter .filter-select__trigger {
        border: 0;
        border-radius: 999px;
        background: #fff;
        padding: 9px 16px;
        font-weight: 700;
        color: #1e293b;
        box-shadow: none;
    }
    .dashboard-period-filter .filter-select__trigger:hover {
        background: #fff;
        border: 0;
    }
    .dashboard-period-filter .filter-select-wrap.is-open .filter-select__trigger {
        border: 0;
        box-shadow: none;
    }
    .dashboard-period-filter .filter-select__menu {
        top: calc(100% + 4px);
        padding: 8px;
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.18);
        max-height: 200px;
        scrollbar-width: thin;
        scrollbar-color: #9fbcbc #e6eeee;
    }
    .dashboard-period-filter .filter-select__menu::-webkit-scrollbar {
        width: 8px;
    }
    .dashboard-period-filter .filter-select__menu::-webkit-scrollbar-track {
        background: #e6eeee;
        border-radius: 8px;
    }
    .dashboard-period-filter .filter-select__menu::-webkit-scrollbar-thumb {
        background: #9fbcbc;
        border-radius: 8px;
    }
    .dashboard-period-filter .filter-select__option {
        text-align: center;
        font-weight: 700;
        color: #1e293b;
        padding: 10px 12px;
        border-radius: 10px;
    }
    .dashboard-period-filter .filter-select__option:hover,
    .dashboard-period-filter .filter-select__option.is-focused {
        background: rgba(212, 225, 24, 0.25);
        color: #1e293b;
    }
    .dashboard-period-filter .filter-select__option.is-selected {
        background: #d4e118;
        color: #1e293b;
    }
    .dashboard-period-filter .filter-select__option.is-selected:hover {
        background: #c8d614;
    }
        .manager-container .dashboard-period-filter .filter-select__option.is-selected {
        background: #d4e118 !important;
        color: #1e293b !important;
    }
    .manager-container .dashboard-period-filter .filter-select__option:hover {
        background: rgba(212, 225, 24, 0.25) !important;
    }
</style>
@endonce
