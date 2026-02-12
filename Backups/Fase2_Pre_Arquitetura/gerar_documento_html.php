<?php

/**
 * GERADOR DE DOCUMENTO HTML (Para impressão/PDF)
 * Renderiza a proposta em HTML limpo baseando-se nos dados do POST.
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
            // Se já tem R$, remove para garantir formatação limpa (evita R$ R$ 2.000,00)
            $valor = str_replace(['R$', ' '], '', $valor);
        } else {
            $valor = str_replace(['R$', ' '], '', $valor);
        }

        // Se tiver vírgula, trata como decimal brasileiro
        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor); // Remove milhar
            $valor = str_replace(',', '.', $valor); // Troca decimal
        }
    }
    return 'R$ ' . number_format(floatval($valor), 2, ',', '.');
}

function dataPorExtenso()
{
    $meses = [
        1 => 'janeiro',
        2 => 'fevereiro',
        3 => 'março',
        4 => 'abril',
        5 => 'maio',
        6 => 'junho',
        7 => 'julho',
        8 => 'agosto',
        9 => 'setembro',
        10 => 'outubro',
        11 => 'novembro',
        12 => 'dezembro'
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
$logo = 'assets/logo_sgt.png'; // Default
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
    'tipo_levantamento' => $dados['tipo_levantamento'] ?? '',

    // Técnico
    'finalidade' => $dados['finalidade'] ?? '',
    'escopo_servico' => isset($dados['escopo_content']) ? nl2br($dados['escopo_content']) : '',

    // Equipamentos
    'Veiculo' => $dados['veiculo'] ?? '',
    'Estacao_Total' => $dados['estacao_total'] ?? '',
    'Drone' => $dados['drone'] ?? '',

    // Financeiro
    'ValorProposta' => formatarMoeda($dados['valor_proposta'] ?? 0),
    'ValorExtenso' => $dados['valor_extenso'] ?? valorPorExtenso($dados['valor_proposta'] ?? 0),
    'prazo_execucao' => $dados['prazo_execucao'] ?? '',

    'mobilizacao_percentual' => $dados['mobilizacao_percentual'] ?? '',
    'mobilizacao_valor' => formatarMoeda($dados['mobilizacao_valor'] ?? 0),
    'restante_percentual' => (100 - intval($dados['mobilizacao_percentual'] ?? 0)),
    'restante_valor' => formatarMoeda($dados['restante_valor'] ?? 0),

    // Empresa
    'Empresa' => $empresa['Empresa'] ?? 'ELM Topografia',
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
    // Fallbacks para variáveis aninhadas que podem não existir no POST direto
    'escopo_servico' => '', // Remove placeholder recursivo
];

// Limpeza de placeholders não encontrados
foreach ($dados as $k => $v) {
    // Se o próprio texto contém o placeholder dele mesmo, remove para evitar loop ou exibição feia
    if (strpos($v, '${' . $k . '}') !== false) {
        $dados[$k] = str_replace('${' . $k . '}', '', $v);
    }
}

// Função Helper para substituir variáveis
function substituir($texto, $vars)
{
    foreach ($vars as $chave => $valor) {
        $texto = str_replace('${' . $chave . '}', $valor, $texto);
    }
    return $texto;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Proposta <?= str_replace('/', '-', $vars['numero_proposta']) ?> - <?= $vars['nome_cliente_salvo'] ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #525659;
            /* Fundo cinza PDF viewer style */
            margin: 0;
            padding: 20px;
        }

        .page {
            background: white;
            width: 21cm;
            min-height: 29.7cm;
            padding: 2cm;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            position: relative;
            box-sizing: border-box;
        }

        @media print {
            body {
                background: none;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
            }

            .page {
                width: 100%;
                box-shadow: none;
                margin: 0;
                padding: 2cm;
                border: none;
            }

            .no-print {
                display: none !important;
            }

            /* Evitar quebra de página dentro de blocos se possível */
            .bloco-secao,
            table,
            tr,
            li,
            h2,
            h3 {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            /* Evitar quebra logo após título */
            h1,
            h2,
            h3 {
                page-break-after: avoid;
                break-after: avoid;
            }

            /* Footer sempre no final da folha se couber, ou na próxima */
            .footer {
                page-break-inside: avoid;
            }
        }

        /* Estilos do Documento */
        h1,
        h2,
        h3 {
            color: #1e293b;
        }

        h1 {
            border-bottom: 2px solid #1e293b;
            padding-bottom: 10px;
            margin-bottom: 30px;
            font-size: 24pt;
            text-transform: uppercase;
        }

        h2 {
            font-size: 14pt;
            margin-top: 30px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }

        p,
        li,
        td {
            line-height: 1.6;
            color: #334155;
            font-size: 11pt;
        }

        .header {
            text-align: right;
            color: #64748b;
            font-size: 10pt;
            margin-bottom: 50px;
        }

        /* Tabelas Simples */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f1f5f9;
            color: #1e293b;
        }

        .page-container {
            /* min-height: 100vh; */
            /* display: flex; */
            /* flex-direction: column; */
            /* Removido comportamento sticky para assinatura ficar junto ao texto */
            display: block;
        }

        .content-wrap {
            /* flex: 1; */
        }

        .footer {
            margin-top: 30px;
            /* Reduzido de 50px */
            padding-top: 0;
            border-top: none;
            /* Removida linha divisória */
            text-align: center;
            font-size: 10pt;
            color: #555;
            page-break-inside: avoid;
        }

        .btn-fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #2563eb;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            text-decoration: none;
            font-size: 24px;
            cursor: pointer;
            border: none;
            z-index: 1000;
        }

        .btn-fab:hover {
            background: #1d4ed8;
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
            <!-- HEADER COM LOGO -->
            <div class="header-container" style="display: flex; justify-content: flex-start; align-items: center; gap: 40px; margin-bottom: 40px; border-bottom: 3px solid #b45f06; padding-bottom: 10px;">
                <div style="flex: 0 0 auto; text-align: left;">
                    <?php if (file_exists($logo)): ?>
                        <!-- Altura padrão definida para 1.5cm (~56px) para manter proporção -->
                        <img src="<?= $logo ?>" alt="Logo" style="height: 60px; width: auto; object-fit: contain;">
                    <?php else: ?>
                        <div style="font-size: 20px; font-weight: bold; color: #b45f06;">LOGO</div>
                    <?php endif; ?>
                </div>
                <div style="flex: 1; text-align: left; color: #b45f06;">
                    <h1 style="margin: 0; font-size: 18pt; border: none; padding: 0; white-space: nowrap;">PROPOSTA TÉCNICA E COMERCIAL</h1>
                </div>
            </div>

            <!-- CITY/DATE & NUMBER ROW -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 30px; font-weight: normal; color: #000; font-size: 1.2em; text-transform: uppercase;">
                <div>
                    <?= strtoupper($vars['Cidade']) ?>, <?= strtoupper($vars['DataExtenso']) ?>
                </div>
                <div>
                    Nº: <?= $vars['numero_proposta'] ?>
                </div>
            </div>

            <!-- TÍTULO -->
            <!-- <h1>PROPOSTA DE SERVIÇOS</h1> -->
            <!-- O usuário preferiu o título vindo do texto, removido daqui para evitar duplicação -->

            <!-- DADOS CLIENTE E OBRA -->
            <div style="display: flex; gap: 20px; margin-bottom: 30px;">
                <div style="flex: 1; padding: 15px; background: #f8fafc; border-radius: 8px;">
                    <strong>DADOS DO CLIENTE</strong><br>
                    Nome: <?= $vars['nome_cliente_salvo'] ?><br>
                    E-mail: <?= $vars['email_salvo'] ?><br>
                    Telefone: <?= $vars['telefone_salvo'] ?>
                </div>
                <div style="flex: 1; padding: 15px; background: #f8fafc; border-radius: 8px;">
                    <strong>LOCAL DA OBRA</strong><br>
                    <?= $vars['endereco_obra'] ?><br>
                    <?= $vars['bairro_obra'] ?> - <?= $vars['cidade_obra'] ?>/<?= $vars['estado_obra'] ?><br>
                    <strong>Área:</strong> <?= $vars['area_obra'] ?><br>
                    <strong>Serviço:</strong> <?= $vars['tipo_levantamento'] ?>
                </div>
            </div>

            <!-- CONTEÚDO DINÂMICO (Vindo dos Blocos) -->
            <?php
            // Recupera os textos dos inputs
            $campos_texto = [
                'apresentacao_content',
                'finalidade_content', // Se existir, ou usa variáveis
                'escopo_content',
                'documentacao_content',
                'metodologia_content',
                'cronograma_obs', // Texto extra do cronograma
                'investimento_texto', // Texto extra
                'condicoes_texto',
                'consideracoes_content'
            ];

            // Ordem de apresentação manual para garantir aderência ao modelo do usuário

            // Função para exibir bloco com título unificado
            function renderBloco($tituloOpicional, $texto, $vars, $debugName = '')
            {
                // Remove o título do texto se ele já estiver lá (para evitar duplicação)
                $textoClean = trim($texto);
                if ($tituloOpicional) {
                    // Regex mais flexível para espaços e caracteres invisíveis
                    $pattern = '/^' . preg_quote($tituloOpicional, '/') . '[:\s-]*/iu';
                    $textoClean = preg_replace($pattern, '', $textoClean);
                }
                // Remove marcadores de etapa duplicados se o título já os cobre (ajuste fino)
                $textoClean = preg_replace('/^(\d+\.\s+)/', '', $textoClean);

                // 1. Substitui variáveis
                $conteudo = substituir($textoClean, $vars);

                // 2. Remove placeholders residuais
                $conteudo = str_replace('${escopo_servico}', '', $conteudo);

                // 3. Aplica Limpezas no TEXTO PLANO (antes do nl2br)

                // --- NORMALIZAÇÃO CRÍTICA PARA LIMPEZA ---
                // Decodifica entidades HTML e remove caracteres invisíveis
                $conteudo = html_entity_decode($conteudo);
                $conteudo = str_replace(["\xc2\xa0", "&nbsp;"], ' ', $conteudo);
                $conteudo = trim($conteudo);
                // -----------------------------------------

                // Remove linhas de assinatura (ex: _________)
                $conteudo = preg_replace('/_+/', '', $conteudo);

                // Remove COMBINAÇÃO: Empresa + [quebras] + Atenciosamente (Caso esteja no meio do bloco)
                // Isso pega o caso específico que o usuário relatou

                // "LINE KILLER": Remove qualquer linha que contenha o nome da empresa se logo abaixo vier "Atenciosamente"
                // AGORA SUPORTA TAGS HTML E QUEBRAS DE LINHA MISTAS

                // Padrão de quebra flexível: aceita \n, \r, espaços, <br>, <p>, </div>
                $quebraPattern = '(?:[\r\n\s]|<br\s*\/?>|<\/p>|<p>|<div>|<\/div>)+';

                // Opção A: GeoMetrópole Hardcoded
                $conteudo = preg_replace('/GeoMetr[óo]pole.*?' . $quebraPattern . 'Atenciosamente/usi', 'Atenciosamente', $conteudo);

                // Opção B: Nome da Empresa Variável
                $empresaRegex = preg_quote(trim($vars['Empresa']), '/');
                $conteudo = preg_replace('/' . $empresaRegex . '.*?' . $quebraPattern . 'Atenciosamente/usi', 'Atenciosamente', $conteudo);

                // Limpeza residual (caso tenha sobrado apenas o nome no final)
                $conteudo = preg_replace('/' . $empresaRegex . '[\s\.\-,]*$/ui', '', $conteudo);

                // Remove "Atenciosamente" solto (Case Insensitive, com pontuação opcional)
                $conteudo = preg_replace('/Atenciosamente[\.,\s]*/ui', '', $conteudo);

                // Remove nome da empresa e cidade APENAS se estiverem no final (assinatura duplicada)
                $conteudo = trim($conteudo);

                // Regex 1: Tenta remover a variável da empresa exata no final
                $conteudo = preg_replace('/' . $empresaRegex . '[\s\.\-,]*$/ui', '', $conteudo);
                $conteudo = trim($conteudo);

                // Regex 2: Fallback explícito para variações comuns no final
                $conteudo = preg_replace('/GeoMetr[óo]pole\s+Engenharia\s+e\s+Topografia(\s+Ltda)?[\s\.\-,]*$/ui', '', $conteudo);
                $conteudo = trim($conteudo);

                $conteudo = preg_replace('/' . preg_quote($vars['Cidade'], '/') . '[\s\.\-]*$/u', '', $conteudo);
                $conteudo = preg_replace('/' . preg_quote(strtoupper($vars['Cidade']), '/') . '[\s\.\-]*$/u', '', $conteudo);
                $conteudo = trim($conteudo);

                // Limpeza Específica para Condições de Pagamento (Remover cabeçalho de tabela texto plano)
                $conteudo = preg_replace('/ETAPA\s*\|\s*%\s*\|\s*VALOR/i', '', $conteudo);

                // Limpeza de R$ duplicado
                $conteudo = str_replace('R$ R$', 'R$', $conteudo);

                // 4. Formatações Finais
                $conteudo = preg_replace('/(Etapa \d+:)/i', '<strong>$1</strong>', $conteudo);

                // Converte quebras de linha em HTML por último, APENAS se não for HTML rico
                // (Detecta tags de bloco comuns do TinyMCE: <p>, <div>, <ul>, <ol>)
                if (strpos($conteudo, '<p') === false && strpos($conteudo, '<div') === false && strpos($conteudo, '<ul') === false) {
                    $conteudo = nl2br($conteudo);
                }

                echo "<div class='bloco-secao next-step-item' style='margin-bottom: 20px; page-break-inside: avoid;'>";
                // Se o texto for muito curto ou vazio (só quebras de linha), não exibe nada
                if (strlen(trim(strip_tags($texto))) > 3) {
                    // Sempre exibe o título formatado corretamente
                    echo "<h2>{$tituloOpicional}</h2>";
                    echo $conteudo;
                }
                echo "</div>";
            }

            // 1. Apresentação
            // 1. Apresentação
            $txtApresentacao = $dados['apresentacao_content'] ?? '';

            // Aplica negrito com prioridade para o nome mais longo (para evitar negrito parcial)
            // Cria array com variantes do nome
            $variantesEmpresa = [
                $vars['Empresa'],
                'GeoMetrópole Engenharia e Topografia Ltda.',
                'GeoMetrópole Engenharia e Topografia',
                'GeoMetrópole'
            ];
            // Remove duplicatas e vazios
            $variantesEmpresa = array_unique(array_filter($variantesEmpresa));
            // Ordena por tamanho (do maior para o menor)
            usort($variantesEmpresa, function ($a, $b) {
                return strlen($b) - strlen($a);
            });

            // Cria regex: (Nome Completo|Nome Médio|Nome Curto)
            $patternEmpresa = '/(' . implode('|', array_map(function ($n) {
                return preg_quote($n, '/');
            }, $variantesEmpresa)) . ')/u';

            // Substitui apenas se ainda não estiver em negrito
            $txtApresentacao = preg_replace($patternEmpresa, '<strong>$1</strong>', $txtApresentacao);

            renderBloco("1. Apresentação", $txtApresentacao, $vars);

            // 2. Finalidade
            // Prioriza o conteúdo editado (content), se não existir usa a seleção (finalidade)
            $textoFinalidade = !empty($dados['finalidade_descricao']) ? $dados['finalidade_descricao'] : ($dados['finalidade'] ?? '');
            renderBloco("2. Finalidade", $textoFinalidade, $vars);

            // 3. Escopo
            $textoEscopo = $dados['escopo_content'] ?? '';
            $textoEscopo = str_replace(['3. Escopo do Serviço', '${escopo_servico}'], '', $textoEscopo);
            renderBloco("3. Escopo do Serviço", $textoEscopo, $vars);

            // 4. Documentação
            renderBloco("4. Documentação Gerada", $dados['documentacao_content'] ?? '', $vars);

            // 5. Metodologia
            renderBloco("5. Metodologia", $dados['metodologia_content'] ?? '', $vars);

            // 6. Equipamentos
            echo "<h3>6. Equipamentos Previstos</h3>";
            echo "<ul>";
            if (!empty($vars['Veiculo'])) echo "<li><strong>Veículo:</strong> {$vars['Veiculo']}</li>";
            echo "<li><strong>Receptor GNSS:</strong> Par de Receptores GNSS RTK de Dupla Frequência</li>"; // Adicionado fixo como pedido
            if (!empty($vars['Estacao_Total'])) echo "<li><strong>Estação Total:</strong> {$vars['Estacao_Total']}</li>";
            // Só exibe Drone se NÃO for 'Não aplicável'
            if (!empty($vars['Drone']) && stripos($vars['Drone'], 'Não aplicável') === false) {
                echo "<li><strong>Drone:</strong> {$vars['Drone']}</li>";
            }
            echo "</ul>";

            // 7. Investimento
            $investimentoTxt = $dados['investimento_texto'] ?? '';

            // Ajuste de Layout: Valor em Negrito e Extenso na mesma linha
            // Procura por: ${ValorProposta} [quebra de linha] (${ValorExtenso})
            $investimentoTxt = preg_replace('/(\$\{ValorProposta\})\s*[\r\n]+\s*(\(\$\{ValorExtenso\}\))/u', '<strong>$1</strong> $2', $investimentoTxt);

            // Caso o usuário tenha salvo o texto já com os valores (sem placeholders), tenta ajustar também
            // Ex: R$ 2.000,00 \n (dois mil reais)
            $investimentoTxt = preg_replace('/(R\$\s?[\d\.,]+)\s*[\r\n]+\s*(\([^\)]+reais\))/iu', '<strong>$1</strong> $2', $investimentoTxt);

            renderBloco("7. Investimento", $investimentoTxt, $vars);

            if (empty($dados['investimento_texto'])) {
                // Fallback se o texto vier vazio, exibe o padrão
                echo "<h3>7. Investimento</h3>";
                echo "<p class='highlight' style='font-size: 1.2em; font-weight: bold; background: #e2e8f0; padding: 10px;'>
                  VALOR TOTAL DA PROPOSTA: {$vars['ValorProposta']} <span style='font-size: 0.9em; font-weight: normal;'>({$vars['ValorExtenso']})</span></p>";
            }

            // 8. Condições
            // O texto do bloco Condições geralmente traz a tabela ou descrição. 
            // Se o usuário colou texto, usamos ele. Se não, montamos a tabela.
            $condicoesTxt = $dados['condicoes_texto'] ?? '';
            if (!empty($condicoesTxt)) {
                renderBloco("8. Condições de Pagamento", $condicoesTxt, $vars);
            } else {
                echo "<h3>8. Condições de Pagamento</h3>";
                echo "<table style='width: 60%;'>";
                echo "<tr><th>Etapa</th><th>%</th><th>Valor</th></tr>";
                echo "<tr><td>Mobilização (Aceite)</td><td><b>{$vars['mobilizacao_percentual']}%</b></td><td>{$vars['mobilizacao_valor']}</td></tr>";
                echo "<tr><td>Entrega Final</td><td><b>{$vars['restante_percentual']}%</b></td><td>{$vars['restante_valor']}</td></tr>";
                echo "</table>";
            }

            // 9. Dados Bancários
            renderBloco("9. Dados Bancários", $dados['dados_bancarios_content'] ?? '', $vars);
            // Fallback para dados bancários se o texto estiver vazio
            if (empty($dados['dados_bancarios_content']) && empty($dados['dados_bancarios'])) {
                echo "<h3>9. Dados Bancários</h3>";
                echo "<p>
                Banco: {$vars['Banco']}<br>
                Agência: {$vars['Agencia']} | Conta: {$vars['Conta']}<br>
                PIX: {$vars['PIX']}
                </p>";
            }

            // 10. Considerações
            renderBloco("10. Considerações Finais", $dados['consideracoes_content'] ?? '', $vars);
            ?>

        </div> <!-- Fim content-wrap -->

        <div class="footer">
            <p style="margin-bottom: 50px; font-size: 1.1em;">Atenciosamente,</p>

            <div style="border-top: 2px solid #333; width: 80%; margin: 20px auto 10px auto;"></div>

            <strong style="font-size: 1.4em; color: #000; display: block; margin-bottom: 5px;"><?= $vars['Empresa'] ?></strong>
            <span style="font-size: 1em; color: #666; text-transform: uppercase;"><?= $vars['empresa_proponente_cidade'] ?></span>
        </div>

    </div> <!-- Fim page-container -->

</body>

</html>