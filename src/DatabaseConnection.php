<?php
/**
 * Conexão com Banco de Dados - Versão Segura
 * 
 * Usa configuração centralizada e prepared statements (PDO)
 * 
 * @package SGT_Propostas
 */

declare(strict_types=1);

namespace SGT;

require_once __DIR__ . '/../config/database.php';

use function SGT\Config\getDatabaseConfig;

class DatabaseConnection
{
    private static ?\PDO $instance = null;
    
    /**
     * Retorna conexão PDO singleton
     */
    public static function getConnection(): \PDO
    {
        if (self::$instance === null) {
            try {
                $config = getDatabaseConfig();
                $dsn = sprintf(
                    "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                    $config['host'],
                    $config['port'],
                    $config['database'],
                    $config['charset']
                );
                
                self::$instance = new \PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
                
            } catch (\PDOException $e) {
                error_log("Erro de conexão: " . $e->getMessage());
                throw new \Exception("Erro ao conectar ao banco de dados");
            }
        }
        
        return self::$instance;
    }
    
    /**
     * Executa query com prepared statement
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Busca único registro
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Busca múltiplos registros
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Insert e retorna ID
     */
    public static function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        self::query($sql, $data);
        
        return (int) self::getConnection()->lastInsertId();
    }
    
    /**
     * Update
     */
    public static function update(string $table, array $data, string $where, array $whereParams): int
    {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $setStr = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$setStr} WHERE {$where}";
        $stmt = self::query($sql, array_merge($data, $whereParams));
        
        return $stmt->rowCount();
    }
    
    /**
     * Inicia transação
     */
    public static function beginTransaction(): void
    {
        self::getConnection()->beginTransaction();
    }
    
    /**
     * Commit
     */
    public static function commit(): void
    {
        self::getConnection()->commit();
    }
    
    /**
     * Rollback
     */
    public static function rollback(): void
    {
        self::getConnection()->rollBack();
    }
    
    /**
     * Previne clonagem
     */
    private function __construct() {}
    private function __clone() {}
}
