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
</script>
@endonce
