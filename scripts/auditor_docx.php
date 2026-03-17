<?php
/**
 * AUDITOR DE MODELOS DOCX - SGT PROPOSTAS
 * Analisa diretamente arquivos .docx para mapear estrutura real
 * 
 * Uso: php auditor_docx.php [diretorio_docx] [diretorio_saida]
 * Ex:  php auditor_docx.php /var/www/modelos_docx /var/www/auditoria
 */

class AuditorDocx {
    private $resultados = [];
    private $diretorioDocx;
    private $diretorioSaida;
    private $log = [];
    
    public function __construct($diretorioDocx, $diretorioSaida = './') {
        $this->diretorioDocx = rtrim($diretorioDocx, '/');
        $this->diretorioSaida = rtrim($diretorioSaida, '/');
        
        if (!is_dir($this->diretorioSaida)) {
            mkdir($this->diretorioSaida, 0755, true);
        }
    }
    
    public function executar() {
        $arquivos = glob($this->diretorioDocx . '/*.docx');
        
        $this->log("========================================");
        $this->log("AUDITOR DE DOCX - SGT PROPOSTAS");
        $this->log("Iniciado: " . date('d/m/Y H:i:s'));
        $this->log("Diretório: {$this->diretorioDocx}");
        $this->log("Arquivos encontrados: " . count($arquivos));
        $this->log("========================================\n");
        
        if (empty($arquivos)) {
            $this->log("ERRO: Nenhum arquivo .docx encontrado!");
            return;
        }
        
        foreach ($arquivos as $index => $arquivo) {
            $this->analisarDocx($arquivo, $index + 1, count($arquivos));
        }
        
        $this->gerarRelatorios();
        $this->exibirResumo();
    }
    
    private function analisarDocx($caminho, $atual, $total) {
        $nome = basename($caminho, '.docx');
        $this->log("[{$atual}/{$total}] Analisando: {$nome}...");
        
        $inicio = microtime(true);
        
        try {
            if (!file_exists($caminho)) {
                throw new Exception("Arquivo não acessível");
            }
            
            $tamanho = filesize($caminho);
            if ($tamanho < 1000) {
                throw new Exception("Arquivo muito pequeno (possivelmente corrompido)");
            }
            
            $zip = new ZipArchive();
            $aberto = $zip->open($caminho);
            
            if ($aberto !== true) {
                throw new Exception("Não é um ZIP válido (código: {$aberto})");
            }
            
            // Extrair document.xml
            $xmlConteudo = $zip->getFromName('word/document.xml');
            $zip->close();
            
            if (!$xmlConteudo) {
                throw new Exception("document.xml não encontrado dentro do DOCX");
            }
            
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadXML($xmlConteudo);
            libxml_clear_errors();
            
            $analise = [
                'nome_arquivo' => $nome,
                'tamanho_bytes' => $tamanho,
                'tamanho_kb' => round($tamanho / 1024, 2),
                'titulo_detectado' => $this->extrairTitulo($dom),
                'campos_placeholders' => $this->extrairPlaceholders($dom),
                'secoes_detectadas' => $this->detectarSecoes($dom),
                'tabelas' => $this->contarTabelas($dom),
                'imagens' => $this->contarImagens($dom),
                'paragrafos' => $this->contarParagrafos($dom),
                'estimativa_paginas' => $this->estimarPaginas($dom),
                'categoria_sugerida' => $this->sugerirCategoria($nome, $dom),
                'texto_amostra' => $this->extrairAmostraTexto($dom),
                'tempo_analise_ms' => round((microtime(true) - $inicio) * 1000, 2),
                'status' => 'sucesso'
            ];
            
            $this->resultados[] = $analise;
            $this->log("  ✓ {$analise['estimativa_paginas']} pgs | " . 
                      count($analise['campos_placeholders']) . " campos | " .
                      count($analise['secoes_detectadas']) . " seções | " .
                      "[{$analise['categoria_sugerida']}]");
            
        } catch (Exception $e) {
            $this->resultados[] = [
                'nome_arquivo' => $nome,
                'tamanho_bytes' => $tamanho ?? 0,
                'erro' => $e->getMessage(),
                'status' => 'erro'
            ];
            $this->log("  ✗ ERRO: " . $e->getMessage());
        }
    }
    
    private function extrairPlaceholders($dom) {
        $placeholders = [];
        $textoCompleto = '';
        
        // Extrai todo texto
        $nodes = $dom->getElementsByTagName('t');
        foreach ($nodes as $node) {
            $textoCompleto .= $node->nodeValue . ' ';
        }
        
        // Padrões de placeholders em propostas
        $padroes = [
            'double_curly' => '/\{\{\s*([^{}|]+?)(?:\|[^}]+)?\s*\}\}/',  // {{campo}} ou {{campo|formato}}
            'single_curly' => '/\{([^{}]+)\}/',                           // {campo}
            'bracket_upper' => '/\[([A-Z][A-Z_0-9]+)\]/',                // [CAMPO]
            'bracket_mixed' => '/\[([A-Za-z][A-Za-z0-9_]*)\]/',          // [Campo] ou [campo]
            'dollar_var' => '/\$([a-z_][a-z0-9_]*)/i',                    // $campo
            'hash_wrap' => '/#([A-Z_]+)#/',                               // #CAMPO#
            'percent_wrap' => '/%([a-z_]+)%/i',                           // %campo%
            'underscore' => '/_([A-Z_]+)_/',                              // _CAMPO_
        ];
        
        foreach ($padroes as $tipo => $regex) {
            if (preg_match_all($regex, $textoCompleto, $matches)) {
                foreach ($matches[1] as $match) {
                    $campo = strtolower(trim($match));
                    $campo = preg_replace('/\s+/', '_', $campo);
                    if (strlen($campo) > 2 && strlen($campo) < 50) {
                        $placeholders[$campo] = [
                            'nome' => $campo,
                            'tipo_detectado' => $this->inferirTipoCampo($campo),
                            'exemplo_original' => $match
                        ];
                    }
                }
            }
        }
        
        // Detecta labels de formulário (Nome:, Cliente:, etc)
        $labels = [
            'cliente', 'empresa', 'contratante', 'nome',
            'cnpj', 'cpf', 'documento', 'ie', 'im',
            'endereco', 'endereço', 'rua', 'bairro', 'cidade', 'estado', 'cep',
            'email', 'e-mail', 'mail', 
            'telefone', 'tel', 'celular', 'cel', 'whatsapp', 'fone',
            'responsavel', 'responsável', 'contato',
            'data', 'data_proposta', 'data_emissao', 'validade',
            'valor', 'valor_total', 'preco', 'investimento', 'custo', 'total',
            'prazo', 'prazo_execucao', 'prazo_entrega',
            'descricao', 'descrição', 'escopo', 'objeto', 'servico', 'serviço',
            'metodologia', 'procedimento', 'execucao', 'execução',
            'equipamento', 'equipamentos', 'material', 'materiais',
            'garantia', 'validade_proposta', 'condicoes', 'condições',
            'pagamento', 'forma_pagamento', 'parcelas'
        ];
        
        foreach ($labels as $label) {
            $regex = '/\b' . preg_quote($label, '/') . '\s*[:=]\s*([^\n]+)/i';
            if (preg_match_all($regex, $textoCompleto, $matches)) {
                $campo = strtolower(preg_replace('/\s+/', '_', $label));
                if (!isset($placeholders[$campo])) {
                    $placeholders[$campo] = [
                        'nome' => $campo,
                        'tipo_detectado' => $this->inferirTipoCampo($campo),
                        'exemplo_original' => $label . ':',
                        'detectado_por' => 'label'
                    ];
                }
            }
        }
        
        return array_values($placeholders);
    }
    
    private function inferirTipoCampo($nomeCampo) {
        $nome = strtolower($nomeCampo);
        
        if (strpos($nome, 'data') !== false) return 'data';
        if (strpos($nome, 'valor') !== false || strpos($nome, 'preco') !== false || strpos($nome, 'custo') !== false) return 'moeda';
        if (strpos($nome, 'email') !== false || strpos($nome, 'e-mail') !== false || strpos($nome, 'mail') !== false) return 'email';
        if (strpos($nome, 'cnpj') !== false) return 'cnpj';
        if (strpos($nome, 'cpf') !== false) return 'cpf';
        if (strpos($nome, 'telefone') !== false || strpos($nome, 'tel') !== false || strpos($nome, 'cel') !== false || strpos($nome, 'fone') !== false) return 'telefone';
        if (strpos($nome, 'cep') !== false) return 'cep';
        if (strpos($nome, 'numero') !== false || strpos($nome, 'número') !== false || strpos($nome, 'prazo') !== false) return 'numero';
        if (strpos($nome, 'descricao') !== false || strpos($nome, 'descrição') !== false || strpos($nome, 'escopo') !== false || strpos($nome, 'metodologia') !== false) return 'texto_longo';
        if (strpos($nome, 'logo') !== false || strpos($nome, 'imagem') !== false || strpos($nome, 'foto') !== false) return 'imagem';
        
        return 'texto';
    }
    
    private function detectarSecoes($dom) {
        $secoes = [];
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        
        // Busca por estilos de título
        $paragrafos = $xpath->query('//w:p');
        $contador = 0;
        
        foreach ($paragrafos as $p) {
            $contador++;
            if ($contador > 200) break; // Limita para performance
            
            // Verifica se é título por estilo
            $styleNode = $xpath->query('w:pPr/w:pStyle/@w:val', $p)->item(0);
            $texto = $this->extrairTextoNode($p);
            $textoLimpo = trim($texto);
            
            if (empty($textoLimpo) || strlen($textoLimpo) < 3) continue;
            
            $nivel = null;
            
            if ($styleNode) {
                $style = strtolower($styleNode->nodeValue);
                if (preg_match('/heading(\d)/i', $style, $m)) {
                    $nivel = (int)$m[1];
                } elseif (strpos($style, 'titulo') !== false || strpos($style, 'título') !== false) {
                    $nivel = 1;
                } elseif (strpos($style, 'subtitle') !== false) {
                    $nivel = 2;
                }
            }
            
            // Se não achou por estilo, tenta por padrão de texto
            if (!$nivel) {
                // Maiúsculas = possível título
                if (strtoupper($textoLimpo) === $textoLimpo && strlen($textoLimpo) > 5 && strlen($textoLimpo) < 60) {
                    $nivel = 2;
                }
                // Número + texto = possível seção
                elseif (preg_match('/^\d+[\.\-)\s]+[A-Z]/', $textoLimpo)) {
                    $nivel = 2;
                }
            }
            
            if ($nivel && $nivel <= 3) {
                $slug = $this->slugify($textoLimpo);
                $secoes[] = [
                    'nivel' => $nivel,
                    'titulo_original' => $textoLimpo,
                    'slug' => $slug,
                    'posicao' => $contador
                ];
            }
        }
        
        return $secoes;
    }
    
    private function extrairTitulo($dom) {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        
        // Primeiro parágrafo centralizado ou em negrito
        $candidatos = $xpath->query('//w:p[w:pPr/w:jc/@w:val="center" or w:pPr/w:jc/@w:val="both"]');
        
        foreach ($candidatos as $p) {
            $texto = $this->extrairTextoNode($p);
            if (strlen($texto) > 5 && strlen($texto) < 100) {
                return trim($texto);
            }
        }
        
        // Ou primeiro parágrafo com texto
        $todos = $xpath->query('//w:p');
        foreach ($todos as $p) {
            $texto = $this->extrairTextoNode($p);
            if (strlen(trim($texto)) > 10) {
                return substr(trim($texto), 0, 80);
            }
        }
        
        return 'Título não detectado';
    }
    
    private function extrairAmostraTexto($dom, $maxCaracteres = 200) {
        $texto = '';
        $nodes = $dom->getElementsByTagName('t');
        foreach ($nodes as $node) {
            $texto .= $node->nodeValue . ' ';
            if (strlen($texto) > $maxCaracteres) break;
        }
        return substr(trim($texto), 0, $maxCaracteres) . '...';
    }
    
    private function contarTabelas($dom) {
        return $dom->getElementsByTagName('tbl')->length;
    }
    
    private function contarImagens($dom) {
        return $dom->getElementsByTagName('drawing')->length;
    }
    
    private function contarParagrafos($dom) {
        return $dom->getElementsByTagName('p')->length;
    }
    
    private function estimarPaginas($dom) {
        $paragrafos = $this->contarParagrafos($dom);
        $tabelas = $this->contarTabelas($dom);
        $imagens = $this->contarImagens($dom);
        
        // Fórmula estimada: parágrafos/40 + tabelas*0.5 + imagens*0.3
        $estimativa = ($paragrafos / 40) + ($tabelas * 0.5) + ($imagens * 0.3);
        return max(1, round($estimativa));
    }
    
    private function sugerirCategoria($nomeArquivo, $dom) {
        $texto = strtolower($nomeArquivo) . ' ';
        $texto .= strtolower($this->extrairAmostraTexto($dom, 500));
        
        $categorias = [
            'drone' => 'drone|aéreo|aereo|rtk|mapeamento|topografia|georreferenciamento|phantom|mavic|uav|vants|aerofotogrametria|orthofoto',
            'ti' => 'software|sistema|desenvolvimento|programação|programacao|aplicativo|app|web|mobile|ti|tecnologia|sistemas|automação|automacao|digital|webdesign|programas',
            'consultoria' => 'consultoria|assessoria|auditoria|análise|analise|diagnóstico|diagnostico|estratégia|estrategia|gestão|gestao|processos|melhoria',
            'educacao' => 'treinamento|capacitação|capacitacao|curso|workshop|palestra|aula|ensino|educação|educacao|formação|formacao',
            'construcao' => 'obra|construção|construcao|civil|engenharia|projeto arquitetônico|arquitetonico|reforma|retrofit|estrutura|fundação|fundacao',
            'marketing' => 'marketing|digital|mídia|midia|social|campanha|branding|publicidade|propaganda|comunicação|comunicacao|design|marca',
            'servicos_gerais' => 'limpeza|conservação|conservacao|higienização|higienizacao|manutenção|manutencao|jardinagem|porteiro|recepção|recepcao',
            'seguranca' => 'segurança|seguranca|vigilância|vigilancia|portaria|cftv|câmera|camera|ronda|patrimonial',
            'saude' => 'saúde|saude|médico|medico|enfermagem|clínica|clinica|exame|consulta|terapia',
            'eventos' => 'evento|festa|casamento|formatura|corporativo|congresso|seminário|seminario|show'
        ];
        
        $scores = [];
        foreach ($categorias as $cat => $regex) {
            if (preg_match("/({$regex})/", $texto)) {
                preg_match_all("/({$regex})/", $texto, $matches);
                $scores[$cat] = count($matches[0]);
            }
        }
        
        if (!empty($scores)) {
            arsort($scores);
            return array_key_first($scores);
        }
        
        return 'geral';
    }
    
    private function extrairTextoNode($node) {
        $texto = '';
        foreach ($node->getElementsByTagName('t') as $t) {
            $texto .= $t->nodeValue;
        }
        return $texto;
    }
    
    private function slugify($texto) {
        $slug = strtolower(trim($texto));
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
        $slug = preg_replace('/_+/', '_', $slug);
        return trim($slug, '_');
    }
    
    private function gerarRelatorios() {
        $timestamp = date('Ymd_His');
        
        // 1. Relatório completo JSON
        $relatorioCompleto = [
            'metadata' => [
                'gerado_em' => date('c'),
                'total_arquivos' => count($this->resultados),
                'diretorio_origem' => $this->diretorioDocx,
                'versao_auditor' => '1.0'
            ],
            'modelos' => $this->resultados,
            'analise_agrupada' => $this->gerarAnaliseAgrupada()
        ];
        
        $arquivoJson = "{$this->diretorioSaida}/auditoria_docx_{$timestamp}.json";
        file_put_contents($arquivoJson, json_encode($relatorioCompleto, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // 2. Relatório resumido HTML
        $arquivoHtml = "{$this->diretorioSaida}/auditoria_docx_{$timestamp}.html";
        file_put_contents($arquivoHtml, $this->gerarHtml($relatorioCompleto));
        
        // 3. Log de execução
        $arquivoLog = "{$this->diretorioSaida}/auditoria_docx_{$timestamp}.log";
        file_put_contents($arquivoLog, implode("\n", $this->log));
        
        $this->log("\n========================================");
        $this->log("RELATÓRIOS GERADOS:");
        $this->log("  JSON: {$arquivoJson}");
        $this->log("  HTML: {$arquivoHtml}");
        $this->log("  LOG:  {$arquivoLog}");
        $this->log("========================================");
    }
    
    private function gerarAnaliseAgrupada() {
        $analise = [
            'por_categoria' => [],
            'por_status' => ['sucesso' => 0, 'erro' => 0],
            'campos_frequencia_global' => [],
            'campos_por_categoria' => [],
            'secoes_frequencia' => [],
            'estatisticas' => [
                'total_paginas_estimadas' => 0,
                'total_tabelas' => 0,
                'total_imagens' => 0,
                'tamanho_medio_kb' => 0
            ]
        ];
        
        $tamanhos = [];
        
        foreach ($this->resultados as $r) {
            // Status
            $analise['por_status'][$r['status']]++;
            
            if ($r['status'] !== 'sucesso') continue;
            
            // Categoria
            $cat = $r['categoria_sugerida'];
            if (!isset($analise['por_categoria'][$cat])) {
                $analise['por_categoria'][$cat] = [];
            }
            $analise['por_categoria'][$cat][] = $r['nome_arquivo'];
            
            // Campos globais
            foreach ($r['campos_placeholders'] as $campo) {
                $nome = $campo['nome'];
                $analise['campos_frequencia_global'][$nome] = ($analise['campos_frequencia_global'][$nome] ?? 0) + 1;
                
                // Por categoria
                if (!isset($analise['campos_por_categoria'][$cat][$nome])) {
                    $analise['campos_por_categoria'][$cat][$nome] = 0;
                }
                $analise['campos_por_categoria'][$cat][$nome]++;
            }
            
            // Seções
            foreach ($r['secoes_detectadas'] as $secao) {
                $slug = $secao['slug'];
                if (!isset($analise['secoes_frequencia'][$slug])) {
                    $analise['secoes_frequencia'][$slug] = [
                        'titulo' => $secao['titulo_original'],
                        'ocorrencias' => 0,
                        'niveis' => []
                    ];
                }
                $analise['secoes_frequencia'][$slug]['ocorrencias']++;
                $analise['secoes_frequencia'][$slug]['niveis'][] = $secao['nivel'];
            }
            
            // Estatísticas
            $analise['estatisticas']['total_paginas_estimadas'] += $r['estimativa_paginas'];
            $analise['estatisticas']['total_tabelas'] += $r['tabelas'];
            $analise['estatisticas']['total_imagens'] += $r['imagens'];
            $tamanhos[] = $r['tamanho_kb'];
        }
        
        // Ordena por frequência
        arsort($analise['campos_frequencia_global']);
        uasort($analise['secoes_frequencia'], function($a, $b) {
            return $b['ocorrencias'] <=> $a['ocorrencias'];
        });
        
        // Média de tamanho
        if (!empty($tamanhos)) {
            $analise['estatisticas']['tamanho_medio_kb'] = round(array_sum($tamanhos) / count($tamanhos), 2);
        }
        
        return $analise;
    }
    
    private function gerarHtml($dados) {
        $analise = $dados['analise_agrupada'];
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Auditoria de Modelos DOCX - SGT Propostas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-box { background: #ecf0f1; padding: 15px; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; color: #2980b9; }
        .stat-label { color: #7f8c8d; font-size: 0.9em; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #34495e; color: white; }
        tr:hover { background: #f5f5f5; }
        .tag { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; margin: 2px; }
        .tag-cat { background: #3498db; color: white; }
        .tag-campo { background: #2ecc71; color: white; }
        .tag-secao { background: #9b59b6; color: white; }
        .erro { color: #e74c3c; background: #fadbd8; padding: 10px; border-radius: 4px; }
        .sucesso { color: #27ae60; }
        .progress-bar { background: #ecf0f1; border-radius: 10px; overflow: hidden; height: 20px; }
        .progress-fill { background: #3498db; height: 100%; transition: width 0.3s; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Auditoria de Modelos DOCX</h1>
        <p><strong>Gerado em:</strong> ' . $dados['metadata']['gerado_em'] . '</p>
        <p><strong>Diretório:</strong> ' . $dados['metadata']['diretorio_origem'] . '</p>
        
        <div class="stats">
            <div class="stat-box">
                <div class="stat-number">' . $dados['metadata']['total_arquivos'] . '</div>
                <div class="stat-label">Total de Modelos</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">' . count($analise['por_categoria']) . '</div>
                <div class="stat-label">Categorias Detectadas</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">' . count($analise['campos_frequencia_global']) . '</div>
                <div class="stat-label">Campos Diferentes</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">' . $analise['por_status']['sucesso'] . '</div>
                <div class="stat-label">Análises Bem-sucedidas</div>
            </div>
        </div>';
        
        // Categorias
        $html .= '<h2>📁 Distribuição por Categoria</h2><table>
            <tr><th>Categoria</th><th>Quantidade</th><th>Modelos</th></tr>';
        foreach ($analise['por_categoria'] as $cat => $modelos) {
            $html .= "<tr><td><span class='tag tag-cat'>{$cat}</span></td>
                <td>" . count($modelos) . "</td>
                <td>" . implode(', ', array_slice($modelos, 0, 3)) . (count($modelos) > 3 ? '...' : '') . "</td></tr>";
        }
        $html .= '</table>';
        
        // Top campos
        $html .= '<h2>🔑 Campos Mais Frequentes (Globais)</h2><table>
            <tr><th>Campo</th><th>Ocorrências</th><th>%</th><th>Tipo Sugerido</th></tr>';
        $total = $dados['metadata']['total_arquivos'];
        foreach (array_slice($analise['campos_frequencia_global'], 0, 20, true) as $campo => $qtd) {
            $percent = round(($qtd / $total) * 100);
            $tipo = $this->inferirTipoCampo($campo);
            $html .= "<tr>
                <td><span class='tag tag-campo'>{$campo}</span></td>
                <td>{$qtd}</td>
                <td><div class='progress-bar'><div class='progress-fill' style='width: {$percent}%'></div></div> {$percent}%</td>
                <td>{$tipo}</td>
            </tr>";
        }
        $html .= '</table>';
        
        // Campos por categoria
        $html .= '<h2>🏷️ Campos Específicos por Categoria</h2>';
        foreach ($analise['campos_por_categoria'] as $cat => $campos) {
            arsort($campos);
            $html .= "<h3>{$cat}</h3><div style='margin: 10px 0;'>";
            foreach (array_slice($campos, 0, 10, true) as $campo => $qtd) {
                $html .= "<span class='tag tag-campo'>{$campo} ({$qtd})</span> ";
            }
            $html .= '</div>';
        }
        
        // Seções
        $html .= '<h2>📑 Seções Mais Comuns</h2><table>
            <tr><th>Seção</th><th>Ocorrências</th><th>Níveis</th></tr>';
        foreach (array_slice($analise['secoes_frequencia'], 0, 15, true) as $slug => $info) {
            $niveis = array_count_values($info['niveis']);
            $nivelStr = [];
            foreach ($niveis as $n => $q) $nivelStr[] = "H{$n}:{$q}";
            $html .= "<tr>
                <td><span class='tag tag-secao'>{$info['titulo']}</span></td>
                <td>{$info['ocorrencias']}</td>
                <td>" . implode(', ', $nivelStr) . "</td>
            </tr>";
        }
        $html .= '</table>';
        
        // Detalhes por modelo
        $html .= '<h2>📄 Detalhes por Modelo</h2><table>
            <tr><th>Arquivo</th><th>Categoria</th><th>Págs</th><th>Campos</th><th>Seções</th><th>Status</th></tr>';
        foreach ($dados['modelos'] as $m) {
            $status = $m['status'] === 'sucesso' 
                ? '<span class="sucesso">✓ OK</span>' 
                : '<span class="erro">✗ ' . $m['erro'] . '</span>';
            $campos = $m['status'] === 'sucesso' ? count($m['campos_placeholders']) : '-';
            $secoes = $m['status'] === 'sucesso' ? count($m['secoes_detectadas']) : '-';
            $pags = $m['status'] === 'sucesso' ? $m['estimativa_paginas'] : '-';
            $cat = $m['status'] === 'sucesso' ? "<span class='tag tag-cat'>{$m['categoria_sugerida']}</span>" : '-';
            
            $html .= "<tr>
                <td>{$m['nome_arquivo']}</td>
                <td>{$cat}</td>
                <td>{$pags}</td>
                <td>{$campos}</td>
                <td>{$secoes}</td>
                <td>{$status}</td>
            </tr>";
        }
        $html .= '</table></div></body></html>';
        
        return $html;
    }
    
    private function exibirResumo() {
        foreach ($this->log as $linha) {
            echo $linha . "\n";
        }
    }
    
    private function log($msg) {
        $this->log[] = $msg;
    }
}

// Execução
$diretorioDocx = $argv[1] ?? './modelos_docx';
$diretorioSaida = $argv[2] ?? './auditoria';

if (!is_dir($diretorioDocx)) {
    die("ERRO: Diretório '{$diretorioDocx}' não encontrado!\n");
}

$auditor = new AuditorDocx($diretorioDocx, $diretorioSaida);
$auditor->executar();
