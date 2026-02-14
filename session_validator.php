<?php
// Arquivo: session_validator.php
// Função: Centraliza a validação da sessão para proteger páginas vitais
// Autor: Sistema de Segurança

// Verificar primeiro se a sessão já foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Configura apenas se os cabeçalhos ainda não foram enviados
    if (!headers_sent()) {
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => $cookieParams['lifetime'],
            'path' => '/', 
            'domain' => $cookieParams['domain'],
            'secure' => isset($_SERVER['HTTPS']), 
            'httponly' => true, 
            'samesite' => 'Lax' 
        ]);
        session_start();
    } else {
        // Se headers já foram enviados, apenas tenta startar (vai gerar erro se não funcionar, mas evita crash)
        @session_start();
    }
}

// GERA TOKEN ANTI-CSRF COM PERSISTÊNCIA
$csrfLifetime = 3600;
$needsNewToken = empty($_SESSION['csrf_token']) 
    || empty($_SESSION['csrf_created'])
    || (time() - $_SESSION['csrf_created'] > $csrfLifetime);

// ⚠️ CORREÇÃO: Em requisições POST, NÃO regenerar o token ANTES da validação.
// Se regenerar aqui, o token do formulário (antigo) não vai bater com o novo da sessão.
// A regeneração em POST será feita DEPOIS de validarCsrf().
$isPostRequest = ($_SERVER['REQUEST_METHOD'] === 'POST');

if ($needsNewToken && !$isPostRequest) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_created'] = time();
}

function validarCsrf() {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        error_log("CSRF FAIL - Session token: " . substr($_SESSION['csrf_token'] ?? 'VAZIO', 0, 8) . "... | Received: " . substr($token, 0, 8) . "...");
        die(json_encode(['erro' => 'Token CSRF inválido']));
    }
    // ✅ Após validação bem-sucedida, regenera o token (one-time use)
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_created'] = time();
}

// 1. Verifica se a variável principal de sessão existe
if (!isset($_SESSION['usuario_id'])) {
    // Captura a URL atual para redirecionar de volta após login
    $atual = $_SERVER['REQUEST_URI'];
    // Se não houver sessão, redireciona para a página de login com o parametro redirect
    header("Location: index.php?redirect=" . urlencode($atual));
    exit;
}

// 2. Normalização de Variáveis (Compatibilidade Legado)
// Alguns scripts mais antigos podem procurar por 'id_usuario' em vez de 'usuario_id'
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['id_usuario'] = $_SESSION['usuario_id'];
}

// 3. Verifica Ambiente (Opcional, mas recomendado para garantir que 'ambiente' esteja setado)
if (!isset($_SESSION['ambiente'])) {
    $_SESSION['ambiente'] = 'producao'; // Default seguro
}
