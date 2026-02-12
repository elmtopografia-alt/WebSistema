<?php
// Nome da página: setup.php
// VERSÃO: REFEITA DO ZERO (Arquitetura Limpa e Segura)

session_start();
require_once 'db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 1. Segurança
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

// 2. Controle de Etapas
$etapa = isset($_GET['etapa']) ? intval($_GET['etapa']) : 1;
$total_etapas = 10;
$progresso = ($etapa / $total_etapas) * 100;

$titulos = [
    1 => "Dados da Empresa", 2 => "Tipos de Equipamentos", 3 => "Modelos e Marcas",
    4 => "Funções e Salários", 5 => "Custos de Estadia", 6 => "Tipos de Consumo",
    7 => "Custos Administrativos", 8 => "Tipos de Serviços", 9 => "Cadastro de Clientes",
    10 => "Conclusão"
];
$titulo_atual = $titulos[$etapa] ?? "Configuração";

// 3. PROCESSAMENTO DE DADOS (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- SALVAR DADOS DA EMPRESA (ETAPA 1) ---
    if ($etapa == 1) {
        // --- INICIO: TRAVA DEMO ---
            if (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') { // <--- ABERTURA 2 (Início da Trava)
                header("Location: setup.php?etapa=2"); 
                exit;
            } // <--- FECHAMENTO 2 (Fim da Trava - É esse que você viu!)
            // --- FIM DA TRAVA ---
        // Verifica se já existe (Update ou Insert)
        $check = $conn->query("SELECT id_empresa FROM DadosEmpresa LIMIT 1");
        if ($check->num_rows == 0) {
            $sql = "INSERT INTO DadosEmpresa (Empresa, CNPJ, Telefone, Celular, Whatsapp, Endereco, Cidade, Estado, Banco, Agencia, Conta, PIX) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssssss", $_POST['Empresa'], $_POST['CNPJ'], $_POST['Telefone'], $_POST['Celular'], $_POST['Whatsapp'], $_POST['Endereco'], $_POST['Cidade'], $_POST['Estado'], $_POST['Banco'], $_POST['Agencia'], $_POST['Conta'], $_POST['PIX']);
        } else {
            $id = $check->fetch_assoc()['id_empresa'];
            $sql = "UPDATE DadosEmpresa SET Empresa=?, CNPJ=?, Telefone=?, Celular=?, Whatsapp=?, Endereco=?, Cidade=?, Estado=?, Banco=?, Agencia=?, Conta=?, PIX=? WHERE id_empresa=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssssssi", $_POST['Empresa'], $_POST['CNPJ'], $_POST['Telefone'], $_POST['Celular'], $_POST['Whatsapp'], $_POST['Endereco'], $_POST['Cidade'], $_POST['Estado'], $_POST['Banco'], $_POST['Agencia'], $_POST['Conta'], $_POST['PIX'], $id);
        }
        $stmt->execute();
        header("Location: setup.php?etapa=2"); exit;
    }

    // --- ADICIONAR ITENS (ETAPAS 2 a 9) ---
    if (isset($_POST['acao']) && $_POST['acao'] == 'adicionar') {
        
        if ($etapa == 2) { // Tipo Locacao
            $stmt = $conn->prepare("INSERT INTO Tipo_Locacao (nome, valor_mensal_default) VALUES (?, ?)");
            $stmt->bind_param("sd", $_POST['nome'], $_POST['valor']);
        }
        elseif ($etapa == 3) { // Marcas
            $stmt = $conn->prepare("INSERT INTO Marcas (id_locacao, nome_marca) VALUES (?, ?)");
            $stmt->bind_param("is", $_POST['id_locacao'], $_POST['nome']);
        }
        elseif ($etapa == 4) { // Funções
            $stmt = $conn->prepare("INSERT INTO Tipo_Funcoes (nome, salario_base_default) VALUES (?, ?)");
            $stmt->bind_param("sd", $_POST['nome'], $_POST['valor']);
        }
        elseif ($etapa == 5) { // Estadia
            $stmt = $conn->prepare("INSERT INTO Tipo_Estadia (nome, valor_unitario_default) VALUES (?, ?)");
            $stmt->bind_param("sd", $_POST['nome'], $_POST['valor']);
        }
        elseif ($etapa == 6) { // Consumo
            $stmt = $conn->prepare("INSERT INTO Tipo_Consumo (nome, valor_litro_default, consumo_kml_default) VALUES (?, ?, ?)");
            $stmt->bind_param("sdd", $_POST['nome'], $_POST['valor'], $_POST['kml']);
        }
        elseif ($etapa == 7) { // Admin
            $stmt = $conn->prepare("INSERT INTO Tipo_Custo_Admin (nome, valor_default) VALUES (?, ?)");
            $stmt->bind_param("sd", $_POST['nome'], $_POST['valor']);
        }
        elseif ($etapa == 8) { // Serviços
            $stmt = $conn->prepare("INSERT INTO Tipo_Servicos (nome, descricao) VALUES (?, ?)");
            $stmt->bind_param("ss", $_POST['nome'], $_POST['descricao']);
        }
        elseif ($etapa == 9) { // Clientes
            $stmt = $conn->prepare("INSERT INTO Clientes (nome_cliente, empresa, telefone, celular, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $_POST['nome'], $_POST['empresa'], $_POST['telefone'], $_POST['celular'], $_POST['email']);
        }

        if (isset($stmt)) $stmt->execute();
        header("Location: setup.php?etapa=$etapa"); exit;
    }

    // --- FINALIZAR (ETAPA 10) ---
    if ($etapa == 10) {
        $conn->query("UPDATE Usuarios SET setup_concluido = 1 WHERE id_usuario = " . $_SESSION['usuario_id']);
        $_SESSION['setup_concluido'] = 1;
        header("Location: index.php"); exit;
    }
}

// 4. PREPARAÇÃO DE DADOS PARA EXIBIÇÃO
$empresa_dados = $conn->query("SELECT * FROM DadosEmpresa LIMIT 1")->fetch_assoc();
$tipos_locacao = $conn->query("SELECT * FROM Tipo_Locacao ORDER BY nome");

// Configuração da Tabela de Listagem
$lista_dados = [];
$colunas_tabela = []; // Cabeçalho da tabela

if ($etapa >= 2 && $etapa <= 9) {
    $sql = "";
    switch ($etapa) {
        case 2: 
            $colunas_tabela = ["ID", "Nome", "Valor Mensal"];
            $sql = "SELECT id_locacao, nome, valor_mensal_default FROM Tipo_Locacao ORDER BY 1 DESC"; 
            break;
        case 3: 
            $colunas_tabela = ["ID", "Tipo", "Marca/Modelo"];
            $sql = "SELECT m.id_marca, tl.nome as tipo, m.nome_marca FROM Marcas m LEFT JOIN Tipo_Locacao tl ON m.id_locacao = tl.id_locacao ORDER BY 1 DESC"; 
            break;
        case 4: 
            $colunas_tabela = ["ID", "Função", "Salário Base"];
            $sql = "SELECT id_funcao, nome, salario_base_default FROM Tipo_Funcoes ORDER BY 1 DESC"; 
            break;
        case 5: 
            $colunas_tabela = ["ID", "Item", "Valor Unit."];
            $sql = "SELECT id_estadia, nome, valor_unitario_default FROM Tipo_Estadia ORDER BY 1 DESC"; 
            break;
        case 6: 
            $colunas_tabela = ["ID", "Combustível", "Preço/L"];
            $sql = "SELECT id_consumo, nome, valor_litro_default FROM Tipo_Consumo ORDER BY 1 DESC"; 
            break;
        case 7: 
            $colunas_tabela = ["ID", "Item", "Custo"];
            $sql = "SELECT id_custo_admin, nome, valor_default FROM Tipo_Custo_Admin ORDER BY 1 DESC"; 
            break;
        case 8: 
            $colunas_tabela = ["ID", "Serviço", "Descrição"];
            $sql = "SELECT id_servico, nome, descricao FROM Tipo_Servicos ORDER BY 1 DESC"; 
            break;
        case 9: 
            $colunas_tabela = ["ID", "Nome", "Empresa", "Telefone", "Celular"];
            $sql = "SELECT id_cliente, nome_cliente, empresa, telefone, celular FROM Clientes ORDER BY 1 DESC"; 
            break;
    }
    
    if ($sql) {
        $res = $conn->query($sql);
        while ($row = $res->fetch_row()) { $lista_dados[] = $row; } // fetch_row retorna array numérico
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Configuração Inicial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>.step-container { max-width: 900px; margin: 40px auto; }</style>
</head>
<body class="bg-light">

<div class="container step-container">
    <!-- CABEÇALHO -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h4 class="text-primary">Bem-vindo ao Sistema!</h4>
            <div class="progress" style="height: 20px;">
                <div class="progress-bar" style="width: <?= $progresso ?>%">Etapa <?= $etapa ?> de 10</div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><?= $etapa ?>. <?= $titulo_atual ?></h5>
        </div>
        <div class="card-body">

            <!-- ================= ETAPA 1: EMPRESA ================= -->
            <?php if ($etapa == 1): ?>
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6"><label>Empresa</label><input type="text" name="Empresa" class="form-control" value="<?= $empresa_dados['Empresa'] ?? '' ?>" required></div>
                    <div class="col-md-6"><label>CNPJ</label><input type="text" name="CNPJ" class="form-control" value="<?= $empresa_dados['CNPJ'] ?? '' ?>"></div>
                    <div class="col-md-4"><label>Tel. Fixo</label><input type="text" name="Telefone" class="form-control" value="<?= $empresa_dados['Telefone'] ?? '' ?>"></div>
                    <div class="col-md-4"><label>Celular</label><input type="text" name="Celular" class="form-control" value="<?= $empresa_dados['Celular'] ?? '' ?>"></div>
                    <div class="col-md-4"><label>WhatsApp</label><input type="text" name="Whatsapp" class="form-control" value="<?= $empresa_dados['Whatsapp'] ?? '' ?>"></div>
                    <div class="col-12"><label>Endereço</label><input type="text" name="Endereco" class="form-control" value="<?= $empresa_dados['Endereco'] ?? '' ?>"></div>
                    <div class="col-md-6"><label>Cidade</label><input type="text" name="Cidade" class="form-control" value="<?= $empresa_dados['Cidade'] ?? '' ?>"></div>
                    <div class="col-md-6"><label>Estado</label><input type="text" name="Estado" class="form-control" value="<?= $empresa_dados['Estado'] ?? '' ?>" maxlength="2"></div>
                    <div class="col-12"><hr><h6>Dados Bancários</h6></div>
                    <div class="col-md-3"><label>Banco</label><input type="text" name="Banco" class="form-control" value="<?= $empresa_dados['Banco'] ?? '' ?>"></div>
                    <div class="col-md-3"><label>Agência</label><input type="text" name="Agencia" class="form-control" value="<?= $empresa_dados['Agencia'] ?? '' ?>"></div>
                    <div class="col-md-3"><label>Conta</label><input type="text" name="Conta" class="form-control" value="<?= $empresa_dados['Conta'] ?? '' ?>"></div>
                    <div class="col-md-3"><label>PIX</label><input type="text" name="PIX" class="form-control" value="<?= $empresa_dados['PIX'] ?? '' ?>"></div>
                </div>
                <div class="mt-4 text-end"><button type="submit" class="btn btn-success">Salvar e Avançar >></button></div>
            </form>
            <?php endif; ?>

            <!-- ================= ETAPAS 2 a 9: FORMULÁRIOS ================= -->
            <?php if ($etapa >= 2 && $etapa <= 9): ?>
                <form method="POST" class="row g-2 align-items-end border p-3 bg-light rounded mb-3">
                    <input type="hidden" name="acao" value="adicionar">
                    
                    <?php if ($etapa == 2): // Tipos Locacao ?>
                        <div class="col-md-6"><label>Nome</label><input type="text" name="nome" class="form-control" required></div>
                        <div class="col-md-4"><label>Valor Mensal</label><input type="number" step="0.01" name="valor" class="form-control"></div>
                    
                    <?php elseif ($etapa == 3): // Marcas ?>
                        <div class="col-md-4"><label>Tipo</label>
                            <select name="id_locacao" class="form-select">
                                <?php while($t = $tipos_locacao->fetch_assoc()): ?><option value="<?= $t['id_locacao'] ?>"><?= $t['nome'] ?></option><?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6"><label>Marca/Modelo</label><input type="text" name="nome" class="form-control" required></div>
                    
                    <?php elseif ($etapa == 4): // Funções ?>
                        <div class="col-md-6"><label>Função</label><input type="text" name="nome" class="form-control" required></div>
                        <div class="col-md-4"><label>Salário Base</label><input type="number" step="0.01" name="valor" class="form-control"></div>
                    
                    <?php elseif ($etapa == 5): // Estadia ?>
                        <div class="col-md-6"><label>Item</label><input type="text" name="nome" class="form-control" required></div>
                        <div class="col-md-4"><label>Valor Diário</label><input type="number" step="0.01" name="valor" class="form-control"></div>
                    
                    <?php elseif ($etapa == 6): // Consumo ?>
                        <div class="col-md-4"><label>Combustível</label><input type="text" name="nome" class="form-control" required></div>
                        <div class="col-md-3"><label>Preço/L</label><input type="number" step="0.01" name="valor" class="form-control"></div>
                        <div class="col-md-3"><label>Km/L</label><input type="number" step="0.1" name="kml" class="form-control" value="10"></div>
                    
                    <?php elseif ($etapa == 7): // Admin ?>
                        <div class="col-md-6"><label>Item</label><input type="text" name="nome" class="form-control" required></div>
                        <div class="col-md-4"><label>Custo Padrão</label><input type="number" step="0.01" name="valor" class="form-control"></div>
                    
                    <?php elseif ($etapa == 8): // Serviços ?>
                        <div class="col-md-4"><label>Nome Curto</label><input type="text" name="nome" class="form-control" required></div>
                        <div class="col-md-6"><label>Descrição</label><textarea name="descricao" class="form-control" rows="1"></textarea></div>
                    
                    <?php elseif ($etapa == 9): // Clientes ?>
                        <div class="col-md-3"><label>Nome</label><input type="text" name="nome" class="form-control" required></div>
                        <div class="col-md-3"><label>Empresa</label><input type="text" name="empresa" class="form-control"></div>
                        <div class="col-md-2"><label>Tel</label><input type="text" name="telefone" class="form-control"></div>
                        <div class="col-md-2"><label>Cel</label><input type="text" name="celular" class="form-control"></div>
                        <div class="col-md-2"><label>Email</label><input type="email" name="email" class="form-control"></div>
                    <?php endif; ?>

                    <div class="col-auto"><button type="submit" class="btn btn-primary">Adicionar</button></div>
                </form>

                <!-- LISTA DE DADOS -->
                <div class="table-responsive mb-3" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <?php foreach($colunas_tabela as $col): ?><th><?= $col ?></th><?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($lista_dados)): ?>
                                <tr><td colspan="<?= count($colunas_tabela) ?>" class="text-center text-muted">Nenhum item cadastrado.</td></tr>
                            <?php else: foreach($lista_dados as $linha): ?>
                                <tr>
                                    <?php foreach($linha as $i => $valor): ?>
                                        <td>
                                            <?php 
                                            // Formatação Inteligente: Se for número e não for ID (indice 0), formata como moeda (exceto na etapa 8 e 9 que tem textos)
                                            if ($i > 0 && is_numeric($valor) && $etapa != 8 && $etapa != 9 && $etapa != 3) {
                                                echo 'R$ ' . number_format($valor, 2, ',', '.');
                                            } else {
                                                echo htmlspecialchars($valor);
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- NAVEGAÇÃO -->
                <div class="d-flex justify-content-between">
                    <?php if ($etapa > 1): ?>
                        <a href="?etapa=<?= $etapa - 1 ?>" class="btn btn-secondary">&laquo; Voltar</a>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>
                    <a href="?etapa=<?= $etapa + 1 ?>" class="btn btn-success">Próxima Etapa &raquo;</a>
                </div>
            <?php endif; ?>

            <!-- ================= ETAPA 10: FIM ================= -->
            <?php if ($etapa == 10): ?>
            <div class="text-center py-5">
                <h2 class="text-success">Configuração Concluída! 🎉</h2>
                <p class="lead">Seu sistema está pronto para uso.</p>
                <form method="POST">
                    <button type="submit" class="btn btn-primary btn-lg mt-3">Ir para o Painel 🚀</button>
                </form>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>