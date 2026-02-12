<?php
// Nome do Arquivo: admin_acessar_cliente.php
// Função: Permite ao Admin logar temporariamente como um cliente para dar suporte.

session_start();
require_once 'config.php';

// 1. Segurança Máxima: Apenas o Admin real pode executar isso
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
    die("Acesso Negado. Tentativa de violação de perfil.");
}

// 2. Valida ID do alvo
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_usuarios.php");
    exit;
}

require_once 'db.php';
$conn = Database::getProd();
$id_alvo = intval($_GET['id']);

// 3. Busca dados do cliente alvo para preencher a sessão
$stmt = $conn->prepare("SELECT id_usuario, nome_completo, usuario, tipo_perfil FROM Usuarios WHERE id_usuario = ?");
$stmt->bind_param('i', $id_alvo);
$stmt->execute();
$res = $stmt->get_result();

if ($alvo = $res->fetch_assoc()) {
    
    // 4. Salva a identidade original do Admin (Para poder voltar depois)
    $_SESSION['admin_original_id'] = $_SESSION['usuario_id'];
    $_SESSION['admin_original_nome'] = $_SESSION['usuario_nome'];

    // 5. Substitui a sessão atual pela do cliente
    $_SESSION['usuario_id']    = $alvo['id_usuario'];
    $_SESSION['usuario_nome']  = $alvo['nome_completo']; // Nome do cliente
    $_SESSION['usuario_login'] = $alvo['usuario'];
    $_SESSION['perfil']        = $alvo['tipo_perfil']; // Geralmente 'cliente'
    $_SESSION['ambiente']      = 'producao'; // Força produção

    // 6. Redireciona para o painel (Agora logado como o cliente)
    header("Location: index.php");
    exit;

} else {
    die("Usuário não encontrado.");
}
