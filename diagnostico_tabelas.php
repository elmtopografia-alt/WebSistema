<?php
// diagnostico_tabelas.php - Script de Varredura para SGT V3.0
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Diagnóstico SGT</title><style>
    body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
    h1 { color: #569cd6; }
    h2 { color: #c586c0; border-bottom: 1px solid #333; padding-bottom: 5px; }
    .success { color: #4ec9b0; }
    .error { color: #f44747; }
    .warning { color: #d7ba7d; }
    .info { color: #9cdcfe; }
    pre { background: #2d2d2d; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style></head><body>";

echo "<h1>Diagnóstico de Ambiente e Banco de Dados SGT</h1>";
echo "<p>Hora do Teste: " . date('Y-m-d H:i:s') . "</p>";

// 1. Teste de Inclusões Básicas
echo "<h2>1. Carga de Arquivos Base</h2>";
$arquivos = [
    'config.php', 
    'ConnectionManager.php', 
    'PropostaRepository.php', 
    'renderizador_modelo_docx.php'
];

foreach ($arquivos as $arq) {
    if (file_exists(__DIR__ . '/' . $arq)) {
        echo "<div class='success'>[OK] {$arq} encontrado.</div>";
    } else {
        echo "<div class='error'>[FALHA] {$arq} não existe.</div>";
    }
}

// 2. Teste de Conexão com o Banco
echo "<h2>2. Teste de Banco de Dados</h2>";
try {
    require_once __DIR__ . '/ConnectionManager.php';
    $conn = ConnectionManager::get();
    echo "<div class='success'>[OK] Conexão MySQL estabelecida.</div>";
    
    // Lista colunas da tabela propostas para garantir o Auto-Heal
    $res = $conn->query("SHOW COLUMNS FROM Propostas");
    if ($res) {
        $colunasEncontradas = [];
        while($row = $res->fetch_assoc()) $colunasEncontradas[] = $row['Field'];
        $vitais = ['modelo_docx', 'docx_conteudo', 'docx_blocos_count', 'config_docx_json'];
        
        $faltantes = array_diff($vitais, $colunasEncontradas);
        if (empty($faltantes)) {
            echo "<div class='success'>[OK] Todas as 4 colunas DOCX (V3.0) existem no banco de dados.</div>";
        } else {
            echo "<div class='error'>[FALHA] As seguintes colunas faltam no banco: " . implode(', ', $faltantes) . "</div>";
            echo "<div class='warning'>A tabela precisa de alteração. Execute o autoHeal() via Repository ou migração manual.</div>";
        }
    } else {
        echo "<div class='error'>[FALHA] Não foi possível ler propriedades da tabela Propostas. Query falhou.</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>[FALHA] Erro de DB: " . $e->getMessage() . "</div>";
    die("</body></html>");
}

// 3. Teste do Repository Isolado
echo "<h2>3. Teste: PropostaRepository e JSON Decode</h2>";
try {
    require_once __DIR__ . '/PropostaRepository.php';
    $repo = new PropostaRepository();
    echo "<div class='success'>[OK] PropostaRepository instanciado.</div>";
    
    // Tenta carregar a proposta problemática
    $id_teste = 203; // Id de referência com modelo DOCX
    $dados = $repo->buscarPorId($id_teste);
    
    if (!$dados) {
        echo "<div class='warning'>[AVISO] ID $id_teste não retornou nada. Tentando buscar a última criada...</div>";
        $resUlt = $conn->query("SELECT id_proposta, modelo_docx FROM Propostas ORDER BY id_proposta DESC LIMIT 1");
        if ($row = $resUlt->fetch_assoc()) {
            echo "<div class='info'>Encontrado ID {$row['id_proposta']} com modelo '{$row['modelo_docx']}'... Testando ele:</div>";
            $dados = $repo->buscarPorId($row['id_proposta']);
        }
    }
    
    if ($dados) {
        echo "<div class='success'>[OK] BuscaPorId executada sem crash.</div>";
        echo "<div class='info'>ID Lida: {$dados['id_proposta']}</div>";
        echo "<div class='info'>Modelo DOCX Atual: " . ($dados['modelo_docx'] ?: 'NÃO DEFINIDO') . "</div>";
        echo "<pre>Extrato dos dados puxados:\n" . print_r(array_slice($dados, 0, 10), true) . "\n...</pre>";
    } else {
        echo "<div class='error'>[FALHA] Nenhuma proposta recuperada para teste.</div>";
    }

} catch (Exception $e) {
    echo "<div class='error'>[FALHA FATAL PHP]: " . $e->getMessage() . " nas linhas: " . $e->getLine() . " (" . basename($e->getFile()) . ")</div>";
} catch (Error $err) {
    // Pega erros sintáticos do PHP7+ (Fatal Errors viram TypeError/ParseError etc)
    echo "<div class='error'>[ERROR DE SINTAXE PHP]: " . $err->getMessage() . " nas linhas: " . $err->getLine() . " (" . basename($err->getFile()) . ")</div>";
}

// 4. Teste Modulos Modelos
echo "<h2>4. Classes de Modelos (PropostaDrone)</h2>";
$arquivo_modelo = __DIR__ . '/modelos_gerados/ModeloPropostaDrone.php';
$classe_modelo = 'SGT\\Propostas\\ModeloPropostaDrone';

if (file_exists($arquivo_modelo)) {
    echo "<div class='success'>[OK] Arquivo {$arquivo_modelo} existe.</div>";
    try {
        require_once $arquivo_modelo;
        echo "<div class='success'>[OK] Arquivo lido (require_once) sem falha sintática 500.</div>";
        
        if (class_exists($classe_modelo)) {
             echo "<div class='success'>[OK] Classe {$classe_modelo} invocável da memória.</div>";
             $inst = new $classe_modelo();
             echo "<div class='info'>Confirmação! A classe instanciou. Namespace correto.</div>";
        } else {
             echo "<div class='error'>[FALHA] A classe {$classe_modelo} não foi achada. Veja se o namespace dentro do arquivo está 'namespace SGT\Propostas;'.</div>";
        }
    } catch(Throwable $e) {
        echo "<div class='error'>[FALHA FATAL] A classe PropostaDrone tem erro: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='warning'>Arquivo 'ModeloPropostaDrone.php' não existe no servidor (ainda não gerado).</div>";
}

echo "<h2>5. Coluna 'cor' e TemaEngine — Sistema v2</h2>";
try {
    // 5a. Coluna 'cor' existe?
    $resCor = $conn->query("SHOW COLUMNS FROM Propostas LIKE 'cor'");
    if ($resCor && $resCor->num_rows > 0) {
        $colCor = $resCor->fetch_assoc();
        echo "<div class='success'>[OK] Coluna 'cor' existe — Tipo: {$colCor['Type']} | Default: {$colCor['Default']}</div>";
        
        // 5b. Verifica se propostas recentes têm cor salva
        $resRecentes = $conn->query("SELECT id_proposta, modelo_docx, cor FROM Propostas ORDER BY id_proposta DESC LIMIT 5");
        echo "<div class='info'>Últimas 5 propostas:</div>";
        echo "<pre>";
        while ($r = $resRecentes->fetch_assoc()) {
            $corVal = $r['cor'] ?: '(NULL/vazia)';
            $modeloVal = $r['modelo_docx'] ?: '(sem modelo)';
            echo sprintf("  ID %-6s | modelo: %-20s | cor: %s\n", $r['id_proposta'], $modeloVal, $corVal);
        }
        echo "</pre>";
        
        // 5c. Estatística geral
        $stat = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN cor IS NOT NULL AND cor != '' THEN 1 ELSE 0 END) as com_cor FROM Propostas")->fetch_assoc();
        echo "<div class='info'>Total propostas: {$stat['total']} | Com cor definida: {$stat['com_cor']}</div>";
        if ($stat['com_cor'] == 0) {
            echo "<div class='warning'>[AVISO] Nenhuma proposta ainda tem cor. Isso é normal se o sistema foi migrado agora — novas propostas já salvarão a cor.</div>";
        }
        
    } else {
        echo "<div class='error'>[FALHA] Coluna 'cor' NÃO existe na tabela Propostas!</div>";
        echo "<div class='warning'>Execute: ALTER TABLE Propostas ADD COLUMN cor VARCHAR(20) NOT NULL DEFAULT 'verde' AFTER modelo_docx;</div>";
        // Tenta criar automaticamente
        if ($conn->query("ALTER TABLE Propostas ADD COLUMN cor VARCHAR(20) NOT NULL DEFAULT 'verde' AFTER modelo_docx")) {
            echo "<div class='success'>[AUTO-HEAL] Coluna 'cor' criada com sucesso! Recarregue a página para confirmar.</div>";
        } else {
            echo "<div class='error'>Auto-heal falhou: " . $conn->error . "</div>";
        }
    }
    
    // 5d. TemaEngine
    if (file_exists(__DIR__ . '/core/TemaEngine.php')) {
        require_once __DIR__ . '/core/TemaEngine.php';
        $cores = ['verde','azul','laranja','cinza'];
        echo "<div class='info'>TemaEngine — testando 4 cores:</div><pre>";
        foreach ($cores as $c) {
            $t = new TemaEngine($c);
            $p = $t->getPaleta();
            echo "  {$c}: primaria=#{$p['primaria']} | nome={$p['nome']}\n";
        }
        echo "</pre>";
        echo "<div class='success'>[OK] TemaEngine funcional.</div>";
    } else {
        echo "<div class='error'>[FALHA] core/TemaEngine.php não encontrado no servidor.</div>";
    }
    
} catch (Throwable $e) {
    echo "<div class='error'>[ERRO] " . $e->getMessage() . " (linha " . $e->getLine() . ")</div>";
}

echo "<h2>Fim do Diagnóstico</h2>";
echo "</body></html>";
