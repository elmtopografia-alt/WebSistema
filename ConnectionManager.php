<?php
require_once __DIR__ . '/db.php';

class ConnectionManager {
    private static $connections = [];
    private static $defaultAmbiente = 'producao';
    
    public static function get($ambiente = null) {
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
