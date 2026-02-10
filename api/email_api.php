<?php
// api/email_api.php - Automação de Emails SGT CRM

header('Content-Type: application/json');
require_once '../db.php';
require_once '../session_validator.php';
require_once '../config.php'; // Para credenciais SMTP

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verifica se existe o autoload do Composer
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
} else {
    // Fallback simples se não tiver Composer (não ideal, mas funcional se classes existirem)
    // Na prática, precisa do PHPMailer instalado
}

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();
$input = json_decode(file_get_contents('php://input'), true);
$acao = $input['acao'] ?? $_GET['acao'] ?? '';

// Helper: Registrar na timeline
function registrarEmailTimeline($conn, $id_proposta, $id_cliente, $tipo, $conteudo, $id_usuario, $metadata = null) {
    if (!$id_proposta) return false;
    
    $stmt = $conn->prepare("
        INSERT INTO Historico_Interacoes 
        (id_proposta, id_cliente, tipo, conteudo, canal, id_usuario, metadata) 
        VALUES (?, ?, ?, ?, 'email', ?, ?)
    ");
    $meta_json = $metadata ? json_encode($metadata) : null;
    $stmt->bind_param("iissis", $id_proposta, $id_cliente, $tipo, $conteudo, $id_usuario, $meta_json);
    return $stmt->execute();
}

// Helper: Processar variáveis do template
function processarTemplate($template, $dados) {
    $variaveis = [
        '{nome_cliente}' => $dados['nome_cliente'] ?? '',
        '{nome_empresa}' => $dados['empresa'] ?? '',
        '{valor_proposta}' => isset($dados['valor_total']) ? 'R$ ' . number_format($dados['valor_total'], 2, ',', '.') : '',
        '{id_proposta}' => $dados['id_proposta'] ?? '',
        '{data_atual}' => date('d/m/Y'),
        '{whatsapp}' => $dados['whatsapp'] ?? '',
        '{email}' => $dados['email'] ?? ''
    ];
    
    return str_replace(array_keys($variaveis), array_values($variaveis), $template);
}

// Helper: Gerar hash único para rastreamento
function gerarHashRastreamento() {
    return hash('sha256', uniqid() . time() . rand());
}

try {
    switch ($acao) {
        // LISTAR TEMPLATES
        case 'listar_templates':
            $stmt = $conn->prepare("
                SELECT * FROM Email_Templates 
                WHERE (id_usuario = ? OR id_usuario = 0) 
                ORDER BY tipo, nome
            ");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $templates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            echo json_encode(['sucesso' => true, 'templates' => $templates]);
            break;

        // SALVAR TEMPLATE
        case 'salvar_template':
            $id_template = $input['id_template'] ?? null;
            $nome = $input['nome'] ?? '';
            $assunto = $input['assunto'] ?? '';
            $corpo = $input['corpo'] ?? '';
            $tipo = $input['tipo'] ?? 'personalizado';
            
            if (empty($nome) || empty($assunto) || empty($corpo)) {
                throw new Exception("Nome, assunto e corpo são obrigatórios");
            }
            
            if ($id_template) {
                // Update
                $stmt = $conn->prepare("
                    UPDATE Email_Templates 
                    SET nome = ?, assunto = ?, corpo = ?, tipo = ? 
                    WHERE id_template = ? AND id_usuario = ?
                ");
                $stmt->bind_param("ssssii", $nome, $assunto, $corpo, $tipo, $id_template, $id_usuario);
            } else {
                // Insert
                $stmt = $conn->prepare("
                    INSERT INTO Email_Templates (id_usuario, nome, assunto, corpo, tipo) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("issss", $id_usuario, $nome, $assunto, $corpo, $tipo);
            }
            
            $stmt->execute();
            
            echo json_encode([
                'sucesso' => true, 
                'id_template' => $id_template ?? $conn->insert_id,
                'mensagem' => 'Template salvo com sucesso'
            ]);
            break;

        // PREPARAR EMAIL (preview com dados reais)
        case 'preparar':
            $id_proposta = intval($input['id_proposta'] ?? 0);
            $id_template = intval($input['id_template'] ?? 0);
            
            // Busca dados da proposta e cliente
            $stmt = $conn->prepare("
                SELECT p.*, c.nome_cliente, c.empresa, c.email, c.whatsapp
                FROM Propostas p
                JOIN Clientes c ON p.id_cliente = c.id_cliente
                WHERE p.id_proposta = ? AND p.id_criador = ?
            ");
            $stmt->bind_param("ii", $id_proposta, $id_usuario);
            $stmt->execute();
            $dados = $stmt->get_result()->fetch_assoc();
            
            if (!$dados) {
                throw new Exception("Proposta não encontrada");
            }
            
            $resultado = [
                'dados' => $dados,
                'destinatario' => $dados['email'],
                'preview_assunto' => '',
                'preview_corpo' => ''
            ];
            
            // Se tem template, processa
            if ($id_template) {
                $stmt = $conn->prepare("SELECT * FROM Email_Templates WHERE id_template = ? AND (id_usuario = ? OR id_usuario = 0)");
                $stmt->bind_param("ii", $id_template, $id_usuario);
                $stmt->execute();
                $template = $stmt->get_result()->fetch_assoc();
                
                if ($template) {
                    $resultado['template'] = $template;
                    $resultado['preview_assunto'] = processarTemplate($template['assunto'], $dados);
                    $resultado['preview_corpo'] = processarTemplate($template['corpo'], $dados);
                }
            }
            
            echo json_encode(['sucesso' => true, 'preview' => $resultado]);
            break;

        // ENVIAR EMAIL
        case 'enviar':
            $id_proposta = intval($input['id_proposta'] ?? 0);
            $destinatario = $input['destinatario'] ?? '';
            $assunto = $input['assunto'] ?? '';
            $corpo = $input['corpo'] ?? '';
            $id_template = intval($input['id_template'] ?? 0);
            $agendar = $input['agendar'] ?? false;
            $data_agendamento = $input['data_agendamento'] ?? null;
            
            if (empty($destinatario) || empty($assunto) || empty($corpo)) {
                throw new Exception("Destinatário, assunto e corpo são obrigatórios");
            }
            
            // Busca dados da proposta para contexto
            $stmt = $conn->prepare("
                SELECT p.*, c.id_cliente, c.nome_cliente 
                FROM Propostas p
                JOIN Clientes c ON p.id_cliente = c.id_cliente
                WHERE p.id_proposta = ? AND p.id_criador = ?
            ");
            $stmt->bind_param("ii", $id_proposta, $id_usuario);
            $stmt->execute();
            $proposta = $stmt->get_result()->fetch_assoc();
            
            if (!$proposta) {
                throw new Exception("Proposta não encontrada");
            }
            
            $hash = gerarHashRastreamento();
            
            // Adiciona pixel de rastreamento (placeholder)
            // Em produção real, você precisaria de um endpoint para servir essa imagem e registrar a abertura
            // $pixel_url = BASE_URL . "/api/email_pixel.php?h=" . $hash;
            // $corpo_rastreado = $corpo . '<img src="' . $pixel_url . '" width="1" height="1" alt="" style="display:block;" />';
            $corpo_rastreado = $corpo; // Simplificado por enquanto
            
            // Se for agendamento
            if ($agendar && $data_agendamento) {
                $stmt = $conn->prepare("
                    INSERT INTO Email_Envios 
                    (id_proposta, id_cliente, id_template, id_usuario, assunto, corpo, destinatario, 
                     status, data_agendamento, hash_rastreamento) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pendente', ?, ?)
                ");
                $stmt->bind_param("iiissssss", 
                    $id_proposta, $proposta['id_cliente'], $id_template, $id_usuario,
                    $assunto, $corpo, $destinatario, $data_agendamento, $hash
                );
                $stmt->execute();
                
                $id_envio = $conn->insert_id;
                
                registrarEmailTimeline($conn, $id_proposta, $proposta['id_cliente'], 
                    'email_agendado', "Email agendado para " . date('d/m H:i', strtotime($data_agendamento)), $id_usuario,
                    ['id_envio' => $id_envio, 'assunto' => $assunto]
                );
                
                echo json_encode(['sucesso' => true, 'mensagem' => 'Email agendado com sucesso']);
                break;
            }
            
            // Envio imediato
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                throw new Exception("Biblioteca PHPMailer não encontrada.");
            }

            $mail = new PHPMailer(true);
            
            try {
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USER;
                $mail->Password = SMTP_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // ou ENCRYPTION_STARTTLS dependendo da porta
                $mail->Port = SMTP_PORT;
                $mail->CharSet = 'UTF-8';
                
                $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                $mail->addAddress($destinatario);
                $mail->isHTML(true);
                $mail->Subject = $assunto;
                $mail->Body = $corpo_rastreado;
                $mail->AltBody = strip_tags($corpo);
                
                $mail->send();
                
                // Salva no banco como enviado
                $stmt = $conn->prepare("
                    INSERT INTO Email_Envios 
                    (id_proposta, id_cliente, id_template, id_usuario, assunto, corpo, destinatario,
                     status, data_envio, hash_rastreamento) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'enviado', NOW(), ?)
                ");
                $stmt->bind_param("iiisssss",
                    $id_proposta, $proposta['id_cliente'], $id_template, $id_usuario,
                    $assunto, $corpo, $destinatario, $hash
                );
                $stmt->execute();
                $id_envio = $conn->insert_id;
                
                registrarEmailTimeline($conn, $id_proposta, $proposta['id_cliente'],
                    'email_enviado', "Email enviado: " . $assunto, $id_usuario,
                    ['id_envio' => $id_envio, 'destinatario' => $destinatario]
                );
                
                echo json_encode([
                    'sucesso' => true,
                    'mensagem' => 'Email enviado com sucesso',
                    'id_envio' => $id_envio
                ]);
                
            } catch (Exception $e) {
                // Log de erro
                $stmt = $conn->prepare("
                    INSERT INTO Email_Envios 
                    (id_proposta, id_cliente, id_template, id_usuario, assunto, corpo, destinatario,
                     status, erro_msg, hash_rastreamento) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'erro', ?, ?)
                ");
                $erro_msg = $mail->ErrorInfo;
                $stmt->bind_param("iiissssss",
                    $id_proposta, $proposta['id_cliente'], $id_template, $id_usuario,
                    $assunto, $corpo, $destinatario, $erro_msg, $hash
                );
                $stmt->execute();
                
                throw new Exception("Erro ao enviar email: " . $erro_msg);
            }
            break;

        default:
            throw new Exception("Ação inválida");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['erro' => $e->getMessage()]);
}
?>
