<?php
// Nome do Arquivo: enviar_email.php
// Função: Interface de envio de e-mail com anexo utilizando PHPMailer.

session_start();
require_once 'config.php';
require_once 'db.php';

// Tenta carregar o Composer (PHPMailer deve estar aqui)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// 1. Validação de Acesso
if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = ($_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();
$id_proposta = intval($_GET['id']);
$msg_feedback = '';

// 2. Busca Dados da Proposta e Empresa
$sql = "SELECT p.*, s.nome as nome_servico, d.Empresa as nome_empresa, d.email_comercial_padrao 
        FROM Propostas p
        LEFT JOIN Tipo_Servicos s ON p.id_servico = s.id_servico
        LEFT JOIN DadosEmpresa d ON p.id_criador = d.id_criador
        WHERE p.id_proposta = ? AND p.id_criador = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $id_proposta, $id_usuario);
$stmt->execute();
$dados = $stmt->get_result()->fetch_assoc();

if (!$dados) die("Proposta não encontrada.");

// 3. Define Caminho do Arquivo Anexo
function gerarNomeArquivo($nomeEmpresa, $numeroProposta) {
    $s = trim(explode(' ', $nomeEmpresa)[0]);
    if (function_exists('iconv')) $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
    $nomeLimpo = preg_replace('/[^a-zA-Z0-9]/', '', $s);
    
    $partes = explode('-', $numeroProposta);
    $seq = end($partes);
    $ano = (count($partes) >= 3) ? $partes[1] : date('Y');
    
    return "{$nomeLimpo}-{$ano}-{$seq}.docx";
}

$nome_arquivo = gerarNomeArquivo($dados['empresa_proponente_nome'], $dados['numero_proposta']);
$caminho_anexo = __DIR__ . '/propostas_emitidas/' . $nome_arquivo;
$arquivo_existe = file_exists($caminho_anexo);

// 4. Processamento do Envio (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'enviar') {
    
    require_once 'GerenciadorEmail.php';

    $destinatario = $_POST['destinatario_email'];
    $assunto = $_POST['assunto'];
    $mensagem = nl2br($_POST['mensagem']);
    $anexos = [];

    if ($arquivo_existe) {
        $anexos[] = $caminho_anexo;
    }

    // Dados do Usuário para Reply-To e CC
    $userEmail = $dados['email_comercial_padrao'] ?? ''; // E-mail comercial do usuário
    $userCompany = $dados['nome_empresa'] ?? 'SGT'; // Nome da empresa do usuário

    // Tenta enviar
    // enviar($to, $toName, $subject, $body, $altBody, $attachments, $replyTo, $cc, $fromName)
    if (GerenciadorEmail::enviar($destinatario, '', $assunto, $mensagem, strip_tags($mensagem), $anexos, $userEmail, $userEmail, $userCompany)) {
        $msg_feedback = "<div class='alert alert-success'>E-mail enviado com sucesso! Uma cópia foi enviada para você.</div>";
    } else {
        $msg_feedback = "<div class='alert alert-danger'>Erro ao enviar o e-mail. Verifique as configurações.</div>";
    }
}

// 5. Prepara Valores Padrão para o Formulário
$assunto_padrao = "Proposta " . $dados['numero_proposta'] . " - " . $dados['nome_empresa'];
$hora = date('H');
$saudacao = ($hora < 12) ? 'Bom dia' : (($hora < 18) ? 'Boa tarde' : 'Boa noite');
$primeiro_nome = explode(' ', trim($dados['nome_cliente_salvo']))[0];

// Gera Link para Download da Proposta
$link_proposta = "";
if ($arquivo_existe) {
    // Codifica o nome do arquivo para URL
    $arquivo_url = rawurlencode($nome_arquivo);
    $link_proposta = BASE_URL . "/propostas_emitidas/" . $arquivo_url;
}

$mensagem_padrao  = "$saudacao, $primeiro_nome.\n\n";
$mensagem_padrao .= "Conforme solicitado, segue o link para acessar a proposta para o serviço de " . ($dados['nome_servico'] ?? 'Topografia') . ".\n\n";
$mensagem_padrao .= "📄 **Acesse a Proposta aqui:**\n$link_proposta\n\n";
$mensagem_padrao .= "Estou à disposição para sanar dúvidas e negociarmos as condições.\n\n";
$mensagem_padrao .= "Atenciosamente,\n";
$mensagem_padrao .= $dados['nome_empresa'];

// Link "Mailto" (Plano B) - Agora inclui o link no corpo!
$mailto_link = "mailto:" . $dados['email_salvo'] . 
               "?subject=" . rawurlencode($assunto_padrao) . 
               "&body=" . rawurlencode($mensagem_padrao);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Enviar Proposta por E-mail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <!-- Navbar Simplificada -->
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-arrow-left me-2"></i>Voltar ao Painel</a>
            <span class="navbar-text text-white">Entrega de Proposta</span>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-envelope-paper-fill me-2"></i>Enviar Proposta: <?php echo $dados['numero_proposta']; ?>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php echo $msg_feedback; ?>

                        <?php if(!$arquivo_existe): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> <strong>Atenção:</strong> O arquivo DOCX desta proposta não foi encontrado no servidor. O e-mail será enviado sem anexo.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info d-flex align-items-center py-2">
                                <i class="bi bi-paperclip fs-4 me-3"></i>
                                <div>
                                    <strong>Anexo Identificado:</strong><br>
                                    <?php echo $nome_arquivo; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="acao" value="enviar">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Para:</label>
                                <input type="email" name="destinatario_email" class="form-control" value="<?php echo htmlspecialchars($dados['email_salvo']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Assunto:</label>
                                <input type="text" name="assunto" class="form-control" value="<?php echo htmlspecialchars($assunto_padrao); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Mensagem:</label>
                                <textarea name="mensagem" class="form-control" rows="8" required><?php echo htmlspecialchars($mensagem_padrao); ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                    <i class="bi bi-send-fill me-2"></i>ENVIAR AGORA (Servidor)
                                </button>
                                
                                <div class="text-center text-muted my-2">- OU -</div>
                                
                                <a href="<?php echo $mailto_link; ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-windows me-2"></i>Abrir no Meu Outlook / Gmail (Sem Anexo Automático)
                                </a>
                                <small class="text-muted text-center">A opção "Abrir no Outlook" preenche o texto, mas você precisará anexar o arquivo manualmente.</small>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>