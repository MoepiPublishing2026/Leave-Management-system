@once
<style>
    .app-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        max-width: none;
        width: 100%;
        margin: 0;
        padding: 0 0 36px;
        color: #273345;
        box-sizing: border-box;
    }
    .app-page-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 0;
    }
    .app-page h1 {
        font-size: 34px;
        font-weight: 700;
        color: #273345;
        letter-spacing: 0.02em;
        margin: 0;
    }
    .app-page-lead {
        margin: 8px 0 22px;
        color: #666;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    .app-page-flash {
        margin-bottom: 16px;
        padding: 12px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.92rem;
    }
    .app-page-flash--success {
        background: #ecfdf5;
        border: 1px solid #86efac;
        color: #065f46;
    }
    .app-page-flash--error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .app-page-panel {
        background: #fff;
        border: 1px solid #d4d4d4;
        border-radius: 21px;
        box-shadow: 12px 17px 24px rgba(0, 0, 0, 0.22);
        padding: 18px 23px;
        margin-bottom: 24px;
    }
    .app-page-panel-1 {
        background: #fff;
        border: 4px solid #d4e510;
        border-radius: 7px;
        box-shadow: 12px 17px 24px rgba(0, 0, 0, 0.28);
        padding: 18px 28px 24px;
        margin-bottom: 24px;
        box-sizing: border-box;
    }
    .leave-info-grid {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 42px;
        align-items: start;
        width: 100%;
    }
    .app-page-section-title {
        font-size: 1rem;
        font-weight: 800;
        color: #334155;
        margin: 0 0 14px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    html body main .app-page-btn,
    html body main form .app-page-btn,
    html body main form button.app-page-btn[type="submit"] {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px !important;
        min-height: 0 !important;
        min-width: 0 !important;
        height: auto !important;
        font-size: 13px !important;
        line-height: 1.2 !important;
        font-weight: 700;
        border-radius: 8px !important;
        cursor: pointer;
        border: none;
        text-decoration: none;
        background: #d4e510;
        color: #080808;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .app-page-btn:hover,
    .app-page-btn--primary:hover,
    .app-page-btn--secondary:hover {
        background: #c3d500;
        color: #080808;
    }
    .app-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    @media (max-width: 720px) {
        .app-form-grid { grid-template-columns: 1fr; }
        .app-page h1 { font-size: 28px; }
    }
    .app-form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .app-form-group label {
        font-weight: 700;
        font-size: 0.84rem;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .app-form-group input[type="text"],
    .app-form-group input[type="email"],
    .app-form-group input[type="password"],
    .app-form-group input[type="number"],
    .app-form-group input[type="file"],
    .app-form-group select,
    .app-form-group textarea {
        width: 100%;
        padding: 10px 14px;
        border: none;
        border-radius: 10px;
        font-size: 0.92rem;
        color: #334155;
        background: #e2e8f0;
        box-sizing: border-box;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.08);
    }
    .app-form-group input:focus,
    .app-form-group select:focus,
    .app-form-group textarea:focus {
        outline: none;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(19, 128, 86, 0.14);
    }
    .app-form-group input[readonly] {
        background: #eef2f6;
        color: #64748b;
        cursor: not-allowed;
    }
    .app-form-hint {
        font-size: 0.82rem;
        color: #64748b;
        margin-top: 4px;
    }
    .app-form-error {
        font-size: 0.82rem;
        color: #b91c1c;
        margin-top: 4px;
    }
    .app-password-wrap {
        position: relative;
    }
    .app-password-wrap input {
        padding-right: 44px;
    }
    .app-password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        border: none;
        background: transparent;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .app-password-toggle svg {
        width: 20px;
        height: 20px;
        stroke: #64748b;
    }
    .app-badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 19px;
        margin-bottom: 27px;
    }
    .app-badge {
        padding: 5px 12px;
        min-height: 0;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0;
        background: #d4e510;
        color: #273345;
        border: none;
    }
    .app-badge--muted {
        background: #cecece;
        color: #273345;
    }
    .app-info-box {
        background: #fff;
        border: none;
        border-radius: 0;
        padding: 0 10px;
        margin-top: 0;
        box-shadow: none;
    }
    .app-info-box ul {
        margin: 0;
        padding-left: 20px;
        color: #080808;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    .app-info-box p {
        margin: 0;
        color: #080808;
        font-size: 0.95rem;
        line-height: 1.5;
    }
</style>
@endonce
