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
        margin-top: 186px;
        margin-right: 36px;
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
        top: -186px;
        left: -36px;
        width: 18px;
        height: 168px;
        background: #d6e05a;
        border-bottom-right-radius: 22px;
    }
    .pdf-header {
        position: fixed;
        top: -160px;
        left: -16px;
        right: -36px;
        height: 148px;
    }
    .pdf-header-table {
        width: 100%;
        border-collapse: collapse;
    }
    .pdf-emblem-cell {
        width: 118px;
        white-space: nowrap;
        padding: 0;
        border: 0;
        vertical-align: middle;
    }
    .pdf-emblem {
        height: 118px;
        width: auto;
        display: block;
    }
    .pdf-gap-cell {
        width: 28px;
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
        font-size: 36px;
        font-weight: 700;
        color: #1b1e40;
        letter-spacing: 0.04em;
        line-height: 1;
        padding: 0;
        vertical-align: top;
    }
    .pdf-name-gap {
        width: 20px;
        padding: 0;
        border: 0;
        font-size: 1px;
        line-height: 1px;
        vertical-align: top;
    }
    .pdf-rule-cell {
        width: 99%;
        vertical-align: top;
        padding: 2px 0 0;
        border: 0;
    }
    .pdf-rule {
        width: 100%;
        border-top: 7px solid #1b1e40;
        height: 0;
        font-size: 1px;
        line-height: 0;
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
