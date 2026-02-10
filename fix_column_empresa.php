<?php
// fix_column_empresa.php
require_once 'config.php';
require_once 'db.php';

echo "<h2>Reparação de Banco de Dados</h2>";

try {
    $conn = Database::getProd();
    
    // Verifica se a coluna existe
    $result = $conn->query("SHOW COLUMNS FROM Propostas LIKE 'empresa_cliente_salvo'");
    
    if ($result->num_rows == 0) {
        echo "<p>Coluna <strong>empresa_cliente_salvo</strong> NÃO encontrada. Tentando adicionar...</p>";
        
        $sql = "ALTER TABLE Propostas ADD COLUMN empresa_cliente_salvo VARCHAR(255) DEFAULT NULL AFTER nome_cliente_salvo";
        
        if ($conn->query($sql)) {
            echo "<p style='color:green; font-weight:bold;'>SUCESSO: Coluna adicionada!</p>";
        } else {
            echo "<p style='color:red;'>ERRO ao adicionar: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:blue;'>A coluna já existe. Nenhuma ação necessária.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>Erro Fatal: " . $e->getMessage() . "</p>";
}

echo "<br><a href='editor_dinamico.php'>Voltar para o Editor</a>";
?>
