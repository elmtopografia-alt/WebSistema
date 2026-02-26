<?php
/**
 * Layout Executivo Premium
 * Características: Tom formal, conservador, ideal para grandes corporações e órgãos públicos
 * Paleta: Azul-marinho profundo + Dourado + Branco
 */

function renderLayoutExecutivo($proposta, $blocos, $config = []) {
    $corPrimaria = '#1e3a5f';      // Azul-marinho profundo
    $corSecundaria = '#c9a227';     // Dourado
    $corTexto = '#2c3e50';          // Cinza-azulado escuro
    $corFundo = '#fafbfc';          // Branco gelo
    $corBorda = '#e2e8f0';          // Cinza claro
    
    $numeroProposta = $proposta['numero_proposta'] ?? 'ELM-2026-000';
    $dataAtual = strftime('%d de %B de %Y', strtotime('today'));
    
    ob_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposta Executiva <?= htmlspecialchars($numeroProposta) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: #525659;
            color: <?= $corTexto ?>;
            line-height: 1.6;
            font-size: 11pt;
        }
        
        .page {
            background: white;
            width: 21cm;
            min-height: 29.7cm;
            margin: 0 auto;
            box-shadow: 0 4px 24px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
        }
        
        /* Header Premium */
        .header-premium {
            background: linear-gradient(135deg, <?= $corPrimaria ?> 0%, #2c5282 100%);
            color: white;
            padding: 0;
            position: relative;
        }
        
        .header-top-bar {
            height: 8px;
            background: <?= $corSecundaria ?>;
        }
        
        .header-content {
            padding: 40px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-area {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo-icon {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.1);
            border: 2px solid <?= $corSecundaria ?>;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        
        .logo-text h1 {
            font-family: 'Crimson Text', serif;
            font-size: 24pt;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        
        .logo-text span {
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 3px;
            opacity: 0.9;
            color: <?= $corSecundaria ?>;
        }
        
        .proposal-badge {
            text-align: right;
        }
        
        .badge-label {
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            opacity: 0.8;
            margin-bottom: 8px;
        }
        
        .badge-number {
            font-family: 'Crimson Text', serif;
            font-size: 20pt;
            font-weight: 700;
            color: <?= $corSecundaria ?>;
            letter-spacing: 1px;
        }
        
        .badge-date {
            font-size: 9pt;
            margin-top: 8px;
            opacity: 0.9;
        }
        
        /* Meta bar */
        .meta-bar {
            background: <?= $corFundo ?>;
            border-bottom: 1px solid <?= $corBorda ?>;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
        }
        
        .meta-item strong {
            color: <?= $corPrimaria ?>;
            display: block;
            font-size: 10pt;
            margin-top: 4px;
            text-transform: none;
        }
        
        /* Conteúdo */
        .content {
            padding: 50px;
        }
        
        /* Cliente Box Premium */
        .cliente-box-premium {
            background: white;
            border: 1px solid <?= $corBorda ?>;
            border-top: 4px solid <?= $corSecundaria ?>;
            padding: 30px;
            margin-bottom: 40px;
            position: relative;
        }
        
        .cliente-box-premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, <?= $corSecundaria ?>, <?= $corPrimaria ?>);
        }
        
        .cliente-label {
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: <?= $corSecundaria ?>;
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .cliente-nome {
            font-family: 'Crimson Text', serif;
            font-size: 18pt;
            color: <?= $corPrimaria ?>;
            margin-bottom: 8px;
        }
        
        .cliente-dados {
            color: #64748b;
            font-size: 10pt;
            line-height: 1.8;
        }
        
        /* Seções */
        .secao-premium {
            margin-bottom: 35px;
        }
        
        .secao-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid <?= $corBorda ?>;
        }
        
        .secao-numero {
            width: 36px;
            height: 36px;
            background: <?= $corPrimaria ?>;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12pt;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .secao-titulo {
            font-family: 'Crimson Text', serif;
            font-size: 16pt;
            color: <?= $corPrimaria ?>;
            font-weight: 600;
        }
        
        .secao-conteudo {
            padding-left: 51px;
            color: <?= $corTexto ?>;
            text-align: justify;
        }
        
        .secao-conteudo p {
            margin-bottom: 12px;
        }
        
        /* Tabela Premium */
        .tabela-premium {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 20px 0;
            font-size: 10pt;
        }
        
        .tabela-premium th {
            background: <?= $corPrimaria ?>;
            color: white;
            padding: 14px 16px;
            text-align: left;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 1px;
        }
        
        .tabela-premium th:first-child {
            border-radius: 6px 0 0 0;
        }
        
        .tabela-premium th:last-child {
            border-radius: 0 6px 0 0;
        }
        
        .tabela-premium td {
            padding: 14px 16px;
            border-bottom: 1px solid <?= $corBorda ?>;
            background: white;
        }
        
        .tabela-premium tr:nth-child(even) td {
            background: <?= $corFundo ?>;
        }
        
        .tabela-premium tr:last-child td:first-child {
            border-radius: 0 0 0 6px;
        }
        
        .tabela-premium tr:last-child td:last-child {
            border-radius: 0 0 6px 0;
        }
        
        /* Box de Valor Executivo */
        .valor-executivo {
            background: linear-gradient(135deg, <?= $corPrimaria ?> 0%, #2c5282 100%);
            color: white;
            padding: 40px;
            border-radius: 8px;
            margin: 40px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .valor-executivo::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(201, 162, 39, 0.1);
            border-radius: 50%;
        }
        
        .valor-label {
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 3px;
            opacity: 0.9;
            margin-bottom: 12px;
            position: relative;
        }
        
        .valor-amount {
            font-family: 'Crimson Text', serif;
            font-size: 36pt;
            font-weight: 700;
            color: <?= $corSecundaria ?>;
            position: relative;
        }
        
        .valor-extenso {
            font-size: 10pt;
            opacity: 0.8;
            margin-top: 8px;
            font-style: italic;
        }
        
        /* Lista Premium */
        .lista-premium {
            list-style: none;
            padding: 0;
        }
        
        .lista-premium li {
            padding: 12px 0 12px 30px;
            position: relative;
            border-bottom: 1px solid <?= $corBorda ?>;
        }
        
        .lista-premium li::before {
            content: '▸';
            position: absolute;
            left: 0;
            color: <?= $corSecundaria ?>;
            font-weight: bold;
        }
        
        /* Footer Premium */
        .footer-premium {
            background: <?= $corFundo ?>;
            border-top: 3px solid <?= $corPrimaria ?>;
            padding: 40px 50px;
            margin-top: 60px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .footer-col h4 {
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: <?= $corSecundaria ?>;
            margin-bottom: 12px;
        }
        
        .footer-col p {
            font-size: 9pt;
            color: #64748b;
            line-height: 1.8;
        }
        
        .assinatura-area {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid <?= $corBorda ?>;
        }
        
        .linha-assinatura {
            width: 300px;
            margin: 0 auto 15px;
            border-bottom: 1px solid <?= $corTexto ?>;
            padding-bottom: 40px;
        }
        
        .assinatura-nome {
            font-family: 'Crimson Text', serif;
            font-size: 12pt;
            color: <?= $corPrimaria ?>;
            font-weight: 600;
        }
        
        .assinatura-cargo {
            font-size: 9pt;
            color: #64748b;
        }
        
        /* Selo de validade */
        .selo-validade {
            position: absolute;
            top: 30px;
            right: 30px;
            background: <?= $corSecundaria ?>;
            color: <?= $corPrimaria ?>;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 8pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transform: rotate(5deg);
        }
        
        @media print {
            body { background: white; }
            .page { box-shadow: none; margin: 0; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="page">
        
        <!-- Header Premium -->
        <header class="header-premium">
            <div class="header-top-bar"></div>
            <div class="header-content">
                <div class="logo-area">
                    <div class="logo-icon">◆</div>
                    <div class="logo-text">
                        <h1>ELM SERVIÇOS</h1>
                        <span>Topografia & Mapeamento</span>
                    </div>
                </div>
                <div class="proposal-badge">
                    <div class="badge-label">Proposta Técnica</div>
                    <div class="badge-number"><?= htmlspecialchars($numeroProposta) ?></div>
                    <div class="badge-date"><?= $dataAtual ?></div>
                </div>
            </div>
        </header>
        
        <!-- Meta Bar -->
        <div class="meta-bar">
            <div class="meta-item">
                Validade
                <strong>15 dias</strong>
            </div>
            <div class="meta-item">
                Prazo de Execução
                <strong>7 a 12 dias úteis</strong>
            </div>
            <div class="meta-item">
                Garantia
                <strong>90 dias</strong>
            </div>
        </div>
        
        <!-- Conteúdo -->
        <div class="content">
            
            <!-- Cliente -->
            <div class="cliente-box-premium">
                <div class="cliente-label">Proposta elaborada para</div>
                <div class="cliente-nome"><?= htmlspecialchars($proposta['cliente_nome'] ?? 'Cliente') ?></div>
                <div class="cliente-dados">
                    <?= htmlspecialchars($proposta['cliente_email'] ?? '') ?><br>
                    <?= htmlspecialchars($proposta['cliente_telefone'] ?? '') ?>
                </div>
            </div>
            
            <!-- Blocos Dinâmicos -->
            <?php 
            $secaoNum = 1;
            foreach ($blocos as $bloco): 
                $tipo = $bloco['tipo'] ?? 'texto';
            ?>
                <?php if ($tipo === 'secao'): ?>
                    <section class="secao-premium">
                        <div class="secao-header">
                            <div class="secao-numero"><?= $secaoNum++ ?></div>
                            <h2 class="secao-titulo"><?= htmlspecialchars($bloco['titulo'] ?? '') ?></h2>
                        </div>
                        <div class="secao-conteudo">
                            <?= $bloco['conteudo'] ?? '' ?>
                        </div>
                    </section>
                    
                <?php elseif ($tipo === 'tabela'): ?>
                    <?= renderTabelaExecutiva($bloco['conteudo'], $corPrimaria, $corFundo, $corBorda) ?>
                    
                <?php elseif ($tipo === 'valor'): ?>
                    <div class="valor-executivo">
                        <div class="valor-label">Investimento Total</div>
                        <div class="valor-amount"><?= $bloco['conteudo'] ?? 'R$ 0,00' ?></div>
                        <div class="valor-extenso"><?= $bloco['extenso'] ?? '' ?></div>
                    </div>
                    
                <?php elseif ($tipo === 'lista'): ?>
                    <ul class="lista-premium">
                        <?php foreach (($bloco['itens'] ?? []) as $item): ?>
                            <li><?= htmlspecialchars($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    
                <?php else: ?>
                    <div class="secao-conteudo" style="margin-bottom: 20px;">
                        <?= $bloco['conteudo'] ?? '' ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            
        </div>
        
        <!-- Footer Premium -->
        <footer class="footer-premium">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>Endereço</h4>
                    <p>Belo Horizonte, MG<br>CEP: 30.000-000</p>
                </div>
                <div class="footer-col">
                    <h4>Contato</h4>
                    <p>(31) 3625-4769<br>contato@geometropole.com.br</p>
                </div>
                <div class="footer-col">
                    <h4>Documento</h4>
                    <p>CNPJ: 14.059.118/0001-08<br>CREA-MG: 123456</p>
                </div>
            </div>
            <div class="assinatura-area">
                <div class="linha-assinatura"></div>
                <div class="assinatura-nome">ELM Serviços Topográficos Ltda.</div>
                <div class="assinatura-cargo">Responsável Técnico - Eng. Cartógrafo</div>
            </div>
        </footer>
        
    </div>
</body>
</html>
<?php
    return ob_get_clean();
}

function renderTabelaExecutiva($dados, $corPrimaria, $corFundo, $corBorda) {
    if (is_string($dados)) $dados = json_decode($dados, true);
    if (empty($dados['linhas'])) return '';
    
    $html = '<table class="tabela-premium"><thead><tr>';
    foreach ($dados['cabecalhos'] ?? [] as $cab) {
        $html .= '<th>' . htmlspecialchars($cab) . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    
    foreach ($dados['linhas'] as $linha) {
        $html .= '<tr>';
        foreach ($linha as $celula) {
            $html .= '<td>' . htmlspecialchars($celula) . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    return $html;
}
