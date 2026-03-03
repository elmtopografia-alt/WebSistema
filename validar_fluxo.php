<?php
// Salve este arquivo na raiz como: C:\xampp\htdocs\SistemaSaaS\testar_fluxo.php

declare(strict_types=1);

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Ajuste o caminho do bootstrap se o seu config ficar em outra pasta. O padrão é este:
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/PropostaRepository.php';
require_once __DIR__ . '/ConnectionManager.php';

echo "<!DOCTYPE html><html lang='pt-BR'><head><meta charset='UTF-8'><title>Diagnóstico SGT: DB Link</title>";
echo "<style>
    body { font-family: sans-serif; padding: 20px; line-height: 1.6; max-width: 800px; margin: 0 auto; } 
    .success { color: green; font-weight: bold; } 
    .error { color: red; font-weight: bold; }
    .box { border: 1px solid #ccc; padding: 15px; margin-top: 20px; border-radius: 5px; background: #f9f9f9; }
    .btn { display: inline-block; padding: 10px 15px; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; border: none; cursor: pointer; }
    .btn-blue { background: #007bff; }
    .btn-green { background: #28a745; }
    .btn-red { background: #dc3545; }
    table { width: 100%; border-collapse: collapse; margin-top:10px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style></head><body>";

echo "<h1>Diagnóstico de Banco: SGT Propostas</h1><hr>";

try {
    // 1. Conecta
    $conn = ConnectionManager::get();
    echo "<ul><li>Conexão com Banco: <span class='success'>OK</span></li>";

    // 2. Conta propostas antes
    $resAntes = $conn->query("SELECT COUNT(*) as total FROM Propostas");
    $totalAntes = $resAntes->fetch_assoc()['total'];
    echo "<li>Total de propostas existentes: <strong>{$totalAntes}</strong></li>";

    // Ação: Se foi pedido para deletar a prop_teste
    if (isset($_POST['delete_test']) && isset($_POST['id_to_delete'])) {
        $idDel = intval($_POST['id_to_delete']);
        $conn->query("DELETE FROM Propostas WHERE id_proposta = {$idDel}");
        echo "<li><span class='success'>Proposta de teste ID {$idDel} removida.</span></li></ul>";
        $totalAntes--;
        echo "<script>setTimeout(() => window.location.href='validar_fluxo.php', 2000);</script>";
        exit;
    }

    // 3. Deleta lixo se tiver nomeado TEST
    $conn->query("DELETE FROM Propostas WHERE numero_proposta = 'TEST-999'");

    // 4. Instancia Repository
    $repo = new PropostaRepository(); // Não passamos param porque seu model default ja faz \ConnectionManager::get() internamente

    echo "<li>Repository Pattern: <span class='success'>Carregado</span></li>";

    // 5. Array de Forçamento Mínimo com ID Válido
    $resCliente = $conn->query("SELECT id_cliente FROM Clientes LIMIT 1");
    $clienteRow = $resCliente->fetch_assoc();
    $idRealCliente = $clienteRow['id_cliente'] ?? 1;

    $dadosTeste = [
        'id_cliente' => $idRealCliente, 
        'nome_cliente_salvo' => 'Cliente Teste Flow',
        'valor_final_proposta' => 99999.99,
        'modelo_docx' => 'PropostaDrone',
        'cor' => 'verde',
        'is_demo' => 1,
        'empresa_proponente_nome' => 'TEST'
    ];

    // O PropostaRepository::salvar aceita array
    $novoId = $repo->salvar($dadosTeste);

    // Ajusta o numero manualmente pra TEST-999 conforme solicitado
    $conn->query("UPDATE Propostas SET numero_proposta = 'TEST-999' WHERE id_proposta = {$novoId}");

    // Conta depois
    $resDepois = $conn->query("SELECT COUNT(*) as total FROM Propostas");
    $totalDepois = $resDepois->fetch_assoc()['total'];

    echo "<li>Query Executada: <span class='success'>OK</span></li></ul>";
    
    echo "<h2>RESULTADO: <span class='success'>Proposta de teste salva com sucesso! ID Gerado: {$novoId}</span></h2>";

    echo "<div class='box'>";
    echo "<h3>Dados Inseridos (Validação):</h3>";
    $resRow = $conn->query("SELECT id_proposta, numero_proposta, nome_cliente_salvo, valor_final_proposta FROM Propostas WHERE id_proposta = {$novoId}");
    $row = $resRow->fetch_assoc();
    
    echo "<table>";
    echo "<tr><th>Campo</th><th>Valor no BD</th></tr>";
    foreach($row as $k => $v) {
        echo "<tr><td>{$k}</td><td>{$v}</td></tr>";
    }
    echo "</table>";
    echo "</div>";

    echo "<div class='box'>";
    echo "<h3>Próximos Passos (Ações Manuais):</h3>";
    echo "<a href='painel.php' class='btn btn-blue'>Verificar no Painel</a>";
    echo "<a href='criar_proposta.php' class='btn btn-green'>Ir para Wizard (Criar Proposta)</a>";
    
    echo "<form method='POST' style='display:inline-block;'>";
    echo "<input type='hidden' name='delete_test' value='1'>";
    echo "<input type='hidden' name='id_to_delete' value='{$novoId}'>";
    echo "<button type='submit' class='btn btn-red'>Excluir este Registro de Teste</button>";
    echo "</form>";
    echo "</div>";

    echo "<div class='box' style='background:#eaf2f8; border-color:#b9e1ff;'>";
    echo "<h3>Testar salvar_rascunho.php (Endpoint AJAX)</h3>";
    echo "<form action='salvar_rascunho.php' method='POST'>";
    echo "<input type='hidden' name='id_cliente' value='1'>";
    echo "<input type='hidden' name='nome_cliente' value='Teste via POST'>";
    echo "<input type='hidden' name='valor_final_proposta' value='15000'>";
    echo "<input type='hidden' name='modelo_docx' value='PropostaDrone'>";
    echo "<input type='hidden' name='cor' value='azul'>";
    echo "<button type='submit' class='btn btn-blue'>Testar Endpoint via POST Direto</button>";
    echo "</form>";
    echo "</div>";

} catch (\Exception $e) {
    echo "<h2>RESULTADO: <span class='error'>FALHA CRÍTICA</span></h2>";
    echo "<p>Ocorreu um erro ao tentar salvar diretamente via MySQL. Isso significa que o seu salvar_rascunho.php também falhará.</p>";
    echo "<pre style='background: #ffe6e6; padding: 15px; border: 1px solid red; overflow-x: auto;'>" . $e->getMessage() . "</pre>";
    echo "<p><strong>Trace:</strong><br>" . nl2br($e->getTraceAsString()) . "</p>";
}

echo "</body></html>";
?>
