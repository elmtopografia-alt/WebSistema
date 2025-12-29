<?php
// Nome do Arquivo: excluir_proposta.php
// Função: Remove registro do banco e arquivo físico.

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = ($_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();
$id_proposta = intval($_GET['id']);

// Busca dados para apagar o arquivo
$stmt = $conn->prepare("SELECT numero_proposta, empresa_proponente_nome FROM Propostas WHERE id_proposta = ? AND id_criador = ?");
$stmt->bind_param('ii', $id_proposta, $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    
    // Função local para reconstruir nome do arquivo
    function gerarNomeArquivo($nomeEmpresa, $numeroProposta) {
        $s = trim(explode(' ', $nomeEmpresa)[0]);
        if (function_exists('iconv')) $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
        $nomeLimpo = preg_replace('/[^a-zA-Z0-9]/', '', $s);
        $partes = explode('-', $numeroProposta);
        $seq = end($partes);
        $ano = $partes[1];
        return "{$nomeLimpo}-{$ano}-{$seq}.docx";
    }

    $arquivo = gerarNomeArquivo($row['empresa_proponente_nome'], $row['numero_proposta']);
    $caminho = __DIR__ . '/propostas_emitidas/' . $arquivo;

    // Apaga arquivo físico
    if (file_exists($caminho)) {
        @unlink($caminho);
    }

    // Apaga do Banco (Cascade deve cuidar dos itens, mas se não, faríamos delete manual)
    // O delete na tabela pai deve limpar os filhos se a FK estiver ON DELETE CASCADE.
    // Como vimos no SQL Dump, está ON DELETE CASCADE.
    $del = $conn->prepare("DELETE FROM Propostas WHERE id_proposta = ?");
    $del->bind_param('i', $id_proposta);
    
    if ($del->execute()) {
        header("Location: index.php?msg=excluido");
    } else {
        die("Erro ao excluir: " . $conn->error);
    }

} else {
    die("Proposta não encontrada ou permissão negada.");
}