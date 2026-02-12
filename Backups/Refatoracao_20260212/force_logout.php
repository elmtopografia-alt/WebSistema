<?php
// Arquivo: force_logout.php
// Objetivo: Limpeza TOTAL de sessão e cookies para resolver loops de login

// 1. Inicia Sessão
session_start();

// 2. Limpa Variáveis
$_SESSION = array();

// 3. Destrói Cookie de Sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Limpa Cookies Específicos do Sistema (se houver)
setcookie('elm_demo_tracker', '', time() - 3600, '/');
setcookie('PHPSESSID', '', time() - 3600, '/');

// 5. Destrói Sessão
session_destroy();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpeza Concluída</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta http-equiv="refresh" content="3;url=index.php">
</head>
<body class="bg-slate-900 text-white flex flex-col items-center justify-center min-h-screen">
    
    <div class="text-center">
        <div class="text-6xl mb-4">🧹 ✨</div>
        <h1 class="text-2xl font-bold text-green-400 mb-2">Navegador Limpo!</h1>
        <p class="text-slate-400 mb-6">Sessão, Cache e Cookies foram resetados.</p>
        
        <p class="text-sm text-slate-500">Redirecionando para o login em 3 segundos...</p>
        
        <a href="index.php" class="mt-8 inline-block bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-lg font-bold transition">
            Ir para Login Agora
        </a>
    </div>

</body>
</html>
