<?php
/**
 * Configuração Centralizada do Banco de Dados
 * 
 * Carrega variáveis do .env e retorna configuração estruturada
 * 
 * @package SGT_Propostas
 * @subpackage Config
 */

declare(strict_types=1);

namespace SGT\Config;

/**
 * Carrega variáveis de ambiente do arquivo .env
 */
function loadEnv(string $path = __DIR__ . '/../.env'): void
{
    if (!file_exists($path)) {
        throw new \RuntimeException(
            "Arquivo .env não encontrado em: {$path}. " .
            "Copie .env.example para .env e configure suas credenciais."
        );
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignora comentários
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove aspas se existirem
            if (preg_match('/^["\'].*["\']$/', $value)) {
                $value = substr($value, 1, -1);
            }
            
            // Define apenas se não existir
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

/**
 * Retorna configuração do banco de dados
 */
function getDatabaseConfig(): array
{
    // Garante que .env foi carregado
    if (empty($_ENV['DB_HOST'])) {
        loadEnv();
    }

    return [
        'driver'    => 'mysql',
        'host'      => $_ENV['DB_HOST'] ?? 'localhost',
        'port'      => (int)($_ENV['DB_PORT'] ?? 3306),
        'database'  => $_ENV['DB_DATABASE'] ?? 'sgt_propostas',
        'username'  => $_ENV['DB_USERNAME'] ?? 'root',
        'password'  => $_ENV['DB_PASSWORD'] ?? '',
        'charset'   => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'options'   => [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false, // Segurança: prepared statements nativos
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            \PDO::ATTR_PERSISTENT         => true    // Conexões persistentes para performance
        ]
    ];
}

/**
 * Retorna configuração de ambiente
 */
function getAppConfig(): array
{
    if (empty($_ENV['APP_ENV'])) {
        loadEnv();
    }

    return [
        'env'        => $_ENV['APP_ENV'] ?? 'production',
        'debug'      => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'url'        => $_ENV['APP_URL'] ?? '',
        'name'       => $_ENV['APP_NAME'] ?? 'SGT Propostas',
        'key'        => $_ENV['APP_KEY'] ?? '',
        'csrf_salt'  => $_ENV['CSRF_TOKEN_SALT'] ?? ''
    ];
}
