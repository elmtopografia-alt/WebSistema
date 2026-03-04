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
            case 'html':    return $bloco['conteudo'] ?? '';
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
                $texto  = $this->substituirVariaveis($celula, $contexto);
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
        return preg_replace_callback('/\$\{(\w+)\}/', function ($matches) use ($contexto) {
            $var = $matches[1];
            if (isset($contexto[$var])) {
                $valor = (string)$contexto[$var];
                // Se for chave de logo, renderiza como imagem
                if (in_array($var, ['logo_empresa', 'logomarca', 'logo', 'logotipo'])) {
                    $url = htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
                    return "<img src='{$url}' alt='Logo' class='sgt-logo-dinamica' style='max-height: 80px; vertical-align: middle;'>";
                }
                return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
            }
            return "[{$var}]";
        }, $texto);
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
        preg_match_all('/\$\{(\w+)\}/', $json, $matches);
        return array_unique($matches[1]);
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
