<?php
require_once __DIR__ . '/ConnectionManager.php';
$conn = ConnectionManager::get();

echo "<h1>Diagnóstico de Banco - Proposta 239</h1>";

// 1. Verificar se a tabela Propostas existe e se o ID 239 está lá
$res = $conn->query("SELECT id_proposta, numero_proposta, status, id_criador FROM Propostas WHERE id_proposta = 239");
if ($res) {
    if ($row = $res->fetch_assoc()) {
        echo "<p style='color:green'>✅ Proposta 239 encontrada!</p>";
        echo "<pre>"; print_r($row); echo "</pre>";
    } else {
        echo "<p style='color:red'>❌ Proposta 239 NÃO encontrada na tabela Propostas.</p>";
        
        // Listar as últimas 5 propostas para ver quais IDs existem
        echo "<h3>Últimas 5 propostas:</h3>";
        $res2 = $conn->query("SELECT id_proposta, numero_proposta FROM Propostas ORDER BY id_proposta DESC LIMIT 5");
        while($row2 = $res2->fetch_assoc()){
            echo "ID: {$row2['id_proposta']} - Numero: {$row2['numero_proposta']}<br>";
        }
    }
} else {
    echo "<p style='color:red'>❌ Erro ao consultar tabela Propostas: " . $conn->error . "</p>";
}

// 2. Verificar o ambiente (produção ou demo)
session_start();
echo "<h3>Ambiente da Sessão:</h3>";
echo "Ambiente: " . ($_SESSION['ambiente'] ?? 'Não definido (padrao: producao)') . "<br>";
echo "Usuario ID: " . ($_SESSION['usuario_id'] ?? 'Não logado') . "<br>";
?>
