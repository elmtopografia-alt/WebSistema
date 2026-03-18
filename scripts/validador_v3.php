<?php
/**
 * VALIDADOR v3.0 - SGT PROPOSTAS
 * 
 * Verifica se DOCXs seguem norma v3.0 estritamente
 * NÃO corrige - apenas reporta para edição humana
 */

require_once __DIR__ . '/../core/TemplateEngineV3.php';

class ValidadorV3 {
    
    private $mapeamento;
    private $chavesPermitidas;
    private $erros = [];
    private $relatorio = [];
    
    public function __construct() {
        $this->mapeamento = require __DIR__ . '/../config/mapeamento_v3.php';
        $this->chavesPermitidas = array_keys($this->mapeamento);
    }
    
    public function validarDiretorio($caminhoDir) {
        $arquivos = [];
        if (is_dir($caminhoDir)) {
            foreach (scandir($caminhoDir) as $item) {
                if (substr(strtolower($item), -5) === '.docx') {
                    $arquivos[] = $caminhoDir . '/' . $item;
                }
            }
        }
        
        echo "========================================\n";
        echo "VALIDADOR v3.0 - SGT PROPOSTAS\n";
        echo "Data: " . date('d/m/Y H:i:s') . "\n";
        echo "Diretório: {$caminhoDir}\n";
        echo "Arquivos: " . count($arquivos) . "\n";
        echo "========================================\n\n";
        
        foreach ($arquivos as $arquivo) {
            $this->validarArquivo($arquivo);
        }
        
        $this->gerarRelatorio();
    }
    
    private function validarArquivo($caminho) {
        $nome = basename($caminho);
        echo "Validando: {$nome}... ";
        
        try {
            $zip = new ZipArchive();
            if ($zip->open($caminho) !== true) {
                throw new Exception("Não é DOCX válido");
            }
            
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            
            if (!$xml) {
                throw new Exception("XML não encontrado");
            }
            
            // Extrair chaves
            preg_match_all('/\$\{(\w+)\}/', $xml, $matches);
            $chavesEncontradas = array_unique($matches[1]);
            
            // Validar
            $invalidas = [];
            $validas = [];
            
            foreach ($chavesEncontradas as $chave) {
                if (in_array($chave, $this->chavesPermitidas)) {
                    $validas[] = $chave;
                } else {
                    $invalidas[] = $chave;
                }
            }
            
            $this->relatorio[] = [
                'arquivo' => $nome,
                'status' => empty($invalidas) ? 'APROVADO' : 'REPROVADO',
                'chaves_validas' => $validas,
                'chaves_invalidas' => $invalidas,
                'total_chaves' => count($chavesEncontradas)
            ];
            
            if (empty($invalidas)) {
                echo "✓ APROVADO (" . count($validas) . " chaves)\n";
            } else {
                echo "✗ REPROVADO\n";
                echo "  Chaves inválidas: " . implode(', ', $invalidas) . "\n";
            }
            
        } catch (Exception $e) {
            $this->relatorio[] = [
                'arquivo' => $nome,
                'status' => 'ERRO',
                'mensagem' => $e->getMessage()
            ];
            echo "✗ ERRO: " . $e->getMessage() . "\n";
        }
    }
    
    private function gerarRelatorio() {
        $aprovados = array_filter($this->relatorio, function($r) {
            return $r['status'] === 'APROVADO';
        });
        
        $reprovados = array_filter($this->relatorio, function($r) {
            return $r['status'] === 'REPROVADO';
        });
        
        $erros = array_filter($this->relatorio, function($r) {
            return $r['status'] === 'ERRO';
        });
        
        echo "\n========================================\n";
        echo "RESUMO DA VALIDAÇÃO\n";
        echo "========================================\n";
        echo "Aprovados:   " . count($aprovados) . "\n";
        echo "Reprovados:  " . count($reprovados) . " ← REQUEREM EDIÇÃO\n";
        echo "Erros:       " . count($erros) . "\n";
        echo "----------------------------------------\n";
        
        if (!empty($reprovados)) {
            echo "\nARQUIVOS REPROVADOS (editar manualmente):\n";
            foreach ($reprovados as $r) {
                echo "\n  📄 {$r['arquivo']}\n";
                echo "     Chaves inválidas:\n";
                foreach ($r['chaves_invalidas'] as $inv) {
                    echo "       - \${{$inv}}\n";
                }
                echo "     → Substituir por chaves v3.0 oficiais\n";
            }
        }
        
        // Salvar JSON detalhado
        $arquivoJson = __DIR__ . '/../auditoria/validacao_v3_' . date('Ymd_His') . '.json';
        file_put_contents($arquivoJson, json_encode($this->relatorio, JSON_PRETTY_PRINT));
        echo "\nRelatório detalhado: " . basename($arquivoJson) . "\n";
        
        // Retorna código de erro se houver reprovados
        exit(count($reprovados) > 0 ? 1 : 0);
    }
}

// Execução (Suporta CLI ou Web)
$isWeb = (php_sapi_name() !== 'cli');
if ($isWeb) echo "<pre style='background:#1e1e1e;color:#0f0;padding:20px;font-family:monospace;'>";

if ($isWeb) {
    $diretorio = $_GET['dir'] ?? __DIR__ . '/../modelos_unificados';
} else {
    $diretorio = $argv[1] ?? __DIR__ . '/../modelos_unificados';
}

$validador = new ValidadorV3();
$validador->validarDiretorio($diretorio);

if ($isWeb) {
    echo "</pre>";
    exit(0); // evita exit de CLI abaixo se for browser
}
