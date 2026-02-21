<?php

/**
 * ARQUIVO: criar_proposta_new.php
 * VERSÃO: Wizard simplificado → Editor Dinâmico (Fluxo Novo)
 * 
 * FLUXO:
 * 1. Usuário preenche dados básicos (cliente, local, serviço)
 * 2. Envia para editor_dinamico.php
 * 3. Editor permite refinar textos de cada seção
 * 4. Gera documento final
 * 
 * NOTA: O arquivo criar_proposta.php original continua funcionando normalmente.
 */

require_once 'session_validator.php';
require_once 'config.php';
require_once 'db.php';

$id_usuario = $_SESSION['usuario_id'] ?? 0;
if (!$id_usuario) {
    header('Location: login.php');
    exit;
}

// Conexão
$ambiente = $_SESSION['ambiente'] ?? 'producao';
$conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

// Carregar Clientes
$stmt_cli = $conn->prepare("SELECT id_cliente, nome_cliente, telefone, celular FROM Clientes WHERE id_criador = ? ORDER BY nome_cliente ASC LIMIT 500");
$stmt_cli->bind_param('i', $id_usuario);
$stmt_cli->execute();
$clientes_res = $stmt_cli->get_result();
$clientes = [];
while ($row = $clientes_res->fetch_assoc()) {
    $clientes[] = $row;
}

// Carregar Serviços
$servicos = [];
$result = $conn->query("SELECT id_servico, nome, descricao FROM Tipo_Servicos ORDER BY nome ASC");
while ($row = $result->fetch_assoc()) {
    $servicos[] = $row;
}

// Carregar Estados
$estados = [];
$result = $conn->query("SELECT sigla, nome FROM estados ORDER BY nome ASC");
while ($row = $result->fetch_assoc()) {
    $estados[] = $row;
}

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Proposta (Editor Avançado) | SGT</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --success: #10b981;
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: #1e293b;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
        }

        .card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            border: 1px solid var(--border-color);
        }

        .card-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .card-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .card-header p {
            color: #64748b;
            font-size: 0.9rem;
        }

        .badge-new {
            display: inline-block;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            font-size: 0.7rem;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            font-size: 0.875rem;
            color: #334155;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: white;
            transition: all 0.2s;
            color: #0f172a;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-row {
            display: grid;
            gap: 1rem;
            grid-template-columns: 1fr 1fr;
        }

        @media (max-width: 576px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            width: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-outline {
            background: white;
            border: 1px solid var(--border-color);
            color: #64748b;
        }

        .btn-outline:hover {
            background: #f8fafc;
            color: #334155;
        }

        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .actions .btn-outline {
            flex: 0 0 auto;
        }

        .actions .btn-primary {
            flex: 1;
        }

        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            color: #1e40af;
        }

        .info-box i {
            margin-right: 0.5rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <span class="badge-new">✨ NOVO FLUXO</span>
                <h1>Criar Proposta Avançada</h1>
                <p>Preencha os dados básicos e personalize os textos no Editor Dinâmico</p>
            </div>

            <div class="info-box">
                <i class="bi bi-info-circle"></i>
                <strong>Como funciona:</strong> Após preencher, você será levado ao Editor Dinâmico onde poderá personalizar cada seção da proposta antes de gerar o documento.
            </div>

            <form action="editor_dinamico.php" method="POST" id="form-proposta-new">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="fluxo_novo" value="1">

                <!-- Cliente -->
                <div class="form-group">
                    <label class="form-label">Cliente *</label>
                    <select name="id_cliente" id="id_cliente" class="form-select" required>
                        <option value="">Selecione o cliente...</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>"><?= htmlspecialchars($c['nome_cliente']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tipo de Serviço -->
                <div class="form-group">
                    <label class="form-label">Tipo de Serviço *</label>
                    <select name="id_servico" id="id_servico" class="form-select" required>
                        <option value="">Selecione o serviço...</option>
                        <?php foreach ($servicos as $s): ?>
                            <option value="<?= $s['id_servico'] ?>"><?= htmlspecialchars($s['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Finalidade -->
                <div class="form-group">
                    <label class="form-label">Finalidade do Serviço</label>
                    <textarea name="finalidade" id="finalidade" class="form-control" rows="2" placeholder="Ex: Levantamento para projeto de loteamento..."></textarea>
                </div>

                <!-- Local da Obra -->
                <div class="form-group">
                    <label class="form-label">Endereço da Obra</label>
                    <input type="text" name="endereco" id="endereco" class="form-control" placeholder="Rua, número, complemento...">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Cidade</label>
                        <input type="text" name="cidade" id="cidade" class="form-control" placeholder="Cidade">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select name="estado" id="estado" class="form-select">
                            <option value="">UF</option>
                            <?php foreach ($estados as $e): ?>
                                <option value="<?= $e['sigla'] ?>"><?= $e['sigla'] ?> - <?= $e['nome'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Área da Obra</label>
                    <input type="text" name="area" id="area" class="form-control" placeholder="Ex: 500 m² ou 10 hectares">
                </div>

                <!-- Valores (simplificado) -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Valor da Proposta (R$)</label>
                        <input type="text" name="valor_final_proposta" id="valor_final_proposta" class="form-control" placeholder="Ex: 5.000,00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prazo de Entrega</label>
                        <input type="text" name="prazo_execucao" id="prazo_execucao" class="form-control" placeholder="Ex: 15 dias úteis">
                    </div>
                </div>

                <!-- Data -->
                <div class="form-group">
                    <label class="form-label">Data da Proposta</label>
                    <input type="date" name="data" id="data" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>

                <!-- Ações -->
                <div class="actions">
                    <a href="painel.php" class="btn btn-outline">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-magic"></i> Ir para Editor Dinâmico
                    </button>
                </div>
            </form>
        </div>

        <p style="text-align: center; margin-top: 1.5rem; color: #94a3b8; font-size: 0.8rem;">
            <a href="criar_proposta.php" style="color: #64748b;">Prefere o método tradicional? Clique aqui</a>
        </p>
    </div>
</body>

</html>