<?php if (!defined('SGT_PROPOSTAS')) die('Acesso negado'); ?>

<header class="header-proposta">
    <div class="header-logo">
        <?php 
        $logo_path = $empresa['logo'] ?? '';
        $real_logo_path = "";
        $show_logo = false;

        if (!empty($logo_path)) {
            if (strpos($logo_path, 'http') === 0) {
                // É um site externo / absolute path web
                $real_logo_path = $logo_path;
                $show_logo = true;
            } else {
                // Remove qualquer "../" que venha do DB/variável anterior
                $logo_limpo = preg_replace('/^(\.\.\/)+/', '', ltrim($logo_path, '/'));
                
                // O HTML vai estar renderizado na Rota: testes_isolados/crm-propostas/gerar-proposta.php
                // Logo o path relativo pro upload da raiz é 2 niveis acima
                $real_logo_path = "../../" . $logo_limpo;
                
                // Mas para checar no S.O se o arquivo físico existe, separamos o querystring (?t=123)
                $file_path_so = explode('?', $logo_limpo)[0];
                $verify_path = __DIR__ . '/../../../' . $file_path_so;
                
                if (file_exists($verify_path)) {
                    $show_logo = true;
                }
            }
        }

        if ($show_logo): ?>
            <img src="<?= $real_logo_path ?>" alt="<?= htmlspecialchars($empresa['nome'] ?? 'Logo') ?>" style="max-height: 80px; object-fit: contain;">
        <?php else: ?>
            <div class="logo-placeholder" style="padding: 10px; border: 1px dashed #ccc; display:inline-block;">[LOGO DA EMPRESA]</div>
        <?php endif; ?>
    </div>
    <div class="header-titulo">
        <h1>Proposta Técnica</h1>
        <div class="header-subtitulo"><?= htmlspecialchars($proposta['tipo_servico']) ?></div>
    </div>
</header>

<div class="header-meta">
    <span><?= htmlspecialchars($proposta['cidade']) ?>, <?= $proposta['data_extenso'] ?></span>
    <span class="numero-proposta">Nº <?= htmlspecialchars($proposta['numero']) ?></span>
</div>
