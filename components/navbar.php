<?php
// Arquivo: components/navbar.php
// Componente Reutilizável de Navegação COM MENU MOBILE

// Garante variáveis
if (!isset($nome_usuario)) $nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
if (!isset($primeiro_nome)) $primeiro_nome = explode(' ', trim($nome_usuario))[0];
if (!isset($is_demo)) $is_demo = (($_SESSION['ambiente'] ?? '') === 'demo');

$currentPage = basename($_SERVER['PHP_SELF']);

function isActive($page, $current) {
    return ($page === $current) ? 'text-white font-bold' : 'text-slate-300 hover:text-white';
}
?>
<!-- Navbar -->
<nav class="w-full glass-panel sticky top-0 z-50 border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-14">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <?php 
                if(file_exists(__DIR__ . '/logo_svg.php')) {
                    include __DIR__ . '/logo_svg.php'; 
                } else if(file_exists('components/logo_svg.php')) {
                    include 'components/logo_svg.php';
                } else {
                    echo '<span class="text-xl font-bold text-white">SGT</span>';
                }
                ?>
                <?php if($is_demo): ?>
                    <span class="px-2 py-0.5 rounded bg-yellow-500/20 text-yellow-400 text-[10px] font-bold border border-yellow-500/30 uppercase">DEMO</span>
                <?php endif; ?>
            </div>

            <!-- Botão Hambúrguer (Mobile) -->
            <button id="btnMenuMobile" class="md:hidden p-2 text-white" onclick="toggleMenuMobile()">
                <i class="ph ph-list text-2xl"></i>
            </button>

            <!-- Menu Desktop -->
            <div class="hidden md:flex items-center gap-4">
                <?php if($currentPage !== 'painel.php'): ?>
                <a href="painel.php" class="text-sm transition-colors flex items-center gap-2 <?= isActive('painel.php', $currentPage) ?>">
                    <i class="ph ph-house"></i> Painel
                </a>
                <?php endif; ?>
                <a href="minha_empresa.php" class="text-sm transition-colors flex items-center gap-2 <?= isActive('minha_empresa.php', $currentPage) ?>">
                    <i class="ph ph-gear"></i> Empresa
                </a>
                <a href="meus_clientes.php" class="text-sm transition-colors flex items-center gap-2 <?= isActive('meus_clientes.php', $currentPage) ?>">
                    <i class="ph ph-users-three-fill text-brand-accent"></i> Clientes
                </a>
                <a href="painel_crm.php" class="text-sm transition-colors flex items-center gap-2 <?= isActive('painel_crm.php', $currentPage) ?>">
                    <i class="ph ph-kanban text-orange-400"></i> CRM
                </a>
                <?php if(isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin'): ?>
                <a href="painel_prospeccao.php" class="text-sm transition-colors flex items-center gap-2 <?= isActive('painel_prospeccao.php', $currentPage) ?>">
                    <i class="ph ph-target text-red-400"></i> Leads
                </a>
                <?php endif; ?>
                
                <a href="criar_proposta.php" class="ml-2 px-4 py-2 bg-[#0066CC] hover:bg-[#0052a3] text-white text-sm font-bold rounded-lg transition-all flex items-center gap-2">
                    <i class="ph ph-plus-bold"></i> NOVA
                </a>
                
                <!-- User + Sair -->
                <div class="flex items-center gap-2 pl-3 border-l border-white/10">
                    <span class="text-sm text-slate-300"><?= htmlspecialchars($primeiro_nome) ?></span>
                    <a href="logout.php" class="flex items-center gap-1 px-2 py-1 text-sm text-red-400 hover:bg-red-500/10 rounded-lg" title="Sair">
                        <i class="ph ph-sign-out"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Mobile (Dropdown) -->
    <div id="menuMobile" class="hidden md:hidden bg-surface/95 backdrop-blur-lg border-t border-white/10">
        <div class="px-4 py-4 space-y-1">
            <a href="painel.php" class="flex items-center gap-3 px-4 py-3 text-white hover:bg-white/5 rounded-lg">
                <i class="ph ph-house text-xl text-blue-400"></i> Painel
            </a>
            <a href="minha_empresa.php" class="flex items-center gap-3 px-4 py-3 text-white hover:bg-white/5 rounded-lg">
                <i class="ph ph-gear text-xl text-slate-400"></i> Minha Empresa
            </a>
            <a href="meus_clientes.php" class="flex items-center gap-3 px-4 py-3 text-white hover:bg-white/5 rounded-lg">
                <i class="ph ph-users-three-fill text-xl text-orange-400"></i> Meus Clientes
            </a>
            <a href="painel_crm.php" class="flex items-center gap-3 px-4 py-3 text-white hover:bg-white/5 rounded-lg">
                <i class="ph ph-kanban text-xl text-orange-400"></i> CRM
            </a>
            <?php if(isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin'): ?>
            <a href="painel_prospeccao.php" class="flex items-center gap-3 px-4 py-3 text-white hover:bg-white/5 rounded-lg">
                <i class="ph ph-target text-xl text-red-400"></i> Leads
            </a>
            <?php endif; ?>
            
            <div class="border-t border-white/10 my-2"></div>
            
            <a href="criar_proposta.php" class="flex items-center justify-center gap-2 px-4 py-3 bg-[#0066CC] text-white font-bold rounded-lg">
                <i class="ph ph-plus-bold"></i> NOVA PROPOSTA
            </a>
            
            <div class="border-t border-white/10 my-2"></div>
            
            <div class="flex items-center justify-between px-4 py-3">
                <span class="text-slate-400 text-sm">👤 <?= htmlspecialchars($primeiro_nome) ?></span>
                <a href="logout.php" class="flex items-center gap-2 px-4 py-2 bg-red-500/10 text-red-400 font-bold rounded-lg">
                    <i class="ph ph-sign-out"></i> Sair
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
function toggleMenuMobile() {
    const menu = document.getElementById('menuMobile');
    const btn = document.getElementById('btnMenuMobile');
    menu.classList.toggle('hidden');
    // Troca ícone
    const icon = btn.querySelector('i');
    if (menu.classList.contains('hidden')) {
        icon.className = 'ph ph-list text-2xl';
    } else {
        icon.className = 'ph ph-x text-2xl';
    }
}
</script>
