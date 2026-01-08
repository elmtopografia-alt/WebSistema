<?php
// Arquivo: criar_usuario_ricardo.php
// Função: Criar usuário Ricardo Noah Dias solicitado pelo usuário

require_once 'config.php';
require_once 'db.php';

$usuario = 'Ricardo Noah Dias';
$senha_raw = '@Qm1I16rop6';
$nome = 'Ricardo Noah Dias';
$perfil = 'admin'; // Demo users are usually admins of their own demo
$validade = date('Y-m-d', strtotime('+1 year'));

try {
    $conn = Database::getDemo();
    
    // 1. Verifica se já existe
    $stmt = $conn->prepare("SELECT id_usuario FROM Usuarios WHERE usuario = ?");
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        echo "<h1>⚠️ Usuário já existe (DEMO)!</h1>";
        echo "<p>O usuário '$usuario' já está cadastrado no ambiente DEMO.</p>";
        // Opcional: Atualizar senha se já existir? Melhor não forçar sem pedir.
    } else {
        // 2. Cria usuário
        $senha_hash = password_hash($senha_raw, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO Usuarios (usuario, senha, nome_completo, tipo_perfil, ambiente, validade_acesso, setup_concluido, data_cadastro) 
                VALUES (?, ?, ?, ?, 'demo', ?, 1, NOW())";
        
        $stmtIns = $conn->prepare($sql);
        $stmtIns->bind_param('sssss', $usuario, $senha_hash, $nome, $perfil, $validade);
        
        if ($stmtIns->execute()) {
            $id_novo = $conn->insert_id;
            
            // 3. Cria dados da empresa (Obrigatório para o sistema funcionar)
            $sqlEmp = "INSERT INTO DadosEmpresa (id_criador, Empresa, Cidade, Estado) VALUES (?, 'Empresa do Ricardo', 'São Paulo', 'SP')";
            $stmtEmp = $conn->prepare($sqlEmp);
            $stmtEmp->bind_param('i', $id_novo);
            $stmtEmp->execute();

            echo "<h1>✅ Usuário Criado com Sucesso!</h1>";
            echo "<p><strong>Usuário:</strong> $usuario</p>";
            echo "<p><strong>Senha:</strong> $senha_raw</p>";
            echo "<p><strong>Perfil:</strong> $perfil (Demo)</p>";
            echo "<br><a href='login_demo.php'>Ir para Login Demo</a>";
        } else {
            echo "Erro ao inserir: " . $conn->error;
        }
    }

} catch (Exception $e) {
    echo "Erro Crítico: " . $e->getMessage();
}
?>
