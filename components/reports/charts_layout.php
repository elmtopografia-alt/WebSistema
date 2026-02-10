<!-- components/reports/charts_layout.php -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Gráfico Evolução (2/3 da largura) -->
    <div class="lg:col-span-2 glass-card rounded-2xl p-6 border border-white/5">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="ph ph-chart-bar text-primary"></i> 
                Evolução Financeira
            </h3>
            <span class="text-xs text-slate-500 bg-white/5 px-2 py-1 rounded">Orçado vs Aprovado</span>
        </div>
        <div class="relative w-full h-80">
            <canvas id="graficoEvolucao"></canvas>
        </div>
    </div>

    <!-- Gráfico Pizza (1/3 da largura) -->
    <div class="glass-card rounded-2xl p-6 border border-white/5">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="ph ph-chart-pie-slice text-secondary"></i> 
                Funil de Status
            </h3>
        </div>
        <div class="relative w-full h-64">
            <canvas id="graficoStatus"></canvas>
        </div>
        <div class="mt-4 text-center">
            <p class="text-xs text-slate-500">Distribuição percentual das propostas</p>
        </div>
    </div>

</div>
