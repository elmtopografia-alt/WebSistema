<?php
// api/email_api.php - Automação de Emails SGT CRM

header('Content-Type: application/json');
require_once '../db.php';
require_once '../session_validator.php';
require_once '../config.php'; // Para credenciais SMTP

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php'; // PHPMailer via Composer

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$conn = Database::getProd();
$input = json_decode(file_get_contents('php://input'), true);
$acao = $input['acao'] ?? $_GET['acao'] ?? '';

// Helper: Registrar na timeline
function registrarEmailTimeline($conn, $id_proposta, $id_cliente, $tipo, $conteudo, $id_usuario, $metadata = null) {
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
                WHERE (id_usuario = ? OR id_usuario = 0) AND ativo = 1 
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
            
            // Valida email
            if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email do destinatário inválido");
            }
            
            // Busca dados da proposta
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
            
            // Adiciona pixel de rastreamento e links trackáveis
            $pixel_url = "https://seusite.com/api/email_pixel.php?h=" . $hash;
            $corpo_rastreado = $corpo . '<img src="' . $pixel_url . '" width="1" height="1" alt="" style="display:block;" />';
            
            // Processa links para rastreamento de cliques
            $corpo_rastreado = preg_replace_callback(
                '/<a\s+href=["\']([^"\']+)["\']/i',
                function($matches) use ($hash) {
                    $url_original = urlencode($matches[1]);
                    $url_track = "https://seusite.com/api/email_click.php?h=" . $hash . "&u=" . $url_original;
                    return '<a href="' . $url_track . '"';
                },
                $corpo_rastreado
            );
            
            // Se for agendamento, salva como pendente
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
                
                // Registra na timeline
                registrarEmailTimeline($conn, $id_proposta, $proposta['id_cliente'], 
                    'email_agendado', "Email agendado para " . $data_agendamento, $id_usuario,
                    ['id_envio' => $id_envio, 'assunto' => $assunto]
                );
                
                echo json_encode([
                    'sucesso' => true,
                    'mensagem' => 'Email agendado com sucesso',
                    'id_envio' => $id_envio,
                    'data_agendamento' => $data_agendamento
                ]);
                break;
            }
            
            // Envio imediato via PHPMailer
            $mail = new PHPMailer(true);
            
            try {
                // Configurações SMTP (ajuste conforme seu servidor)
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USER;
                $mail->Password = SMTP_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = SMTP_PORT;
                
                $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
                $mail->addAddress($destinatario, $proposta['nome_cliente']);
                $mail->isHTML(true);
                $mail->Subject = $assunto;
                $mail->Body = $corpo_rastreado;
                $mail->AltBody = strip_tags($corpo);
                
                // Anexos se houver
                if (!empty($input['anexos'])) {
                    foreach ($input['anexos'] as $anexo) {
                        if (file_exists($anexo['caminho'])) {
                            $mail->addAttachment($anexo['caminho'], $anexo['nome']);
                        }
                    }
                }
                
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
                
                // Registra na timeline
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
                // Salva erro
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

        // LISTAR ENVIOS DE UMA PROPOSTA
        case 'listar_envios':
            $id_proposta = intval($_GET['id_proposta'] ?? 0);
            
            $stmt = $conn->prepare("
                SELECT e.*, t.nome as nome_template
                FROM Email_Envios e
                LEFT JOIN Email_Templates t ON e.id_template = t.id_template
                WHERE e.id_proposta = ? AND e.id_usuario = ?
                ORDER BY e.created_at DESC
            ");
            $stmt->bind_param("ii", $id_proposta, $id_usuario);
            $stmt->execute();
            $envios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            echo json_encode(['sucesso' => true, 'envios' => $envios]);
            break;

        // ESTATÍSTICAS DE EMAIL
        case 'estatisticas':
            $periodo = $_GET['periodo'] ?? 'mes';
            $intervalo = [
                'hoje' => '1 DAY',
                'semana' => '7 DAY',
                'mes' => '30 DAY'
            ][$periodo] ?? '30 DAY';
            
            $stats = [];
            
            // Total enviado
            $stmt = $conn->prepare("
                SELECT COUNT(*) as total,
                       SUM(CASE WHEN status = 'enviado' THEN 1 ELSE 0 END) as enviados,
                       SUM(CASE WHEN status = 'aberto' THEN 1 ELSE 0 END) as abertos,
                       SUM(CASE WHEN status = 'clicado' THEN 1 ELSE 0 END) as clicados,
                       SUM(CASE WHEN status = 'erro' THEN 1 ELSE 0 END) as erros
                FROM Email_Envios 
                WHERE id_usuario = ? AND created_at >= DATE_SUB(NOW(), INTERVAL {$intervalo})
            ");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stats['geral'] = $stmt->get_result()->fetch_assoc();
            
            // Taxas
            $total = $stats['geral']['total'] ?: 1;
            $stats['taxas'] = [
                'abertura' => round(($stats['geral']['abertos'] / $total) * 100, 1),
                'clique' => round(($stats['geral']['clicados'] / $total) * 100, 1),
                'erro' => round(($stats['geral']['erros'] / $total) * 100, 1)
            ];
            
            echo json_encode(['sucesso' => true, 'estatisticas' => $stats]);
            break;

        default:
            throw new Exception("Ação inválida");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['erro' => $e->getMessage()]);
}
?>