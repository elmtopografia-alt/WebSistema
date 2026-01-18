<?php
// ARQUIVO: setup_updates.php
// FUNÇÃO: Cria as tabelas necessárias para o sistema de notificações de atualização.

require_once 'config.php';
require_once 'db.php';

echo "<h2>Configurando Banco de Dados para Atualizações...</h2>";

try {
    // Conecta no Banco de Produção (onde ficam as versões oficiais)
    $conn = Database::getProd();

    // 1. Tabela de Versões
    $sql1 = "CREATE TABLE IF NOT EXISTS Versoes_Sistema (
        id_versao INT AUTO_INCREMENT PRIMARY KEY,
        versao VARCHAR(20) NOT NULL,
        titulo VARCHAR(255) NOT NULL,
        descricao TEXT NOT NULL,
        data_lancamento DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($conn->query($sql1)) {
        echo "<p>✅ Tabela 'Versoes_Sistema' verificada/criada.</p>";
    } else {
        throw new Exception("Erro ao criar Versoes_Sistema: " . $conn->error);
    }

    // 2. Tabela de Visualizações (Quem já viu o modal)
    $sql2 = "CREATE TABLE IF NOT EXISTS Usuarios_Versoes_Vistas (
        id_vista INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        versao VARCHAR(20) NOT NULL,
        data_vista DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (id_usuario, versao)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($conn->query($sql2)) {
        echo "<p>✅ Tabela 'Usuarios_Versoes_Vistas' verificada/criada.</p>";
    } else {
        throw new Exception("Erro ao criar Usuarios_Versoes_Vistas: " . $conn->error);
    }

    echo "<hr><h3 style='color:green'>Sucesso! O banco está pronto.</h3>";
    echo "<p>Agora você pode acessar <a href='admin_release.php'>admin_release.php</a> para lançar a primeira versão.</p>";

} catch (Exception $e) {
    echo "<h3 style='color:red'>Erro Fatal: " . $e->getMessage() . "</h3>";
}
?>
