<?php
/**
 * SGT ModeloBase v2
 * Classe base abstrata para todos os modelos de proposta.
 * Compatível com PHP 7.4+ (sem match expression) e sem namespaces (sistema legado).
 */

require_once __DIR__ . '/TemaEngine.php';

abstract class ModeloBase
{
    protected TemaEngine $tema;
    protected array $blocos   = [];
    protected array $variaveis = [];

    abstract protected function definirBlocos(): array;
    abstract public function getNome(): string;

    public function __construct(string $cor = null)
    {
        // Prioridade: 1. Argumento explicito (escolha do usuario no Passo 2)
        //             2. Constante COR_PADRAO (do modelo legado)
        //             3. Default 'verde'
        $corFinal = $cor;
        
        if (empty($corFinal) && defined('static::COR_PADRAO')) {
             $corFinal = static::COR_PADRAO;
        }
        
        $this->tema     = new TemaEngine($corFinal ?? 'verde');
        $this->blocos   = $this->definirBlocos();
        $this->variaveis = $this->extrairVariaveis();
    }

    /**
     * Renderiza o modelo completo.
     * $resolvedor deve ser uma instância de ResolvedorChavesSistema (ou mock de teste).
     */
    public function render(array $dados, $resolvedor, int $usuarioId): string
    {
        $contexto = $this->prepararContexto($dados, $resolvedor, $usuarioId);

        $html  = $this->abrirContainer();
        foreach ($this->blocos as $bloco) {
            $html .= $this->renderBloco($bloco, $contexto);
        }
        $html .= $this->fecharContainer();

        return $html;
    }

    // ─── Container ────────────────────────────────────────────────────────────

    public function getCorAtiva(): string
    {
        return $this->tema->getCorAtiva();
    }

    public function getBlocos(): array
    {
        return $this->blocos;
    }

    public function getVariaveis(): array
    {
        return $this->variaveis;
    }

    protected function abrirContainer(): string
    {
        $cssUrl = $this->tema->getCssUrl();
        return "<div class='sgt-proposta'>
                <link rel='stylesheet' href='{$cssUrl}'>
                <main class='sgt-conteudo'>";
    }

    protected function fecharContainer(): string
    {
        return "</main></div>";
    }

    // ─── Dispatcher de blocos ─────────────────────────────────────────────────

    protected function renderBloco(array $bloco, array $contexto): string
    {
        switch ($bloco['tipo'] ?? '') {
            case 'titulo':  return $this->renderTitulo($bloco, $contexto);
            case 'texto':   return $this->renderTexto($bloco, $contexto);
            case 'dados':   return $this->renderDados($bloco, $contexto);
            case 'lista':   return $this->renderLista($bloco, $contexto);
            case 'tabela':  return $this->renderTabela($bloco, $contexto);
            case 'html':    return $this->substituirVariaveis($bloco['conteudo'] ?? '', $contexto);
            default:        return '';
        }
    }

    // ─── Tipos de bloco ───────────────────────────────────────────────────────

    protected function renderTitulo(array $bloco, array $contexto): string
    {
        $nivel    = $bloco['nivel'] ?? 2;
        $tag      = "h{$nivel}";
        $conteudo = $this->substituirVariaveis($bloco['conteudo'], $contexto);
        $classes  = ['sgt-titulo'];
        if ($nivel === 1) $classes[] = 'sgt-titulo-principal';
        $classe = implode(' ', $classes);

        return "<{$tag} class='{$classe}'>{$conteudo}</{$tag}>";
    }

    protected function renderTexto(array $bloco, array $contexto): string
    {
        $conteudo = $this->substituirVariaveis($bloco['conteudo'], $contexto);
        $estilo   = $bloco['estilo'] ?? 'normal';
        
        $tag = 'p';
        $classes = ['sgt-texto'];
        
        switch ($estilo) {
            case 'titulo1':
                $tag = 'h1';
                $classes[] = 'sgt-titulo-principal';
                break;
            case 'titulo2':
                $tag = 'h2';
                $classes[] = 'sgt-titulo';
                break;
            case 'titulo3':
                $tag = 'h3';
                $classes[] = 'sgt-titulo';
                break;
            case 'header_footer':
                $classes[] = 'sgt-texto-header_footer';
                break;
            case 'destaque':
                $classes[] = 'sgt-texto-destaque';
                break;
            case 'valor':
                $classes[] = 'sgt-texto-valor';
                break;
            case 'subtitulo':
                $classes[] = 'sgt-subtitulo';
                break;
            default:
                $classes[] = "sgt-texto-{$estilo}";
        }

        $classList = implode(' ', $classes);
        return "<{$tag} class='{$classList}'>{$conteudo}</{$tag}>";
    }

    protected function renderDados(array $bloco, array $contexto): string
    {
        $html = "<div class='sgt-dados'>";
        foreach ($bloco['campos'] as $label => $valor) {
            $valorResolvido = $this->substituirVariaveis($valor, $contexto);
            $html .= "<div class='sgt-linha'>
                        <span class='sgt-label'>" . ucfirst($label) . ":</span>
                        <span class='sgt-valor'>{$valorResolvido}</span>
                      </div>";
        }
        $html .= "</div>";
        return $html;
    }

    protected function renderLista(array $bloco, array $contexto): string
    {
        $html = "<ul class='sgt-lista'>";
        foreach ($bloco['itens'] as $item) {
            $itemResolvido = $this->substituirVariaveis($item, $contexto);
            $html .= "<li>{$itemResolvido}</li>";
        }
        $html .= "</ul>";
        return $html;
    }

    protected function renderTabela(array $bloco, array $contexto): string
    {
        $html = "<table class='sgt-tabela'>";
        foreach ($bloco['linhas'] as $i => $linha) {
            $tag  = $i === 0 ? 'th' : 'td';
            $html .= "<tr>";
            foreach ($linha as $celula) {
                // Suporta célula como array ['texto'=>'...'] (novo) ou string (legado)
                $textoRaw = is_array($celula) ? ($celula['texto'] ?? '') : (string)$celula;
                $texto  = $this->substituirVariaveis($textoRaw, $contexto);
                $classe = (stripos($texto, 'TOTAL') !== false) ? ' class="sgt-total"' : '';
                $html  .= "<{$tag}{$classe}>{$texto}</{$tag}>";
            }
            $html .= "</tr>";
        }
        $html .= "</table>";
        return $html;
    }

    // ─── Substituição de variáveis ────────────────────────────────────────────

    protected function substituirVariaveis(string $texto, array $contexto): string
    {
        // 1. Substitui variáveis mágicas ${var} (suporta espaços extras e etiquetas de estilo)
        $texto = preg_replace_callback('/\$\{\s*([^}]+)\s*\}/', function ($matches) use ($contexto) {
            $rawVar = $matches[1];
            $var = trim(strip_tags($rawVar));
            $varBusca = strtolower($var);

            if (isset($contexto[$var]) || isset($contexto[$varBusca])) {
                $valor = (string)($contexto[$var] ?? $contexto[$varBusca]);
                
                // Se for chave de logo, renderiza como imagem
                if (in_array($varBusca, ['logo_empresa', 'logomarca', 'logo', 'logotipo', 'empresa_logo'])) {
                    $url = trim($valor);
                    if (empty($url) || $url === 'NÃO' || $url === '[logo_empresa]') return '';
                    
                    // Garante prefixo do projeto se não for URL absoluta ou caminho absoluto
                    if (!preg_match('/^https?:\/\//', $url) && !str_starts_with($url, '/')) {
                        $prefixo = strpos($_SERVER['REQUEST_URI'] ?? '', 'SistemaWeb') !== false ? '/SistemaWeb/' : '/SistemaSaaS/';
                        $url = $prefixo . $url;
                    }
                    
                    $urlEscaped = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                    return "<img src='{$urlEscaped}' alt='Logo' class='sgt-logo-dinamica' style='max-height: 80px; vertical-align: middle;'>";
                }
                return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
            }
            return "";
        }, $texto);

        return $texto;
    }

    // ─── Contexto e variáveis ─────────────────────────────────────────────────

    protected function prepararContexto(array $dados, $resolvedor, int $usuarioId): array
    {
        $sistema = $resolvedor->resolver($this->variaveis, $usuarioId, $dados);
        return array_merge($dados, $sistema);
    }

    protected function extrairVariaveis(): array
    {
        $vars = [];
        $json = json_encode($this->blocos);
        // Regex robusto para pegar variáveis dentro das chaves, mesmo com espaços
        preg_match_all('/\$\{\s*([^}]+)\s*\}/', $json, $matches);
        foreach ($matches[1] as $m) {
            $vars[] = trim(strip_tags($m));
        }
        return array_unique($vars);
    }

    // ─── Helper builders ─────────────────────────────────────────────────────

    protected function titulo(string $texto, int $nivel = 2): array
    {
        return ['tipo' => 'titulo', 'conteudo' => $texto, 'nivel' => $nivel];
    }

    protected function texto(string $texto, string $estilo = 'normal'): array
    {
        return ['tipo' => 'texto', 'conteudo' => $texto, 'estilo' => $estilo];
    }

    protected function dados(array $campos): array
    {
        return ['tipo' => 'dados', 'campos' => $campos];
    }

    protected function lista(array $itens): array
    {
        return ['tipo' => 'lista', 'itens' => $itens];
    }

    protected function tabela(array $linhas): array
    {
        return ['tipo' => 'tabela', 'linhas' => $linhas];
    }

    protected function html(string $conteudo): array
    {
        return ['tipo' => 'html', 'conteudo' => $conteudo];
    }
}
