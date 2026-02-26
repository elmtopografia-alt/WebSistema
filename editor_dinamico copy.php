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

// Tenta obter ID da proposta
$id_proposta_ativo = (int)($_GET['id'] ?? 0);
$incomingData = [];

if ($id_proposta_ativo > 0) {
    try {
        $incomingData = $repo->buscarPorId($id_proposta_ativo) ?: [];
        
        // CORREÇÃO: Garante que equipamentos estão nos dados para o resolvedor
        // Se vieram vazios do banco, tenta reconstruir das locações
        if (empty($incomingData['modelo_drone']) || $incomingData['modelo_drone'] === 'Não se aplica') {
            if (!empty($incomingData['itens']['locacoes'])) {
                foreach ($incomingData['itens']['locacoes'] as $loc) {
                    $tipoNome = mb_strtolower($loc['tipo_nome'] ?? $loc['tipo'] ?? '');
                    $marcaNome = $loc['marca_nome'] ?? $loc['marca'] ?? 'Não informada';
                    
                    if (strpos($tipoNome, 'drone') !== false) {
                        $incomingData['modelo_drone'] = $marcaNome;
                    } elseif (strpos($tipoNome, 'gps') !== false || strpos($tipoNome, 'gnss') !== false) {
                        $incomingData['modelo_gps'] = $marcaNome;
                    } elseif (strpos($tipoNome, 'estação') !== false || strpos($tipoNome, 'estacao') !== false) {
                        $incomingData['modelo_estacao_total'] = $marcaNome;
                    } elseif (strpos($tipoNome, 'veículo') !== false || strpos($tipoNome, 'veiculo') !== false) {
                        $incomingData['modelo_veiculo'] = $marcaNome;
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Erro ao carregar proposta no editor: " . $e->getMessage());
    }
}

// Detecta modelo DOCX (URL > Banco > Null)
$modeloDocxAtivo = $_GET['modelo_docx'] ?? $incomingData['modelo_docx'] ?? null;

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
            'cidade_limpo'     => trim(explode(',', $d['cidade_obra'] ?? '')[0] ?? ''),
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
            'Drone'        => $d['modelo_drone']        ?? $d['drone']        ?? 'Não aplicável',
            'Veiculo'      => $d['modelo_veiculo']      ?? $d['veiculo']      ?? 'Não incluso',
            'Estacao_Total'=> $d['modelo_estacao_total']?? $d['estacao_total']?? 'Não inclusa',
            'GPS'          => $d['modelo_gps']          ?? $d['gps']          ?? 'Par de Receptores GNSS RTK',

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
            'restante_valor'         => $this->formatarMoedaSemPrefixo($d['restante_valor'] ?? 0)
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
        $html .= "<div class='flex justify-between items-center mb-4 pb-3 border-b border-white/10 block-header'>";
        $html .= "<div class='flex items-center gap-3'>";
        $html .= "<span class='w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold block-header-indicator' style='background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white;'>";
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
       // =====================================================
    // CORREÇÃO CRÍTICA: Preenche equipamentos antes do resolvedor
    // =====================================================
    // Verifica se equipamentos estão vazios ou "Não se aplica"
    $equipamentosVazios = 
        (empty($incomingData['modelo_drone']) || $incomingData['modelo_drone'] === 'Não se aplica') &&
        (empty($incomingData['modelo_gps']) || $incomingData['modelo_gps'] === 'Não se aplica') &&
        (empty($incomingData['modelo_estacao_total']) || $incomingData['modelo_estacao_total'] === 'Não se aplica') &&
        (empty($incomingData['modelo_veiculo']) || $incomingData['modelo_veiculo'] === 'Não se aplica');
    
    // Se equipamentos estão vazios mas temos locações no banco, reconstrói
    if ($equipamentosVazios && !empty($incomingData['itens']['locacoes'])) {
        // Converte formato do banco para o formato esperado por preencherEquipamentosFlat
        $incomingData['locacoes'] = [];
        foreach ($incomingData['itens']['locacoes'] as $loc) {
            $incomingData['locacoes'][] = [
                'tipo' => $loc['tipo'] ?? $loc['id_locacao'] ?? 0,
                'marca' => $loc['marca'] ?? $loc['id_marca'] ?? null
            ];
        }
        
        // Chama o método do repository para preencher os campos flat
        $repo->preencherEquipamentosFlat($incomingData);
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
            if (!isset($structure)) $structure = [];
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
        :root {
            --accent-emerald: 16, 185, 129;
            --accent-blue: 59, 130, 246;
        }

        body { font-family: 'Inter', sans-serif; }
        
        /* Glassmorphism Efeitos */
        .glass-premium {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .glass-card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* TinyMCE Dark Customization */
        .tox-tinymce {
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        }

        /* Scrollbar Superior */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.1); }
        ::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(148, 163, 184, 0.3); }

        /* Animações */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .block-card { animation: fadeIn 0.4s ease-out forwards; }
        
        /* Estilos específicos para o formulário Legacy */
        .legacy-field-group {
            @apply mb-6 p-5 rounded-xl bg-slate-800/40 border border-slate-700/50;
        }
        
        .legacy-label {
            @apply block text-sm font-semibold text-slate-300 mb-2 flex items-center gap-2;
        }

        #save-indicator {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="<?= $modoDocx ? ThemeManager::THEMES['premium']['bg'] : ThemeManager::THEMES['legacy']['bg'] ?> min-h-screen text-slate-200 selection:bg-emerald-500/30">

    <!-- Header / Nav Superior -->
    <header class="sticky top-0 z-50 w-full border-b border-white/5 bg-slate-950/80 backdrop-blur-xl">
        <div class="px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="editar_proposta.php?id=<?= $id_prop ?>" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-sm font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Editor Dinâmico 
                        <span class="text-slate-500 font-normal">/</span> 
                        <span class="text-emerald-400">#<?= $incomingData['numero_proposta'] ?? $id_prop ?></span>
                    </h1>
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-medium">
                        <?= $modoDocx ? 'Modo Premium DOCX' : 'Modo Professional Legacy' ?>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Status de Salvamento -->
                <div id="save-status" class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-800/50 border border-white/5 text-[11px] text-slate-400">
                    <span id="save-indicator" class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                    <span id="save-text">Alterações salvas</span>
                </div>

                <div class="h-6 w-px bg-white/10 mx-2"></div>

                <!-- Ações Principais -->
                <button type="button" onclick="salvarProposta()" class="flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-500 text-white text-sm font-bold hover:bg-emerald-400 hover:shadow-lg hover:shadow-emerald-500/20 active:scale-95 transition-all">
                    <i class="bi bi-check2-circle text-lg"></i>
                    <span>SALVAR TUDO</span>
                </button>
            </div>
        </div>
    </header>

    <div class="flex h-[calc(100vh-64px)] overflow-hidden">
        
        <!-- Sidebar de Configurações -->
        <aside class="w-80 border-r border-white/5 bg-slate-900/50 overflow-y-auto hidden lg:block p-6 custom-scrollbar">
            
            <!-- Widget Cliente -->
            <div class="mb-8">
                <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="bi bi-person-badge"></i> Dados do Cliente
                </h3>
                <div class="p-4 rounded-2xl bg-slate-800/40 border border-white/5">
                    <p class="text-xs font-bold text-white mb-1"><?= $variaveis['nome_cliente'] ?></p>
                    <p class="text-[10px] text-slate-500 mb-3 truncate"><?= $variaveis['email_cliente'] ?></p>
                    <div class="flex gap-2">
                        <span class="px-2 py-1 rounded-md bg-emerald-500/10 text-emerald-400 text-[9px] font-bold"><?= $variaveis['cidade_limpo'] ?></span>
                        <span class="px-2 py-1 rounded-md bg-blue-500/10 text-blue-400 text-[9px] font-bold uppercase"><?= $variaveis['estado_obra'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Seletor de Modelo -->
            <div class="mb-8">
                <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="bi bi-file-earmark-richtext"></i> Modelo da Proposta
                </h3>
                <div class="space-y-2">
                    <select id="select-modelo" onchange="trocarModelo(this.value)" class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:ring-2 focus:ring-emerald-500/50 outline-none transition-all">
                        <optgroup label="Modelos DOCX (V3.0)">
                            <?php foreach ($modelosDisponiveis as $mod): ?>
                                <option value="<?= $mod['id'] ?>" <?= ($modeloDocxAtivo == $mod['id']) ? 'selected' : '' ?>>
                                    📄 <?= $mod['nome'] ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Modelos Legados">
                            <option value="legacy" <?= !$modoDocx ? 'selected' : '' ?>>⚙️ Editor Estruturado</option>
                        </optgroup>
                    </select>
                    <p class="text-[9px] text-slate-600 px-1 italic">Dica: Alterar o modelo recarrega a página.</p>
                </div>
            </div>

            <!-- Navegação Rápida -->
            <div class="mb-8">
                <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="bi bi-list-nested"></i> Sumário
                </h3>
                <nav class="space-y-1" id="sumario-editor">
                    <?php if ($modoDocx): ?>
                        <?php foreach ($docxData['blocos'] as $idx => $bloco): ?>
                            <a href="#docx-bloco-<?= $idx ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs text-slate-400 hover:bg-white/5 hover:text-white transition-all group">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-emerald-500 transition-colors"></span>
                                <span class="truncate"><?= ucfirst($bloco['tipo']) ?> #<?= $idx+1 ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="px-3 py-2 text-[10px] text-slate-600 italic">Navegação automática para modelos estruturados.</div>
                    <?php endif; ?>
                </nav>
            </div>

        </aside>

        <!-- Área Principal do Editor -->
        <main class="flex-1 overflow-y-auto bg-slate-900/20 relative p-4 md:p-10 custom-scrollbar scroll-smooth">
            
            <form id="form-proposta-dinamica" class="max-w-4xl mx-auto pb-20">
                <!-- Hidden fields essenciais -->
                <input type="hidden" name="id_proposta" value="<?= $id_prop ?>">
                <input type="hidden" name="modelo_docx" value="<?= $modeloDocxAtivo ?>">
                <input type="hidden" name="modo_docx" value="<?= $modoDocx ? '1' : '0' ?>">

                <?php if ($modoDocx): ?>
                    <!-- AREA MODERNA (BLOCO A BLOCO) -->
                    <div class="mb-10 text-center">
                        <span class="inline-block px-4 py-1.5 rounded-full bg-emerald-500/10 text-emerald-400 text-[10px] font-bold uppercase tracking-tightest mb-4 border border-emerald-500/20">
                            Renderização Baseado em DOCX
                        </span>
                        <h2 class="text-3xl font-black text-white tracking-tight"><?= $docxData['nome'] ?></h2>
                    </div>

                    <?php 
                        $renderer = new GenericBlockRenderer($incomingData, new ThemeManager(true));
                        echo $renderer->renderDocxBlocks($docxData['blocos']);
                    ?>

                <?php else: ?>
                    <!-- AREA LEGACY (HARDCODED) -->
                    <div class="mb-10 text-center">
                        <span class="inline-block px-4 py-1.5 rounded-full bg-orange-500/10 text-orange-400 text-[10px] font-bold uppercase tracking-tightest mb-4 border border-orange-500/20">
                            Editor Estruturado Legacy
                        </span>
                        <h2 class="text-3xl font-black text-white tracking-tight">Proposta Comercial</h2>
                    </div>

                    <div class="space-y-6">
                        <!-- Aqui entraria o renderizador legacy se houvesse estrutura definida -->
                        <div class="glass-card rounded-2xl p-8 border border-white/5 text-center">
                            <i class="bi bi-exclamation-triangle text-orange-400 text-4xl mb-4 block"></i>
                            <h3 class="text-white font-bold mb-2">Editor Legado Indisponível</h3>
                            <p class="text-sm text-slate-400 max-w-sm mx-auto">Esta proposta não possui uma estrutura de dados estruturados. Recomendamos selecionar um dos modelos DOCX disponíveis na barra lateral para prosseguir.</p>
                            <button type="button" onclick="$('#select-modelo').focus()" class="mt-6 px-6 py-2 rounded-xl bg-orange-500/20 text-orange-400 text-xs font-bold hover:bg-orange-500/30 transition-all">
                                SELECIONAR MODELO DOCX
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

            </form>

            <!-- Copyright Floating -->
            <div class="fixed bottom-6 right-6 flex items-center gap-3">
                <div class="px-4 py-2 rounded-xl glass-premium text-[10px] font-medium text-slate-500 border border-white/5 shadow-2xl">
                    SGT PROPOSTAS <span class="text-slate-700 mx-1">|</span> V3.1.2
                </div>
            </div>

        </main>
    </div>

    <!-- Scripts -->
    <script>
        // Configuração Global do TinyMCE
        const tinymceConfig = {
            selector: '.tinymce-editor',
            height: 400,
            menubar: false,
            skin: "oxide-dark",
            content_css: "dark",
            plugins: 'advlist autolink lists link charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime table code help wordcount',
            toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | removeformat | code',
            content_style: `
                body { font-family: Inter, sans-serif; font-size: 14px; color: #e2e8f0; background-color: #0f172a; line-height: 1.6; }
                p { margin-bottom: 10px; }
                table { border-collapse: collapse; width: 100%; border: 1px solid #334155; }
                th, td { border: 1px solid #334155; padding: 8px; }
            `,
            setup: function(editor) {
                editor.on('change', function() {
                    setSaveStatus('pending');
                });
            }
        };

        $(document).ready(function() {
            tinymce.init(tinymceConfig);
            
            // Atalho de teclado (Ctrl+S)
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    salvarRascunhoSilencioso();
                }
            });
        });

        function setSaveStatus(status) {
            const indicator = $('#save-indicator');
            const text = $('#save-text');
            const banner = $('#save-status');

            banner.removeClass('hidden').addClass('flex');

            if (status === 'saving') {
                indicator.removeClass().addClass('w-1.5 h-1.5 rounded-full bg-blue-500 animate-ping');
                text.text('Salvando...');
            } else if (status === 'saved') {
                indicator.removeClass().addClass('w-1.5 h-1.5 rounded-full bg-emerald-500');
                text.text('Tudo salvo');
                setTimeout(() => banner.fadeOut(1000), 3000);
            } else if (status === 'pending') {
                indicator.removeClass().addClass('w-1.5 h-1.5 rounded-full bg-orange-500');
                text.text('Alteraçõe pendentes');
                banner.fadeIn();
            } else if (status === 'error') {
                indicator.removeClass().addClass('w-1.5 h-1.5 rounded-full bg-red-500');
                text.text('Erro ao salvar');
            }
        }

        function trocarModelo(id) {
            if (!id) return;
            const url = new URL(window.location.href);
            if (id === 'legacy') {
                url.searchParams.delete('modelo_docx');
            } else {
                url.searchParams.set('modelo_docx', id);
            }
            window.location.href = url.toString();
        }

        function inserirVariavel(editorId, variavel) {
            tinymce.get(editorId).insertContent(variavel);
        }

        function toggleBloco(index) {
            $(`#content-${index}`).slideToggle();
        }

        // Funções para manipulação de tabelas dinâmicas
        function adicionarLinha(index) {
            const table = document.getElementById(`tabela-${index}`);
            const structureInput = document.getElementById(`estrutura-${index}`);
            let estrutura = JSON.parse(structureInput.value);
            
            const numCols = table.rows[0].cells.length;
            const newRow = table.insertRow(-1);
            newRow.className = 'bg-slate-800/40 border-t border-slate-700/30';
            
            const novaEstruturaRow = [];
            
            for (let i = 0; i < numCols; i++) {
                const cell = newRow.insertCell(i);
                cell.className = 'p-3 text-slate-400 border-r border-slate-700/30 last:border-r-0';
                
                const nomeBase = `docx_bloco_${index}_cell_${table.rows.length-1}_${i}`;
                cell.innerHTML = `
                    <input type="text" name="${nomeBase}" value="" 
                    class="w-full bg-transparent border-none text-slate-400 focus:outline-none focus:bg-slate-700/30 focus:text-slate-200 rounded px-2 py-1 transition-colors" 
                    placeholder="Conteúdo...">
                `;
                novaEstruturaRow.push({ texto: '', colspan: 1 });
            }
            
            estrutura.push(novaEstruturaRow);
            structureInput.value = JSON.stringify(estrutura);
        }

        function removerLinha(index) {
            const table = document.getElementById(`tabela-${index}`);
            if (table.rows.length <= 2) return; // Mantém pelo menos o header e uma linha
            
            table.deleteRow(-1);
            
            const structureInput = document.getElementById(`estrutura-${index}`);
            let estrutura = JSON.parse(structureInput.value);
            estrutura.pop();
            structureInput.value = JSON.stringify(estrutura);
        }


        function salvarRascunhoSilencioso() {
            setSaveStatus('saving');
            
            const formData = new FormData(document.getElementById('form-proposta-dinamica'));
            
            // Sincroniza TinyMCE antes de enviar
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }

            // Coleta dados extras de tabelas se houver
            $('.modelo-docx-editor [data-tipo="tabela"]').each(function() {
                const index = $(this).attr('id').split('-').pop();
                const table = document.getElementById(`tabela-${index}`);
                const structure = [];
                
                for (let i = 0; i < table.rows.length; i++) {
                    const row = [];
                    for (let j = 0; j < table.rows[i].cells.length; j++) {
                        const input = table.rows[i].cells[j].querySelector('input');
                        row.push({ texto: input ? input.value : '', colspan: 1 });
                    }
                    structure.push(row);
                }
                formData.set(`docx_bloco_${index}_estrutura`, JSON.stringify(structure));
            });

            $.ajax({
                url: 'salvar_rascunho.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const res = typeof response === 'string' ? JSON.parse(response) : response;
                        if (res.success) {
                            setSaveStatus('saved');
                        } else {
                            setSaveStatus('error');
                            console.error('Erro no servidor:', res.message);
                        }
                    } catch (e) {
                        setSaveStatus('saved'); // Provavelmente retornou texto puro de sucesso
                    }
                },
                error: function() {
                    setSaveStatus('error');
                }
            });
        }

        async function salvarProposta() {
            setSaveStatus('saving');
            
            // Sincroniza editores
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }

            const formData = new FormData(document.getElementById('form-proposta-dinamica'));
            
            // Mostra loading overlay
            const btn = event?.target?.closest('button') || $('button[onclick="salvarProposta()"]')[0];
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split animate-spin text-lg"></i> <span>PROCESSANDO...</span>';
            btn.disabled = true;

            try {
                const response = await fetch('processar_proposta_dinamica.php', {
                    method: 'POST',
                    body: formData
                });

                const res = await response.json();

                if (res.success) {
                    setSaveStatus('saved');
                    
                    // Notificação flutuante de sucesso
                    const toast = $('<div class="fixed top-20 right-6 z-[100] px-6 py-4 rounded-2xl bg-emerald-500 text-white shadow-2xl flex items-center gap-4 animate-bounce">')
                        .html('<i class="bi bi-check-circle-fill text-2xl"></i> <div><p class="font-bold">Sucesso!</p><p class="text-xs opacity-90">Sua proposta foi processada e salva.</p></div>');
                    
                    $('body').append(toast);
                    
                    setTimeout(() => {
                        toast.fadeOut(() => {
                            toast.remove();
                            // Redireciona para visualização
                            window.location.href = `visualizar_proposta.php?id=<?= $id_prop ?>`;
                        });
                    }, 2000);
                    
                } else {
                    setSaveStatus('error');
                    alert('Erro ao salvar: ' + (res.message || 'Erro desconhecido'));
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }
            } catch (error) {
                setSaveStatus('error');
                console.error('Erro:', error);
                alert('Erro na comunicação com o servidor.');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        }

        function visualizarNoCRM() {
            window.location.href = 'painel_propostas.php';
        }
    </script>
</body>
</html>


