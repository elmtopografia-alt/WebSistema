<?php
/**
 * EDITOR DINÂMICO DE PROPOSTAS V3.0 (Correção Arquitetural DOCX)
 * 
 * Suporte dual: Modelos DOCX (genérico) + Modelos Legacy (hardcoded)
 */

require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/PropostaRepository.php';
require_once __DIR__ . '/vendor/autoload.php';

// --- INTEGRAÇÃO DOCX ---
require_once __DIR__ . '/renderizador_modelo_docx.php';

try {
    $repo = new PropostaRepository();
    $docxRenderer = new RenderizadorModeloDOCX($repo->getConn());
    $modelosDisponiveis = $docxRenderer->listarModelos();
} catch (Exception $e) {
    error_log("Erro ao inicializar repositório no editor: " . $e->getMessage());
    $modelosDisponiveis = [];
}

$modeloDocxAtivo = $_GET['modelo_docx'] ?? null;

// =====================================================
// SISTEMA DE MAPEAMENTO INTELIGENTE DE VARIÁVEIS V3.0
// =====================================================

/**
 * Mapeamento unificado: Resolve qualquer chave ${xxx} para dados do sistema
 * Suporta aliases e fallback inteligente
 */
class VariableResolver {
    private $data;
    private $map;
    
    public function __construct($data) {
        $this->data = $data;
        $this->buildMap();
    }
    
    private function buildMap() {
        $d = $this->data;
        
        // MAPEAMENTO PRINCIPAL: Chave DOCX => Campo Banco
        $this->map = [
            // Dados Cliente (múltiplas possibilidades)
            'nome_cliente' => $d['nome_cliente'] ?? $d['cliente_nome'] ?? 'Cliente não informado',
            'email_cliente' => $d['email_cliente'] ?? $d['email_salvo'] ?? $d['cliente_email'] ?? '',
            'telefone_cliente' => $d['telefone_cliente'] ?? $d['telefone_salvo'] ?? $d['cliente_telefone'] ?? '',
            'celular_cliente' => $d['celular_cliente'] ?? $d['celular_salvo'] ?? $d['cliente_celular'] ?? $d['whatsapp'] ?? '',
            'whatsapp_cliente' => $d['whatsapp'] ?? $d['celular_salvo'] ?? '',
            
            // Local/Obra
            'endereco_obra' => $d['endereco_obra'] ?? $d['obra_endereco'] ?? '',
            'bairro_obra' => $d['bairro_obra'] ?? $d['obra_bairro'] ?? $d['ClienteBairro'] ?? '',
            'cidade_obra' => $d['cidade_obra'] ?? $d['obra_cidade'] ?? '',
            'estado_obra' => $d['estado_obra'] ?? $d['obra_estado'] ?? $d['uf_obra'] ?? '',
            'ClienteCidadeUF' => ($d['cidade_obra'] ?? '') . '-' . ($d['estado_obra'] ?? ''),
            
            // Valores e datas
            'ValorProposta' => $this->formatarMoeda($d['valor_final_proposta'] ?? 0),
            'ValorExtenso' => $this->valorPorExtenso($d['valor_final_proposta'] ?? 0),
            'DataExtenso' => $this->dataPorExtenso($d['data_criacao'] ?? null),
            'numero_proposta' => $d['numero_proposta'] ?? $d['id'] ?? 'N/A',
            
            // Área e unidade
            'AreaEstimada' => ($d['area_obra'] ?? '0') . ' ' . ($d['unidade_area'] ?? 'm²'),
            'area_obra' => $d['area_obra'] ?? '0',
            'unidade_area' => $d['unidade_area'] ?? 'm²',
            
            // Drone/Campo
            'TipoTerreno' => $d['tipo_terreno'] ?? 'Não informado',
            'CoberturaVegetal' => $d['cobertura_vegetal'] ?? 'Não informado',
            'AcessoLocal' => $d['acesso_local'] ?? 'Não informado',
            'RestricoesAereas' => $d['restricoes_aereas'] ?? 'Não informado',
            
            // Equipamentos
            'Drone' => $d['drone'] ?? 'Não aplicável',
            'Veiculo' => $d['veiculo'] ?? 'Não incluso',
            'Estacao_Total' => $d['estacao_total'] ?? 'Não inclusa',
            'GPS' => $d['gps'] ?? 'Par de Receptores GNSS RTK',
            
            // Empresa
            'Empresa' => $d['nome_empresa'] ?? 'SGT Topografia',
            'empresa' => $d['nome_empresa'] ?? 'SGT Topografia',
            'CNPJ' => $d['empresa_proponente_cnpj'] ?? '',
            'logo_empresa' => $d['logo_empresa'] ?? 'assets/logo_sgt.png',
            
            // Prazos
            'dias_campo' => $d['dias_campo'] ?? '0',
            'dias_escritorio' => $d['dias_escritorio'] ?? '0',
        ];
        
        // Adiciona todas as chaves originais como fallback
        $this->map = array_merge($d, $this->map);
    }
    
    public function resolve($key) {
        // Remove ${} ou {{}} se presente
        $key = preg_replace('/^\$\{|\}$|^\{\{|\}\}$/', '', trim($key));
        return $this->map[$key] ?? "[{$key}]"; // Retorna [chave] se não encontrar
    }
    
    public function getAll() {
        return $this->map;
    }
    
    private function formatarMoeda($valor) {
        if (empty($valor)) return 'R$ 0,00';
        if (is_string($valor) && strpos($valor, 'R$') === 0) return $valor;
        $num = floatval(str_replace(['.', ','], ['', '.'], preg_replace('/[^0-9.,]/', '', $valor)));
        return 'R$ ' . number_format($num, 2, ',', '.');
    }
    
    private function valorPorExtenso($valor) {
        $valor = floatval(preg_replace('/[^0-9.,]/', '', $valor));
        if ($valor == 0) return 'ZERO REAIS';
        $fmt = new NumberFormatter("pt_BR", NumberFormatter::SPELLOUT);
        return mb_strtoupper($fmt->format($valor) . " REAIS");
    }
    
    private function dataPorExtenso($data) {
        $meses = [1=>'janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];
        $ts = is_string($data) ? strtotime($data) : ($data ?? time());
        return date('d', $ts) . ' de ' . $meses[intval(date('m', $ts))] . ' de ' . date('Y', $ts);
    }
    
    // NOVO: Adicionei este método para o uso que é feito na GenericBlockRenderer
    public function substituirVariaveis($conteudo) {
         if (empty($conteudo)) return '';
         
         // Encontra todas as variáveis do tipo ${...} ou {{...}} no texto
         return preg_replace_callback('/(\$\{\s*([^}]+)\s*\}|\{\{\s*([^}]+)\s*\}\})/', function($matches) {
             $chave = trim($matches[2] ?? $matches[3] ?? '');
             if (empty($chave)) return $matches[0];
             $valor = $this->resolve($chave);
             return $valor !== "[{$chave}]" ? $valor : $matches[0]; // Mantém a string original se não resolver
         }, $conteudo);
    }
}

// =====================================================
// RENDERIZADOR DUAL: DOCX vs LEGACY
// =====================================================

/**
 * Renderizador de Blocos Genéricos (para modelos DOCX)
 */
class GenericBlockRenderer {
    private $resolver;
    
    public function __construct($data) {
        $this->resolver = new VariableResolver($data);
    }
    
    /**
     * Renderiza estrutura vinda do parser DOCX
     */
    public function renderDocxBlocks($blocos, $variaveisDetectadas = []) {
        $html = "<div class='modelo-docx-editor'>";
        
        foreach ($blocos as $index => $bloco) {
            $html .= $this->renderBlocoEditable($bloco, $index);
        }
        
        $html .= "</div>";
        return $html;
    }
    
    private function renderBlocoEditable($bloco, $index) {
        $tipo = $bloco['tipo'] ?? 'texto';
        $id = "docx-bloco-{$index}";
        
        $html = "<div class='glass-card rounded-2xl border-l-4 border-l-emerald-500 p-6 mb-6 block-card' id='{$id}'>";
        $html .= "<div class='flex justify-between items-center mb-4'>";
        $html .= "<span class='text-[10px] font-bold text-slate-500 uppercase tracking-widest'>Bloco " . ($index + 1) . " ({$tipo})</span>";
        $html .= "</div>";
        
        if ($tipo === 'texto') {
            $conteudo = $this->resolver->substituirVariaveis($bloco['conteudo'] ?? '');
            $nomeCampo = "docx_bloco_{$index}_content";
            
            $html .= "<textarea name='{$nomeCampo}' id='ed-{$nomeCampo}' class='w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-300 focus:outline-none focus:border-emerald-500/50 text-sm transition-all' rows='6'>";
            $html .= htmlspecialchars($conteudo);
            $html .= "</textarea>";
            
            // Mostra variáveis detectadas neste bloco
            if (!empty($bloco['variaveis'])) {
                $html .= "<div class='mt-3 flex flex-wrap gap-2'>";
                foreach ($bloco['variaveis'] as $var) {
                    $valor = $this->resolver->resolve($var);
                    $html .= "<span class='text-[10px] bg-emerald-500/10 text-emerald-400 px-2 py-1 rounded border border-emerald-500/20' title='Valor: {$valor}'>";
                    $html .= '${' . $var . '}';
                    $html .= "</span>";
                }
                $html .= "</div>";
            }
        }
        elseif ($tipo === 'tabela') {
            $html .= "<div class='overflow-x-auto'><table class='w-full text-sm text-left text-slate-300 border border-white/10 rounded-xl'>";
            foreach ($bloco['linhas'] ?? [] as $i => $linha) {
                $html .= "<tr class='" . ($i === 0 ? 'bg-white/5' : 'border-t border-white/5') . "'>";
                foreach ($linha as $celula) {
                    $tag = ($i === 0) ? 'th' : 'td';
                    $texto = $this->resolver->substituirVariaveis($celula['texto'] ?? '');
                    $html .= "<{$tag} class='px-4 py-3' colspan='" . ($celula['colspan'] ?? 1) . "'>";
                    $html .= htmlspecialchars($texto);
                    $html .= "</{$tag}>";
                }
                $html .= "</tr>";
            }
            $html .= "</table></div>";
        }
        
        $html .= "</div>";
        return $html;
    }
}

// =====================================================
// CARREGAMENTO DE DADOS E FUNÇÕES LEGACY
// =====================================================

// MANTIDO: Funções Auxiliares Legacy Originais (para garantir fallbacks)
function formatarMoedaLegacy($valor) {
    if (empty($valor) || $valor === 'R$ 0,00' || $valor === '0' || $valor === 0) { return 'R$ 0,00'; }
    if (is_string($valor) && strpos($valor, 'R$') === 0) { return $valor; }
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

function substituicaoVariaveisLegacy($texto, $variaveis) {
    if (empty($texto)) return '';
    foreach ($variaveis as $chave => $valor) {
        if (!is_array($valor)) {
            $texto = str_replace('${' . $chave . '}', (string)$valor, $texto);
        }
    }
    return $texto;
}

$id_prop = (int)($_GET['id'] ?? 0);
if (!$id_prop && isset($_SESSION['id_proposta_ativa'])) {
    $id_prop = (int)$_SESSION['id_proposta_ativa'];
}

if (!$id_prop) {
    die("ID da proposta não informado");
}

$_SESSION['id_proposta_ativa'] = $id_prop;

try {
    $incomingData = $repo->buscarPorId($id_prop);
    if (!$incomingData) {
        die("Proposta não encontrada");
    }
    
    // Inicializa o resolvedor de variáveis
    $varResolver = new VariableResolver($incomingData);
    $variaveis = $varResolver->getAll();
    
    // Detecta modo de operação: DOCX ou LEGACY
    $modoDocx = false;
    $docxData = null;
    
    // Se o usuário clicou para trocar o modelo, usa o do GET, senão usa o salvo no banco (migração V3.0)
    $modeloDocxAtivo = $_GET['modelo_docx'] ?? $incomingData['modelo_docx'] ?? null;
    
    if ($modeloDocxAtivo) {
        // Tenta carregar modelo DOCX
        $caminhoModelo = __DIR__ . '/modelos_gerados/Modelo' . preg_replace('/[^a-zA-Z0-9]/', '', $modeloDocxAtivo) . '.php';
        
        if (file_exists($caminhoModelo)) {
            require_once $caminhoModelo;
            $classeModelo = 'SGT\\Propostas\\Modelo' . preg_replace('/[^a-zA-Z0-9]/', '', $modeloDocxAtivo);
            
            if (class_exists($classeModelo)) {
                $instanciaModelo = new $classeModelo();
                $config = $instanciaModelo->getConfig();
                $docxData = $config;
                $modoDocx = true;
            }
        }
    }
    
    // Se não achou DOCX válido, carrega estrutura legacy
    if (!$modoDocx) {
        $serviceTypeId = (int)$incomingData['id_servico'];
        $loader = new ProposalArchitect\Infrastructure\DatabaseStructureLoader($repo->getConn());
        $model = $loader->getVirtualModel($serviceTypeId);
        $treeBuilder = new ProposalArchitect\Infrastructure\HierarchyTreeBuilder();
        $structure = $treeBuilder->build($model);
        $metadata = $model->getModelMetadata();
    }
    
} catch (Exception $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

// =====================================================
// RENDERIZAÇÃO DA VIEW
// =====================================================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor SGT | <?= $modoDocx ? ($docxData['nome'] ?? 'Modelo DOCX') : ($metadata['name'] ?? 'Proposta') ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="assets/css/editor-tailwind.css?v=<?= time() ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>

    <style>
        body { background-color: #0a0f1a; color: #f8fafc; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(17, 24, 39, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glass-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .block-card:hover { border-color: rgba(249, 115, 22, 0.4); transform: translateY(-2px); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0a0f1a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        
        /* Estilos específicos para modo DOCX */
        .modo-docx-badge { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .variavel-chip { transition: all 0.2s; cursor: help; }
        .variavel-chip:hover { background: rgba(16, 185, 129, 0.3); transform: scale(1.05); }
    </style>
</head>

<body class="h-screen flex flex-col antialiased">

    <!-- Header -->
    <header class="glass border-b border-white/10 px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <a href="painel.php" class="text-slate-400 hover:text-white hover:bg-white/5 p-2 rounded-xl transition-all">
                <i class="bi bi-arrow-left text-2xl"></i>
            </a>
            <div class="<?= $modoDocx ? 'modo-docx-badge' : 'bg-orange-500/20' ?> text-white p-2 rounded-xl border border-white/10">
                <i class="bi bi-<?= $modoDocx ? 'file-earmark-word' : 'pencil-square' ?> text-xl"></i>
            </div>
            <div>
                <h1 class="font-bold text-white text-lg">
                    <?= $modoDocx ? ($docxData['nome'] ?? 'Modelo Word') : ($metadata['name'] ?? 'Editor Legacy') ?>
                </h1>
                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">
                    <?= $modoDocx ? 'Modo DOCX Genérico' : 'Modo Legacy SGT' ?>
                </p>
            </div>
        </div>
        
        <div class="flex gap-3">
            <button type="button" onclick="salvarRascunho()" class="px-4 py-2 text-sm font-bold text-slate-300 bg-white/5 border border-white/10 rounded-xl hover:bg-white/10 transition-all flex items-center gap-2">
                <i class="bi bi-cloud-arrow-up"></i> Salvar Rascunho
            </button>
            
            <button type="button" onclick="submitForm('html')" class="px-4 py-2 text-sm font-bold text-white bg-slate-600 rounded-xl hover:bg-slate-500 shadow-lg shadow-slate-900/20 transition-all flex items-center gap-2">
                <i class="bi bi-eye"></i> Visualizar
            </button>
            
            <!-- Seletor de Modelos -->
            <div class="dropdown relative">
                <button type="button" class="px-4 py-2 text-sm font-bold text-white bg-slate-800 border border-white/10 rounded-xl hover:bg-slate-700 transition-all flex items-center gap-2" onclick="toggleDropdown()">
                    <i class="bi bi-file-earmark-richtext"></i>
                    <?= $modeloDocxAtivo ? str_replace('_', ' ', $modeloDocxAtivo) : 'Modelo Padrão' ?>
                </button>
                <ul id="dropdown-modelos" class="hidden absolute right-0 mt-2 w-64 bg-slate-900 border border-white/10 rounded-xl shadow-2xl z-50 p-2">
                    <li><a class="block px-3 py-2 rounded-lg hover:bg-white/5 text-slate-300" href="?id=<?= $id_prop ?>">📂 Modelo Tradicional (SGT)</a></li>
                    <li class="border-t border-white/5 my-1"></li>
                    <li class="px-3 py-1 text-[10px] font-bold text-slate-500 uppercase">Modelos DOCX</li>
                    <?php foreach ($modelosDisponiveis as $mod): ?>
                        <li>
                            <a class="block px-3 py-2 rounded-lg hover:bg-white/5 <?= ($modeloDocxAtivo === $mod['id']) ? 'bg-emerald-500/20 text-emerald-400' : 'text-slate-300' ?>" 
                               href="?id=<?= $id_prop ?>&modelo_docx=<?= $mod['id'] ?>">
                               📄 <?= $mod['nome'] ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li class="border-t border-white/5 my-1"></li>
                    <li><a class="block px-3 py-2 rounded-lg text-emerald-400 hover:bg-emerald-500/10" href="gerador_upload_docx.php" target="_blank">
                        <i class="bi bi-plus-circle"></i> Gerenciar DOCX
                    </a></li>
                </ul>
            </div>

            <button type="button" onclick="submitForm('docx')" class="px-5 py-2 text-sm font-bold text-white bg-orange-600 rounded-xl hover:bg-orange-500 shadow-lg shadow-orange-900/20 transition-all flex items-center gap-2">
                <i class="bi bi-file-earmark-word"></i> Gerar Word
            </button>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        
        <!-- Sidebar com variáveis disponíveis (apenas modo DOCX) -->
        <?php if ($modoDocx && !empty($docxData['variaveis'])): ?>
        <aside class="w-72 glass border-r border-white/10 overflow-y-auto hidden lg:block">
            <div class="p-5">
                <h3 class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="bi bi-magic"></i> Variáveis Detectadas
                </h3>
                <div class="space-y-2">
                    <?php foreach ($docxData['variaveis'] as $var): 
                        $valor = $varResolver->resolve($var);
                        $curto = mb_strlen($valor) > 30 ? mb_substr($valor, 0, 30) . '...' : $valor;
                        $isResolved = ($valor !== "[{$var}]");
                    ?>
                        <div class="variavel-chip bg-white/5 border border-white/10 rounded-lg p-3" title="<?= htmlspecialchars($valor) ?>">
                            <div class="text-[10px] <?= $isResolved ? 'text-emerald-400' : 'text-slate-400' ?> font-mono mb-1">${<?= $var ?>}</div>
                            <div class="text-[11px] text-slate-400 truncate"><?= htmlspecialchars($curto) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-6 p-3 bg-amber-500/10 border border-amber-500/20 rounded-lg">
                    <p class="text-[10px] text-amber-200">
                        <i class="bi bi-info-circle"></i> 
                        Variáveis em <span class="text-emerald-400">[verde]</span> foram resolvidas. 
                        Em <span class="text-slate-400">[cinza]</span> usam os valores digitados no editor ou não foram encontradas.
                    </p>
                </div>
            </div>
        </aside>
        <?php elseif (!$modoDocx): ?>
        <!-- NAV LEGACY -->
        <aside class="w-64 glass border-r border-white/10 overflow-y-auto hidden md:block">
            <div class="p-5">
                <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">Estrutura (Legacy)</h3>
                <nav class="space-y-1">
                    <?php
                    function renderNav($nodes, $depth = 0) {
                        foreach ($nodes as $node) {
                            $padding = $depth * 1.25;
                            $activeClass = $depth === 0 ? 'text-slate-300 font-semibold' : 'text-slate-500 text-xs';
                            echo "<a href='#block-{$node['id']}' class='flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/5 transition-all {$activeClass}' style='padding-left: calc(0.75rem + {$padding}rem)'>";
                            echo "<span>{$node['title']}</span>";
                            echo "</a>";
                            if (!empty($node['children'])) renderNav($node['children'], $depth + 1);
                        }
                    }
                    if(isset($structure)) renderNav($structure);
                    ?>
                </nav>
            </div>
        </aside>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-8 scroll-smooth" id="main-scroll">
            <div class="max-w-4xl mx-auto pb-20">
                
                <form id="formProposta" method="POST" action="salvar_proposta.php">
                    <input type="hidden" name="id_proposta" value="<?= $id_prop ?>">
                    <input type="hidden" name="id_proposta_original" value="<?= $id_prop ?>">
                    <input type="hidden" name="modelo_docx" value="<?= htmlspecialchars($modeloDocxAtivo ?? '') ?>">
                    <input type="hidden" name="formato_saida" id="inputFormatoSaida" value="html">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <?php if ($modoDocx && $docxData): ?>
                        <!-- MODO DOCX: Renderização Genérica -->
                        <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                            <h2 class="text-emerald-400 font-bold flex items-center gap-2">
                                <i class="bi bi-file-earmark-word"></i> 
                                Editando Modelo: <?= $docxData['nome'] ?>
                            </h2>
                            <p class="text-sm text-slate-400 mt-1">
                                Blocos carregados do arquivo Word. Edite o conteúdo abaixo (Variáveis processadas).
                            </p>
                        </div>
                        
                        <?php
                        $renderer = new GenericBlockRenderer($incomingData);
                        
                        // [V3.0] Prepara os blocos com o conteúdo salvo anteriormente (se houver) no DB
                        $blocosSalvosParaDocx = [];
                        if(!empty($incomingData['docx_conteudo'])) {
                            $decoded = json_decode($incomingData['docx_conteudo'], true);
                            if(is_array($decoded)) $blocosSalvosParaDocx = $decoded;
                        }
                        
                        $blocosParaRender = $docxData['blocos'];
                        if(!empty($blocosSalvosParaDocx)) {
                            // Mescla o conteúdo salvo com a estrutura do DOCX
                            foreach($blocosParaRender as $idx => &$b) {
                                if(isset($blocosSalvosParaDocx[$idx]['conteudo'])) {
                                    $b['conteudo'] = $blocosSalvosParaDocx[$idx]['conteudo'];
                                }
                                if($b['tipo'] === 'tabela' && isset($blocosSalvosParaDocx[$idx]['linhas'])) {
                                     $b['linhas'] = $blocosSalvosParaDocx[$idx]['linhas'];
                                }
                            }
                        }
                        
                        echo $renderer->renderDocxBlocks($blocosParaRender, $docxData['variaveis'] ?? []);
                        ?>
                        
                    <?php else: ?>
                        <!-- MODO LEGACY: Renderização Hardcoded -->
                        <div class="mb-6 p-4 bg-slate-800/50 border border-slate-700 rounded-xl">
                            <h2 class="text-slate-300 font-bold flex items-center gap-2">
                                <i class="bi bi-layers"></i> 
                                Modelo Legacy SGT
                            </h2>
                            <p class="text-sm text-slate-400 mt-1">
                                Você está editando usando a interface fixa tradicional. Se desejar, selecione um modelo DOCX no menu superior.
                            </p>
                        </div>
                        
                        <?php
                        // FUNÇÕES DO RENDER LEGACY 
                        function renderFieldLegacy($name, $label, $type, $value, $full = false) {
                            $span = $full ? 'md:col-span-2' : '';
                            echo "<div class='{$span}'>";
                            echo "<label class='block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5 ml-1'>{$label}</label>";
                            $valSafe = htmlspecialchars($value);
                            if ($type === 'textarea') {
                                echo "<textarea name='{$name}' id='ed-{$name}' class='w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-300 focus:outline-none focus:border-orange-500/50 text-sm transition-all'>{$valSafe}</textarea>";
                            } else {
                                echo "<input type='{$type}' name='{$name}' value='{$valSafe}' class='w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-orange-500/50 text-sm transition-all'>";
                            }
                            echo "</div>";
                        }
                        
                        // [O MESMO RENDER FORMS DA V2]
                        function renderFormBlocksLegacy($nodes, $model, $incomingData, $variaveis) {
                            foreach ($nodes as $node) {
                                $slug = $node['id'];
                                if (in_array($slug, ['recursos_equipamentos', 'cronograma_drone', 'investimento-extra'])) continue;
                                $borderColors = [ 'presentation' => 'border-l-blue-500', 'technical' => 'border-l-indigo-500', 'financial' => 'border-l-emerald-500', 'legal' => 'border-l-slate-500' ];
                                $borderColor = $borderColors[$node['category']] ?? 'border-l-slate-700';

                                $defaultContent = $node['default_content'] ?? '';
                                $userValue = $incomingData[$slug . '_content'] ?? ($incomingData[$slug] ?? '');
                                $rawContent = (!empty($userValue)) ? $userValue : $defaultContent;
                                $finalContent = substituicaoVariaveisLegacy($rawContent, $variaveis);

                                echo "<div id='block-{$slug}' class='glass-card rounded-2xl border-l-4 {$borderColor} block-card p-6 scroll-mt-24 mb-8'>";
                                echo "<div class='flex justify-between items-center mb-6'>";
                                echo "<div class='flex items-center gap-3'><h3 class='text-lg font-bold text-white'>{$node['title']}</h3></div>";
                                echo "</div>";
                                echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-5'>";

                                switch ($slug) {
                                    case 'dados_cliente':
                                        renderFieldLegacy('nome_cliente', 'Cliente', 'text', $variaveis['nome_cliente']);
                                        renderFieldLegacy('email_cliente', 'E-mail', 'email', $variaveis['email_salvo']);
                                        break;
                                    case 'local_obra':
                                        renderFieldLegacy('endereco_obra', 'Endereço', 'text', $variaveis['endereco_obra'], true);
                                        renderFieldLegacy('cidade_obra', 'Cidade', 'text', $variaveis['cidade_obra']);
                                        renderFieldLegacy('estado_obra', 'UF', 'text', $variaveis['estado_obra']);
                                        break;
                                    default:
                                        $fieldName = $slug . '_content';
                                        renderFieldLegacy($fieldName, 'Conteúdo', 'textarea', $finalContent, true);
                                        break;
                                }

                                echo "</div></div>";

                                if (!empty($node['children'])) {
                                    echo "<div class='pl-8 border-l-2 border-white/5 space-y-6'>";
                                    renderFormBlocksLegacy($node['children'], $model, $incomingData, $variaveis);
                                    echo "</div>";
                                }
                            }
                        }

                        if(isset($structure)) renderFormBlocksLegacy($structure, $model, $incomingData, $variaveis);
                        ?>
                        
                    <?php endif; ?>
                </form>
                
            </div>
        </main>
    </div>

    <script>
        // Dropdown simples
        function toggleDropdown() {
            const el = document.getElementById('dropdown-modelos');
            el.classList.toggle('hidden');
        }
        
        // Fecha dropdown ao clicar fora
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('dropdown-modelos');
            const btn = e.target.closest('.dropdown');
            if (!btn && dropdown) dropdown.classList.add('hidden');
        });

        // Inicialização TinyMCE
        document.addEventListener("DOMContentLoaded", function() {
            tinymce.init({
                selector: 'textarea[id^="ed-"]',
                height: 300,
                skin: "oxide-dark",
                content_css: "dark",
                menubar: false,
                plugins: 'lists link code table wordcount',
                toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | removeformat | code',
                setup: function(editor) {
                    editor.on('change', function() { editor.save(); });
                }
            });
        });

        // Funções de salvamento (mantidas do original)
        async function salvarRascunho() {
            tinymce.triggerSave();
            const btn = document.querySelector('button[onclick="salvarRascunho()"]');
            const original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> Salvando...';
            
            try {
                const formData = new FormData(document.getElementById('formProposta'));
                const resp = await fetch('salvar_rascunho.php', { method: 'POST', body: formData });
                if (resp.ok) {
                    btn.innerHTML = '<i class="bi bi-check-lg"></i> Salvo!';
                    setTimeout(() => btn.innerHTML = original, 2000);
                }
            } catch(e) {
                alert('Erro ao salvar');
                btn.innerHTML = original;
                btn.disabled = false;
            }
        }

        async function submitForm(formato) {
            tinymce.triggerSave();
            const form = document.getElementById('formProposta');
            const formData = new FormData(form);
            formData.append('formato_saida', formato);
            formData.append('ajax', '1');
            
            try {
                const resp = await fetch('salvar_proposta.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                // Trata a possível renderização de erro de banco do salvar_proposta na tela
                const textResp = await resp.text();
                let data;
                try {
                    data = JSON.parse(textResp);
                } catch(err) {
                     console.error(textResp);
                     alert('Ocorreu um erro no servidor ou a ação de Salvar requer que salvar_proposta.php também seja atualizado (Etapa 3.3).');
                     return;
                }
                
                if (data.success) {
                    if (formato === 'html') window.open(data.redirect, '_blank');
                    else window.location.href = data.redirect;
                } else {
                    throw new Error(data.error);
                }
            } catch(e) {
                alert('Erro da aplicação: ' + e.message + "\nLembre-se de atualizar salvar_proposta.php para dar suporte a este novo fluxo DOCX.");
            }
        }
    </script>
</body>
</html>
