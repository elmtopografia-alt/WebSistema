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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/landing_dark.css">
    <style>
        /* Overrides específicos para o formulário no tema Dark */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .card-cadastro {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            animation: float 6s ease-in-out infinite; /* Sutil movimento */
        }

        h3 {
            color: var(--primary);
            margin-top: 0;
            font-weight: 800;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            color: #ccc;
            margin-bottom: 30px;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: var(--primary);
            font-size: 0.8rem;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-group {
            display: flex;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .input-group:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 242, 254, 0.2);
        }

        .input-icon {
            padding: 12px 15px;
            color: #888;
            display: flex;
            align-items: center;
        }

        input {
            background: transparent;
            border: none;
            color: white;
            width: 100%;
            padding: 12px 10px;
            outline: none;
            font-family: 'Inter', sans-serif;
        }

        input::placeholder {
            color: rgba(255,255,255,0.3);
        }

        .btn-submit {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: #000;
            border: none;
            width: 100%;
            padding: 15px;
            border-radius: 50px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 242, 254, 0.4);
        }

        .password-rules {
            background: rgba(0,0,0,0.2);
            padding: 15px;
            border-radius: 10px;
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 20px;
        }

        .rule-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 5px;
        }

        .rule-item.valid { color: #00ff88; }
        .rule-item.invalid { color: #ff4444; }

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #888;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .links a:hover { color: var(--primary); }

        .alert-danger {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid #ff4444;
            color: #ff4444;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <div class="card-cadastro">
        <h3>Quase lá! 🚀</h3>
        <p class="subtitle">
            Para <strong>acessar e editar</strong> o modelo de proposta, precisamos criar seu acesso seguro.<br>
            É rápido, gratuito e sem compromisso.
        </p>

        <?php if($erro): ?>
            <div class="alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="formCadastro" novalidate autocomplete="off">
            <!-- Fake fields to trick browser autofill -->
            <input type="text" style="display:none">
            <input type="password" style="display:none">

            <div class="form-group">
                <label>Nome Completo</label>
                <div class="input-group">
                    <div class="input-icon"><i class="bi bi-person"></i></div>
                    <input type="text" name="nome" required placeholder="Digite seu nome" autocomplete="new-password">
                </div>
            </div>

            <div class="form-group">
                <label>Seu Melhor E-mail</label>
                <div class="input-group">
                    <div class="input-icon"><i class="bi bi-envelope"></i></div>
                    <input type="email" name="email" required placeholder="seu@email.com" autocomplete="new-password">
                </div>
            </div>

            <div class="form-group">
                <label>Crie uma Senha Forte</label>
                <div class="input-group">
                    <div class="input-icon"><i class="bi bi-lock"></i></div>
                    <input type="password" name="senha" id="senhaInput" required placeholder="Mínimo 8 caracteres..." autocomplete="new-password">
                    <div class="input-icon" style="cursor: pointer;" onclick="toggleSenha()">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </div>
                </div>
            </div>

            <div class="password-rules">
                <div style="margin-bottom: 8px; font-weight: bold; color: #ccc;">Sua senha precisa ter:</div>
                <div class="rule-item" id="rule-length"><i class="bi bi-circle"></i> Mínimo 8 caracteres</div>
                <div class="rule-item" id="rule-upper"><i class="bi bi-circle"></i> Uma letra Maiúscula</div>
                <div class="rule-item" id="rule-number"><i class="bi bi-circle"></i> Um Número</div>
                <div class="rule-item" id="rule-symbol"><i class="bi bi-circle"></i> Um Símbolo (@ # $ %)</div>
            </div>

            <button type="submit" class="btn-submit" id="btnSubmit">
                Liberar Meu Acesso
            </button>
        </form>

        <div class="links">
            <a href="login_demo.php">Já tenho conta, quero entrar</a><br><br>
            <a href="index.php">&larr; Voltar ao Início</a>
        </div>
    </div>

    <script>
        const senhaInput = document.getElementById('senhaInput');
        
        // Regras
        const rules = {
            length: document.getElementById('rule-length'),
            upper: document.getElementById('rule-upper'),
            number: document.getElementById('rule-number'),
            symbol: document.getElementById('rule-symbol')
        };

        function toggleSenha() {
            const type = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
            senhaInput.setAttribute('type', type);
            document.getElementById('eyeIcon').classList.toggle('bi-eye');
            document.getElementById('eyeIcon').classList.toggle('bi-eye-slash');
        }

        senhaInput.addEventListener('input', function() {
            const val = senhaInput.value;
            
            // Validações
            updateRule('length', val.length >= 8);
            updateRule('upper', /[A-Z]/.test(val));
            updateRule('number', /[0-9]/.test(val));
            updateRule('symbol', /[\W_]/.test(val));
        });

        function updateRule(rule, isValid) {
            const el = rules[rule];
            const icon = el.querySelector('i');
            
            if (isValid) {
                el.classList.add('valid');
                el.classList.remove('invalid');
                icon.className = 'bi bi-check-circle-fill';
            } else {
                el.classList.remove('valid');
                el.classList.add('invalid'); // Opcional
                icon.className = 'bi bi-circle';
            }
        }

        document.getElementById('formCadastro').addEventListener('submit', function(e) {
            const val = senhaInput.value;
            if (val.length < 8 || !/[A-Z]/.test(val) || !/[0-9]/.test(val) || !/[\W_]/.test(val)) {
                e.preventDefault();
                alert('Por favor, fortaleça sua senha seguindo as regras abaixo.');
                senhaInput.focus();
            }
        });
    </script>

</body>
</html>