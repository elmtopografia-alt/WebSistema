<?php
// debug_last_proposals.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'db.php';

// Check PROD Database
echo "=== PROD DATABASE ===\n";
try {
    $conn = Database::getProd();
    $sql = "SELECT id_proposta, numero_proposta, id_cliente, id_criador, status, data_criacao, data_atualizacao FROM Propostas ORDER BY id_proposta DESC LIMIT 10";
    $result = $conn->query($sql);
    if ($result) {
        $found = false;
        while ($row = $result->fetch_assoc()) {
            $found = true;
            print_r($row);
        }
        if (!$found) echo "No proposals found in PROD.\n";
    } else {
        echo "Query failed in PROD: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Connection failed in PROD: " . $e->getMessage() . "\n";
}

// Check DEMO Database
echo "\n=== DEMO DATABASE ===\n";
try {
    $conn = Database::getDemo();
    $sql = "SELECT id_proposta, numero_proposta, id_cliente, id_criador, status, data_criacao, data_atualizacao FROM Propostas ORDER BY id_proposta DESC LIMIT 10";
    $result = $conn->query($sql);
    if ($result) {
        $found = false;
        while ($row = $result->fetch_assoc()) {
            $found = true;
            print_r($row);
        }
        if (!$found) echo "No proposals found in DEMO.\n";
    } else {
        echo "Query failed in DEMO: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Connection failed in DEMO: " . $e->getMessage() . "\n";
}
?>
