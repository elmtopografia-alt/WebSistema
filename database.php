<?php
/**
 * database.php
 * Criação da conexão PDO
 */

require_once __DIR__ . '/config.php';

try {
    // Escolha do ambiente
    $host = DB_PROD_HOST;
    $db   = DB_PROD_NAME;
    $user = DB_PROD_USER;
    $pass = DB_PROD_PASS;

    $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    exit('Erro de conexão com o banco de dados.');
}
