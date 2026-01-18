<?php
// ARQUIVO: api_check_novidades.php
// FUNÇÃO: Verifica se o usuário já viu a versão atual.

session_start();
require_once 'config.php';
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['tem_novidade' => false]);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$versao_atual = defined('SISTEMA_VERSAO') ? SISTEMA_VERSAO : '01';

try {
    $conn = Database::getProd();

    // POST: Marcar como lida
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'marcar_lida') {
        $stmt = $conn->prepare("INSERT IGNORE INTO Usuarios_Versoes_Vistas (id_usuario, versao) VALUES (?, ?)");
        $stmt->bind_param("is", $id_usuario, $versao_atual);
        $stmt->execute();
        echo json_encode(['sucesso' => true]);
        exit;
    }

    // GET: Verificar se tem novidade
    // 1. Busca dados da versão atual no banco
    $stmtV = $conn->prepare("SELECT titulo, descricao FROM Versoes_Sistema WHERE versao = ? LIMIT 1");
    $stmtV->bind_param("s", $versao_atual);
    $stmtV->execute();
    $resV = $stmtV->get_result();
    
    if ($resV->num_rows > 0) {
        $dadosVersao = $resV->fetch_assoc();
        
        // 2. Verifica se o usuário já viu
        $stmtCheck = $conn->prepare("SELECT 1 FROM Usuarios_Versoes_Vistas WHERE id_usuario = ? AND versao = ? LIMIT 1");
        $stmtCheck->bind_param("is", $id_usuario, $versao_atual);
        $stmtCheck->execute();
        
        if ($stmtCheck->get_result()->num_rows == 0) {
            // NÃO VIU AINDA -> MOSTRAR!
            echo json_encode([
                'tem_novidade' => true,
                'versao' => $versao_atual,
                'titulo' => $dadosVersao['titulo'],
                'descricao' => $dadosVersao['descricao']
            ]);
            exit;
        }
    }

    echo json_encode(['tem_novidade' => false]);

} catch (Exception $e) {
    echo json_encode(['tem_novidade' => false, 'erro' => $e->getMessage()]);
}
?>
