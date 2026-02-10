<!-- components/landing/navbar.php -->
<header id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-transparent">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <!-- Logo -->
            <a href="#inicio" class="flex items-center gap-2 group transform hover:scale-105 transition-transform duration-200">
                <?php include __DIR__ . '/../../components/logo_svg.php'; ?>
            </a>

            <!-- Desktop Nav -->
            <nav class="hidden md:flex items-center gap-1">
                <a href="#inicio" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-white/5 transition-colors">Início</a>
                <a href="#recursos" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-white/5 transition-colors">Recursos</a>
                <a href="#dashboard" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-white/5 transition-colors">Dashboard</a>
                <a href="#planos" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-white/5 transition-colors">Planos</a>
            </nav>

            <!-- CTA -->
            <div class="hidden md:flex items-center gap-3">
                <button onclick="toggleLoginModal()" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 rounded-lg transition-colors">
                    Login
                </button>
                <a href="#planos" class="btn-shine px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-sm font-bold rounded-lg shadow-lg shadow-orange-500/25 transition-all">
                    Começar Agora
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <button onclick="toggleMobileMenu()" class="md:hidden p-2 rounded-lg text-slate-300 hover:text-white hover:bg-white/5">
                <i class="ph ph-list text-2xl"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden glass border-t border-white/10 absolute w-full">
        <div class="px-4 py-4 space-y-2">
            <a href="#inicio" onclick="toggleMobileMenu()" class="block px-4 py-3 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg">Início</a>
            <a href="#recursos" onclick="toggleMobileMenu()" class="block px-4 py-3 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg">Recursos</a>
            <a href="#dashboard" onclick="toggleMobileMenu()" class="block px-4 py-3 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg">Dashboard</a>
            <a href="#planos" onclick="toggleMobileMenu()" class="block px-4 py-3 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg">Planos</a>
            <div class="pt-4 space-y-2 border-t border-white/10">
                <button onclick="toggleLoginModal(); toggleMobileMenu()" class="w-full px-4 py-2 border border-white/20 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg">
                    Login
                </button>
                <a href="#planos" onclick="toggleMobileMenu()" class="block text-center w-full px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold rounded-lg">
                    Começar Agora
                </a>
            </div>
        </div>
    </div>
</header>
