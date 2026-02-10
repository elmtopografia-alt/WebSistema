<!-- components/reports/kpi_cards.php -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <!-- Receita Real (Aprovada) -->
    <div class="glass-card p-6 rounded-2xl relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-transparent opacity-50"></div>
        <div class="absolute top-0 right-0 p-4 opacity-50">
            <i class="ph ph-money text-6xl text-green-900/40 group-hover:text-green-500/10 transition-colors"></i>
        </div>
        
        <div class="relative z-10">
            <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-400 mb-4 border border-green-500/20">
                <i class="ph ph-chart-line-up text-2xl"></i>
            </div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Receita Real (Aprovada)</p>
            <h3 class="text-3xl font-bold text-white" id="kpi-receita">R$ 0,00</h3>
        </div>
    </div>

    <!-- Volume Total Orçado -->
    <div class="glass-card p-6 rounded-2xl relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-transparent opacity-50"></div>
        <div class="absolute top-0 right-0 p-4 opacity-50">
            <i class="ph ph-calculator text-6xl text-blue-900/40 group-hover:text-blue-500/10 transition-colors"></i>
        </div>
        
        <div class="relative z-10">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 mb-4 border border-blue-500/20">
                <i class="ph ph-files text-2xl"></i>
            </div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Volume Total Orçado</p>
            <h3 class="text-3xl font-bold text-white" id="kpi-volume">R$ 0,00</h3>
        </div>
    </div>

    <!-- Ticket Médio -->
    <div class="glass-card p-6 rounded-2xl relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-transparent opacity-50"></div>
        <div class="absolute top-0 right-0 p-4 opacity-50">
            <i class="ph ph-trend-up text-6xl text-purple-900/40 group-hover:text-purple-500/10 transition-colors"></i>
        </div>
        
        <div class="relative z-10">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400 mb-4 border border-purple-500/20">
                <i class="ph ph-receipt text-2xl"></i>
            </div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Ticket Médio Geral</p>
            <h3 class="text-3xl font-bold text-white" id="kpi-ticket">R$ 0,00</h3>
        </div>
    </div>

</div>
