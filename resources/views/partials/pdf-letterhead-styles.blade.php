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
        margin-top: 228px;
        margin-right: 36px;
        margin-bottom: 108px;
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
        top: -228px;
        left: -36px;
        width: 18px;
        height: 210px;
        background: #d6e05a;
        border-bottom-right-radius: 24px;
    }
    .pdf-header {
        position: fixed;
        top: -200px;
        left: -12px;
        right: -36px;
        height: 180px;
    }
    .pdf-header-table {
        width: 100%;
        border-collapse: collapse;
    }
    .pdf-emblem-cell {
        width: 156px;
        white-space: nowrap;
        padding: 0;
        border: 0;
        vertical-align: middle;
    }
    .pdf-emblem {
        height: 148px;
        width: auto;
        display: block;
    }
    .pdf-gap-cell {
        width: 22px;
        padding: 0;
        border: 0;
        font-size: 1px;
        line-height: 1px;
        vertical-align: middle;
    }
    .pdf-lockup-cell {
        padding: 0;
        border: 0;
        vertical-align: middle;
    }
    .pdf-lockup-table {
        width: 100%;
        border-collapse: collapse;
    }
    .pdf-wordmark {
        width: 1%;
        white-space: nowrap;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, DejaVu Sans, sans-serif;
        font-size: 42px;
        font-weight: 700;
        color: #1b1e40;
        letter-spacing: 0.06em;
        line-height: 1;
        padding: 0;
        vertical-align: middle;
    }
    .pdf-name-gap {
        width: 18px;
        padding: 0;
        border: 0;
        font-size: 1px;
        line-height: 1px;
        vertical-align: middle;
    }
    .pdf-rule-cell {
        width: 99%;
        vertical-align: middle;
        padding: 0;
        border: 0;
    }
    .pdf-rule {
        width: 100%;
        border-top: 8px solid #1b1e40;
        height: 0;
        font-size: 1px;
        line-height: 0;
    }
    .pdf-footer {
        position: fixed;
        left: 56px;
        right: 56px;
        bottom: -108px;
        height: 100px;
        overflow: hidden;
        background: #808388;
        border-radius: 48px 48px 0 0;
    }
    .pdf-footer-shape {
        display: none;
    }
    .pdf-footer-text {
        position: relative;
        z-index: 2;
        color: #ffffff;
        text-align: center;
        padding: 18px 24px 12px;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, DejaVu Sans, sans-serif;
        font-size: 11px;
        line-height: 1.35;
    }
    .pdf-footer-contacts {
        width: auto;
        margin: 0 auto 10px;
        border-collapse: collapse;
    }
    .pdf-footer-contacts td {
        padding: 0 10px;
        white-space: nowrap;
        color: #ffffff;
        font-size: 11px;
        vertical-align: middle;
    }
    .pdf-footer-row {
        margin: 0;
        color: #ffffff;
        text-align: center;
    }
    .pdf-footer-icon {
        color: #e3c44d;
        font-size: 11px;
        padding-right: 4px;
    }
    .pdf-footer-sep {
        color: #ffffff;
        padding: 0 6px;
    }
    .pdf-content {
        padding: 0;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, DejaVu Sans, sans-serif;
    }
</style>
