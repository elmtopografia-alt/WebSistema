<?php
// ARQUIVO: editar_proposta.php
// VERSÃO: PREMIUM WIZARD (Refatorado)

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
$id_usuario = $_SESSION['usuario_id'];
$id_proposta = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_proposta === 0) die("ID inválido.");

try {
    // 1. Carregar Proposta
    $stmt = $conn->prepare("SELECT * FROM Propostas WHERE id_proposta = ? AND id_criador = ?");
    $stmt->bind_param('ii', $id_proposta, $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) die("Proposta não encontrada.");
    $prop = $res->fetch_assoc();

    // 2. Carregar Itens
    $itens_salario = []; $r = $conn->query("SELECT * FROM Proposta_Salarios WHERE id_proposta = $id_proposta"); while($w=$r->fetch_assoc()) $itens_salario[]=$w;
    $itens_estadia = []; $r = $conn->query("SELECT * FROM Proposta_Estadia WHERE id_proposta = $id_proposta"); while($w=$r->fetch_assoc()) $itens_estadia[]=$w;
    $itens_consumo = []; $r = $conn->query("SELECT * FROM Proposta_Consumos WHERE id_proposta = $id_proposta"); while($w=$r->fetch_assoc()) $itens_consumo[]=$w;
    $itens_locacao = []; $r = $conn->query("SELECT * FROM Proposta_Locacao WHERE id_proposta = $id_proposta"); while($w=$r->fetch_assoc()) $itens_locacao[]=$w;
    $itens_admin = []; $r = $conn->query("SELECT * FROM Proposta_Custos_Administrativos WHERE id_proposta = $id_proposta"); while($w=$r->fetch_assoc()) $itens_admin[]=$w;

    // 3. Carregar Listas Auxiliares
    $stmt_cli = $conn->prepare("SELECT id_cliente, nome_cliente, telefone, celular FROM Clientes WHERE id_criador = ? ORDER BY nome_cliente ASC");
    $stmt_cli->bind_param('i', $id_usuario);
    $stmt_cli->execute();
    $clientes_res = $stmt_cli->get_result();
    
    $servicos_res = $conn->query("SELECT id_servico, nome, descricao FROM Tipo_Servicos ORDER BY nome ASC");
    
    $estados = [];
    $re = $conn->query("SELECT nome, sigla FROM estados ORDER BY nome ASC");
    if($re) while ($row = $re->fetch_assoc()) $estados[] = $row;
    
    // Arrays para JS/PHP
    $tipos_funcao = []; $r=$conn->query("SELECT * FROM Tipo_Funcoes ORDER BY nome ASC"); while($w=$r->fetch_assoc()) $tipos_funcao[]=$w;
    $tipos_estadia = []; $r=$conn->query("SELECT * FROM Tipo_Estadia ORDER BY nome ASC"); while($w=$r->fetch_assoc()) $tipos_estadia[]=$w;
    $tipos_consumo = []; $r=$conn->query("SELECT * FROM Tipo_Consumo ORDER BY nome ASC"); while($w=$r->fetch_assoc()) $tipos_consumo[]=$w;
    $tipos_locacao = []; $r=$conn->query("SELECT * FROM Tipo_Locacao ORDER BY nome ASC"); while($w=$r->fetch_assoc()) $tipos_locacao[]=$w;
    $tipos_admin = []; $r=$conn->query("SELECT * FROM Tipo_Custo_Admin ORDER BY nome ASC"); while($w=$r->fetch_assoc()) $tipos_admin[]=$w;
    
    $marcas_por_tipo = [];
    $r = $conn->query("SELECT id_marca, id_locacao, nome_marca FROM Marcas ORDER BY nome_marca ASC");
    while($row = $r->fetch_assoc()) $marcas_por_tipo[$row['id_locacao']][] = $row;

} catch (Exception $e) { die("Erro: " . $e->getMessage()); }

// Helper para Área
$area_val = $prop['area_obra'];
$area_unid = 'm²';
if(preg_match('/([\d\.,]+)\s*(.*)/', $prop['area_obra'], $matches)) {
    $area_val = $matches[1];
    $area_unid = trim($matches[2]) ?: 'm²';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proposta | SGT Premium</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root { --primary: #2563eb; --primary-dark: #1e40af; --secondary: #64748b; --bg-body: #f1f5f9; --card-bg: #ffffff; --border-color: #e2e8f0; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-body); color: #1e293b; }
        .navbar { background: white; border-bottom: 1px solid var(--border-color); }
        .navbar-brand { font-weight: 700; color: var(--primary-dark); }
        .wizard-container { max-width: 1000px; margin: 2rem auto; background: var(--card-bg); border-radius: 16px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); overflow: hidden; display: flex; flex-direction: column; min-height: 80vh; }
        .wizard-progress { background: #f8fafc; padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .step-indicator { display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--secondary); font-weight: 500; }
        .step-indicator.active { color: var(--primary); font-weight: 700; }
        .step-number { width: 28px; height: 28px; border-radius: 50%; background: #e2e8f0; color: var(--secondary); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; }
        .step-indicator.active .step-number { background: var(--primary); color: white; }
        .step-indicator.completed .step-number { background: #10b981; color: white; }
        .wizard-content { padding: 2rem; flex: 1; }
        .step-panel { display: none; animation: fadeIn 0.3s ease; }
        .step-panel.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .section-header { margin-bottom: 2rem; }
        .section-header h2 { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
        .form-label { font-weight: 500; font-size: 0.9rem; color: #334155; }
        .form-control, .form-select { border-radius: 8px; border-color: #cbd5e1; padding: 0.6rem 1rem; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .cost-list { display: flex; flex-direction: column; gap: 1rem; }
        .cost-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 1rem; transition: all 0.2s; }
        .cost-item:hover { border-color: var(--primary); background: white; box-shadow: 0 2px 4px rgb(0 0 0 / 0.05); }
        .cost-icon { width: 40px; height: 40px; background: #eff6ff; color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .cost-details { flex: 1; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; align-items: center; gap: 1rem; }
        .cost-total { font-weight: 700; color: var(--primary-dark); min-width: 100px; text-align: right; }
        .btn-add-item { border-style: dashed; border-width: 2px; width: 100%; margin-top: 1rem; }
        .wizard-footer { padding: 1.5rem 2rem; border-top: 1px solid var(--border-color); background: #f8fafc; display: flex; justify-content: space-between; align-items: center; }

    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="painel.php"><i class="bi bi-grid-fill me-2"></i> SGT Premium</a>
            <div class="ms-auto d-flex gap-2">
                <span class="badge bg-warning text-dark align-self-center">Editando Proposta #<?= $prop['numero_proposta'] ?></span>
                <a href="painel.php" class="btn btn-outline-secondary btn-sm">Cancelar</a>
            </div>
        </div>
    </nav>

    <div class="wizard-container">
        <!-- Progress -->
        <div class="wizard-progress">
            <div class="step-indicator active" id="ind-1"><div class="step-number">1</div> Cliente & Obra</div>
            <div class="step-indicator" id="ind-2"><div class="step-number">2</div> Escopo</div>
            <div class="step-indicator" id="ind-3"><div class="step-number">3</div> Custos</div>
            <div class="step-indicator" id="ind-4"><div class="step-number">4</div> Fechamento</div>
        </div>

        <form action="salvar_edicao_proposta.php" method="POST" id="form-proposta" class="d-flex flex-column flex-grow-1" novalidate>
            <input type="hidden" name="id_proposta_original" value="<?= $id_proposta ?>">
            
            <div class="wizard-content">
                
                <!-- STEP 1: Cliente -->
                <div class="step-panel active" id="step-1">
                    <div class="section-header"><h2>Dados Iniciais</h2><p>Selecione o cliente e o local da obra.</p></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <select class="form-select" name="id_cliente" id="id_cliente" required>
                                <option value="">Selecione...</option>
                                <?php if($clientes_res) mysqli_data_seek($clientes_res, 0); while ($c = $clientes_res->fetch_assoc()): 
                                    $primeiroNome = explode(' ', trim($c['nome_cliente']))[0];
                                    $ct = $primeiroNome . ' contato: ' . ($c['celular'] ? $c['celular'] : $c['telefone']); ?>
                                    <option value="<?= $c['id_cliente'] ?>" <?= ($c['id_cliente']==$prop['id_cliente'])?'selected':'' ?> data-contato="<?= htmlspecialchars($ct) ?>"><?= htmlspecialchars($c['nome_cliente']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contato na Obra</label>
                            <input type="text" name="contato_obra" id="contato_obra" class="form-control" value="<?= htmlspecialchars($prop['contato_obra']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Endereço da Obra</label>
                            <input type="text" name="endereco" class="form-control" value="<?= htmlspecialchars($prop['endereco_obra']) ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Bairro</label>
                            <input type="text" name="bairro" class="form-control" value="<?= htmlspecialchars($prop['bairro_obra']) ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="cidade" class="form-control" value="<?= htmlspecialchars($prop['cidade_obra']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">UF</label>
                            <select name="estado" class="form-select">
                                <?php foreach($estados as $e): ?><option value="<?= $e['sigla'] ?>" <?= ($e['sigla']==$prop['estado_obra'])?'selected':'' ?>><?= $e['sigla'] ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Escopo -->
                <div class="step-panel" id="step-2">
                    <div class="section-header"><h2>Escopo do Serviço</h2><p>Defina o que será realizado e os prazos.</p></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Serviço</label>
                            <select class="form-select" name="id_servico" id="id_servico" required>
                                <option value="">Selecione...</option>
                                <?php if($servicos_res) mysqli_data_seek($servicos_res, 0); while ($s = $servicos_res->fetch_assoc()): ?>
                                    <option value="<?= $s['id_servico'] ?>" <?= ($s['id_servico']==$prop['id_servico'])?'selected':'' ?> data-descricao="<?= htmlspecialchars($s['descricao']) ?>"><?= htmlspecialchars($s['nome']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Título do Levantamento</label>
                            <input type="text" name="tipo_levantamento" id="tipo_levantamento" class="form-control" value="<?= htmlspecialchars($prop['tipo_levantamento']) ?>">
                        </div>
                        
                        <div class="col-12">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Descrição do Escopo / Finalidade</label>
                                    <textarea class="form-control" name="finalidade" id="finalidade" rows="3"><?= htmlspecialchars($prop['finalidade']) ?></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Área Aproximada</label>
                                    <div class="input-group">
                                        <input type="text" name="area" class="form-control" value="<?= htmlspecialchars($area_val) ?>">
                                        <select name="unidade_area" class="form-select" style="max-width: 90px;">
                                            <option value="m²" <?= ($area_unid=='m²')?'selected':'' ?>>m²</option>
                                            <option value="ha" <?= ($area_unid=='ha')?'selected':'' ?>>ha</option>
                                            <option value="m" <?= ($area_unid=='m')?'selected':'' ?>>m</option>
                                            <option value="unid" <?= ($area_unid=='unid')?'selected':'' ?>>unid</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Dias em Campo</label>
                            <input type="number" name="dias_campo" id="dias_campo" class="form-control" value="<?= $prop['dias_campo'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Dias Escritório</label>
                            <input type="number" name="dias_escritorio" id="dias_escritorio" class="form-control" value="<?= $prop['dias_escritorio'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prazo de Entrega</label>
                            <input type="text" name="prazo_execucao" id="prazo_execucao" class="form-control" value="<?= htmlspecialchars($prop['prazo_execucao']) ?>">
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Custos -->
                <div class="step-panel" id="step-3">
                    <div class="section-header"><h2>Composição de Custos</h2><p>Adicione os recursos necessários para o projeto.</p></div>
                    
                    <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-equipe" type="button">Equipe</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-estadia" type="button">Estadia</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-consumo" type="button">Combustível</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-equip" type="button">Equipamentos</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-admin" type="button">Admin</button></li>
                    </ul>

                    <div class="tab-content">
                        <!-- Equipe -->
                        <div class="tab-pane fade show active" id="tab-equipe">
                            <div id="list-salarios" class="cost-list">
                                <?php foreach($itens_salario as $it): ?>
                                <div class="cost-item">
                                    <div class="cost-icon"><i class="bi bi-person"></i></div>
                                    <div class="cost-details">
                                        <div><label class="small text-muted">Função</label><select name="salario_id_funcao[]" class="form-select form-select-sm"><option value="">Selecione</option><?php foreach($tipos_funcao as $t): ?><option value="<?= $t['id_funcao'] ?>" <?= ($t['id_funcao']==$it['id_funcao'])?'selected':'' ?> data-valor="<?= $t['salario_base_default'] ?>"><?= $t['nome'] ?></option><?php endforeach; ?></select><input type="hidden" name="salario_nome[]" class="item-nome-hidden" value="<?= htmlspecialchars($it['funcao']) ?>"></div>
                                        <div><label class="small text-muted">Qtd</label><input type="number" name="salario_qtd[]" class="form-control form-control-sm recalc" value="<?= $it['quantidade'] ?>"></div>
                                        <div><label class="small text-muted">Salário Base</label><input type="number" name="salario_valor[]" class="form-control form-control-sm recalc autofill-valor" step="0.01" value="<?= $it['salario_base'] ?>"></div>
                                        <div><label class="small text-muted">Enc %</label><input type="number" name="encargos[]" class="form-control form-control-sm recalc" value="<?= round(($it['fator_encargos']-1)*100, 2) ?>"></div>
                                        <div><label class="small text-muted">Dias</label><input type="number" name="salario_dias[]" class="form-control form-control-sm recalc" value="<?= $it['dias'] ?>"></div>
                                    </div>
                                    <div class="cost-total total-linha">0,00</div>
                                    <button type="button" class="btn btn-link text-danger remove-row"><i class="bi bi-trash"></i></button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-add-item" id="add-salario"><i class="bi bi-plus-lg"></i> Adicionar Profissional</button>
                        </div>

                        <!-- Estadia -->
                        <div class="tab-pane fade" id="tab-estadia">
                            <div id="list-estadia" class="cost-list">
                                <?php foreach($itens_estadia as $it): ?>
                                <div class="cost-item">
                                    <div class="cost-icon"><i class="bi bi-house"></i></div>
                                    <div class="cost-details">
                                        <div><label class="small text-muted">Tipo</label><select name="estadia_id[]" class="form-select form-select-sm"><option value="">Selecione</option><?php foreach($tipos_estadia as $t): ?><option value="<?= $t['id_estadia'] ?>" <?= ($t['id_estadia']==$it['id_estadia'])?'selected':'' ?> data-valor="<?= $t['valor_unitario_default'] ?>"><?= $t['nome'] ?></option><?php endforeach; ?></select><input type="hidden" name="estadia_nome[]" class="item-nome-hidden" value="<?= htmlspecialchars($it['tipo']) ?>"></div>
                                        <div><label class="small text-muted">Qtd</label><input type="number" name="estadia_qtd[]" class="form-control form-control-sm recalc" value="<?= $it['quantidade'] ?>"></div>
                                        <div><label class="small text-muted">Valor R$</label><input type="number" name="estadia_valor[]" class="form-control form-control-sm recalc autofill-valor" step="0.01" value="<?= $it['valor_unitario'] ?>"></div>
                                        <div><label class="small text-muted">Dias</label><input type="number" name="estadia_dias[]" class="form-control form-control-sm recalc" value="<?= $it['dias'] ?>"></div>
                                        <div></div>
                                    </div>
                                    <div class="cost-total total-linha">0,00</div>
                                    <button type="button" class="btn btn-link text-danger remove-row"><i class="bi bi-trash"></i></button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-add-item" id="add-estadia"><i class="bi bi-plus-lg"></i> Adicionar Item</button>
                        </div>

                        <!-- Consumo -->
                        <div class="tab-pane fade" id="tab-consumo">
                            <div id="list-consumos" class="cost-list">
                                <?php foreach($itens_consumo as $it): ?>
                                <div class="cost-item">
                                    <div class="cost-icon"><i class="bi bi-fuel-pump"></i></div>
                                    <div class="cost-details">
                                        <div><label class="small text-muted">Combustível</label><select name="consumo_id[]" class="form-select form-select-sm"><option value="">Selecione</option><?php foreach($tipos_consumo as $t): ?><option value="<?= $t['id_consumo'] ?>" <?= ($t['id_consumo']==$it['id_consumo'])?'selected':'' ?> data-valor-litro="<?= $t['valor_litro_default'] ?>" data-consumo-kml="<?= $t['consumo_kml_default'] ?>"><?= $t['nome'] ?></option><?php endforeach; ?></select><input type="hidden" name="consumo_nome[]" class="item-nome-hidden" value="<?= htmlspecialchars($it['tipo']) ?>"></div>
                                        <div><label class="small text-muted">Qtd Veic</label><input type="number" name="consumo_qtd[]" class="form-control form-control-sm recalc" value="<?= $it['quantidade'] ?>"></div>
                                        <div><label class="small text-muted">Km/L</label><input type="number" name="consumo_kml[]" class="form-control form-control-sm recalc" step="0.1" value="<?= $it['consumo_kml'] ?>"></div>
                                        <div><label class="small text-muted">R$/L</label><input type="number" name="consumo_litro[]" class="form-control form-control-sm recalc" step="0.01" value="<?= $it['valor_litro'] ?>"></div>
                                        <div><label class="small text-muted">Km Total</label><input type="number" name="consumo_km_total[]" class="form-control form-control-sm recalc" value="<?= $it['km_total'] ?>"></div>
                                    </div>
                                    <div class="cost-total total-linha">0,00</div>
                                    <button type="button" class="btn btn-link text-danger remove-row"><i class="bi bi-trash"></i></button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-add-item" id="add-consumo"><i class="bi bi-plus-lg"></i> Adicionar Combustível</button>
                        </div>

                        <!-- Equipamentos -->
                        <div class="tab-pane fade" id="tab-equip">
                            <div id="list-locacao" class="cost-list">
                                <?php foreach($itens_locacao as $it): ?>
                                <div class="cost-item">
                                    <div class="cost-icon"><i class="bi bi-tools"></i></div>
                                    <div class="cost-details" style="grid-template-columns: 3fr 2.5fr 1fr 1.5fr 1fr;">
                                        <div><label class="small text-muted">Equipamento</label><select name="locacao_id[]" class="form-select form-select-sm"><option value="">Selecione</option><?php foreach($tipos_locacao as $t): ?><option value="<?= $t['id_locacao'] ?>" <?= ($t['id_locacao']==$it['id_locacao'])?'selected':'' ?> data-valor="<?= $t['valor_mensal_default'] ?>"><?= $t['nome'] ?></option><?php endforeach; ?></select><input type="hidden" name="locacao_nome[]" class="item-nome-hidden" value=""></div>
                                        <div class="div-marca"><label class="small text-muted">Marca</label><select name="locacao_id_marca[]" class="form-select form-select-sm"><option value="">-</option><?php if(isset($marcas_por_tipo[$it['id_locacao']])): foreach($marcas_por_tipo[$it['id_locacao']] as $m): ?><option value="<?= $m['id_marca'] ?>" <?= ($m['id_marca']==$it['id_marca'])?'selected':'' ?>><?= $m['nome_marca'] ?></option><?php endforeach; endif; ?></select></div>
                                        <div><label class="small text-muted">Qtd</label><input type="number" name="locacao_qtd[]" class="form-control form-control-sm recalc" value="<?= $it['quantidade'] ?>"></div>
                                        <div><label class="small text-muted">Mensal R$</label><input type="number" name="locacao_valor[]" class="form-control form-control-sm recalc autofill-valor" step="0.01" value="<?= $it['valor_mensal'] ?>"></div>
                                        <div><label class="small text-muted">Dias</label><input type="number" name="locacao_dias[]" class="form-control form-control-sm recalc" value="<?= $it['dias'] ?>"></div>
                                    </div>
                                    <div class="cost-total total-linha">0,00</div>
                                    <button type="button" class="btn btn-link text-danger remove-row"><i class="bi bi-trash"></i></button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-add-item" id="add-locacao"><i class="bi bi-plus-lg"></i> Adicionar Equipamento</button>
                        </div>

                        <!-- Admin -->
                        <div class="tab-pane fade" id="tab-admin">
                            <div id="list-admin" class="cost-list">
                                <?php foreach($itens_admin as $it): ?>
                                <div class="cost-item">
                                    <div class="cost-icon"><i class="bi bi-briefcase"></i></div>
                                    <div class="cost-details">
                                        <div><label class="small text-muted">Item</label><select name="admin_id[]" class="form-select form-select-sm"><option value="">Selecione</option><?php foreach($tipos_admin as $t): ?><option value="<?= $t['id_custo_admin'] ?>" <?= ($t['id_custo_admin']==$it['id_custo_admin'])?'selected':'' ?> data-valor="<?= $t['valor_default'] ?>"><?= $t['nome'] ?></option><?php endforeach; ?></select><input type="hidden" name="admin_nome[]" class="item-nome-hidden" value="<?= htmlspecialchars($it['tipo']) ?>"></div>
                                        <div><label class="small text-muted">Qtd</label><input type="number" name="admin_qtd[]" class="form-control form-control-sm recalc" value="<?= $it['quantidade'] ?>"></div>
                                        <div><label class="small text-muted">Valor Unit.</label><input type="number" name="admin_valor[]" class="form-control form-control-sm recalc autofill-valor" step="0.01" value="<?= $it['valor'] ?>"></div>
                                        <div></div><div></div>
                                    </div>
                                    <div class="cost-total total-linha">0,00</div>
                                    <button type="button" class="btn btn-link text-danger remove-row"><i class="bi bi-trash"></i></button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-add-item" id="add-admin"><i class="bi bi-plus-lg"></i> Adicionar Custo Admin</button>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Fechamento -->
                <div class="step-panel" id="step-4">
                    <div class="section-header"><h2>Fechamento da Proposta</h2><p>Revise os valores e defina as condições comerciais.</p></div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Resumo de Custos</h5>
                                    <div class="d-flex justify-content-between mb-2"><span>Equipe:</span> <strong id="resumo-salarios">R$ 0,00</strong></div>
                                    <div class="d-flex justify-content-between mb-2"><span>Estadia:</span> <strong id="resumo-estadia">R$ 0,00</strong></div>
                                    <div class="d-flex justify-content-between mb-2"><span>Combustível:</span> <strong id="resumo-consumos">R$ 0,00</strong></div>
                                    <div class="d-flex justify-content-between mb-2"><span>Equipamentos:</span> <strong id="resumo-locacao">R$ 0,00</strong></div>
                                    <div class="d-flex justify-content-between border-top pt-2"><span>Total Custos:</span> <strong id="total-custos-geral" class="text-danger">R$ 0,00</strong></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label">Margem de Lucro (%)</label>
                                <div class="input-group">
                                    <input type="number" name="percentual_lucro" id="percentual_lucro" class="form-control form-control-lg" value="<?= $prop['percentual_lucro'] ?>" step="0.1">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="text-end text-success fw-bold mt-1" id="valor-lucro">+ R$ 0,00</div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Desconto (R$)</label>
                                <input type="number" name="valor_desconto" id="valor_desconto" class="form-control" value="<?= $prop['valor_desconto'] ?>" step="0.01">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-3">Condições de Pagamento</h5>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="small text-muted">Entrada %</label>
                                    <input type="number" name="mobilizacao_percentual" id="mobilizacao_percentual" class="form-control" value="<?= $prop['mobilizacao_percentual'] ?>">
                                </div>
                                <div class="col-6">
                                    <label class="small text-muted">Valor Entrada</label>
                                    <input type="text" id="mobilizacao_valor_display" class="form-control bg-light" readonly>
                                </div>
                                <div class="col-6 mt-2">
                                    <label class="small text-muted">Restante %</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light" id="restante_percentual_display">70</span>
                                        <span class="input-group-text bg-light">%</span>
                                    </div>
                                </div>
                                <div class="col-6 mt-2">
                                    <label class="small text-muted">Valor Restante</label>
                                    <input type="text" id="restante_valor_display" class="form-control bg-light" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="small text-muted">VALOR FINAL</div>
                            <div class="display-4 fw-bold text-primary" id="valor-final-proposta">R$ 0,00</div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Navigation -->
            <div class="wizard-footer">
                <button type="button" class="btn btn-outline-secondary px-4" id="btn-prev" style="display:none;">Voltar</button>
                <div class="ms-auto">
                    <button type="button" class="btn btn-primary px-5" id="btn-next">Próximo <i class="bi bi-arrow-right ms-2"></i></button>
                    <button type="submit" class="btn btn-success px-5" id="btn-finish" style="display:none;"><i class="bi bi-check-lg me-2"></i> Salvar Revisão</button>
                </div>
            </div>
        <!-- Inputs Hidden para Cálculos -->
        <input type="hidden" name="total_custos_salarios" id="hidden_total_custos_salarios">
        <input type="hidden" name="total_custos_estadia" id="hidden_total_custos_estadia">
        <input type="hidden" name="total_custos_consumos" id="hidden_total_custos_consumos">
        <input type="hidden" name="total_custos_locacao" id="hidden_total_custos_locacao">
        <input type="hidden" name="total_custos_admin" id="hidden_total_custos_admin">
        <input type="hidden" name="valor_lucro" id="hidden_valor_lucro">
        <input type="hidden" name="subtotal_com_lucro" id="hidden_subtotal_com_lucro">
        <input type="hidden" name="valor_final_proposta" id="hidden_valor_final_proposta">
        <input type="hidden" name="mobilizacao_valor" id="hidden_mobilizacao_valor">
        <input type="hidden" name="restante_percentual" id="hidden_restante_percentual">
        <input type="hidden" name="restante_valor" id="hidden_restante_valor">
        <input type="hidden" name="form_complete" value="1">
        </form>

    </div>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Dados PHP para JS
        const opcoesFuncaoHtml = `<?php foreach ($tipos_funcao as $item): ?><option value="<?php echo $item['id_funcao']; ?>" data-valor="<?php echo $item['salario_base_default']; ?>"><?php echo htmlspecialchars($item['nome']); ?></option><?php endforeach; ?>`;
        const opcoesEstadiaHtml = `<?php foreach ($tipos_estadia as $item): ?><option value="<?php echo $item['id_estadia']; ?>" data-valor="<?php echo $item['valor_unitario_default']; ?>"><?php echo htmlspecialchars($item['nome']); ?></option><?php endforeach; ?>`;
        const opcoesConsumoHtml = `<?php foreach ($tipos_consumo as $item): ?><option value="<?php echo $item['id_consumo']; ?>" data-valor-litro="<?php echo $item['valor_litro_default']; ?>" data-consumo-kml="<?php echo $item['consumo_kml_default']; ?>"><?php echo htmlspecialchars($item['nome']); ?></option><?php endforeach; ?>`;
        const opcoesLocacaoHtml = `<?php foreach ($tipos_locacao as $item): ?><option value="<?php echo $item['id_locacao']; ?>" data-valor="<?php echo $item['valor_mensal_default']; ?>"><?php echo htmlspecialchars($item['nome']); ?></option><?php endforeach; ?>`;
        const opcoesAdminHtml = `<?php foreach ($tipos_admin as $item): ?><option value="<?php echo $item['id_custo_admin']; ?>" data-valor="<?php echo $item['valor_default']; ?>"><?php echo htmlspecialchars($item['nome']); ?></option><?php endforeach; ?>`;
        const marcasPorTipo = <?php echo json_encode($marcas_por_tipo); ?>;
    </script>
    <script src="calculos.js?v=<?php echo time(); ?>"></script>
    <script>
        $(document).ready(function() {
            // Select2
            $('#id_cliente').select2({ theme: 'bootstrap-5', width: '100%' });
            
            // Auto-fill Cliente
            $('#id_cliente').on('select2:select', function(e){
                $('#contato_obra').val($(this).find(':selected').data('contato'));
            });
            
            // Auto-fill Serviço
            $('#id_servico').on('change', function(){
                var txt = $(this).find(':selected').text();
                var desc = $(this).find(':selected').data('descricao');
                
                // Sempre atualiza a descrição ao trocar o serviço
                if(desc) $('#finalidade').val(desc);
                
                $('#tipo_levantamento').val('Levantamento ' + txt);
            });

            // Wizard Logic
            let currentStep = 1;
            const totalSteps = 4;

            function showStep(step) {
                $('.step-panel').removeClass('active');
                $('#step-' + step).addClass('active');
                
                $('.step-indicator').removeClass('active completed');
                for(let i=1; i<step; i++) { $('#ind-'+i).addClass('completed'); }
                $('#ind-'+step).addClass('active');

                // Buttons
                if(step === 1) $('#btn-prev').hide(); else $('#btn-prev').show();
                if(step === totalSteps) {
                    $('#btn-next').hide();
                    $('#btn-finish').show();
                } else {
                    $('#btn-next').show();
                    $('#btn-finish').hide();
                }
                currentStep = step;
            }

            $('#btn-next').click(function() {
                if(currentStep === 1 && !$('#id_cliente').val()) { alert('Selecione um cliente'); return; }
                if(currentStep === 2 && !$('#id_servico').val()) { alert('Selecione um serviço'); return; }
                if(currentStep < totalSteps) showStep(currentStep + 1);
            });

            $('#btn-prev').click(function() {
                if(currentStep > 1) showStep(currentStep - 1);
            });

            // Submit Handler
            $('#btn-finish').click(function(e) {
                e.preventDefault();
                var btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Salvando...');
                $('#form-proposta').submit();
            });

            // Inicializar Listeners para Itens Existentes
            function initExistingItems() {
                document.querySelectorAll('.cost-item select').forEach(sel => {
                    // Update hidden name on change
                    sel.addEventListener('change', function() {
                        const item = this.closest('.cost-item');
                        const nameInput = item.querySelector('.item-nome-hidden');
                        if (nameInput && this.options[this.selectedIndex]) {
                            nameInput.value = this.options[this.selectedIndex].text;
                        }
                        
                        // Auto-fill values (same logic as addRow)
                        const opt = this.options[this.selectedIndex];
                        const val = opt.getAttribute('data-valor');
                        if(val) {
                           const valInput = item.querySelector('.autofill-valor');
                           if(valInput) valInput.value = val;
                        }
                        // Specific for Consumo
                        const lit = opt.getAttribute('data-valor-litro');
                        const kml = opt.getAttribute('data-consumo-kml');
                        if(lit) item.querySelectorAll('input')[3].value = lit;
                        if(kml) item.querySelectorAll('input')[2].value = kml;
                        
                        // Trigger recalculation
                        // (We don't call recalcularTudo here directly to avoid loops, but the global input listener will catch value changes)
                    });

                    // Locacao Brand Logic
                    if(sel.name === 'locacao_id[]') {
                        sel.addEventListener('change', function(){
                            const idLoc = this.value;
                            const div = this.closest('.cost-item');
                            const divMarca = div.querySelector('.div-marca');
                            let htmlMarca = '<label class="small text-muted">Marca</label><select name="locacao_id_marca[]" class="form-select form-select-sm"><option value="">-</option>';
                            if(marcasPorTipo[idLoc]) {
                                marcasPorTipo[idLoc].forEach(m => {
                                    htmlMarca += `<option value="${m.id_marca}">${m.nome_marca}</option>`;
                                });
                            }
                            htmlMarca += '</select>';
                            divMarca.innerHTML = htmlMarca;
                        });
                    }
                });
                
                // Forçar recálculo inicial
                if(typeof recalcularTudo === 'function') recalcularTudo();
            }

            initExistingItems();
        });
    </script>
</body>
</html>