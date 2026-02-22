<?php
/**
 * GERADOR DE DOCUMENTO HTML PREMIUM (Para impressão/PDF)
 * Renderiza a proposta em HTML elegante baseando-se nos dados do POST.
 * Design premium com tipografia Inter + cores harmonizadas.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Evita cache ao dar refresh (sempre gera documento fresco)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ConnectionManager.php';
require_once __DIR__ . '/PropostaRepository.php';

// [NOVO] Autoload do Composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

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

// Função Helper para substituir variáveis — suporta ${var}, [var] e {{var}}
function substituir($texto, $vars)
{
    foreach ($vars as $chave => $valor) {
        if (is_array($valor) || is_object($valor)) continue;
        $val = $valor ?? '';
        // Formato 1: ${variavel}
        $texto = str_ireplace('${' . $chave . '}', $val, $texto);
        // Formato 2: [Variavel] — usado nos defaultContent dos blocos
        $texto = str_ireplace('[' . $chave . ']', $val, $texto);
        // Formato 3: {{variavel}} — usado nos templates piloto
        $texto = preg_replace('/\{\{\s*' . preg_quote($chave, '/') . '\s*\}\}/i', $val, $texto);
    }
    return $texto;
}


/**
 * Remove valor monetário e texto de investimento de blocos indesejados.
 */
function limparConteudoInvestimentoDeBloco($texto)
{
    if (empty($texto) || !is_string($texto)) return $texto;
    $t = $texto;
    $t = preg_replace_callback('/R\$\s*([\d\.,]+)(?:\s*\([^)]+\))?/iu', function ($m) {
        $v = (float) str_replace(',', '.', str_replace('.', '', $m[1]));
        return ($v >= 0) ? '' : $m[0];
    }, $t);
    $t = preg_replace('/<(?:p|div)[^>]*>\s*\([^)]*[Rr]eais\)\s*<\/(?:p|div)>/iu', '', $t);
    $t = preg_replace('/^[\r\n\s]*\([^)]*[Rr]eais\)[\r\n\s]*/imu', "\n", $t);
    $t = preg_replace('/<(?:p|div)[^>]*>\s*Este investimento[^<]*<\/(?:p|div)>/iu', '', $t);
    $t = preg_replace('/Este investimento[^.]*\./iu', '', $t);
    $t = preg_replace('/<(?:p|div)[^>]*>\s*R\$\s*[\d\.,]+\s*<\/(?:p|div)>/iu', '', $t);
    $t = preg_replace('/^[\r\n\s]*R\$\s*[\d\.,]+[\r\n\s]*/imu', "\n", $t);
    $t = preg_replace('/<(?:p|div)[^>]*>\s*(?:&nbsp;|\s)*<\/(?:p|div)>/iu', '', $t);
    $t = preg_replace("/\n\s*\n+/", "\n", $t);
    return trim($t);
}

/**
 * Remove APENAS linhas duplicadas de valor do bloco Investimento.
 */
function removerValorDuplicadoDoInvestimento($texto)
{
    if (empty($texto) || !is_string($texto)) return $texto;
    $t = $texto;
    $t = preg_replace('/<(?:p|div)[^>]*>\s*R\$\s*[\d\.,]+\s*\([^)]*[Rr]eais\)\s*<\/(?:p|div)>/iu', '', $t);
    $t = preg_replace('/<(?:p|div)[^>]*>\s*\([^)]*[Rr]eais\)\s*<\/(?:p|div)>/iu', '', $t);
    $t = preg_replace('/^[\r\n\s]*R\$\s*[\d\.,]+\s*\([^)]*[Rr]eais\)[\r\n\s]*/imu', '', $t);
    $t = preg_replace('/^[\r\n\s]*R\$\s*[\d\.,]+[\r\n\s]*/imu', '', $t);
    $t = preg_replace('/^[\r\n\s]*\([^)]*[Rr]eais\)[\r\n\s]*/imu', '', $t);
    $t = preg_replace('/\$\{ValorProposta\}\s*\(\$\{ValorExtenso\}\)/u', '', $t);
    $t = preg_replace("/\n\s*\n+/", "\n", $t);
    return trim($t);
}

// 1. Carregamento Unificado de Dados
$repo = new PropostaRepository();
$dados = $_POST;

if (empty($dados) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $dados = $repo->buscarPorId($id);
    if (!$dados) die("Proposta não encontrada.");
}

// 2. Dados da Empresa e Lookup
$idUsuarioRef = $_SESSION['usuario_id'] ?? ($dados['id_criador'] ?? 0);
$lookup = $repo->getAllLookupData($idUsuarioRef);
$empresa = $lookup['empresa'];

// Logo Logic
$logo = 'assets/logo_sgt.png';
if (!empty($empresa['logo_caminho']) && file_exists($empresa['logo_caminho'])) {
    $logo = $empresa['logo_caminho'];
}

// 3. Mapeamento de Variáveis (Flattening)
$vars = [
    // ── Cliente (campos reais do banco com sufixo _salvo) ──────────────
    'nome_cliente_salvo' => $dados['nome_cliente_salvo'] ?? '',
    'email_salvo'        => $dados['email_salvo']        ?? '',
    'telefone_salvo'     => $dados['telefone_salvo']     ?? '',
    'celular_salvo'      => $dados['celular_salvo']      ?? '',
    'whatsapp_salvo'     => $dados['whatsapp_salvo']     ?? ($dados['celular_salvo'] ?? ''),
    'empresa_cliente_salvo' => $dados['empresa_cliente_salvo'] ?? '',

    // Aliases curtos usados nos templates DOCX
    'nome'      => $dados['nome_cliente_salvo'] ?? '',
    'nome_cliente' => $dados['nome_cliente_salvo'] ?? '',
    'email'     => $dados['email_salvo']     ?? '',
    'telefone'  => $dados['telefone_salvo']  ?? '',
    'celular'   => $dados['celular_salvo']   ?? '',
    'whatsapp'  => $dados['whatsapp_salvo']  ?? ($empresa['Whatsapp'] ?? ''),

    // ── Proposta ────────────────────────────────────────────────────────
    'numero_proposta' => $dados['numero_proposta'] ?? '000/0000',

    // ── Obra ────────────────────────────────────────────────────────────
    'endereco_obra' => $dados['endereco_obra'] ?? '',
    'bairro_obra'   => $dados['bairro_obra']   ?? '',
    'cidade_obra'   => $dados['cidade_obra']   ?? '',
    'estado_obra'   => $dados['estado_obra']   ?? '',
    'area_obra'     => $dados['area_obra']     ?? '',
    'unidade_area'  => $dados['unidade_area']  ?? 'm²',
    'tipo_levantamento' => $dados['tipo_levantamento'] ?? '',
    // Aliases curtos
    'endereco'  => $dados['endereco_obra'] ?? '',
    'bairro'    => $dados['bairro_obra']   ?? '',
    'cidade'    => $dados['cidade_obra']   ?? ($empresa['Cidade'] ?? 'Belo Horizonte'),
    'estado'    => $dados['estado_obra']   ?? '',
    'area'      => $dados['area_obra']     ?? '',

    // ── Técnico ─────────────────────────────────────────────────────────
    'finalidade'     => $dados['finalidade']    ?? '',
    'escopo_servico' => isset($dados['escopo_content']) ? nl2br($dados['escopo_content']) : '',

    // ── Equipamentos ────────────────────────────────────────────────────
    'Veiculo'        => $dados['marca_veiculo']        ?? '',
    'Estacao_Total'  => $dados['marca_estacao_total']  ?? '',
    'GPS'            => $dados['marca_gps']            ?? '',
    'Drone'          => $dados['marca_drone']          ?? '',
    'Softwares'      => $dados['softwares_content']    ?? 'Softwares de Processamento de Precisão',

    // ── Financeiro (campos reais do banco) ──────────────────────────────
    'ValorProposta'  => formatarMoeda($dados['valor_final_proposta'] ?? 0),
    'ValorExtenso'   => $dados['Valor_proposta_extenso'] ?? valorPorExtenso($dados['valor_final_proposta'] ?? 0),
    'valor_proposta' => formatarMoeda($dados['valor_final_proposta'] ?? 0),
    'valor_extenso'  => $dados['Valor_proposta_extenso'] ?? '',
    'prazo_execucao' => $dados['prazo_execucao'] ?? '',
    'prazos_content' => $dados['prazos_content'] ?? ($dados['cronograma_content'] ?? ''),
    'dias_campo'     => $dados['dias_campo']     ?? '0',
    'dias_escritorio'=> $dados['dias_escritorio']?? '0',

    'mobilizacao_percentual'   => $dados['mobilizacao_percentual'] ?? '',
    'mobilizacao_valor'        => formatarMoeda($dados['mobilizacao_valor'] ?? 0),
    'restante_percentual'      => (100 - intval($dados['mobilizacao_percentual'] ?? 0)),
    'restante_valor'           => formatarMoeda($dados['restante_valor'] ?? 0),
    'mobilizacao_valor_total'  => formatarMoeda($dados['mobilizacao_valor'] ?? 0),
    'restante_valor_total'     => formatarMoeda($dados['restante_valor'] ?? 0),

    // ── Empresa Proponente (DadosEmpresa via $lookup) ───────────────────
    'Empresa'          => $empresa['Empresa']  ?? 'ELM Topografia',
    'empresa'          => $empresa['Empresa']  ?? 'ELM Topografia',
    'CNPJ'             => $empresa['CNPJ']     ?? '',
    'Banco'            => $empresa['Banco']    ?? '',
    'Agencia'          => $empresa['Agencia']  ?? '',
    'Conta'            => $empresa['Conta']    ?? '',
    'PIX'              => $empresa['PIX']      ?? '',
    'empresa_proponente_nome'     => $dados['empresa_proponente_nome']     ?? ($empresa['Empresa'] ?? ''),
    'empresa_proponente_cnpj'     => $dados['empresa_proponente_cnpj']     ?? ($empresa['CNPJ'] ?? ''),
    'empresa_proponente_cidade'   => $dados['empresa_proponente_cidade']   ?? ($empresa['Cidade'] ?? ''),
    'empresa_proponente_banco'    => $dados['empresa_proponente_banco']    ?? ($empresa['Banco'] ?? ''),
    'empresa_proponente_agencia'  => $dados['empresa_proponente_agencia']  ?? ($empresa['Agencia'] ?? ''),
    'empresa_proponente_conta'    => $dados['empresa_proponente_conta']    ?? ($empresa['Conta'] ?? ''),
    'empresa_proponente_pix'      => $dados['empresa_proponente_pix']      ?? ($empresa['PIX'] ?? ''),

    // ── Layout / Data ───────────────────────────────────────────────────
    'Cidade'       => $dados['empresa_proponente_cidade'] ?? ($empresa['Cidade'] ?? 'Belo Horizonte'),
    'DExrenso'     => dataPorExtenso(),
    'DataExtenso'  => dataPorExtenso(),
    'data_hoje'    => date('d/m/Y'),
    'logo'         => $logo,
    'logo_empresa' => $logo,

    // ── Variáveis Dinâmicas de Campo/Drone ──────────────────────────────
    'ClienteCidadeUF'  => ($dados['cidade_obra'] ?? '') . '-' . ($dados['estado_obra'] ?? ''),
    'TipoTerreno'      => $dados['tipo_terreno']    ?? '',
    'CoberturaVegetal' => $dados['cobertura_vegetal'] ?? '',
    'AcessoLocal'      => $dados['acesso_local']    ?? '',
    'RestricoesAereas' => $dados['restricoes_aereas'] ?? '',

    // ── Dados do Usuário ─────────────────────────────────────────────────
    'empresa_nome'     => $empresa['Empresa'] ?? 'ELM Topografia',
    'empresa_cnpj'     => $empresa['CNPJ']    ?? '',
    'empresa_endereco' => $empresa['Endereco']?? '',
    'empresa_cidade'   => $empresa['Cidade']  ?? '',
    'empresa_uf'       => $empresa['Estado']  ?? '',
    'empresa_telefone' => $empresa['Telefone']?? '',
    'empresa_whatsapp' => $empresa['Whatsapp']?? '',
    'usuario_nome'     => $_SESSION['nome_completo'] ?? $lookup['usuario']['nome_completo'] ?? 'Profissional SGT',
    'usuario_email'    => $_SESSION['usuario'] ?? $lookup['usuario']['usuario'] ?? '',
];


// 4. Carregador de Estrutura de Blocos (Caminho Absoluto)
require_once __DIR__ . '/src/ProposalArchitect/Infrastructure/DatabaseStructureLoader.php';
$structureLoader = new \ProposalArchitect\Infrastructure\DatabaseStructureLoader(ConnectionManager::get());
$idServico = intval($dados['id_servico'] ?? 0);
$blocosEstrutura = $structureLoader->loadActiveStructure($idServico);

?>
<?php
// === DETECÇÃO DE TEMA (LEGADO vs MODERNO) ===
require_once __DIR__ . '/classes/TemaProposta.php';

$dataCriacao = date('Y-m-d H:i:s'); // Default: Hoje (Moderno)

// Tenta recuperar data real
if (isset($dados['data_criacao']) && !empty($dados['data_criacao'])) {
    $dataCriacao = $dados['data_criacao'];
} elseif (!empty($dados['id_proposta'])) {
    // Busca no banco se não veio no POST
    $idProp = (int)$dados['id_proposta'];
    $resDate = $conn->query("SELECT data_criacao FROM Propostas WHERE id_proposta = $idProp");
    if ($resDate && $rowDate = $resDate->fetch_assoc()) {
        $dataCriacao = $rowDate['data_criacao'];
    }
}

$classeTema = TemaProposta::getClasse($dataCriacao);
// Link do CSS do tema (apenas se for clássico para economizar request, ou sempre se preferir)
// Link do CSS do tema (apenas se for clássico para economizar request, ou sempre se preferir)
$cssTemaPath = 'assets/css/tema_proposta.css';

// === MODO PILOTO (Drone - HTML Web/Bootstrap) ===
// Permite testar templates adicionando &piloto=drone ou &piloto=novo na URL
if (isset($_GET['piloto'])) {
    $modo = $_GET['piloto'];
    $caminhoPiloto = '';

    if ($modo === 'drone') {
        $caminhoPiloto = __DIR__ . '/proposta_drone_piloto.php'; // Versão Bootstrap Simples
    } elseif ($modo === 'novo') {
        $caminhoPiloto = __DIR__ . '/proposta_drone_novo.php';   // Versão Dashboard (Novo Pedido)
    }
    
    if ($caminhoPiloto && file_exists($caminhoPiloto)) {
        // Carrega o HTML executando o PHP (para cache busting do CSS funcionar)
        ob_start();
        include $caminhoPiloto;
        $conteudoTemplate = ob_get_clean();
        
        if ($conteudoTemplate) {
            // SUBSTITUIÇÃO ROBUSTA COM REGEX (Pega {{variavel}} ou {{ variavel }} ou {{  VARIAVEL  }})
            foreach ($vars as $chave => $valor) {
                // Previne erro se valor for array
                if (is_array($valor) || is_object($valor)) continue;
                
                // Regex: {{ (espaços opcionais) chave (espaços opcionais) }}
                // Ignora maiúsculas/minúsculas na chave para facilitar
                $pattern = '/\{\{\s*' . preg_quote($chave, '/') . '\s*\}\}/i';
                $conteudoTemplate = preg_replace($pattern, strval($valor), $conteudoTemplate);
            }
            
            // Limpa variáveis não encontradas para não mostrar lixo na tela
            $conteudoTemplate = preg_replace('/\{\{\s*.*?\s*\}\}/', '', $conteudoTemplate);
            
            // Injeta Bootstrap se não tiver (Somente para o piloto simples antigo)
            if ($modo === 'drone' && strpos($conteudoTemplate, 'bootstrap') === false) {
                 $bootstrapLink = '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">';
                 $conteudoTemplate = str_replace('</head>', $bootstrapLink . "\n</head>", $conteudoTemplate);
            }
    
            echo $conteudoTemplate;
            exit; // IMPORTANTE: Para aqui
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Proposta <?= str_replace('/', '-', $vars['numero_proposta']) ?> - <?= $vars['nome_cliente_salvo'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- CSS TEMA LEGADO (Se necessário) -->
    <link rel="stylesheet" href="<?= $cssTemaPath ?>">

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
            background: transparent;
            color: var(--brand);
            border: 1px solid var(--brand);
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
    <!-- Font Awesome removido para evitar warning -->
</head>

<body class="<?= $classeTema ?>">

    <!-- Barra de Ferramentas Flutuante -->
    <div class="no-print" style="position: fixed; bottom: 30px; right: 30px; display: flex; flex-direction: column; gap: 12px; z-index: 9999; font-family: 'Inter', sans-serif;">
        <a href="painel.php" style="background: #1e293b; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; box-shadow: 0 4px 15px rgba(0,0,0,0.3); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="ph ph-arrow-left"></i> Voltar ao Painel
        </a>
        <a href="editor_dinamico.php?id=<?= $id ?>" style="background: #f97316; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; box-shadow: 0 4px 15px rgba(249,115,22,0.3); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="ph ph-pencil-simple"></i> Editar Proposta
        </a>
        <button onclick="window.print()" style="background: var(--brand); color: white; padding: 12px 20px; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; box-shadow: 0 4px 15px rgba(180, 95, 6, 0.3); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="ph ph-printer"></i> Imprimir / PDF
        </button>
    </div>

    <div class="proposta-wrapper <?= $classeTema ?>">
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
            /**
             * LOOP DINÂMICO DE RENDERIZAÇÃO (Universo SGT - 14 Pontos)
             */
            function renderConteudoBloco($slug, $titulo, $conteudo, $vars, $dados)
            {
                // 1. Substitui Variáveis
                $html = substituir($conteudo, $vars);
                
                // 2. Formatação Premium (Markdown Simples para HTML)
                if (strpos($html, '<p') === false && strpos($html, '<ul') === false) {
                    // Negritos (Markdown: **texto**)
                    $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
                    // Listas (Markdown: - item)
                    $html = preg_replace('/^- (.*)/m', '<li>$1</li>', $html);
                    if (strpos($html, '<li>') !== false) {
                        $html = "<ul style='padding-left: 20px; list-style-type: disc; margin-bottom: 20px;'>" . $html . "</ul>";
                        $html = str_replace("</li>\n", "</li>", $html); // Limpa quebras extras em listas
                    } else {
                        $html = nl2br($html);
                    }
                }

                // 3. Limpezas Adicionais
                $html = html_entity_decode($html);
                $html = str_replace(["\xc2\xa0", "&nbsp;"], ' ', $html);
                $html = trim($html);

                // 4. Lógica Especializada por Slug
                echo "<div class='bloco-secao' id='block-{$slug}'>";
                
                // Título do Bloco (Exceto cabeçalho/rodape/assinatura que geralmente são visuais)
                $ignoreTitles = ['cabecalho', 'rodape', 'assinatura', 'dados_cliente', 'local_obra'];
                if (!in_array($slug, $ignoreTitles)) {
                    echo "<h2>{$titulo}</h2>";
                }

                // Renderização de Conteúdo Base
                echo $html;

                // --- COMPONENTES ESPECIALIZADOS (Injeção de Tabelas/Gráficos) ---

                // Ponto 8: Equipamentos Previstos (Injeção de Lista Estruturada se vazio)
                if ($slug === 'equipamentos' && strlen(strip_tags($html)) < 10) {
                    echo "<ul class='equip-list' style='padding-left: 40px; list-style-type: disc; margin-bottom: 20px; font-family: \"Inter\", sans-serif; font-size: 14px; color: #334155;'>";
                    if (!empty($vars['GPS'])) echo "<li style='margin-bottom: 5px;'><strong>Receptor GNSS:</strong> {$vars['GPS']} (Precisão Centimétrica)</li>";
                    if (!empty($vars['Estacao_Total'])) echo "<li style='margin-bottom: 5px;'><strong>Estação Total:</strong> {$vars['Estacao_Total']}</li>";
                    if (!empty($vars['Drone'])) echo "<li style='margin-bottom: 5px;'><strong>Drone:</strong> {$vars['Drone']}</li>";
                    echo "<li style='margin-bottom: 5px;'><strong>Softwares:</strong> {$vars['Softwares']}</li>";
                    echo "</ul>";
                }

                // Ponto 9: Prazos / Cronograma (Injeção de Tabela)
                if ($slug === 'prazos') {
                    $obsPrazos = $vars['prazos_content'] ?? '';
                    if (!empty($obsPrazos) && strlen(strip_tags($obsPrazos)) > 5) {
                        echo "<div style='margin-bottom: 12px; font-style: italic; color: var(--text-muted);'>" . nl2br($obsPrazos) . "</div>";
                    }

                    $prazoTotal = $vars['prazo_execucao'] ?: (intval($vars['dias_campo'] ?? 0) + intval($vars['dias_escritorio'] ?? 0)) . ' dias úteis';
                    echo "<table>";
                    echo "<tr><th>Etapa</th><th>Descrição</th><th>Prazo Estimado</th></tr>";
                    echo "<tr><td><strong>1. Campo</strong></td><td>Levantamento, voo ou rastreio geodésico</td><td>" . ($vars['dias_campo'] ?: 'A confirmar') . " dias</td></tr>";
                    echo "<tr><td><strong>2. Escritório</strong></td><td>Processamento, desenho e relatórios</td><td>" . ($vars['dias_escritorio'] ?: 'A confirmar') . " dias</td></tr>";
                    echo "<tr style='border-top: 2px solid var(--brand); color: var(--brand); font-weight: 700;'><td colspan='2'>TOTAL ESTIMADO</td><td>{$prazoTotal}</td></tr>";
                    echo "</table>";
                }

                // Ponto 10: Investimento (Invest Box Premium)
                if ($slug === 'investimento') {
                    echo "<div class='invest-box'>";
                    echo "<div class='invest-label'>Valor Total da Proposta</div>";
                    echo "<div class='invest-value'>{$vars['ValorProposta']}</div>";
                    echo "<div class='invest-extenso'>({$vars['ValorExtenso']})</div>";
                    echo "</div>";
                }

                // Ponto 11: Condições Pagamento (Tabela Financeira)
                if ($slug === 'condicoes_pagamento' && strlen(strip_tags($html)) < 10) {
                    echo "<table>";
                    echo "<tr><td>Etapa</td><td style='text-align:center;'>%</td><td style='text-align:right;'>Valor</td></tr>";
                    echo "<tr><td>Mobilização (Aceite da Proposta)</td><td style='text-align:center;'>" . number_format((float)$vars['mobilizacao_percentual'], 0) . "%</td><td style='text-align:right;'><strong>{$vars['mobilizacao_valor_total']}</strong></td></tr>";
                    echo "<tr><td>Entrega Final</td><td style='text-align:center;'>" . number_format((float)$vars['restante_percentual'], 0) . "%</td><td style='text-align:right;'><strong>{$vars['restante_valor_total']}</strong></td></tr>";
                    echo "</table>";
                }

                // Ponto 12: Dados Bancários (Info Card Verde)
                if ($slug === 'dados_bancarios' && strlen(strip_tags($html)) < 10) {
                    echo "<div class='info-card' style='border-left-color: var(--accent-green);'>";
                    echo "<p><span class='label'>Banco:</span> {$vars['Banco']} &nbsp;|&nbsp; <span class='label'>Agência:</span> {$vars['Agencia']}</p>";
                    echo "<p><span class='label'>Conta:</span> {$vars['Conta']} &nbsp;|&nbsp; <span class='label'>PIX:</span> {$vars['PIX']}</p>";
                    echo "</div>";
                }

                echo "</div>"; // Fim bloco-secao
            }

            // --- EXECUÇÃO DO LOOP DINÂMICO ---
            foreach ($blocosEstrutura as $def) {
                // Recupera conteúdo salvo para este bloco na proposta
                $slug = $def->id;
                $conteudoSalvo = $dados[$slug . '_content'] ?? $def->defaultContent;
                
                // Pular blocos de layout que renderizamos fora do loop principal de texto
                if ($slug === 'cabecalho' || $slug === 'rodape' || $slug === 'assinatura') continue;
                
                // Se for dados do cliente ou obra, já renderizamos lá em cima de forma fixa, 
                // para manter o design de "Info Cards".
                if ($slug === 'dados_cliente' || $slug === 'local_obra') continue;

                renderConteudoBloco($slug, $def->name, $conteudoSalvo, $vars, $dados);
            }
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

</div> <!-- Fim proposta-wrapper -->


</body>

</html>