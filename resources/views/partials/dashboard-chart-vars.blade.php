@once
<script>
    window.leaveStatusColors = @json(config('leave_status_colors.colors'));
    window.leaveStatusPieLabels = @json(config('leave_status_colors.pie_labels'));
    window.leaveMonthlyApplicationsColor = @json(config('leave_status_colors.monthly_applications_line'));

    window.colorForStatus = (label) => {
        const key = String(label || '').trim().toLowerCase();
        return window.leaveStatusColors[key] || '#64748b';
    };

    window.statusColorsForLabels = (labels) => labels.map((label) => window.colorForStatus(label));

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

    window.applyBoldChartDefaults = () => {
        if (typeof Chart === 'undefined') return;
        Chart.defaults.font.family = '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif';
        Chart.defaults.font.weight = '700';
        Chart.defaults.color = '#1e293b';
    };

    window.boldChartOptions = () => ({
        responsive: true,
        plugins: {
            legend: {
                labels: {
                    font: { weight: '700', size: 12 },
                },
            },
            tooltip: {
                bodyFont: { size: 13, weight: '700' },
                titleFont: { size: 13, weight: '700' },
            },
        },
        scales: {
            x: {
                ticks: { font: { weight: '700' } },
            },
            y: {
                ticks: { font: { weight: '700' } },
            },
        },
    });

    window.mergeChartOptions = (base = {}, extra = {}) => {
        const merged = { ...base, ...extra };
        merged.plugins = { ...(base.plugins || {}), ...(extra.plugins || {}) };
        if (base.scales || extra.scales) {
            merged.scales = { ...(base.scales || {}), ...(extra.scales || {}) };
            ['x', 'y'].forEach((axis) => {
                if (base.scales?.[axis] || extra.scales?.[axis]) {
                    merged.scales[axis] = {
                        ...(base.scales?.[axis] || {}),
                        ...(extra.scales?.[axis] || {}),
                        ticks: {
                            ...((base.scales?.[axis] || {}).ticks || {}),
                            ...((extra.scales?.[axis] || {}).ticks || {}),
                            font: {
                                weight: '700',
                                ...(((base.scales?.[axis] || {}).ticks || {}).font || {}),
                                ...(((extra.scales?.[axis] || {}).ticks || {}).font || {}),
                            },
                        },
                    };
                }
            });
        }
        return merged;
    };
</script>
@endonce
