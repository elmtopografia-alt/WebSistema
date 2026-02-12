<?php
/**
 * VIRADA DE CHAVE - MIGRAÇÃO DEFINITIVA
 * 
 * ⚠️  AVISO: Execute apenas após testar todos os .NOVO.php
 * ⚠️  FAÇA BACKUP DO BANCO ANTES!
 * 
 * Acesso: seu-site.com/executar_virada_chave.php?chave=sgt_migrar_f5_2026&confirmar=sim
 */

declare(strict_types=1);

$token = $_GET['chave'] ?? '';
$confirmar = ($_GET['confirmar'] ?? '') === 'sim';
$tokenEsperado = 'sgt_migrar_f5_2026'; // TOKEN DE ACIONAMENTO

if ($token !== $tokenEsperado) {
    http_response_code(403);
    die('Token de segurança inválido');
}

if (!$confirmar) {
    die('Adicione &confirmar=sim na URL para confirmar a operação irreversível');
}

$log = [];
$timestamp = date('Ymd_His');

// Configuração de diretórios
$backupDir = __DIR__ . '/backups/migracao_fase5/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Função segura de backup
function backupArquivo(string $arquivo): bool {
    global $timestamp, $log, $backupDir;
    
    if (!file_exists($arquivo)) {
        $log[] = "⚠️  Arquivo original não existe (pulando backup): {$arquivo}";
        return false;
    }
    
    $nomeArquivo = basename($arquivo);
    $backup = $backupDir . $timestamp . '_' . $nomeArquivo . '.bak';
    if (copy($arquivo, $backup)) {
        $log[] = "💾 Backup criado em: backups/migracao_fase5/" . basename($backup);
        return true;
    }
    
    $log[] = "❌ Falha no backup: {$arquivo}";
    return false;
}

// Função de substituição
function substituirArquivo(string $original): bool {
    global $log;
    
    $novo = $original . '.NOVO.php';
    
    if (!file_exists($novo)) {
        $log[] = "❌ Arquivo fonte (.NOVO.php) não encontrado para: {$original}";
        return false;
    }
    
    // Substituição direta
    if (copy($novo, $original)) {
        $log[] = "✅ Substituição concluída: {$original}";
        return true;
    }
    
    $log[] = "❌ Falha crítica ao substituir: {$original}";
    return false;
}

// === EXECUÇÃO DA MIGRAÇÃO ===

$arquivosParaMigrar = [
    'login.php',
    'salvar_proposta.php'
];

echo "<h1>🚀 EXECUÇÃO: VIRADA DE CHAVE SGT</h1>";
echo "<pre>";

foreach ($arquivosParaMigrar as $arquivo) {
    echo "\n--- Processando: {$arquivo} ---\n";
    
    // 1. Backup do original
    backupArquivo($arquivo);
    
    // 2. Substituição pelo novo seguro
    substituirArquivo($arquivo);
}

echo "\n\n" . implode("\n", $log);
echo "</pre>";

echo "<hr><h2>🔍 Verificação de Integridade</h2>";
echo "<ul>";
echo "<li>Login Principal: <a href='login.php' target='_blank'>Abrir Login</a></li>";
echo "<li>Validação de Sistema: " . (file_exists('bootstrap.php') ? '✅ OK' : '❌ ERRO: bootstrap.php ausente') . "</li>";
echo "</ul>";

echo "<hr><h2>🚨 Problemas?</h2>";
echo "<p>Use: <code>reverter_migracao.php?chave=sgt_revert_f5_2026</code></p>";
