<?php
/**
 * Layout Técnico Premium
 * Características: Foco em dados, grids, planilhas, aspecto de relatório técnico
 * Paleta: Cinza grafite + Laranja técnica + Branco puro
 */

function renderLayoutTecnico($proposta, $blocos, $config = []) {
    $corPrimaria = '#374151';       // Grafite escuro
    $corSecundaria = '#ea580c';     // Laranja técnico
    $corAccent = '#f97316';         // Laranja claro
    $corFundo = '#f9fafb';          // Cinza muito claro
    $corGrid = '#e5e7eb';           // Cinza grid
    $corTexto = '#1f2937';          // Quase preto
    
    $numeroProposta = $proposta['numero_proposta'] ?? 'ELM-2026-000';
    $dataAtual = date('d/m/Y');
    
    ob_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Técnico <?= htmlspecialchars($numeroProposta) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #525659;
            color: <?= $corTexto ?>;
            line-height: 1.5;
            font-size: 10pt;
        }
        
        .page {
            background: white;
            width: 21cm;
            min-height: 29.7cm;
            margin: 0 auto;
            box-shadow: 0 4px 24px rgba(0,0,0,0.15);
            display: grid;
            grid-template-rows: auto 1fr auto;
        }
        
        /* Header Técnico */
        .header-tecnico {
            background: <?= $corPrimaria ?>;
            color: white;
            padding: 25px 40px;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 30px;
            align-items: center;
            border-bottom: 4px solid <?= $corSecundaria ?>;
        }
        
        .doc-id {
            font-family: 'JetBrains Mono', monospace;
            background: rgba(255,255,255,0.1);
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 9pt;
        }
        
        .doc-id-label {
            font-size: 7pt;
            text-transform: uppercase;
            opacity: 0.7;
            margin-bottom: 2px;
        }
        
        .doc-id-value {
            font-weight: 600;
            letter-spacing: 1px;
        }
        
        .header-titulo h1 {
            font-size: 14pt;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .header-titulo span {
            font-size: 9pt;
            opacity: 0.8;
            display: block;
            margin-top: 4px;
        }
        
        .header-meta {
            text-align: right;
        }
        
        .header-meta-data {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11pt;
            color: <?= $corSecundaria ?>;
            font-weight: 600;
        }
        
        /* Grid System */
        .grid-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 0;
        }
        
        /* Sidebar Técnica */
        .sidebar-tecnica {
            background: <?= $corFundo ?>;
            border-right: 1px solid <?= $corGrid ?>;
            padding: 30px;
        }
        
        .info-card {
            background: white;
            border: 1px solid <?= $corGrid ?>;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 20px;
        }
        
        .info-card-header {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: <?= $corSecundaria ?>;
            font-weight: 600;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid <?= $corGrid ?>;
        }
        
        .info-card-body {
            font-size: 9pt;
            line-height: 1.6;
        }
        
        .info-card-body strong {
            color: <?= $corPrimaria ?>;
            display: block;
            margin-bottom: 4px;
            font-size: 10pt;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            background: <?= $corSecundaria ?>;
            color: white;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Main Content */
        .main-content {
            padding: 40px;
        }
        
        /* Seção Técnica */
        .secao-tecnica {
            margin-bottom: 30px;
        }
        
        .secao-header-tecnica {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid <?= $corGrid ?>;
        }
        
        .secao-tag {
            font-family: 'JetBrains Mono', monospace;
            background: <?= $corSecundaria ?>;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: 600;
        }
        
        .secao-titulo-tecnica {
            font-size: 13pt;
            font-weight: 600;
            color: <?= $corPrimaria ?>;
        }
        
        .secao-conteudo-tecnica {
            color: <?= $corTexto ?>;
            line-height: 1.7;
        }
        
        .secao-conteudo-tecnica p {
            margin-bottom: 12px;
        }
        
        /* Tabela Técnica (Excel-like) */
        .tabela-tecnica-container {
            overflow-x: auto;
            margin: 20px 0;
            border: 1px solid <?= $corGrid ?>;
            border-radius: 6px;
        }
        
        .tabela-tecnica {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            font-family: 'JetBrains Mono', monospace;
        }
        
        .tabela-tecnica th {
            background: <?= $corPrimaria ?>;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-weight: 500;
            border-right: 1px solid rgba(255,255,255,0.1);
        }
        
        .tabela-tecnica th:last-child {
            border-right: none;
        }
        
        .tabela-tecnica td {
            padding: 10px 12px;
            border-bottom: 1px solid <?= $corGrid ?>;
            border-right: 1px solid <?= $corGrid ?>;
        }
        
        .tabela-tecnica tr:nth-child(even) {
            background: <?= $corFundo ?>;
        }
        
        .tabela-tecnica tr:hover {
            background: #fff7ed;
        }
        
        .tabela-tecnica .numero {
            text-align: right;
            color: <?= $corSecundaria ?>;
            font-weight: 500;
        }
        
        .tabela-tecnica .total {
            background: <?= $corPrimaria ?> !important;
            color: white;
            font-weight: 600;
        }
        
        /* Specs Grid */
        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin: 20px 0;
        }
        
        .spec-item {
            background: <?= $corFundo ?>;
            border-left: 3px solid <?= $corSecundaria ?>;
            padding: 16px;
            border-radius: 0 6px 6px 0;
        }
        
        .spec-label {
            font-size: 8pt;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        
        .spec-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12pt;
            color: <?= $corPrimaria ?>;
            font-weight: 600;
        }
        
        /* Box de Valor Técnico */
        .valor-tecnico {
            background: white;
            border: 2px solid <?= $corSecundaria ?>;
            border-radius: 8px;
            padding: 30px;
            margin: 30px 0;
            position: relative;
        }
        
        .valor-tecnico::before {
            content: 'VALOR ORÇADO';
            position: absolute;
            top: -10px;
            left: 20px;
            background: <?= $corSecundaria ?>;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: 600;
            letter-spacing: 1px;
        }
        
        .valor-tecnico-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 30px;
            align-items: center;
        }
        
        .valor-tecnico-info h4 {
            font-size: 10pt;
            color: <?= $corPrimaria ?>;
            margin-bottom: 8px;
        }
        
        .valor-tecnico-info p {
            font-size: 9pt;
            color: #6b7280;
            margin: 0;
        }
        
        .valor-tecnico-amount {
            text-align: right;
        }
        
        .valor-tecnico-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: 24pt;
            color: <?= $corSecundaria ?>;
            font-weight: 700;
        }
        
        .valor-tecnico-prazo {
            font-size: 9pt;
            color: #6b7280;
            margin-top: 4px;
        }
        
        /* Lista Técnica */
        .lista-tecnica {
            list-style: none;
            padding: 0;
        }
        
        .lista-tecnica li {
            padding: 10px 0 10px 28px;
            position: relative;
            border-bottom: 1px solid <?= $corGrid ?>;
        }
        
        .lista-tecnica li::before {
            content: '□';
            position: absolute;
            left: 0;
            color: <?= $corSecundaria ?>;
            font-weight: bold;
        }
        
        .lista-tecnica li.checked::before {
            content: '■';
        }
        
        /* Footer Técnico */
        .footer-tecnico {
            background: <?= $corPrimaria ?>;
            color: white;
            padding: 20px 40px;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 30px;
            align-items: center;
            font-size: 8pt;
        }
        
        .footer-tecnico-col {
            opacity: 0.8;
        }
        
        .footer-tecnico-col.center {
            text-align: center;
            border-left: 1px solid rgba(255,255,255,0.2);
            border-right: 1px solid rgba(255,255,255,0.2);
            padding: 0 30px;
        }
        
        .footer-tecnico strong {
            color: <?= $corAccent ?>;
            display: block;
            margin-bottom: 4px;
        }
        
        /* Annotations */
        .annotation {
            background: #fff7ed;
            border-left: 3px solid <?= $corSecundaria ?>;
            padding: 12px 16px;
            margin: 16px 0;
            font-size: 9pt;
            color: #7c2d12;
        }
        
        .annotation::before {
            content: 'NOTA: ';
            font-weight: 600;
            color: <?= $corSecundaria ?>;
        }
        
        @media print {
            body { background: white; }
            .page { box-shadow: none; width: 100%; }
            .grid-container { display: block; }
            .sidebar-tecnica { display: none; }
        }
    </style>
</head>
<body>
    <div class="page">
        
        <!-- Header -->
        <header class="header-tecnico">
            <div class="doc-id">
                <div class="doc-id-label">Documento</div>
                <div class="doc-id-value"><?= htmlspecialchars($numeroProposta) ?></div>
            </div>
            <div class="header-titulo">
                <h1>RELATÓRIO TÉCNICO DE SERVIÇOS</h1>
                <span>Topografia e Mapeamento Aéreo • Aerofotogrametria com Drones</span>
            </div>
            <div class="header-meta">
                <div class="header-meta-data"><?= $dataAtual ?></div>
                <div style="font-size: 8pt; opacity: 0.7; margin-top: 4px;">Rev. 01</div>
            </div>
        </header>
        
        <!-- Grid Container -->
        <div class="grid-container">
            
            <!-- Sidebar -->
            <aside class="sidebar-tecnica">
                <div class="info-card">
                    <div class="info-card-header">Status</div>
                    <div class="info-card-body">
                        <span class="status-badge">Orçamento</span>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-card-header">Cliente</div>
                    <div class="info-card-body">
                        <strong><?= htmlspecialchars($proposta['cliente_nome'] ?? 'N/A') ?></strong>
                        <?= htmlspecialchars($proposta['cliente_email'] ?? '') ?>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-card-header">Obra / Local</div>
                    <div class="info-card-body">
                        <strong><?= htmlspecialchars($proposta['obra_endereco'] ?? 'Endereço não informado') ?></strong>
                        <?= htmlspecialchars($proposta['obra_cidade'] ?? '') ?> - <?= htmlspecialchars($proposta['obra_estado'] ?? '') ?>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-card-header">Especificações</div>
                    <div class="info-card-body" style="font-family: 'JetBrains Mono', monospace; font-size: 8pt;">
                        Área: <?= htmlspecialchars($proposta['obra_area'] ?? '0') ?> ha<br>
                        Equidistância: 1m<br>
                        Escala: 1:500<br>
                        EPSG: 31983
                    </div>
                </div>
            </aside>
            
            <!-- Main Content -->
            <main class="main-content">
                
                <?php 
                $secaoNum = 1;
                foreach ($blocos as $bloco): 
                    $tipo = $bloco['tipo'] ?? 'texto';
                ?>
                    
                    <?php if ($tipo === 'secao'): ?>
                        <section class="secao-tecnica">
                            <div class="secao-header-tecnica">
                                <span class="secao-tag">SEC-0<?= $secaoNum++ ?></span>
                                <h2 class="secao-titulo-tecnica"><?= htmlspecialchars($bloco['titulo'] ?? '') ?></h2>
                            </div>
                            <div class="secao-conteudo-tecnica">
                                <?= $bloco['conteudo'] ?? '' ?>
                            </div>
                        </section>
                        
                    <?php elseif ($tipo === 'specs'): ?>
                        <div class="specs-grid">
                            <?php foreach (($bloco['itens'] ?? []) as $spec): ?>
                                <div class="spec-item">
                                    <div class="spec-label"><?= htmlspecialchars($spec['label'] ?? '') ?></div>
                                    <div class="spec-value"><?= htmlspecialchars($spec['value'] ?? '') ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                    <?php elseif ($tipo === 'tabela'): ?>
                        <div class="tabela-tecnica-container">
                            <?= renderTabelaTecnica($bloco['conteudo']) ?>
                        </div>
                        
                    <?php elseif ($tipo === 'valor'): ?>
                        <div class="valor-tecnico">
                            <div class="valor-tecnico-grid">
                                <div class="valor-tecnico-info">
                                    <h4>Condições Comerciais</h4>
                                    <p>Pagamento em 2x (30% entrada + 70% na entrega)<br>
                                    Validade: 15 dias corridos</p>
                                </div>
                                <div class="valor-tecnico-amount">
                                    <div class="valor-tecnico-number"><?= $bloco['conteudo'] ?? 'R$ 0,00' ?></div>
                                    <div class="valor-tecnico-prazo">Prazo: 7-12 dias úteis</div>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($tipo === 'annotation'): ?>
                        <div class="annotation">
                            <?= $bloco['conteudo'] ?? '' ?>
                        </div>
                        
                    <?php else: ?>
                        <div class="secao-conteudo-tecnica" style="margin-bottom: 20px;">
                            <?= $bloco['conteudo'] ?? '' ?>
                        </div>
                    <?php endif; ?>
                    
                <?php endforeach; ?>
                
            </main>
        </div>
        
        <!-- Footer -->
        <footer class="footer-tecnico">
            <div class="footer-tecnico-col">
                <strong>ELM SERVIÇOS TOPOGRÁFICOS</strong>
                CNPJ: 14.059.118/0001-08
            </div>
            <div class="footer-tecnico-col center">
                <strong>CREA-MG</strong>
                123456 • Responsável Técnico
            </div>
            <div class="footer-tecnico-col" style="text-align: right;">
                <strong>CONTATO</strong>
                (31) 3625-4769
            </div>
        </footer>
        
    </div>
</body>
</html>
<?php
    return ob_get_clean();
}

function renderTabelaTecnica($dados) {
    if (is_string($dados)) $dados = json_decode($dados, true);
    if (empty($dados['linhas'])) return '';
    
    $html = '<table class="tabela-tecnica"><thead><tr>';
    foreach ($dados['cabecalhos'] ?? [] as $cab) {
        $html .= '<th>' . htmlspecialchars($cab) . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    
    $totalRows = count($dados['linhas']);
    foreach ($dados['linhas'] as $i => $linha) {
        $isLast = ($i === $totalRows - 1);
        $class = $isLast ? 'total' : '';
        $html .= '<tr class="' . $class . '">';
        foreach ($linha as $j => $celula) {
            $align = is_numeric(str_replace(['.', ','], '', $celula)) ? 'numero' : '';
            $html .= '<td class="' . $align . '">' . htmlspecialchars($celula) . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    return $html;
}
