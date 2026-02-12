<?php
// Arquivo: qrcode_login.php
// QR Code via PHP - IMAGEM DIRETA (sem JavaScript)

require_once 'config.php';
require_once 'db.php';
require_once 'session_validator.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
if (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') {
    $conn = Database::getDemo();
} else {
    $conn = Database::getProd();
}

// Garante tabela existe
$conn->query("
    CREATE TABLE IF NOT EXISTS Tokens_Acesso_Rapido (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        token VARCHAR(64) NOT NULL UNIQUE,
        expiracao DATETIME NOT NULL,
        usado BOOLEAN DEFAULT FALSE,
        INDEX idx_token (token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// Gera novo token
$token = bin2hex(random_bytes(32));
$expiracao = date('Y-m-d H:i:s', strtotime('+5 minutes'));

// Limpa tokens antigos
$conn->query("DELETE FROM Tokens_Acesso_Rapido WHERE id_usuario = $id_usuario OR expiracao < NOW()");

// Salva novo token
$stmt = $conn->prepare("INSERT INTO Tokens_Acesso_Rapido (id_usuario, token, expiracao) VALUES (?, ?, ?)");
$stmt->bind_param('iss', $id_usuario, $token, $expiracao);
$stmt->execute();

// Monta URL do login (detecta diretório automaticamente)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']); // Ex: /Orcamento
$scriptDir = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;
$loginUrl = "$protocol://$host$scriptDir/magic_login.php?t=" . $token;

// Gera QR Code via API QRServer (funciona!)
$qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($loginUrl);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - Acesso Rápido | SGT</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a0f1a 0%, #1a2235 100%); 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #e2e8f0;
        }
        .card {
            background: rgba(17, 24, 39, 0.9);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 40px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .icon {
            width: 80px;
            height: 80px;
            background: rgba(34, 197, 94, 0.1);
            border: 2px solid rgba(34, 197, 94, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
        }
        h1 { font-size: 24px; margin-bottom: 8px; }
        .subtitle { color: #94a3b8; font-size: 14px; margin-bottom: 24px; }
        .qr-container {
            background: white;
            padding: 16px;
            border-radius: 16px;
            display: inline-block;
            margin-bottom: 24px;
        }
        .qr-container img {
            display: block;
            width: 250px;
            height: 250px;
        }
        .info { color: #64748b; font-size: 13px; margin-bottom: 20px; line-height: 1.6; }
        .info strong { color: #22c55e; }
        .link-box {
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 20px;
        }
        .link-box input {
            width: 100%;
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 11px;
            text-align: center;
            outline: none;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }
        .btn-back:hover { color: white; }
    </style>
</head>
<body>

    <div class="card">
        <div class="icon">📱</div>
        <h1>Acesso Mágico</h1>
        <p class="subtitle">Escaneie o QR Code com seu celular</p>
        
        <!-- QR CODE COMO IMAGEM (sem JavaScript!) -->
        <div class="qr-container">
            <img src="<?= htmlspecialchars($qrImageUrl) ?>" alt="QR Code" />
        </div>
        
        <div class="info">
            ⏱️ Expira em <strong>5 minutos</strong><br>
            🔒 Uso único - não compartilhe
        </div>
        
        <div class="link-box">
            <input type="text" value="<?= htmlspecialchars($loginUrl) ?>" readonly onclick="this.select()">
        </div>
        
        <a href="painel.php" class="btn-back">
            ← Voltar ao Painel
        </a>
    </div>

</body>
</html>
