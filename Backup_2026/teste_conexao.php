<?php
require_once 'db.php';
try {
    $conn = Database::getProd();
    echo "✅ Conexão PROD (Demanda): SUCESSO!<br>";
} catch (Exception $e) {
    echo "❌ Conexão PROD: FALHOU.<br>";
}

try {
    $conn2 = Database::getDemo();
    echo "✅ Conexão DEMO (Proposta): SUCESSO!<br>";
} catch (Exception $e) {
    echo "❌ Conexão DEMO: FALHOU.<br>";
}
?>