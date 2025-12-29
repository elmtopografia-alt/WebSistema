<?php
// ARQUIVO: reset_mestre.php
// VERSÃO: 3.0 - SEM BLOQUEIO DE LOGIN (Garante acesso mesmo deslogado)

// Inicia sessão apenas para mostrar mensagens, não verifica usuário
session_start();
require_once 'config.php';

$msg = "";
$senha_mestre_fixa = "elm2025"; 

// Verifica se o form foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar']) && $_POST['confirmar'] === 'YES') {
    
    $senha_digitada = $_POST['senha_seguranca'] ?? '';
    $ambiente_escolhido = $_POST['ambiente_alvo'] ?? '';

    // Valida a senha mestre
    if ($senha_digitada !== $senha_mestre_fixa) {
        $msg = "<div class='alert alert-danger'>❌ Senha de segurança incorreta.</div>";
    } else {
        
        // Define conexão baseada na escolha do SELECT
        if ($ambiente_escolhido === 'demo') {
            $host = DB_DEMO_HOST; $user = DB_DEMO_USER; $pass = DB_DEMO_PASS; $name = DB_DEMO_NAME;
            $label_amb = "DEMO (Banco: proposta)";
            $admin_amb_val = 'demo';
        } elseif ($ambiente_escolhido === 'producao') {
            $host = DB_PROD_HOST; $user = DB_PROD_USER; $pass = DB_PROD_PASS; $name = DB_PROD_NAME;
            $label_amb = "PRODUÇÃO (Banco: demanda)";
            $admin_amb_val = 'producao';
        } else {
            die("Ambiente inválido.");
        }

        $conn = new mysqli($host, $user, $pass, $name);
        
        if ($conn->connect_error) {
            $msg = "<div class='alert alert-danger'>Erro conexão: " . $conn->connect_error . "</div>";
        } else {
            // --- EXECUTA A LIMPEZA ---
            $conn->query("SET FOREIGN_KEY_CHECKS = 0");

            $tabelas = [
                'Proposta_Salarios', 'Proposta_Estadia', 'Proposta_Consumos', 
                'Proposta_Locacao', 'Proposta_Custos_Administrativos',
                'Propostas', 'Clientes', 'DadosEmpresa', 'Usuarios'
            ];

            foreach ($tabelas as $tab) {
                $conn->query("TRUNCATE TABLE $tab");
            }

            // --- RECRIA O ADMIN ---
            // Cria o usuário 'admin' na tabela do banco selecionado
            $sql_admin = "INSERT INTO Usuarios (id_usuario, usuario, senha, nome_completo, setup_concluido, tipo_perfil, ambiente) 
                          VALUES (1, 'admin', '123456', 'Administrador', 1, 'admin', ?)";
            
            $stmt = $conn->prepare($sql_admin);
            $stmt->bind_param('s', $admin_amb_val);
            
            if ($stmt->execute()) {
                // Cria dados da empresa do admin
                $conn->query("INSERT INTO DadosEmpresa (id_criador, Empresa, CNPJ) VALUES (1, 'Admin System', '00.000.000/0001-00')");
                
                // Aplica trava de segurança (Unique Email)
                try { $conn->query("ALTER TABLE Usuarios ADD UNIQUE (usuario)"); } catch(Exception $e){}

                $msg = "<div class='alert alert-success mt-3'>
                            <h4>✅ Sucesso!</h4>
                            <p>O ambiente <strong>$label_amb</strong> foi resetado.</p>
                            <p>Foi criado o usuário: <strong>admin</strong> / Senha: <strong>123456</strong> neste banco.</p>
                            <hr>
                            <a href='logout.php' class='btn btn-primary'>Ir para Login</a>
                        </div>";
            } else {
                $msg = "<div class='alert alert-danger'>❌ Erro ao criar admin: " . $stmt->error . "</div>";
            }
            
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Reset Mestre 3.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card text-dark shadow-lg" style="max-width: 600px; width: 100%;">
        <div class="card-header bg-warning fw-bold text-center">
            🚧 RESET MESTRE (Sem Login)
        </div>
        <div class="card-body p-5">
            
            <?php if ($msg): ?>
                <?= $msg ?>
            <?php else: ?>
                <div class="alert alert-secondary border-start border-4 border-warning">
                    <strong>Atenção:</strong><br>
                    Isso apagará todos os Usuários e Clientes do banco escolhido.<br>
                    Um usuário <b>admin</b> será criado automaticamente.
                </div>

                <form method="POST">
                    <input type="hidden" name="confirmar" value="YES">
                    
                    <div class="mb-3">
                        <label class="fw-bold">Qual banco limpar?</label>
                        <select name="ambiente_alvo" class="form-select bg-light" required>
                            <option value="" selected disabled>Escolha...</option>
                            <option value="producao">PRODUÇÃO (demanda)</option>
                            <option value="demo">DEMONSTRAÇÃO (proposta)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold">Senha Mestre</label>
                        <input type="password" name="senha_seguranca" class="form-control" placeholder="..." required>
                        <div class="form-text">Senha: <strong>elm2025</strong></div>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold py-3">
                        ZERAR TUDO
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>