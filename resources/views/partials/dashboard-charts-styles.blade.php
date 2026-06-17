@once
<style>
    /* Shared dashboard chart layout — keeps legends visible on mobile */
    .chart-card {
        overflow: visible;
    }
    .chart-body {
        position: relative;
        width: 100%;
        overflow: visible;
    }
    .chart-body--ring {
        height: 200px;
        min-height: 180px;
        flex-shrink: 0;
    }
    .chart-body--ring canvas {
        width: 100% !important;
        height: 100% !important;
        max-height: 200px;
    }
    .chart-body--plot {
        height: 220px;
        min-height: 200px;
    }
    .chart-body--plot canvas {
        width: 100% !important;
        height: 100% !important;
    }
    .chart-html-legend {
        list-style: none;
        margin: 10px 0 0;
        padding: 0;
        display: flex;
        flex-wrap: wrap;
        gap: 8px 12px;
        justify-content: center;
        width: 100%;
    }
    .chart-html-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        font-weight: 800;
        color: #2c3e50;
        line-height: 1.25;
        max-width: 100%;
    }
    .chart-html-legend-swatch {
        width: 12px;
        height: 12px;
        border-radius: 3px;
        flex-shrink: 0;
        border: 1px solid rgba(0, 0, 0, 0.08);
    }
    .chart-html-legend-label {
        word-break: break-word;
    }
    @media (max-width: 768px) {
        .chart-card {
            min-height: auto !important;
            padding-bottom: 14px;
        }
        .chart-body--ring {
            height: 190px;
            min-height: 170px;
        }
        .chart-body--ring canvas {
            max-height: 190px;
        }
        .chart-body--plot {
            height: auto;
            min-height: 220px;
        }
        .chart-html-legend {
            justify-content: flex-start;
        }
        .chart-html-legend-item {
            flex: 1 1 calc(50% - 12px);
            min-width: 0;
        }
        /* Never clip chart canvases on small screens */
        .chart-card canvas,
        .charts-container canvas,
        .dash-charts-grid canvas {
            max-height: none !important;
        }
    }
</style>
@endonce
