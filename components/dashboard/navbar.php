<!-- components/dashboard/navbar.php -->
<nav class="fixed top-0 w-full glass z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <?php include __DIR__ . '/../../components/logo_svg.php'; ?>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center gap-4">
                <a href="painel.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/5">
                    <i class="ph ph-chart-line-up"></i> Dashboard
                </a>
                <a href="minha_empresa.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/5">
                    <i class="ph ph-buildings"></i> Empresa
                </a>
                <a href="meus_clientes.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/5">
                    <i class="ph ph-users"></i> Clientes
                </a>
                <a href="minha_assinatura.php" class="text-sm font-medium text-orange-400 hover:text-orange-300 transition-colors flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-orange-500/10">
                    <i class="ph ph-crown"></i> Assinatura
                </a>
                
                <?php if($data['usuario']['perfil'] == 'admin'): ?>
                    <a href="admin_usuarios.php" class="px-3 py-1.5 bg-yellow-600/20 hover:bg-yellow-600/30 text-yellow-400 text-xs font-bold rounded-lg border border-yellow-600/30 transition-colors">
                        Admin
                    </a>
                <?php endif; ?>

                <?php if(isset($nav_extra_content)) echo $nav_extra_content; ?>

                <a href="criar_proposta_dinamica.php" class="ml-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-sm font-bold rounded-lg transition-all shadow-lg shadow-orange-500/25 flex items-center gap-2 transform hover:-translate-y-0.5">
                    <i class="ph ph-plus-bold"></i> Nova Proposta
                </a>

                <!-- User Menu Dropdown -->
                <div class="relative group ml-2">
                    <button class="flex items-center gap-3 text-white font-medium pl-3 border-l border-white/10">
                        <div class="w-9 h-9 rounded-full bg-surface border border-white/10 flex items-center justify-center text-orange-400 ring-2 ring-transparent group-hover:ring-orange-500/30 transition-all">
                            <i class="ph ph-user"></i>
                        </div>
                    </button>
                    <!-- Dropdown -->
                    <div class="absolute right-0 mt-4 w-56 glass rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right z-50">
                        <div class="p-3 border-b border-white/5 mb-1">
                            <p class="text-sm font-bold text-white"><?= htmlspecialchars($data['usuario']['nome_completo']) ?></p>
                            <p class="text-xs text-slate-500">Usuário PRO</p>
                        </div>
                        <div class="p-1">
                            <a href="alterar_senha.php" class="flex items-center w-full px-3 py-2 text-sm text-slate-300 hover:bg-white/5 hover:text-white rounded-lg transition-colors">
                                <i class="ph ph-key mr-2 text-slate-400"></i> Alterar Senha
                            </a>
                            <a href="logout.php" class="flex items-center w-full px-3 py-2 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 rounded-lg transition-colors">
                                <i class="ph ph-sign-out mr-2"></i> Sair
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu Button -->
            <button class="md:hidden p-2 text-slate-300 hover:text-white" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                <i class="ph ph-list text-2xl"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden glass border-t border-white/10 absolute w-full left-0">
        <div class="p-4 space-y-2">
            <a href="painel.php" class="block px-4 py-3 rounded-lg hover:bg-white/5 text-slate-300">Dashboard</a>
            <a href="criar_proposta_dinamica.php?nova=1" class="block px-4 py-3 rounded-lg bg-orange-500/20 text-orange-400 font-bold">Nova Proposta</a>
            <a href="meus_clientes.php" class="block px-4 py-3 rounded-lg hover:bg-white/5 text-slate-300">Clientes</a>
            <a href="minha_empresa.php" class="block px-4 py-3 rounded-lg hover:bg-white/5 text-slate-300">Minha Empresa</a>
            <a href="logout.php" class="block px-4 py-3 rounded-lg hover:bg-red-500/10 text-red-400">Sair</a>
        </div>
    </div>
</nav>
