<?php
// Nome do Arquivo: fix_dono.php
// Função: Script de correção única para vincular dados órfãos ao usuário atual.

session_start();
require_once 'config.php';
require_once 'db.php';

// Verifica se está logado
if (!isset($_SESSION['usuario_id'])) {
    die("Por favor, faça LOGIN primeiro para rodar este script.");
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');

echo "<h3>Ferramenta de Correção de Dados (Migração)</h3>";
echo "<p>Usuário Logado ID: <strong>$id_usuario</strong></p>";
echo "<p>Ambiente: <strong>" . ($is_demo ? 'DEMO' : 'PRODUÇÃO') . "</strong></p>";

try {
    $conn = $is_demo ? Database::getDemo() : Database::getProd();

    // 1. Atualiza Propostas
    $sqlProp = "UPDATE Propostas SET id_criador = $id_usuario WHERE id_criador IS NULL OR id_criador = 0";
    $conn->query($sqlProp);
    $afetadosProp = $conn->affected_rows;
    echo "<p>✅ Propostas recuperadas: $afetadosProp</p>";

    // 2. Atualiza Clientes
    $sqlCli = "UPDATE Clientes SET id_criador = $id_usuario WHERE id_criador IS NULL OR id_criador = 0";
    $conn->query($sqlCli);
    $afetadosCli = $conn->affected_rows;
    echo "<p>✅ Clientes recuperados: $afetadosCli</p>";
    
    // 3. Atualiza Dados da Empresa
    $sqlEmp = "UPDATE DadosEmpresa SET id_criador = $id_usuario WHERE id_criador IS NULL OR id_criador = 0";
    $conn->query($sqlEmp);
    $afetadosEmp = $conn->affected_rows;
    echo "<p>✅ Dados de Empresa vinculados: $afetadosEmp</p>";

    echo "<hr>";
    echo "<h3><a href='painel.php'>CLIQUE AQUI PARA VOLTAR AO PAINEL</a></h3>";

} catch (Exception $e) {
    die("Erro ao corrigir: " . $e->getMessage());
}
?>