<?php
// conexao.php - Conexão Inteligente (Integração com config.php)

// 1. Tenta carregar as configurações do sistema
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

// 2. Detecção de Ambiente (Local vs Servidor)
// Verifica se está rodando no localhost (IPv4 ou IPv6)
$whitelist_local = ['127.0.0.1', '::1', 'localhost'];
$is_local = in_array($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', $whitelist_local);

// 3. Definição das Credenciais
if ($is_local) {
    // --- AMBIENTE LOCAL (XAMPP/Dev) ---
    $db_host = '127.0.0.1';
    $db_name = 'sistemas_web';
    $db_user = 'root';
    $db_pass = ''; // Senha padrão XAMPP vazia
} else {
    // --- AMBIENTE PRODUÇÃO (Locaweb/Server) ---
    // Usa as constantes do config.php se existirem
    if (defined('DB_PROD_HOST')) {
        $db_host = DB_PROD_HOST;
        $db_name = DB_PROD_NAME;
        $db_user = DB_PROD_USER;
        $db_pass = DB_PROD_PASS;
    } else {
        // Fallback de emergência (se config.php falhar)
        die("Erro Crítico: Configurações de banco de produção não encontradas.");
    }
}

// 4. Conexão PDO
try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 5,
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    return $pdo;

} catch (PDOException $e) {
    // Em produção, evita vazar credenciais no erro
    $erro_msg = $is_local ? $e->getMessage() : "Falha na conexão com o banco de dados. Contate o suporte.";
    die("<h1>Erro de Conexão</h1><p>$erro_msg</p>");
}
?>
