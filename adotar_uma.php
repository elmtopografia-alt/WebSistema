<?php
// Nome do Arquivo: adotar_uma.php
// Função: Ferramenta de apoio para transferir UMA proposta específica para o usuário logado.

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    die("Erro: Faça login primeiro.");
}

$meu_id = $_SESSION['usuario_id'];
$conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();

// AÇÃO: ADOTAR UMA PROPOSTA
if (isset($_GET['adotar'])) {
    $id_alvo = intval($_GET['adotar']);
    
    // 1. Pega o ID do Cliente dessa proposta
    $res = $conn->query("SELECT id_cliente FROM Propostas WHERE id_proposta = $id_alvo");
    $row = $res->fetch_assoc();
    $id_cliente_associado = $row['id_cliente'];

    // 2. Transfere a Proposta para MIM
    $conn->query("UPDATE Propostas SET id_criador = $meu_id WHERE id_proposta = $id_alvo");
    
    // 3. Transfere o Cliente associado para MIM (para não dar erro de permissão ao editar)
    if ($id_cliente_associado) {
        $conn->query("UPDATE Clientes SET id_criador = $meu_id WHERE id_cliente = $id_cliente_associado");
    }

    $msg = "✅ Proposta #$id_alvo agora pertence a você (ID $meu_id)!";
}

// LISTAGEM GERAL (Ignorando filtro de dono para você poder escolher)
$sql = "SELECT id_proposta, numero_proposta, nome_cliente_salvo, valor_final_proposta, id_criador 
        FROM Propostas 
        ORDER BY id_proposta DESC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adotar Proposta | Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Seletor de Propostas (Debug)</h5>
                <a href="painel.php" class="btn btn-sm btn-outline-light">Voltar ao Painel</a>
            </div>
            <div class="card-body">
                
                <?php if(isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>

                <p>Usuário Logado: <strong>ID <?php echo $meu_id; ?></strong></p>
                <p class="small text-muted">Escolha uma proposta abaixo para testar o sistema. Isso vai mudar o dono dela para VOCÊ.</p>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Valor</th>
                                <th>Dono Atual</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $resultado->fetch_assoc()): ?>
                                <?php 
                                    $eh_meu = ($row['id_criador'] == $meu_id);
                                    $classe = $eh_meu ? "table-success" : "";
                                ?>
                                <tr class="<?php echo $classe; ?>">
                                    <td><?php echo $row['id_proposta']; ?></td>
                                    <td><?php echo $row['numero_proposta']; ?></td>
                                    <td><?php echo $row['nome_cliente_salvo']; ?></td>
                                    <td>R$ <?php echo number_format($row['valor_final_proposta'], 2, ',', '.'); ?></td>
                                    <td>
                                        <?php echo $row['id_criador']; ?>
                                        <?php if($eh_meu) echo " <strong>(VOCÊ)</strong>"; ?>
                                    </td>
                                    <td>
                                        <?php if(!$eh_meu): ?>
                                            <a href="?adotar=<?php echo $row['id_proposta']; ?>" class="btn btn-sm btn-primary">
                                                Adotar
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-success">Já é sua</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>