<?php
// Arquivo: login_diag.php
// Objetivo: Diagnosticar login passo a passo (Mostra ONDE falha)

session_start();
require_once 'config.php';
require_once 'db.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['user'];
    $p = $_POST['pass'];
    
    // Ativa exibição de erros
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    try {
        $msg .= "<div class='p-4 bg-slate-800 rounded mb-4 font-mono text-xs'>";
        $msg .= "1. Buscando usuário '$u' no banco PRODUÇÃO...<br>";
        
        $conn = Database::getProd();
        $stmt = $conn->prepare("SELECT * FROM Usuarios WHERE usuario = ?");
        $stmt->bind_param('s', $u);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        
        if ($user) {
            $msg .= "2. <span class='text-green-400'>USUÁRIO ENCONTRADO!</span> (ID: {$user['id_usuario']}, Perfil: {$user['tipo_perfil']})<br>";
            $msg .= "3. Verificando Senha...<br>";
            
            if (password_verify($p, $user['senha'])) {
                $msg .= "4. <span class='text-green-400'>SENHA CORRETA (Hash Válido)!</span><br>";
                
                // Simula Logica do Index
                $msg .= "5. Simulando Lógica do Index.php:<br>";
                $ambiente = ($user['usuario'] == 'demo' || $user['tipo_perfil'] == 'demo') ? 'demo' : 'producao';
                $msg .= "   - Ambiente definido como: <strong>$ambiente</strong><br>";
                
                if ($user['tipo_perfil'] === 'admin') {
                    $msg .= "   - Perfil é Admin. Redirecionaria para <strong>painel.php</strong>.<br>";
                } else {
                    $msg .= "   - Perfil é Cliente. Redirecionaria para <strong>painel.php</strong>.<br>";
                }
                
                $msg .= "<br><strong>CONCLUSÃO:</strong> Login está funcionando tecnicamente.<br>";
                $msg .= "Se você não consegue entrar pelo site, é problema de CACHE do navegador.<br>";
                
            } else {
                $msg .= "4. <span class='text-red-400'>SENHA INCORRETA!</span><br>";
                $msg .= "   - Hash no Banco: " . substr($user['senha'], 0, 10) . "...<br>";
                $msg .= "   - Senha Digitada: $p<br>";
                if ($user['senha'] === $p) {
                     $msg .= "   - ⚠️ AVISO: A senha no banco está em TEXTO PURO (não criptografada). O sistema pode estar falhando em migrar.<br>";
                }
            }
            
        } else {
            $msg .= "2. <span class='text-red-400'>USUÁRIO NÃO ENCONTRADO</span> no banco de Produção.<br>";
            $msg .= "   - Tentando no banco DEMO...<br>";
            
            $connDemo = Database::getDemo();
            $stmtD = $connDemo->prepare("SELECT * FROM Usuarios WHERE usuario = ?");
            $stmtD->bind_param('s', $u);
            $stmtD->execute();
            $resD = $stmtD->get_result();
            $userD = $resD->fetch_assoc();
            
            if ($userD) {
                 $msg .= "   - <span class='text-yellow-400'>ACHEI NO DEMO!</span> (ID: {$userD['id_usuario']})<br>";
                 if (password_verify($p, $userD['senha'])) {
                     $msg .= "   - <span class='text-green-400'>SENHA DEMO CORRETA!</span><br>";
                 } else {
                     $msg .= "   - <span class='text-red-400'>SENHA DEMO INCORRETA!</span><br>";
                 }
            } else {
                 $msg .= "   - <span class='text-red-400'>NÃO ACHEI EM LUGAR NENHUM.</span><br>";
            }
        }
        
        $msg .= "</div>";

    } catch (Throwable $e) {
        $msg .= "<div class='p-4 bg-red-900/50 text-red-200 border border-red-500 rounded mt-4'>";
        $msg .= "<strong>ERRO FATAL:</strong> " . $e->getMessage() . "<br>";
        $msg .= "<small>" . $e->getFile() . " na linha " . $e->getLine() . "</small>";
        $msg .= "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico de Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-900 text-zinc-200 p-8 flex flex-col items-center">

    <h1 class="text-2xl font-bold mb-6">Diagnóstico de Login 🕵️‍♂️</h1>
    
    <div class="w-full max-w-md bg-black/40 p-6 rounded-xl border border-white/10">
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-zinc-500 mb-1">USUÁRIO / EMAIL</label>
                <input type="text" name="user" class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white placeholder-zinc-600" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-zinc-500 mb-1">SENHA</label>
                <input type="text" name="pass" class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white placeholder-zinc-600" required>
            </div>
            <button class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 rounded">TESTAR LOGIN</button>
        </form>
    </div>

    <div class="w-full max-w-2xl mt-8">
        <?= $msg ?>
    </div>

    <!-- Instruções Manuais -->
    <div class="mt-12 p-6 bg-amber-900/20 border border-amber-500/30 rounded-xl max-w-2xl">
        <h2 class="text-xl font-bold text-amber-500 mb-4">🧹 Limpeza MANUAL (Como você pediu)</h2>
        <ul class="list-disc pl-5 space-y-2 text-amber-200/80">
            <li><strong>Atalho Rápido:</strong> Aperte <code>CTRL</code> + <code>SHIFT</code> + <code>R</code> (Isso recarrega a página ignorando o cache).</li>
            <li><strong>Limpeza Completa (Chrome/Edge):</strong>
                <ol class="list-decimal pl-5 mt-1 space-y-1 text-sm text-zinc-400">
                    <li>Aperte <code>CTRL</code> + <code>SHIFT</code> + <code>DELETE</code></li>
                    <li>Em "Intervalo de tempo", escolha <strong>Todo o período</strong>.</li>
                    <li>Marque Apenas: <strong>Cookies e outros dados do site</strong> e <strong>Imagens e arquivos em cache</strong>.</li>
                    <li>Clique em <strong>Limpar dados</strong> (Clear data).</li>
                </ol>
            </li>
        </ul>
    </div>

</body>
</html>
