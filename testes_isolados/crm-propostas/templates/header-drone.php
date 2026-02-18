<?php if (!defined('SGT_PROPOSTAS')) die('Acesso negado'); ?>

<header class="header-proposta">
    <div class="badge-tech">VANT / Drone</div>
    <div class="header-logo">
        <?php 
        $logo_path = $empresa['logo'];
        $real_logo_path = "../../" . $logo_path;
        if (!empty($logo_path) && file_exists($real_logo_path)): ?>
            <img src="<?= $real_logo_path ?>" alt="<?= htmlspecialchars($empresa['nome']) ?>">
        <?php else: ?>
            <div class="logo-placeholder">[LOGO]</div>
        <?php endif; ?>
    </div>
    <div class="header-titulo">
        <h1>Proposta de Serviços</h1>
        <div class="header-subtitulo">Topografia e Mapeamento Aéreo</div>
    </div>
</header>

<div class="header-meta">
    <span><strong>Proposta Nº:</strong> <?= htmlspecialchars($proposta['numero']) ?></span>
    <span class="numero-proposta"><?= htmlspecialchars($proposta['cidade']) ?>, <?= $proposta['data_extenso'] ?></span>
</div>
