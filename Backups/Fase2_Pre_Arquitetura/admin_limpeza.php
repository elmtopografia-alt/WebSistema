<?php
// Nome do Arquivo: admin_limpeza.php
// Função: Limpeza profunda APENAS DO AMBIENTE DEMO.
// Correção: Permite acesso por Perfil Admin (não trava por ID).

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança: Verifica se está logado e se é ADMIN
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
    // Se não for admin, mata o processo
    die("<div style='color:red; font-weight:bold; font-family:sans-serif; text-align:center; margin-top:50px;'>⛔ ACESSO NEGADO.<br>Esta ferramenta é exclusiva para Administradores.</div>");
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmacao']) && $_POST['confirmacao'] === 'SIM') {
    
    try {
        // --- TRAVA DE SEGURANÇA: SÓ DEMO ---
        $conn = Database::getDemo();
        $nome_banco = "DEMO (Testes)";

        // COMANDOS DE LIMPEZA (Reseta tabelas e IDs)
        $sql = "
            SET FOREIGN_KEY_CHECKS = 0;
            DELETE FROM Proposta_Salarios; ALTER TABLE Proposta_Salarios AUTO_INCREMENT = 1;
            DELETE FROM Proposta_Estadia; ALTER TABLE Proposta_Estadia AUTO_INCREMENT = 1;
            DELETE FROM Proposta_Consumos; ALTER TABLE Proposta_Consumos AUTO_INCREMENT = 1;
            DELETE FROM Proposta_Locacao; ALTER TABLE Proposta_Locacao AUTO_INCREMENT = 1;
            DELETE FROM Proposta_Custos_Administrativos; ALTER TABLE Proposta_Custos_Administrativos AUTO_INCREMENT = 1;
            DELETE FROM Propostas; ALTER TABLE Propostas AUTO_INCREMENT = 1;
            DELETE FROM Clientes; ALTER TABLE Clientes AUTO_INCREMENT = 1;
            SET FOREIGN_KEY_CHECKS = 1;
        ";

        if ($conn->multi_query($sql)) {
            while ($conn->next_result()) {;} 
            $msg = "<div class='alert alert-success'>✅ O ambiente <strong>DEMO</strong> foi resetado com sucesso! Seus dados de produção estão seguros.</div>";
        } else {
            throw new Exception("Erro ao executar SQL: " . $conn->error);
        }

    } catch (Exception $e) {
        $msg = "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Limpeza Demo | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-secondary mb-5">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-recycle"></i> Manutenção do Sistema</span>
            <a href="painel.php" class="btn btn-outline-light btn-sm">Voltar</a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                
                <?php echo $msg; ?>

                <div class="card border-info shadow">
                    <div class="card-header bg-info text-white fw-bold">
                        <i class="bi bi-eraser-fill me-2"></i> Resetar Ambiente DEMO
                    </div>
                    <div class="card-body p-4 text-center">
                        <h5 class="card-title text-dark">Limpeza de Testes</h5>
                        <p class="card-text text-muted">
                            Esta ferramenta apaga todos os Clientes e Propostas gerados na <strong>Versão de Teste (Demo)</strong>.
                            <br><br>
                            <span class="badge bg-success">SEGURO:</span> Seus dados de Produção (Clientes Reais) <strong>NÃO</strong> serão afetados.
                        </p>
                        
                        <hr>

                        <form method="POST">
                            <div class="mb-4 text-start">
                                <label class="fw-bold text-dark">Confirmação</label>
                                <select name="confirmacao" class="form-select border-info" required>
                                    <option value="">Selecione...</option>
                                    <option value="SIM">SIM, limpar todos os dados da DEMO</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-info text-white w-100 fw-bold py-2">
                                <i class="bi bi-check-circle-fill"></i> EXECUTAR LIMPEZA
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>