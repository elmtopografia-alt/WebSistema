<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const formatarMoeda = (val) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);

    // Aurora Theme Colors
    const colors = {
        primary: '#f97316', // Orange
        secondary: '#3b82f6', // Blue
        success: '#10b981', // Emerald
        surface: 'rgba(255, 255, 255, 0.05)',
        grid: 'rgba(255, 255, 255, 0.1)',
        text: '#94a3b8' // Slate 400
    };

    Chart.defaults.color = colors.text;
    Chart.defaults.borderColor = colors.grid;

    // Fetch Data
    fetch('api_graficos.php')
        .then(response => response.json())
        .then(data => {
            if(data.erro) { console.error(data.erro); return; }

            // Update KPIs
            const kpiReceita = document.getElementById('kpi-receita');
            const kpiVolume = document.getElementById('kpi-volume');
            const kpiTicket = document.getElementById('kpi-ticket');

            if(kpiReceita) kpiReceita.innerText = formatarMoeda(data.kpis.receita_real || 0);
            if(kpiVolume) kpiVolume.innerText = formatarMoeda(data.kpis.volume_orcado || 0);
            if(kpiTicket) kpiTicket.innerText = formatarMoeda(data.kpis.ticket_medio || 0);

            // 1. Evolution Chart (Area/Line)
            const ctxEvolucao = document.getElementById('graficoEvolucao');
            if (ctxEvolucao) {
                new Chart(ctxEvolucao, {
                    type: 'line',
                    data: {
                        labels: data.grafico_linha.map(i => i.label),
                        datasets: [
                            {
                                label: 'Receita (Aprovada)',
                                data: data.grafico_linha.map(i => i.total_aprovado),
                                borderColor: colors.success,
                                backgroundColor: (context) => {
                                    const ctx = context.chart.ctx;
                                    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                                    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
                                    gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');
                                    return gradient;
                                },
                                tension: 0.4,
                                fill: true,
                                borderWidth: 3,
                                pointBackgroundColor: colors.background,
                                pointBorderColor: colors.success,
                                pointBorderWidth: 2,
                                pointRadius: 4
                            },
                            {
                                label: 'Volume (Orçado)',
                                data: data.grafico_linha.map(i => i.total_orcado),
                                borderColor: colors.secondary, // Blue for potential
                                backgroundColor: 'transparent',
                                tension: 0.4,
                                borderWidth: 2,
                                borderDash: [5, 5],
                                pointRadius: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { labels: { usePointStyle: true, boxWidth: 6 } },
                            tooltip: { 
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titleColor: '#fff',
                                bodyColor: '#cbd5e1',
                                borderColor: 'rgba(255,255,255,0.1)',
                                borderWidth: 1,
                                padding: 10,
                                callbacks: { label: function(c) { return c.dataset.label + ': ' + formatarMoeda(c.raw); } } 
                            }
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: { 
                                grid: { color: colors.grid },
                                ticks: { callback: function(v) { return 'R$ ' + v.toLocaleString('pt-BR'); } } 
                            }
                        }
                    }
                });
            }

            // 2. Status Chart (Doughnut)
            const ctxStatus = document.getElementById('graficoStatus');
            if (ctxStatus) {
                new Chart(ctxStatus, {
                    type: 'doughnut',
                    data: {
                        labels: ['Aprovada', 'Enviada', 'Em elaboração', 'Cancelada'],
                        datasets: [{
                            data: [
                                data.status_pizza['Aprovada'] || 0,
                                data.status_pizza['Enviada'] || 0,
                                data.status_pizza['Em elaboração'] || 0,
                                data.status_pizza['Cancelada'] || 0
                            ],
                            backgroundColor: [
                                colors.success,  // Aprovada (Green)
                                colors.secondary, // Enviada (Blue)
                                '#94a3b8',       // Em elaboração (Slate)
                                '#ef4444'        // Cancelada (Red)
                            ],
                            borderWidth: 0,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, color: colors.text } }
                        }
                    }
                });
            }
        })
        .catch(err => console.error("Erro na API:", err));
</script>
