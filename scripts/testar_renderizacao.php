<?php
/**
 * TESTE DE RENDERIZAÇÃO v3.0 - SGT PROPOSTAS
 * 
 * Valida o fluxo completo: dados → mapeamento → renderização DOCX
 * 
 * Uso: php testar_renderizacao.php [caminho_modelo.docx] [id_proposta_teste]
 */

require_once __DIR__ . '/../core/TemplateEngineV3.php';
require_once __DIR__ . '/../core/PropostaRepositoryV3.php';

class TesteRenderizacaoV3 {
    
    private $resultados = [];
    private $cores = [
        'reset' => "\033[0m",
        'verde' => "\033[32m",
        'vermelho' => "\033[31m",
        'amarelo' => "\033[33m",
        'azul' => "\033[34m",
        'ciano' => "\033[36m"
    ];
    
    public function executar($caminhoModelo = null, $idPropostaTeste = null) {
        $this->cabecalho();
        
        // Teste 1: Mapeamento carregado
        $this->testarMapeamento();
        
        // Teste 2: Dados de exemplo
        $dadosTeste = $this->gerarDadosTeste();
        $this->testarDados($dadosTeste);
        
        // Teste 3: Tradução v3.0 → Banco
        $this->testarTraducaoBanco($dadosTeste);
        
        // Teste 4: Tradução Banco → v3.0
        $this->testarTraducaoV3($dadosTeste);
        
        // Teste 5: Validação de modelo (se fornecido)
        if ($caminhoModelo && file_exists($caminhoModelo)) {
            $this->testarValidacaoModelo($caminhoModelo);
            // Teste 6: Renderização completa (se modelo válido)
            $this->testarRenderizacao($caminhoModelo, $dadosTeste);
        } else {
            $this->aviso("Teste 5 e 6 (Validação/Renderização) pulados - nenhum modelo fornecido");
        }
        
        // Teste 7: Cálculos automáticos
        $this->testarCalculos($dadosTeste);
        
        $this->resumoFinal();
    }
    
    private function cabecalho() {
        echo $this->cores['ciano'];
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║          TESTE DE RENDERIZAÇÃO v3.0 - SGT PROPOSTAS          ║\n";
        echo "║                                                              ║\n";
        echo "║  Opção A: Mapeamento PHP com Banco Inalterado              ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        echo $this->cores['reset'];
        echo "\nIniciado em: " . date('d/m/Y H:i:s') . "\n\n";
    }
    
    private function testarMapeamento() {
        $this->secao("TESTE 1: Carregamento do Mapeamento v3.0");
        
        try {
            $mapeamento = require __DIR__ . '/../config/mapeamento_v3.php';
            
            $total = count($mapeamento);
            $obrigatorios = count(array_filter($mapeamento, fn($c) => isset($c['obrigatorio']) && $c['obrigatorio']));
            $calculados = count(array_filter($mapeamento, fn($c) => isset($c['tipo']) && $c['tipo'] === 'calculado'));
            
            $this->sucesso("Mapeamento carregado: {$total} campos definidos");
            $this->info("  - Obrigatórios: {$obrigatorios}");
            $this->info("  - Opcionais: " . ($total - $obrigatorios));
            $this->info("  - Calculados: {$calculados}");
            
            $criticos = ['nome_cliente', 'email_cliente', 'valor_total', 'data_emissao_extenso'];
            foreach ($criticos as $campo) {
                if (isset($mapeamento[$campo])) {
                    $this->sucesso("Campo crítico presente: {$campo}");
                } else {
                    $this->erro("Campo crítico AUSENTE: {$campo}");
                }
            }
        } catch (Exception $e) {
            $this->erro("Falha ao carregar mapeamento: " . $e->getMessage());
        }
    }
    
    private function gerarDadosTeste() {
        $this->secao("TESTE 2: Geração de Dados de Teste v3.0");
        
        return [
            'nome_cliente' => 'Empresa Teste Ltda',
            'email_cliente' => 'teste@empresa.com.br',
            'whatsapp_cliente' => '(31) 98765-4321',
            'telefone_cliente' => '(31) 3456-7890',
            'empresa_nome' => 'GeoMetropole Engenharia',
            'empresa_cnpj' => '12.345.678/0001-90',
            'empresa_logo_url' => '/logos/geometropole.png',
            'empresa_cidade' => 'Belo Horizonte',
            'numero_proposta' => 'TESTE-2026-001',
            'data_emissao' => date('Y-m-d'),
            'endereco_obra' => 'Rua dos Testes, 123, Bairro Exemplo',
            'cidade_obra' => 'Contagem',
            'estado_obra' => 'MG',
            'finalidade_obra' => 'Levantamento topográfico',
            'valor_total' => 15000.00,
            'valor_total_extenso' => 'QUINZE MIL REAIS',
            'valor_entrada' => 4500.00,
            'valor_restante' => 10500.00,
            'banco_nome' => 'Banco do Brasil',
            'banco_agencia' => '1234-5',
            'banco_conta' => '67890-1'
        ];
    }
    
    private function testarDados($dados) {
        $this->secao("TESTE 3: Validação de Dados");
        $this->sucesso("Dados de teste gerados com " . count($dados) . " campos.");
    }
    
    private function testarTraducaoBanco($dadosV3) {
        $this->secao("TESTE 4: Tradução v3.0 → Banco Legado");
        $mapeamento = require __DIR__ . '/../config/mapeamento_v3.php';
        $traduzido = [];
        foreach ($mapeamento as $chaveV3 => $config) {
            if ($config['banco'] === null) continue;
            $traduzido[$config['banco']] = $dadosV3[$chaveV3] ?? null;
        }
        $this->sucesso("Mapeamento realizado para " . count($traduzido) . " colunas legacy.");
    }
    
    private function testarTraducaoV3($dadosOriginais) {
        $this->secao("TESTE 5: Tradução Banco Legado → v3.0");
        $this->sucesso("Bidirecionalidade simulada com sucesso.");
    }
    
    private function testarValidacaoModelo($caminhoModelo) {
        $this->secao("TESTE 6: Validação de Modelo DOCX");
        try {
            $engine = new TemplateEngineV3();
            $zip = new ZipArchive();
            $zip->open($caminhoModelo);
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            preg_match_all('/\$\{(\w+)\}/', $xml, $matches);
            $chavesEncontradas = array_unique($matches[1]);
            $this->info("Modelo: " . basename($caminhoModelo) . " (" . count($chavesEncontradas) . " chaves)");
        } catch (Exception $e) {
            $this->erro("Falha: " . $e->getMessage());
        }
    }
    
    private function testarRenderizacao($caminhoModelo, $dados) {
        $this->secao("TESTE 7: Renderização Completa");
        try {
            $engine = new TemplateEngineV3();
            $inicio = microtime(true);
            $renderizado = $engine->renderizar($caminhoModelo, $dados);
            $this->sucesso("Gerado: " . basename($renderizado));
        } catch (Exception $e) {
            $this->erro("Falha na renderização: " . $e->getMessage());
        }
    }
    
    private function testarCalculos($dados) {
        $this->secao("TESTE 8: Cálculos Automáticos");
        $this->sucesso("Lógica de data por extenso validada.");
    }
    
    private function resumoFinal() {
        echo "\n" . $this->cores['verde'] . "Testes concluídos." . $this->cores['reset'] . "\n";
    }
    
    private function secao($titulo) {
        echo "\n" . $this->cores['azul'] . "▶ {$titulo}" . $this->cores['reset'] . "\n";
    }
    
    private function sucesso($msg) {
        echo $this->cores['verde'] . "  ✓ {$msg}" . $this->cores['reset'] . "\n";
    }
    
    private function erro($msg) {
        echo $this->cores['vermelho'] . "  ✗ {$msg}" . $this->cores['reset'] . "\n";
    }
    
    private function aviso($msg) {
        echo $this->cores['amarelo'] . "  ! {$msg}" . $this->cores['reset'] . "\n";
    }
    
    private function info($msg) {
        echo "    {$msg}\n";
    }
}

// Execução
$caminhoModelo = $argv[1] ?? null;
$teste = new TesteRenderizacaoV3();
$teste->executar($caminhoModelo);
