<?php
/**
 * Layout Criativo Premium
 * Características: Impacto visual, elementos gráficos, tipografia ousada, experiência memorável
 * Paleta: Roxo profundo + Coral + Amarelo-dourado + Preto
 */

function renderLayoutCriativo($proposta, $blocos, $config = []) {
    $corPrimaria = '#581c87';       // Roxo profundo
    $corSecundaria = '#f97316';     // Coral/laranja
    $corAccent = '#eab308';         // Amarelo/dourado
    $corDark = '#0f0a1a';           // Preto-roxo
    $corFundo = '#faf5ff';          // Lavanda claro
    $corTexto = '#1f1b2e';          // Quase preto com tom roxo
    
    $numeroProposta = $proposta['numero_proposta'] ?? 'ELM-2026-000';
    $dataAtual = strftime('%d de %B de %Y', strtotime('today'));
    
    ob_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposta Criativa <?= htmlspecialchars($numeroProposta) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Space Grotesk', sans-serif;
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
        
        /* Background Shapes */
        .bg-shape {
            position: absolute;
            z-index: 0;
            opacity: 0.03;
        }
        
        .shape-1 {
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: <?= $corPrimaria ?>;
            border-radius: 50%;
        }
        
        .shape-2 {
            bottom: 200px;
            left: -150px;
            width: 300px;
            height: 300px;
            background: <?= $corSecundaria ?>;
            transform: rotate(45deg);
        }
        
        /* Header Criativo */
        .header-criativo {
            position: relative;
            background: <?= $corDark ?>;
            color: white;
            padding: 60px 50px;
            overflow: hidden;
        }
        
        .header-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.1;
            background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0);
            background-size: 20px 20px;
        }
        
        .header-content {
            position: relative;
            z-index: 1;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
        }
        
        .logo-criativo {
            font-family: 'Playfair Display', serif;
            font-size: 28pt;
            font-weight: 600;
            letter-spacing: -1px;
        }
        
        .logo-criativo span {
            color: <?= $corSecundaria ?>;
        }
        
        .tag-proposta {
            background: linear-gradient(135deg, <?= $corSecundaria ?>, <?= $corAccent ?>);
            color: <?= $corDark ?>;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 9pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .header-main {
            max-width: 70%;
        }
        
        .header-pretitle {
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 3px;
            opacity: 0.7;
            margin-bottom: 16px;
        }
        
        .header-title {
            font-family: 'Playfair Display', serif;
            font-size: 42pt;
            line-height: 1.1;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .header-title em {
            color: <?= $corSecundaria ?>;
            font-style: italic;
        }
        
        .header-number {
            font-size: 14pt;
            color: <?= $corAccent ?>;
            font-weight: 300;
            letter-spacing: 2px;
        }
        
        /* Accent Bar */
        .accent-bar {
            height: 8px;
            background: linear-gradient(90deg, <?= $corSecundaria ?>, <?= $corAccent ?>, <?= $corPrimaria ?>);
        }
        
        /* Content */
        .content-criativo {
            padding: 50px;
            position: relative;
            z-index: 1;
        }
        
        /* Cliente Card Criativo */
        .cliente-card-criativo {
            background: <?= $corFundo ?>;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .cliente-card-criativo::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, <?= $corPrimaria ?>, <?= $corSecundaria ?>);
        }
        
        .cliente-label-criativo {
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: <?= $corPrimaria ?>;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .cliente-nome-criativo {
            font-family: 'Playfair Display', serif;
            font-size: 24pt;
            color: <?= $corDark ?>;
            margin-bottom: 8px;
        }
        
        .cliente-meta-criativo {
            display: flex;
            gap: 20px;
            color: #6b7280;
            font-size: 10pt;
        }
        
        .cliente-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .dot {
            width: 6px;
            height: 6px;
            background: <?= $corSecundaria ?>;
            border-radius: 50%;
        }
        
        /* Seções Criativas */
        .secao-criativa {
            margin-bottom: 40px;
            position: relative;
        }
        
        .secao-header-criativo {
            display: flex;
            align-items: baseline;
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .secao-numero-criativo {
            font-family: 'Playfair Display', serif;
            font-size: 48pt;
            color: <?= $corPrimaria ?>;
            opacity: 0.2;
            line-height: 1;
            font-weight: 700;
        }
        
        .secao-titulo-criativo {
            font-size: 20pt;
            color: <?= $corDark ?>;
            font-weight: 600;
            position: relative;
        }
        
        .secao-titulo-criativo::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, <?= $corSecundaria ?>, <?= $corAccent ?>);
        }
        
        .secao-conteudo-criativo {
            padding-left: 64px;
            color: <?= $corTexto ?>;
        }
        
        .secao-conteudo-criativo p {
            margin-bottom: 16px;
            font-size: 11pt;
            line-height: 1.8;
        }
        
        /* Highlight Box */
        .highlight-box {
            background: <?= $corDark ?>;
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }
        
        .highlight-box::before {
            content: '"';
            position: absolute;
            top: -20px;
            right: 20px;
            font-family: 'Playfair Display', serif;
            font-size: 120pt;
            opacity: 0.1;
            color: <?= $corSecundaria ?>;
        }
        
        .highlight-box p {
            font-family: 'Playfair Display', serif;
            font-size: 14pt;
            font-style: italic;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }
        
        /* Tabela Criativa */
        .tabela-criativa {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 24px 0;
            font-size: 10pt;
        }
        
        .tabela-criativa th {
            background: <?= $corPrimaria ?>;
            color: white;
            padding: 16px;
            text-align: left;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 1px;
        }
        
        .tabela-criativa th:first-child {
            border-radius: 12px 0 0 0;
        }
        
        .tabela-criativa th:last-child {
            border-radius: 0 12px 0 0;
        }
        
        .tabela-criativa td {
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
            background: white;
        }
        
        .tabela-criativa tr:last-child td:first-child {
            border-radius: 0 0 0 12px;
        }
        
        .tabela-criativa tr:last-child td:last-child {
            border-radius: 0 0 12px 0;
        }
        
        .tabela-criativa tr:nth-child(even) td {
            background: <?= $corFundo ?>;
        }
        
        /* Valor Criativo - Big Impact */
        .valor-criativo {
            background: linear-gradient(135deg, <?= $corPrimaria ?> 0%, <?= $corDark ?> 100%);
            color: white;
            padding: 50px;
            border-radius: 20px;
            margin: 40px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .valor-criativo-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,0.03) 10px,
                rgba(255,255,255,0.03) 20px
            );
            animation: slide 20s linear infinite;
        }
        
        @keyframes slide {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        
        .valor-label-criativo {
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 4px;
            opacity: 0.8;
            margin-bottom: 16px;
            position: relative;
        }
        
        .valor-amount-criativo {
            font-family: 'Playfair Display', serif;
            font-size: 48pt;
            font-weight: 700;
            background: linear-gradient(135deg, <?= $corAccent ?>, <?= $corSecundaria ?>);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
        }
        
        .valor-extenso-criativo {
            font-size: 11pt;
            opacity: 0.8;
            margin-top: 12px;
            font-style: italic;
        }
        
        /* Lista Criativa */
        .lista-criativa {
            list-style: none;
            padding: 0;
        }
        
        .lista-criativa li {
            padding: 16px 0 16px 40px;
            position: relative;
            border-bottom: 1px dashed #e5e7eb;
            font-size: 11pt;
        }
        
        .lista-criativa li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: <?= $corSecundaria ?>;
            font-weight: bold;
            font-size: 16pt;
            line-height: 1;
        }
        
        /* Grid de Features */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        
        .feature-item {
            text-align: center;
            padding: 24px;
            background: <?= $corFundo ?>;
            border-radius: 12px;
            transition: transform 0.3s;
        }
        
        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, <?= $corPrimaria ?>, <?= $corSecundaria ?>);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: white;
            font-size: 20px;
        }
        
        .feature-title {
            font-weight: 600;
            color: <?= $corDark ?>;
            margin-bottom: 8px;
        }
        
        .feature-desc {
            font-size: 9pt;
            color: #6b7280;
        }
        
        /* Footer Criativo */
        .footer-criativo {
            background: <?= $corDark ?>;
            color: white;
            padding: 50px;
            position: relative;
            overflow: hidden;
        }
        
        .footer-criativo::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, transparent 0%, rgba(88,28,135,0.3) 100%);
        }
        
        .footer-content {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }
        
        .footer-brand {
            font-family: 'Playfair Display', serif;
            font-size: 24pt;
            margin-bottom: 16px;
        }
        
        .footer-brand span {
            color: <?= $corSecundaria ?>;
        }
        
        .footer-text {
            opacity: 0.8;
            line-height: 1.8;
            font-size: 10pt;
        }
        
        .footer-assinatura {
            text-align: center;
            padding: 30px;
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 12px;
        }
        
        .assinatura-linha {
            width: 200px;
            margin: 0 auto 16px;
            border-bottom: 1px solid rgba(255,255,255,0.3);
            padding-bottom: 40px;
        }
        
        .footer-contato {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            font-size: 9pt;
            opacity: 0.6;
        }
        
        @media print {
            body { background: white; }
            .page { box-shadow: none; width: 100%; }
            .valor-criativo-bg { animation: none; }
        }
    </style>
</head>
<body>
    <div class="page">
        
        <!-- Background Shapes -->
        <div class="bg-shape shape-1"></div>
        <div class="bg-shape shape-2"></div>
        
        <!-- Header -->
        <header class="header-criativo">
            <div class="header-pattern"></div>
            <div class="header-content">
                <div class="header-top">
                    <div class="logo-criativo">ELM<span>.</span></div>
                    <span class="tag-proposta">Proposta Premium</span>
                </div>
                <div class="header-main">
                    <div class="header-pretitle">Proposta Técnica de Serviços</div>
                    <h1 class="header-title">Topografia & <em>Mapeamento</em> Aéreo</h1>
                    <div class="header-number"><?= htmlspecialchars($numeroProposta) ?></div>
                </div>
            </div>
        </header>
        
        <div class="accent-bar"></div>
        
        <!-- Content -->
        <div class="content-criativo">
            
            <!-- Cliente -->
            <div class="cliente-card-criativo">
                <div class="cliente-label-criativo">Preparado exclusivamente para</div>
                <div class="cliente-nome-criativo"><?= htmlspecialchars($proposta['cliente_nome'] ?? 'Cliente') ?></div>
                <div class="cliente-meta-criativo">
                    <span class="cliente-meta-item"><span class="dot"></span> <?= $dataAtual ?></span>
                    <span class="cliente-meta-item"><span class="dot"></span> Validade: 15 dias</span>
                    <span class="cliente-meta-item"><span class="dot"></span> Belo Horizonte, MG</span>
                </div>
            </div>
            
            <!-- Blocos -->
            <?php 
            $secaoNum = 1;
            foreach ($blocos as $bloco): 
                $tipo = $bloco['tipo'] ?? 'texto';
            ?>
                
                <?php if ($tipo === 'secao'): ?>
                    <section class="secao-criativa">
                        <div class="secao-header-criativo">
                            <span class="secao-numero-criativo">0<?= $secaoNum++ ?></span>
                            <h2 class="secao-titulo-criativo"><?= htmlspecialchars($bloco['titulo'] ?? '') ?></h2>
                        </div>
                        <div class="secao-conteudo-criativo">
                            <?= $bloco['conteudo'] ?? '' ?>
                        </div>
                    </section>
                    
                <?php elseif ($tipo === 'highlight'): ?>
                    <div class="highlight-box">
                        <p><?= $bloco['conteudo'] ?? '' ?></p>
                    </div>
                    
                <?php elseif ($tipo === 'features'): ?>
                    <div class="features-grid">
                        <?php foreach (($bloco['itens'] ?? []) as $item): ?>
                            <div class="feature-item">
                                <div class="feature-icon">★</div>
                                <div class="feature-title"><?= htmlspecialchars($item['titulo'] ?? '') ?></div>
                                <div class="feature-desc"><?= htmlspecialchars($item['desc'] ?? '') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                <?php elseif ($tipo === 'tabela'): ?>
                    <?= renderTabelaCriativa($bloco['conteudo']) ?>
                    
                <?php elseif ($tipo === 'valor'): ?>
                    <div class="valor-criativo">
                        <div class="valor-criativo-bg"></div>
                        <div class="valor-label-criativo">Investimento</div>
                        <div class="valor-amount-criativo"><?= $bloco['conteudo'] ?? 'R$ 0,00' ?></div>
                        <div class="valor-extenso-criativo"><?= $bloco['extenso'] ?? '' ?></div>
                    </div>
                    
                <?php elseif ($tipo === 'lista'): ?>
                    <ul class="lista-criativa">
                        <?php foreach (($bloco['itens'] ?? []) as $item): ?>
                            <li><?= htmlspecialchars($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    
                <?php else: ?>
                    <div class="secao-conteudo-criativo" style="margin-bottom: 24px;">
                        <?= $bloco['conteudo'] ?? '' ?>
                    </div>
                <?php endif; ?>
                
            <?php endforeach; ?>
            
        </div>
        
        <!-- Footer -->
        <footer class="footer-criativo">
            <div class="footer-content">
                <div>
                    <div class="footer-brand">ELM<span>.</span></div>
                    <p class="footer-text">
                        Transformando dados geoespaciais em insights precisos.<br>
                        Topografia de precisão com tecnologia de ponta.
                    </p>
                </div>
                <div class="footer-assinatura">
                    <div class="assinatura-linha"></div>
                    <div style="font-weight: 600;">ELM Serviços Topográficos Ltda.</div>
                    <div style="font-size: 9pt; opacity: 0.7; margin-top: 4px;">CNPJ: 14.059.118/0001-08</div>
                </div>
            </div>
            <div class="footer-contato">
                <span>(31) 3625-4769</span>
                <span>contato@geometropole.com.br</span>
                <span>Belo Horizonte, MG</span>
            </div>
        </footer>
        
    </div>
</body>
</html>
<?php
    return ob_get_clean();
}

function renderTabelaCriativa($dados) {
    if (is_string($dados)) $dados = json_decode($dados, true);
    if (empty($dados['linhas'])) return '';
    
    $html = '<table class="tabela-criativa"><thead><tr>';
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
