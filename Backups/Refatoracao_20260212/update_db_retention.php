<?php
// Arquivo: update_db_retention.php
// Função: Atualiza o banco de dados para suportar a Política de Retenção (LGPD/GDPR)
// Execute uma vez no navegador.

require_once 'config.php';
require_once 'db.php';

echo "<h2>🛠️ Inicializando atualização de Banco de Dados...</h2>";

$conn = Database::getProd();

// 1. Adicionar coluna 'ultimo_acesso' se não existir
echo "<p>Verificando tabela <strong>Usuarios</strong>...</p>";

$check = $conn->query("SHOW COLUMNS FROM Usuarios LIKE 'ultimo_acesso'");
if ($check->num_rows == 0) {
    try {
        $conn->query("ALTER TABLE Usuarios ADD COLUMN ultimo_acesso DATETIME DEFAULT NULL AFTER validade_acesso");
        echo "<p style='color:green'>✅ Coluna <strong>ultimo_acesso</strong> criada com sucesso.</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Erro ao criar coluna: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:blue'>ℹ️ Coluna <strong>ultimo_acesso</strong> já existe.</p>";
}

echo "<h3>🎉 Atualização Concluída! Você pode apagar este arquivo.</h3>";
?>
