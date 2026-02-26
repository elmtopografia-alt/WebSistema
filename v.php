<?php
/**
 * v.php - Visualizador Simples
 */
session_start();
$_SESSION['usuario_id'] = 1;
$_GET['id'] = 999999;

$modo = $_GET['modo'] ?? 'docx';

$clienteFake = [
    'nome' => 'Empresa Teste LTDA',
    'cnpj' => '12.345.678/0001-90',
    'endereco' => 'Av. Paulista, 1000 - Sala 502',
    'cidade' => 'São Paulo',
    'estado' => 'SP',
    'cep' => '01310-100',
    'telefone' => '(11) 98765-4321',
    'email' => 'contato@empresateste.com.br',
    'responsavel' => 'João da Silva'
];

$propostaFake = [
    'id' => 999999,
    'numero' => 'PROP-TESTE-001',
    'data_criacao' => date('Y-m-d H:i:s'),
    'data_validade' => date('Y-m-d', strtotime('+30 days')),
    'valor_total' => 15000.00,
    'status' => 'rascunho',
    'modelo_docx_id' => 1
];

$blocos = [
    'docx_bloco_0_content' => json_encode(['tipo' => 'cabecalho', 'campos' => ['titulo' => 'SIMULAÇÃO SGT']]),
    'docx_bloco_1_content' => json_encode(['tipo' => 'dados_cliente', 'campos' => ['nome_cliente' => $clienteFake['nome']]])
];

$incomingData = array_merge($propostaFake, $blocos);
$modoEdicao = true;

include 'editor_dinamico.php';
?>
