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

// --- INTEGRAÇÃO DOCX ---
require_once 'renderizador_modelo_docx.php';
$docxRenderer = new RenderizadorModeloDOCX($conn);
$modelosDisponiveis = $docxRenderer->listarModelos();
$modeloDocxAtivo = $_GET['modelo_docx'] ?? null;
// -----------------------

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
/**
 * Mapeia dados brutos para o mapa de variáveis do template
 */
function getVariableMap($data, $conn) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $id_criador = $_SESSION['usuario_id'] ?? 0;

    $map = $data;
    $map['ValorProposta'] = formatarMoeda($data['valor_final_proposta'] ?? 0);
    $map['ValorExtenso'] = valorPorExtenso($data['valor_final_proposta'] ?? 0);
    $map['DataExtenso'] = dataPorExtenso();
    
    // Novas chaves para Área e Unidade
    $map['unidade'] = $data['unidade_area'] ?? 'm²';
    $map['AreaEstimada'] = ($data['area_obra'] ?? '0') . ' ' . ($data['unidade_area'] ?? 'm²');
    $map['area_formatada'] = $map['AreaEstimada'];
    
    // Variáveis de Drone/Campo (Garantir Mapeamento)
    $map['TipoTerreno'] = $data['tipo_terreno'] ?? 'Não informado';
    $map['CoberturaVegetal'] = $data['cobertura_vegetal'] ?? 'Não informado';
    $map['AcessoLocal'] = $data['acesso_local'] ?? 'Não informado';
    $map['RestricoesAereas'] = $data['restricoes_aereas'] ?? 'Não informado';
    
    // Cidade/UF do Cliente (Obra)
    $map['ClienteCidadeUF'] = ($data['cidade_obra'] ?? '') . '-' . ($data['estado_obra'] ?? '');
    $map['ClienteBairro'] = $data['bairro_obra'] ?? ''; // Alias solicitado

    // Equipamentos (Aliases Capitalizados)
    $map['Drone'] = $data['drone'] ?? 'Não aplicável';
    $map['Veiculo'] = $data['veiculo'] ?? 'Não incluso';
    $map['Estacao_Total'] = $data['estacao_total'] ?? 'Não inclusa';
    $map['GPS'] = 'Par de Receptores GNSS RTK de Dupla Frequência'; // Valor Padrão SGT

    // Dados Bancários e Empresa (Prioridade: Sessão > Proposta > Geral)
    $emp = null;
    if ($id_criador > 0) {
        $emp = $conn->query("SELECT * FROM DadosEmpresa WHERE id_criador = $id_criador LIMIT 1")->fetch_assoc();
    }
    
    // Se não achou pelo criador logado, tenta pegar qualquer um (fallback demo)
    if (!$emp) {
        $emp = $conn->query("SELECT * FROM DadosEmpresa LIMIT 1")->fetch_assoc();
    }

    if ($emp) {
        // Força sobrescrita se o dado na proposta estiver vazio OU se quisermos garantir o atual
        $map['Empresa'] = $emp['Empresa'];
        $map['empresa'] = $emp['Empresa']; // Case insensitive support
        $map['CNPJ'] = $emp['CNPJ'];
        $map['whatsapp'] = $emp['Whatsapp'];
        $map['logo_empresa'] = 'assets/' . ($emp['Logo'] ?? 'logo_sgt.png');
        
        // Dados Bancários
        $map['Banco'] = $emp['Banco'] ?? '';
        $map['Agencia'] = $emp['Agencia'] ?? '';
        $map['Conta'] = $emp['Conta'] ?? '';
        $map['PIX'] = $emp['PIX'] ?? '';
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
            <a href='criar_proposta.php?id_proposta={$idPropStr}' style='display:inline-flex;align-items:center;gap:8px;padding:14px 28px;background:linear-gradient(135deg, {$corPrimaria} 0%, #ea580c 100%);color:white;text-decoration:none;border-radius:12px;font-weight:600;font-size:14px;box-shadow:0 4px 14px rgba(249,115,22,0.4);'>
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
    $serviceTypeId = isset($incomingData['id_servico']) ? (int)$incomingData['id_servico'] : 0;
    $loader = new DatabaseStructureLoader($conn);
    $model = $loader->getVirtualModel($serviceTypeId);
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
    <!-- CSS Tailwind estático (substituiu cdn.tailwindcss.com que causava loop com TinyMCE) -->
    <link rel="stylesheet" href="assets/css/editor-tailwind.css?v=<?= time() ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>


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
            <a href="painel.php" class="text-slate-400 hover:text-white hover:bg-white/5 p-2 rounded-xl transition-all mr-1" title="Voltar ao Painel">
                <i class="bi bi-arrow-left text-2xl"></i>
            </a>
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
            <a href="gerar_proposta_premium.php?id=<?= (int)$id_prop ?>" target="_blank" class="px-4 py-2 text-sm font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 rounded-xl hover:bg-emerald-500/20 transition-all flex items-center gap-2" title="Layout Premium (piloto)">
                <i class="bi bi-sparkles"></i>
                Piloto
            </a>
            <div class="dropdown">
                <button type="button" class="px-4 py-2 text-sm font-bold text-white bg-slate-800 border border-white/10 rounded-xl hover:bg-slate-700 transition-all flex items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown" id="dropdownModelos">
                    <i class="bi bi-file-earmark-richtext"></i>
                    <?= $modeloDocxAtivo ? str_replace('_', ' ', $modeloDocxAtivo) : 'Modelo Padrão' ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark bg-slate-900 border border-white/10 p-2 shadow-2xl" aria-labelledby="dropdownModelos">
                    <li><a class="dropdown-item rounded-lg py-2 hover:bg-white/5 active:bg-primary" href="?id=<?= $id_prop ?>">📂 Modelo Tradicional (SGT)</a></li>
                    <li><hr class="dropdown-divider border-white/5"></li>
                    <li class="px-3 py-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Modelos DOCX</li>
                    <?php if (empty($modelosDisponiveis)): ?>
                        <li class="px-3 py-2 text-xs italic text-slate-500">Nenhum modelo gerado</li>
                    <?php else: ?>
                        <?php foreach ($modelosDisponiveis as $mod): ?>
                            <li><a class="dropdown-item rounded-lg py-2 hover:bg-white/5 <?= ($modeloDocxAtivo === $mod['id']) ? 'bg-primary/20 text-primary' : '' ?>" href="?id=<?= $id_prop ?>&modelo_docx=<?= $mod['id'] ?>">📄 <?= $mod['nome'] ?></a></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider border-white/5"></li>
                    <li><a class="dropdown-item rounded-lg py-2 text-emerald-400 hover:bg-emerald-500/10" href="gerador_upload_docx.php" target="_blank"><i class="bi bi-plus-circle me-1"></i> Criar Novo do Word</a></li>
                </ul>
            </div>

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
                    <?php 
                    // SE HOUVER MODELO DOCX ATIVO, RENDERIZA CABEÇALHO DE VARIÁVEIS DO DOCX
                    if ($modeloDocxAtivo): 
                        $metadataDocx = $docxRenderer->obterMetadata($modeloDocxAtivo);
                    ?>
                        <div class="glass-card rounded-2xl border-l-4 border-l-emerald-500 p-6 mb-10 shadow-xl shadow-emerald-950/20">
                            <div class="flex justify-between items-center mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="bg-emerald-500/20 text-emerald-400 p-2 rounded-xl border border-emerald-500/30">
                                        <i class="bi bi-magic text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Modo Word Ativo: <?= str_replace('_', ' ', $modeloDocxAtivo) ?></h3>
                                        <p class="text-[10px] text-slate-500 uppercase tracking-widest">Variáveis detectadas no documento original preenchidas aqui</p>
                                    </div>
                                </div>
                                <span class="text-[10px] text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20 font-bold uppercase">Edição Direta</span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <?php 
                                // Lista apenas variáveis que NÃO são do sistema (as manuais)
                                $chavesSistema = require 'config_chaves_sistema.php';
                                foreach ($metadataDocx['variaveis'] as $var): 
                                    if (isset($chavesSistema[$var])) continue; // Pula as automáticas
                                    $label = ucwords(str_replace('_', ' ', $var));
                                    $valor = $incomingData[$var . '_content'] ?? $incomingData[$var] ?? '';
                                ?>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1"><?= $label ?></label>
                                        <input type="text" name="<?= $var ?>_content" value="<?= htmlspecialchars($valor) ?>" 
                                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-emerald-100 focus:outline-none focus:border-emerald-500/50 text-sm transition-all"
                                            placeholder="Valor para {<?= $var ?>}">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <input type="hidden" name="id_proposta" value="<?= $id_prop ?>">
                    <input type="hidden" name="id_proposta_original" value="<?= $id_prop ?>">
                    <input type="hidden" name="id_cliente" value="<?= $incomingData['id_cliente'] ?? '' ?>">
                    <input type="hidden" name="id_servico" value="<?= $incomingData['id_servico'] ?? '' ?>">
                    <input type="hidden" name="modelo_docx" value="<?= htmlspecialchars($modeloDocxAtivo ?? '') ?>">
                    <input type="hidden" name="formato_saida" id="inputFormatoSaida" value="html">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <?php
                    function renderFormBlocks($nodes, $model, $incomingData, $variaveis) {
                        foreach ($nodes as $node) {
                            $slug = $node['id'];
                            
                            // [REFATORAÇÃO] Filtra blocos duplicados ou removidos
                            if (in_array($slug, ['recursos_equipamentos', 'cronograma_drone', 'investimento-extra'])) continue;
                            $borderColors = [
                                'presentation' => 'border-l-presentation',
                                'technical' => 'border-l-tech',
                                'financial' => 'border-l-financial',
                                'legal' => 'border-l-legal'
                            ];
                            $borderColor = $borderColors[$node['category']] ?? 'border-l-slate-700';

                            // Resolução de Conteúdo
                            $defaultContent = $node['default_content'] ?? '';
                            $userValue = $incomingData[$slug . '_content'] ?? ($incomingData[$slug] ?? '');
                            
                            // Define conteúdo base (usuário > padrão)
                            $rawContent = (!empty($userValue)) ? $userValue : $defaultContent;
                            
                            // APLICA SUBSTITUIÇÃO DE VARIÁVEIS (FORÇADO)
                            // Isso garante que mesmo se o usuário salvou um rascunho com ${Variaveis}, elas sejam processadas ao abrir o editor.
                            $finalContent = substituirVariaveis($rawContent, $variaveis);

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
                                    renderField('estado_obra', 'Estado (UF)', 'text', $variaveis['estado_obra']);
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
                                    echo '<div class="col-span-2">';
                                    
                                    // Alerta
                                    echo '<div class="bg-amber-500/10 border border-amber-500/20 rounded-lg p-3 mb-4">
                                        <p class="text-xs text-amber-200">
                                            <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                                            O cumprimento dos prazos depende de condições climáticas favoráveis (ausência de chuva e ventos superiores a 30 km/h).
                                        </p>
                                    </div>';

                                    // Tabela Fixa
                                    echo '<div class="overflow-x-auto mb-4">
                                        <table class="w-full text-sm text-left text-slate-300">
                                            <thead class="text-xs text-slate-400 uppercase bg-white/5">
                                                <tr>
                                                    <th class="px-4 py-3 rounded-l-lg">Etapa</th>
                                                    <th class="px-4 py-3">Descrição</th>
                                                    <th class="px-4 py-3 rounded-r-lg">Prazo Estimado</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-white/5">
                                                <tr class="hover:bg-white/5">
                                                    <td class="px-4 py-3 font-medium text-white">1. Mobilização</td>
                                                    <td class="px-4 py-3">Planejamento, análise DECEA e ida a campo</td>
                                                    <td class="px-4 py-3">Até 02 dias</td>
                                                </tr>
                                                <tr class="hover:bg-white/5">
                                                    <td class="px-4 py-3 font-medium text-white">2. Campo (GCPs)</td>
                                                    <td class="px-4 py-3">Instalação de pontos de controle terrestre</td>
                                                    <td class="px-4 py-3">01 dia</td>
                                                </tr>
                                                <tr class="hover:bg-white/5">
                                                    <td class="px-4 py-3 font-medium text-white">3. Campo (Voo)</td>
                                                    <td class="px-4 py-3">Execução do voo de mapeamento</td>
                                                    <td class="px-4 py-3">01 dia</td>
                                                </tr>
                                                <tr class="hover:bg-white/5">
                                                    <td class="px-4 py-3 font-medium text-white">4. Processamento</td>
                                                    <td class="px-4 py-3">Geração da nuvem de pontos e ortomosaico</td>
                                                    <td class="px-4 py-3">03 a 05 dias</td>
                                                </tr>
                                                <tr class="hover:bg-white/5">
                                                    <td class="px-4 py-3 font-medium text-white">5. CAD/Vetorização</td>
                                                    <td class="px-4 py-3">Desenho técnico e curvas de nível</td>
                                                    <td class="px-4 py-3">03 a 05 dias</td>
                                                </tr>
                                                <tr class="bg-primary/10 font-semibold">
                                                    <td colspan="2" class="px-4 py-3 text-white rounded-l-lg">TOTAL ESTIMADO</td>
                                                    <td class="px-4 py-3 text-primary rounded-r-lg">
                                                        <span id="prazo-total-display">'. (intval($variaveis['dias_campo'] ?? 0) + intval($variaveis['dias_escritorio'] ?? 0)) .' dias úteis</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>';

                                    // Campos Editáveis
                                    echo '<div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Dias de Campo</label>
                                            <input type="number" name="dias_campo" id="input-dias-campo" value="'. htmlspecialchars($variaveis['dias_campo'] ?? '0') .'" min="1" max="30"
                                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary/50 text-sm transition-all"
                                                onchange="atualizarPrazoTotal()">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Dias de Escritório</label>
                                            <input type="number" name="dias_escritorio" id="input-dias-escritorio" value="'. htmlspecialchars($variaveis['dias_escritorio'] ?? '0') .'" min="1" max="30"
                                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary/50 text-sm transition-all"
                                                onchange="atualizarPrazoTotal()">
                                        </div>
                                    </div>';

                                    // Observações
                                    $obsContent = htmlspecialchars($incomingData['cronograma_content'] ?? '');
                                    echo '<div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Observações Adicionais</label>
                                        <textarea name="cronograma_content" id="ed-cronograma-obs" rows="3"
                                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-300 focus:outline-none focus:border-primary/50 text-sm transition-all"
                                            placeholder="Informações complementares sobre prazos, dependências ou condições especiais...">'. $obsContent .'</textarea>
                                    </div>';
                                    
                                    echo '</div>'; // close col-span-2
                                    break;

                                case 'equipamentos':
                                    echo '<div class="col-span-2">';
                                    
                                    // Carregar Defaults
                                    $configEquip = require 'config/equipamentos_servico.php';
                                    $nomeServico = mb_strtolower($incomingData['nome_servico'] ?? '');
                                    $tipo = 'padrao';
                                    if (strpos($nomeServico, 'drone') !== false || strpos($nomeServico, 'fotogrametria') !== false) $tipo = 'drone_fotogrametria';
                                    elseif (strpos($nomeServico, 'geo') !== false) $tipo = 'georreferenciamento';
                                    elseif (strpos($nomeServico, 'topografia') !== false) $tipo = 'topografia_tradicional';
                                    elseif (strpos($nomeServico, 'cadastral') !== false) $tipo = 'levantamento_cadastral';
                                    
                                    $defaults = $configEquip[$tipo] ?? $configEquip['padrao'];
                                    
                                    // Valores (Salvos ou Padrão) - Usando $variaveis se disponível (mapeado de $incomingData) ou direto de $incomingData
                                    // $variaveis mapeia Estacao_Total, GPS etc, mas pode ser diferente dos names dos inputs.
                                    // Vamos usar $incomingData direto para os names específicos ou $defaults
                                    
                                    $val = [
                                        'estacao_total' => $incomingData['equipamentos_estacao_total_content'] ?? $defaults['estacao_total'],
                                        'gps' => $incomingData['equipamentos_gps_content'] ?? $defaults['gps'],
                                        'drone' => $incomingData['equipamentos_drone_content'] ?? $defaults['drone'],
                                        'veiculo' => $incomingData['equipamentos_veiculo_content'] ?? $defaults['veiculo']
                                    ];

                                    // Lista Automática
                                    echo '<div class="bg-white/5 p-4 rounded-xl border border-white/10 mb-4">
                                        <ul class="space-y-3 text-sm text-slate-300">
                                            <li class="flex gap-3">
                                                <span class="text-tech font-bold min-w-[120px]">Estação Total:</span>
                                                <span id="equip-estacao-total">'. htmlspecialchars($val['estacao_total']) .'</span>
                                            </li>
                                            <li class="flex gap-3">
                                                <span class="text-tech font-bold min-w-[120px]">GPS:</span>
                                                <span id="equip-gps">'. htmlspecialchars($val['gps']) .'</span>
                                            </li>
                                            <li class="flex gap-3">
                                                <span class="text-tech font-bold min-w-[120px]">Drone:</span>
                                                <span id="equip-drone">'. htmlspecialchars($val['drone']) .'</span>
                                            </li>
                                            <li class="flex gap-3">
                                                <span class="text-tech font-bold min-w-[120px]">Veículo:</span>
                                                <span id="equip-veiculo">'. htmlspecialchars($val['veiculo']) .'</span>
                                            </li>
                                        </ul>
                                    </div>';
                                    
                                    // Override UI
                                    $overrideContent = $incomingData['equipamentos_override_content'] ?? '';
                                    echo '<div class="mt-4">
                                        <label class="flex items-center gap-2 cursor-pointer mb-3">
                                            <input type="checkbox" id="check-override-equip" name="equipamentos_override_ativo" value="1" 
                                                class="w-4 h-4 rounded border-white/10 bg-white/5 text-primary focus:ring-primary/50"
                                                onchange="toggleEquipOverride()" '. (!empty($overrideContent) ? 'checked' : '') .'>
                                            <span class="text-sm text-slate-400">Personalizar equipamentos</span>
                                        </label>
                                        
                                        <div id="equip-override-area" class="'. (!empty($overrideContent) ? '' : 'hidden') .'">
                                            <textarea name="equipamentos_override_content" id="ed-equipamentos_override" 
                                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-300 text-sm transition-all"
                                                rows="6" placeholder="Cole aqui o texto personalizado de equipamentos...">'. htmlspecialchars($overrideContent) .'</textarea>
                                        </div>
                                    </div>';
                                    
                                    // Hidden Fields (Renamed to _content)
                                    echo '<input type="hidden" name="equipamentos_estacao_total_content" value="'. htmlspecialchars($val['estacao_total']) .'">';
                                    echo '<input type="hidden" name="equipamentos_gps_content" value="'. htmlspecialchars($val['gps']) .'">';
                                    echo '<input type="hidden" name="equipamentos_drone_content" value="'. htmlspecialchars($val['drone']) .'">';
                                    echo '<input type="hidden" name="equipamentos_veiculo_content" value="'. htmlspecialchars($val['veiculo']) .'">';
                                    
                                    echo '</div>'; // close col-span-2
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
        // Função de Salvamento (Async sem travar a tela)
        async function salvarRascunho() {
            tinymce.triggerSave();
            const btn = document.querySelector('button[onclick="salvarRascunho()"]');
            const originalContent = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> Salvando...';

            try {
                const formData = new FormData(document.getElementById('formProposta'));
                
                // [FIX] Adicionar token CSRF manualmente se necessário
                // formData.append('csrf_token', 'TOKEN_AQUI'); 

                const response = await fetch('salvar_rascunho.php', { method: 'POST', body: formData });
                
                if (response.ok) {
                    // Feedback visual sutil
                    const icon = btn.querySelector('i');
                    if(icon) {
                        icon.className = 'bi bi-check-lg';
                        setTimeout(() => { icon.className = 'bi bi-cloud-arrow-up'; }, 2000);
                    }
                    // Opcional: Toast sem alert modal
                    // SGTUtils.showToast('Rascunho salvo!', 'success');
                } else {
                    throw new Error('Falha no servidor');
                }
            } catch (e) {
                console.error('Erro ao salvar:', e);
                alert('Erro ao salvar rascunho. Tente novamente.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        }

        async function submitForm(formato) {
            tinymce.triggerSave();
            
            // UI Feedback: Encontra o botão clicado
            // Nota: Se houver múltiplos botões, isso pode pegar o primeiro. Melhor passar 'this' no onclick, mas vamos buscar pelo formato.
            // Ajuste: Botão visual web tem ícone eye.
            let btn = null;
            if (formato === 'html') btn = document.querySelector('button i.bi-eye')?.parentElement;
            else if (formato === 'docx') btn = document.querySelector('button i.bi-file-word')?.parentElement;
            
            const originalContent = btn ? btn.innerHTML : '';
            if(btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> Processando...';
            }

            try {
                const form = document.getElementById('formProposta');
                const formData = new FormData(form);
                formData.append('formato_saida', formato);
                formData.append('ajax', '1'); // Solicita resposta JSON

                const response = await fetch('salvar_proposta.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                // Tenta fazer parse do JSON
                let data;
                const text = await response.text();
                try {
                    data = JSON.parse(text);
                } catch(e) {
                    console.error('Resposta não-JSON:', text);
                    throw new Error('Servidor retornou resposta inválida');
                }

                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Erro desconhecido ao processar');
                }

                // Lógica de Redirecionamento Inteligente
                if (formato === 'html') {
                    // Visualização Web: Abre em NOVA ABA
                    window.open(data.redirect, '_blank');
                } else if (formato === 'editor') {
                    // Salvar apenas: Recarrega ou notifica
                    // Se reload for necessário: window.location.reload();
                    // Mas idealmente apenas notifica sucesso
                    if (data.redirect.includes('success=1')) {
                         // Opcional: Mostrar toast
                         // alert('Salvo com sucesso!');
                    }
                } else {
                    // Download/Sucesso: Redireciona a página atual
                    window.location.href = data.redirect;
                }

            } catch (e) {
                console.error('Erro ao processar:', e);
                alert('Ocorreu um erro: ' + e.message);
                
                // Se falhar visualização, tenta fallback tradicional (debug)
                if (formato === 'html' && confirm('Tentar método tradicional (pode falhar)?')) {
                     const form = document.getElementById('formProposta');
                     document.getElementById('inputFormatoSaida').value = formato;
                     form.target = '_blank';
                     form.submit();
                }
            } finally {
                if(btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            }
        }

        // Inicialização TinyMCE Dark (Simplificada e Segura)
        document.addEventListener("DOMContentLoaded", function() {
            // Remove qualquer overlay residual
            const loaders = document.querySelectorAll('.loading-spinner, .overlay-loading');
            loaders.forEach(el => el.remove());

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
        // Script Personalizado para Equipamentos e Cronograma
        function toggleEquipOverride() {
            const checkbox = document.getElementById('check-override-equip');
            const area = document.getElementById('equip-override-area');
            if(checkbox && area) {
                if (checkbox.checked) {
                    area.classList.remove('hidden');
                } else {
                    area.classList.add('hidden');
                }
            }
        }

        function atualizarPrazoTotal() {
            const campo = parseInt(document.getElementById('input-dias-campo').value) || 0;
            const escritorio = parseInt(document.getElementById('input-dias-escritorio').value) || 0;
            const total = campo + escritorio;
            const display = document.getElementById('prazo-total-display');
            if(display) {
                display.textContent = total + ' dias úteis';
            }
        }

        // Carregar Equipamentos via AJAX (Complemento ao PHP)
        document.addEventListener('DOMContentLoaded', function() {
            const idServico = document.querySelector('input[name="id_servico"]')?.value;
            
            // Só busca se não tiver valores já preenchidos (ex: edição) ou se quisermos forçar update
            // O PHP já preenche, mas o AJAX garante atualização se mudar o serviço (futuro) ou se PHP falhar no match
            const temValores = document.querySelector('input[name="equipamentos_gps_content"]')?.value;

            if (idServico && !temValores) {
                fetch(`ajax/get_equipamentos_servico.php?id_servico=${idServico}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            if(document.getElementById('equip-estacao-total')) document.getElementById('equip-estacao-total').textContent = data.estacao_total;
                            if(document.getElementById('equip-gps')) document.getElementById('equip-gps').textContent = data.gps;
                            if(document.getElementById('equip-drone')) document.getElementById('equip-drone').textContent = data.drone;
                            if(document.getElementById('equip-veiculo')) document.getElementById('equip-veiculo').textContent = data.veiculo;
                            
                            // Atualizar hidden fields
                            const inEstacao = document.querySelector('input[name="equipamentos_estacao_total_content"]');
                            const inGps = document.querySelector('input[name="equipamentos_gps_content"]');
                            const inDrone = document.querySelector('input[name="equipamentos_drone_content"]');
                            const inVeiculo = document.querySelector('input[name="equipamentos_veiculo_content"]');

                            if(inEstacao) inEstacao.value = data.estacao_total;
                            if(inGps) inGps.value = data.gps;
                            if(inDrone) inDrone.value = data.drone;
                            if(inVeiculo) inVeiculo.value = data.veiculo;
                        }
                    })
                    .catch(console.error);
            }
        });
    </script>
</body>
</html>
