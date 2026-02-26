<?php
require_once __DIR__ . '/db.php'; // Define as constantes DB_...

function checkInDb($host, $user, $pass, $name, $label, $id) {
    echo "<h3>Check em $label ($name)</h3>";
    $conn = @new mysqli($host, $user, $pass, $name);
    if ($conn->connect_error) {
        echo "Erro de conexão: " . $conn->connect_error . "<br>";
        return;
    }
    $res = $conn->query("SELECT id_proposta, numero_proposta FROM Propostas WHERE id_proposta = $id");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        echo "<p style='color:green'>✅ ENCONTRADA! Numero: {$row['numero_proposta']}</p>";
    } else {
        echo "<p style='color:red'>❌ NÃO ENCONTRADA.</p>";
    }
    $conn->close();
}

$id = 239;
checkInDb(DB_PROD_HOST, DB_PROD_USER, DB_PROD_PASS, DB_PROD_NAME, "PRODUÇÃO", $id);
checkInDb(DB_DEMO_HOST, DB_DEMO_USER, DB_DEMO_PASS, DB_DEMO_NAME, "DEMO", $id);
?>
