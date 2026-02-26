<?php
/**
 * diagnostico_custos.php - Valida se as tabelas Master-Detail estão recebendo dados
 */
require_once 'config.php';
require_once 'ConnectionManager.php';
require_once 'PropostaRepository.php';

header('Content-Type: text/plain');
echo "--- DIAGNÓSTICO DE CUSTOS OPERACIONAIS (Master-Detail) ---\n\n";

$conn = ConnectionManager::get();
$tables = [
    'Proposta_Salarios',
    'Proposta_Estadia',
    'Proposta_Consumos',
    'Proposta_Locacao',
    'Proposta_Custos_Administrativos'
];

foreach ($tables as $table) {
    $res = $conn->query("SELECT COUNT(*) as total FROM $table");
    if ($res) {
        $row = $res->fetch_assoc();
        echo "Tabela $table: " . $row['total'] . " registros encontrados.\n";
        
        if ($row['total'] > 0) {
            echo "Amostra dos últimos 2:\n";
            $sample = $conn->query("SELECT * FROM $table ORDER BY id DESC LIMIT 2");
            while($s = $sample->fetch_assoc()) {
                print_r($s);
            }
        }
    } else {
        echo "ERRO: Tabela $table não encontrada ou erro na query: " . $conn->error . "\n";
    }
    echo "-------------------------------------------\n";
}

echo "\nFim do Diagnóstico.";
