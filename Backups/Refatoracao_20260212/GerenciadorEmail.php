<?php
/**
 * GerenciadorEmail.php
 * Helper para envio de e-mails usando PHPMailer e configurações do config.php
 * Traduzido para Português
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Garante carregamento do Autoload se ainda não foi feito
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Verifica se a classe foi carregada
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    die("Erro Crítico: A biblioteca PHPMailer não foi encontrada. <br>Certifique-se de ter enviado a pasta <strong>'vendor'</strong> para o servidor.");
}

class GerenciadorEmail {

    /**
     * Envia um e-mail usando as configurações do sistema.
     *
     * @param string $destinatarioEmail E-mail do destinatário
     * @param string $destinatarioNome Nome do destinatário (opcional)
     * @param string $assunto Assunto do e-mail
     * @param string $corpoHTML Corpo do e-mail em HTML
     * @param string $altBody Corpo do e-mail em Texto Puro (opcional)
     * @param array $anexos Array de caminhos de arquivos para anexar (opcional)
     * @param string $replyToEmail E-mail para resposta (Reply-To)
     * @param string $ccEmail E-mail para cópia (CC)
     * @param string $customFromName Nome personalizado do remetente (aparece "Nome <admin...>")
     * @return bool True se enviado com sucesso, False caso contrário.
     */
    public static function enviar($destinatarioEmail, $destinatarioNome, $assunto, $corpoHTML, $altBody = '', $anexos = [], $replyToEmail = '', $ccEmail = '', $customFromName = '') {
        $mail = new PHPMailer(true);

        try {
            // Configura idioma para Português
            $mail->setLanguage('pt_br');

            // Configurações de Servidor
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Locaweb recomenda SSL na 465
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            // Remetente
            // Se tiver nome personalizado (ex: Empresa do Usuário), usa ele. Senão usa o padrão do sistema.
            $fromName = !empty($customFromName) ? $customFromName : SMTP_FROM_NAME;
            $mail->setFrom(SMTP_FROM_EMAIL, $fromName);
            
            // Return-Path
            $mail->Sender = SMTP_FROM_EMAIL; 

            // Reply-To (Responder Para)
            if (!empty($replyToEmail)) {
                $mail->addReplyTo($replyToEmail, $fromName);
            }

            // CC (Cópia)
            if (!empty($ccEmail)) {
                $mail->addCC($ccEmail);
            }

            // Destinatário
            $mail->addAddress($destinatarioEmail, $destinatarioNome);

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $corpoHTML;
            $mail->AltBody = $altBody ? $altBody : strip_tags($corpoHTML);

            // Anexos
            if (!empty($anexos)) {
                foreach ($anexos as $anexo) {
                    if (file_exists($anexo)) {
                        $mail->addAttachment($anexo);
                    }
                }
            }

            $mail->send();
            return true;

        } catch (Exception $e) {
            // Em produção, você pode querer logar o erro:
            // error_log("Erro ao enviar e-mail: {$mail->ErrorInfo}");
            return false;
        }
    }
}
