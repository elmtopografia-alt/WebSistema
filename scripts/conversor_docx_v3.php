<?php
/**
 * CONVERSOR DOCX v3.0 - SGT PROPOSTAS
 * 
 * Faz o trabalho grosso: copia + substitui chaves legado → v3.0
 * Depois você valida arquivo por arquivo com validador_v3.php
 * 
 * Uso: php conversor_docx_v3.php
 */

class ConversorDocxV3 {
    
    private $substituicoes = [
        // Dados do cliente
        '${nome_cliente_salvo}' => '${nome_cliente}',
        '${email_salvo}' => '${email_cliente}',
        '${mail}' => '${email_cliente}',
        '${e-mail}' => '${email_cliente}',
        '${whatsapp_salvo}' => '${whatsapp_cliente}',
        '${whatsapp}' => '${whatsapp_cliente}',
        '${telefone_salvo}' => '${telefone_cliente}',
        
        // Dados da empresa
        '${empresa}' => '${empresa_nome}',
        '${cnpj}' => '${empresa_cnpj}',
        '${logo}' => '${empresa_logo_url}',
        '${cidade}' => '${empresa_cidade}',
        
        // Proposta e obra
        '${endereco_obra}' => '${endereco_obra}', // já está v3.0
        '${cidade_obra}' => '${cidade_obra}', // já está v3.0
        '${estado_obra}' => '${estado_obra}', // já está v3.0
        '${finalidade}' => '${finalidade_obra}',
        
        // Valores - CRÍTICO
        '${valorproposta}' => '${valor_total}',
        '${valorextenso}' => '${valor_total_extenso}',
        '${valor_extenso}' => '${valor_total_extenso}', // novo legado
        '${Valor_proposta_extenso}' => '${valor_total_extenso}',
        '${mobilizacao_valor}' => '${valor_entrada}',
        '${restante_valor}' => '${valor_restante}',
        
        // Dados bancários (case sensivity importa)
        '${banco}' => '${banco_nome}',
        '${Banco}' => '${banco_nome}',
        '${agencia}' => '${banco_agencia}',
        '${Agencia}' => '${banco_agencia}',
        '${conta}' => '${banco_conta}',
        '${Conta}' => '${banco_conta}',
        '${PIX}' => '${chave_pix}',
        
        // Datas e localizações genéricas
        '${data_extenso}' => '${data_emissao_extenso}',
        '${Data}' => '${data_emissao_extenso}',
        '${Cidade}' => '${empresa_cidade}',
        '${CNPJ}' => '${empresa_cnpj}',
        '${Empresa}' => '${empresa_nome}',
        
        // Equipamentos e Adicionais
        '${Veiculo}' => '${veiculo}',
        '${GPS}' => '${gps}',
        '${Drone}' => '${drone}',
        '${Estacao_Total}' => '${estacao_total}',
        '${tabela_cronograma}' => '${cronograma}',
        
        // Outros legados encontrados na auditoria
        '${nome}' => '${nome_cliente}',
        '${cliente}' => '${nome_cliente}',
        '${email}' => '${email_cliente}',
        '${valor}' => '${valor_total}',
        '${total}' => '${valor_total}',
        '${celular_cliente}' => '${whatsapp_cliente}',
        '${finalidade}' => '${finalidade_obra}',
    ];
    
    private $origem;
    private $destino;
    private $log = [];
    private $chavesValidas = [];
    
    public function __construct($origem, $destino) {
        $this->origem = $origem;
        $this->destino = $destino;
        
        $mapaOff = require __DIR__ . '/../config/mapeamento_v3.php';
        foreach ($mapaOff as $k => $info) {
            $this->chavesValidas[] = $info['docx'];
        }
        
        if (!is_dir($this->destino)) {
            mkdir($this->destino, 0777, true);
        }
    }
    
    public function executar() {
        echo "========================================\n";
        echo "CONVERSOR DOCX v3.0 - TRABALHO GROSSO\n";
        echo "========================================\n";
        echo "Origem:  {$this->origem}\n";
        echo "Destino: {$this->destino}\n\n";
        
        $arquivos = [];
        if (is_dir($this->origem)) {
            foreach (scandir($this->origem) as $item) {
                if (substr(strtolower($item), -5) === '.docx') {
                    $arquivos[] = $this->origem . '/' . $item;
                }
            }
        }
        
        if (empty($arquivos)) {
            die("Nenhum .docx encontrado em {$this->origem}\n");
        }
        
        echo "Arquivos encontrados: " . count($arquivos) . "\n\n";
        
        foreach ($arquivos as $arquivo) {
            $this->converterArquivo($arquivo);
        }
        
        $this->gerarRelatorio();
    }
    
    private function converterArquivo($caminhoOrigem) {
        $nome = basename($caminhoOrigem);
        $caminhoDestino = $this->destino . '/' . $nome;
        
        echo "Convertendo: {$nome} ... ";
        
        try {
            // Abrir DOCX
            $zip = new ZipArchive();
            if ($zip->open($caminhoOrigem) !== true) {
                throw new Exception("Não conseguiu abrir ZIP");
            }
            
            // Ler document.xml
            $xml = $zip->getFromName('word/document.xml');
            if (!$xml) {
                throw new Exception("document.xml não encontrado");
            }
            
            // Contar substituições
            $contador = 0;
            $xmlNovo = $xml;
            $chavesEncontradas = [];
            
            foreach ($this->substituicoes as $antiga => $nova) {
                $ocorrencias = substr_count($xmlNovo, $antiga);
                if ($ocorrencias > 0) {
                    $chavesEncontradas[] = "{$antiga} ({$ocorrencias}x)";
                    $contador += $ocorrencias;
                    $xmlNovo = str_replace($antiga, $nova, $xmlNovo);
                }
            }
            
            // Verificar se ainda restam chaves ${...} que são inválidas
            preg_match_all('/\$\{(\w+)\}/', $xmlNovo, $matches);
            $todasExistentes = array_unique($matches[0]);
            
            $chavesNaoMapeadas = [];
            foreach ($todasExistentes as $k) {
                // Se a chave não está na lista de válidas v3, então não está mapeada corretamente
                if (!in_array($k, $this->chavesValidas)) {
                    $chavesNaoMapeadas[] = $k;
                }
            }
            
            // FECHAR ORIGINAL (SÓ LEITURA)
            $zip->close();
            
            // Usar temp file seguro no Windows para edições do ZipArchive
            $tempFile = tempnam(sys_get_temp_dir(), 'docx_v3_');
            copy($caminhoOrigem, $tempFile);
            
            // Reabrir no temp seguro e salvar XML modificado
            $zipTemp = new ZipArchive();
            if ($zipTemp->open($tempFile) === true) {
                $zipTemp->deleteName('word/document.xml');
                $zipTemp->addFromString('word/document.xml', $xmlNovo);
                $zipTemp->close();
                
                // Agora mover o temp finalizado de volta para o destino final
                if (file_exists($caminhoDestino)) {
                    unlink($caminhoDestino);
                }
                rename($tempFile, $caminhoDestino);
            } else {
                if (file_exists($tempFile)) unlink($tempFile);
                throw new Exception("Falha ao abrir DOCX temp para salvação");
            }
            
            $this->log[] = [
                'arquivo' => $nome,
                'status' => 'CONVERTIDO',
                'substituicoes' => $contador,
                'chaves_trocadas' => $chavesEncontradas,
                'chaves_nao_mapeadas' => $chavesNaoMapeadas,
                'aviso' => !empty($chavesNaoMapeadas) ? 'REQUER_REVISAO' : 'OK'
            ];
            
            if (!empty($chavesNaoMapeadas)) {
                echo "⚠️  CONVERTIDO ({$contador} trocas, " . count($chavesNaoMapeadas) . " chaves não mapeadas)\n";
            } else {
                echo "✓ CONVERTIDO ({$contador} trocas)\n";
            }
            
        } catch (Exception $e) {
            $this->log[] = [
                'arquivo' => $nome,
                'status' => 'ERRO',
                'mensagem' => $e->getMessage()
            ];
            echo "✗ ERRO: " . $e->getMessage() . "\n";
        }
    }
    
    private function gerarRelatorio() {
        $convertidos = array_filter($this->log, fn($l) => $l['status'] === 'CONVERTIDO');
        $erros = array_filter($this->log, fn($l) => $l['status'] === 'ERRO');
        $revisao = array_filter($this->log, fn($l) => ($l['aviso'] ?? '') === 'REQUER_REVISAO');
        
        echo "\n========================================\n";
        echo "RELATÓRIO DE CONVERSÃO\n";
        echo "========================================\n";
        echo "Convertidos:    " . count($convertidos) . "\n";
        echo "Erros:          " . count($erros) . "\n";
        echo "Requer revisão: " . count($revisao) . " ← ATENÇÃO\n";
        
        if (!empty($revisao)) {
            echo "\nARQUIVOS QUE PRECISAM DE REVISÃO MANUAL:\n";
            foreach ($revisao as $r) {
                echo "\n  📄 {$r['arquivo']}\n";
                echo "     Chaves não mapeadas encontradas:\n";
                foreach ($r['chaves_nao_mapeadas'] as $chave) {
                    echo "       - {$chave}\n";
                }
                echo "     → Edite manualmente no Word\n";
            }
        }
        
        // Salvar JSON detalhado
        $jsonFile = $this->destino . '/_relatorio_conversao_' . date('Ymd_His') . '.json';
        file_put_contents($jsonFile, json_encode($this->log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "\nRelatório salvo: {$jsonFile}\n";
        
        echo "\n========================================\n";
        echo "PRÓXIMO PASSO:\n";
        echo "Execute: php validador_v3.php {$this->destino}\n";
        echo "Para validar cada arquivo convertido.\n";
        echo "========================================\n";
    }
}

// Execução (Suporta CLI ou Web)
$isWeb = (php_sapi_name() !== 'cli');
if ($isWeb) echo "<pre style='background:#1e1e1e;color:#0f0;padding:20px;font-family:monospace;'>";

if ($isWeb) {
    $origem = $_GET['origem'] ?? __DIR__ . '/../modelos_prod';
    $destino = $_GET['destino'] ?? __DIR__ . '/../modelos_unificados';
} else {
    $origem = $argv[1] ?? __DIR__ . '/../modelos_prod';
    $destino = $argv[2] ?? __DIR__ . '/../modelos_unificados';
}

$conversor = new ConversorDocxV3($origem, $destino);
$conversor->executar();

if ($isWeb) echo "</pre>";