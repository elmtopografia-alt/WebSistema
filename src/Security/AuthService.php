<?php
/**
 * Serviço de Autenticação
 * 
 * Gerencia login, logout, sessões e permissões de usuários
 * 
 * @package SGT_Propostas
 * @subpackage Security
 */

declare(strict_types=1);

namespace SGT\Security;

require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/InputSanitizer.php';

use function SGT\Config\startSecureSession;
use function SGT\Config\getDatabaseConfig;

class AuthService
{
    private static ?\PDO $db = null;
    
    /**
     * Inicializa conexão com banco
     */
    private static function getDb(): \PDO
    {
        if (self::$db === null) {
            $config = getDatabaseConfig();
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            self::$db = new \PDO($dsn, $config['username'], $config['password'], $config['options']);
        }
        return self::$db;
    }
    
    /**
     * Realiza login do usuário
     */
    public static function login(string $email, string $password): array
    {
        startSecureSession();
        
        try {
            $db = self::getDb();
            $stmt = $db->prepare("
                SELECT id, nome, email, senha_hash, nivel_acesso, ativo 
                FROM usuarios 
                WHERE email = :email 
                LIMIT 1
            ");
            $stmt->execute([':email' => InputSanitizer::email($email)]);
            $user = $stmt->fetch();
            
            if (!$user) {
                self::logAttempt($email, 'user_not_found');
                return ['success' => false, 'error' => 'Credenciais inválidas'];
            }
            
            if (!$user['ativo']) {
                self::logAttempt($email, 'inactive_user');
                return ['success' => false, 'error' => 'Usuário inativo'];
            }
            
            if (!password_verify($password, $user['senha_hash'])) {
                self::logAttempt($email, 'wrong_password');
                return ['success' => false, 'error' => 'Credenciais inválidas'];
            }
            
            // Regenera sessão após login
            session_regenerate_id(true);
            
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_level'] = $user['nivel_acesso'];
            $_SESSION['login_time'] = time();
            
            // Atualiza último login
            $stmt = $db->prepare("
                UPDATE usuarios 
                SET ultimo_login = NOW(), 
                    ip_ultimo_login = :ip 
                WHERE id = :id
            ");
            $stmt->execute([
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                ':id' => $user['id']
            ]);
            
            self::logAttempt($email, 'success');
            return ['success' => true, 'user' => $user];
            
        } catch (\PDOException $e) {
            error_log("Erro login: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erro interno'];
        }
    }
    
    /**
     * Verifica se usuário está logado
     */
    public static function check(): bool
    {
        startSecureSession();
        return isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] > 0;
    }
    
    /**
     * Retorna usuário atual
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['usuario_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'level' => $_SESSION['user_level']
        ];
    }
    
    /**
     * Verifica nível de acesso
     */
    public static function hasLevel(string $level): bool
    {
        $user = self::user();
        if (!$user) return false;
        
        $levels = ['admin' => 3, 'gerente' => 2, 'usuario' => 1];
        $userLevel = $levels[$user['level']] ?? 0;
        $requiredLevel = $levels[$level] ?? 0;
        
        return $userLevel >= $requiredLevel;
    }
    
    /**
     * Realiza logout
     */
    public static function logout(): void
    {
        startSecureSession();
        
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
        
        session_destroy();
    }
    
    /**
     * Registra tentativa de login (para auditoria)
     */
    private static function logAttempt(string $email, string $status): void
    {
        try {
            $db = self::getDb();
            // Verifica se a tabela log_login existe antes de inserir
            $db->exec("CREATE TABLE IF NOT EXISTS log_login (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255),
                ip_address VARCHAR(45),
                user_agent VARCHAR(255),
                status VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB");

            $stmt = $db->prepare("
                INSERT INTO log_login 
                (email, ip_address, user_agent, status, created_at) 
                VALUES (:email, :ip, :ua, :status, NOW())
            ");
            $stmt->execute([
                ':email' => $email,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                ':status' => $status
            ]);
        } catch (\Exception $e) {
            // Silencioso - não quebra login se log falhar
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }
    
    /**
     * Middleware: exige autenticação
     */
    public static function requireAuth(): void
    {
        // [DEBUG] Bypass temporário para diagnóstico de visualização
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica apenas se existe ID na sessão
        if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
            // Se falhar, exibe erro em vez de redirecionar (para debug)
            http_response_code(401);
            die("<h1>Erro de Sessão</h1><p>Você não está logado ou a sessão expirou.</p><p>Sessão ID: " . session_id() . "</p><a href='login.php'>Fazer Login</a>");
        }
    }
    
    /**
     * Middleware: exige nível específico
     */
    public static function requireLevel(string $level): void
    {
        self::requireAuth();
        
        if (!self::hasLevel($level)) {
            http_response_code(403);
            die('Acesso negado');
        }
    }
}
