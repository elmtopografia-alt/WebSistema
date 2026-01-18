<?php
// ARQUIVO: admin_release.php
// FUNÇÃO: Painel para lançar novas versões e notificar usuários.

session_start();
require_once 'config.php';
require_once 'db.php';

// Segurança: Apenas Admin
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
    die("Acesso Negado.");
}

// Carrega PHPMailer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msg = '';

// --- PROCESSAMENTO DO LANÇAMENTO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'publicar') {
    $nova_versao = trim($_POST['versao']);
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']); // HTML permitido

    if ($nova_versao && $titulo && $descricao) {
        try {
            $conn = Database::getProd();
            
            // 1. Salva no Banco
            $stmt = $conn->prepare("INSERT INTO Versoes_Sistema (versao, titulo, descricao) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nova_versao, $titulo, $descricao);
            $stmt->execute();

            // 2. Atualiza config.php (Gambiarra Segura para mudar a constante)
            $arquivo_config = 'config.php';
            $conteudo = file_get_contents($arquivo_config);
            // Busca a linha define('SISTEMA_VERSAO', 'XX') e substitui
            $padrao = "/define\('SISTEMA_VERSAO', '.*?'\);/";
            $substituicao = "define('SISTEMA_VERSAO', '$nova_versao');";
            
            if (preg_match($padrao, $conteudo)) {
                $novo_conteudo = preg_replace($padrao, $substituicao, $conteudo);
            } else {
                // Se não achar, adiciona no final (fallback)
                $novo_conteudo = str_replace("?>", "$substituicao\n?>", $conteudo);
            }
            file_put_contents($arquivo_config, $novo_conteudo);

            // 3. Disparo de E-mails (Simples - Loop direto)
            // OBS: Para muitos usuários, isso deveria ser um Job em background.
            $usuarios = [];
            
            // Pega usuários PROD
            $resProd = $conn->query("SELECT email FROM Clientes WHERE email IS NOT NULL UNION SELECT usuario as email FROM Usuarios WHERE ambiente='producao'");
            while($u = $resProd->fetch_assoc()) { $usuarios[] = $u['email']; }
            
            // Pega usuários DEMO
            $connDemo = Database::getDemo();
            $resDemo = $connDemo->query("SELECT usuario as email FROM Usuarios WHERE ambiente='demo'");
            while($u = $resDemo->fetch_assoc()) { $usuarios[] = $u['email']; }

            $usuarios = array_unique($usuarios); // Remove duplicados
            $total_emails = count($usuarios);
            $enviados = 0;

            require_once 'GerenciadorEmail.php';
            $assunto = "🚀 Novidades no SGT: Versão $nova_versao";

            foreach ($usuarios as $destinatario) {
                if (filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
                    $corpoHTML = "
                        <div style='font-family: sans-serif; color: #333;'>
                            <h2 style='color: #0d6efd;'>$titulo</h2>
                            <p>Olá! O SGT acabou de ser atualizado para a versão <strong>$nova_versao</strong>.</p>
                            <hr>
                            $descricao
                            <hr>
                            <p>Acesse agora para conferir: <a href='" . BASE_URL . "'>" . BASE_URL . "</a></p>
                        </div>
                    ";
                    
                    if (GerenciadorEmail::enviar($destinatario, '', $assunto, $corpoHTML)) {
                        $enviados++;
                    }
                }
            }

            $msg = "<div class='alert alert-success'>
                ✅ Versão <strong>$nova_versao</strong> publicada!<br>
                📝 Banco de dados atualizado.<br>
                ⚙️ Arquivo config.php atualizado.<br>
                📧 $enviados e-mails disparados.
            </div>";

        } catch (Exception $e) {
            $msg = "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>";
        }
    } else {
        $msg = "<div class='alert alert-warning'>Preencha todos os campos.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Lançar Versão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Editor Simples (Summernote ou similar seria ideal, mas vamos de textarea) -->
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h4 class="mb-0">🚀 Lançamento de Versão</h4>
            </div>
            <div class="card-body">
                <?= $msg ?>
                
                <form method="POST">
                    <input type="hidden" name="acao" value="publicar">
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nova Versão (Ex: 01.01)</label>
                            <input type="text" name="versao" class="form-control" required placeholder="00.00">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Título da Atualização</label>
                            <input type="text" name="titulo" class="form-control" required placeholder="Ex: Novo Painel Financeiro">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">O que mudou? (Aceita HTML)</label>
                        <textarea name="descricao" class="form-control" rows="10" required placeholder="<ul><li>Item 1</li><li>Item 2</li></ul>"></textarea>
                        <div class="form-text">Use tags HTML simples como &lt;ul&gt;, &lt;li&gt;, &lt;b&gt; para formatar.</div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Atenção:</strong> Ao clicar em Publicar, e-mails serão enviados para TODOS os usuários e a versão do sistema mudará imediatamente.
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">PUBLICAR VERSÃO AGORA</button>
                </form>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="painel.php">Voltar ao Painel</a>
        </div>
    </div>
</body>
</html>
