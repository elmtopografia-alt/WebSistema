<?php
// robo_exec.php
// Executor do Robô
header('Content-Type: text/html; charset=utf-8');
require 'conexao.php';
require 'engine_busca.php';

// Aumentar tempo de execução
set_time_limit(300); 

$termo = $_POST['termo'] ?? '';
$local = $_POST['local'] ?? '';
$paginas = $_POST['paginas'] ?? 1;

if (!$termo) {
    die("
    <div style='font-family: sans-serif; text-align: center; padding: 50px;'>
        <h2>⚠️ Acesso Direto Não Permitido</h2>
        <p>Você precisa lançar o robô a partir da central de comando.</p>
        <a href='robo_start.php' style='background: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir para o Início</a>
    </div>");
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Processando Missão...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Auto-scroll para acompanhar logs
        function scrollDown() { window.scrollTo(0, document.body.scrollHeight); }
        setInterval(scrollDown, 1000);
    </script>
</head>
<body class="bg-dark text-light p-4">
    <div class="container">
        <h3><span class="spinner-border spinner-border-sm text-primary"></span> Executando Robô...</h3>
        <p class="text-muted">Alvo: <strong><?= htmlspecialchars("$termo em $local") ?></strong></p>
        <hr>
        <div id="console" class="font-monospace">
<?php

flush(); // Enviar cabeçalho pro browser logo

$engine = new SearchEngine();
echo "<p>🌍 Iniciando busca no DuckDuckGo...</p>";
flush();

$resultados = $engine->buscar($termo, $local, $paginas);
$encontrados = count($resultados);

echo "<p>🔍 Encontrados $encontrados resultados brutos.</p>";
echo "<p>⚙️ Filtrando e salvando no banco...</p>";
flush();

$novos = 0;
$duplicados = 0;

foreach ($resultados as $lead) {
    // 1. Verificar Duplicidade via URL
    // Normalizar URL para busca (remover http/https e www para comparar melhor)
    $stmt = $pdo->prepare("SELECT id FROM leads_prospeccao WHERE site_origem = ?");
    $stmt->execute([$lead['link']]);
    
    if ($stmt->fetch()) {
        echo "<span class='text-secondary'>[JÁ TEM] " . htmlspecialchars($lead['link']) . "</span><br>";
        $duplicados++;
    } else {
        // 2. Inserir Novo
        try {
            $sql = "INSERT INTO leads_prospeccao (nome_empresa, site_origem, ramo_atuacao, data_captura, status_envio, metodo_captura) 
                    VALUES (?, ?, ?, NOW(), 'PENDENTE', ?)";
            $stmtInsert = $pdo->prepare($sql);
            $ramo = "Auto: $termo";
            $metodo = "Robô DDG";
            
            $stmtInsert->execute([$lead['nome'], $lead['link'], $ramo, $metodo]);
            
            echo "<span class='text-success fw-bold'>[NOVO] " . htmlspecialchars($lead['nome']) . " (" . htmlspecialchars($lead['link']) . ")</span><br>";
            $novos++;
        } catch (PDOException $e) {
            echo "<span class='text-danger'>[ERRO] " . $e->getMessage() . "</span><br>";
        }
    }
    flush(); // Atualiza tela a cada linha
}

?>
        </div>
        <hr>
        <div class="card bg-secondary text-white mt-4">
            <div class="card-body">
                <h4>Relatório Final da Missão</h4>
                <ul>
                    <li>Processados: <?= $encontrados ?></li>
                    <li>Novos Capturados: <strong><?= $novos ?></strong></li>
                    <li>Ignorados (Já existiam): <?= $duplicados ?></li>
                </ul>
                <a href="painel_prospeccao.php" class="btn btn-success btn-lg">Ver Painel de Leads</a>
                <a href="robo_start.php" class="btn btn-outline-light">Nova Busca</a>
            </div>
        </div>
    </div>
</body>
</html>
