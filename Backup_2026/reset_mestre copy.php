<?php
// ARQUIVO: reset_mestre.php
// VERSÃO: STANDALONE (Não precisa de login, escolhe o banco na tela)

session_start();
require_once 'config.php';

$msg = "";

// SENHA PARA RODAR O SCRIPT (Segurança para não rodar acidentalmente)
$senha_mestre_fixa = "elm2025"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar']) && $_POST['confirmar'] === 'YES') {
    
    $senha_digitada = $_POST['senha_seguranca'] ?? '';
    $ambiente_escolhido = $_POST['ambiente_alvo'] ?? '';

    if ($senha_digitada !== $senha_mestre_fixa) {
        $msg = "<div class='alert alert-danger'>❌ Senha de segurança incorreta.</div>";
    } else {
        
        // Define qual banco conectar
        if ($ambiente_escolhido === 'demo') {
            $host = DB_DEMO_HOST; $user = DB_DEMO_USER; $pass = DB_DEMO_PASS; $name = DB_DEMO_NAME;
            $label_amb = "DEMO";
        } else {
            $host = DB_PROD_HOST; $user = DB_PROD_USER; $pass = DB_PROD_PASS; $name = DB_PROD_NAME;
            $label_amb = "PRODUÇÃO";
        }

        $conn = new mysqli($host, $user, $pass, $name);
        
        if ($conn->connect_error) {
            $msg = "<div class='alert alert-danger'>Erro de conexão com $label_amb: " . $conn->connect_error . "</div>";
        } else {
            // INÍCIO DA LIMPEZA
            $conn->query("SET FOREIGN_KEY_CHECKS = 0");

            $tabelas_limpeza = [
                'Proposta_Salarios', 'Proposta_Estadia', 'Proposta_Consumos', 
                'Proposta_Locacao', 'Proposta_Custos_Administrativos',
                'Propostas',
                'Clientes',
                'DadosEmpresa',
                'Usuarios' 
            ];

            foreach ($tabelas_limpeza as $tab) {
                $conn->query("TRUNCATE TABLE $tab");
            }

            // RECRIA ADMIN
            $sql_admin = "INSERT INTO Usuarios (id_usuario, usuario, senha, nome_completo, setup_concluido, tipo_perfil, ambiente) 
                          VALUES (1, 'admin', '123456', 'Administrador', 1, 'admin', ?)";
            
            $stmt = $conn->prepare($sql_admin);
            $amb_val = ($ambiente_escolhido === 'demo') ? 'demo' : 'producao';
            $stmt->bind_param('s', $amb_val);
            
            if ($stmt->execute()) {
                // Empresa do Admin
                $conn->query("INSERT INTO DadosEmpresa (id_criador, Empresa, CNPJ) VALUES (1, 'Minha Empresa Admin', '00.000.000/0001-00')");
                
                // TRAVA DE UNICIDADE (Só aplica se ainda não existir)
                try {
                    $conn->query("ALTER TABLE Usuarios ADD UNIQUE (usuario)");
                } catch (Exception $e) { /* Ignora se já existe */ }

                $msg = "<div class='alert alert-success mt-3'>
                            <h4>✅ Sucesso no ambiente $label_amb!</h4>
                            <p>Tabelas limpas e Admin recriado.</p>
                            <p><strong>Login:</strong> admin<br><strong>Senha:</strong> 123456</p>
                            <hr>
                            <a href='logout.php' class='btn btn-primary'>Ir para Login</a>
                        </div>";
            } else {
                $msg = "<div class='alert alert-danger'>❌ Erro ao recriar admin: " . $conn->error . "</div>";
            }
            
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Reset Mestre - Standalone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card text-dark shadow-lg" style="max-width: 600px; width: 100%;">
        <div class="card-header bg-warning fw-bold text-center">
            🚧 FERRAMENTA DE RESET (Sem Login)
        </div>
        <div class="card-body p-5">
            
            <?php if ($msg): ?>
                <?= $msg ?>
            <?php else: ?>
                <div class="alert alert-secondary border-start border-4 border-warning">
                    <strong>Atenção:</strong><br>
                    Este script apaga Usuários, Clientes e Propostas.<br>
                    Ele <u>mantém</u> os Tipos de Serviços e Configurações.
                </div>

                <form method="POST">
                    <input type="hidden" name="confirmar" value="YES">
                    
                    <div class="mb-3">
                        <label class="fw-bold">Qual banco deseja limpar?</label>
                        <select name="ambiente_alvo" class="form-select bg-light" required>
                            <option value="" selected disabled>Selecione...</option>
                            <option value="producao">PRODUÇÃO (Real)</option>
                            <option value="demo">DEMONSTRAÇÃO (Teste)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold">Senha de Segurança</label>
                        <input type="password" name="senha_seguranca" class="form-control" placeholder="Digite a senha mestre..." required>
                        <div class="form-text">A senha padrão configurada no arquivo é: <strong>elm2025</strong></div>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold py-3">
                        EXECUTAR LIMPEZA
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>