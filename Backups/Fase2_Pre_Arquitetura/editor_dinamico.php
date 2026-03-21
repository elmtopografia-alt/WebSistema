<?php
/**
 * EDITOR DINÂMICO DE PROPOSTAS V2.1 (Avançado + SGT Theme)
 * 
 * Restauração da Interface de Edição (V1) com Estética SGT Glassmorphism.
 * Permite editar blocos de texto dinâmicos e salvar rascunhos.
 */

// 1. INICIALIZAÇÃO E SEGURANÇA
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();

// ========== DEBUG KIMI (Adaptado para Windows) ==========
$logDebugStr = [
    'arquivo' => 'editor_dinamico.php',
    'hora' => date('Y-m-d H:i:s'),
    'get_id' => $_GET['id'] ?? 'NÃO RECEBIDO',
    'get_todos' => $_GET,
    'session_id' => $_SESSION['id_proposta_ativa'] ?? 'NÃO TEM NA SESSÃO'
];
file_put_contents(__DIR__ . '/debug_editor.log', json_encode($logDebugStr, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
// ========== FIM DEBUG ==========
require_once 'vendor/autoload.php';
require_once 'db.php'; 

use ProposalArchitect\Infrastructure\HierarchyTreeBuilder;
use ProposalArchitect\Infrastructure\DatabaseStructureLoader;

// =====================================================
// 2. FUNÇÕES AUXILIARES E FORMATAÇÃO
// =====================================================

/**
 * Formata um valor numérico como moeda brasileira
 */
function formatarMoeda($valor)
{
    if (empty($valor) || $valor === 'R$ 0,00' || $valor === '0' || $valor === 0) {
        return 'R$ 0,00';
    }
    if (is_string($valor) && strpos($valor, 'R$') === 0) {
        return $valor;
    }
    if (is_string($valor)) {
        $valor = str_replace(['R$', 'R$ ', ' '], '', $valor);
        $valor = trim($valor);
        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
    }
    $numero = floatval($valor);
    return 'R$ ' . number_format($numero, 2, ',', '.');
}

/**
 * Converte valor para extenso
 */
function valorPorExtenso($valor)
{
    if (is_string($valor)) {
        $valor = str_replace(['R$', 'R$ ', ' '], '', $valor);
        $valor = trim($valor);
        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
    }
    $valor = floatval($valor);
    if ($valor == 0) return 'zero reais';

    $fmt = new NumberFormatter("pt_BR", NumberFormatter::SPELLOUT);
    return mb_strtoupper($fmt->format($valor) . " reais");
}

/**
 * Motor de Substituição de Variáveis
 */
function substituirVariaveis($texto, $variaveis)
{
    if (empty($texto)) return '';
    foreach ($variaveis as $chave => $valor) {
        if (!is_array($valor)) {
            $texto = str_replace('${' . $chave . '}', $valor, $texto);
        }
    }
    return $texto;
}

/**
 * Mapeia dados brutos para o mapa de variáveis do template
 */
function getVariableMap($data, $conn) {
    $map = $data;
    $map['ValorProposta'] = formatarMoeda($data['valor_final_proposta'] ?? 0);
    $map['ValorExtenso'] = valorPorExtenso($data['valor_final_proposta'] ?? 0);
    $map['DataExtenso'] = dataPorExtenso();
    
    // Novas chaves para Área e Unidade
    $map['unidade'] = $data['unidade_area'] ?? 'm²';
    $map['AreaEstimada'] = ($data['area_obra'] ?? '0') . ' ' . ($data['unidade_area'] ?? 'm²');
    $map['area_formatada'] = $map['AreaEstimada'];
    
    // Dados da Empresa (Fallback se não vier no data)
    if (empty($data['Empresa'])) {
        $emp = $conn->query("SELECT * FROM DadosEmpresa LIMIT 1")->fetch_assoc();
        if ($emp) {
            $map['Empresa'] = $emp['Empresa'];
            $map['CNPJ'] = $emp['CNPJ'];
            $map['whatsapp'] = $emp['Whatsapp'];
            $map['logo_empresa'] = 'assets/' . ($emp['Logo'] ?? 'logo_sgt.png');
        }
    }
    return $map;
}

function dataPorExtenso($data = null)
{
    $meses = [1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril', 5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto', 9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'];
    if ($data === null) $data = time();
    elseif (is_string($data)) $data = strtotime($data);
    $dia = date('d', $data);
    $mes = $meses[intval(date('m', $data))];
    $ano = date('Y', $data);
    return "{$dia} de {$mes} de {$ano}";
}

// 3. CARREGAMENTO DE DADOS COM VALIDAÇÃO
$id_prop = (int)($_GET['id'] ?? 0);

// Fallback para sessão se não vier via GET
if (!$id_prop && isset($_SESSION['id_proposta_ativa'])) {
    $id_prop = (int)$_SESSION['id_proposta_ativa'];
}

/**
 * Exibe página de erro elegante se algo falhar
 */
function mostrarErroEditor($titulo, $mensagem, $fatal = false, $detalhes = []) {
    $corPrimaria = $fatal ? '#ef4444' : '#f97316';
    $corBg = $fatal ? 'rgba(239, 68, 68, 0.15)' : 'rgba(249, 115, 22, 0.15)';
    $icone = $fatal ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
    
    $detalhesHtml = '';
    if (!empty($detalhes)) {
        $itens = implode('</li><li style="padding:8px 0;color:#cbd5e1;border-bottom:1px solid rgba(255,255,255,0.05);">', $detalhes);
        $detalhesHtml = "
        <div style='background:rgba(0,0,0,0.3);border-radius:12px;padding:20px;margin:24px 0;border:1px solid rgba(255,255,255,0.1);'>
            <h3 style='color:#94a3b8;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:16px;'>
                Detalhes técnicos:
            </h3>
            <ul style='list-style:none;padding:0;margin:0;font-size:13px;'>
                <li style='padding:8px 0;color:#cbd5e1;border-bottom:1px solid rgba(255,255,255,0.05);'>{$itens}</li>
            </ul>
        </div>";
    }
    
    $idPropStr = $GLOBALS['id_prop'] ?? 'N/A';
    $acoes = $fatal ? "
        <a href='painel.php' style='display:inline-flex;align-items:center;gap:8px;padding:14px 28px;background:linear-gradient(135deg, {$corPrimaria} 0%, #ea580c 100%);color:white;text-decoration:none;border-radius:12px;font-weight:600;font-size:14px;box-shadow:0 4px 14px rgba(249,115,22,0.4);transition:all 0.2s;'>
            ← Ir para o Painel
        </a>
    " : "
        <div style='display:flex;gap:12px;flex-wrap:wrap;justify-content:center;'>
            <a href='criar_proposta_dinamica.php?id_proposta={$idPropStr}' style='display:inline-flex;align-items:center;gap:8px;padding:14px 28px;background:linear-gradient(135deg, {$corPrimaria} 0%, #ea580c 100%);color:white;text-decoration:none;border-radius:12px;font-weight:600;font-size:14px;box-shadow:0 4px 14px rgba(249,115,22,0.4);'>
                ← Completar Dados
            </a>
            <a href='painel.php' style='display:inline-flex;align-items:center;gap:8px;padding:14px 28px;background:rgba(255,255,255,0.05);color:#e2e8f0;text-decoration:none;border-radius:12px;font-weight:600;font-size:14px;border:1px solid rgba(255,255,255,0.1);'>
                Ir para Painel
            </a>
        </div>
    ";
    
    echo "<!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>{$titulo} - SGT Propostas</title>
        <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap' rel='stylesheet'>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                background: linear-gradient(135deg, #0a0f1a 0%, #1a1f2e 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                color: #f8fafc;
            }
            .container {
                background: rgba(17, 24, 39, 0.95);
                border: 1px solid {$corPrimaria}40;
                border-radius: 24px;
                padding: 48px;
                max-width: 560px;
                width: 100%;
                text-align: center;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            }
            .icon-wrapper {
                width: 72px;
                height: 72px;
                background: {$corBg};
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 28px;
                border: 2px solid {$corPrimaria}60;
            }
            .icon-wrapper svg {
                width: 36px;
                height: 36px;
                color: {$corPrimaria};
            }
            h1 {
                font-size: 24px;
                font-weight: 700;
                margin-bottom: 16px;
                line-height: 1.3;
            }
            .mensagem {
                color: #94a3b8;
                font-size: 15px;
                line-height: 1.6;
                margin-bottom: 24px;
            }
            .proposta-id {
                display: inline-block;
                background: rgba(255,255,255,0.05);
                padding: 6px 14px;
                border-radius: 8px;
                font-family: monospace;
                font-size: 12px;
                color: {$corPrimaria};
                margin-bottom: 24px;
                border: 1px solid rgba(255,255,255,0.1);
            }
            .dica {
                background: rgba(59, 130, 246, 0.1);
                border: 1px solid rgba(59, 130, 246, 0.3);
                border-radius: 12px;
                padding: 16px;
                margin-top: 32px;
                text-align: left;
            }
            .dica h4 {
                color: #60a5fa;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                margin-bottom: 8px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .dica p {
                color: #93c5fd;
                font-size: 13px;
                line-height: 1.5;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='icon-wrapper'>
                <svg fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='{$icone}'/>
                </svg>
            </div>
            <h1>{$titulo}</h1>
            <p class='mensagem'>{$mensagem}</p>
            <div class='proposta-id'>Proposta #{$idPropStr}</div>
            {$detalhesHtml}
            {$acoes}
            <div class='dica'>
                <h4><svg width='14' height='14' fill='currentColor' viewBox='0 0 16 16'><path d='M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z'/></svg> Dica</h4>
                <p>Verifique se todos os campos obrigatórios foram preenchidos na tela de criação de proposta antes de acessar o editor avançado.</p>
            </div>
        </div>
    </body>
    </html>";
    exit;
}

if (!$id_prop) {
    mostrarErroEditor("Acesso Inválido", "Não foi possível identificar o ID da proposta para edição.", true);
}

// Guarda na sessão para garantir consistência
$_SESSION['id_proposta_ativa'] = $id_prop;

$conn = Database::getProd();

// Carrega dados da proposta
$sql = "SELECT p.*, c.nome_cliente, c.email as email_salvo, c.telefone as telefone_salvo, 
        c.celular as celular_salvo, ts.nome as nome_servico 
        FROM Propostas p 
        LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
        LEFT JOIN Tipo_Servicos ts ON p.id_servico = ts.id_servico
        WHERE p.id_proposta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_prop);
$stmt->execute();
$incomingData = $stmt->get_result()->fetch_assoc();

if (!$incomingData) {
    mostrarErroEditor("Proposta não encontrada", "Não conseguimos localizar os dados desta proposta no banco de dados.", true, ["ID: {$id_prop}"]);
}

// Validação de dados mínimos no carregamento
$dadosFaltantes = [];
if (empty($incomingData['id_cliente'])) $dadosFaltantes[] = "Cliente não selecionado";
if (empty($incomingData['id_servico'])) $dadosFaltantes[] = "Tipo de serviço não definido";

if (!empty($dadosFaltantes)) {
    mostrarErroEditor("Dados Incompletos", "Esta proposta ainda não possui todas as informações básicas salvas.", false, $dadosFaltantes);
}

// Carrega conteúdos personalizados
$resContent = $conn->query("SELECT block_id, conteudo_texto FROM Proposta_Conteudo_Personalizado WHERE id_proposta = $id_prop");
while ($row = $resContent->fetch_assoc()) {
    $incomingData[$row['block_id'] . '_content'] = $row['conteudo_texto'];
}

// Carrega Estrutura (Árvore de Blocos)
try {
    $loader = new DatabaseStructureLoader($conn);
    $model = $loader->getVirtualModel();
    $treeBuilder = new HierarchyTreeBuilder();
    $structure = $treeBuilder->build($model);
    $metadata = $model->getModelMetadata();
} catch (Exception $e) {
    mostrarErroEditor("Erro de Estrutura", "Houve uma falha ao carregar os blocos da proposta.", true, [$e->getMessage()]);
}

$variaveis = getVariableMap($incomingData, $conn);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor SGT | <?= $metadata['name'] ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Exo+2:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        background: '#0a0f1a',
                        surface: '#111827',
                        primary: '#f97316',
                        secondary: '#3b82f6',
                        tech: '#0ea5e9',
                        financial: '#10b981',
                        presentation: '#8b5cf6',
                        legal: '#94a3b8'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Exo 2', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #0a0f1a;
            color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }
        .glass {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        .block-card:hover {
            border-color: rgba(249, 115, 22, 0.4);
            transform: translateY(-2px);
        }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0a0f1a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        
        .tox-tinymce {
            border: 1px solid rgba(255,255,255,0.1) !important;
            border-radius: 8px !important;
        }
    </style>
</head>

<body class="h-screen flex flex-col antialiased">

    <!-- Header -->
    <header class="glass border-b border-white/10 px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <div class="bg-primary/20 text-primary p-2 rounded-xl border border-primary/30">
                <i class="bi bi-pencil-square text-xl"></i>
            </div>
            <div>
                <h1 class="font-display font-bold text-white text-lg"><?= $metadata['name'] ?></h1>
                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Editor Avançado SGT • ProposalArchitect™</p>
            </div>
        </div>
        <div class="flex gap-3">
            <button type="button" onclick="salvarRascunho()" class="px-4 py-2 text-sm font-bold text-slate-300 bg-white/5 border border-white/10 rounded-xl hover:bg-white/10 transition-all flex items-center gap-2">
                <i class="bi bi-cloud-arrow-up"></i>
                Salvar Rascunho
            </button>
            <button type="button" onclick="submitForm('html')" class="px-4 py-2 text-sm font-bold text-white bg-secondary/80 rounded-xl hover:bg-secondary shadow-lg shadow-secondary/20 transition-all flex items-center gap-2">
                <i class="bi bi-eye"></i>
                Visualizar Web
            </button>
            <button type="button" onclick="submitForm('docx')" class="px-5 py-2 text-sm font-bold text-white bg-primary rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                <i class="bi bi-file-earmark-word"></i>
                Gerar Word
            </button>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">

        <!-- Sidebar Navigation -->
        <aside class="w-64 glass border-r border-white/10 overflow-y-auto hidden md:block">
            <div class="p-5">
                <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">Estrutura da Proposta</h3>
                <nav class="space-y-1">
                    <?php
                    function renderNav($nodes, $depth = 0) {
                        foreach ($nodes as $node) {
                            $padding = $depth * 1.25;
                            $activeClass = $depth === 0 ? 'text-slate-300 font-semibold' : 'text-slate-500 text-xs';
                            $iconColorMap = [
                                'technical' => 'text-tech',
                                'financial' => 'text-financial',
                                'presentation' => 'text-presentation',
                                'legal' => 'text-legal'
                            ];
                            $color = $iconColorMap[$node['category']] ?? 'text-slate-400';
                            
                            echo "<a href='#block-{$node['id']}' class='flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/5 transition-all {$activeClass}' style='padding-left: calc(0.75rem + {$padding}rem)'>";
                            echo "<div class='w-1.5 h-1.5 rounded-full {$color} bg-current shadow-[0_0_8px_currentColor]'></div>";
                            echo "<span>{$node['title']}</span>";
                            echo "</a>";

                            if (!empty($node['children'])) renderNav($node['children'], $depth + 1);
                        }
                    }
                    renderNav($structure);
                    ?>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-8 scroll-smooth" id="main-scroll">
            <div class="max-w-3xl mx-auto space-y-10 pb-20">

                <form id="formProposta" method="POST" action="salvar_proposta.php">
                    <input type="hidden" name="id_proposta" value="<?= $id_prop ?>">
                    <input type="hidden" name="id_proposta_original" value="<?= $id_prop ?>">
                    <input type="hidden" name="formato_saida" id="inputFormatoSaida" value="html">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <?php
                    function renderFormBlocks($nodes, $model, $incomingData, $variaveis) {
                        foreach ($nodes as $node) {
                            $slug = $node['id'];
                            $borderColors = [
                                'presentation' => 'border-l-presentation',
                                'technical' => 'border-l-tech',
                                'financial' => 'border-l-financial',
                                'legal' => 'border-l-legal'
                            ];
                            $borderColor = $borderColors[$node['category']] ?? 'border-l-slate-700';

                            // Resolução de Conteúdo
                            $defaultContent = substituirVariaveis($node['default_content'] ?? '', $variaveis);
                            $userValue = $incomingData[$slug . '_content'] ?? ($incomingData[$slug] ?? '');
                            $finalContent = (!empty($userValue)) ? $userValue : $defaultContent;

                            echo "<div id='block-{$slug}' class='glass-card rounded-2xl border-l-4 {$borderColor} block-card p-6 scroll-mt-24 mb-8'>";
                            
                            echo "<div class='flex justify-between items-center mb-6'>";
                            echo "<div class='flex items-center gap-3'><h3 class='text-lg font-bold text-white font-display'>{$node['title']}</h3><span class='text-[9px] bg-white/5 text-slate-400 px-2 py-0.5 rounded-md uppercase border border-white/5 font-bold tracking-tighter'>{$node['category']}</span></div>";
                            if (!empty($node['required'])) echo "<span class='text-[10px] text-primary bg-primary/10 px-2 py-0.5 rounded-full border border-primary/20 font-bold uppercase'>Obrigatório</span>";
                            echo "</div>";

                            echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-5'>";

                            // Switch de Renderização
                            switch ($slug) {
                                case 'cabecalho':
                                    echo '<div class="col-span-2 space-y-4">';
                                    echo '<div class="bg-white/5 p-4 rounded-xl border border-white/10 flex items-center justify-between">';
                                    echo '<div class="flex items-center gap-4"><img src="' . htmlspecialchars($variaveis['logo_empresa']) . '" class="h-10 object-contain opacity-80" onerror="this.src=\'assets/logo_sgt.png\'"><div class="text-sm font-bold text-white">LOGO SELECIONADA</div></div>';
                                    echo '<div class="text-right text-xs"><div class="text-slate-500">PROPOSTA Nº</div><div class="text-primary font-bold font-mono">' . $variaveis['numero_proposta'] . '</div></div>';
                                    echo '</div></div>';
                                    break;

                                case 'dados_cliente':
                                    renderField('nome_cliente', 'Cliente', 'text', $variaveis['nome_cliente']);
                                    renderField('email_cliente', 'E-mail', 'email', $variaveis['email_salvo']);
                                    renderField('telefone_cliente', 'Telefone', 'tel', $variaveis['telefone_salvo']);
                                    renderField('celular_cliente', 'WhatsApp', 'tel', $variaveis['celular_salvo']);
                                    break;

                                case 'local_obra':
                                    renderField('endereco_obra', 'Endereço Completo', 'text', $variaveis['endereco_obra'], true);
                                    renderField('bairro_obra', 'Bairro', 'text', $variaveis['bairro_obra']);
                                    renderField('cidade_obra', 'Cidade', 'text', $variaveis['cidade_obra']);
                                    echo "<div class='md:col-span-2'>";
                                    echo "<label class='block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5 ml-1'>Área e Unidade</label>";
                                    echo "<div class='flex items-stretch'>";
                                    echo "<input type='text' name='area' value='".htmlspecialchars($variaveis['area_obra'])."' class='flex-1 bg-white/5 border border-white/10 rounded-l-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary/50 text-sm transition-all' style='border-right: none;'>";
                                    echo "<select name='unidade_area' class='bg-white/5 border border-white/10 rounded-r-xl px-4 py-3 text-primary font-bold text-sm focus:outline-none focus:border-primary/50 transition-all' style='max-width: 100px;'>";
                                    foreach(['m²','ha','km²'] as $un) {
                                        $sel = ($variaveis['unidade'] == $un) ? 'selected' : '';
                                        echo "<option value='$un' $sel>$un</option>";
                                    }
                                    echo "</select></div>";
                                    echo "</div>";
                                    break;

                                case 'cronograma':
                                    echo '<div class="col-span-2 grid grid-cols-4 gap-3 mb-2">';
                                    renderStat('Dias Campo', $variaveis['dias_campo'], 'text-secondary');
                                    renderStat('Dias Escritório', $variaveis['dias_escritorio'], 'text-secondary');
                                    renderStat('Área Total', $variaveis['area_formatada'], 'text-tech');
                                    renderStat('Prazo Total', $variaveis['prazo_execucao'], 'text-primary');
                                    echo '</div>';
                                    renderField('dias_campo', 'Dias de Campo', 'number', $variaveis['dias_campo']);
                                    renderField('dias_escritorio', 'Dias Escritório', 'number', $variaveis['dias_escritorio']);
                                    if (!empty($finalContent)) renderField($slug . '_content', 'Observações', 'textarea', $finalContent, true);
                                    break;

                                case 'investimento':
                                    echo '<div class="col-span-2 glass p-4 rounded-xl border border-green-500/20 mb-2">';
                                    renderStat('Valor Total da Proposta', $variaveis['ValorProposta'], 'text-financial !text-2xl');
                                    echo '</div>';
                                    if (!empty($finalContent)) renderField($slug . '_content', 'Texto Investimento', 'textarea', $finalContent, true);
                                    break;

                                default:
                                    // Bloco com TinyMCE
                                    $fieldName = $slug . '_content';
                                    renderField($fieldName, 'Conteúdo Editável', 'textarea', $finalContent, true);
                                    break;
                            }

                            echo "</div></div>"; // Fim Grid e Card

                            if (!empty($node['children'])) {
                                echo "<div class='pl-8 border-l-2 border-white/5 space-y-6'>";
                                renderFormBlocks($node['children'], $model, $incomingData, $variaveis);
                                echo "</div>";
                            }
                        }
                    }

                    function renderField($name, $label, $type, $value, $full = false) {
                        $span = $full ? 'md:col-span-2' : '';
                        echo "<div class='{$span}'>";
                        echo "<label class='block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5 ml-1'>{$label}</label>";
                        $valSafe = htmlspecialchars($value);
                        if ($type === 'textarea') {
                            echo "<textarea name='{$name}' id='ed-{$name}' class='w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-300 focus:outline-none focus:border-primary/50 text-sm transition-all'>{$valSafe}</textarea>";
                        } else {
                            echo "<input type='{$type}' name='{$name}' value='{$valSafe}' class='w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary/50 text-sm transition-all'>";
                        }
                        echo "</div>";
                    }

                    function renderStat($label, $val, $color) {
                        echo "<div class='bg-white/5 p-3 rounded-xl border border-white/5 text-center'>";
                        echo "<div class='text-[9px] font-bold text-slate-500 uppercase mb-0.5'>{$label}</div>";
                        echo "<div class='text-lg font-bold {$color}'>{$val}</div>";
                        echo "</div>";
                    }

                    renderFormBlocks($structure, $model, $incomingData, $variaveis);
                    ?>
                </form>

            </div>
        </main>
    </div>

    <script>
        async function salvarRascunho() {
            tinymce.triggerSave();
            const btn = document.querySelector('button[onclick="salvarRascunho()"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> Salvando...';

            try {
                const formData = new FormData(document.getElementById('formProposta'));
                const response = await fetch('salvar_rascunho.php', { method: 'POST', body: formData });
                if (response.ok) alert('✅ Rascunho atualizado com sucesso!');
                else throw new Error('Falha no servidor');
            } catch (e) {
                alert('❌ Erro: ' + e.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Salvar Rascunho';
            }
        }

        async function submitForm(formato) {
            tinymce.triggerSave();
            const form = document.getElementById('formProposta');
            document.getElementById('inputFormatoSaida').value = formato;
            
            // Abre em nova aba para visualização
            form.target = '_blank';
            form.submit();
        }

        // Inicialização TinyMCE Dark
        document.addEventListener("DOMContentLoaded", function() {
            tinymce.init({
                selector: 'textarea',
                height: 350,
                skin: "oxide-dark",
                content_css: "dark",
                menubar: false,
                plugins: 'lists link code table wordcount',
                toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | removeformat | code',
                setup: function(editor) {
                    editor.on('change', function() { editor.save(); });
                },
                content_style: 'body { font-family:Inter,sans-serif; font-size:14px; background-color: #111827; color: #e2e8f0; }'
            });
        });
    </script>
</body>
</html>