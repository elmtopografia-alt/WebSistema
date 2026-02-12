<?php
require_once __DIR__ . '/db.php';

class ConnectionManager {
    private static array $connections = [];
    private static string $defaultAmbiente = 'producao';
    
    public static function get(?string $ambiente = null): mysqli {
        $amb = $ambiente ?? self::detectAmbiente();
        if (!isset(self::$connections[$amb])) {
            self::$connections[$amb] = ($amb === 'demo') 
                ? Database::getDemo() 
                : Database::getProd();
        }
        return self::$connections[$amb];
    }
    
    private static function detectAmbiente(): string {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        return $_SESSION['ambiente'] ?? self::$defaultAmbiente;
    }
}

$conn = ConnectionManager::get();
