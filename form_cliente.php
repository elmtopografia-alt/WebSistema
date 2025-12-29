<?php
// Nome do Arquivo: form_cliente.php
// Função: Formulário de Cliente. 
// Ajuste: Script de máscara CPF/CNPJ corrigido para transição suave.

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Validação
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = ($_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();

// Variáveis padrão
$titulo = "Novo Cliente";
$acao = "criar";
$id_cliente = "";
$dados = [
    'nome_cliente' => '', 'empresa' => '', 'cnpj_cpf' => '', 
    'email' => '', 'telefone' => '', 'celular' => '', 'whatsapp' => ''
];

// Modo Edição
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_cliente = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM Clientes WHERE id_cliente = ? AND id_criador = ?");
    $stmt->bind_param('ii', $id_cliente, $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $dados = $row;
        $titulo = "Editar Cliente";
        $acao = "editar";
    } else {
        die("Cliente não encontrado.");
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Ajuste de label para ficar mais elegante */
        .form-label { font-weight: 600; color: #495057; }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar Simplificada -->
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="painel.php"><i class="bi bi-arrow-left me-2"></i>Voltar ao Painel</a>
            <span class="navbar-text text-white">Gestão de Clientes</span>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="card shadow border-0">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-primary"><?php echo $titulo; ?></h5>
                            <a href="meus_clientes.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> Voltar para Lista
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        
                        <form action="salvar_cliente.php" method="POST">
                            <input type="hidden" name="acao" value="<?php echo $acao; ?>">
                            <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">

                            <div class="mb-3">
                                <label class="form-label">Nome Completo *</label>
                                <input type="text" name="nome_cliente" class="form-control form-control-lg" value="<?php echo htmlspecialchars($dados['nome_cliente']); ?>" required autofocus>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nome da Empresa (Opcional)</label>
                                    <input type="text" name="empresa" class="form-control" value="<?php echo htmlspecialchars($dados['empresa']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">CPF ou CNPJ</label>
                                    <!-- Classe 'cpf-cnpj' será o alvo do nosso JavaScript -->
                                    <input type="text" name="cnpj_cpf" class="form-control cpf-cnpj" value="<?php echo htmlspecialchars($dados['cnpj_cpf']); ?>" placeholder="Digite apenas números">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($dados['email']); ?>">
                            </div>

                            <h6 class="text-muted mt-4 mb-3 pb-2 border-bottom">Contatos</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Celular / WhatsApp</label>
                                    <input type="text" name="celular" class="form-control celular" value="<?php echo htmlspecialchars($dados['celular']); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Telefone Fixo</label>
                                    <input type="text" name="telefone" class="form-control telefone" value="<?php echo htmlspecialchars($dados['telefone']); ?>">
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold">SALVAR CLIENTE</button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bibliotecas JS (jQuery e Mask Plugin) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
    <script>
        $(document).ready(function(){
            // Máscaras de Telefone
            $('.celular').mask('(00) 00000-0000');
            $('.telefone').mask('(00) 0000-0000');
            
            // --- MÁSCARA DINÂMICA CPF / CNPJ ---
            var cpfCnpjOptions = {
                onKeyPress: function (val, e, field, options) {
                    // Define a máscara correta baseada no tamanho atual
                    // Se tiver até 11 dígitos (contando tudo), usa CPF
                    // O '9' no final do CPF é o segredo: permite digitar o 12º caractere para disparar a troca
                    var masks = ['000.000.000-009', '00.000.000/0000-00'];
                    var mask = (val.replace(/\D/g, '').length > 11) ? masks[1] : masks[0];
                    $('.cpf-cnpj').mask(mask, options);
                }
            };

            // Aplica a lógica inicial
            $('.cpf-cnpj').mask('000.000.000-009', cpfCnpjOptions);
        });
    </script>
</body>
</html>