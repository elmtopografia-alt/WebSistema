template_php.php<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Proposta <?= htmlspecialchars($numero_proposta ?? 'PROP-2026-XXX') ?> - <?= htmlspecialchars($cliente_nome ?? 'Cliente') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    
    <style>
        /* === VARIÁVEIS DE MARCA === */
        :root {
            --brand-primary: #b45f06;        /* Terracota/Laranja queimado */
            --brand-secondary: #8b4513;      /* Marrom siena */
            --brand-light: #d4a574;        /* Bege dourado */
            --brand-bg: #fdf8f3;           /* Fundo creme */
            
            --text-dark: #1a1a2e;          /* Quase preto */
            --text-body: #4a4a68;          /* Cinza azulado */
            --text-muted: #6b7280;         /* Cinza médio */
            
            --border-elegant: #e5e7eb;     /* Borda sutil */
            --border-light: #f3f4f6;       /* Borda muito sutil */
            --surface-warm: #faf7f2;       /* Superfície quente */
            
            --accent-green: #059669;       /* Verde pagamento */
            --accent-blue: #2563eb;        /* Azul info */
            
            --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        /* === BASE === */
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f5;           /* Cinza claro fora da página */
            margin: 0;
            padding: 40px 20px;
            font-size: 10.5pt;
            line-height: 1.6;
            color: var(--text-body);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* === PÁGINA A4 === */
        .page {
            background: white;
            width: 210mm;                  /* A4 exato */
            min-height: 297mm;             /* A4 exato */
            padding: 25mm 20mm;            /* Margens elegantes */
            margin: 0 auto;
            box-shadow: var(--shadow-medium);
            position: relative;
            box-sizing: border-box;
        }

        /* === PRINT === */
        @media print {
            body { 
                background: none; 
                margin: 0; 
                padding: 0; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
            .page { 
                width: 100% !important; 
                min-height: auto;
                box-shadow: none; 
                margin: 0; 
                padding: 20mm; 
                border: none;
            }
            .no-print { display: none !important; }
            
            /* Controle de quebra de página */
            .bloco-secao, 
            .info-card,
            table, 
            tr, 
            .invest-box { 
                page-break-inside: avoid; 
                break-inside: avoid; 
            }
            
            h2, h3 { 
                page-break-after: avoid; 
                break-after: avoid; 
            }
        }

        /* === TIPOGRAFIA ELEGANTE === */
        h1, h2, h3, h4 {
            font-family: 'Playfair Display', 'Georgia', serif;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -0.01em;
        }

        h1 {
            font-size: 24pt;
            color: var(--brand-primary);
            margin-bottom: 8px;
            line-height: 1.2;
        }

        h2 {
            font-family: 'Inter', sans-serif;    /* Inter para subtítulos técnicos */
            font-size: 11pt;
            font-weight: 600;
            color: var(--brand-primary);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 24px;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid var(--brand-light);
        }

        h3 {
            font-size: 12pt;
            color: var(--text-dark);
            margin-top: 16px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        p {
            margin-bottom: 10px;
            text-align: justify;
            hyphens: auto;
        }

        strong { 
            color: var(--text-dark); 
            font-weight: 600; 
        }

        /* === HEADER ELEGANTE === */
        .header-elegante {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding-bottom: 20px;
            border-bottom: 3px solid var(--brand-primary);
            margin-bottom: 20px;
        }

        .logo-area {
            flex: 0 0 auto;
        }

        .logo-area img {
            max-height: 70px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .titulo-area {
            text-align: right;
        }

        .titulo-area h1 {
            font-family: 'Playfair Display', serif;
            font-size: 22pt;
            color: var(--brand-primary);
            margin: 0;
            font-weight: 700;
        }

        .titulo-area .subtitulo {
            font-family: 'Inter', sans-serif;
            font-size: 10pt;
            color: var(--text-muted);
            font-weight: 400;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* === META INFO (Data + Número) === */
        .meta-linha {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 10pt;
            color: var(--text-muted);
        }

        .meta-linha .cidade-data {
            font-weight: 400;
            letter-spacing: 0.02em;
        }

        .meta-linha .numero-proposta {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            color: var(--brand-primary);
            font-size: 11pt;
            letter-spacing: 0.05em;
            padding: 4px 12px;
            background: var(--brand-bg);
            border-radius: 4px;
            border: 1px solid var(--brand-light);
        }

        /* === CARDS DE INFORMAÇÃO === */
        .cards-duplo {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .card-info {
            background: var(--surface-warm);
            border: 1px solid var(--border-elegant);
            border-radius: 8px;
            padding: 16px 18px;
            border-left: 4px solid var(--brand-primary);
        }

        .card-info.destaque-verde {
            border-left-color: var(--accent-green);
            background: linear-gradient(135deg, #f0fdf4 0%, var(--surface-warm) 100%);
        }

        .card-info.destaque-azul {
            border-left-color: var(--accent-blue);
        }

        .card-titulo {
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--brand-primary);
            margin-bottom: 10px;
            font-family: 'Inter', sans-serif;
        }

        .card-info.destaque-verde .card-titulo {
            color: var(--accent-green);
        }

        .card-linha {
            font-size: 9.5pt;
            margin-bottom: 4px;
            color: var(--text-body);
            line-height: 1.5;
        }

        .card-linha .rotulo {
            font-weight: 600;
            color: var(--text-dark);
        }

        .card-linha .bullet {
            color: var(--brand-primary);
            margin-right: 6px;
        }

        /* === SEÇÕES DE CONTEÚDO === */
        .secao-padrao {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .secao-padrao p {
            font-size: 10.5pt;
            line-height: 1.7;
            color: var(--text-body);
        }

        /* === TABELAS ELEGANTES === */
        .tabela-elegante {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 16px 0;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-elegant);
            font-size: 9.5pt;
        }

        .tabela-elegante th {
            background: var(--brand-bg);
            color: var(--brand-primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.06em;
            padding: 12px 14px;
            text-align: left;
            border-bottom: 2px solid var(--brand-light);
            font-family: 'Inter', sans-serif;
        }

        .tabela-elegante td {
            padding: 10px 14px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: top;
        }

        .tabela-elegante tr:last-child td {
            border-bottom: none;
        }

        .tabela-elegante tr:nth-child(even) {
            background: var(--surface-warm);
        }

        /* Linha de total */
        .tabela-elegante tr.linha-total td {
            border-top: 2px solid var(--brand-primary);
            font-weight: 700;
            color: var(--brand-primary);
            background: var(--brand-bg);
        }

        .texto-centralizado { text-align: center; }
        .texto-direita { text-align: right; }

        /* === LISTA DE EQUIPAMENTOS === */
        .lista-equipamentos {
            list-style: none;
            padding: 0;
            margin: 12px 0;
        }

        .lista-equipamentos li {
            position: relative;
            padding: 10px 14px 10px 20px;
            margin-bottom: 8px;
            background: var(--surface-warm);
            border-radius: 0 6px 6px 0;
            border-left: 3px solid var(--brand-light);
            font-size: 10pt;
        }

        .lista-equipamentos li::before {
            content: "▸";
            position: absolute;
            left: 8px;
            color: var(--brand-primary);
            font-weight: bold;
        }

        .lista-equipamentos li strong {
            color: var(--text-dark);
            font-weight: 600;
        }

        /* === BOX DE INVESTIMENTO (Destaque) === */
        .box-investimento {
            background: linear-gradient(135deg, var(--brand-bg) 0%, #ffffff 100%);
            border: 2px solid var(--brand-primary);
            border-radius: 12px;
            padding: 24px 28px;
            text-align: center;
            margin: 20px 0;
            box-shadow: var(--shadow-soft);
            position: relative;
            overflow: hidden;
        }

        .box-investimento::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-light));
        }

        .investimento-label {
            font-size: 9pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--text-muted);
            margin-bottom: 8px;
            font-family: 'Inter', sans-serif;
        }

        .investimento-valor {
            font-family: 'Playfair Display', serif;
            font-size: 26pt;
            font-weight: 700;
            color: var(--brand-primary);
            line-height: 1.1;
            margin: 8px 0;
            text-shadow: 0 2px 4px rgba(180, 95, 6, 0.1);
        }

        .investimento-extenso {
            font-size: 10pt;
            color: var(--text-muted);
            font-style: italic;
            margin-top: 8px;
            font-family: 'Inter', sans-serif;
        }

        /* === CRONOGRAMA VISUAL === */
        .cronograma-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin: 16px 0;
        }

        .crono-item {
            background: white;
            border: 1px solid var(--border-elegant);
            border-radius: 8px;
            padding: 14px 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .crono-dias {
            font-size: 20pt;
            font-weight: 700;
            color: var(--brand-primary);
            line-height: 1;
            font-family: 'Playfair Display', serif;
        }

        .crono-label {
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            margin-top: 6px;
            font-weight: 600;
        }

        /* === FOOTER ELEGANTE === */
        .rodape-elegante {
            margin-top: 40px;
            padding-top: 30px;
            text-align: center;
            border-top: 1px solid var(--border-elegant);
            page-break-inside: avoid;
        }

        .texto-atenciosamente {
            font-family: 'Playfair Display', serif;
            font-size: 12pt;
            color: var(--text-muted);
            font-style: italic;
            margin-bottom: 40px;
        }

        .linha-assinatura {
            width: 60%;
            max-width: 300px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--text-dark), transparent);
            margin: 0 auto 12px auto;
        }

        .info-empresa {
            font-size: 9pt;
            color: var(--text-muted);
            font-weight: 500;
            letter-spacing: 0.02em;
            line-height: 1.6;
        }

        .info-empresa strong {
            color: var(--text-dark);
            font-weight: 600;
        }

        /* === BOTÃO FAB === */
        .btn-acao {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 56px;
            height: 56px;
            background: var(--brand-primary);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(180, 95, 6, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .btn-acao:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 24px rgba(180, 95, 6, 0.5);
        }

        /* === UTILITÁRIOS === */
        .espacador { height: 20px; }
        .divisor-sutil {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border-elegant), transparent);
            margin: 20px 0;
        }
    </style>
</head>

<body>

    <!-- Botão Imprimir -->
    <button class="btn-acao no-print" onclick="window.print()" title="Imprimir / Salvar PDF">
        🖨️
    </button>

    <div class="page">

        <!-- HEADER -->
        <header class="header-elegante">
            <div class="logo-area">
                <img src="<?= $logo_empresa ?? 'assets/img/logo.png' ?>" alt="<?= $nome_empresa ?? 'Empresa' ?>">
            </div>
            <div class="titulo-area">
                <h1>Proposta Técnica</h1>
                <div class="subtitulo"><?= $tipo_servico ?? 'Levantamento Topográfico' ?></div>
            </div>
        </header>

        <!-- META INFO -->
        <div class="meta-linha">
            <div class="cidade-data">
                <?= $cidade_empresa ?? 'BELO HORIZONTE' ?>, <?= $data_formatada ?? '16 de Fevereiro de 2026' ?>
            </div>
            <div class="numero-proposta">
                Nº <?= $numero_proposta ?? 'PROP-2026-010' ?>
            </div>
        </div>

        <!-- CARDS CLIENTE + OBRA -->
        <div class="cards-duplo">
            <div class="card-info">
                <div class="card-titulo">Dados do Cliente</div>
                <div class="card-linha">
                    <span class="rotulo">Nome:</span> <?= htmlspecialchars($cliente_nome ?? 'Dalmo De Jesus Alcantara') ?>
                </div>
                <div class="card-linha">
                    <span class="rotulo">E-mail:</span> <?= htmlspecialchars($cliente_email ?? 'dalmo@ab.com.br') ?>
                </div>
                <div class="card-linha">
                    <span class="rotulo">Telefone:</span> <?= $cliente_telefone ?? '(31) 2547-6958' ?>
                </div>
                <?php if(!empty($cliente_whatsapp)): ?>
                <div class="card-linha">
                    <span class="rotulo">WhatsApp:</span> <?= $cliente_whatsapp ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="card-info">
                <div class="card-titulo">Local da Obra</div>
                <div class="card-linha">
                    <span class="bullet">●</span>
                    <span class="rotulo">Endereço:</span> <?= $obra_endereco ?? '' ?>
                </div>
                <div class="card-linha">
                    <span class="bullet">●</span>
                    <span class="rotulo">Bairro:</span> <?= $obra_bairro ?? '' ?>
                </div>
                <div class="card-linha">
                    <span class="bullet">●</span>
                    <span class="rotulo">Cidade/UF:</span> <?= ($obra_cidade ?? '') . ' - ' . ($obra_estado ?? '') ?>
                </div>
                <div class="card-linha">
                    <span class="bullet">●</span>
                    <span class="rotulo">Área Estimada:</span> <?= $obra_area ?? '' ?> ha
                </div>
            </div>
        </div>

        <!-- CONTEÚDO DINÂMICO -->
        <main>
            <?= $conteudo_blocos ?? '' ?>
        </main>

        <!-- FOOTER -->
        <footer class="rodape-elegante">
            <p class="texto-atenciosamente">Atenciosamente,</p>
            <div class="linha-assinatura"></div>
            <div class="info-empresa">
                <strong><?= $nome_empresa ?? 'GeoMetrópole Engenharia e Topografia Ltda.' ?></strong><br>
                <?= $cidade_empresa ?? 'Belo Horizonte' ?> • <?= $slogan_empresa ?? 'Levantamentos Topográficos de Precisão' ?>
            </div>
        </footer>

    </div>

</body>
</html>