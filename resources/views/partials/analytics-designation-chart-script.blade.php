    const designationLabels = @json($designationLabels);
    const designationColors = designationLabels.map((label) => colorForGroupLabel(label));
    const designationPct = @json($designationPercentages ?? []);

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: designationLabels,
            datasets: [{
                data: @json($designationData),
                backgroundColor: designationColors,
                borderColor: '#fff',
                borderWidth: designationLabels.length > 1 ? 2 : 0
            }]
        },
        options: mergeChartOptions({
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const pct = (designationPct[ctx.label] ?? 0).toFixed(1);
                            return `${ctx.label}: ${ctx.parsed}`;
                        }
                    }
                }
            }
        })
    });
    renderChartHtmlLegend('designationPieLegend', designationLabels, designationColors);
