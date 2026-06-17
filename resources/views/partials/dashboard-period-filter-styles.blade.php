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
        border: 1px solid #91bf14;
        border-radius: 8px;
        background: #b2cd16;
        color: #0a1347;
        font-family: inherit;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .dashboard-period-filter__btn:hover {
        background: #9eb812;
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
</style>
@endonce
