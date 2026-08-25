<style>
    @font-face {
        font-family: "Segoe UI";
        font-weight: 400;
        src: url("file:///C:/Windows/Fonts/segoeui.ttf") format("truetype");
    }
    @font-face {
        font-family: "Segoe UI";
        font-weight: 700;
        src: url("file:///C:/Windows/Fonts/segoeuib.ttf") format("truetype");
    }
    /* Reserve header/footer space on EVERY page so content cannot overlap. */
    @page {
        margin-top: 108px;
        margin-right: 32px;
        margin-bottom: 92px;
        margin-left: 36px;
    }
    body {
        margin: 0;
        padding: 0;
        color: #111827;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, DejaVu Sans, sans-serif;
    }
    .pdf-accent {
        position: fixed;
        top: -108px;
        left: -36px;
        width: 12px;
        height: 96px;
        background: #d6e05a;
        border-bottom-right-radius: 8px;
    }
    .pdf-header {
        position: fixed;
        top: -100px;
        left: -20px;
        right: -16px;
        height: 92px;
    }
    .pdf-header-table {
        width: 100%;
        border-collapse: collapse;
    }
    .pdf-header-table td {
        vertical-align: middle;
        padding: 0;
        border: 0;
    }
    .pdf-logo {
        height: 78px;
        width: auto;
        max-width: 340px;
    }
    .pdf-wordmark {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, DejaVu Sans, sans-serif;
        font-size: 28px;
        font-weight: 700;
        color: #1b1e40;
        letter-spacing: 1px;
        line-height: 1;
    }
    .pdf-rule {
        border-top: 6px solid #2f2f2f;
        margin-left: 14px;
        margin-top: 2px;
    }
    .pdf-footer {
        position: fixed;
        left: -36px;
        right: -32px;
        bottom: -92px;
        height: 92px;
        overflow: hidden;
    }
    .pdf-footer-shape {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 92px;
    }
    .pdf-footer-text {
        position: relative;
        z-index: 2;
        color: #ffffff;
        text-align: center;
        padding: 28px 18px 8px;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, DejaVu Sans, sans-serif;
        font-size: 9px;
        line-height: 1.5;
    }
    .pdf-footer-row {
        margin: 0 0 3px;
        color: #ffffff;
    }
    .pdf-footer-icon {
        color: #d6e05a;
        font-size: 10px;
        padding-right: 4px;
    }
    .pdf-footer-sep {
        color: #d6e05a;
        padding: 0 9px;
    }
    .pdf-content {
        padding: 0;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, DejaVu Sans, sans-serif;
    }
</style>
