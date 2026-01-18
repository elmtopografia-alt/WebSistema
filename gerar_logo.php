<?php
// Arquivo: gerar_logo.php
// Função: Gerar imagem PNG transparente com texto simples (Logo Genérico)

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança e Validação
if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');

// Bloqueio Demo (redundante, mas seguro)
if ($is_demo) {
    echo "Ação bloqueada na demonstração.";
    exit;
}

$cor_hex = $_POST['cor'] ?? '#000000';
$modo_texto = $_POST['modo_texto'] ?? 'empresa'; // 'empresa' ou 'nome_topografia'

// 2. Buscar Dados para o Texto
$conn = Database::getProd(); // Se não é demo, é prod
$stmt = $conn->prepare("SELECT Empresa FROM DadosEmpresa WHERE id_criador = ?");
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$res = $stmt->get_result();
$empresa = $res->fetch_assoc();

$texto_final = "Minha Empresa";

if ($modo_texto === 'empresa') {
    // Usa nome da empresa ou padrão
    $texto_final = !empty($empresa['Empresa']) ? $empresa['Empresa'] : 'Minha Empresa';
} else {
    // Usa Primeiro Nome + Topografia
    $nome_user = $_SESSION['usuario_nome'] ?? 'Usuario';
    $parts = explode(' ', trim($nome_user));
    $primeiro_nome = $parts[0];
    $texto_final = $primeiro_nome . " Topografia";
}

// Limitar tamanho
if (strlen($texto_final) > 30) {
    $texto_final = substr($texto_final, 0, 30);
}

// 3. Geração da Imagem (GD Library)
// Dimensões
$width = 500;
$height = 150;

$image = imagecreatetruecolor($width, $height);

// Fundo Transparente
imagealphablending($image, false);
$transparency = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefill($image, 0, 0, $transparency);
imagesavealpha($image, true);

// Converter HEX para RGB
list($r, $g, $b) = sscanf($cor_hex, "#%02x%02x%02x");
$text_color = imagecolorallocate($image, $r, $g, $b);

// 4. Tentar Escrever Texto
$font_path = 'C:/Windows/Fonts/arial.ttf'; // Tenta fonte do sistema Windows
$font_size = 30; // Tamanho inicial

// Tenta usar TTF, se falhar, usa fonte nativa feia
$usou_ttf = false;

if (file_exists($font_path)) {
    try {
        // Cálculo de Bounding Box para centralizar
        $bbox = imagettfbbox($font_size, 0, $font_path, $texto_final);
        if ($bbox) {
            $text_width = $bbox[2] - $bbox[0];
            $text_height = $bbox[1] - $bbox[7];
            
            $x = ($width - $text_width) / 2;
            $y = ($height - $text_height) / 2 + $text_height; // Base do texto
            
            imagettftext($image, $font_size, 0, $x, $y, $text_color, $font_path, $texto_final);
            $usou_ttf = true;
        }
    } catch (Exception $e) {
        $usou_ttf = false;
    }
}

// Fallback: Fonte Nativa (imagestring)
if (!$usou_ttf) {
    // Fonte 5 é a maior nativa (aprox 15px largura)
    $font = 5;
    $char_width = imagefontwidth($font);
    $char_height = imagefontheight($font);
    
    $text_width = strlen($texto_final) * $char_width;
    $text_height = $char_height;
    
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;
    
    imagestring($image, $font, $x, $y, $texto_final, $text_color);
}

// 5. Salvar Arquivo
$upload_dir = __DIR__ . '/uploads/logos/';
if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }

$filename = 'logo_gen_' . $id_usuario . '_' . time() . '.png';
$filepath = $upload_dir . $filename;
$db_path = 'uploads/logos/' . $filename;

imagepng($image, $filepath);
imagedestroy($image);

// 6. Atualizar Banco
// Remove anterior (opcional, copia o código de upload_logo.php se quiser limpar lixo)
$stmtBusca = $conn->prepare("SELECT logo_caminho FROM DadosEmpresa WHERE id_criador = ?");
$stmtBusca->bind_param('i', $id_usuario);
$stmtBusca->execute();
$resBusca = $stmtBusca->get_result();
if ($row = $resBusca->fetch_assoc()) {
    if (!empty($row['logo_caminho']) && file_exists(__DIR__ . '/' . $row['logo_caminho'])) {
        @unlink(__DIR__ . '/' . $row['logo_caminho']);
    }
}

$stmtUpd = $conn->prepare("UPDATE DadosEmpresa SET logo_caminho = ? WHERE id_criador = ?");
$stmtUpd->bind_param('si', $db_path, $id_usuario);

if ($stmtUpd->execute()) {
    header("Location: minha_empresa.php?msg=sucesso");
} else {
    echo "Erro ao salvar no banco: " . $conn->error;
}
?>
