<?php
namespace SGT\Propostas;

/**
 * CLASSE BASE - A Veia do Sistema v6.0
 * Contém a lógica de renderização premium unificada.
 */
abstract class BaseModeloDOCX {
    
    // Propriedades que cada modelo deve definir
    protected $blocos = [];
    protected $cssCustom = "";
    protected $variaveisDetectadas = [];

    /**
     * Engine de Renderização v6.0 (A Veia)
     */
    public function renderDireto(array $dados): string
    {
        $contexto = $dados;
        $html = "<div class='modelo-docx-container'>";
        $html .= "<style>{$this->cssCustom}</style>";
        
        // Aplica cor de marca personalizada se existir
        if (!empty($dados['cor_personalizada'])) {
            $cor = htmlspecialchars($dados['cor_personalizada']);
            $html .= "<style>
                :root { --brand: {$cor} !important; }
                .modelo-docx h1 { color: var(--brand) !important; border-bottom-color: var(--brand) !important; }
                .modelo-docx h2 { color: var(--brand) !important; border-left-color: var(--brand) !important; }
                .modelo-docx h3 { color: var(--brand) !important; }
                .tabela-proposta th { background: #f8fafc !important; color: var(--brand) !important; }
            </style>";
        }

        $html .= "<div class='modelo-docx'>";
        
        // Prioridade absoluta para blocos do Editor Dinâmico
        if (!empty($dados['blocos_custom']) && is_array($dados['blocos_custom'])) {
            foreach ($dados['blocos_custom'] as $bloco) {
                $html .= $this->renderBloco($bloco, $contexto);
            }
        } else {
            foreach ($this->blocos as $bloco) {
                $html .= $this->renderBloco($bloco, $contexto);
            }
        }
        
        $html .= "</div></div>";
        return $this->saneamentoFinal($html);
    }

    /**
     * Limpeza de dados para evitar "bagunça" v6.0
     */
    protected function saneamentoFinal(string $html): string
    {
        // 1. Unidades duplicadas "ha ha" -> "ha"
        $unidades = ['ha', 'm²', 'km', 'km²', 'm2'];
        foreach ($unidades as $u) {
            $u_esc = preg_quote($u, '/');
            $html = preg_replace('/\b' . $u_esc . '\s+' . $u_esc . '\b/i', $u, $html);
        }

        // 2. Tags vazias e resíduos
        $html = str_replace(['<p> </p>', '<p>&nbsp;</p>'], '', $html);

        return $html;
    }

    /**
     * Renderizador de Blocos Premium
     */
    protected function renderBloco(array $bloco, array $dados): string
    {
        $conteudo = $bloco['conteudo'] ?? '';
        
        // Substituição Recursiva de Variáveis
        foreach ($dados as $k => $v) {
            if (is_scalar($v)) {
                $val = (string)$v;
                $conteudo = str_replace(['${' . $k . '}', '[' . $k . ']'], $val, $conteudo);
            }
        }
        
        // Tabelas Premium
        if (($bloco['tipo'] ?? '') === 'tabela' && is_array($bloco['conteudo'] ?? null)) {
            return $this->renderTabela($bloco['conteudo'], $dados);
        }
        
        $tag = (!empty($bloco['nivel_titulo']) && $bloco['nivel_titulo'] > 0) ? "h" . $bloco['nivel_titulo'] : "p";
        $style = $this->mapEstilos($bloco['estilos_css'] ?? []);
        
        // Visual Premium v6.0
        $baseStyle = "margin-bottom: 1.2rem; line-height: 1.7; color: #334155; ";
        if ($tag === 'p') $baseStyle .= "font-size: 10.5pt; text-align: justify; ";
        
        return "<$tag style='{$baseStyle}{$style}' class='modelo-docx-bloco'>$conteudo</$tag>";
    }

    /**
     * Tabelas Premium v6.0
     */
    protected function renderTabela(array $linhas, array $dados): string
    {
        $html = "<table class='tabela-proposta' style='width:100%; border-collapse:separate; border-spacing: 0; margin:20px 0; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; font-size: 10pt;'>";
        foreach ($linhas as $idx => $linha) {
            $html .= "<tr>";
            foreach ($linha as $celula) {
                $txt = $celula['texto'] ?? '';
                foreach ($dados as $k => $v) {
                   if (is_scalar($v)) $txt = str_replace(['${'.$k.'}', '['.$k.']'], (string)$v, $txt);
                }
                $tag = ($idx === 0) ? 'th' : 'td';
                $colspan = $celula['colspan'] ?? 1;
                
                $style = "padding: 12px 15px; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; ";
                if ($tag === 'th') {
                    $style .= "background: #f8fafc; color: var(--brand, #334155); font-weight: 700; text-transform: uppercase; font-size: 8.5pt; letter-spacing: 0.5px; ";
                }
                
                $html .= "<$tag colspan='$colspan' style='$style'>" . nl2br($txt) . "</$tag>";
            }
            $html .= "</tr>";
        }
        $html .= "</table>";
        return $html;
    }

    protected function mapEstilos($estilos) {
        $out = "";
        if (is_array($estilos)) {
            foreach ($estilos as $k => $v) $out .= "$k:$v; ";
        }
        return $out;
    }

    /**
     * Retorna a configuração do modelo para o Editor Dinâmico
     */
    public function getConfig(): array {
        return [
            'nome' => defined('static::NOME') ? static::NOME : 'Modelo Base',
            'blocos' => $this->blocos,
            'variaveis' => $this->variaveisDetectadas,
            'css' => $this->cssCustom
        ];
    }

    // Método legacy para compatibilidade
    public function render($dadosManuais, $resolvedor, $id_usuario) {
        return $this->renderDireto($dadosManuais);
    }
}
