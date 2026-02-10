<?php
// Nome do Arquivo: criar_conta_demo.php
// Função: Cadastro Demo com Layout Bootstrap e Regras Claras.

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
                    header("Location: login_demo.php?msg=criada");
                    exit;
                } else {
                    $erro = "Erro ao criar conta. Tente novamente.";
                }
            }
        } catch (Exception $e) { $erro = "Erro técnico no servidor."; }
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
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;800&display=swap" rel="stylesheet">
    <style>
         body { background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Inter', sans-serif; }
        
        .login-card { width: 100%; border-radius: 15px; box-shadow: 0 15px 30px rgba(0,0,0,0.3); overflow: hidden; }
        .login-header { background-color: #fff; color: #EA580C; padding: 25px 20px 10px; text-align: center; }
        
        /* Botão Laranja Personalizado */
        .btn-custom { background-color: #EA580C; color: white; font-weight: 800; letter-spacing: 0.5px; border: none; }
        .btn-custom:hover { background-color: #C2410C; color: white; }

        .feature-item { margin-bottom: 20px; display: flex; align-items: flex-start; }
        .feature-icon { font-size: 1.5rem; margin-right: 15px; flex-shrink: 0; color: #FDBA74; }
        .feature-text { color: white; font-size: 0.9rem; opacity: 0.95; }
        .feature-title { font-weight: bold; display: block; margin-bottom: 2px; font-size: 1rem; color: #FFF; }
        
        .password-rules {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .rule-item { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
        .rule-item i { font-size: 1rem; }
        .rule-item.valid { color: #198754; font-weight: 600; }
        .rule-item.invalid { color: #dc3545; }
        .rule-item.valid i::before { content: "\F26B"; /* Bootstap check-circle-fill code (approx) or phosphor */ }
    </style>
</head>
<body>

    <div class="row justify-content-center w-100 px-3">
        <!-- Coluna de Regras DEMO (Esquerda) -->
        <div class="col-lg-5 col-md-8 mb-4 mb-lg-0">
            <div class="card h-100 border-0 shadow-lg" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1);">
                <div class="card-body p-4 p-lg-5 text-white">
                    <h2 class="fw-bold mb-4">Conta Demo <span style="color: #FF7518;">Grátis 🚀</span></h2>
                    <p class="subtitle mb-5 opacity-75">Entenda como funciona nosso período de avaliação:</p>
                    
                    <div class="feature-item">
                        <div class="feature-icon"><i class="ph ph-calendar-check"></i></div>
                        <div class="feature-text">
                            <span class="feature-title">5 Dias de Acesso Total</span>
                            Você terá acesso irrestrito a todas as ferramentas por 5 dias corridos.
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon"><i class="ph ph-prohibit"></i></div>
                        <div class="feature-text">
                            <span class="feature-title">Sem Cartão de Crédito</span>
                            Não pedimos dados de pagamento para testar. É só cadastrar e usar.
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon"><i class="ph ph-hourglass-high"></i></div>
                        <div class="feature-text">
                            <span class="feature-title">Validade dos Dados (30 Dias)</span>
                            Seus dados ficam salvos por <strong>30 dias</strong> após o teste. Contrate o plano PRO neste prazo para não perdê-los.
                        </div>
                    </div>

                    <div class="alert bg-warning-subtle text-dark border-0 mt-5 rounded-4 shadow-sm" role="alert">
                        <div class="d-flex">
                            <i class="ph ph-warning fs-2 me-3 text-danger"></i>
                            <div>
                                <strong class="text-uppercase mb-1 d-block text-danger" style="font-size: 0.85rem;">Política de Exclusão</strong>
                                <span class="d-block lh-sm" style="font-size: 0.85rem;">
                                    Ao final dos 5 dias, seu acesso trava. <strong>Após 30 dias inativo, tudo é apagado permanentemente.</strong>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Coluna de Cadastro (Direita) -->
        <div class="col-lg-4 col-md-8">
            <div class="card login-card bg-white border-0 h-100">
                <div class="login-header">
                    <h3 class="mb-1 fw-bold">Criar Login Seguro</h3>
                    <p class="text-muted small">Preencha para liberar seu acesso agora</p>
                </div>
                <div class="card-body p-4">
                    
                    <?php if($erro): ?>
                        <div class="alert alert-danger text-center py-2 shadow-sm border-0 small">
                            <?php echo $erro; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="formCadastro" novalidate autocomplete="off">
                        <!-- Fake fields -->
                        <input type="text" style="display:none">
                        <input type="password" style="display:none">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small">SEU NOME COMPLETO</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="ph ph-user"></i></span>
                                <input type="text" name="nome" class="form-control bg-light border-start-0" required placeholder="Digite seu nome" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small">SEU MELHOR E-MAIL</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="ph ph-envelope"></i></span>
                                <input type="email" name="email" class="form-control bg-light border-start-0" required placeholder="seu@email.com" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold text-secondary small">CRIE UMA SENHA FORTE</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="ph ph-lock"></i></span>
                                <input type="password" name="senha" id="senhaInput" class="form-control bg-light border-start-0" required placeholder="Mínimo 8 caracteres..." autocomplete="new-password">
                                <span class="input-group-text bg-light border-start-0" style="cursor: pointer;" onclick="toggleSenha()">
                                    <i class="ph ph-eye" id="eyeIcon"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Feedback Visual da Senha -->
                        <div class="password-rules">
                            <div class="fw-bold mb-2 text-dark" style="font-size: 0.75rem;">REQUISITOS DA SENHA:</div>
                            <div class="rule-item" id="rule-length"><i class="ph ph-circle"></i> Mínimo 8 caracteres</div>
                            <div class="rule-item" id="rule-upper"><i class="ph ph-circle"></i> Uma letra Maiúscula</div>
                            <div class="rule-item" id="rule-number"><i class="ph ph-circle"></i> Um Número</div>
                            <div class="rule-item" id="rule-symbol"><i class="ph ph-circle"></i> Um Símbolo (@ # $ %)</div>
                        </div>

                        <button type="submit" class="btn btn-custom w-100 btn-lg shadow-sm mb-3">
                            LIBERAR ACESSO GRÁTIS
                        </button>
                    </form>
                    
                    <div class="text-center pt-2">
                        <a href="login_demo.php" class="text-decoration-none text-muted small fw-bold">
                            Já tenho conta, quero entrar
                        </a>
                        <br>
                        <a href="index.php" class="text-decoration-none text-muted small mt-2 d-inline-block">
                            &larr; Voltar ao site
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const senhaInput = document.getElementById('senhaInput');
        
        // Mapeamento de Regras
        const rules = {
            length: document.getElementById('rule-length'),
            upper: document.getElementById('rule-upper'),
            number: document.getElementById('rule-number'),
            symbol: document.getElementById('rule-symbol')
        };

        function toggleSenha() {
            const type = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
            senhaInput.setAttribute('type', type);
            const icon = document.getElementById('eyeIcon');
            if(type === 'text') {
                icon.classList.remove('ph-eye');
                icon.classList.add('ph-eye-slash');
            } else {
                icon.classList.remove('ph-eye-slash');
                icon.classList.add('ph-eye');
            }
        }

        senhaInput.addEventListener('input', function() {
            const val = senhaInput.value;
            
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
                icon.classList.remove('ph-circle');
                icon.classList.add('ph-check-circle');
            } else {
                el.classList.remove('valid');
                el.classList.add('invalid'); // Opcional, para deixar vermelho
                icon.classList.remove('ph-check-circle');
                icon.classList.add('ph-circle');
            }
        }

        document.getElementById('formCadastro').addEventListener('submit', function(e) {
            const val = senhaInput.value;
            // Bloqueio extra no front para evitar reload desnecessário
            if (val.length < 8 || !/[A-Z]/.test(val) || !/[0-9]/.test(val) || !/[\W_]/.test(val)) {
                e.preventDefault();
                alert('Por favor, fortaleça sua senha seguindo todas as regras.');
                senhaInput.focus();
            }
        });
    </script>
</body>
</html>