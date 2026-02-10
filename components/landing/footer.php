<!-- components/landing/footer.php -->
<footer class="relative bg-[#0f172a] border-t border-white/10 pt-12 pb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8 mb-8">
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-xl font-bold font-display text-white">SGT-</span>
                    <span class="text-xl font-bold font-display text-orange-500">Propostas</span>
                </div>
                <p class="text-slate-400 text-sm max-w-sm">
                    Simplifique sua gestão de propostas e vendas com nossa solução SaaS. Segurança, velocidade e prosperidade em um só lugar.
                </p>
                <div class="flex gap-4 mt-6">
                    <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-slate-400 hover:text-white hover:bg-blue-600 hover:scale-110 transition-all">
                        <i class="ph ph-instagram-logo text-lg"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-slate-400 hover:text-white hover:bg-blue-800 hover:scale-110 transition-all">
                        <i class="ph ph-facebook-logo text-lg"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-slate-400 hover:text-white hover:bg-sky-500 hover:scale-110 transition-all">
                        <i class="ph ph-twitter-logo text-lg"></i>
                    </a>
                </div>
            </div>
            
            <div>
                <h4 class="text-white font-bold mb-4">Produto</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="#recursos" class="hover:text-orange-400 transition-colors">Recursos</a></li>
                    <li><a href="#dashboard" class="hover:text-orange-400 transition-colors">Dashboard</a></li>
                    <li><a href="#planos" class="hover:text-orange-400 transition-colors">Planos e Preços</a></li>
                    <li><a href="login_demo.php" class="hover:text-orange-400 transition-colors">Demonstração</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-bold mb-4">Legal</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="termos_uso.php" class="hover:text-orange-400 transition-colors">Termos de Uso</a></li>
                    <li><a href="politica_privacidade.php" class="hover:text-orange-400 transition-colors">Privacidade</a></li>
                    <li><a href="#" class="hover:text-orange-400 transition-colors">Contato</a></li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-white/5 pt-6 text-center">
            <p class="text-slate-500 text-sm">
                &copy; <?php echo date('Y'); ?> SGT Propostas. Todos os direitos reservados.
            </p>
        </div>
    </div>
</footer>

<script>
    // Smooth Scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Mobile Menu
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    }

    // Navbar transparency logic
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 20) {
            navbar.classList.remove('bg-transparent');
            navbar.classList.add('glass', 'shadow-lg');
        } else {
            navbar.classList.add('bg-transparent');
            navbar.classList.remove('glass', 'shadow-lg');
        }
    });

    // Modal Logic
    function toggleLoginModal() {
        const modal = document.getElementById('loginModal');
        const content = document.getElementById('modalContent');
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        } else {
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    }
</script>
