<!-- components/landing/dashboard_preview.php -->
<section id="dashboard" class="relative py-24 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-orange-500/5 to-transparent pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-2 rounded-full bg-blue-500/10 text-blue-400 text-sm font-medium mb-4 border border-blue-500/20">Dashboard</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4 font-display">
                Visualize seu negócio em <span class="text-gradient-blue">tempo real</span>
            </h2>
            <p class="text-slate-400 text-lg max-w-2xl mx-auto">
                Dashboards intuitivos que mostram exatamente onde está o dinheiro e como otimizar seus resultados.
            </p>
        </div>

        <div class="relative">
            <!-- Glow -->
            <div class="absolute -inset-4 bg-gradient-to-r from-blue-500/10 to-orange-500/10 rounded-3xl blur-2xl pointer-events-none"></div>

            <!-- Container -->
            <div class="relative bg-surface rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 bg-[#0f172a] border-b border-white/5">
                    <div class="flex items-center gap-4">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        </div>
                        <span class="text-sm text-slate-500 font-mono hidden sm:inline">sgt-propostas.app/dashboard</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5">
                            <i class="ph ph-calendar-blank text-slate-400"></i>
                            <span class="text-sm text-slate-300">Últimos 30 dias</span>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="p-4 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <i class="ph ph-file-text text-slate-400 text-xl"></i>
                                <span class="text-xs text-green-400 flex items-center gap-1">+12% <i class="ph ph-trend-up"></i></span>
                            </div>
                            <p class="text-2xl font-bold text-white mb-1">1.234</p>
                            <p class="text-xs text-slate-500">Propostas Enviadas</p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <i class="ph ph-chart-line-up text-slate-400 text-xl"></i>
                                <span class="text-xs text-green-400 flex items-center gap-1">+5% <i class="ph ph-trend-up"></i></span>
                            </div>
                            <p class="text-2xl font-bold text-white mb-1">68%</p>
                            <p class="text-xs text-slate-500">Conversão</p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <i class="ph ph-users text-slate-400 text-xl"></i>
                                <span class="text-xs text-green-400 flex items-center gap-1">+23% <i class="ph ph-trend-up"></i></span>
                            </div>
                            <p class="text-2xl font-bold text-white mb-1">456</p>
                            <p class="text-xs text-slate-500">Clientes</p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <i class="ph ph-currency-dollar text-slate-400 text-xl"></i>
                                <span class="text-xs text-green-400 flex items-center gap-1">+18% <i class="ph ph-trend-up"></i></span>
                            </div>
                            <p class="text-2xl font-bold text-white mb-1">R$ 2.5M</p>
                            <p class="text-xs text-slate-500">Faturamento</p>
                        </div>
                    </div>

                    <!-- List -->
                        <div class="grid lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 p-5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center">
                            <div class="text-center py-10">
                                <i class="ph ph-chart-bar text-6xl text-slate-600 mb-4"></i>
                                <p class="text-slate-400">Gráfico de Desempenho Visual</p>
                            </div>
                        </div>
                        <div class="p-5 rounded-xl bg-white/5 border border-white/10">
                            <h4 class="text-sm font-medium text-white mb-4">Recentes</h4>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 rounded-lg bg-white/5">
                                    <div>
                                        <p class="text-sm text-white">Construtora ABC</p>
                                        <p class="text-xs text-slate-500">Hoje</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-orange-400">R$ 150k</p>
                                        <span class="text-xs text-green-400">Aprovada</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-lg bg-white/5">
                                    <div>
                                        <p class="text-sm text-white">Engenharia XYZ</p>
                                        <p class="text-xs text-slate-500">Ontem</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-orange-400">R$ 89.5k</p>
                                        <span class="text-xs text-yellow-400">Pendente</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>

                </div>
            </div>
        </div>
        </div>
</section>
