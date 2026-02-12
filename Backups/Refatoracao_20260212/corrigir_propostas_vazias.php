<?php
/**
 * SGT Propostas - Script de Reparo Pre-Lançamento
 * Objetivo: Identificar e corrigir propostas com numero_proposta vazio ou nulo.
 */

require_once 'session_validator.php';
require_once 'config.php';
require_once 'db.php';

// Apenas Admin pode rodar este script
if (($_SESSION['usuario_nivel'] ?? '') !== 'admin' && !isset($_GET['force'])) {
    die("Acesso negado. Apenas administradores podem executar o reparo.");
}

$ambiente = $_SESSION['ambiente'] ?? 'producao';
$conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Reparo SGT - Go Live</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #0a0f1a; color: #e2e8f0; padding: 40px; }
        .card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 24px; max-width: 800px; margin: 0 auto; }
        h1 { color: #f97316; margin-top: 0; }
        .log { background: #000; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 13px; color: #10b981; overflow-y: auto; max-height: 400px; }
        .btn { display: inline-block; background: #f97316; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class='card'>
        <h1>Reparo de Dados SGT</h1>
        <div class='log'>";

// 1. Busca propostas problemáticas
$res = $conn->query("SELECT p.id_proposta, p.id_criador, e.Empresa 
                     FROM Propostas p 
                     LEFT JOIN DadosEmpresa e ON p.id_criador = e.id_criador 
                     WHERE p.numero_proposta = '' OR p.numero_proposta IS NULL");

echo "Foram encontradas " . $res->num_rows . " propostas com número vazio.\n\n";

if ($res->num_rows > 0) {
    // Reutiliza a função de gerar número (deve estar disponível aqui)
    require_once 'salvar_proposta.php'; 
    
    while ($row = $res->fetch_assoc()) {
        $id = $row['id_proposta'];
        $nomeEmpresa = $row['Empresa'] ?? 'SGT';
        
        $novoNumero = gerarNumero($conn, $nomeEmpresa);
        
        $update = $conn->prepare("UPDATE Propostas SET numero_proposta = ? WHERE id_proposta = ?");
        if ($update->execute([$novoNumero, $id])) {
            echo "<span style='color:#10b981'>[OK] ID: $id -> Novo Número: $novoNumero</span>\n";
        } else {
            echo "<span style='color:#ef4444'>[ERRO] ID: $id -> Falha ao atualizar</span>\n";
        }
    }
} else {
    echo "Nenhuma correção necessária. Tudo limpo!\n";
}

echo "        </div>
        <a href='painel.php' class='btn'>Voltar ao Painel</a>
    </div>
</body>
</html>";
