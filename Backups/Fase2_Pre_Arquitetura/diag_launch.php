<?php
require_once 'config.php';
require_once 'db.php';

$ambiente = 'producao';
$conn = Database::getProd();

echo "--- DIAGNÓSTICO DE BANCO DE DADOS (PROPOSTAS) ---\n";

// 1. Verificar registros com numero_proposta vazio
$res = $conn->query("SELECT id_proposta, numero_proposta, data_criacao, status FROM Propostas WHERE numero_proposta = '' OR numero_proposta IS NULL");
echo "Propostas sem número: " . $res->num_rows . "\n";
while ($row = $res->fetch_assoc()) {
    echo "ID: {$row['id_proposta']} | Criada em: {$row['data_criacao']} | Status: {$row['status']}\n";
}

// 2. Verificar duplicatas de numero_proposta (exceto vazios)
$res = $conn->query("SELECT numero_proposta, COUNT(*) as qtd FROM Propostas WHERE numero_proposta != '' GROUP BY numero_proposta HAVING qtd > 1");
echo "\nNúmeros duplicados: " . $res->num_rows . "\n";
while ($row = $res->fetch_assoc()) {
    echo "Número: {$row['numero_proposta']} | Qtd: {$row['qtd']}\n";
}

// 3. Verificar estrutura da tabela
$res = $conn->query("SHOW CREATE TABLE Propostas");
$row = $res->fetch_assoc();
echo "\nEstrutura da Tabela:\n" . $row['Create Table'] . "\n";

echo "\n--- FIM DO DIAGNÓSTICO ---\n";
?>
