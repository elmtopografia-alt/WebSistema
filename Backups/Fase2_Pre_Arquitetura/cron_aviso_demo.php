<?php
// ARQUIVO: cron_aviso_demo.php
// FUNÇÃO: Envia e-mails de aviso para usuários DEMO prestes a expirar.
// FREQUÊNCIA: Rodar 1x por dia (ex: 08:00 AM)

require_once 'config.php';
require_once 'db.php';

// Carrega PHPMailer (se disponível via Composer)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configurações de E-mail (Idealmente viriam de um arquivo de config centralizado)
function enviarEmailAviso($destinatario, $nome, $diasRestantes, $dataExpiracao) {
    require_once 'GerenciadorEmail.php';

    $assunto = "";
    $corpo = "";

    // Assunto e Mensagem baseados nos dias restantes
    if ($diasRestantes <= 0) {
        $assunto = "⚠️ SEU ACESSO DEMO EXPIROU HOJE!";
        $corpo = "
            <h2>Olá, $nome!</h2>
            <p>Seu período de teste no SGT <strong>acabou de terminar</strong>.</p>
            <p style='color: red; font-weight: bold;'>Seus dados serão excluídos automaticamente nas próximas horas.</p>
            <p>Para manter seu acesso e todos os dados salvos, contrate agora:</p>
            <p><a href='" . BASE_URL . "/contratar.php' style='background: green; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>QUERO CONTRATAR AGORA</a></p>
        ";
    } elseif ($diasRestantes == 1) {
        $assunto = "⏳ ÚLTIMO DIA de Teste no SGT";
        $corpo = "
            <h2>Olá, $nome!</h2>
            <p>Amanhã seu acesso ao SGT será bloqueado.</p>
            <p>Você tem apenas <strong>24 horas</strong> para garantir que seus dados não sejam perdidos.</p>
            <p>Data da Exclusão: <strong>" . date('d/m/Y H:i', strtotime($dataExpiracao)) . "</strong></p>
            <p><a href='" . BASE_URL . "/contratar.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>CONTRATAR E SALVAR DADOS</a></p>
        ";
    } else { // 3 dias
        $assunto = "Faltam $diasRestantes dias para seu teste acabar";
        $corpo = "
            <h2>Olá, $nome!</h2>
            <p>Esperamos que esteja gostando do SGT.</p>
            <p>Lembramos que seu período de teste encerra em <strong>" . date('d/m/Y', strtotime($dataExpiracao)) . "</strong>.</p>
            <p>Não deixe para a última hora!</p>
            <p><a href='" . BASE_URL . "/contratar.php'>Ver Planos Disponíveis</a></p>
        ";
    }

    return GerenciadorEmail::enviar($destinatario, $nome, $assunto, $corpo);
}

// Lógica Principal
try {
    $conn = Database::getDemo();
    $hoje = new DateTime();
    
    // Busca usuários ativos no demo
    $sql = "SELECT id_usuario, usuario, nome_completo, validade_acesso FROM Usuarios WHERE ambiente = 'demo' AND validade_acesso > NOW()";
    $res = $conn->query($sql);

    echo "<h2>Verificando Avisos Demo...</h2>";

    while ($row = $res->fetch_assoc()) {
        $validade = new DateTime($row['validade_acesso']);
        $diff = $hoje->diff($validade);
        $dias = $diff->days;
        
        // Se já passou da hora (mas o script rodou antes da limpeza), dias pode ser 0 mas invertido.
        // Vamos focar nos dias inteiros positivos.
        
        if ($diff->invert == 0) {
            // Faltam X dias
            if ($dias == 3 || $dias == 1 || $dias == 0) {
                echo "Enviando aviso para {$row['usuario']} (Faltam $dias dias)... ";
                if (enviarEmailAviso($row['usuario'], $row['nome_completo'], $dias, $row['validade_acesso'])) {
                    echo "OK<br>";
                } else {
                    echo "Falha<br>";
                }
            }
        }
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
