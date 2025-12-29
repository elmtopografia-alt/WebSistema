<?php
// Nome do Arquivo: criar_conta_demo.php
// Função: Cadastro Demo com Instruções Visuais e Bloqueio de Senha Fraca.

session_start();
require_once 'config.php';
require_once 'db.php';

// Limpa sessão anterior para evitar conflito
session_destroy(); 

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    // 1. Validação PHP (Última barreira)
    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Todos os campos são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Digite um e-mail válido.";
    } elseif (strlen($senha) < 8 || !preg_match("/[A-Z]/", $senha) || !preg_match("/[0-9]/", $senha) || !preg_match("/[\W]/", $senha)) {
        // Se passar pelo JS, o PHP barra aqui
        $erro = "A senha não atende aos requisitos de segurança (8 dígitos, Maiúscula, Número e Símbolo).";
    } else {
        try {
            $conn = Database::getDemo();

            // Verifica duplicidade
            $stmt = $conn->prepare("SELECT id_usuario FROM Usuarios WHERE usuario = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                header("Location: login_demo.php?erro=existe");
                exit;
            } else {
                $validade = date('Y-m-d H:i:s', strtotime('+5 days'));
                
                // CRIPTOGRAFIA OBRIGATÓRIA
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, ambiente, tipo_perfil, data_cadastro, validade_acesso) 
                        VALUES (?, ?, ?, 1, 'demo', 'admin', NOW(), ?)";
                
                $stmtInsert = $conn->prepare($sql);
                $stmtInsert->bind_param('ssss', $email, $senha_hash, $nome, $validade);
                
                if ($stmtInsert->execute()) {
                    $id_novo = $conn->insert_id;
                    
                    // Cria dados da empresa
                    $sqlEmp = "INSERT INTO DadosEmpresa (id_criador, Empresa, Cidade, Estado, CNPJ) VALUES (?, 'Sua Empresa Demo', 'São Paulo', 'SP', '00.000.000/0001-00')";
                    $stmtEmp = $conn->prepare($sqlEmp);
                    $stmtEmp->bind_param('i', $id_novo);
                    $stmtEmp->execute();

                    header("Location: login_demo.php?msg=criada");
                    exit;
                } else {
                    $erro = "Erro ao cadastrar no banco de dados.";
                }
            }
        } catch (Exception $e) { $erro = "Erro técnico: " . $e->getMessage(); }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Conta Demo | SGT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .card-cadastro { max-width: 500px; width: 95%; border-radius: 15px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.3); }
        
        /* Lista de requisitos de senha */
        .password-rules { font-size: 0.85rem; background-color: #f8f9fa; padding: 10px; border-radius: 8px; border: 1px solid #dee2e6; margin-top: 5px; }
        .rule-item { display: flex; align-items: center; margin-bottom: 2px; color: #6c757d; transition: all 0.3s; }
        .rule-item i { margin-right: 8px; font-size: 1rem; }
        .rule-item.valid { color: #198754; font-weight: bold; }
        .rule-item.invalid { color: #dc3545; }
    </style>
</head>
<body>

    <div class="card card-cadastro p-4 bg-white">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">Solicitar Teste Grátis</h3>
            <p class="text-muted small">Preencha os dados para gerar seu acesso imediato.</p>
        </div>

        <?php if($erro): ?>
            <div class="alert alert-danger text-center small py-2">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="formCadastro" novalidate>
            
            <div class="mb-3">
                <label class="form-label text-secondary fw-bold small">NOME COMPLETO</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                    <input type="text" name="nome" class="form-control border-start-0 ps-0" required placeholder="Ex: João Silva">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary fw-bold small">SEU MELHOR E-MAIL</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 ps-0" required placeholder="joao@email.com">
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label text-secondary fw-bold small">CRIE SUA SENHA</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock"></i></span>
                    <input type="password" name="senha" id="senhaInput" class="form-control border-start-0 ps-0" required placeholder="Digite sua senha...">
                    <button class="btn btn-outline-secondary border-start-0 border" type="button" onclick="toggleSenha()">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <!-- PAINEL DE REGRAS (Feedback Visual) -->
            <div class="password-rules mb-4">
                <div class="fw-bold text-dark mb-2 small">A senha deve conter:</div>
                <div class="rule-item" id="rule-length"><i class="bi bi-circle"></i> Mínimo 8 caracteres</div>
                <div class="rule-item" id="rule-upper"><i class="bi bi-circle"></i> Uma letra Maiúscula (A-Z)</div>
                <div class="rule-item" id="rule-number"><i class="bi bi-circle"></i> Um Número (0-9)</div>
                <div class="rule-item" id="rule-symbol"><i class="bi bi-circle"></i> Um Símbolo (@ # $ %)</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold btn-lg shadow-sm" id="btnSubmit">
                CRIAR CONTA
            </button>
        </form>

        <div class="text-center mt-3 pt-3 border-top">
            <a href="login_demo.php" class="text-decoration-none text-muted small">Já tenho senha, quero entrar</a>
        </div>
        <div class="text-center mt-2">
            <a href="index.php" class="text-decoration-none text-muted small">&larr; Voltar ao Início</a>
        </div>
    </div>

    <!-- Scripts de Validação em Tempo Real -->
    <script>
        const senhaInput = document.getElementById('senhaInput');
        const btnSubmit = document.getElementById('btnSubmit');

        // Regras (Elementos)
        const rules = {
            length: document.getElementById('rule-length'),
            upper: document.getElementById('rule-upper'),
            number: document.getElementById('rule-number'),
            symbol: document.getElementById('rule-symbol')
        };

        // Função para mostrar/ocultar senha
        function toggleSenha() {
            const type = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
            senhaInput.setAttribute('type', type);
            document.getElementById('eyeIcon').classList.toggle('bi-eye');
            document.getElementById('eyeIcon').classList.toggle('bi-eye-slash');
        }

        // Validação ao digitar
        senhaInput.addEventListener('input', function() {
            const val = senhaInput.value;
            let validCount = 0;

            // 1. Tamanho
            if (val.length >= 8) { setValid('length'); validCount++; } else { setInvalid('length'); }
            
            // 2. Maiúscula
            if (/[A-Z]/.test(val)) { setValid('upper'); validCount++; } else { setInvalid('upper'); }
            
            // 3. Número
            if (/[0-9]/.test(val)) { setValid('number'); validCount++; } else { setInvalid('number'); }
            
            // 4. Símbolo (Caracteres especiais comuns)
            if (/[\W_]/.test(val)) { setValid('symbol'); validCount++; } else { setInvalid('symbol'); }

            // Se tudo válido, libera botão (opcional, mas bom pra UX)
            /*
            if (validCount === 4) {
                btnSubmit.classList.remove('disabled');
            } else {
                btnSubmit.classList.add('disabled');
            }
            */
        });

        function setValid(rule) {
            rules[rule].classList.remove('invalid');
            rules[rule].classList.add('valid');
            rules[rule].querySelector('i').className = 'bi bi-check-circle-fill';
        }

        function setInvalid(rule) {
            rules[rule].classList.remove('valid');
            rules[rule].classList.add('invalid'); // Opcional: deixar vermelho ou voltar ao neutro
            rules[rule].querySelector('i').className = 'bi bi-circle'; // Neutro
        }

        // Bloqueio no Submit (Garantia Final)
        document.getElementById('formCadastro').addEventListener('submit', function(e) {
            const val = senhaInput.value;
            if (val.length < 8 || !/[A-Z]/.test(val) || !/[0-9]/.test(val) || !/[\W_]/.test(val)) {
                e.preventDefault(); // Impede o envio
                alert('Sua senha é muito fraca!\n\nPor favor, siga todas as regras listadas abaixo do campo de senha.');
                senhaInput.focus();
            }
        });
    </script>

</body>
</html>