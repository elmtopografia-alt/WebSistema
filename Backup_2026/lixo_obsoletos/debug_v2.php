<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Erro 500 - V2</h1>";

echo "[1] Carregando configurações... <br>";
try {
    if (!file_exists('config.php')) throw new Exception("config.php não encontrado");
    require_once 'config.php';
    echo "OK: config.php carregado.<br>";
} catch (Throwable $e) { die("ERRO CRÍTICO: " . $e->getMessage()); }

echo "[2] Conectando ao Banco de Dados... <br>";
try {
    if (!file_exists('db.php')) throw new Exception("db.php não encontrado");
    require_once 'db.php';
    $conn = Database::getProd();
    if ($conn->connect_error) throw new Exception("Falha na conexão: " . $conn->connect_error);
    echo "OK: Conexão estabelecida.<br>";
} catch (Throwable $e) { die("ERRO BD: " . $e->getMessage()); }

echo "[3] Carregando Mapper de Equipamentos... <br>";
try {
    if (!file_exists('equipamentos_mapper.php')) throw new Exception("equipamentos_mapper.php não encontrado");
    require_once 'equipamentos_mapper.php';
    if (!class_exists('EquipamentosMapper')) throw new Exception("Classe EquipamentosMapper não definida");
    echo "OK: Mapper carregado.<br>";
} catch (Throwable $e) { die("ERRO CLASS: " . $e->getMessage()); }

echo "[4] Buscando Proposta de Teste (ID 177)... <br>";
$id_proposta = 177; // ID fixo para teste
try {
    // TESTE DA QUERY PRINCIPAL
    $sql = "SELECT p.*, c.nome_cliente FROM Propostas p 
            LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
            WHERE p.id_proposta = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Erro no prepare da query principal: " . $conn->error);
    
    $stmt->bind_param("i", $id_proposta);
    $stmt->execute();
    $res = $stmt->get_result();
    $dados = $res->fetch_assoc();
    
    if (!$dados) echo "AVISO: Proposta 177 não encontrada, mas query rodou.<br>";
    else echo "OK: Dados da proposta recuperados.<br>";
    $stmt->close(); // Fecha
} catch (Throwable $e) { die("ERRO QUERY PROPOSTA: " . $e->getMessage()); }

echo "[5] Buscando Conteúdo Personalizado... <br>";
try {
    $sql = "SELECT block_id FROM Proposta_Conteudo_Personalizado WHERE id_proposta = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id_proposta);
        $stmt->execute();
        $stmt->close(); // Fecha
        echo "OK: Tabela Proposta_Conteudo_Personalizado acessível.<br>";
    } else {
        echo "AVISO: Tabela Conteúdo não acessível (pode ser o erro): " . $conn->error . "<br>";
    }
} catch (Throwable $e) { echo "ERRO CATCH CONTEUDO: " . $e->getMessage() . "<br>"; }

echo "[6] Buscando Cronograma... <br>";
try {
    $sql = "SELECT * FROM Proposta_Cronograma WHERE id_proposta = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id_proposta);
        $stmt->execute();
        $stmt->close(); // Fecha
        echo "OK: Tabela Proposta_Cronograma acessível.<br>";
    } else {
        echo "AVISO: Tabela Cronograma não acessível (pode ser o erro): " . $conn->error . "<br>";
    }
} catch (Throwable $e) { echo "ERRO CATCH CRONOGRAMA: " . $e->getMessage() . "<br>"; }

echo "[7] Testando Template... <br>";
if (!file_exists('proposta_template_v2.php')) {
    die("ERRO: proposta_template_v2.php não encontrado.");
}
echo "OK: Arquivo de template existe.<br>";

echo "<hr><h3>Tentando incluir o gerador agora...</h3>";
$_GET['id'] = 177; // Simula ID
try {
    include 'gerar_proposta_v2.php';
} catch (Throwable $e) {
    echo "<h1>ERRO FATAL NO GERADOR: " . $e->getMessage() . "</h1>";
}
?>
