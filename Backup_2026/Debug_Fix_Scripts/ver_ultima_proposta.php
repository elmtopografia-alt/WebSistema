<?php
// 🔌 Conexão com o banco
$host     = 'demanda.mysql.dbaas.com.br';
$username = 'demanda';
$password = 'Qtamaqmde5202@';
$database = 'demanda';
$port     = '3306';

$conn = new mysqli($host, $username, $password, $database, $port);

// Verifica a conexão
if ($conn->connect_error) {
    die("❌ Falha ao conectar ao banco de dados: " . $conn->connect_error);
}

// Charset UTF-8
$conn->set_charset("utf8mb4");

// 🔎 Consulta para pegar o último registro da tabela Propostas
$sql = "SELECT * FROM Propostas ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    echo "<h2>Última proposta cadastrada:</h2>";
    echo "<table border='1' cellpadding='8' cellspacing='0'>";
    echo "<tr>";

    // Cabeçalhos da tabela (nomes das colunas)
    foreach ($row as $coluna => $valor) {
        echo "<th>" . htmlspecialchars($coluna) . "</th>";
    }
    echo "</tr><tr>";

    // Valores da tabela
    foreach ($row as $valor) {
        echo "<td>" . htmlspecialchars($valor) . "</td>";
    }
    echo "</tr>";
    echo "</table>";
} else {
    echo "⚠️ Nenhum registro encontrado na tabela Propostas.";
}

// Fecha a conexão
$conn->close();
?>