<?php

/**
 * GERADOR DE DOCUMENTO HTML PREMIUM (Para impressão/PDF)
 * Renderiza a proposta em HTML elegante baseando-se nos dados do POST.
 * Design premium com tipografia Inter + cores harmonizadas.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'db.php';

// Formatação Auxiliar
function formatarMoeda($valor)
{
    if (empty($valor)) return 'R$ 0,00';
    if (is_string($valor)) {
        if (strpos($valor, 'R$') === 0) {
            $valor = str_replace(['R$', ' '], '', $valor);
        } else {
            $valor = str_replace(['R$', ' '], '', $valor);
        }
        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
    }
    return 'R$ ' . number_format(floatval($valor), 2, ',', '.');
}

function dataPorExtenso()
{
    $meses = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
    ];
    $dia = date('d');
    $mes = $meses[intval(date('m'))];
    $ano = date('Y');
    return "$dia de $mes de $ano";
}

function valorPorExtenso($valor = 0)
{
    if (!$valor) return "zero reais";

    $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
    $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");

    $c = array("", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
    $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa");
    $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezessete", "dezoito", "dezenove");
    $u = array("", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");

    $z = 0;
    $valor = number_format($valor, 2, ".", ".");
    $inteiro = explode(".", $valor);
    $count = count($inteiro);
    $rt = "";

    for ($i = 0; $i < $count; $i++) {
        for ($ii = mb_strlen($inteiro[$i]); $ii < 3; $ii++) {
            $inteiro[$i] = "0" . $inteiro[$i];
        }

        $rc = "";
        $valor = $inteiro[$i];
        $r = substr($valor, 0, 1);
        $ri = substr($valor, 1, 1);
        $rii = substr($valor, 2, 1);

        if ($valor > 100 && ($ri . $rii) != "00") {
            $rc = $c[$r];
        } else {
            if ($valor > 100) {
                $rc = ($r == 1) ? "cem" : $c[$r];
            } else {
                if ($valor == 100) {
                    $rc = "cem";
                }
            }
        }

        if (($ri . $rii) < 20 && ($ri . $rii) > 9) {
            $rc .= (($r != 0) ? " e " : "") . $d10[$rii];
        } else {
            if ($ri != 0) {
                $rc .= (($r != 0) ? " e " : "") . $d[$ri];
            }
            if ($rii != 0) {
                $rc .= (($r != 0 || $ri != 0) ? " e " : "") . $u[$rii];
            }
        }

        $r = $count - $i - 1;
        $str = ($inteiro[$i] > 1) ? $plural[$r] : $singular[$r];
        if ($inteiro[$i] != 000) {
            $rt .= (($z > 0) ? (($z > 1) ? " " : " e ") : "") . "$rc $str";
        }
        $z++;
    }
    return $rt;
}

// 1. Receber Dados
$dados = $_POST;

// 2. Dados da Empresa (Fallback)
$conn = Database::getProd();
$empresa = [];
$id_usuario = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : 0;

if ($id_usuario > 0) {
    $res = $conn->query("SELECT * FROM DadosEmpresa WHERE id_criador = $id_usuario LIMIT 1");
    $empresa = $res->fetch_assoc() ?: [];
}
if (empty($empresa)) {
    $res = $conn->query("SELECT * FROM DadosEmpresa LIMIT 1");
    $empresa = $res->fetch_assoc() ?: [];
}

// Logo Logic
$logo = 'assets/logo_sgt.png';
if (!empty($empresa['logo_caminho']) && file_exists($empresa['logo_caminho'])) {
    $logo = $empresa['logo_caminho'];
}

// 3. Mapeamento de Variáveis (Flattening)
$vars = [
    // Cliente
    'nome_cliente_salvo' => $dados['nome_cliente'] ?? '',
    'email_salvo' => $dados['email_cliente'] ?? '',
    'telefone_salvo' => $dados['telefone_cliente'] ?? '',
    'celular_salvo' => $dados['celular_cliente'] ?? '',
    'whatsapp_salvo' => $dados['celular_cliente'] ?? '',
    'numero_proposta' => $dados['numero_proposta'] ?? '000/0000',

    // Obra
    'endereco_obra' => $dados['endereco_obra'] ?? '',
    'bairro_obra' => $dados['bairro_obra'] ?? '',
    'cidade_obra' => $dados['cidade_obra'] ?? '',
    'estado_obra' => $dados['estado_obra'] ?? '',
    'area_obra' => $dados['area_obra'] ?? '',
    'unidade_area' => $dados['unidade_area'] ?? 'm²',
    'tipo_levantamento' => $dados['tipo_levantamento'] ?? '',

    // Técnico
    'finalidade' => $dados['finalidade'] ?? '',
    'escopo_servico' => isset($dados['escopo_content']) ? nl2br($dados['escopo_content']) : '',

    // Equipamentos
    'Veiculo' => $dados['veiculo'] ?? ($dados['equipamentos_veiculo_content'] ?? ''),
    'Estacao_Total' => $dados['estacao_total'] ?? ($dados['equipamentos_estacao_total_content'] ?? ''),
    'Drone' => $dados['drone'] ?? ($dados['equipamentos_drone_content'] ?? ''),

    // Financeiro
    'ValorProposta' => formatarMoeda($dados['valor_proposta'] ?? 0),
    'ValorExtenso' => $dados['valor_extenso'] ?? valorPorExtenso($dados['valor_proposta'] ?? 0),
    'prazo_execucao' => $dados['prazo_execucao'] ?? '',
    'dias_campo' => $dados['dias_campo'] ?? '0',
    'dias_escritorio' => $dados['dias_escritorio'] ?? '0',

    'mobilizacao_percentual' => $dados['mobilizacao_percentual'] ?? '',
    'mobilizacao_valor' => formatarMoeda($dados['mobilizacao_valor'] ?? 0),
    'restante_percentual' => (100 - intval($dados['mobilizacao_percentual'] ?? 0)),
    'restante_valor' => formatarMoeda($dados['restante_valor'] ?? 0),

    // Empresa
    'Empresa' => $empresa['Empresa'] ?? 'ELM Topografia',
    'empresa' => $empresa['Empresa'] ?? 'ELM Topografia',
    'CNPJ' => $empresa['CNPJ'] ?? '',
    'Banco' => $empresa['Banco'] ?? '',
    'Agencia' => $empresa['Agencia'] ?? '',
    'Conta' => $empresa['Conta'] ?? '',
    'PIX' => $empresa['PIX'] ?? '',
    'whatsapp' => $empresa['Whatsapp'] ?? '',
    'empresa_proponente_cidade' => $empresa['Cidade'] ?? 'Belo Horizonte',

    // Layout
    'Cidade' => $empresa['Cidade'] ?? 'Belo Horizonte',
    'DExrenso' => dataPorExtenso(),
    'DataExtenso' => dataPorExtenso(),

    // Variáveis Dinâmicas de Drone/Campo
    'ClienteCidadeUF' => ($dados['cidade_obra'] ?? '') . '-' . ($dados['estado_obra'] ?? ''),
    'TipoTerreno' => $dados['tipo_terreno'] ?? '',
    'CoberturaVegetal' => $dados['cobertura_vegetal'] ?? '',
    'AcessoLocal' => $dados['acesso_local'] ?? '',
    'RestricoesAereas' => $dados['restricoes_aereas'] ?? '',

    // Fallbacks
    'escopo_servico' => '',
];

// Limpeza de placeholders não encontrados
foreach ($dados as $k => $v) {
    if (is_string($v) && strpos($v, '${' . $k . '}') !== false) {
        $dados[$k] = str_replace('${' . $k . '}', '', $v);
    }
}

// Função Helper para substituir variáveis
function substituir($texto, $vars)
{
    foreach ($vars as $chave => $valor) {
        $texto = str_ireplace('${' . $chave . '}', $valor ?? '', $texto);
    }
    return $texto;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Proposta <?= str_replace('/', '-', $vars['numero_proposta']) ?> - <?= $vars['nome_cliente_salvo'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #b45f06;
            --brand-light: #d4a574;
            --brand-bg: #fdf8f3;
            --text-primary: #1a1a2e;
            --text-secondary: #4a4a68;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --border-light: #f3f4f6;
            --surface: #f8fafc;
            --accent-green: #059669;
            --accent-blue: #2563eb;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #525659;
            margin: 0;
            padding: 20px;
            font-size: 11pt;
            line-height: 1.7;
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
        }

        .page {
            background: white;
            width: 21cm;
            min-height: 29.7cm;
            padding: 2cm 2.2cm;
            margin: 0 auto;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
            position: relative;
            border-radius: 2px;
        }

        @media print {
            body { background: none; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page { width: 100%; box-shadow: none; margin: 0; padding: 2cm; border: none; border-radius: 0; }
            .no-print { display: none !important; }
            .bloco-secao, table, tr, li, h2, h3 { page-break-inside: avoid; break-inside: avoid; }
            h1, h2, h3 { page-break-after: avoid; break-after: avoid; }
            .footer { page-break-inside: avoid; }
        }

        /* === TYPOGRAPHY === */
        h1, h2, h3 { font-family: 'Inter', sans-serif; font-weight: 700; letter-spacing: -0.02em; }

        h2 {
            font-size: 12pt;
            color: var(--brand);
            margin-top: 28px;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1.5px solid var(--brand-light);
            letter-spacing: 0.02em;
            text-transform: uppercase;
            font-weight: 600;
        }

        h3 { font-size: 11pt; color: var(--text-primary); margin-top: 20px; margin-bottom: 8px; }

        p, li, td { line-height: 1.7; color: var(--text-secondary); font-size: 10.5pt; }

        strong { color: var(--text-primary); font-weight: 600; }

        /* === HEADER === */
        .header-container {
            display: flex;
            align-items: center;
            gap: 24px;
            padding-bottom: 18px;
            border-bottom: 3px solid var(--brand);
            margin-bottom: 24px;
        }

        .header-logo img {
            max-height: 80px;
            width: auto;
            object-fit: contain;
        }

        .header-title {
            flex: 1;
            text-align: right;
        }

        .header-title h1 {
            font-family: 'Playfair Display', serif;
            font-size: 20pt;
            color: var(--brand);
            margin: 0;
            border: none;
            padding: 0;
            line-height: 1.2;
            letter-spacing: 0.03em;
        }

        .header-title .subtitle {
            font-family: 'Inter', sans-serif;
            font-size: 10pt;
            color: var(--text-muted);
            font-weight: 400;
            letter-spacing: 0.05em;
            margin-top: 4px;
        }

        /* === META ROW === */
        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            font-size: 10pt;
            color: var(--text-muted);
            font-weight: 400;
        }

        .meta-row .numero {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            color: var(--brand);
            font-size: 10.5pt;
            letter-spacing: 0.02em;
        }

        /* === INFO CARDS === */
        .info-cards {
            display: flex;
            gap: 16px;
            margin-bottom: 28px;
        }

        .info-card {
            flex: 1;
            padding: 16px 18px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            border-left: 3px solid var(--brand);
        }

        .info-card-title {
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--brand);
            margin-bottom: 8px;
        }

        .info-card p {
            font-size: 10pt;
            margin-bottom: 2px;
            color: var(--text-secondary);
        }

        .info-card .label {
            font-weight: 500;
            color: var(--text-primary);
        }

        /* === TABLES === */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 12px 0 20px 0;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        th, td {
            padding: 10px 14px;
            text-align: left;
            font-size: 10pt;
            border-bottom: 1px solid var(--border-light);
        }

        th {
            background: var(--text-primary);
            color: white;
            font-weight: 600;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border: none;
        }

        tr:last-child td { border-bottom: none; }
        tr:nth-child(even) td { background: var(--surface); }

        td strong { color: var(--brand); }

        /* === CRONOGRAMA MINI CARDS === */
        .cronograma-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin: 12px 0 16px 0;
        }

        .crono-card {
            text-align: center;
            padding: 14px 10px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .crono-card .crono-value {
            font-size: 18pt;
            font-weight: 700;
            color: var(--brand);
            line-height: 1;
        }

        .crono-card .crono-label {
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            font-weight: 600;
            margin-top: 4px;
        }

        /* === INVESTMENT HIGHLIGHT === */
        .invest-box {
            background: linear-gradient(135deg, var(--brand-bg), #fff);
            border: 2px solid var(--brand);
            border-radius: 10px;
            padding: 20px 24px;
            text-align: center;
            margin: 16px 0;
        }

        .invest-box .invest-label {
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 6px;
        }

        .invest-box .invest-value {
            font-family: 'Playfair Display', serif;
            font-size: 22pt;
            font-weight: 700;
            color: var(--brand);
            line-height: 1.2;
        }

        .invest-box .invest-extenso {
            font-size: 9.5pt;
            color: var(--text-muted);
            font-style: italic;
            margin-top: 4px;
        }

        /* === EQUIPAMENTOS LIST === */
        ul.equip-list {
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }

        ul.equip-list li {
            padding: 8px 14px;
            border-left: 3px solid var(--brand-light);
            margin-bottom: 6px;
            background: var(--surface);
            border-radius: 0 6px 6px 0;
            font-size: 10.5pt;
        }

        ul.equip-list li strong { color: var(--text-primary); }

        /* === SEÇÃO BLOCO === */
        .bloco-secao {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .bloco-secao p, .bloco-secao div { font-size: 10.5pt; color: var(--text-secondary); line-height: 1.7; }
        .bloco-secao ul, .bloco-secao ol { padding-left: 20px; margin: 8px 0; }
        .bloco-secao li { margin-bottom: 4px; }

        /* === FOOTER === */
        .footer {
            margin-top: 40px;
            padding-top: 0;
            text-align: center;
            page-break-inside: avoid;
        }

        .footer .atenciosamente {
            font-size: 10.5pt;
            color: var(--text-secondary);
            font-style: italic;
            margin-bottom: 50px;
        }

        .footer .assinatura-linha {
            width: 55%;
            border-top: 1px solid var(--text-primary);
            margin: 0 auto 10px auto;
        }

        .footer .empresa-info {
            font-size: 9.5pt;
            color: var(--text-muted);
            font-weight: 500;
            letter-spacing: 0.02em;
        }

        /* === FAB BUTTON === */
        .btn-fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--brand);
            color: white;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(180, 95, 6, 0.35);
            text-decoration: none;
            font-size: 22px;
            cursor: pointer;
            border: none;
            z-index: 1000;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-fab:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(180, 95, 6, 0.45);
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <!-- Botão Flutuante Imprimir/Salvar -->
    <button class="btn-fab no-print" onclick="window.print()" title="Salvar como PDF">
        <i class="fas fa-print"></i>
    </button>

    <div class="page page-container">
        <div class="content-wrap">

            <!-- HEADER COM LOGO -->
            <div class="header-container">
                <div class="header-logo">
                    <?php if (file_exists($logo)): ?>
                        <img src="<?= $logo ?>" alt="Logo">
                    <?php else: ?>
                        <div style="font-size: 20px; font-weight: bold; color: var(--brand); border: 2px dashed var(--brand); padding: 12px;">LOGO</div>
                    <?php endif; ?>
                </div>
                <div class="header-title">
                    <h1>Proposta Técnica</h1>
                    <div class="subtitle"><?= $vars['tipo_levantamento'] ?></div>
                </div>
            </div>

            <!-- CIDADE/DATA & NÚMERO -->
            <div class="meta-row">
                <div><?= $vars['Cidade'] ?>, <?= $vars['DataExtenso'] ?></div>
                <div class="numero">Nº <?= $vars['numero_proposta'] ?></div>
            </div>

            <!-- DADOS CLIENTE E OBRA -->
            <div class="info-cards">
                <div class="info-card">
                    <div class="info-card-title">Dados do Cliente</div>
                    <p><span class="label">Nome:</span> <?= $vars['nome_cliente_salvo'] ?></p>
                    <p><span class="label">E-mail:</span> <?= $vars['email_salvo'] ?></p>
                    <p><span class="label">Telefone:</span> <?= $vars['telefone_salvo'] ?></p>
                    <?php if (!empty($vars['celular_salvo'])): ?>
                    <p><span class="label">WhatsApp:</span> <?= $vars['celular_salvo'] ?></p>
                    <?php endif; ?>
                </div>
                <div class="info-card">
                    <div class="info-card-title">Local da Obra</div>
                    <p>● <span class="label">Endereço:</span> <?= $vars['endereco_obra'] ?></p>
                    <p>● <span class="label">Bairro:</span> <?= $vars['bairro_obra'] ?></p>
                    <p>● <span class="label">Cidade/Estado:</span> <?= $vars['cidade_obra'] ?> - <?= $vars['estado_obra'] ?></p>
                    <p>● <span class="label">Área Estimada:</span> <?= $vars['area_obra'] ?> <?= $vars['unidade_area'] ?></p>
                </div>
            </div>

            <!-- CONTEÚDO DINÂMICO (Vindo dos Blocos) -->
            <?php
            // Função para exibir bloco com título unificado
            function renderBloco($tituloOpicional, $texto, $vars, $debugName = '')
            {
                $textoClean = trim($texto);
                if ($tituloOpicional) {
                    $pattern = '/^' . preg_quote($tituloOpicional, '/') . '[:\s-]*/iu';
                    $textoClean = preg_replace($pattern, '', $textoClean);
                }
                $textoClean = preg_replace('/^(\d+\.\s+)/', '', $textoClean);

                $conteudo = substituir($textoClean, $vars);
                $conteudo = str_replace('${escopo_servico}', '', $conteudo);

                // Limpezas de texto
                $conteudo = html_entity_decode($conteudo);
                $conteudo = str_replace(["\xc2\xa0", "&nbsp;"], ' ', $conteudo);
                $conteudo = trim($conteudo);
                $conteudo = preg_replace('/_+/', '', $conteudo);

                $quebraPattern = '(?:[\r\n\s]|<br\s*\/?>|<\/p>|<p>|<div>|<\/div>)+';
                $empresaRegex = preg_quote(trim($vars['Empresa']), '/');
                $conteudo = preg_replace('/GeoMetr[óo]pole.*?' . $quebraPattern . 'Atenciosamente/usi', 'Atenciosamente', $conteudo);
                $conteudo = preg_replace('/' . $empresaRegex . '.*?' . $quebraPattern . 'Atenciosamente/usi', 'Atenciosamente', $conteudo);
                $conteudo = preg_replace('/' . $empresaRegex . '[\s\.\-,]*$/ui', '', $conteudo);
                $conteudo = preg_replace('/Atenciosamente[\.,\s]*/ui', '', $conteudo);
                $conteudo = trim($conteudo);
                $conteudo = preg_replace('/' . $empresaRegex . '[\s\.\-,]*$/ui', '', $conteudo);
                $conteudo = trim($conteudo);
                $conteudo = preg_replace('/GeoMetr[óo]pole\s+Engenharia\s+e\s+Topografia(\s+Ltda)?[\s\.\-,]*$/ui', '', $conteudo);
                $conteudo = trim($conteudo);
                $conteudo = preg_replace('/' . preg_quote($vars['Cidade'], '/') . '[\s\.\-]*$/u', '', $conteudo);
                $conteudo = preg_replace('/' . preg_quote(strtoupper($vars['Cidade']), '/') . '[\s\.\-]*$/u', '', $conteudo);
                $conteudo = trim($conteudo);
                $conteudo = preg_replace('/ETAPA\s*\|\s*%\s*\|\s*VALOR/i', '', $conteudo);
                $conteudo = str_replace('R$ R$', 'R$', $conteudo);
                $conteudo = preg_replace('/(Etapa \d+:)/i', '<strong>$1</strong>', $conteudo);

                if (strpos($conteudo, '<p') === false && strpos($conteudo, '<div') === false && strpos($conteudo, '<ul') === false) {
                    $conteudo = nl2br($conteudo);
                }

                echo "<div class='bloco-secao'>";
                if (strlen(trim(strip_tags($texto))) > 3) {
                    echo "<h2>{$tituloOpicional}</h2>";
                    echo $conteudo;
                }
                echo "</div>";
            }

            // === 1. APRESENTAÇÃO ===
            $txtApresentacao = $dados['apresentacao_content'] ?? '';
            $txtApresentacao = substituir($txtApresentacao, $vars);
            if ($vars['Empresa'] !== 'ELM Serviços Topográficos Ltda.' && strpos($txtApresentacao, 'ELM Serviços Topográficos Ltda.') !== false) {
                $txtApresentacao = str_replace('ELM Serviços Topográficos Ltda.', $vars['Empresa'], $txtApresentacao);
            }
            $variantesEmpresa = [
                $vars['Empresa'], 'GeoMetrópole Engenharia e Topografia Ltda.',
                'GeoMetrópole Engenharia e Topografia', 'GeoMetrópole',
                'ELM Serviços Topográficos Ltda.', 'ELM Serviços Topográficos'
            ];
            $variantesEmpresa = array_unique(array_filter($variantesEmpresa));
            usort($variantesEmpresa, function ($a, $b) { return mb_strlen($b) - mb_strlen($a); });
            $patternEmpresa = '/(' . implode('|', array_map(function ($n) { return preg_quote($n, '/'); }, $variantesEmpresa)) . ')/u';
            $txtApresentacao = preg_replace($patternEmpresa, '<strong>$1</strong>', $txtApresentacao);
            renderBloco("1. Apresentação", $txtApresentacao, $vars);

            // === 2. FINALIDADE ===
            $textoFinalidade = !empty($dados['finalidade_content']) ? $dados['finalidade_content'] 
                : (!empty($dados['finalidade_descricao']) ? $dados['finalidade_descricao'] : ($dados['finalidade'] ?? ''));
            renderBloco("2. Finalidade", $textoFinalidade, $vars);

            // === 3. ESCOPO ===
            $textoEscopo = $dados['escopo_content'] ?? '';
            $textoEscopo = str_replace(['3. Escopo do Serviço', '${escopo_servico}'], '', $textoEscopo);
            renderBloco("3. Escopo do Serviço", $textoEscopo, $vars);

            // === 4. DOCUMENTAÇÃO ===
            renderBloco("4. Documentação Gerada", $dados['documentacao_content'] ?? '', $vars);

            // === 5. METODOLOGIA ===
            renderBloco("5. Metodologia", $dados['metodologia_content'] ?? '', $vars);

            // === 6. EQUIPAMENTOS ===
            $isConferencia = stripos($vars['tipo_levantamento'] ?? '', 'Conferência') !== false;
            $hasEquip = !empty($vars['Veiculo']) || !$isConferencia || !empty($vars['Estacao_Total']) 
                        || (!empty($vars['Drone']) && stripos($vars['Drone'], 'Não aplicável') === false);

            if ($hasEquip) {
                echo "<h2>6. Equipamentos Previstos</h2>";
                echo "<ul class='equip-list'>";
                if (!empty($vars['Veiculo'])) echo "<li><strong>Veículo:</strong> {$vars['Veiculo']}</li>";
                if (!$isConferencia) echo "<li><strong>Receptor GNSS:</strong> Par de Receptores GNSS RTK de Dupla Frequência</li>";
                if (!empty($vars['Estacao_Total'])) echo "<li><strong>Estação Total:</strong> {$vars['Estacao_Total']}</li>";
                if (!empty($vars['Drone']) && stripos($vars['Drone'], 'Não aplicável') === false) {
                    echo "<li><strong>Drone:</strong> {$vars['Drone']}</li>";
                }
                echo "</ul>";
            }

            // === 7. CRONOGRAMA ===
            $prazo = $vars['prazo_execucao'] ?: (intval($vars['dias_campo']) + intval($vars['dias_escritorio'])) . ' dias úteis';
            $hasCronograma = intval($vars['dias_campo']) > 0 || intval($vars['dias_escritorio']) > 0 || !empty($dados['cronograma_content']);
            
            // Extrai texto de investimento do cronograma para usar no bloco 8
            $textoInvestDoCrono = '';
            if (!empty($dados['cronograma_content'])) {
                $cronoRaw = $dados['cronograma_content'];
                // Captura frases de investimento (R$ + extenso + custo-benefício)
                if (preg_match('/(R\$\s?[\d\.,]+\s*\([^)]*reais[^)]*\))/iu', $cronoRaw, $m)) {
                    $textoInvestDoCrono .= $m[1] . "\n";
                }
                if (preg_match('/(Este investimento[^.]*\.)/iu', $cronoRaw, $m)) {
                    $textoInvestDoCrono .= $m[1];
                }
                if (preg_match('/(custo-benef[ií]cio[^.]*\.)/iu', $cronoRaw, $m)) {
                    $textoInvestDoCrono .= ' ' . $m[1];
                }
                $textoInvestDoCrono = trim($textoInvestDoCrono);
            }
            
            if ($hasCronograma) {
                echo "<h2>7. Cronograma de Execução</h2>";

                // Tabela de Etapas Premium
                echo "<table>";
                echo "<tr><th>Etapa</th><th>Descrição</th><th>Prazo Estimado</th></tr>";
                echo "<tr><td><strong>1. Mobilização</strong></td><td>Planejamento, análise DECEA e ida a campo</td><td>Até 02 dias</td></tr>";
                echo "<tr><td><strong>2. Campo (GCPs)</strong></td><td>Instalação de pontos de controle terrestre</td><td>01 dia</td></tr>";
                echo "<tr><td><strong>3. Campo (Voo)</strong></td><td>Execução do voo de mapeamento</td><td>01 dia</td></tr>";
                echo "<tr><td><strong>4. Processamento</strong></td><td>Geração da nuvem de pontos e ortomosaico</td><td>03 a 05 dias</td></tr>";
                echo "<tr><td><strong>5. CAD/Vetorização</strong></td><td>Desenho técnico e curvas de nível</td><td>03 a 05 dias</td></tr>";
                echo "<tr style='background: var(--surface); font-weight: 600;'><td colspan='2'><strong>TOTAL ESTIMADO</strong></td><td><strong>{$prazo}</strong></td></tr>";
                echo "</table>";
                
                // Texto adicional do cronograma (sem parte de investimento)
                if (!empty($dados['cronograma_content'])) {
                    $cronoTexto = $dados['cronograma_content'];
                    $cronoTexto = preg_replace('/R\$\s?[\d\.,]+\s*\([^)]*reais[^)]*\)/iu', '', $cronoTexto);
                    $cronoTexto = preg_replace('/Este investimento[^.]*\./iu', '', $cronoTexto);
                    $cronoTexto = preg_replace('/custo-benef[ií]cio[^.]*\./iu', '', $cronoTexto);
                    $cronoTexto = preg_replace('/VALOR TOTAL DA PROPOSTA[^.]*\./iu', '', $cronoTexto);
                    $cronoTexto = trim($cronoTexto);
                    
                    if (!empty($cronoTexto) && strlen(strip_tags($cronoTexto)) > 5) {
                        $cronoTexto = substituir($cronoTexto, $vars);
                        if (strpos($cronoTexto, '<p') === false) $cronoTexto = nl2br($cronoTexto);
                        echo "<div class='bloco-secao'>{$cronoTexto}</div>";
                    }
                }
            }

            // === 8. INVESTIMENTO ===
            $investimentoTxt = $dados['investimento_content'] ?? ($dados['investimento_texto'] ?? '');
            
            // Se tem conteúdo editado, renderiza como bloco
            if (!empty($investimentoTxt) && strlen(trim(strip_tags($investimentoTxt))) > 5) {
                $investimentoTxt = preg_replace('/(\$\{ValorProposta\})\s*[\r\n]*\s*(\$\{ValorExtenso\})/u', '<strong>$1</strong> ($2)', $investimentoTxt);
                $investimentoTxt = preg_replace('/(\$\{ValorProposta\})\s*[\r\n]*\s*(\(\$\{ValorExtenso\}\))/u', '<strong>$1</strong> $2', $investimentoTxt);
                $investimentoTxt = preg_replace('/(R\$\s?[\d\.,]+)\s*[\r\n]*\s*\(([^\)]+reais)\)/iu', '<strong>$1</strong> ($2)', $investimentoTxt);
                renderBloco("8. Investimento", $investimentoTxt, $vars);
            } else {
                echo "<h2>8. Investimento</h2>";
            }
            
            // Texto de investimento extraído do cronograma (antes do quadro de valor)
            if (!empty($textoInvestDoCrono)) {
                $textoInvestDoCrono = substituir($textoInvestDoCrono, $vars);
                if (strpos($textoInvestDoCrono, '<p') === false) {
                    $textoInvestDoCrono = "<p>{$textoInvestDoCrono}</p>";
                }
                echo "<div class='bloco-secao'>{$textoInvestDoCrono}</div>";
            }
            
            // Quadro de destaque do valor
            echo "<div class='invest-box'>";
            echo "<div class='invest-label'>Valor Total da Proposta</div>";
            echo "<div class='invest-value'>{$vars['ValorProposta']}</div>";
            echo "<div class='invest-extenso'>({$vars['ValorExtenso']})</div>";
            echo "</div>";


            // === 9. CONDIÇÕES DE PAGAMENTO ===
            $condicoesTxt = $dados['condicoes_content'] ?? ($dados['condicoes_texto'] ?? '');
            if (!empty($condicoesTxt) && strlen(trim(strip_tags($condicoesTxt))) > 5) {
                renderBloco("9. Condições de Pagamento", $condicoesTxt, $vars);
            } else {
                echo "<h2>9. Condições de Pagamento</h2>";
                echo "<table>";
                echo "<tr><th>Etapa</th><th style='text-align:center;'>%</th><th style='text-align:right;'>Valor</th></tr>";
                echo "<tr><td>Mobilização (Aceite da Proposta)</td><td style='text-align:center;'><strong>{$vars['mobilizacao_percentual']}%</strong></td><td style='text-align:right;'>{$vars['mobilizacao_valor']}</td></tr>";
                echo "<tr><td>Entrega Final</td><td style='text-align:center;'><strong>{$vars['restante_percentual']}%</strong></td><td style='text-align:right;'>{$vars['restante_valor']}</td></tr>";
                echo "</table>";
            }

            // === 10. DADOS BANCÁRIOS ===
            $dadosBancariosTxt = $dados['dados_bancarios_content'] ?? ($dados['dados_bancarios'] ?? '');
            if (!empty($dadosBancariosTxt) && strlen(trim(strip_tags($dadosBancariosTxt))) > 5) {
                renderBloco("10. Dados Bancários", $dadosBancariosTxt, $vars);
            } else {
                echo "<h2>10. Dados Bancários</h2>";
                echo "<div class='info-card' style='border-left-color: var(--accent-green);'>";
                echo "<p><span class='label'>Banco:</span> {$vars['Banco']}</p>";
                echo "<p><span class='label'>Agência:</span> {$vars['Agencia']} &nbsp;|&nbsp; <span class='label'>Conta:</span> {$vars['Conta']}</p>";
                echo "<p><span class='label'>PIX:</span> {$vars['PIX']}</p>";
                echo "</div>";
            }

            // === 11. CONSIDERAÇÕES FINAIS ===
            renderBloco("11. Considerações Finais", $dados['consideracoes_content'] ?? '', $vars);
            ?>

        </div> <!-- Fim content-wrap -->

        <div class="footer">
            <p class="atenciosamente">Atenciosamente,</p>
            <div class="assinatura-linha"></div>
            <div class="empresa-info">
                <strong><?= $vars['Empresa'] ?></strong> &nbsp;•&nbsp; <?= $vars['empresa_proponente_cidade'] ?> &nbsp;•&nbsp; Levantamentos Topográficos
            </div>
        </div>

    </div> <!-- Fim page-container -->

</body>

</html>