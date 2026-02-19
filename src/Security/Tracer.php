<?php

namespace SGT\Security;

/**
 * SGT Tracer - Sistema de Observabilidade e GPS de Rotas
 * Registra o caminho percorrido pelo código em cada requisição.
 */
class Tracer
{
    private static $logFile = __DIR__ . '/../../logs/sgt_routes.log';

    public static function init(): void
    {
        self::logRoute();
        
        // Registra função para capturar o "Fim da Linha"
        register_shutdown_function([self::class, 'logShutdown']);
    }

    public static function logRoute(): void
    {
        // Só executa se não for acesso a arquivos de sistema ou logs
        $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        if (strpos($uri, '.log') !== false || strpos($uri, '.js') !== false || strpos($uri, '.css') !== false) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'N/A';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'N/A';
        $userId = $_SESSION['usuario_id'] ?? 'guest';
        $ambiente = $_SESSION['ambiente'] ?? 'unknown';
        
        // Alerta de Segurança: Demo tentando acessar scripts que costumam ser de Pro
        $alerta = '';
        if ($ambiente === 'demo' && (strpos($uri, 'admin') !== false || strpos($uri, 'config.php') !== false)) {
            $alerta = ' [⚠️ SECURITY: CROSS-ENV ATTEMPT]';
        }

        // Captura o arquivo principal que iniciou a execução
        $mainFile = $_SERVER['SCRIPT_FILENAME'] ?? 'unknown';
        $mainFile = basename($mainFile);

        $logEntry = "[$timestamp] [$ip] [ENV:$ambiente] [UID:$userId] $method $uri (Root: $mainFile){$alerta}\n";
        
        // Verifica se a pasta de logs existe
        if (!is_dir(dirname(self::$logFile))) {
            @mkdir(dirname(self::$logFile), 0777, true);
        }

        @file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Registra o encerramento da rota e os arquivos incluídos (opcional para debug profundo)
     */
    public static function logShutdown(): void
    {
        // Pode ser usado via register_shutdown_function para ver o 'fim da linha'
        $includedFiles = count(get_included_files());
        $memory = round(memory_get_peak_usage() / 1024 / 1024, 2);
        
        $logEntry = "  └─ [SHUTDOWN] Arquivos: $includedFiles | Memória: {$memory}MB\n";
        @file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
    }
}
