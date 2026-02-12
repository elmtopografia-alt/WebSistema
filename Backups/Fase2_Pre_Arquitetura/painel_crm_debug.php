<?php
// painel_crm_debug.php - VERSÃO DE DEBUG (Apagar depois)
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>TESTE 1: PHP Funcionando</h1>";

// Teste 2: Session
if (file_exists('session_validator.php')) {
    require_once 'session_validator.php';
    echo "<h2>TESTE 2: session_validator.php OK</h2>";
} else {
    session_start();
    echo "<h2>TESTE 2: session_start() manual</h2>";
}

// Teste 3: Banco
echo "<h2>TESTE 3: Carregando db.php...</h2>";
require_once 'db.php';
echo "<h2>TESTE 3: db.php OK</h2>";

// Teste 4: Conexão
echo "<h2>TESTE 4: Tentando conexão...</h2>";
$ambiente = isset($_SESSION['ambiente']) ? $_SESSION['ambiente'] : 'producao';
$conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();
echo "<h2>TESTE 4: Conexão OK</h2>";

// Teste 5: Query simples
echo "<h2>TESTE 5: Query simples...</h2>";
$result = $conn->query("SELECT 1 as teste");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<h2>TESTE 5: Query retornou: " . $row['teste'] . "</h2>";
} else {
    echo "<h2 style='color:red'>TESTE 5: ERRO na query: " . $conn->error . "</h2>";
}

// Teste 6: Verifica tabela Propostas
echo "<h2>TESTE 6: Verificando tabela Propostas...</h2>";
$check = $conn->query("SHOW COLUMNS FROM Propostas LIKE 'fase_crm'");
if ($check) {
    echo "<h2>TESTE 6: Coluna fase_crm existe? " . ($check->num_rows > 0 ? 'SIM' : 'NÃO') . "</h2>";
} else {
    echo "<h2 style='color:red'>TESTE 6: ERRO: " . $conn->error . "</h2>";
}

echo "<hr><h1 style='color:green'>TESTES BÁSICOS PASSARAM!</h1>";

// Teste 7: Query de KPIs
echo "<h2>TESTE 7: Query de KPIs...</h2>";
$id_usuario = $_SESSION['usuario_id'];
$sqlKPI = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN fase_crm = 'FECHADA' THEN 1 ELSE 0 END) as fechadas
    FROM Propostas WHERE id_criador = $id_usuario";
$resKPI = $conn->query($sqlKPI);
if ($resKPI) {
    $kpis = $resKPI->fetch_assoc();
    echo "<h2>TESTE 7: KPIs OK - Total: " . $kpis['total'] . ", Fechadas: " . $kpis['fechadas'] . "</h2>";
} else {
    echo "<h2 style='color:red'>TESTE 7: ERRO: " . $conn->error . "</h2>";
}

// Teste 8: Query Dedo-Duro
echo "<h2>TESTE 8: Query Dedo-Duro...</h2>";
$sqlDedoDuro = "SELECT p.id_proposta, c.nome_cliente 
                 FROM Propostas p 
                 LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente
                 WHERE p.id_criador = $id_usuario LIMIT 3";
$resDedo = $conn->query($sqlDedoDuro);
if ($resDedo) {
    echo "<h2>TESTE 8: Query Dedo-Duro OK - Rows: " . $resDedo->num_rows . "</h2>";
} else {
    echo "<h2 style='color:red'>TESTE 8: ERRO: " . $conn->error . "</h2>";
}

// Teste 9: fetch_all (pode não existir em PHP antigo)
echo "<h2>TESTE 9: Testando fetch_all()...</h2>";
if (method_exists($resDedo, 'fetch_all')) {
    $dados = $resDedo->fetch_all(MYSQLI_ASSOC);
    echo "<h2>TESTE 9: fetch_all() OK - Retornou " . count($dados) . " itens</h2>";
} else {
    echo "<h2 style='color:red'>TESTE 9: ERRO! fetch_all() NÃO EXISTE nesta versão do PHP!</h2>";
    echo "<p>Versão do PHP: " . phpversion() . "</p>";
}

// Teste 10: Tabela Interacoes_CRM
echo "<h2>TESTE 10: Verificando tabela Interacoes_CRM...</h2>";
$checkTable = $conn->query("SHOW TABLES LIKE 'Interacoes_CRM'");
if ($checkTable) {
    echo "<h2>TESTE 10: Tabela Interacoes_CRM existe? " . ($checkTable->num_rows > 0 ? 'SIM' : 'NÃO - Será criada no primeiro acesso') . "</h2>";
} else {
    echo "<h2 style='color:red'>TESTE 10: ERRO: " . $conn->error . "</h2>";
}

echo "<hr><h1>Versão do PHP: " . phpversion() . "</h1>";
echo "<p>Se o TESTE 9 falhou, esse é o problema! A função fetch_all() requer mysqlnd.</p>";
?>
