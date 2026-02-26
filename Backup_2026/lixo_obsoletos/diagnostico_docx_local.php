<?php
/**
 * diagnostico_docx_local.php
 * Script de diagnóstico para o ambiente XAMPP do usuário.
 */

// Desativa cache para ver resultados reais
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo "<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <title>SGT - Diagnóstico de Conversão DOCX</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0a0f1a; color: #f8fafc; padding: 40px; }
        .card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 25px; max-width: 800px; margin: 0 auto; }
        h1 { color: #f97316; margin-top: 0; }
        .status { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .ok { background: #15803d; color: white; }
        .erro { background: #b91c1c; color: white; }
        .aviso { background: #a16207; color: white; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        pre { background: #000; padding: 15px; border-radius: 8px; overflow-x: auto; color: #10b981; }
    </style>
</head>
<body>
    <div class='card'>
        <h1>🔍 Diagnóstico SGT - Conversor DOCX</h1>
        <p>Este script verifica as dependências necessárias para converter arquivos .docx em modelos PHP no seu XAMPP.</p>
        
        <table>
            <tr>
                <th>Item</th>
                <th>Status</th>
                <th>Detalhes</th>
            </tr>";

// 1. Versão do PHP
$phpVersion = PHP_VERSION;
$phpOk = version_compare($phpVersion, '7.4.0', '>=');
echo "<tr>
    <td>Versão PHP</td>
    <td><span class='status " . ($phpOk ? 'ok' : 'erro') . "'>" . ($phpOk ? 'OK' : 'ANTIGA') . "</span></td>
    <td>$phpVersion (Mínimo: 7.4.0)</td>
</tr>";

// 2. Extensões PHP
$extensions = ['mbstring', 'zip', 'mysqli', 'openssl'];
foreach ($extensions as $ext) {
    $exists = extension_loaded($ext);
    echo "<tr>
        <td>Extensão: $ext</td>
        <td><span class='status " . ($exists ? 'ok' : 'erro') . "'>" . ($exists ? 'OK' : 'FALTANDO') . "</span></td>
        <td>" . ($exists ? 'Carregada' : 'Ative no php.ini') . "</td>
    </tr>";
}

// 3. Comando Python
$pythonCmd = (stripos(PHP_OS, 'WIN') === 0) ? 'python' : 'python3';
$pythonPath = shell_exec("where $pythonCmd 2>nul") ?: shell_exec("which $pythonCmd 2>/dev/null");
echo "<tr>
    <td>Python Executável</td>
    <td><span class='status " . ($pythonPath ? 'ok' : 'erro') . "'>" . ($pythonPath ? 'OK' : 'NÃO ENCONTRADO') . "</span></td>
    <td>" . ($pythonPath ? htmlspecialchars(trim($pythonPath)) : "Instale o Python 3 e adicione ao PATH") . "</td>
</tr>";

// 4. Bibliotecas Python (Mammoth e python-docx)
if ($pythonPath) {
    $libs = ['mammoth', 'docx']; // python-docx aparece como 'docx' no import
    foreach ($libs as $lib) {
        $output = [];
        exec("$pythonCmd -c \"import $lib; print('ok')\" 2>&1", $output);
        $libOk = (isset($output[0]) && trim($output[0]) === 'ok');
        echo "<tr>
            <td>Biblioteca Python: $lib</td>
            <td><span class='status " . ($libOk ? 'ok' : 'erro') . "'>" . ($libOk ? 'OK' : 'FALTANDO') . "</span></td>
            <td>" . ($libOk ? 'Instalada' : "Rode: <code>pip install " . ($lib === 'docx' ? 'python-docx' : $lib) . "</code>") . "</td>
        </tr>";
    }
}

// 5. Permissões de Pastas
$dirs = ['uploads_temp', 'modelos_gerados'];
foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    $exists = is_dir($path);
    $writable = $exists ? is_writable($path) : false;
    echo "<tr>
        <td>Pasta: $dir</td>
        <td><span class='status " . ($writable ? 'ok' : 'erro') . "'>" . ($writable ? 'OK' : 'SEM ACESSO') . "</span></td>
        <td>" . ($exists ? ($writable ? 'Permissão de escrita OK' : 'Sem permissão de escrita') : 'Pasta não existe') . "</td>
    </tr>";
}

// 6. Variáveis de Ambiente (.env)
$envFile = __DIR__ . '/.env';
$envExists = file_exists($envFile);
echo "<tr>
    <td>Arquivo .env</td>
    <td><span class='status " . ($envExists ? 'ok' : 'aviso') . "'>" . ($envExists ? 'OK' : 'AVISO') . "</span></td>
    <td>" . ($envExists ? 'Encontrado' : 'Arquivo .env não existe (usando db.php/config.php)') . "</td>
</tr>";

echo "</table>";

// Se Python falhou, mostra o erro detalhado
if ($pythonPath) {
    echo "<h3>Saída de Teste do Conversor:</h3>";
    $script = __DIR__ . '/conversor_docx.py';
    if (file_exists($script)) {
        $cmdTest = "$pythonCmd \"$script\" --test";
        $testOut = shell_exec($cmdTest . " 2>&1");
        echo "<pre>" . htmlspecialchars($testOut ?: "Nenhuma saída do script.") . "</pre>";
    } else {
        echo "<p class='erro status'>Script conversor_docx.py não encontrado!</p>";
    }
}

echo "    <p style='margin-top:20px; font-size: 0.8em; color: #64748b;'>ID da Sessão: " . session_id() . " | IP: " . $_SERVER['REMOTE_ADDR'] . "</p>
    </div>
</body>
</html>";
