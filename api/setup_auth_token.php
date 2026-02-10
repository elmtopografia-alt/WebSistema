<?php
// Arquivo: api/setup_auth_token.php
// Função: Cria a tabela para os tokens de login mágico

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

try {
    $conn = Database::getProd();

    $sql = "CREATE TABLE IF NOT EXISTS Tokens_Acesso_Rapido (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        token VARCHAR(64) NOT NULL UNIQUE,
        expiracao DATETIME NOT NULL,
        usado BOOLEAN DEFAULT FALSE,
        INDEX idx_token (token),
        FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($conn->query($sql)) {
        echo json_encode(['sucesso' => true, 'msg' => 'Tabela Tokens_Acesso_Rapido criada/verificada com sucesso.']);
    } else {
        throw new Exception("Erro SQL: " . $conn->error);
    }

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'msg' => $e->getMessage()]);
}
?>
