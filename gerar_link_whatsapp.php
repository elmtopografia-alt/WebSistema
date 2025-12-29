<?php
// Nome do Arquivo: gerar_link_whatsapp.php
// Função: Busca dados da proposta, formata o telefone do cliente e redireciona para o WhatsApp com mensagem pré-definida.

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Validação de Acesso
if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = ($_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();
$id_proposta = intval($_GET['id']);

// 2. Busca Dados da Proposta e da Empresa (para a assinatura)
$sql = "SELECT p.numero_proposta, p.celular_salvo, p.whatsapp_salvo, p.nome_cliente_salvo, s.nome as nome_servico,
               d.Empresa as nome_minha_empresa
        FROM Propostas p
        LEFT JOIN Tipo_Servicos s ON p.id_servico = s.id_servico
        LEFT JOIN DadosEmpresa d ON p.id_criador = d.id_criador
        WHERE p.id_proposta = ? AND p.id_criador = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $id_proposta, $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    
    // 3. Define qual número usar (Prioridade: WhatsApp Salvo > Celular Salvo)
    $numero_bruto = !empty($row['whatsapp_salvo']) ? $row['whatsapp_salvo'] : $row['celular_salvo'];
    
    // Limpa o número (deixa apenas dígitos)
    $numero_limpo = preg_replace('/[^0-9]/', '', $numero_bruto);

    // Validação básica de número brasileiro
    if (strlen($numero_limpo) < 10) {
        die("Erro: O número de telefone cadastrado para este cliente parece inválido ou incompleto ($numero_bruto).");
    }

    // Se não tiver o código do país (55), adiciona
    if (substr($numero_limpo, 0, 2) !== '55') {
        $numero_limpo = '55' . $numero_limpo;
    }

    // 4. Monta a Mensagem
    // Saudação baseada na hora
    $hora = date('H');
    $saudacao = ($hora < 12) ? 'Bom dia' : (($hora < 18) ? 'Boa tarde' : 'Boa noite');
    $primeiro_nome = explode(' ', trim($row['nome_cliente_salvo']))[0];

    $msg  = "*$saudacao, $primeiro_nome!*\n\n";
    $msg .= "Aqui é da empresa *" . $row['nome_minha_empresa'] . "*.\n\n";
    $msg .= "Segue em anexo a proposta técnica/comercial referente ao serviço de: *" . ($row['nome_servico'] ?? 'Topografia') . "*.\n";
    $msg .= "Número da Proposta: *" . $row['numero_proposta'] . "*\n\n";
    $msg .= "Fico à disposição para esclarecer qualquer dúvida.\n\n";
    $msg .= "Att,";

    // Codifica para URL
    $texto_url = urlencode($msg);

    // 5. Redireciona para API do WhatsApp
    // Usa api.whatsapp.com que funciona tanto em Desktop quanto Mobile
    $url_final = "https://api.whatsapp.com/send?phone=$numero_limpo&text=$texto_url";

    header("Location: $url_final");
    exit;

} else {
    die("Proposta não encontrada ou acesso negado.");
}