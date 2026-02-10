<?php
// Arquivo: debug_qr.php
require_once 'config.php';
require_once 'db.php';

// 1. Teste de Banco de Dados (Tabela Tokens)
$msgDB = "";
try {
    $conn = Database::getProd(); // Magic Login sempre usa Prod ou depende do user? O Auth Mobile usa a conexão do usuario logado.
    // Vamos checar se o usuario está logado
    session_start();
    if (!isset($_SESSION['usuario_id'])) {
        die("<h1 style='color:red'>Você precisa estar logado para testar. <a href='index.php'>Faça login</a></h1>");
    }
    
    $check = $conn->query("SHOW TABLES LIKE 'Tokens_Acesso_Rapido'");
    if ($check->num_rows > 0) {
        $msgDB = "<span style='color:green'>✅ Tabela 'Tokens_Acesso_Rapido' existe.</span>";
    } else {
        $msgDB = "<span style='color:red'>❌ Tabela 'Tokens_Acesso_Rapido' NÃO encontrada.</span>";
    }
} catch (Exception $e) {
    $msgDB = "<span style='color:red'>Erro DB: " . $e->getMessage() . "</span>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico QR Code</title>
    <style>
        body { font-family: sans-serif; background: #1a1a1a; color: #eee; text-align: center; padding: 20px; }
        .box { background: #333; padding: 20px; border-radius: 10px; display: inline-block; margin-top: 20px; max-width: 400px; width: 100%; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; font-size: 16px; cursor: pointer; border-radius: 5px; margin-bottom: 10px; }
        button:hover { background: #0056b3; }
        #log { text-align: left; background: #000; padding: 10px; font-family: monospace; font-size: 12px; margin-top: 10px; border: 1px solid #555; height: 150px; overflow-y: scroll; }
        .success { color: #4ade80; }
        .error { color: #f87171; }
    </style>
    <!-- Carrega a biblioteca QR Code -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>

    <h1>🕵️ Diagnóstico Magic Login (QR Code)</h1>
    <p>Status do Banco: <?php echo $msgDB; ?></p>

    <div class="box">
        <h3>Teste de Geração</h3>
        <button onclick="testarMagicLogin()">Gerar QR Code Agora</button>
        
        <div id="qrcode" style="background: white; padding: 10px; display: flex; justify-content: center; min-height: 100px; align-items: center; color: #333;">
            (O QR Code deve aparecer aqui)
        </div>
        
        <div id="link-area" style="margin-top:10px; font-size: 12px; word-break: break-all;"></div>
    </div>

    <div class="box">
        <h3>Log de Execução</h3>
        <div id="log">Aguardando iníco...</div>
    </div>

    <script>
        function log(msg, type = 'info') {
            const el = document.getElementById('log');
            const color = type === 'error' ? 'red' : (type === 'success' ? '#4ade80' : '#ccc');
            el.innerHTML += `<div style="color:${color}">[${new Date().toLocaleTimeString()}] ${msg}</div>`;
            el.scrollTop = el.scrollHeight; // Auto scroll
        }

        async function testarMagicLogin() {
            log("Iniciando requisição API...", 'info');
            document.getElementById('qrcode').innerHTML = "Carregando...";
            
            try {
                // 1. Testa Fetch na API
                const resp = await fetch('api/auth_mobile.php', { method: 'POST' });
                log(`Status HTTP: ${resp.status}`, resp.ok ? 'success' : 'error');
                
                // 2. Tenta ler texto cru para debug
                const text = await resp.text();
                log(`Resposta Raw: ${text.substring(0, 50)}...`, 'info');

                if (!text) throw new Error("Resposta vazia da API");

                // 3. Tenta parsear JSON
                let json;
                try {
                    json = JSON.parse(text);
                    log("JSON Parse: OK", 'success');
                } catch (e) {
                    throw new Error("Falha ao ler JSON. O servidor pode estar enviando erros PHP no meio.");
                }

                if (json.erro) throw new Error("API retornou erro: " + json.erro);
                if (!json.magic_link) throw new Error("JSON não contém 'magic_link'");

                // 4. Sucesso na API - Tenta desenhar
                log(`Link Recebido: ${json.magic_link.substring(0, 20)}...`, 'success');
                document.getElementById('link-area').innerText = json.magic_link;
                document.getElementById('qrcode').innerHTML = ""; // Limpa

                try {
                    new QRCode(document.getElementById("qrcode"), {
                        text: json.magic_link,
                        width: 128,
                        height: 128
                    });
                    log("QR Code Desenhado com Sucesso!", 'success');
                } catch (e) {
                    throw new Error("Erro na biblioteca QRCode.js: " + e.message);
                }

            } catch (err) {
                log(err.message, 'error');
                document.getElementById('qrcode').innerHTML = "<span style='color:red'>FALHA</span>";
                alert("Erro: " + err.message);
            }
        }
        
        // Verifica se a lib carregou
        window.onload = function() {
            if (typeof QRCode === 'undefined') {
                log("ATENÇÃO: Biblioteca QRCode.js NÃO CARREGOU. Verifique sua internet/CDN.", 'error');
            } else {
                log("Biblioteca QRCode pronta.", 'success');
            }
        }
    </script>
</body>
</html>
