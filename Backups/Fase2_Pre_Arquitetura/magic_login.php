<?php
// Arquivo: magic_login.php
// Função: Autentica via token e redireciona para painel.php

require_once 'config.php';
require_once 'db.php';

// Inicia sessão limpa se não existir
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$token = $_GET['t'] ?? '';

if (empty($token)) {
    die("Token inválido.");
}

try {
    $conn = Database::getProd();

    // 1. Busca Token Válido
    $stmt = $conn->prepare("SELECT t.id, t.id_usuario, u.nome_completo, u.tipo_perfil, u.validade_acesso 
                           FROM Tokens_Acesso_Rapido t
                           JOIN Usuarios u ON t.id_usuario = u.id_usuario
                           WHERE t.token = ? AND t.usado = 0 AND t.expiracao > NOW()
                           LIMIT 1");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $dados = $result->fetch_assoc();

    if ($dados) {
        // 2. Queima o Token (Replay Attack Prevention)
        $upd = $conn->prepare("UPDATE Tokens_Acesso_Rapido SET usado = 1 WHERE id = ?");
        $upd->bind_param('i', $dados['id']);
        $upd->execute();

        // 3. Verifica Validade da Conta (Segurança Adicional)
        $hoje = new DateTime();
        $val = new DateTime($dados['validade_acesso'] ? $dados['validade_acesso'] : '2000-01-01');
        
        if ($hoje > $val && $dados['tipo_perfil'] !== 'admin') {
            die("Sua assinatura venceu. Entre em contato.");
        }

        // 4. Cria a Sessão (LOGIN MÁGICO)
        session_regenerate_id(true);
        $_SESSION['usuario_id']    = $dados['id_usuario'];
        $_SESSION['usuario_nome']  = $dados['nome_completo'];
        $_SESSION['perfil']        = $dados['tipo_perfil'];
        // Mantém a lógica do index.php para ambiente
        $_SESSION['ambiente']      = ($dados['tipo_perfil'] === 'demo') ? 'demo' : 'producao'; 
        $_SESSION['origem_login']  = 'magic_qr';
        
        // 5. Redireciona para PAINEL.PHP (Conforme solicitado)
        header("Location: painel.php");
        exit;

    } else {
        // Token inválido, expirado ou já usado
        header("Location: index.php?error=link_expirado");
        exit;
    }

} catch (Exception $e) {
    die("Erro no sistema: " . $e->getMessage());
}
?>
