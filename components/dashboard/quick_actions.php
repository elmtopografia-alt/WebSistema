<!-- components/dashboard/quick_actions.php -->
<h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
    <i class="ph ph-lightning text-orange-400"></i> Acesso Rápido
</h2>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Criar Nova -->
    <a href="criar_proposta_dinamica.php" class="glass-card p-6 rounded-2xl flex flex-col items-center text-center hover:bg-surface/80 group border-dashed border-2 border-white/10 hover:border-orange-500/50">
        <div class="w-16 h-16 rounded-full bg-orange-500/10 flex items-center justify-center text-orange-400 mb-4 group-hover:scale-110 transition-transform">
            <i class="ph ph-plus text-3xl"></i>
        </div>
        <h3 class="text-white font-bold mb-1">Nova Proposta</h3>
        <p class="text-xs text-slate-500">Criar orçamento do zero</p>
    </a>

    <!-- Painel -->
    <a href="painel.php" class="glass-card p-6 rounded-2xl flex flex-col items-center text-center group">
        <div class="w-16 h-16 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400 mb-4 group-hover:scale-110 transition-transform">
            <i class="ph ph-squares-four text-3xl"></i>
        </div>
        <h3 class="text-white font-bold mb-1">Dashboard</h3>
        <p class="text-xs text-slate-500">Gerenciar propostas</p>
    </a>

    <!-- Clientes -->
    <a href="meus_clientes.php" class="glass-card p-6 rounded-2xl flex flex-col items-center text-center group">
        <div class="w-16 h-16 rounded-full bg-purple-500/10 flex items-center justify-center text-purple-400 mb-4 group-hover:scale-110 transition-transform">
            <i class="ph ph-users-three text-3xl"></i>
        </div>
        <h3 class="text-white font-bold mb-1">Meus Clientes</h3>
        <p class="text-xs text-slate-500">Base de contatos</p>
    </a>

    <!-- Configurações -->
    <a href="minha_empresa.php" class="glass-card p-6 rounded-2xl flex flex-col items-center text-center group">
        <div class="w-16 h-16 rounded-full bg-slate-500/10 flex items-center justify-center text-slate-400 mb-4 group-hover:scale-110 transition-transform">
            <i class="ph ph-gear text-3xl"></i>
        </div>
        <h3 class="text-white font-bold mb-1">Configurações</h3>
        <p class="text-xs text-slate-500">Dados da empresa</p>
    </a>
</div>
