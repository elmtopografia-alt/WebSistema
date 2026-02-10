<?php
// Arquivo: api/auth_mobile.php
// Função: Gera um token temporário para login via QR Code

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../session_validator.php'; // Garante que quem pede está logado

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception("Sessão inválida.");
    }

    $id_usuario = $_SESSION['usuario_id'];
    $conn = Database::getProd();

    // 0. AUTO-SETUP: Garante que a tabela existe (Lazy Init)
    $sqlTable = "CREATE TABLE IF NOT EXISTS Tokens_Acesso_Rapido (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        token VARCHAR(64) NOT NULL UNIQUE,
        expiracao DATETIME NOT NULL,
        usado BOOLEAN DEFAULT FALSE,
        INDEX idx_token (token),
        FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->query($sqlTable);

    // 1. Gera Token Seguro
    $token = bin2hex(random_bytes(32)); // 64 chars

    // 2. Define Expiração (2 minutos)
    $expiracao = date('Y-m-d H:i:s', strtotime('+2 minutes'));

    // 3. Salva no Banco
    $stmt = $conn->prepare("INSERT INTO Tokens_Acesso_Rapido (id_usuario, token, expiracao) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $id_usuario, $token, $expiracao);
    
    if ($stmt->execute()) {
        // Retorna a URL completa para o QRCode
        // Detecta diretório automaticamente (subindo de /api/ para o diretório pai)
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $scriptDir = dirname(dirname($_SERVER['SCRIPT_NAME'])); // Sobe de /Orcamento/api para /Orcamento
        $scriptDir = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;
        $url = $protocol . "://" . $host . $scriptDir . "/magic_login.php?t=" . $token;
        echo json_encode(['sucesso' => true, 'url' => $url]);
    } else {
        throw new Exception("Erro ao salvar token: " . $conn->error);
    }

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'msg' => $e->getMessage()]);
}
