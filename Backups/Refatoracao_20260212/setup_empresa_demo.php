<?php
// Inicio: setup_empresa_demo.php
// Função: Configura a empresa DEMO com dados fictícios e BAIXA O BANNER oficial.

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Só roda se for DEMO
if (!isset($_SESSION['ambiente']) || $_SESSION['ambiente'] !== 'demo') {
    die("Esta função é exclusiva do ambiente de demonstração.");
}

$id_usuario = $_SESSION['usuario_id'];
$conn = Database::getDemo();

try {
    // 2. Lógica do Banner (Download Automático com Fallback)
    $url_banner = 'http://elmtopografia.com.br/Orcamento/img/cabecalho_proposta_dEMO.png';
    $pasta_local = 'uploads/logos/';
    $nome_arquivo = 'banner_demo_oficial.png';
    $caminho_banco = $pasta_local . $nome_arquivo; 

    // Cria a pasta se não existir
    $dir_absoluto = __DIR__ . '/' . $pasta_local;
    if (!is_dir($dir_absoluto)) {
        mkdir($dir_absoluto, 0755, true);
    }

    // Tenta baixar a imagem
    $conteudo_imagem = @file_get_contents($url_banner);
    
    // Validação simples: se baixou e tem tamanho razoável (> 1KB)
    if ($conteudo_imagem && strlen($conteudo_imagem) > 1000) {
        file_put_contents($dir_absoluto . $nome_arquivo, $conteudo_imagem);
    } else {
        // Fallback: Se não conseguir baixar, usa uma imagem local ou deixa sem
        // Tenta copiar de assets/img/sem_logo.png se existir, ou cria um placeholder
        $fallback = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDAgNjAiIHdpZHRoPSIyMDAiIGhlaWdodD0iNjAiPjxkZWZzPjxsaW5lYXJHcmFkaWVudCBpZD0iZ3JhZEljb24iIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMTAwJSIgeTI9IjEwMCUiPjxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiNmOTczMTY7c3RvcC1vcGFjaXR5OjEiIC8+PHN0b3Agb2Zmc2V0PSIxMDAlIiBzdHlsZT0ic3RvcC1jb2xvcjojZWE1ODBjO3N0b3Atb3BhY2l0eToxIiAvPjwvbGluZWFyR3JhZGllbnQ+PC9kZWZzPjxyZWN0IHg9IjUiIHk9IjUiIHdpZHRoPSI1MCIgaGVpZ2h0PSI1MCIgcng9IjEwIiBmaWxsPSJ1cmwoI2dyYWRJY29uKSIvPjxwYXRoIGQ9Ik0yOCAxNSBMMjIgMjggTDMwIDI4IEwyNiA0NSBMMzggMzAgTDMwIDMwIEwzNCAxNSBaIiBmaWxsPSJ3aGl0ZSIvPjx0ZXh0IHg9IjY1IiB5PSIzNSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXdlaWdodD0iYm9sZCIgZm9udC1zaXplPSIyNCIgZmlsbD0id2hpdGUiPlNHVDwvdGV4dD48dGV4dCB4PSI2NSIgeT0iNTAiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxMiIgZmlsbD0iI2ZiOTIzYyI+UHJvcG9zdGFzPC90ZXh0Pjwvc3ZnPg==';
        if (file_exists($fallback)) {
            copy($fallback, $dir_absoluto . $nome_arquivo);
        } else {
            // Se nem o fallback existir, não salva caminho de logo no banco para evitar erro
            $caminho_banco = ''; 
        }
        error_log("Aviso: Falha ao baixar banner demo ($url_banner). Usando fallback.");
    }

    // 3. Dados Fictícios Profissionais
    // CORREÇÃO: Removido '&' que pode quebrar o XML do Word se não escapado corretamente
    $empresa = "Topografia e Engenharia Modelo Ltda";
    $cnpj = "12.345.678/0001-90";
    $endereco = "Av. Paulista, 1000 - Conjunto 501";
    $cidade = "São Paulo";
    $estado = "SP";
    $telefone = "(11) 3000-0000";
    $celular = "(11) 99999-8888";
    $whatsapp = "(11) 99999-8888";
    $banco = "Banco do Brasil";
    $agencia = "1234-5";
    $conta = "10500-X";
    $pix = "financeiro@demo.com.br";
    
    // 4. Atualiza ou Insere no Banco (Incluindo o logo_caminho)
    $check = $conn->query("SELECT id_empresa FROM DadosEmpresa WHERE id_criador = $id_usuario");
    
    if ($check->num_rows > 0) {
        $sql = "UPDATE DadosEmpresa SET 
                Empresa=?, CNPJ=?, Endereco=?, Cidade=?, Estado=?, 
                Telefone=?, Celular=?, Whatsapp=?, 
                Banco=?, Agencia=?, Conta=?, PIX=?,
                logo_caminho=? 
                WHERE id_criador=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssssi", $empresa, $cnpj, $endereco, $cidade, $estado, $telefone, $celular, $whatsapp, $banco, $agencia, $conta, $pix, $caminho_banco, $id_usuario);
    } else {
        $sql = "INSERT INTO DadosEmpresa (Empresa, CNPJ, Endereco, Cidade, Estado, Telefone, Celular, Whatsapp, Banco, Agencia, Conta, PIX, logo_caminho, id_criador) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssssi", $empresa, $cnpj, $endereco, $cidade, $estado, $telefone, $celular, $whatsapp, $banco, $agencia, $conta, $pix, $caminho_banco, $id_usuario);
    }
    
    $stmt->execute();
    
    // Redireciona com sucesso
    header("Location: painel.php?msg=demo_configurada");

} catch (Exception $e) {
    die("Erro ao configurar demo: " . $e->getMessage());
}
// Fim: setup_empresa_demo.php
?>