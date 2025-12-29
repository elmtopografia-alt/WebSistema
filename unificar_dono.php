<?php
// Nome do Arquivo: unificar_dono.php
// Função: Transfere a titularidade de TODAS as propostas do banco para o usuário logado.

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    die("Erro: Faça login primeiro.");
}

$meu_id = $_SESSION['usuario_id']; // Deve ser 5
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');

echo "<h2>⚙️ Painel de Unificação de Dados</h2>";
echo "<p>Usuário Destino: <strong>ID $meu_id (" . $_SESSION['usuario_nome'] . ")</strong></p>";

try {
    $conn = $is_demo ? Database::getDemo() : Database::getProd();

    // 1. Diagnóstico Inicial
    $total = $conn->query("SELECT COUNT(*) as qtd FROM Propostas")->fetch_assoc()['qtd'];
    $meus = $conn->query("SELECT COUNT(*) as qtd FROM Propostas WHERE id_criador = $meu_id")->fetch_assoc()['qtd'];
    
    echo "<p>📊 Situação Atual:</p>";
    echo "<ul>";
    echo "<li>Total de Propostas no Banco: <strong>$total</strong></li>";
    echo "<li>Pertencem a você agora: <strong>$meus</strong></li>";
    echo "<li>Pertencem a outros (Invisíveis): <strong>" . ($total - $meus) . "</strong></li>";
    echo "</ul>";

    // 2. Botão de Ação
    if (isset($_POST['executar'])) {
        // TRANSERE TUDO PARA O ID 5
        $conn->query("UPDATE Propostas SET id_criador = $meu_id");
        $conn->query("UPDATE Clientes SET id_criador = $meu_id");
        $conn->query("UPDATE DadosEmpresa SET id_criador = $meu_id");
        
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "✅ <strong>SUCESSO!</strong> Todas as $total propostas agora pertencem a você.<br>";
        echo "<a href='painel.php' style='font-weight:bold; font-size:1.2em;'>VOLTAR AO PAINEL AGORA</a>";
        echo "</div>";
    } else {
        echo "<hr>";
        echo "<form method='POST'>";
        echo "<button type='submit' name='executar' style='background: #007bff; color: white; padding: 15px 30px; border: none; font-size: 16px; cursor: pointer; border-radius: 5px;'>";
        echo "🚀 TRANSFERIR TUDO PARA MIM (CORRIGIR PAINEL)";
        echo "</button>";
        echo "</form>";
    }

} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
?>