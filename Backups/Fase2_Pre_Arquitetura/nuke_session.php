<?php
// Arquivo: nuke_session.php
// Objetivo: Limpeza EXTREMA de sessão (PHP + JS + Headers)

// 1. Headers Anti-Cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 2. Destruição PHP
session_start();
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Força remoção de cookies conhecidos
$cookies = ['PHPSESSID', 'elm_demo_tracker', 'usuario_id', 'ambiente'];
foreach ($cookies as $c) {
    setcookie($c, '', time() - 3600, '/');
    setcookie($c, '', time() - 3600);
}

session_destroy();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nuclear Reset</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // 3. Destruição via JS (Local Storage / Session Storage)
        function nukeIt() {
            console.log("Limpando LocalStorage...");
            localStorage.clear();
            
            console.log("Limpando SessionStorage...");
            sessionStorage.clear();
            
            // Limpa cookies via JS também por garantia
            document.cookie.split(";").forEach(function(c) { 
                document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
            });

            document.getElementById('status').innerHTML = "✅ TUDO LIMPO!";
            document.getElementById('btn-login').classList.remove('hidden');
        }
    </script>
</head>
<body class="bg-red-900 text-white flex flex-col items-center justify-center min-h-screen" onload="nukeIt()">
    
    <div class="bg-black/30 p-10 rounded-xl text-center backdrop-blur-sm border border-red-500/30">
        <div class="text-6xl mb-4 animate-bounce">☢️</div>
        <h1 class="text-3xl font-bold text-red-400 mb-2">RESET NUCLEAR</h1>
        <p class="text-red-200 mb-6" id="status">Limpando resquícios...</p>
        
        <p class="text-sm text-red-300 mb-8 max-w-md">
            Limpamos Sessão PHP, Cookies do Servidor, LocalStorage, SessionStorage e Cache.
        </p>
        
        <a id="btn-login" href="index.php" class="hidden inline-block bg-white text-red-900 px-8 py-4 rounded-lg font-bold hover:bg-red-100 transition shadow-lg text-xl">
            TENTAR LOGIN AGORA
        </a>
    </div>

</body>
</html>
