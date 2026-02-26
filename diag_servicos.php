<?php
require 'conexao.php';

echo "--- Tabela Tipo_Servicos (Modelos de Proposta) ---\n";
$res1 = $conn->query("SELECT id_servico, nome FROM Tipo_Servicos");
while ($row = $res1->fetch_assoc()) {
    echo "ID: {$row['id_servico']} | Nome: {$row['nome']}\n";
}

echo "\n--- Tabela tipos_servico (Classificações CRM) ---\n";
$res2 = $conn->query("SELECT id, nome FROM tipos_servico");
while ($row = $res2->fetch_assoc()) {
    echo "ID: {$row['id']} | Nome: {$row['nome']}\n";
}
?>
