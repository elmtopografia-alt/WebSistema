<?php
/**
 * SCRIPT DE EMERGÊNCIA - REVERTER MIGRAÇÃO
 * 
 * Se algo der errado, acesse: seu-site.com/reverter_migracao.php?chave=sgt_revert_f5_2026
 */

$token = $_GET['chave'] ?? '';
$tokenEsperado = 'sgt_revert_f5_2026'; // TOKEN DE EMERGÊNCIA

if ($token !== $tokenEsperado) {
    http_response_code(403);
    die('Acesso negado');
}

$acoes = [];
$erros = [];

// Lista de arquivos para reverter (conforme fase 4 e 5)
$arquivos = [
    'login.php',
    'salvar_proposta.php',
    'bootstrap.php',
    'index.php'
];

foreach ($arquivos as $arquivo) {
    $legado = $arquivo . '.LEGADO'; // Ajustado para o padrão que estamos usando
    $legadoComTimestamp = glob($arquivo . '.LEGADO_*');
    
    // Tenta primeiro o .LEGADO simples (Fase 1/2)
    if (file_exists($legado)) {
        if (copy($legado, $arquivo)) {
            $acoes[] = "✅ Restaurado: {$arquivo} (de .LEGADO)";
        } else {
            $erros[] = "❌ Falha ao restaurar: {$arquivo}";
        }
    } 
    // Ou tenta o mais recente com timestamp (Fase 5)
    elseif (!empty($legadoComTimestamp)) {
        rsort($legadoComTimestamp);
        $recente = $legadoComTimestamp[0];
        if (copy($recente, $arquivo)) {
            $acoes[] = "✅ Restaurado: {$arquivo} (de {$recente})";
        } else {
            $erros[] = "❌ Falha ao restaurar: {$arquivo}";
        }
    }
}

echo "<h1>REVERSÃO DE MIGRAÇÃO SGT</h1>";
echo "<pre>";
if (empty($acoes) && empty($erros)) {
    echo "Nenhum arquivo legado encontrado para restauração.";
} else {
    echo implode("\n", $acoes);
    echo "\n";
    echo implode("\n", $erros);
}
echo "</pre>";
echo "<hr>";
echo "<p>Sistema processado para reversão.</p>";
echo "<p><a href='login.php'>Tentar Login</a></p>";
