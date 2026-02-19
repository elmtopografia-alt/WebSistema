<?php
header('Content-Type: text/plain');
echo "SGT FORCE DEPLOY\n";
echo "================\n";

// Conteúdo Seguro do db.php
$db_content = <<<'EOD'
<?php
/**
 * db.php
 * Gerenciador de Conexões (Versão MySQLi Nativo)
 */

require_once __DIR__ . '/config.php';

if (!defined('DB_PROD_HOST')) {
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $env = parse_ini_file($envFile);
        if ($env) {
            foreach ($env as $k => $v) {
                if (!defined($k)) define($k, $v);
            }
        }
    }
}

if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'production');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class Database {
    private static $connProd = null;
    private static $connDemo = null;

    public static function getProd() {
        if (self::$connProd === null) {
            self::$connProd = self::connect(
                defined('DB_PROD_HOST') ? DB_PROD_HOST : '',
                defined('DB_PROD_NAME') ? DB_PROD_NAME : '',
                defined('DB_PROD_USER') ? DB_PROD_USER : '',
                defined('DB_PROD_PASS') ? DB_PROD_PASS : ''
            );
        }
        return self::$connProd;
    }

    public static function getDemo() {
        if (self::$connDemo === null) {
            self::$connDemo = self::connect(
                defined('DB_DEMO_HOST') ? DB_DEMO_HOST : '',
                defined('DB_DEMO_NAME') ? DB_DEMO_NAME : '',
                defined('DB_DEMO_USER') ? DB_DEMO_USER : '',
                defined('DB_DEMO_PASS') ? DB_DEMO_PASS : ''
            );
        }
        return self::$connDemo;
    }

    private static function connect($host, $dbname, $user, $pass) {
        if (empty($host) || empty($dbname)) {
            die("Erro Fatal: Credenciais de banco de dados não configuradas.");
        }
        try {
            $mysqli = new mysqli($host, $user, $pass, $dbname);
            if ($mysqli->connect_error) throw new Exception($mysqli->connect_error);
            $mysqli->set_charset("utf8mb4");
            return $mysqli;
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') die("Erro: " . $e->getMessage());
            else die("Sistema indisponível.");
        }
    }
}

$is_demo_legacy = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
try {
    if ($is_demo_legacy) $conn = Database::getDemo();
    else $conn = Database::getProd();
} catch (Exception $e) {
    if (ENVIRONMENT === 'development') echo "Erro: " . $e->getMessage();
    else die("Erro de infra.");
}
?>
EOD;

// Conteúdo Seguro do config.php
$config_content = <<<'EOD'
<?php
/**
 * config.php
 */

if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) exit('acesso direto negado');

if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'production');
if (!defined('SISTEMA_VERSAO')) define('SISTEMA_VERSAO', '01');

if (!function_exists('loadEnv')) {
    function loadEnv($var, $default = null) {
        $val = getenv($var);
        if ($val !== false) return $val;
        if (isset($_ENV[$var])) return $_ENV[$var];
        static $envCache = null;
        if ($envCache === null) {
            $envFile = __DIR__ . '/.env';
            if (file_exists($envFile)) {
                $content = file_get_contents($envFile);
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    if (strpos(trim($line), '#') === 0 || !strpos($line, '=')) continue;
                    list($k, $v) = explode('=', $line, 2);
                    $envCache[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
                }
            } else { $envCache = []; }
        }
        if (isset($envCache[$var])) return $envCache[$var];
        if ($default === null) die("ERRO: Variável $var não configurada");
        return $default;
    }
}

if (!defined('DB_PROD_HOST')) define('DB_PROD_HOST', loadEnv('DB_PROD_HOST'));
if (!defined('DB_PROD_NAME')) define('DB_PROD_NAME', loadEnv('DB_PROD_NAME'));
if (!defined('DB_PROD_USER')) define('DB_PROD_USER', loadEnv('DB_PROD_USER'));
if (!defined('DB_PROD_PASS')) define('DB_PROD_PASS', loadEnv('DB_PROD_PASS'));

if (!defined('DB_DEMO_HOST')) define('DB_DEMO_HOST', loadEnv('DB_DEMO_HOST'));
if (!defined('DB_DEMO_NAME')) define('DB_DEMO_NAME', loadEnv('DB_DEMO_NAME'));
if (!defined('DB_DEMO_USER')) define('DB_DEMO_USER', loadEnv('DB_DEMO_USER'));
if (!defined('DB_DEMO_PASS')) define('DB_DEMO_PASS', loadEnv('DB_DEMO_PASS'));

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$path = rtrim(dirname($script), '/\\');
define('BASE_URL', "$protocol://$host$path");

date_default_timezone_set('america/sao_paulo');
setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
mb_internal_encoding('utf-8');

require_once __DIR__ . '/src/Security/Tracer.php';
\SGT\Security\Tracer::init();
?>
EOD;

echo "Atualizando db.php... ";
if (file_put_contents('db.php', $db_content)) echo "OK\n";
else echo "FALHOU\n";

echo "Atualizando config.php... ";
if (file_put_contents('config.php', $config_content)) echo "OK\n";
else echo "FALHOU\n";

if (function_exists('opcache_reset')) {
    echo "Limpando OpCache... ";
    opcache_reset();
    echo "OK\n";
}

echo "\nFIM.";
