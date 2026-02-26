<?php if (!defined('SGT_PROPOSTAS')) die('Acesso negado'); 

// Fallbacks para evitar erros
$empresa['nome'] = $empresa['nome'] ?? 'Empresa Proponente';
$empresa['logo'] = $empresa['logo'] ?? '';
$proposta['numero'] = $proposta['numero'] ?? '0000';
$proposta['data_extenso'] = $proposta['data_extenso'] ?? dataPorExtenso();
$proposta['tipo_servico'] = $proposta['tipo_servico'] ?? 'Serviços Topográficos';
?>

<header class="header-proposta">
    <div class="header-logo">
        <?php 
        $logo_path = $empresa['logo'];
        $real_logo_path = "";
        $show_logo = false;

        if (!empty($logo_path)) {
            if (strpos($logo_path, 'http') === 0) {
                $real_logo_path = $logo_path;
                $show_logo = true;
            } else {
                // Tenta validar e ajustar o path local
                $logo_basename = basename($logo_path);
                
                // Paths relativos para o navegador
                $paths_web = [
                    '../../uploads/' . $logo_basename,
                    '../../assets/' . $logo_basename,
                    '../uploads/' . $logo_basename,
                    '../assets/' . $logo_basename
                ];
                
                // Paths físicos para o servidor
                $paths_fisicos = [
                    __DIR__ . '/../../../uploads/' . $logo_basename,
                    __DIR__ . '/../../../assets/' . $logo_basename,
                    __DIR__ . '/../../uploads/' . $logo_basename,
                    __DIR__ . '/../../assets/' . $logo_basename
                ];
                
                foreach ($paths_fisicos as $index => $fisico) {
                    if (file_exists($fisico)) {
                        $real_logo_path = $paths_web[$index];
                        $show_logo = true;
                        break;
                    }
                }
            }
        }

        if ($show_logo): ?>
            <img src="<?= htmlspecialchars($real_logo_path) ?>" 
                 alt="<?= htmlspecialchars($empresa['nome']) ?>" 
                 style="max-height: 80px; max-width: 200px; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
        <?php else: ?>
            <div class="logo-placeholder" style="padding: 12px 20px; border: 2px dashed var(--brand); color: var(--brand); font-weight: bold; border-radius: 8px; display: inline-block; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">
                <?= htmlspecialchars($empresa['nome']) ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="header-titulo" style="text-align: right;">
        <h1 style="margin: 0; color: var(--brand); font-size: 24px; font-weight: 800; text-transform: uppercase; letter-spacing: -0.5px;">Proposta Técnica</h1>
        <div class="header-subtitulo" style="color: #64748b; font-size: 14px; margin-top: 4px; font-weight: 500;">
            <?= htmlspecialchars($proposta['tipo_servico']) ?>
        </div>
    </div>
</header>

<div class="header-meta" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 2px solid #f1f5f9; margin-bottom: 25px; color: #64748b; font-size: 13px;">
    <div class="data-extenso"><?= htmlspecialchars($empresa['cidade']) ?>, <?= htmlspecialchars($proposta['data_extenso']) ?></div>
    <div class="numero-proposta" style="padding: 4px 12px; background: rgba(var(--brand-rgb, 30, 58, 138), 0.1); color: var(--brand); border-radius: 20px; font-weight: 700;">PROPOSTA Nº <?= htmlspecialchars($proposta['numero']) ?></div>
</div>
