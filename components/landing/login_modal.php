<!-- components/landing/login_modal.php -->
<!-- LOGIN MODAL -->
<div id="loginModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" onclick="toggleLoginModal()"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div id="modalContent" class="relative transform overflow-hidden rounded-2xl bg-[#0f172a] border border-white/10 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md scale-95 opacity-0 duration-300">
            
            <!-- Close Button -->
            <button onclick="toggleLoginModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white transition-colors">
                <i class="ph ph-x text-xl"></i>
            </button>

            <div class="p-8">
                <div class="text-center mb-6">
                    <div class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center mx-auto mb-4 border border-orange-500/20">
                        <i class="ph ph-user-circle text-2xl text-orange-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Bem-vindo de volta!</h3>
                    <p class="text-sm text-slate-400">Acesse sua conta para continuar</p>
                </div>

                <!-- Error Message (PHP Injection) -->
                <?php if (!empty($auth['erro_login'])): ?>
                    <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 flex items-start gap-3">
                        <i class="ph ph-warning-circle text-red-500 mt-0.5"></i>
                        <p class="text-sm text-red-400"><?= htmlspecialchars($auth['erro_login']) ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1 uppercase tracking-wider">Usuário</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
                                    <i class="ph ph-envelope-simple"></i>
                                </span>
                                <input type="text" name="usuario" class="w-full bg-[#1e293b] border border-slate-700 rounded-lg py-2.5 pl-10 pr-4 text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-colors" placeholder="Seu usuário" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1 uppercase tracking-wider">Senha</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
                                    <i class="ph ph-lock-key"></i>
                                </span>
                                <input type="password" name="senha" class="w-full bg-[#1e293b] border border-slate-700 rounded-lg py-2.5 pl-10 pr-4 text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-colors" placeholder="Sua senha" required>
                            </div>
                            <div class="text-right mt-1">
                                <a href="esqueci_senha.php" class="text-xs text-orange-400 hover:text-orange-300 transition-colors">Esqueceu a senha?</a>
                            </div>
                        </div>

                        <button type="submit" class="w-full btn-shine py-3 rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold shadow-lg shadow-orange-500/25 transition-all">
                            Entrar
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-6 border-t border-white/5 text-center">
                    <p class="text-sm text-slate-400">
                        Não tem uma conta? 
                        <a href="criar_conta_demo.php" class="text-orange-400 hover:text-orange-300 font-medium transition-colors">Criar conta grátis</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($auth['modal_aberto'])): ?>
    <script>
        // Auto-open modal if PHP says so (error state)
        document.addEventListener('DOMContentLoaded', function() {
            toggleLoginModal();
        });
    </script>
    <?php endif; ?>
</div>
