<?php
// fix_status_db.php
// Script para padronizar os status no banco de dados

require_once 'config.php';
require_once 'db.php';

// Detecta ambiente (mesma lógica do painel)
session_start();
$ambiente = $_SESSION['ambiente'] ?? 'producao';
$conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

// Função auxiliar para diagnóstico
function mostrarDiagnostico($conn, $titulo) {
    echo "\n--- $titulo ---\n";
    $sql = "SELECT status, COUNT(*) as qtd, HEX(status) as hex_val FROM Propostas GROUP BY status";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "Status: [" . $row['status'] . "] | Qtd: " . $row['qtd'] . " | Hex: " . $row['hex_val'] . "\n";
        }
    }
    echo "--------------------------------------\n";
}

mostrarDiagnostico($conn, "DIAGNÓSTICO ANTES");

// 1. Corrigir 'elaborando' -> 'Em elaboração'
$sql1 = "UPDATE Propostas SET status = 'Em elaboração' WHERE status = 'elaborando'";
if ($conn->query($sql1)) {
    echo "Corrigido 'elaborando' -> 'Em elaboração': " . $conn->affected_rows . " registros.\n";
}

// 2. Corrigir 'enviada' -> 'Enviada'
$sql2 = "UPDATE Propostas SET status = 'Enviada' WHERE status = 'enviada'";
if ($conn->query($sql2)) {
    echo "Corrigido 'enviada' -> 'Enviada': " . $conn->affected_rows . " registros.\n";
}

// 3. Corrigir 'Em elaboração' mal formatado
$sql3 = "UPDATE Propostas SET status = 'Em elaboração' WHERE status LIKE '%elabora%' AND status != 'Em elaboração'";
if ($conn->query($sql3)) {
    echo "Padronizado variações de 'elaboração': " . $conn->affected_rows . " registros.\n";
}

// 4. Corrigir 'Aceita' -> 'Aprovada'
$sql4 = "UPDATE Propostas SET status = 'Aprovada' WHERE status = 'Aceita'";
if ($conn->query($sql4)) {
    echo "Corrigido 'Aceita' -> 'Aprovada': " . $conn->affected_rows . " registros.\n";
}

mostrarDiagnostico($conn, "DIAGNÓSTICO DEPOIS");


echo "--- CONCLUIDO ---\n";
?>
