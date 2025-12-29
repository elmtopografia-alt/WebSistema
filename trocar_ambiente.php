<?php
// ARQUIVO: trocar_ambiente.php
// FUNÇÃO: Permite ao ADMIN alternar entre Produção e Demo sem senha

session_start();
require_once 'config.php';

// 1. Segurança: Só deixa prosseguir se estiver logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Descobre o ambiente atual e define o alvo
$atual = $_SESSION['ambiente'] ?? 'producao';

if ($atual === 'producao') {
    $novo_ambiente = 'demo';
    $host = DB_DEMO_HOST; $user = DB_DEMO_USER; $pass = DB_DEMO_PASS; $name = DB_DEMO_NAME;
} else {
    $novo_ambiente = 'producao';
    $host = DB_PROD_HOST; $user = DB_PROD_USER; $pass = DB_PROD_PASS; $name = DB_PROD_NAME;
}

// 3. Conecta no Banco de Destino para pegar o ID correto do Admin
$conn_dest = new mysqli($host, $user, $pass, $name);

if ($conn_dest->connect_error) {
    die("Erro ao conectar no ambiente de destino: " . $conn_dest->connect_error);
}

// Busca o usuário 'admin' (ou o seu login atual) no outro banco
// IMPORTANTE: O nome de usuário (login) deve ser igual nos dois bancos para isso funcionar perfeitamente
$usuario_atual_nome = 'admin'; // Forçamos a busca pelo super admin padrão
// Se quiser usar o email logado: $usuario_atual_nome = $_SESSION['usuario_login_original'] ?? 'admin';

$stmt = $conn_dest->prepare("SELECT id_usuario, nome_completo, setup_concluido FROM Usuarios WHERE usuario = ? OR tipo_perfil = 'admin' LIMIT 1");
$stmt->bind_param('s', $usuario_atual_nome);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $dados = $res->fetch_assoc();
    
    // 4. ATUALIZA A SESSÃO (O Pulo do Gato)
    $_SESSION['usuario_id'] = $dados['id_usuario'];
    $_SESSION['usuario_nome'] = $dados['nome_completo']; // Nome no outro banco
    $_SESSION['setup_concluido'] = $dados['setup_concluido'];
    $_SESSION['ambiente'] = $novo_ambiente;
    
    // Remove travas de demo se estiver indo para produção
    if ($novo_ambiente === 'producao') {
        unset($_SESSION['validade_demo']);
    } else {
        // Se for para demo, dá acesso livre
        $_SESSION['validade_demo'] = date('Y-m-d H:i:s', strtotime('+30 days')); 
    }

    header("Location: index.php");
    exit;

} else {
    die("<h3>Erro de Transição</h3><p>O usuário Admin não foi encontrado no banco de dados de <strong>$novo_ambiente</strong>.</p><p>Dica: Rode o 'Reset Mestre' naquele ambiente para criar o admin.</p><a href='index.php'>Voltar</a>");
}
?>