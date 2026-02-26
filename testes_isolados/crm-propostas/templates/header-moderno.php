<?php if (!defined('SGT_PROPOTAS')) die('Acesso negado'); ?>

<header class="header-proposta">
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
        <h1>Proposta Técnica</h1>
        <div class="header-subtitulo">Elegância e Precisão</div>
    </div>
</header>

<div class="header-meta">
    <span><?= $proposta['data_extenso'] ?></span>
    <span class="numero-proposta">Nº <?= htmlspecialchars($proposta['numero']) ?></span>
</div>
