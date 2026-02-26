<?php
/**
 * DEBUG - Verificar dados da proposta (VERSÃO RAIZ)
 * Acesse: debug_proposta.php?id=249
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/PropostaRepository.php';

$id_proposta = intval($_GET['id'] ?? 0);
if ($id_proposta <= 0) {
    die("ID inválido");
}

$repo = new PropostaRepository($conn);
$dados = $repo->buscarPorId($id_proposta);

echo "<h1>DEBUG Proposta #{$id_proposta}</h1>";
echo "<pre>";
echo "<h2>Dados da Proposta (tabela propostas):</h2>";
print_r($dados);

// Buscar cliente
if (!empty($dados['id_cliente'])) {
    $stmt = $conn->prepare("SELECT * FROM Clientes WHERE id_cliente = ?");
    $stmt->bind_param("i", $dados['id_cliente']);
    $stmt->execute();
    $cliente = $stmt->get_result()->fetch_assoc();
    echo "<h2>Dados do Cliente (tabela Clientes):</h2>";
    print_r($cliente);
}

// Buscar obra
if (!empty($dados['id_obra'])) {
    $stmt = $conn->prepare("SELECT * FROM obras WHERE id = ?");
    $stmt->bind_param("i", $dados['id_obra']);
    $stmt->execute();
    $obra = $stmt->get_result()->fetch_assoc();
    echo "<h2>Dados da Obra (tabela obras):</h2>";
    print_r($obra);
}

// Buscar empresa do criador
if (!empty($dados['id_criador'])) {
    $stmt = $conn->prepare("SELECT * FROM DadosEmpresa WHERE id_criador = ?");
    $stmt->bind_param("i", $dados['id_criador']);
    $stmt->execute();
    $empresa = $stmt->get_result()->fetch_assoc();
    echo "<h2>Dados da Empresa (tabela DadosEmpresa):</h2>";
    print_r($empresa);
}

echo "</pre>";
