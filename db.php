<?php
/**
 * db.php
 * Gerenciador de Conexões (Versão MySQLi Nativo)
 * 
 * Correção:
 * Substitui PDO por MySQLi para garantir compatibilidade com index.php
 * e com os comandos fetch_assoc() do sistema legado.
 */

require_once __DIR__ . '/config.php';

// Ativa relatório de erros estrito do MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class Database {

    private static $connProd = null;
    private static $connDemo = null;

    /**
     * Retorna conexão MySQLi com Produção
     * @return mysqli
     */
    public static function getProd() {
        if (self::$connProd === null) {
            self::$connProd = self::connect(DB_PROD_HOST, DB_PROD_NAME, DB_PROD_USER, DB_PROD_PASS);
        }
        return self::$connProd;
    }

    /**
     * Retorna conexão MySQLi com Demo
     * @return mysqli
     */
    public static function getDemo() {
        if (self::$connDemo === null) {
            self::$connDemo = self::connect(DB_DEMO_HOST, DB_DEMO_NAME, DB_DEMO_USER, DB_DEMO_PASS);
        }
        return self::$connDemo;
    }

    private static function connect($host, $dbname, $user, $pass) {
        // Cria conexão estilo "Antigo" (MySQLi) mas de forma orientada a objetos
        $mysqli = new mysqli($host, $user, $pass, $dbname);

        // Verifica erro
        if ($mysqli->connect_error) {
            if (ENVIRONMENT === 'development') {
                die("Erro de Conexão MySQLi ({$dbname}): " . $mysqli->connect_error);
            } else {
                error_log($mysqli->connect_error);
                die("Sistema indisponível.");
            }
        }

        // Define Charset
        $mysqli->set_charset("utf8mb4");
        
        return $mysqli;
    }
}

// ==========================================================
// CAMADA DE COMPATIBILIDADE (GLOBAL VARIABLE)
// ==========================================================
// Cria a variável $conn que o index.php espera encontrar.

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$is_demo_legacy = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');

try {
    if ($is_demo_legacy) {
        $conn = Database::getDemo();
    } else {
        $conn = Database::getProd();
    }
} catch (Exception $e) {
    echo "Erro fatal no DB: " . $e->getMessage();
}
?>