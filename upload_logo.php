<?php
// Nome do Arquivo: upload_logo.php
// Função: Processa o upload da imagem e salva o caminho na coluna 'logo_caminho'

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = ($_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();

// Pasta de destino
$diretorio = __DIR__ . '/uploads/logos/';
if (!is_dir($diretorio)) {
    mkdir($diretorio, 0755, true);
}

// Verifica se arquivo foi enviado
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    
    $fileTmpPath = $_FILES['logo']['tmp_name'];
    $fileName    = $_FILES['logo']['name'];
    $fileSize    = $_FILES['logo']['size'];
    $fileType    = $_FILES['logo']['type'];
    
    // Extensões permitidas
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    $allowedfileExtensions = array('jpg', 'jpeg', 'png');

    if (in_array($fileExtension, $allowedfileExtensions)) {
        // Gera nome único para evitar cache e sobreposição
        $newFileName = 'logo_' . $id_usuario . '_' . md5(time()) . '.' . $fileExtension;
        $dest_path = $diretorio . $newFileName;
        
        // Caminho relativo para salvar no banco
        $db_path = 'uploads/logos/' . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            
            // Remove logo antigo se existir (para não encher o servidor)
            // 1. Busca antigo
            $stmtBusca = $conn->prepare("SELECT logo_caminho FROM DadosEmpresa WHERE id_criador = ?");
            $stmtBusca->bind_param('i', $id_usuario);
            $stmtBusca->execute();
            $res = $stmtBusca->get_result();
            if ($row = $res->fetch_assoc()) {
                if (!empty($row['logo_caminho']) && file_exists(__DIR__ . '/' . $row['logo_caminho'])) {
                    @unlink(__DIR__ . '/' . $row['logo_caminho']);
                }
            }

            // 2. Atualiza Banco
            // ATENÇÃO: Certifique-se de ter rodado o ALTER TABLE para criar a coluna logo_caminho
            $stmtUpd = $conn->prepare("UPDATE DadosEmpresa SET logo_caminho = ? WHERE id_criador = ?");
            $stmtUpd->bind_param('si', $db_path, $id_usuario);
            
            if ($stmtUpd->execute()) {
                header("Location: minha_empresa.php?msg=sucesso");
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