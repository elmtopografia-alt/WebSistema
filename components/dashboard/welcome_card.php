<!-- components/dashboard/welcome_card.php -->
<div class="glass rounded-2xl p-8 mb-8 text-center border border-white/5 relative overflow-hidden group">
    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-blue-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
    
    <div class="relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-500/10 border border-green-500/20 text-green-400 text-xs font-bold mb-6 tracking-wide uppercase">
            <i class="ph ph-crown-simple text-lg"></i> Assinante PRO
        </div>
        
        <h1 class="font-display text-4xl md:text-5xl font-bold text-white mb-4">
            Bem-vindo, <span class="text-gradient"><?= htmlspecialchars($data['usuario']['primeiro_nome']) ?></span>!
        </h1>
        
        <p class="text-slate-400 text-lg max-w-2xl mx-auto mb-6">
            Seu painel de controle está pronto. Vamos gerar novos negócios hoje?
        </p>
        
        <?php if($data['assinatura']['ativa']): ?>
            <div class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-surface/50 border border-white/5 text-sm text-slate-400">
                <i class="ph ph-calendar-check text-green-400"></i>
                <span>Acesso válido até <strong class="text-white"><?= $data['assinatura']['validade_formatada'] ?></strong></span>
                <?php if($data['assinatura']['dias_restantes'] <= 30): ?>
                    <a href="minha_assinatura.php" class="ml-2 text-xs font-bold text-orange-400 hover:text-orange-300 underline">Renovar Agora</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
