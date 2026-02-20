<?php
/**
 * Script de Diagnóstico para o SGT Tracer
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<body style='background:#f1f5f9; font-family:sans-serif; padding:40px;'>";
echo "<div style='background:white; padding:30px; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.1); max-width:800px; margin:auto;'>";
echo "<h1 style='color:#1e293b; border-bottom:2px solid #f97316; padding-bottom:10px;'>🔍 Diagnóstico SGT Tracer</h1>";

// Teste 1: O arquivo existe?
$tracerPath = __DIR__ . '/src/Security/Tracer.php';
echo "<h3>1. Verificação de Arquivo</h3>";
echo "<p>Caminho: <code>$tracerPath</code></p>";
if (file_exists($tracerPath)) {
    echo "<p style='color:green'><b>✓ Arquivo encontrado!</b></p>";
} else {
    echo "<p style='color:red'><b>✗ Erro: Arquivo não encontrado!</b></p>";
    echo "</div></body>";
    exit;
}

// Teste 2: Consegue fazer require?
echo "<h3>2. Teste de Inclusão (Require)</h3>";
try {
    require_once $tracerPath;
    echo "<p style='color:green'><b>✓ Require executado com sucesso!</b></p>";
} catch (Throwable $e) {
    echo "<p style='color:red'><b>✗ Erro Fatal no Require:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre style='background:#fef2f2; padding:10px; border:1px solid #fee2e2;'>" . $e->getTraceAsString() . "</pre>";
    echo "</div></body>";
    exit;
}

// Teste 3: Classe existe?
echo "<h3>3. Verificação de Classe e Namespace</h3>";
$className = '\SGT\Security\Tracer';
if (class_exists($className)) {
    echo "<p style='color:green'><b>✓ Classe $className encontrada!</b></p>";
} else {
    echo "<p style='color:red'><b>✗ Erro: Classe $className NÃO encontrada.</b></p>";
    echo "<p>Classes carregadas com 'Tracer':</p><ul>";
    foreach (get_declared_classes() as $c) {
        if (stripos($c, 'Tracer') !== false) echo "<li>$c</li>";
    }
    echo "</ul></div></body>";
    exit;
}

// Teste 4: Tentar init()
echo "<h3>4. Teste de Inicialização (init)</h3>";
try {
    if (is_callable([$className, 'init'])) {
        $className::init();
        echo "<p style='color:green'><b>✓ Tracer::init() invocado com sucesso!</b></p>";
    } else {
        echo "<p style='color:red'><b>✗ Erro: Método init() não é chamável.</b></p>";
    }
} catch (Throwable $e) {
    echo "<p style='color:red'><b>✗ Erro Fatal ao executar init():</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Arquivo: <code>" . basename($e->getFile()) . "</code> Linha: <b>" . $e->getLine() . "</b></p>";
    echo "<pre style='background:#fef2f2; padding:10px; border:1px solid #fee2e2;'>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr style='margin:30px 0; border:0; border-top:1px solid #e2e8f0;'>";
echo "<p style='text-align:center'><button onclick='location.reload()' style='background:#f97316; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer;'>Repetir Teste</button></p>";
echo "</div></body>";
