@once
<style>
    .app-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 4px 32px;
        color: #1e293b;
    }
    .app-page-header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }
    .app-page h1 {
        font-weight: 700;
        font-size: 40px;
        color: #2c3e50;
        margin: 0;
        letter-spacing: 0.04em;
    }
    .app-page-lead {
        margin: 0 0 20px;
        color: #64748b;
        font-size: 0.92rem;
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
        border: none;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        padding: 22px 24px;
        margin-bottom: 18px;
    }
    .app-page-section-title {
        font-size: 1rem;
        font-weight: 800;
        color: #334155;
        margin: 0 0 14px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .app-page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 9px 18px;
        font-weight: 700;
        font-size: 0.88rem;
        border-radius: 8px;
        cursor: pointer;
        border: 1px solid #91bf14;
        text-decoration: none;
        background: #b2cd16;
        color: #0a1347;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .app-page-btn:hover,
    .app-page-btn--primary:hover,
    .app-page-btn--secondary:hover {
        background: #9eb812;
        color: #0a1347;
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
        gap: 8px;
        margin-bottom: 12px;
    }
    .app-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 11px;
        border-radius: 8px;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        background: #ecfdf5;
        color: #047857;
        border: none;
    }
    .app-badge--muted {
        background: #e2e8f0;
        color: #475569;
    }
    .app-info-box {
        background: #eef2f6;
        border: none;
        border-radius: 12px;
        padding: 16px 18px;
        margin-top: 8px;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.06);
    }
    .app-info-box ul {
        margin: 8px 0 0;
        padding-left: 20px;
        color: #475569;
        line-height: 1.6;
    }
    .app-info-box p {
        margin: 0;
        color: #475569;
        line-height: 1.6;
    }
</style>
@endonce
