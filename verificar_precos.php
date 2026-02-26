<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SGT - Diagnóstico de Custos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --accent: #00d2ff;
        }
        body {
            background: #0f172a;
            color: #e2e8f0;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            padding: 2rem;
        }
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        h1, h2 { color: var(--accent); }
        .table { color: #cbd5e1; }
        .table thead th { border-bottom: 2px solid var(--glass-border); color: var(--accent); }
        .table td { border-bottom: 1px solid var(--glass-border); vertical-align: middle; }
        .badge-id { background: rgba(0, 210, 255, 0.2); color: var(--accent); border: 1px solid var(--accent); }
        .btn-copy { background: var(--accent); color: #000; border: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-database-check"></i> Lista de Custos no Banco</h1>
            <a href="index.php" class="btn btn-outline-light btn-sm">Voltar ao Painel</a>
        </div>

        <?php
        require_once 'config.php';
        require_once 'ConnectionManager.php';
        $conn = ConnectionManager::get();

        $tabelas = [
            'Tipo_Funcoes' => ['label' => 'Equipe / Funções', 'icon' => 'bi-people', 'id' => 'id_funcao'],
            'Tipo_Estadia' => ['label' => 'Estadias / Alimentação', 'icon' => 'bi-house', 'id' => 'id_estadia'],
            'Tipo_Consumo' => ['label' => 'Consumos / Combustível', 'icon' => 'bi-fuel-pump', 'id' => 'id_consumo'],
            'Tipo_Locacao' => ['label' => 'Locações / Equipamentos', 'icon' => 'bi-tools', 'id' => 'id_locacao'],
            'Tipo_Custo_Admin' => ['label' => 'Custos Administrativos', 'icon' => 'bi-briefcase', 'id' => 'id_custo_admin']
        ];

        foreach ($tabelas as $table => $cfg): 
            $res = $conn->query("SELECT * FROM $table ORDER BY nome ASC");
        ?>
            <div class="glass-card">
                <h2><i class="bi <?php echo $cfg['icon']; ?>"></i> <?php echo $cfg['label']; ?></h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Nome</th>
                                <th>Valor Ref.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($res): while($row = $res->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="badge badge-id"><?php echo $row[$cfg['id']]; ?></span></td>
                                    <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                    <td>R$ <?php echo number_format($row['salario_base_default'] ?? $row['valor_unitario_default'] ?? $row['valor_litro_default'] ?? $row['valor_mensal_default'] ?? $row['valor_default'] ?? 0, 2, ',', '.'); ?></td>
                                </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="glass-card">
            <h2><i class="bi bi-tags"></i> Marcas / Modelos de Equipamentos</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Equipamento</th>
                            <th>Marca/Modelo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $res = $conn->query("SELECT m.id_marca, m.nome_marca, l.nome as equipamento 
                                            FROM Marcas m 
                                            LEFT JOIN Tipo_Locacao l ON m.id_locacao = l.id_locacao 
                                            ORDER BY equipamento, nome_marca");
                        if ($res): while($row = $res->fetch_assoc()): ?>
                            <tr>
                                <td><span class="badge badge-id"><?php echo $row['id_marca']; ?></span></td>
                                <td><?php echo htmlspecialchars($row['equipamento']); ?></td>
                                <td><?php echo htmlspecialchars($row['nome_marca']); ?></td>
                            </tr>
                        <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
