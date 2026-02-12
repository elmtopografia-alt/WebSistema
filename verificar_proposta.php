<?php
require_once 'db.php';

// Determinar o ambiente (demo ou prod) com base na sessão
require_once 'session_validator.php';
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>SGT - Verificação Técnica</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #e2e8f0; padding: 20px; line-height: 1.6; }
        h2 { color: #f97316; border-bottom: 2px solid #334155; padding-bottom: 10px; margin-top: 40px; }
        table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 8px; overflow: hidden; margin: 20px 0; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #334155; }
        th { background: #334155; color: #f8fafc; font-weight: 600; }
        tr:hover { background: #334155; }
        pre { background: #000; color: #0f0; padding: 15px; border-radius: 8px; overflow-x: auto; white-space: pre-wrap; font-size: 13px; border: 1px solid #334155; }
        .ambiente-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; margin-left: 10px; }
        .badge-prod { background: #ef4444; color: white; }
        .badge-demo { background: #3b82f6; color: white; }
    </style>
</head>
<body>

<h1>Ferramenta de Verificação de Propostas 
    <span class='ambiente-badge " . ($is_demo ? 'badge-demo' : 'badge-prod') . "'>" . ($is_demo ? 'Ambiente DEMO' : 'Ambiente PROD') . "</span>
</h1>";

// Ver estrutura da tabela
echo "<h2>Estrutura da Tabela Propostas</h2>";
$result = $conn->query("DESCRIBE Propostas");
if ($result) {
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>Erro ao descrever tabela: " . $conn->error . "</p>";
}

// Ver propostas recentes
echo "<h2>Últimas 5 Propostas</h2>";
$result = $conn->query("SELECT id_proposta, numero_proposta, id_cliente, nome_cliente_salvo, status, data_criacao FROM Propostas ORDER BY id_proposta DESC LIMIT 5");
if ($result) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Número</th><th>ID Cliente</th><th>Nome Cliente</th><th>Status</th><th>Data</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id_proposta']}</td>";
        echo "<td>{$row['numero_proposta']}</td>";
        echo "<td>" . ($row['id_cliente'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['nome_cliente_salvo'] ?? 'NULL') . "</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['data_criacao']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>Erro ao buscar propostas: " . $conn->error . "</p>";
}

// Ver se há erros no log
echo "<h2>Últimas Linhas do Log de Debug</h2>";
$logfile = __DIR__ . '/debug_insert.log';
if (file_exists($logfile)) {
    $linhas = array_slice(file($logfile), -50);
    echo "<pre>";
    foreach ($linhas as $linha) {
        echo htmlspecialchars($linha);
    }
    echo "</pre>";
} else {
    echo "<p>Arquivo de log <code>debug_insert.log</code> não encontrado. Tente salvar uma proposta primeiro.</p>";
}

echo "</body></html>";
?>
