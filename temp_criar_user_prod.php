<?php
// Script temporário para criar usuário solicitado
if (php_sapi_name() === 'cli' && !isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['SCRIPT_NAME'] = __FILE__;
    $_SERVER['HTTPS'] = 'off';
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
require_once 'db.php';
require_once 'config.php';

$usuario = 'elmyopografia@gmail.com';
$senha_raw = 'Elm$1955$';
$nome = 'ELM Topografia (Produção)';
$nome_empresa = 'ELM Topografia';

echo "Iniciando criação de usuário...\n";

try {
    $conn = Database::getProd();
    
    // 1. Verifica se usuário já existe
    $stmt = $conn->prepare("SELECT id_usuario FROM Usuarios WHERE usuario = ?");
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $id_usuario = 0;
    
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $id_usuario = $row['id_usuario'];
        echo "Usuário $usuario já existe (ID: $id_usuario). Atualizando senha...\n";
        
        $hash = password_hash($senha_raw, PASSWORD_DEFAULT);
        $up = $conn->prepare("UPDATE Usuarios SET senha = ?, validade_acesso = DATE_ADD(NOW(), INTERVAL 1 YEAR) WHERE id_usuario = ?");
        $up->bind_param('si', $hash, $id_usuario);
        $up->execute();
        
    } else {
        echo "Criando novo usuário $usuario...\n";
        
        $hash = password_hash($senha_raw, PASSWORD_DEFAULT);
        $validade = date('Y-m-d H:i:s', strtotime('+1 year'));
        
        $ins = $conn->prepare("INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, ambiente, tipo_perfil, validade_acesso, data_cadastro) VALUES (?, ?, ?, 1, 'producao', 'cliente', ?, NOW())");
        $ins->bind_param('ssss', $usuario, $hash, $nome, $validade);
        
        if ($ins->execute()) {
            $id_usuario = $conn->insert_id;
            echo "Usuário criado com sucesso! ID: $id_usuario\n";
        } else {
            throw new Exception("Erro ao inserir usuário: " . $conn->error);
        }
    }
    
    // 2. Verifica se já tem DadosEmpresa
    $stmtEmp = $conn->prepare("SELECT id_empresa FROM DadosEmpresa WHERE id_criador = ?");
    $stmtEmp->bind_param('i', $id_usuario);
    $stmtEmp->execute();
    if ($stmtEmp->get_result()->num_rows == 0) {
        echo "Vinculando dados da empresa...\n";
        
        // Tenta copiar da empresa principal (ID 1) se existir
        $resMain = $conn->query("SELECT * FROM DadosEmpresa WHERE id_empresa = 1");
        if ($resMain && $rw = $resMain->fetch_assoc()) {
             // Copia dados
             $insEmp = $conn->prepare("INSERT INTO DadosEmpresa (id_criador, Empresa, Endereco, Cidade, Estado, CNPJ, Telefone, Celular, Whatsapp, Banco, Agencia, Conta, PIX) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
             $insEmp->bind_param('issssssssssss', 
                $id_usuario, 
                $rw['Empresa'], 
                $rw['Endereco'], 
                $rw['Cidade'], 
                $rw['Estado'], 
                $rw['CNPJ'], 
                $rw['Telefone'], 
                $rw['Celular'], 
                $rw['Whatsapp'], 
                $rw['Banco'], 
                $rw['Agencia'], 
                $rw['Conta'], 
                $rw['PIX']
             );
             $insEmp->execute();
             echo "Dados copiados da empresa principal.\n";
        } else {
             // Cria com dados básicos
             $insEmp = $conn->prepare("INSERT INTO DadosEmpresa (id_criador, Empresa, Cidade, Estado, CNPJ) VALUES (?, ?, 'Belo Horizonte', 'MG', '')");
             $insEmp->bind_param('is', $id_usuario, $nome_empresa);
             $insEmp->execute();
             echo "Dados básicos de empresa criados.\n";
        }
    } else {
        echo "Usuário já possui dados de empresa vinculados.\n";
    }

    echo "=== CONCLUÍDO ===\nUsuario: $usuario\nSenha: [Definida]\n";

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
