<!-- components/landing/hero.php -->
<section id="inicio" class="relative min-h-screen flex items-center pt-28 pb-16 overflow-hidden">
        <!-- Background Effects -->
    <div class="absolute inset-0 grid-bg opacity-30 pointer-events-none"></div>
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-orange-500/10 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            
            <!-- Left Content -->
            <div class="text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-500/10 text-orange-400 border border-orange-500/20 mb-8 hover:bg-orange-500/20 transition-colors">
                    <i class="ph ph-sparkle text-lg"></i>
                    <span class="text-sm font-medium">Sistema SaaS de Gestão</span>
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mb-6 font-display">
                    <span class="text-white">SGT-</span>
                    <span class="text-gradient">Propostas</span>
                </h1>

                <p class="text-xl sm:text-2xl text-slate-300 font-medium mb-4">
                    Gestão de Prosperidade
                </p>

                <p class="text-slate-400 text-base sm:text-lg mb-8 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                    Sistema integrado ao MySQL, seguro e acessível. Transforme leads em contratos fechados com nossa solução SaaS completa.
                </p>

                <div class="flex flex-col sm:flex-row gap-8 justify-center lg:justify-start mb-10">
                    <!-- Já Sou Cliente -->
                    <div class="space-y-3">
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-bold">Já sou cliente</p>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button onclick="toggleLoginModal()" class="btn-shine inline-flex items-center justify-center px-6 py-3 rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white shadow-lg shadow-blue-500/25 font-semibold transition-all hover:-translate-y-1">
                                <i class="ph ph-crown text-lg mr-2"></i>
                                Login Cliente PRO
                            </button>
                            <a href="login_demo.php" class="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-white/20 text-slate-300 hover:text-white hover:bg-white/5 transition-all hover:-translate-y-1">
                                <i class="ph ph-arrow-right text-lg mr-2"></i>
                                Login Demo
                            </a>
                        </div>
                    </div>

                    <!-- Quero Conhecer -->
                    <div class="space-y-3">
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-bold">Quero conhecer</p>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="criar_conta_demo.php" class="btn-shine inline-flex items-center justify-center px-6 py-3 rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white shadow-lg shadow-orange-500/25 font-semibold transition-all hover:-translate-y-1">
                                <i class="ph ph-rocket-launch text-lg mr-2"></i>
                                Criar Conta Demo
                            </a>
                            <a href="#planos" class="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-green-500/30 text-green-400 hover:text-green-300 hover:bg-green-500/10 transition-all hover:-translate-y-1">
                                <i class="ph ph-shopping-cart text-lg mr-2"></i>
                                Plano PRO
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Badges -->
                <div class="flex flex-wrap gap-3 justify-center lg:justify-start">
                    <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10">
                        <i class="ph ph-database text-orange-400"></i>
                        <span class="text-sm text-slate-300">MySQL</span>
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10">
                        <i class="ph ph-shield-check text-orange-400"></i>
                        <span class="text-sm text-slate-300">Seguro</span>
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10">
                        <i class="ph ph-device-mobile text-orange-400"></i>
                        <span class="text-sm text-slate-300">Responsivo</span>
                    </div>
                </div>
            </div>

            <!-- Right Content (Mockup 1 - Pie Chart) -->
            <div class="relative mt-12 lg:mt-0 animate-float">
                <div class="absolute -inset-4 bg-gradient-to-r from-orange-500/20 to-blue-500/20 rounded-3xl blur-2xl"></div>
                
                <div class="relative bg-surface rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
                    <!-- Fake Browser Header -->
                    <div class="flex items-center gap-2 px-4 py-3 bg-[#0f172a] border-b border-white/5">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                        </div>
                        <div class="flex-1 text-center">
                            <span class="text-xs text-slate-500 font-mono">sgt-propostas.app/dashboard</span>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6">
                        <!-- Top Stats -->
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                                <p class="text-xs text-slate-500 mb-1">Custo Total</p>
                                <p class="text-sm font-bold text-blue-400">R$ 300.000,00</p>
                            </div>
                            <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                                <p class="text-xs text-slate-500 mb-1">Margem</p>
                                <p class="text-sm font-bold text-green-400">20%</p>
                            </div>
                            <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                                <p class="text-xs text-slate-500 mb-1">Valor Proposta</p>
                                <p class="text-sm font-bold text-orange-400">R$ 375.000,00</p>
                            </div>
                        </div>

                        <!-- Chart Area -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl bg-white/5 border border-white/10 flex flex-col items-center justify-center">
                                <!-- Simple CSS Pie Chart representation -->
                                <div class="relative w-32 h-32 rounded-full border-8 border-slate-700 flex items-center justify-center">
                                    <div class="absolute inset-0 rounded-full border-8 border-blue-500 border-t-transparent border-l-transparent transform rotate-45"></div>
                                    <div class="text-center">
                                        <p class="text-[10px] text-slate-500">Total</p>
                                        <p class="text-sm font-bold text-white">R$ 375k</p>
                                    </div>
                                </div>
                                <div class="mt-4 w-full space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="flex items-center gap-2 text-slate-400"><span class="w-2 h-2 rounded-full bg-blue-500"></span>Mão de Obra</span>
                                        <span class="text-slate-300">35%</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="flex items-center gap-2 text-slate-400"><span class="w-2 h-2 rounded-full bg-orange-500"></span>Materiais</span>
                                        <span class="text-slate-300">28%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                                <p class="text-xs font-medium text-white mb-3">Custos Recentes</p>
                                <div class="space-y-2">
                                    <div class="flex justify-between py-1 border-b border-white/5">
                                        <span class="text-xs text-slate-400">Mão de Obra</span>
                                        <span class="text-xs text-slate-300">R$ 105k</span>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-white/5">
                                        <span class="text-xs text-slate-400">Materiais</span>
                                        <span class="text-xs text-slate-300">R$ 84k</span>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-white/5">
                                        <span class="text-xs text-slate-400">Equip.</span>
                                        <span class="text-xs text-slate-300">R$ 66k</span>
                                    </div>
                                    <div class="mt-2 pt-1 flex justify-between">
                                        <span class="text-xs font-bold text-white">Total</span>
                                        <span class="text-xs font-bold text-orange-400">R$ 375k</span>
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
