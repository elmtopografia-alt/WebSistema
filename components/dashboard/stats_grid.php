<!-- components/dashboard/stats_grid.php -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
    <!-- Total Propostas -->
    <div class="glass-card p-6 rounded-2xl relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-4 opacity-50">
            <i class="ph ph-file-text text-6xl text-slate-800 group-hover:text-slate-700 transition-colors"></i>
        </div>
        <div class="relative z-10">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 mb-4 border border-blue-500/20">
                <i class="ph ph-files text-2xl"></i>
            </div>
            <p class="text-slate-400 text-sm font-medium mb-1">Propostas (Mês)</p>
            <h3 class="text-3xl font-bold text-white"><?= $data['kpi']['total'] ?? 0 ?></h3>
        </div>
    </div>

    <!-- Aprovadas -->
    <div class="glass-card p-6 rounded-2xl relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-4 opacity-50">
            <i class="ph ph-check-circle text-6xl text-slate-800 group-hover:text-slate-700 transition-colors"></i>
        </div>
        <div class="relative z-10">
            <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-400 mb-4 border border-green-500/20">
                <i class="ph ph-thumbs-up text-2xl"></i>
            </div>
            <p class="text-slate-400 text-sm font-medium mb-1">Aprovadas (Mês)</p>
            <h3 class="text-3xl font-bold text-white"><?= $data['kpi']['aprovadas'] ?? 0 ?></h3>
        </div>
    </div>

    <!-- Valor Total -->
    <div class="glass-card p-6 rounded-2xl relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-4 opacity-50">
            <i class="ph ph-currency-dollar text-6xl text-slate-800 group-hover:text-slate-700 transition-colors"></i>
        </div>
        <div class="relative z-10">
            <div class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-400 mb-4 border border-orange-500/20">
                <i class="ph ph-chart-line-up text-2xl"></i>
            </div>
            <p class="text-slate-400 text-sm font-medium mb-1">Valor Total (Mês)</p>
            <div class="flex flex-col items-center">
                <span class="text-sm text-slate-500 line-through decoration-white decoration-1">
                    R$ <?= number_format(($data['kpi']['valor_total'] ?? 0) * 1.1, 0, ',', '.') ?>
                </span>
                <h3 class="text-3xl font-bold text-white whitespace-nowrap">
                    R$ <?= number_format($data['kpi']['valor_total'] ?? 0, 0, ',', '.') ?>
                </h3>
            </div>
        </div>
    </div>
</div>
