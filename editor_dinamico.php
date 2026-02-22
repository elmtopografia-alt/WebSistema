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
// SISTEMA DE TEMAS PREMIUM V3.1 E MAPEAMENTO INTELIGENTE
// =====================================================

/**
 * Gerenciador de Temas Premium - Define estilos consistentes para todo o editor
 */
class ThemeManager {
    const THEMES = [
        'premium' => [
            'name' => 'Premium Dark',
            'bg' => 'bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900',
            'card' => 'glass-premium',
            'accent' => 'emerald',
            'border' => 'border-emerald-500/30',
            'text' => 'text-slate-100',
            'muted' => 'text-slate-400',
            'input' => 'bg-slate-800/50 border-slate-600/50 focus:border-emerald-500/50',
            'shadow' => 'shadow-2xl shadow-emerald-500/5'
        ],
        'legacy' => [
            'name' => 'Professional Orange',
            'bg' => 'bg-[#0a0f1a]',
            'card' => 'glass-legacy',
            'accent' => 'orange',
            'border' => 'border-orange-500/30',
            'text' => 'text-slate-100',
            'muted' => 'text-slate-500',
            'input' => 'bg-white/5 border-white/10 focus:border-orange-500/50',
            'shadow' => 'shadow-xl shadow-orange-500/5'
        ]
    ];
    
    private $currentTheme;
    
    public function __construct($isDocxMode) {
        $this->currentTheme = $isDocxMode ? self::THEMES['premium'] : self::THEMES['legacy'];
    }
    
    public function get($key) {
        return $this->currentTheme[$key] ?? '';
    }
    
    public function cardClass($category = 'default') {
        $colors = [
            'presentation' => 'border-l-blue-500',
            'technical' => 'border-l-indigo-500',
            'financial' => 'border-l-emerald-500',
            'legal' => 'border-l-slate-500',
            'default' => $this->get('border')
        ];
        $border = $colors[$category] ?? $colors['default'];
        return "glass-card rounded-2xl border-l-4 {$border} p-6 mb-6 block-card transition-all duration-300 hover:translate-y-[-2px] " . $this->get('shadow');
    }
    
    public function inputClass() {
        return "w-full rounded-xl px-4 py-3 text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-opacity-50 " . $this->get('input') . " " . $this->get('text');
    }
    
    public function btnClass($type = 'primary') {
        $base = "px-5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 flex items-center gap-2 shadow-lg hover:shadow-xl active:scale-95 ";
        $variants = [
            'primary' => "bg-gradient-to-r from-{$this->get('accent')}-500 to-{$this->get('accent')}-600 text-white hover:from-{$this->get('accent')}-400 hover:to-{$this->get('accent')}-500",
            'secondary' => "bg-slate-700 text-slate-200 hover:bg-slate-600 border border-slate-600",
            'ghost' => "bg-white/5 text-slate-300 hover:bg-white/10 border border-white/10"
        ];
        return $base . ($variants[$type] ?? $variants['primary']);
    }
}


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

        $this->map = [
            // ── Cliente (chaves _salvo = padrão do banco) ─────────────────────
            'nome_cliente_salvo'    => $d['nome_cliente_salvo'] ?? $d['nome_cliente'] ?? 'Cliente não informado',
            'email_salvo'           => $d['email_salvo']        ?? $d['email_cliente'] ?? '',
            'telefone_salvo'        => $d['telefone_salvo']     ?? $d['telefone_cliente'] ?? '',
            'celular_salvo'         => $d['celular_salvo']      ?? $d['celular_cliente'] ?? '',
            'whatsapp_salvo'        => $d['whatsapp_salvo']     ?? $d['celular_salvo'] ?? '',
            'empresa_cliente_salvo' => $d['empresa_cliente_salvo'] ?? '',
            // aliases curtos para templates antigos
            'nome_cliente'          => $d['nome_cliente_salvo'] ?? $d['nome_cliente'] ?? 'Cliente não informado',
            'email_cliente'         => $d['email_salvo']        ?? '',
            'telefone_cliente'      => $d['telefone_salvo']     ?? '',
            'celular_cliente'       => $d['celular_salvo']      ?? '',

            // ── Local/Obra ────────────────────────────────────────────────────
            'endereco_obra'    => $d['endereco_obra']   ?? '',
            'bairro_obra'      => $d['bairro_obra']     ?? '',
            // CORREÇÃO: fallback para campo genérico 'cidade'/'estado'
            'cidade_obra'      => $d['cidade_obra']     ?? $d['cidade'] ?? '',
            'estado_obra'      => $d['estado_obra']     ?? $d['estado'] ?? '',
            'ClienteCidadeUF'  => ($d['cidade_obra'] ?? $d['cidade'] ?? '') . '-' . ($d['estado_obra'] ?? $d['estado'] ?? ''),
            // CORREÇÃO: Cidade mapeada com fallback em cascata
            'Cidade'           => $d['empresa_proponente_cidade'] ?? $d['cidade_obra'] ?? $d['cidade'] ?? '',
            'cidade'           => $d['empresa_proponente_cidade'] ?? $d['cidade_obra'] ?? $d['cidade'] ?? '',
            'AreaEstimada'     => ($d['area_obra'] ?? '0') . ' ' . ($d['unidade_area'] ?? 'm²'),
            'area_obra'        => $d['area_obra']       ?? '0',
            'unidade_area'     => $d['unidade_area']    ?? 'm²',

            // ── Valores e datas ───────────────────────────────────────────────
            // CORREÇÃO: ValorProposta sem R$ para evitar duplicação quando template já tem R$
            'ValorProposta'    => $this->formatarMoedaSemPrefixo($d['valor_final_proposta'] ?? 0),
            'ValorExtenso'     => $d['Valor_proposta_extenso'] ?? $this->valorPorExtenso($d['valor_final_proposta'] ?? 0),
            'DataExtenso'      => $this->dataPorExtenso($d['data_criacao'] ?? null),
            'DExrenso'         => $this->dataPorExtenso($d['data_criacao'] ?? null),
            'numero_proposta'  => $d['numero_proposta'] ?? 'N/A',

            // ── Drone/Campo ───────────────────────────────────────────────────
            'TipoTerreno'      => $d['tipo_terreno']     ?? 'Não informado',
            'CoberturaVegetal' => $d['cobertura_vegetal']?? 'Não informado',
            'AcessoLocal'      => $d['acesso_local']     ?? 'Não informado',
            'RestricoesAereas' => $d['restricoes_aereas']?? 'Não informado',

            // ── Equipamentos ──────────────────────────────────────────────────
            'Drone'        => $d['marca_drone']        ?? $d['drone']        ?? 'Não aplicável',
            'Veiculo'      => $d['marca_veiculo']      ?? $d['veiculo']      ?? 'Não incluso',
            'Estacao_Total'=> $d['marca_estacao_total']?? $d['estacao_total']?? 'Não inclusa',
            'GPS'          => $d['marca_gps']          ?? $d['gps']          ?? 'Par de Receptores GNSS RTK',

            // ── Empresa Proponente (campos reais do banco) ────────────────────
            'Empresa'                  => $d['empresa_proponente_nome']   ?? $d['nome_empresa'] ?? 'SGT Topografia',
            'empresa'                  => $d['empresa_proponente_nome']   ?? $d['nome_empresa'] ?? 'SGT Topografia',
            'CNPJ'                     => $d['empresa_proponente_cnpj']   ?? '',
            'Banco'                    => $d['empresa_proponente_banco']  ?? '',
            'Agencia'                  => $d['empresa_proponente_agencia']?? '',
            'Conta'                    => $d['empresa_proponente_conta']  ?? '',
            'PIX'                      => $d['empresa_proponente_pix']    ?? '',
            'empresa_proponente_nome'  => $d['empresa_proponente_nome']   ?? '',
            'empresa_proponente_cnpj'  => $d['empresa_proponente_cnpj']   ?? '',
            'logo_empresa'             => $d['logo_empresa'] ?? 'assets/logo_sgt.png',
            'logo'                     => $d['logo_empresa'] ?? 'assets/logo_sgt.png',

            // ── Prazos ────────────────────────────────────────────────────────
            'dias_campo'     => $d['dias_campo']     ?? '0',
            'dias_escritorio'=> $d['dias_escritorio']?? '0',
            'prazo_execucao' => $d['prazo_execucao'] ?? '',

            // ── Textos ────────────────────────────────────────────────────────
            'finalidade'      => $d['finalidade']       ?? '',
            'tipo_levantamento'=> $d['tipo_levantamento']?? '',

            // ── Pagamento ─────────────────────────────────────────────────────
            // CORREÇÃO: percentual formatado sem .00 quando inteiro; valor sem R$
            'mobilizacao_percentual' => $this->formatarPercentual($d['mobilizacao_percentual'] ?? 30),
            'mobilizacao_valor'      => $this->formatarMoedaSemPrefixo($d['mobilizacao_valor'] ?? 0),
            'restante_percentual'    => $this->formatarPercentual($d['restante_percentual'] ?? 70),
            'restante_valor'         => $this->formatarMoedaSemPrefixo($d['restante_valor'] ?? 0),
        ];

        // Adiciona todas as chaves originais como fallback (campos que não foram explicitamente mapeados)
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
    
    public function substituirVariaveis($conteudo) {
        if (empty($conteudo)) return '';
        
        return preg_replace_callback('/(\$\{\s*([^}]+)\s*\}|\{\{\s*([^}]+)\s*\}\})/', function($matches) {
            $chave = trim($matches[2] ?? $matches[3] ?? '');
            if (empty($chave)) return $matches[0];
            $valor = $this->resolve($chave);
            return $valor !== "[{$chave}]" ? $valor : $matches[0];
        }, $conteudo);
    }
    
    // CORREÇÃO: Formata valor monetário SEM o prefixo R$ (evita duplicação quando template já tem R$)
    private function formatarMoedaSemPrefixo($valor) {
        if (empty($valor)) return '0,00';
        $valorStr = str_replace(['R$', 'r$', ' '], '', (string)$valor);
        if (is_numeric($valorStr)) {
            $num = (float)$valorStr;
        } else {
            $valorStr = preg_replace('/[^0-9.,\-]/', '', $valorStr);
            if (strpos($valorStr, ',') !== false) {
                $valorStr = str_replace('.', '', $valorStr);
                $valorStr = str_replace(',', '.', $valorStr);
            }
            $num = (float)$valorStr;
        }
        return number_format($num, 2, ',', '.');
    }

    // CORREÇÃO: Formata percentual sem casas decimais desnecessárias (30.00 → 30)
    private function formatarPercentual($valor) {
        $num = (float)$valor;
        if ($num == intval($num)) {
            return (string)intval($num);
        }
        return number_format($num, 2, ',', '.');
    }

    // Mantido para compatibilidade com código legado
    private function formatarMoeda($valor) {
        if (empty($valor)) return 'R$ 0,00';
        return 'R$ ' . $this->formatarMoedaSemPrefixo($valor);
    }
    
    private function valorPorExtenso($valor) {
        $valorStr = str_replace(['R$', 'r$', ' '], '', (string)$valor);
        if (is_numeric($valorStr)) {
            $num = (float)$valorStr;
        } else {
            $valorStr = preg_replace('/[^0-9.,\-]/', '', $valorStr);
            if (strpos($valorStr, ',') !== false) {
                $valorStr = str_replace('.', '', $valorStr);
                $valorStr = str_replace(',', '.', $valorStr);
            }
            $num = (float)$valorStr;
        }
        if ($num == 0) return 'ZERO REAIS';
        if (class_exists('NumberFormatter')) {
            $fmt = new NumberFormatter("pt_BR", NumberFormatter::SPELLOUT);
            return mb_strtoupper($fmt->format($num) . " REAIS");
        }
        return 'R$ ' . number_format($num, 2, ',', '.') . ' (VALOR)';
    }
    
    private function dataPorExtenso($data) {
        $meses = [1=>'janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];
        $ts = is_string($data) ? strtotime($data) : ($data ?? time());
        return date('d', $ts) . ' de ' . $meses[intval(date('m', $ts))] . ' de ' . date('Y', $ts);
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
    private $theme;
    
    public function __construct($data, $theme) {
        $this->resolver = new VariableResolver($data);
        $this->theme = $theme;
    }
    
    public function renderDocxBlocks($blocos, $variaveisDetectadas = []) {
        $html = "<div class='modelo-docx-editor space-y-6'>";
        
        foreach ($blocos as $index => $bloco) {
            $html .= $this->renderBlocoEditable($bloco, $index);
        }
        
        $html .= "</div>";
        return $html;
    }
    
    private function renderBlocoEditable($bloco, $index) {
        $tipo = $bloco['tipo'] ?? 'texto';
        $id = "docx-bloco-{$index}";
        $nomeBase = "docx_bloco_{$index}";
        
        // Card premium com animação
        $html = "<div class='{$this->theme->cardClass('technical')}' id='{$id}' data-tipo='{$tipo}'>";
        
        // Header do bloco
        $html .= "<div class='flex justify-between items-center mb-4 pb-3 border-b border-white/10'>";
        $html .= "<div class='flex items-center gap-3'>";
        $html .= "<span class='w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 flex items-center justify-center text-emerald-400 text-xs font-bold'>";
        $html .= ($index + 1);
        $html .= "</span>";
        $html .= "<span class='text-xs font-bold text-slate-400 uppercase tracking-wider'>" . ucfirst($tipo) . "</span>";
        $html .= "</div>";
        
        // Controles do bloco
        $html .= "<div class='flex gap-2'>";
        $html .= "<button type='button' onclick='toggleBloco({$index})' class='p-2 rounded-lg bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white transition-colors' title='Expandir/Recolher'>";
        $html .= "<i class='bi bi-arrows-expand'></i>";
        $html .= "</button>";
        $html .= "</div>";
        $html .= "</div>";
        
        // Conteúdo editável
        $html .= "<div class='bloco-content' id='content-{$index}'>";
        
        if ($tipo === 'texto') {
            $html .= $this->renderEditorTexto($bloco, $nomeBase, $index);
        }
        elseif ($tipo === 'tabela') {
            $html .= $this->renderEditorTabela($bloco, $nomeBase, $index);
        }
        
        $html .= "</div>";
        $html .= "</div>";
        
        return $html;
    }
    
    /**
     * Editor de texto com TinyMCE
     */
    private function renderEditorTexto($bloco, $nomeBase, $index) {
        $conteudoOriginal = $bloco['conteudo'] ?? '';
        $conteudoProcessado = $this->resolver->substituirVariaveis($conteudoOriginal);
        
        $nomeCampo = "{$nomeBase}_content";
        
        $html = "<div class='space-y-3'>";
        
        // Textarea para TinyMCE
        $html .= "<textarea name='{$nomeCampo}' id='editor-{$index}' ";
        $html .= "class='tinymce-editor " . $this->theme->inputClass() . "' ";
        $html .= "rows='8' data-original='" . htmlspecialchars($conteudoOriginal, ENT_QUOTES) . "'>";
        $html .= htmlspecialchars($conteudoProcessado);
        $html .= "</textarea>";
        
        // Variáveis detectadas neste bloco
        if (!empty($bloco['variaveis'])) {
            $html .= "<div class='mt-3 p-3 bg-slate-800/50 rounded-lg border border-slate-700/50'>";
            $html .= "<p class='text-[10px] text-slate-500 uppercase mb-2'>Variáveis neste bloco (clique para inserir):</p>";
            $html .= "<div class='flex flex-wrap gap-2'>";
            foreach ($bloco['variaveis'] as $var) {
                $valor = $this->resolver->resolve($var);
                $curto = mb_strlen($valor) > 25 ? mb_substr($valor, 0, 25) . '...' : $valor;
                $isResolved = ($valor !== "[{$var}]");
                
                $html .= "<button type='button' onclick=\"inserirVariavel('editor-{$index}', '{{{$var}}}')\" ";
                $html .= "class='group flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-[11px] transition-all ";
                $html .= $isResolved ? "bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20" : "bg-slate-700/50 text-slate-400 border border-slate-600 hover:bg-slate-600";
                $html .= "' title='{$valor}'>";
                $html .= "<span class='font-mono'>{{{$var}}}</span>";
                $html .= "<span class='text-slate-500 group-hover:text-slate-300'>→</span>";
                $html .= "<span class='truncate max-w-[100px]'>{$curto}</span>";
                $html .= "</button>";
            }
            $html .= "</div>";
            $html .= "</div>";
        }
        
        $html .= "</div>";
        return $html;
    }
    
    /**
     * Editor de tabela editável (células viram inputs)
     */
    private function renderEditorTabela($bloco, $nomeBase, $index) {
        $linhas = $bloco['linhas'] ?? [];
        
        $html = "<div class='space-y-3'>";
        $html .= "<div class='overflow-x-auto rounded-lg border border-slate-700/50'>";
        $html .= "<table class='w-full text-sm' id='tabela-{$index}'>";
        
        foreach ($linhas as $i => $linha) {
            $isHeader = ($i === 0);
            $html .= "<tr class='" . ($isHeader ? 'bg-slate-800/80' : 'bg-slate-800/40 border-t border-slate-700/30') . "'>";
            
            foreach ($linha as $j => $celula) {
                $textoOriginal = $celula['texto'] ?? '';
                $textoProcessado = $this->resolver->substituirVariaveis($textoOriginal);
                $colspan = $celula['colspan'] ?? 1;
                
                $nomeCelula = "{$nomeBase}_cell_{$i}_{$j}";
                
                if ($isHeader) {
                    $html .= "<th class='p-3 text-left text-slate-300 font-semibold border-r border-slate-700/30 last:border-r-0' colspan='{$colspan}'>";
                    $html .= "<input type='text' name='{$nomeCelula}' value='" . htmlspecialchars($textoProcessado, ENT_QUOTES) . "' ";
                    $html .= "class='w-full bg-transparent border-none text-slate-300 font-semibold focus:outline-none focus:bg-slate-700/30 rounded px-2 py-1' ";
                    $html .= "placeholder='Cabeçalho...'>";
                    $html .= "</th>";
                } else {
                    $html .= "<td class='p-3 text-slate-400 border-r border-slate-700/30 last:border-r-0' colspan='{$colspan}'>";
                    $html .= "<input type='text' name='{$nomeCelula}' value='" . htmlspecialchars($textoProcessado, ENT_QUOTES) . "' ";
                    $html .= "class='w-full bg-transparent border-none text-slate-400 focus:outline-none focus:bg-slate-700/30 focus:text-slate-200 rounded px-2 py-1 transition-colors' ";
                    $html .= "placeholder='Conteúdo...'>";
                    $html .= "</td>";
                }
            }
            
            $html .= "</tr>";
        }
        
        $html .= "</table>";
        $html .= "</div>";
        
        // Botões de controle da tabela
        $html .= "<div class='flex gap-2'>";
        $html .= "<button type='button' onclick='adicionarLinha({$index})' class='px-3 py-1.5 rounded-lg bg-slate-700/50 text-slate-300 text-xs hover:bg-slate-600 transition-colors'>";
        $html .= "<i class='bi bi-plus-lg'></i> Linha";
        $html .= "</button>";
        $html .= "<button type='button' onclick='removerLinha({$index})' class='px-3 py-1.5 rounded-lg bg-slate-700/50 text-slate-300 text-xs hover:bg-red-500/20 hover:text-red-400 transition-colors'>";
        $html .= "<i class='bi bi-dash-lg'></i> Linha";
        $html .= "</button>";
        $html .= "</div>";
        
        // Hidden para estrutura da tabela (JSON)
        $html .= "<input type='hidden' name='{$nomeBase}_estrutura' id='estrutura-{$index}' value='" . htmlspecialchars(json_encode($linhas), ENT_QUOTES) . "'>";
        $html .= "<input type='hidden' name='{$nomeBase}_tipo' value='tabela'>";
        
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
            try {
                require_once $caminhoModelo;
                $classeModelo = 'SGT\\Propostas\\Modelo' . preg_replace('/[^a-zA-Z0-9]/', '', $modeloDocxAtivo);
                
                if (class_exists($classeModelo)) {
                    $instanciaModelo = new $classeModelo();
                    $config = $instanciaModelo->getConfig();
                    $docxData = $config;
                    $modoDocx = true;
                } else {
                    error_log("Editor - Classe DOCX não encontrada: {$classeModelo}");
                    die("<div style='padding:20px; background:#fff3cd; color:#856404; font-family:sans-serif;'><b>Aviso:</b> A classe {$classeModelo} não foi encontrada no arquivo gerado. O upload do seu DOCX pode ter falhado ou o nome possui caracteres inválidos. Tente fazer o upload novamente no Gerador.</div>");
                }
            } catch (Throwable $e) { // Usando Throwable para pegar Error fatal no PHP7/8
                error_log("Editor - Erro Fatal carregando DOCX: " . $e->getMessage());
                die("<div style='padding:20px; background:#f8d7da; color:#721c24; font-family:sans-serif;'><b>Erro Crítico no DOCX:</b> " . $e->getMessage() . "<br><br>Verifique se o seu template possui variáveis que conflitam com código PHP (por exemplo, chaves de array sem aspas).</div>");
            }
        } else {
            die("<div style='padding:20px; background:#fff3cd; color:#856404; font-family:sans-serif;'><b>Aviso:</b> O arquivo físico do modelo DOCX ({$modeloDocxAtivo}) não foi encontrado no servidor. Por favor, volte ao Gerador DOCX e envie o arquivo novamente.</div>");
        }
    }
    
    // Se não achou DOCX válido, verifica se tem modelo salvo na proposta ou usa fallback
    if (!$modoDocx) {
        // CORREÇÃO: ProposalArchitect não existe no servidor — removendo chamada que causava Fatal Error silencioso
        // Tenta usar o primeiro modelo DOCX disponível no sistema como fallback
        if (!empty($modelosDisponiveis)) {
            $primeiroModelo = $modelosDisponiveis[0];
            $modeloDocxAtivo = $primeiroModelo['id'];
            $caminhoModelo = __DIR__ . '/modelos_gerados/Modelo' . preg_replace('/[^a-zA-Z0-9]/', '', $modeloDocxAtivo) . '.php';
            
            if (file_exists($caminhoModelo)) {
                try {
                    require_once $caminhoModelo;
                    $classeModelo = 'SGT\\Propostas\\Modelo' . preg_replace('/[^a-zA-Z0-9]/', '', $modeloDocxAtivo);
                    if (class_exists($classeModelo)) {
                        $instanciaModelo = new $classeModelo();
                        $docxData = $instanciaModelo->getConfig();
                        $modoDocx = true;
                    }
                } catch (Throwable $e) {
                    error_log("Editor fallback DOCX falhou: " . $e->getMessage());
                }
            }
        }
        
        // Se ainda não tem modelo, define estrutura de metadata básica para o modo legacy funcionar
        if (!$modoDocx) {
            $metadata = ['name' => 'Editor de Proposta', 'blocos' => []];
        }
    }
    
} catch (Exception $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

// =====================================================
// VIEW
// =====================================================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor SGT Premium | <?= $modoDocx ? ($docxData['nome'] ?? 'Modelo DOCX') : ($metadata['name'] ?? 'Proposta') ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        slate: {
                            850: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Glassmorphism Premium */
        .glass-premium {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.8) 100%);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 
                        inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }
        
        .glass-legacy {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .glass-card {
            position: relative;
            overflow: hidden;
        }
        
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        }
        
        /* Animações */
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-slide-in {
            animation: slideIn 0.5s ease-out forwards;
        }
        
        /* Scrollbar Premium */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.5);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.5);
        }
        
        /* TinyMCE Custom Dark Theme */
        .tox.tox-tinymce {
            border-radius: 0.75rem !important;
            border: 1px solid rgba(71, 85, 105, 0.5) !important;
        }
        .tox-editor-container {
            background: rgba(15, 23, 42, 0.8) !important;
        }
        .tox-edit-area__iframe {
            background: rgba(15, 23, 42, 0.8) !important;
        }
        
        /* Botões */
        .btn-premium {
            position: relative;
            overflow: hidden;
        }
        .btn-premium::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn-premium:hover::after {
            left: 100%;
        }
        
        /* Dropdown */
        .dropdown-menu {
            transform-origin: top right;
            transition: transform 0.2s, opacity 0.2s;
        }
        .dropdown-menu.hidden {
            transform: scale(0.95);
            opacity: 0;
            pointer-events: none;
        }
        .dropdown-menu:not(.hidden) {
            transform: scale(1);
            opacity: 1;
            pointer-events: auto;
        }
    </style>
</head>

<?php $theme = new ThemeManager($modoDocx); ?>
<body class="<?= $theme->get('bg') ?> text-slate-100 font-sans antialiased min-h-screen">

<?php /* === DIAGNÓSTICO TEMPORÁRIO - REMOVER APÓS DEBUG === */
echo '<div style="position:fixed;top:0;left:0;z-index:9999;background:#1e293b;color:#94a3b8;font-family:monospace;font-size:12px;padding:10px;border:2px solid #f97316;max-width:600px;">';
echo '<b style="color:#f97316">🔍 DIAGNÓSTICO EDITOR</b><br>';
echo 'modoDocx: ' . ($modoDocx ? 'TRUE ✅' : 'FALSE ❌') . '<br>';
echo 'docxData[nome]: ' . ($docxData['nome'] ?? '-- ausente --') . '<br>';
echo 'id_prop: ' . $id_prop . '<br>';
echo 'theme.bg: ' . $theme->get('bg') . '<br>';
echo 'blocos count: ' . count($docxData['blocos'] ?? []) . '<br>';
echo '</div>';
?>

    <!-- Header Premium -->
    <header class="sticky top-0 z-50 glass-premium border-b border-white/10">
        <div class="max-w-[1920px] mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <!-- Esquerda: Logo e Info -->
                <div class="flex items-center gap-4">
                    <a href="painel.php" class="group flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 hover:border-emerald-500/30 transition-all">
                        <i class="bi bi-arrow-left text-lg group-hover:-translate-x-0.5 transition-transform"></i>
                    </a>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-<?= $modoDocx ? 'emerald' : 'orange' ?>-500/20 to-<?= $modoDocx ? 'emerald' : 'orange' ?>-600/20 flex items-center justify-center border border-<?= $modoDocx ? 'emerald' : 'orange' ?>-500/30">
                            <i class="bi bi-<?= $modoDocx ? 'file-earmark-word' : 'pencil-square' ?> text-xl text-<?= $modoDocx ? 'emerald' : 'orange' ?>-400"></i>
                        </div>
                        <div>
                            <h1 class="font-bold text-white text-lg leading-tight">
                                <?= $modoDocx ? ($docxData['nome'] ?? 'Modelo Word') : ($metadata['name'] ?? 'Editor Legacy') ?>
                            </h1>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider bg-<?= $modoDocx ? 'emerald' : 'orange' ?>-500/20 text-<?= $modoDocx ? 'emerald' : 'orange' ?>-400 border border-<?= $modoDocx ? 'emerald' : 'orange' ?>-500/20">
                                    <?= $modoDocx ? 'DOCX Genérico' : 'Legacy SGT' ?>
                                </span>
                                <span class="text-xs text-slate-500">#<?= $id_prop ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Direita: Ações -->
                <div class="flex items-center gap-3">
                    <!-- Salvar Rascunho -->
                    <button type="button" onclick="salvarRascunho()" id="btn-rascunho"
                        class="btn-premium px-4 py-2.5 rounded-xl bg-slate-700 text-slate-200 font-medium text-sm hover:bg-slate-600 border border-slate-600 transition-all flex items-center gap-2 shadow-lg shadow-black/20">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span>Salvar Rascunho</span>
                    </button>
                    
                    <!-- Visualizar -->
                    <button type="button" onclick="submitForm('html')" id="btn-visualizar"
                        class="btn-premium px-4 py-2.5 rounded-xl bg-blue-600 text-white font-medium text-sm hover:bg-blue-500 transition-all flex items-center gap-2 shadow-lg shadow-blue-500/20">
                        <i class="bi bi-eye"></i>
                        <span>Visualizar</span>
                    </button>
                    
                    <!-- Dropdown Modelos -->
                    <div class="relative" id="dropdown-container">
                        <button type="button" onclick="toggleDropdown()" id="btn-modelos"
                            class="btn-premium px-4 py-2.5 rounded-xl bg-slate-700 text-slate-200 font-medium text-sm hover:bg-slate-600 border border-slate-600 transition-all flex items-center gap-2">
                            <i class="bi bi-collection"></i>
                            <span><?= $modeloDocxAtivo ? str_replace('_', ' ', $modeloDocxAtivo) : 'Modelo Padrão' ?></span>
                            <i class="bi bi-chevron-down text-xs"></i>
                        </button>
                        
                        <div id="dropdown-modelos" class="dropdown-menu hidden absolute right-0 mt-2 w-72 glass-premium rounded-xl border border-white/10 shadow-2xl z-50 py-2">
                            <div class="px-3 py-2 border-b border-white/5">
                                <p class="text-xs font-semibold text-slate-500 uppercase">Selecionar Modelo</p>
                            </div>
                            
                            <a href="?id=<?= $id_prop ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors <?= !$modoDocx ? 'bg-orange-500/10 text-orange-400' : 'text-slate-300' ?>">
                                <i class="bi bi-layers text-lg"></i>
                                <div>
                                    <p class="text-sm font-medium">Modelo Tradicional</p>
                                    <p class="text-[10px] text-slate-500">Sistema SGT Legacy</p>
                                </div>
                            </a>
                            
                            <div class="border-t border-white/5 my-2"></div>
                            <p class="px-4 py-1 text-[10px] font-semibold text-slate-500 uppercase">Modelos DOCX</p>
                            
                            <?php foreach ($modelosDisponiveis as $mod): ?>
                                <a href="?id=<?= $id_prop ?>&modelo_docx=<?= $mod['id'] ?>" 
                                   class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors <?= ($modeloDocxAtivo === $mod['id']) ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-300' ?>">
                                    <i class="bi bi-file-earmark-word text-lg"></i>
                                    <div>
                                        <p class="text-sm font-medium"><?= $mod['nome'] ?></p>
                                        <p class="text-[10px] text-slate-500">DOCX Parser</p>
                                    </div>
                                    <?php if ($modeloDocxAtivo === $mod['id']): ?>
                                        <i class="bi bi-check-circle-fill ml-auto text-emerald-400"></i>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                            
                            <div class="border-t border-white/5 my-2"></div>
                            <a href="gerador_upload_docx.php" target="_blank" class="flex items-center gap-3 px-4 py-3 text-emerald-400 hover:bg-emerald-500/10 transition-colors">
                                <i class="bi bi-plus-circle text-lg"></i>
                                <span class="text-sm font-medium">Gerenciar Modelos DOCX</span>
                            </a>
                        </div>
                    </div>

                    <!-- Gerar Word -->
                    <button type="button" onclick="submitForm('docx')" id="btn-word"
                        class="btn-premium px-5 py-2.5 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 text-white font-medium text-sm hover:from-orange-400 hover:to-orange-500 transition-all flex items-center gap-2 shadow-lg shadow-orange-500/20">
                        <i class="bi bi-file-earmark-word"></i>
                        <span>Gerar Word</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        
        <!-- Sidebar com variáveis disponíveis (apenas modo DOCX) -->
        <?php if ($modoDocx && !empty($docxData['variaveis'])): ?>
        <aside class="w-72 glass-premium border-r border-white/10 overflow-y-auto hidden lg:block custom-scrollbar">
            <div class="p-5">
                <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="bi bi-magic"></i> Variáveis Detectadas
                </h3>
                
                <div class="mb-4 p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                    <p class="text-[11px] text-emerald-200 leading-relaxed">
                        <i class="bi bi-info-circle mr-1"></i> 
                        Clique em uma variável abaixo para copiá-la ou inseri-la no texto.
                    </p>
                </div>

                <div class="space-y-2">
                    <?php foreach ($docxData['variaveis'] as $var): 
                        $valor = $varResolver->resolve($var);
                        // Troca de mb_strlen/mb_substr por tratamento embutido sem dependência da extensão mbstring
                        $curto = (strlen($valor) > 30) ? substr($valor, 0, 30) . '...' : $valor;
                        $isResolved = ($valor !== "[{$var}]");
                    ?>
                        <button type="button" onclick="copiarVariavel('{{<?= htmlspecialchars($var) ?>}}')" class="w-full text-left group bg-slate-800/50 hover:bg-slate-700/50 border <?= $isResolved ? 'border-emerald-500/30' : 'border-slate-700' ?> rounded-xl p-3 transition-all duration-200">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[11px] <?= $isResolved ? 'text-emerald-400' : 'text-slate-400' ?> font-mono font-semibold">${<?= htmlspecialchars($var) ?>}</span>
                                <i class="bi bi-clipboard opacity-0 group-hover:opacity-100 transition-opacity text-slate-400 text-xs"></i>
                            </div>
                            <div class="text-[11px] text-slate-300 truncate" title="<?= htmlspecialchars($valor) ?>"><?= htmlspecialchars($curto) ?></div>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
        <?php elseif (!$modoDocx): ?>
        <!-- NAV LEGACY -->
        <aside class="w-64 glass-legacy border-r border-white/10 overflow-y-auto hidden md:block custom-scrollbar">
            <div class="p-5">
                <h3 class="text-xs font-bold text-orange-400 uppercase tracking-widest mb-4">Estrutura (Legacy)</h3>
                <nav class="space-y-1">
                    <?php
                    function renderNav($nodes, $depth = 0) {
                        foreach ($nodes as $node) {
                            $padding = $depth * 1.25;
                            $activeClass = $depth === 0 ? 'text-slate-200 font-semibold' : 'text-slate-400 text-[11px]';
                            echo "<a href='#block-{$node['id']}' class='flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/5 transition-all {$activeClass}' style='padding-left: calc(0.75rem + {$padding}rem)'>";
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
        <main class="flex-1 overflow-y-auto p-8 scroll-smooth custom-scrollbar relative" id="main-scroll">
            <div class="max-w-4xl mx-auto pb-32">
                
                <form id="formProposta" method="POST" action="salvar_proposta.php">
                    <input type="hidden" name="id_proposta" value="<?= $id_prop ?>">
                    <input type="hidden" name="id_proposta_original" value="<?= $id_prop ?>">
                    <input type="hidden" name="modelo_docx" value="<?= htmlspecialchars($modeloDocxAtivo ?? '') ?>">
                    <input type="hidden" name="formato_saida" id="inputFormatoSaida" value="html">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <?php if ($modoDocx && $docxData): ?>
                        <!-- MODO DOCX: Renderização Genérica -->
                        <div class="mb-8 p-5 bg-gradient-to-r from-emerald-500/10 to-transparent border border-emerald-500/20 rounded-2xl animate-slide-in">
                            <h2 class="text-emerald-400 font-bold text-lg flex items-center gap-3">
                                <i class="bi bi-magic text-xl"></i> 
                                Editando: <?= $docxData['nome'] ?>
                            </h2>
                            <p class="text-sm text-slate-300 mt-2">
                                Revise os blocos de texto e tabelas abaixo. O conteúdo será perfeitamente encaixado no seu modelo original.
                            </p>
                        </div>
                        
                        <?php
                        $renderer = new GenericBlockRenderer($incomingData, $theme);
                        
                        // [V3.0] Prepara os blocos com o conteúdo salvo anteriormente
                        $blocosSalvosParaDocx = [];
                        if(!empty($incomingData['docx_conteudo'])) {
                            $decoded = json_decode($incomingData['docx_conteudo'], true);
                            if(is_array($decoded)) $blocosSalvosParaDocx = $decoded;
                        }
                        
                        $blocosParaRender = $docxData['blocos'];
                        if(!empty($blocosSalvosParaDocx)) {
                            // Mescla conteúdo salvo -> tabelas editadas e textos
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
                        <div class="mb-8 p-5 bg-gradient-to-r from-orange-500/10 to-transparent border border-orange-500/20 rounded-2xl animate-slide-in">
                            <h2 class="text-orange-400 font-bold text-lg flex items-center gap-3">
                                <i class="bi bi-clock-history text-xl"></i> 
                                Modo Legacy (V2)
                            </h2>
                            <p class="text-sm text-slate-300 mt-2">
                                Interface antiga ativa. Considere migrar para o <span class="text-emerald-400 font-semibold">Parser DOCX Dinâmico</span> escolhendo um modelo acima.
                            </p>
                        </div>
                        
                        <?php
                        // FUNÇÕES DO RENDER LEGACY 
                        function renderFieldLegacy($name, $label, $type, $value, $full = false) {
                            $span = $full ? 'md:col-span-2' : '';
                            echo "<div class='{$span}'>";
                            echo "<label class='block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1'>{$label}</label>";
                            $valSafe = htmlspecialchars($value);
                            if ($type === 'textarea') {
                                echo "<textarea name='{$name}' id='ed-{$name}' class='w-full bg-slate-800/50 border border-slate-600/50 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/50 text-sm transition-all' rows='4'>{$valSafe}</textarea>";
                            } else {
                                echo "<input type='{$type}' name='{$name}' value='{$valSafe}' class='w-full bg-slate-800/50 border border-slate-600/50 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/50 text-sm transition-all'>";
                            }
                            echo "</div>";
                        }
                        
                        // [O MESMO RENDER FORMS DA V2]
                        function renderFormBlocksLegacy($nodes, $model, $incomingData, $variaveis) {
                            foreach ($nodes as $node) {
                                $slug = $node['id'];
                                if (in_array($slug, ['recursos_equipamentos', 'cronograma_drone', 'investimento-extra'])) continue;
                                $borderColors = [ 'presentation' => 'border-l-blue-500', 'technical' => 'border-l-indigo-500', 'financial' => 'border-l-emerald-500', 'legal' => 'border-l-slate-500' ];
                                $borderColor = $borderColors[$node['category']] ?? 'border-l-slate-600';

                                $defaultContent = $node['default_content'] ?? '';
                                $userValue = $incomingData[$slug . '_content'] ?? ($incomingData[$slug] ?? '');
                                $rawContent = (!empty($userValue)) ? $userValue : $defaultContent;
                                $finalContent = substituicaoVariaveisLegacy($rawContent, $variaveis);

                                echo "<div id='block-{$slug}' class='glass-card rounded-2xl border-l-4 {$borderColor} block-card p-6 mb-8 transition-all hover:-translate-y-1 hover:shadow-xl'>";
                                echo "<div class='flex justify-between items-center mb-6 pb-4 border-b border-white/5'>";
                                echo "<div class='flex items-center gap-3'><h3 class='text-lg font-bold text-white'>{$node['title']}</h3></div>";
                                echo "</div>";
                                echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-6'>";

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

    <!-- Sistema de Toast unificado -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-[100] flex flex-col gap-3"></div>

    <script>
        // Dropdown
        function toggleDropdown() {
            const menu = document.getElementById('dropdown-modelos');
            menu.classList.toggle('hidden');
        }

        document.addEventListener('click', (e) => {
            const menu = document.getElementById('dropdown-modelos');
            const btn = document.getElementById('btn-modelos');
            if (menu && !menu.classList.contains('hidden') && !menu.contains(e.target) && !btn.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        // Toggle Blocos Genéricos
        function toggleBloco(index) {
            const content = document.getElementById(`content-${index}`);
            const icone = document.querySelector(`#docx-bloco-${index} button i`);
            if(content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icone.classList.replace('bi-arrows-collapse', 'bi-arrows-expand');
            } else {
                content.classList.add('hidden');
                icone.classList.replace('bi-arrows-expand', 'bi-arrows-collapse');
            }
        }

        // Sistema de Toast
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            const colors = {
                success: 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400',
                error: 'bg-red-500/10 border-red-500/20 text-red-400',
                info: 'bg-blue-500/10 border-blue-500/20 text-blue-400'
            };
            
            const icons = {
                success: 'bi-check-circle',
                error: 'bi-x-circle',
                info: 'bi-info-circle'
            };

            toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl border backdrop-blur-md shadow-lg transform transition-all duration-300 translate-y-10 opacity-0 ${colors[type]}`;
            toast.innerHTML = `
                <i class="bi ${icons[type]} text-lg"></i>
                <span class="text-sm font-medium">${message}</span>
            `;

            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-y-10', 'opacity-0');
            }, 10);

            setTimeout(() => {
                toast.classList.add('translate-y-10', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Variáveis
        function copiarVariavel(texto) {
            navigator.clipboard.writeText(texto).then(() => {
                showToast(`Copiado: ${texto}`, 'info');
            });
        }

        function inserirVariavel(editorId, varString) {
            const editor = tinymce.get(editorId);
            if (editor) {
                editor.insertContent(varString);
                showToast(`Variável inserida`, 'success');
            } else {
                // Tenta input nativo
                const el = document.getElementById(editorId);
                if(el) {
                    const start = el.selectionStart;
                    const end = el.selectionEnd;
                    el.value = el.value.substring(0, start) + varString + el.value.substring(end);
                    showToast(`Variável inserida`, 'success');
                }
            }
        }

        // --- CONTROLE DE TABELAS DINÂMICAS ---
        function atualizarEstruturaTabela(index) {
            const tabela = document.getElementById(`tabela-${index}`);
            if (!tabela) return;
            
            const htmlLinhas = tabela.querySelectorAll('tr');
            const dados = [];
            
            htmlLinhas.forEach(tr => {
                const linhaArray = [];
                tr.querySelectorAll('th, td').forEach(celula => {
                    const input = celula.querySelector('input');
                    linhaArray.push({
                        texto: input ? input.value : celula.innerText,
                        colspan: celula.getAttribute('colspan') ? parseInt(celula.getAttribute('colspan')) : 1
                    });
                });
                dados.push(linhaArray);
            });
            
            document.getElementById(`estrutura-${index}`).value = JSON.stringify(dados);
        }

        function adicionarLinha(index) {
            const tabelaHTML = document.getElementById(`tabela-${index}`);
            if (!tabelaHTML) return;
            
            // Pega a última linha para saber quantas células criar
            const ultimaLinha = tabelaHTML.querySelector('tr:last-child');
            if(!ultimaLinha) return;
            
            const numCelulas = ultimaLinha.querySelectorAll('td').length;
            const nomeBase = `docx_bloco_${index}`;
            const novoIndiceLinha = tabelaHTML.querySelectorAll('tr').length;
            
            const novaTr = document.createElement('tr');
            novaTr.className = 'bg-slate-800/40 border-t border-slate-700/30';
            
            for(let j=0; j<numCelulas; j++) {
                const td = document.createElement('td');
                td.className = 'p-3 text-slate-400 border-r border-slate-700/30 last:border-r-0';
                
                const input = document.createElement('input');
                input.type = 'text';
                input.name = `${nomeBase}_cell_${novoIndiceLinha}_${j}`;
                input.className = 'w-full bg-transparent border-none text-slate-400 focus:outline-none focus:bg-slate-700/30 focus:text-slate-200 rounded px-2 py-1 transition-colors';
                input.placeholder = 'Conteúdo...';
                
                td.appendChild(input);
                novaTr.appendChild(td);
            }
            
            tabelaHTML.querySelector('tbody').appendChild(novaTr);
            atualizarEstruturaTabela(index);
        }

        function removerLinha(index) {
            const tabelaHTML = document.getElementById(`tabela-${index}`);
            if (!tabelaHTML) return;
            
            const linhas = tabelaHTML.querySelectorAll('tr');
            if (linhas.length > 1) { // Não apagar cabeçalho
                linhas[linhas.length - 1].remove();
                atualizarEstruturaTabela(index);
            } else {
                showToast("Não é possível remover o cabeçalho.", "error");
            }
        }

        // --- INICIALIZAÇÃO E HOTKEYS ---
        const tinyMCEInstances = [];

        document.addEventListener("DOMContentLoaded", function() {
            tinymce.init({
                selector: 'textarea.tinymce-editor',
                height: 350,
                skin: "oxide-dark",
                content_css: "dark",
                menubar: false,
                statusbar: false,
                plugins: 'lists link code table wordcount autosave',
                toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | removeformat | code',
                setup: function(editor) {
                    // Atualiza a textarea real a cada mudança
                    editor.on('change input blur', function() {
                        editor.save(); 
                    });
                    editor.on('init', function() {
                        tinyMCEInstances.push(editor);
                    });
                }
            });
            
            // Tratamento de tabelas nos inputs para atualizar hidden auto
            document.querySelectorAll('input[name*="_cell_"]').forEach(inp => {
                inp.addEventListener('blur', function() {
                    const idTabela = this.closest('table').id;
                    const index = idTabela.split('-')[1];
                    atualizarEstruturaTabela(index);
                });
            });
            
            // Atalho Ctrl+S
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    salvarRascunho();
                }
            });
        });

        // --- SUBMISSÕES (RASCUNHO E FINAL) ---
        async function salvarRascunho() {
            tinymce.triggerSave();
            
            // Atualiza todas as tabelas antes de enviar
            document.querySelectorAll('[id^="tabela-"]').forEach(tabela => {
                const index = tabela.id.replace('tabela-', '');
                atualizarEstruturaTabela(index);
            });
            
            const btn = document.getElementById('btn-rascunho');
            const originalIcon = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i><span>Salvando...</span>';
            
            try {
                const formData = new FormData(document.getElementById('formProposta'));
                formData.append('ajax', '1');
                
                const resp = await fetch('salvar_rascunho.php', { 
                    method: 'POST', 
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const textResp = await resp.text();
                let data;
                try {
                    data = JSON.parse(textResp);
                } catch(e) {
                    console.error("Resposta não-JSON:", textResp);
                    throw new Error("Erro de servidor (HTML retornado).");
                }
                
                if (data.success) {
                    btn.innerHTML = '<i class="bi bi-check-circle text-emerald-400"></i><span>Salvo!</span>';
                    showToast('Rascunho salvo com sucesso!');
                    setTimeout(() => btn.innerHTML = originalIcon, 2000);
                } else {
                    throw new Error(data.error || "Erro desconhecido");
                }
            } catch(e) {
                showToast(e.message, 'error');
                btn.innerHTML = originalIcon;
            } finally {
                btn.disabled = false;
            }
        }

        async function submitForm(formato) {
            tinymce.triggerSave();
            
            // Atualiza todas as tabelas antes de enviar
            document.querySelectorAll('[id^="tabela-"]').forEach(tabela => {
                const index = tabela.id.replace('tabela-', '');
                atualizarEstruturaTabela(index);
            });
            
            const form = document.getElementById('formProposta');
            const formData = new FormData(form);
            formData.append('formato_saida', formato);
            formData.append('ajax', '1');
            
            const btnHtml = document.getElementById('btn-visualizar');
            const btnWord = document.getElementById('btn-word');
            const targetBtn = formato === 'html' ? btnHtml : btnWord;
            const originalIcon = targetBtn.innerHTML;
            
            targetBtn.disabled = true;
            targetBtn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Processando...';
            
            try {
                const resp = await fetch('salvar_proposta.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const textResp = await resp.text();
                let data;
                try {
                    data = JSON.parse(textResp);
                } catch(err) {
                    console.error(textResp);
                    throw new Error('Retorno do servidor inválido (possível erro PHP).');
                }
                
                if (data.success) {
                    showToast('Documento gerado!', 'success');
                    if (formato === 'html') {
                        window.open(data.redirect, '_blank');
                    } else {
                        window.location.href = data.redirect;
                    }
                } else {
                    throw new Error(data.error);
                }
            } catch(e) {
                showToast(e.message, 'error');
            } finally {
                targetBtn.innerHTML = originalIcon;
                targetBtn.disabled = false;
            }
        }
    </script>
</body>
</html>
