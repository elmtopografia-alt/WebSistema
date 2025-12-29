<?php
// Nome do Arquivo: reset.php
// Função: FORÇA BRUTA para limpar sessões travadas e cookies de loop.

session_start();
session_unset();
session_destroy();

// Mata o cookie da sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

echo "<h1>Sessão Limpa!</h1>";
echo "<p>O loop de redirecionamento deve ter parado.</p>";
echo "<a href='login.php'>Tentar acessar o Login novamente</a>";
?>