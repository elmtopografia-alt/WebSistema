<?php
/**
 * funcoes_gerador_docs.php
 * Funções compartilhadas para o gerador de DOCS -> PHP
 */

/**
 * 🔥 Processa o HTML exportado do Google Docs
 * Converte para a estrutura de blocos do SGT
 */
function processarHtmlGoogleDocs($html) {
    // Limpa o HTML e prepara o DOM
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    $blocos = [];
    
    // 1. Extrai mapeamento de estilos (opcional, para negrito/itálico)
    $cssStyles = [];
    $styleTags = $dom->getElementsByTagName('style');
    foreach ($styleTags as $tag) {
        $css = $tag->textContent;
        if (preg_match_all('/\.([a-z0-9_-]+)\{(.*?)\}/si', $css, $m)) {
            foreach ($m[1] as $i => $cls) {
                $rules = $m[2][$i];
                if (strpos($rules, 'font-weight:700') !== false || strpos($rules, 'font-weight:bold') !== false) {
                    $cssStyles[$cls]['font-weight'] = 'bold';
                }
            }
        }
    }

    // 2. Localiza todos os elementos relevantes no body
    $elements = $xpath->query('//body//*[self::p or self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6 or self::table]');
    
    foreach ($elements as $el) {
        $tipo = $el->nodeName;
        $texto = trim($el->textContent);
        
        // Evita blocos vazios (exceto tabelas)
        if (empty($texto) && $tipo !== 'table') continue;
        
        // Ignora elementos que estão dentro de outros já processados (ex: p dentro de td)
        $parent = $el->parentNode;
        $isInsideTable = false;
        while($parent) {
            if ($parent->nodeName === 'table') { $isInsideTable = true; break; }
            $parent = $parent->parentNode;
        }
        if ($isInsideTable && $tipo !== 'table') continue;

        if (strpos($tipo, 'h') === 0) {
            $nivel = (int)substr($tipo, 1);
            $blocos[] = [
                'tipo' => 'texto',
                'subtipo' => 'titulo',
                'conteudo' => $texto,
                'nivel_titulo' => $nivel,
                'estilos_css' => ['font-weight' => 'bold']
            ];
        } elseif ($tipo === 'p') {
            // Verifica se o parágrafo ou seus spans têm negrito
            $isBold = false;
            $class = $el->getAttribute('class');
            if (isset($cssStyles[$class]['font-weight'])) $isBold = true;
            
            // Verifica spans internos
            foreach ($el->getElementsByTagName('span') as $span) {
                $sClass = $span->getAttribute('class');
                if (isset($cssStyles[$sClass]['font-weight'])) $isBold = true;
            }

            $blocos[] = [
                'tipo' => 'texto',
                'subtipo' => 'texto_geral',
                'conteudo' => $texto,
                'nivel_titulo' => 0,
                'estilos_css' => $isBold ? ['font-weight' => 'bold'] : []
            ];
        } elseif ($tipo === 'table') {
            $linhas = [];
            foreach ($el->getElementsByTagName('tr') as $tr) {
                $linha = [];
                foreach ($tr->getElementsByTagName('td') as $td) {
                    $linha[] = [
                        'texto' => trim($td->textContent),
                        'estilos' => [] // Pode expandir futuramente
                    ];
                }
                $linhas[] = $linha;
            }
            $blocos[] = [
                'tipo' => 'tabela',
                'linhas' => $linhas
            ];
        }
    }
    
    // 3. Pós-processamento de variáveis e metadados
    $variaveisGlobais = [];
    foreach ($blocos as &$bloco) {
        $c = isset($bloco['conteudo']) ? $bloco['conteudo'] : '';
        if (isset($bloco['linhas'])) {
            foreach($bloco['linhas'] as $row) {
                foreach($row as $cell) $c .= ' ' . $cell['texto'];
            }
        }
        
        $vars = detectarVariaveis($c);
        $bloco['variaveis'] = $vars;
        $variaveisGlobais = array_merge($variaveisGlobais, $vars);
    }

    return [
        'sucesso' => true,
        'blocos' => $blocos,
        'variaveis' => array_values(array_unique($variaveisGlobais)),
        'total_blocos' => count($blocos)
    ];
}

/**
 * Detecta variáveis no texto
 */
function detectarVariaveis($texto) {
    preg_match_all('/\{\{(\s*[\w\.\-]+\s*)\}\}/', $texto, $m1);
    preg_match_all('/\$\{(\s*[\w\.\-]+\s*)\}/', $texto, $m2);
    
    $vars = [];
    if (!empty($m1[1])) foreach($m1[1] as $v) $vars[] = trim($v);
    if (!empty($m2[1])) foreach($m2[1] as $v) $vars[] = trim($v);
    
    return array_values(array_unique($vars));
}

/**
 * Remove vírgulas trailing de arrays PHP exportados (compatibilidade PHP < 7.3)
 */
function removeTrailingComma($str) {
    return preg_replace('/,([\s\)]*\))/', '$1', $str);
}

/**
 * 🔥 Converte sintaxe ${variavel} para {{variavel}} no array de dados
 */
function converterSintaxeParaModerna(array $dados): array {
    if (isset($dados['blocos']) && is_array($dados['blocos'])) {
        foreach ($dados['blocos'] as &$bloco) {
            if (isset($bloco['conteudo'])) {
                $bloco['conteudo'] = converterVariaveisLegacy($bloco['conteudo']);
            }
            if (isset($bloco['conteudo_html'])) {
                $bloco['conteudo_html'] = converterVariaveisLegacy($bloco['conteudo_html']);
            }
            if (isset($bloco['linhas']) && is_array($bloco['linhas'])) {
                foreach ($bloco['linhas'] as &$linha) {
                    foreach ($linha as &$celula) {
                        if (is_array($celula) && isset($celula['texto'])) {
                            $celula['texto'] = converterVariaveisLegacy($celula['texto']);
                        } elseif (is_string($celula)) {
                            $celula = converterVariaveisLegacy($celula);
                        }
                    }
                }
            }
        }
    }
    if (isset($dados['variaveis']) && is_array($dados['variaveis'])) {
        $dados['variaveis_modernas'] = array_map(function($v) {
            return '{{' . $v . '}}';
        }, $dados['variaveis']);
    }
    return $dados;
}

/**
 * 🔥 Converte ${variavel} → {{variavel}}
 */
function converterVariaveisLegacy(string $texto): string {
    if (strpos($texto, '{{') !== false && strpos($texto, '${') === false) {
        return $texto; 
    }
    return preg_replace('/(?<!\\\\)\$\{(\w+)\}/', '{{$1}}', $texto);
}

/**
 * Converte blocos do formato DOCS Parser (anterior docx) → formato ModeloBase v3.2
 */
function converterBlocosParaModeloBase(array $blocos): array {
    $resultado = array();
    foreach ($blocos as $bloco) {
        if ($bloco['tipo'] === 'texto') {
            $nivel = intval($bloco['nivel_titulo'] ?? 0);
            $conteudo = converterVariaveisLegacy($bloco['conteudo'] ?? '');
            $conteudoHtml = isset($bloco['conteudo_html']) ? converterVariaveisLegacy($bloco['conteudo_html']) : null;

            if ($nivel > 0) {
                $resultado[] = array('tipo' => 'titulo', 'conteudo' => $conteudo, 'nivel' => $nivel);
            } else {
                if ($conteudoHtml !== null) {
                    $resultado[] = array('tipo' => 'texto', 'conteudo' => $conteudoHtml, 'estilo' => 'normal');
                } else {
                    $negritoTotal = (!empty($bloco['estilos_css']['font-weight']) && $bloco['estilos_css']['font-weight'] === 'bold');
                    $estilo = $negritoTotal ? 'destaque' : 'normal';
                    if (isset($bloco['subtipo']) && $bloco['subtipo'] === 'header_footer') {
                        $estilo = 'header_footer';
                    }
                    $resultado[] = array('tipo' => 'texto', 'conteudo' => $conteudo, 'estilo' => $estilo);
                }
            }
        } elseif ($bloco['tipo'] === 'tabela') {
            $linhas = array();
            foreach ($bloco['linhas'] as $linha) {
                $linhaSimples = array();
                foreach ($linha as $celula) {
                    $texto = isset($celula['texto']) ? $celula['texto'] : (is_string($celula) ? $celula : '');
                    $texto = converterVariaveisLegacy($texto);
                    $linhaSimples[] = $texto;
                }
                $linhas[] = $linhaSimples;
            }
            $resultado[] = array('tipo' => 'tabela', 'linhas' => $linhas);
        }
    }
    return $resultado;
}

function gerarCodigoPHP($dados, $nomeClasseSuffix) {
    $nomeClasse = "Modelo" . $nomeClasseSuffix;
    $data = date('d/m/Y H:i');
    $blocosConvertidos = converterBlocosParaModeloBase($dados['blocos']);
    $blocosStr = removeTrailingComma(var_export($blocosConvertidos, true));

    $template = <<<'CODE'
<?php
/**
 * MODELO GERADO (DOCS) - SGT Template Engine v3.2
 * Fonte: {{NOME_ARQUIVO}} | Gerado em: {{DATA}}
 */

require_once __DIR__ . '/../core/ModeloBase.php';

class {{NOME_CLASSE}} extends ModeloBase
{
    public function getNome(): string
    {
        return '{{NOME_SUFFIX}}';
    }

    protected function definirBlocos(): array
    {
        return {{BLOCOS}};
    }
}
CODE;

    return str_replace(
        array('{{NOME_ARQUIVO}}', '{{DATA}}', '{{NOME_CLASSE}}', '{{NOME_SUFFIX}}', '{{BLOCOS}}'),
        array($dados['nome_arquivo'], $data, $nomeClasse, $nomeClasseSuffix, $blocosStr),
        $template
    );
}

function limparBackupsAntigos($diretorio, $prefixo, $manter = 3) {
    $backups = glob($diretorio . $prefixo . '*');
    if (count($backups) > $manter) {
        usort($backups, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        $paraRemover = array_slice($backups, 0, count($backups) - $manter);
        foreach ($paraRemover as $file) {
            @unlink($file);
        }
    }
}
