<?php
/**
 * checar_colunas_banco.php
 * Verifica se as colunas solicitadas existem nas tabelas Propostas de SGT_Prod e SGT_Demo
 */

require_once 'db.php';

function checarColunas($conn, $dbName) {
    $colunasAlvo = ['acesso_local', 'cobertura_vegetal', 'tipo_terreno', 'restricoes_aereas'];
    echo "<h3>Verificando Banco: $dbName</h3>";
    echo "<ul>";
    
    // Lista todas as colunas
    $res = $conn->query("SHOW COLUMNS FROM Propostas");
    if (!$res) {
        echo "<li style='color:red'>Erro ao ler tabela Propostas: " . $conn->error . "</li>";
        return;
    }

    $colunasExistentes = [];
    while ($row = $res->fetch_assoc()) {
        $colunasExistentes[] = strtolower($row['Field']);
    }

    foreach ($colunasAlvo as $col) {
        if (in_array(strtolower($col), $colunasExistentes)) {
            echo "<li style='color:green'>✔ Coluna <strong>$col</strong> encontrada.</li>";
        } else {
            echo "<li style='color:red'>✘ Coluna <strong>$col</strong> NÃO encontrada!</li>";
            
            // Tenta criar se não existir (Opcional - mas o usuário só pediu pra checar)
            // $sql = "ALTER TABLE Propostas ADD COLUMN $col TEXT NULL DEFAULT NULL";
            // $conn->query($sql);
            // echo "<li>Tentativa de criação automática...</li>";
        }
    }
    echo "</ul><hr>";
}

echo "<h1>Diagnóstico de Estrutura de Banco de Dados</h1>";

try {
    // 1. Checar Produção
    $connProd = Database::getProd();
    checarColunas($connProd, "PRODUÇÃO");

    // 2. Checar Demo
    $connDemo = Database::getDemo();
    checarColunas($connDemo, "DEMONSTRAÇÃO");

} catch (Exception $e) {
    echo "<h2>Erro Fatal: " . $e->getMessage() . "</h2>";
}
?>
