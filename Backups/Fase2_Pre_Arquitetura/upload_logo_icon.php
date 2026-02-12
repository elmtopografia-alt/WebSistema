<?php
// Nome do Arquivo: upload_logo_icon.php
// Função: Processa o upload do ícone/logo compacto e salva o caminho na coluna 'logo_icon_caminho'

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

// VALIDAÇÃO CSRF
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die("Acesso Negado: Token de Segurança Inválido (CSRF).");
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = ($_SESSION['ambiente'] === 'demo');

// Demo não pode fazer upload
if ($is_demo) {
    header("Location: minha_empresa.php?erro=demo");
    exit;
}

$conn = Database::getProd();

// Pasta de destino (mesma pasta dos logos)
$diretorio = __DIR__ . '/uploads/logos/';
if (!is_dir($diretorio)) {
    mkdir($diretorio, 0755, true);
}

// Verifica se arquivo foi enviado
if (isset($_FILES['logo_icon']) && $_FILES['logo_icon']['error'] === UPLOAD_ERR_OK) {

    $fileTmpPath = $_FILES['logo_icon']['tmp_name'];
    $fileName    = $_FILES['logo_icon']['name'];
    $fileSize    = $_FILES['logo_icon']['size'];
    $fileType    = $_FILES['logo_icon']['type'];

    // Extensões permitidas
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    $allowedfileExtensions = array('jpg', 'jpeg', 'png');

    if (in_array($fileExtension, $allowedfileExtensions)) {
        // Gera nome único para o ícone
        $newFileName = 'icon_' . $id_usuario . '_' . md5(time()) . '.' . $fileExtension;
        $dest_path = $diretorio . $newFileName;

        // Caminho relativo para salvar no banco
        $db_path = 'uploads/logos/' . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {

            // Remove ícone antigo se existir
            $stmtBusca = $conn->prepare("SELECT logo_icon_caminho FROM DadosEmpresa WHERE id_criador = ?");
            $stmtBusca->bind_param('i', $id_usuario);
            $stmtBusca->execute();
            $res = $stmtBusca->get_result();
            if ($row = $res->fetch_assoc()) {
                if (!empty($row['logo_icon_caminho']) && file_exists(__DIR__ . '/' . $row['logo_icon_caminho'])) {
                    @unlink(__DIR__ . '/' . $row['logo_icon_caminho']);
                }
            }

            // Atualiza Banco com o novo ícone
            $stmtUpd = $conn->prepare("UPDATE DadosEmpresa SET logo_icon_caminho = ? WHERE id_criador = ?");
            $stmtUpd->bind_param('si', $db_path, $id_usuario);

            if ($stmtUpd->execute()) {
                header("Location: minha_empresa.php?msg=sucesso&icon=1");
                exit;
            } else {
                echo "Erro ao atualizar banco de dados: " . $conn->error;
            }
        } else {
            echo "Erro ao mover o arquivo para a pasta uploads.";
        }
    } else {
        echo "Formato de arquivo inválido. Apenas JPG e PNG.";
    }
} else {
    echo "Nenhum arquivo enviado ou erro no upload.";
}
