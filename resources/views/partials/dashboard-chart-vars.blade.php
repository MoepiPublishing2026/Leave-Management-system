@once
<script>
    window.leaveStatusColors = @json(config('leave_status_colors.colors'));
    window.leaveStatusPieLabels = @json(config('leave_status_colors.pie_labels'));
    window.leaveMonthlyStatusLabels = @json(config('leave_status_colors.monthly_status_labels'));
    window.leaveMonthlyApplicationsColor = @json(config('leave_status_colors.monthly_applications_line'));
    window.groupLabelPalette = @json(config('designation_colors.palette'));

    window.colorForStatus = (label) => {
        const key = String(label || '').trim().toLowerCase();
        return window.leaveStatusColors[key] || '#64748b';
    };

    window.statusColorsForLabels = (labels) => labels.map((label) => window.colorForStatus(label));

    window.hashStringForColor = (str) => {
        let h = 0;
        const s = String(str || '');
        for (let i = 0; i < s.length; i += 1) {
            h = ((h << 5) - h) + s.charCodeAt(i);
            h |= 0;
        }
        return Math.abs(h);
    };

    window.colorForGroupLabel = (label) => {
        const key = String(label || '').trim().toLowerCase() || 'unknown';
        const palette = window.groupLabelPalette || [];
        if (!palette.length) {
            return '#64748b';
        }
        return palette[window.hashStringForColor(key) % palette.length];
    };

    window.renderChartHtmlLegend = (listElementId, labels, colors, percentages = {}) => {
        const list = document.getElementById(listElementId);
        if (!list) return;

        list.innerHTML = labels.map((label, index) => {
            const color = colors[index] || '#64748b';
            const pct = percentages[label];
            const pctText = pct !== undefined && pct !== null ? ` (${Number(pct).toFixed(1)}%)` : '';
            return `<li class="chart-html-legend-item">
                <span class="chart-html-legend-swatch" style="background:${color}"></span>
                <span class="chart-html-legend-label">${label}${pctText}</span>
            </li>`;
        }).join('');
    };

    window.chartTextColor = '#0f172a';

    window.chartBoldFontSpec = {
        family: '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif',
        size: 12,
        weight: 'bold',
        style: 'normal',
    };

    window.chartTooltipBold = {
        bodyFont: { size: 13, weight: 'bold' },
        titleFont: { size: 13, weight: 'bold' },
        footerFont: { size: 12, weight: 'bold' },
    };

    window.chartLegendBold = {
        labels: {
            font: { ...window.chartBoldFontSpec },
            color: window.chartTextColor,
        },
    };

    window.chartAxisTicksBold = {
        color: window.chartTextColor,
        font: { ...window.chartBoldFontSpec },
    };

    window.isNarrowChartViewport = () => (
        typeof window.matchMedia === 'function'
        && window.matchMedia('(max-width: 768px)').matches
    );

    window.getMonthAxisTicks = () => {
        const narrow = window.isNarrowChartViewport();

        return {
            autoSkip: false,
            maxTicksLimit: 12,
            maxRotation: narrow ? 90 : 0,
            minRotation: narrow ? 90 : 0,
            color: window.chartTextColor,
            font: {
                ...window.chartBoldFontSpec,
                size: narrow ? 9 : 12,
            },
            padding: narrow ? 2 : 4,
        };
    };

    window.getMonthChartLayout = () => {
        const narrow = window.isNarrowChartViewport();

        return narrow
            ? { padding: { bottom: 10, left: 2, right: 6 } }
            : {};
    };

    window.monthAxisTicksBold = window.getMonthAxisTicks();

    window.refreshMonthAxisCharts = () => {
        const ticks = window.getMonthAxisTicks();
        const layout = window.getMonthChartLayout();
        window.monthAxisTicksBold = ticks;
        (window.monthAxisCharts || []).forEach((chart) => {
            if (!chart?.options?.scales?.x) return;
            chart.options.scales.x.ticks = { ...ticks };
            chart.options.layout = layout;
            chart.update('none');
        });
    };

    window.registerMonthAxisChart = (chart) => {
        if (!chart) return;
        window.monthAxisCharts = window.monthAxisCharts || [];
        if (!window.monthAxisCharts.includes(chart)) {
            window.monthAxisCharts.push(chart);
        }
        if (!window.monthAxisResizeBound) {
            window.monthAxisResizeBound = true;
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(window.refreshMonthAxisCharts, 150);
            });
        }
    };

    window.polarAreaChartScales = {
        r: {
            beginAtZero: true,
            ticks: {
                display: false,
                backdropColor: 'transparent',
            },
            grid: {
                color: 'rgba(148, 163, 184, 0.35)',
                circular: true,
            },
            angleLines: {
                color: 'rgba(148, 163, 184, 0.35)',
            },
            pointLabels: {
                display: false,
                color: window.chartTextColor,
                font: { ...window.chartBoldFontSpec },
            },
        },
    };

    window.deepMergeChartOptions = (target, source) => {
        const output = { ...(target || {}) };
        if (!source || typeof source !== 'object' || Array.isArray(source)) {
            return output;
        }

        Object.keys(source).forEach((key) => {
            const sourceValue = source[key];
            const targetValue = output[key];

            if (
                sourceValue
                && typeof sourceValue === 'object'
                && !Array.isArray(sourceValue)
                && targetValue
                && typeof targetValue === 'object'
                && !Array.isArray(targetValue)
            ) {
                output[key] = window.deepMergeChartOptions(targetValue, sourceValue);
            } else {
                output[key] = sourceValue;
            }
        });

        return output;
    };

    window.enforceBoldChartFonts = (options) => {
        const opts = options || {};
        const boldFont = { ...window.chartBoldFontSpec };

        opts.color = window.chartTextColor;
        opts.font = { ...(opts.font || {}), ...boldFont };

        opts.plugins = opts.plugins || {};
        opts.plugins.legend = opts.plugins.legend || {};
        opts.plugins.legend.labels = opts.plugins.legend.labels || {};
        opts.plugins.legend.labels.color = window.chartTextColor;
        opts.plugins.legend.labels.font = {
            ...(opts.plugins.legend.labels.font || {}),
            ...boldFont,
        };

        opts.plugins.tooltip = opts.plugins.tooltip || {};
        ['titleFont', 'bodyFont', 'footerFont'].forEach((fontKey) => {
            opts.plugins.tooltip[fontKey] = {
                size: fontKey === 'footerFont' ? 12 : 13,
                weight: 'bold',
                ...(opts.plugins.tooltip[fontKey] || {}),
            };
        });

        if (opts.scales) {
            Object.keys(opts.scales).forEach((axisKey) => {
                const scale = opts.scales[axisKey];
                if (!scale || typeof scale !== 'object') return;

                scale.ticks = scale.ticks || {};
                scale.ticks.color = window.chartTextColor;
                scale.ticks.font = { ...(scale.ticks.font || {}), ...boldFont };

                if (scale.pointLabels) {
                    scale.pointLabels.color = window.chartTextColor;
                    scale.pointLabels.font = { ...(scale.pointLabels.font || {}), ...boldFont };
                }
            });
        }

        return opts;
    };

    window.baseChartOptions = () => ({
        responsive: true,
        maintainAspectRatio: false,
        color: window.chartTextColor,
        font: { ...window.chartBoldFontSpec },
        plugins: {
            legend: window.chartLegendBold,
            tooltip: window.chartTooltipBold,
        },
    });

    window.mergeChartOptions = (extra = {}) => {
        const merged = window.deepMergeChartOptions(window.baseChartOptions(), extra);
        return window.enforceBoldChartFonts(merged);
    };

    window.applyBoldChartDefaults = () => {
        if (typeof Chart === 'undefined') return;

        Chart.defaults.color = window.chartTextColor;
        Chart.defaults.font.family = window.chartBoldFontSpec.family;
        Chart.defaults.font.size = 12;
        Chart.defaults.font.weight = 'bold';
        Chart.defaults.font.style = 'normal';
        Chart.defaults.maintainAspectRatio = false;
    };

    window.finalizeChartDefaults = () => {
        window.applyBoldChartDefaults();
    };

    window.categoryBarChartOptions = (yTickExtra = {}) => window.mergeChartOptions({
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { ...window.chartAxisTicksBold } },
            y: {
                beginAtZero: true,
                ticks: {
                    ...window.chartAxisTicksBold,
                    ...yTickExtra,
                },
            },
        },
    });

    window.statusStackedBarChartOptions = () => window.mergeChartOptions({
        layout: window.getMonthChartLayout(),
        plugins: { legend: { display: false } },
        scales: {
            x: {
                type: 'category',
                stacked: true,
                offset: true,
                ticks: { ...window.getMonthAxisTicks() },
            },
            y: {
                stacked: true,
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    ...window.chartAxisTicksBold,
                },
            },
        },
    });

    window.renderDatasetLegend = (listElementId, datasets) => {
        const labels = (datasets || []).map((dataset) => dataset.label).filter(Boolean);
        const colors = labels.map((label) => window.colorForStatus(label));
        window.renderChartHtmlLegend(listElementId, labels, colors);
    };
</script>
@endonce
