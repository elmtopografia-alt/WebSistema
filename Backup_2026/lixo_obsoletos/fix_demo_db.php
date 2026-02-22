<?php
/**
 * fix_demo_db.php
 * Adiciona as colunas faltantes no banco de dados DEMO
 */

require_once 'db.php';

// Ativar exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Atualização do Banco de Dados DEMO</h1>";

try {
    $conn = Database::getDemo();
    $dbName = DB_DEMO_NAME;
    
    echo "<p>Conectado ao banco: <strong>$dbName</strong></p>";
    
    $colunas = [
        'acesso_local' => "VARCHAR(255) NULL DEFAULT NULL",
        'cobertura_vegetal' => "VARCHAR(255) NULL DEFAULT NULL",
        'tipo_terreno' => "VARCHAR(255) NULL DEFAULT NULL",
        'restricoes_aereas' => "VARCHAR(255) NULL DEFAULT NULL"
    ];

    // Verificar colunas existentes
    $existingCols = [];
    $res = $conn->query("SHOW COLUMNS FROM Propostas");
    while ($row = $res->fetch_assoc()) {
        $existingCols[] = strtolower($row['Field']);
    }

    echo "<ul>";
    foreach ($colunas as $col => $def) {
        if (!in_array($col, $existingCols)) {
            $sql = "ALTER TABLE Propostas ADD COLUMN $col $def";
            if ($conn->query($sql)) {
                echo "<li style='color:green'>✔ Coluna <strong>$col</strong> adicionada com sucesso.</li>";
            } else {
                echo "<li style='color:red'>✘ Erro ao adicionar <strong>$col</strong>: " . $conn->error . "</li>";
            }
        } else {
            echo "<li style='color:blue'>ℹ Coluna <strong>$col</strong> já existe.</li>";
        }
    }
    echo "</ul>";

    echo "<h3>Verificação Final:</h3>";
    // Re-check
    $res = $conn->query("SHOW COLUMNS FROM Propostas");
    while ($row = $res->fetch_assoc()) {
        if (array_key_exists($row['Field'], $colunas)) {
            echo "{$row['Field']}: <span style='color:green'>OK</span><br>";
        }
    }
    
    echo "<hr><p>Processo concluído.</p>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>Erro Fatal: " . $e->getMessage() . "</h2>";
}
?>
