
<?php
//===== editar_proposta.php =====
// Nome do Arquivo: editar_proposta.php
// Função: Interface de Edição (Revisão)
// Ação: Carrega dados do banco e envia para 'salvar_edicao_proposta.php' para criar o histórico.

session_start();
require_once 'config.php';
require_once 'db.php';

// Validação de Sessão
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$id_usuario = $_SESSION['usuario_id'];
$id_proposta = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_proposta === 0) die("ID inválido.");

try {
    // 1. Carregar Proposta (Validando Dono)
    $stmt = $conn->prepare("SELECT * FROM Propostas WHERE id_proposta = ? AND id_criador = ?");
    $stmt->bind_param('ii', $id_proposta, $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 0) {
        die("<div class='alert alert-danger m-4'>Proposta não encontrada ou acesso negado. <a href='painel.php'>Voltar</a></div>");
    }
    $prop = $res->fetch_assoc();

    // 2. Carregar Itens e Calcular Totais Iniciais (PHP)
    // Isso evita que a tela abra com "R$ 0,00" antes do JS carregar
    $total_ops = 0; 

    // Salários
    $itens_salario = [];
    $r = $conn->query("SELECT * FROM Proposta_Salarios WHERE id_proposta = $id_proposta");
    while ($row = $r->fetch_assoc()) { 
        $itens_salario[] = $row;
        $total_ops += ($row['quantidade'] * $row['salario_base'] * $row['fator_encargos'] / 30) * $row['dias'];
    }

    // Estadia
    $itens_estadia = [];
    $r = $conn->query("SELECT * FROM Proposta_Estadia WHERE id_proposta = $id_proposta");
    while ($row = $r->fetch_assoc()) { 
        $itens_estadia[] = $row;
        $total_ops += ($row['quantidade'] * $row['valor_unitario'] * $row['dias']);
    }

    // Consumos
    $itens_consumo = [];
    $r = $conn->query("SELECT * FROM Proposta_Consumos WHERE id_proposta = $id_proposta");
    while ($row = $r->fetch_assoc()) { 
        $itens_consumo[] = $row;
        $kml = $row['consumo_kml'] > 0 ? $row['consumo_kml'] : 1;
        $total_ops += ($row['km_total'] * $row['valor_litro'] / $kml) * $row['quantidade'];
    }

    // Locação
    $itens_locacao = [];
    $r = $conn->query("SELECT * FROM Proposta_Locacao WHERE id_proposta = $id_proposta");
    while ($row = $r->fetch_assoc()) { 
        $itens_locacao[] = $row;
        $total_ops += ($row['quantidade'] * $row['valor_mensal'] / 30) * $row['dias'];
    }

    // Admin
    $itens_admin = [];
    $r = $conn->query("SELECT * FROM Proposta_Custos_Administrativos WHERE id_proposta = $id_proposta");
    while ($row = $r->fetch_assoc()) { 
        $itens_admin[] = $row;
        $total_ops += ($row['quantidade'] * $row['valor']);
    }

    // 3. Fechamento Financeiro Inicial (PHP)
    $lucro_perc = floatval($prop['percentual_lucro']);
    $valor_lucro = $total_ops * ($lucro_perc / 100);
    $subtotal = $total_ops + $valor_lucro;
    $desconto = floatval($prop['valor_desconto']);
    $valor_final = $subtotal - $desconto;

    // Pagamento
    $mob_perc = floatval($prop['mobilizacao_percentual']);
    $mob_valor = $valor_final * ($mob_perc / 100);
    $rest_perc = 100 - $mob_perc;
    $rest_valor = $valor_final - $mob_valor;

    // 4. Carregar Listas Auxiliares
    $stmt_cli = $conn->prepare("SELECT id_cliente, nome_cliente, telefone, celular FROM Clientes WHERE id_criador = ? ORDER BY id_cliente DESC");
    $stmt_cli->bind_param('i', $id_usuario);
    $stmt_cli->execute();
    $clientes_res = $stmt_cli->get_result();
    
    $servicos_res = $conn->query("SELECT id_servico, nome, descricao FROM Tipo_Servicos ORDER BY nome ASC");
    
    $estados = [];
    $re = $conn->query("SELECT nome, sigla FROM estados ORDER BY nome ASC");
    if($re) while ($row = $re->fetch_assoc()) $estados[] = $row;
    
    // Arrays para o JavaScript
    $tipos_funcao = []; $r=$conn->query("SELECT * FROM Tipo_Funcoes ORDER BY nome ASC"); while($w=$r->fetch_assoc()) $tipos_funcao[]=$w;
    $tipos_estadia = []; $r=$conn->query("SELECT * FROM Tipo_Estadia ORDER BY nome ASC"); while($w=$r->fetch_assoc()) $tipos_estadia[]=$w;
    $tipos_consumo = []; $r=$conn->query("SELECT * FROM Tipo_Consumo ORDER BY nome ASC"); while($w=$r->fetch_assoc()) $tipos_consumo[]=$w;
    $tipos_locacao = []; $r=$conn->query("SELECT * FROM Tipo_Locacao ORDER BY nome ASC"); while($w=$r->fetch_assoc()) $tipos_locacao[]=$w;
    $tipos_admin = []; $r=$conn->query("SELECT * FROM Tipo_Custo_Admin ORDER BY nome ASC"); while($w=$r->fetch_assoc()) $tipos_admin[]=$w;
    
    $marcas_por_tipo = [];
    $r = $conn->query("SELECT id_marca, id_locacao, nome_marca FROM Marcas ORDER BY nome_marca ASC");
    while($row = $r->fetch_assoc()) $marcas_por_tipo[$row['id_locacao']][] = $row;

} catch (Exception $e) { die("Erro técnico: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Editar Proposta | SGT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        body { background-color: #e9ecef; color: #343a40; font-family: 'Segoe UI', sans-serif; }
        
        /* CABEÇALHO LARANJA (Modo Edição) */
        .header-edit {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            color: white; border: none; position: relative; overflow: hidden;
        }
        .header-edit::before {
            content: ''; position: absolute; top: -50%; right: -10%; width: 300px; height: 300px;
            background: rgba(255, 255, 255, 0.1); border-radius: 50%; pointer-events: none;
        }

        .app-icon {
            width: 60px; height: 60px; background: rgba(255, 255, 255, 0.2);
            border-radius: 15px; display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; color: white; backdrop-filter: blur(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.2); color: white;
            border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(5px); transition: all 0.3s;
        }
        .btn-glass:hover { background: rgba(255, 255, 255, 0.3); color: white; transform: translateY(-2px); }

        .form-section { background: #f8f9fa; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; border: 1px solid #dee2e6; }
        .section-title { border-bottom: 2px solid #dee2e6; padding-bottom: 10px; margin-bottom: 20px; font-weight: 700; color: #495057; font-size: 1.1rem; }
        
        /* Box de Totais */
        .total-box { background: #fff3cd; padding: 20px; border-radius: 8px; text-align: right; border: 1px solid #ffeeba; }
        .total-linha { font-weight: bold; background-color: #fff; text-align: right; vertical-align: middle; }

        label { font-weight: 600; color: #495057; margin-bottom: 5px; font-size: 0.9rem; }
        .form-control, .form-select { background-color: #fff; border-color: #ced4da; }
        
        @media (max-width: 768px) {
            .table-responsive { overflow-x: auto; margin-bottom: 10px; }
            .header-edit .d-flex { flex-direction: column; align-items: flex-start !important; }
            .header-edit .btn-glass { width: 100%; margin-top: 15px; }
        }
    </style>
</head>
<body>
    
    <div class="container mt-4 mb-5">
        
        <!-- CABEÇALHO -->
        <div class="card shadow-lg mb-4 rounded-4 overflow-hidden header-edit">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                
                <div class="d-flex align-items-center gap-3">
                    <div class="app-icon"><i class="bi bi-pencil-square"></i></div>
                    <div>
                        <div class="text-white-50 fw-bold small text-uppercase ls-1">Modo de Edição</div>
                        <h2 class="fw-bold mb-0 text-white"><?= htmlspecialchars($prop['numero_proposta']) ?></h2>
                        <p class="text-white-50 mb-0 small mt-1">Ao salvar, uma revisão (Rv) será gerada.</p>
                    </div>
                </div>
                
                <!-- Botão Voltar ao Painel -->
                <div>
                    <a href="painel.php" class="btn btn-glass btn-sm px-4 py-2 fw-bold rounded-pill">
                        <i class="bi bi-arrow-left me-2"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="progress" style="height: 4px; background: rgba(0,0,0,0.1);">
                <div class="progress-bar bg-warning" role="progressbar" style="width: 100%"></div>
            </div>
        </div>

        <!-- FORMULÁRIO -->
        <!-- Action aponta para salvar_edicao_proposta.php -->
        <form action="salvar_edicao_proposta.php" method="POST" id="form-proposta">
            
            <input type="hidden" name="id_proposta_original" value="<?= $id_proposta ?>">

            <!-- 1. DADOS GERAIS -->
            <div class="form-section">
                <h5 class="section-title"><i class="bi bi-person-badge me-2 text-warning"></i>Dados do Cliente e Obra</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Cliente</label>
                        <select class="form-select" name="id_cliente" id="id_cliente" required>
                            <option value="">Selecione...</option>
                            <?php if($clientes_res) mysqli_data_seek($clientes_res, 0); while ($c = $clientes_res->fetch_assoc()): ?>
                                <option value="<?= $c['id_cliente'] ?>" <?= ($c['id_cliente']==$prop['id_cliente'])?'selected':'' ?> 
                                    data-nome="<?= htmlspecialchars($c['nome_cliente']) ?>" data-celular="<?= htmlspecialchars($c['celular']) ?>" data-telefone="<?= htmlspecialchars($c['telefone']) ?>">
                                    <?= htmlspecialchars($c['nome_cliente']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Serviço</label>
                        <select class="form-select" name="id_servico" id="id_servico" required>
                            <option value="">Selecione...</option>
                            <?php if($servicos_res) mysqli_data_seek($servicos_res, 0); while ($s = $servicos_res->fetch_assoc()): ?>
                                <option value="<?= $s['id_servico'] ?>" <?= ($s['id_servico']==$prop['id_servico'])?'selected':'' ?> data-descricao="<?= htmlspecialchars($s['descricao']) ?>">
                                    <?= htmlspecialchars($s['nome']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-12"><label>Finalidade / Escopo</label><textarea class="form-control" name="finalidade" id="finalidade" rows="2"><?= htmlspecialchars($prop['finalidade']) ?></textarea></div>
                    
                    <div class="col-md-4"><label>Contato Obra</label><input type="text" name="contato_obra" id="contato_obra" class="form-control" value="<?= htmlspecialchars($prop['contato_obra']) ?>"></div>
                    <div class="col-md-4"><label>Tipo Levantamento</label><input type="text" name="tipo_levantamento" id="tipo_levantamento" class="form-control" value="<?= htmlspecialchars($prop['tipo_levantamento']) ?>"></div>
                    <div class="col-md-4"><label>Área (m²/ha)</label><input type="text" name="area" class="form-control" value="<?= htmlspecialchars($prop['area_obra']) ?>"></div>
                    
                    <div class="col-md-4"><label>Endereço</label><input type="text" name="endereco" class="form-control" value="<?= htmlspecialchars($prop['endereco_obra']) ?>"></div>
                    <div class="col-md-3"><label>Bairro</label><input type="text" name="bairro" class="form-control" value="<?= htmlspecialchars($prop['bairro_obra']) ?>"></div>
                    <div class="col-md-3"><label>Cidade</label><input type="text" name="cidade" class="form-control" value="<?= htmlspecialchars($prop['cidade_obra']) ?>"></div>
                    <div class="col-md-2"><label>UF</label>
                        <select name="estado" class="form-select">
                            <?php foreach($estados as $e): ?><option value="<?= $e['sigla'] ?>" <?= ($e['sigla']==$prop['estado_obra'])?'selected':'' ?>><?= $e['sigla'] ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4"><label>Dias Campo</label><input type="number" name="dias_campo" class="form-control" value="<?= $prop['dias_campo'] ?>"></div>
                    <div class="col-md-4"><label>Dias Escritório</label><input type="number" name="dias_escritorio" class="form-control" value="<?= $prop['dias_escritorio'] ?>"></div>
                    <div class="col-md-4"><label>Prazo Total</label><input type="text" name="prazo_execucao" class="form-control" value="<?= htmlspecialchars($prop['prazo_execucao']) ?>"></div>
                </div>
            </div>

            <!-- 2. CUSTOS -->
            <div class="form-section">
                <h5 class="section-title"><i class="bi bi-calculator me-2 text-warning"></i>Composição de Custos</h5>
                
                <!-- Salários -->
                <div class="mb-4">
                    <h6 class="fw-bold text-secondary">Equipe e Salários</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm border-secondary" style="min-width: 600px;">
                            <thead class="table-secondary"><tr><th>Função</th><th width="80">Qtd</th><th>Base</th><th width="80">Enc(%)</th><th width="80">Dias</th><th>Total</th><th width="40"></th></tr></thead>
                            <tbody id="tbody-salarios">
                                <?php foreach($itens_salario as $it): 
                                    $enc_view = ($it['fator_encargos'] - 1) * 100;
                                    $tot = ($it['quantidade'] * $it['salario_base'] * $it['fator_encargos'] / 30) * $it['dias']; ?>
                                <tr class="linha-calculo" data-tipo="salario">
                                    <td><select name="salario_id_funcao[]" class="form-select form-select-sm salario-select">
                                        <?php foreach($tipos_funcao as $t): ?><option value="<?= $t['id_funcao'] ?>" <?= ($t['id_funcao']==$it['id_funcao'])?'selected':'' ?> data-valor="<?= $t['salario_base_default'] ?>"><?= $t['nome'] ?></option><?php endforeach; ?>
                                    </select><input type="hidden" name="salario_nome[]" class="input-nome" value="<?= htmlspecialchars($it['funcao']) ?>"></td>
                                    <td><input type="number" name="salario_qtd[]" class="form-control form-control-sm qtd" value="<?= $it['quantidade'] ?>" step="0.1"></td>
                                    <td><input type="number" name="salario_valor[]" class="form-control form-control-sm valor" value="<?= $it['salario_base'] ?>" step="0.01"></td>
                                    <td><input type="number" name="encargos[]" class="form-control form-control-sm encargos" value="<?= round($enc_view,2) ?>" step="0.1"></td>
                                    <td><input type="number" name="salario_dias[]" class="form-control form-control-sm dias" value="<?= $it['dias'] ?>" step="0.1"></td>
                                    <td class="total-linha text-end">R$ <?= number_format($tot,2,',','.') ?></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold" id="add-salario"><i class="bi bi-plus-lg"></i> Adicionar</button>
                    <span class="float-end fw-bold text-dark" id="total-secao-salarios">R$ <?= number_format(0,2,',','.') ?></span>
                </div>

                <!-- Estadia -->
                <div class="mb-4">
                    <h6 class="fw-bold text-secondary">Estadia</h6>
                    <div class="table-responsive"><table class="table table-bordered table-sm border-secondary"><thead><tr class="table-secondary"><th>Tipo</th><th width="80">Qtd</th><th>Valor</th><th width="80">Dias</th><th>Total</th><th width="40"></th></tr></thead><tbody id="tbody-estadia">
                        <?php foreach($itens_estadia as $it): $tot=$it['quantidade']*$it['valor_unitario']*$it['dias']; ?>
                        <tr class="linha-calculo" data-tipo="estadia">
                            <td><select name="estadia_id[]" class="form-select form-select-sm estadia-select"><?php foreach($tipos_estadia as $t): ?><option value="<?= $t['id_estadia'] ?>" <?= ($t['id_estadia']==$it['id_estadia'])?'selected':'' ?> data-valor="<?= $t['valor_unitario_default'] ?>"><?= $t['nome'] ?></option><?php endforeach; ?></select><input type="hidden" name="estadia_nome[]" class="input-nome" value="<?= htmlspecialchars($it['tipo']) ?>"></td>
                            <td><input type="number" name="estadia_qtd[]" class="form-control form-control-sm qtd" value="<?= $it['quantidade'] ?>"></td>
                            <td><input type="number" name="estadia_valor[]" class="form-control form-control-sm valor" value="<?= $it['valor_unitario'] ?>"></td>
                            <td><input type="number" name="estadia_dias[]" class="form-control form-control-sm dias" value="<?= $it['dias'] ?>"></td>
                            <td class="total-linha text-end">R$ <?= number_format($tot,2,',','.') ?></td>
                            <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody></table></div>
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold" id="add-estadia"><i class="bi bi-plus-lg"></i> Adicionar</button>
                    <span class="float-end fw-bold text-dark" id="total-secao-estadia">R$ 0,00</span>
                </div>

                <!-- Consumo -->
                <div class="mb-4">
                    <h6 class="fw-bold text-secondary">Combustível</h6>
                    <div class="table-responsive"><table class="table table-bordered table-sm border-secondary"><thead><tr class="table-secondary"><th>Tipo</th><th width="80">Qtd</th><th>Km/L</th><th>R$/L</th><th>Km Total</th><th>Total</th><th width="40"></th></tr></thead><tbody id="tbody-consumos">
                        <?php foreach($itens_consumo as $it): $kml=$it['consumo_kml']>0?$it['consumo_kml']:1; $tot=($it['km_total']*$it['valor_litro']/$kml)*$it['quantidade']; ?>
                        <tr class="linha-calculo" data-tipo="consumo">
                            <td><select name="consumo_id[]" class="form-select form-select-sm consumo-select"><?php foreach($tipos_consumo as $t): ?><option value="<?= $t['id_consumo'] ?>" <?= ($t['id_consumo']==$it['id_consumo'])?'selected':'' ?> data-valor-litro="<?= $t['valor_litro_default'] ?>" data-consumo-kml="<?= $t['consumo_kml_default'] ?>"><?= $t['nome'] ?></option><?php endforeach; ?></select><input type="hidden" name="consumo_nome[]" class="input-nome" value="<?= htmlspecialchars($it['tipo']) ?>"></td>
                            <td><input type="number" name="consumo_qtd[]" class="form-control form-control-sm qtd" value="<?= $it['quantidade'] ?>"></td>
                            <td><input type="number" name="consumo_kml[]" class="form-control form-control-sm kml" value="<?= $it['consumo_kml'] ?>"></td>
                            <td><input type="number" name="consumo_litro[]" class="form-control form-control-sm litro" value="<?= $it['valor_litro'] ?>"></td>
                            <td><input type="number" name="consumo_km_total[]" class="form-control form-control-sm km-total" value="<?= $it['km_total'] ?>"></td>
                            <td class="total-linha text-end">R$ <?= number_format($tot,2,',','.') ?></td>
                            <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody></table></div>
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold" id="add-consumo"><i class="bi bi-plus-lg"></i> Adicionar</button>
                    <span class="float-end fw-bold text-dark" id="total-secao-consumos">R$ 0,00</span>
                </div>

                <!-- Locação -->
                <div class="mb-4">
                    <h6 class="fw-bold text-secondary">Locação</h6>
                    <div class="table-responsive"><table class="table table-bordered table-sm border-secondary"><thead><tr class="table-secondary"><th>Item</th><th>Marca</th><th width="80">Qtd</th><th>Mensal</th><th width="80">Dias</th><th>Total</th><th width="40"></th></tr></thead><tbody id="tbody-locacao">
                        <?php foreach($itens_locacao as $it): $tot=($it['quantidade']*$it['valor_mensal']/30)*$it['dias']; ?>
                        <tr class="linha-calculo" data-tipo="locacao">
                            <td><select name="locacao_id[]" class="form-select form-select-sm locacao-select"><?php foreach($tipos_locacao as $t): ?><option value="<?= $t['id_locacao'] ?>" <?= ($t['id_locacao']==$it['id_locacao'])?'selected':'' ?> data-valor="<?= $t['valor_mensal_default'] ?>"><?= $t['nome'] ?></option><?php endforeach; ?></select><input type="hidden" name="locacao_nome[]" class="input-nome" value=""></td>
                            <td><select name="locacao_id_marca[]" class="form-select form-select-sm marca-select"><option value="">-</option><?php if(isset($marcas_por_tipo[$it['id_locacao']])): foreach($marcas_por_tipo[$it['id_locacao']] as $m): ?><option value="<?= $m['id_marca'] ?>" <?= ($m['id_marca']==$it['id_marca'])?'selected':'' ?>><?= $m['nome_marca'] ?></option><?php endforeach; endif; ?></select></td>
                            <td><input type="number" name="locacao_qtd[]" class="form-control form-control-sm qtd" value="<?= $it['quantidade'] ?>"></td>
                            <td><input type="number" name="locacao_valor[]" class="form-control form-control-sm valor" value="<?= $it['valor_mensal'] ?>"></td>
                            <td><input type="number" name="locacao_dias[]" class="form-control form-control-sm dias" value="<?= $it['dias'] ?>"></td>
                            <td class="total-linha text-end">R$ <?= number_format($tot,2,',','.') ?></td>
                            <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody></table></div>
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold" id="add-locacao"><i class="bi bi-plus-lg"></i> Adicionar</button>
                    <span class="float-end fw-bold text-dark" id="total-secao-locacao">R$ 0,00</span>
                </div>

                <!-- Admin -->
                <div class="mb-4">
                    <h6 class="fw-bold text-secondary">Admin</h6>
                    <div class="table-responsive"><table class="table table-bordered table-sm border-secondary"><thead><tr class="table-secondary"><th>Item</th><th width="80">Qtd</th><th>Valor</th><th>Total</th><th width="40"></th></tr></thead><tbody id="tbody-admin">
                        <?php foreach($itens_admin as $it): $tot=$it['quantidade']*$it['valor']; ?>
                        <tr class="linha-calculo" data-tipo="admin">
                            <td><select name="admin_id[]" class="form-select form-select-sm admin-select"><?php foreach($tipos_admin as $t): ?><option value="<?= $t['id_custo_admin'] ?>" <?= ($t['id_custo_admin']==$it['id_custo_admin'])?'selected':'' ?> data-valor="<?= $t['valor_default'] ?>"><?= $t['nome'] ?></option><?php endforeach; ?></select><input type="hidden" name="admin_nome[]" class="input-nome" value="<?= htmlspecialchars($it['tipo']) ?>"></td>
                            <td><input type="number" name="admin_qtd[]" class="form-control form-control-sm qtd" value="<?= $it['quantidade'] ?>"></td>
                            <td><input type="number" name="admin_valor[]" class="form-control form-control-sm valor" value="<?= $it['valor'] ?>"></td>
                            <td class="total-linha text-end">R$ <?= number_format($tot,2,',','.') ?></td>
                            <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody></table></div>
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold" id="add-admin"><i class="bi bi-plus-lg"></i> Adicionar</button>
                    <span class="float-end fw-bold text-dark" id="total-secao-admin">R$ 0,00</span>
                </div>
            </div>

            <!-- TOTAIS -->
            <div class="form-section">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-secondary">Condições de Pagamento</h6>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-secondary text-white">Entrada %</span>
                            <input type="number" name="mobilizacao_percentual" id="mobilizacao_percentual" class="form-control" value="<?= $mob_perc ?>" step="1">
                            <!-- PREENCHIDO PELO PHP -->
                            <input type="text" name="mobilizacao_valor" id="mobilizacao_valor_display" class="form-control bg-light fw-bold" readonly value="R$ <?= number_format($mob_valor, 2, ',', '.') ?>">
                        </div>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-secondary text-white">Restante %</span>
                            <span class="form-control bg-light" id="restante_percentual_display"><?= $rest_perc ?></span>
                            <!-- PREENCHIDO PELO PHP -->
                            <input type="text" name="restante_valor" id="restante_valor_display" class="form-control bg-light fw-bold" readonly value="R$ <?= number_format($rest_valor, 2, ',', '.') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="total-box shadow-sm mt-3 mt-md-0">
                            <!-- PREENCHIDO PELO PHP -->
                            <p class="mb-2">Custo Operacional: <strong id="total-custos-geral">R$ <?= number_format($total_ops, 2, ',', '.') ?></strong></p>
                            
                            <div class="input-group input-group-sm mb-2 justify-content-end">
                                <span class="input-group-text fw-bold">Lucro %</span>
                                <input type="number" name="percentual_lucro" id="percentual_lucro" class="form-control text-end" style="max-width: 80px" value="<?= $lucro_perc ?>" step="0.1">
                                <!-- PREENCHIDO PELO PHP -->
                                <span class="input-group-text text-success fw-bold" id="valor-lucro">+ R$ <?= number_format($valor_lucro, 2, ',', '.') ?></span>
                            </div>
                            
                            <div class="input-group input-group-sm mb-2 justify-content-end">
                                <span class="input-group-text fw-bold text-danger">Desconto R$</span>
                                <input type="number" name="valor_desconto" id="valor_desconto" class="form-control text-end text-danger fw-bold" style="max-width: 100px" value="<?= $desconto ?>" step="0.01">
                            </div>
                            
                            <hr>
                            <!-- PREENCHIDO PELO PHP -->
                            <h3 class="text-primary mt-2 mb-0" id="valor-final-proposta">R$ <?= number_format($valor_final, 2, ',', '.') ?></h3>
                            <small class="text-muted">Valor Final da Revisão</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Campos Ocultos para enviar ao backend -->
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

            <div class="text-center py-4">
                <button type="submit" class="btn btn-warning btn-lg px-5 shadow-lg fw-bold text-dark">
                    <i class="bi bi-check2-square me-2"></i> Alterar Proposta
                </button>
            </div>
        </form>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <!-- JS de Cálculo Embutido (Garante funcionamento) -->
    <script>
        // Dados PHP para JS
        const opcoesFuncaoHtml = `<?php foreach ($tipos_funcao as $i): ?><option value="<?php echo $i['id_funcao']; ?>" data-valor="<?php echo $i['salario_base_default']; ?>"><?php echo htmlspecialchars($i['nome']); ?></option><?php endforeach; ?>`;
        const opcoesEstadiaHtml = `<?php foreach ($tipos_estadia as $i): ?><option value="<?php echo $i['id_estadia']; ?>" data-valor="<?php echo $i['valor_unitario_default']; ?>"><?php echo htmlspecialchars($i['nome']); ?></option><?php endforeach; ?>`;
        const opcoesConsumoHtml = `<?php foreach ($tipos_consumo as $i): ?><option value="<?php echo $i['id_consumo']; ?>" data-valor-litro="<?php echo $i['valor_litro_default']; ?>" data-consumo-kml="<?php echo $i['consumo_kml_default']; ?>"><?php echo htmlspecialchars($i['nome']); ?></option><?php endforeach; ?>`;
        const opcoesLocacaoHtml = `<?php foreach ($tipos_locacao as $i): ?><option value="<?php echo $i['id_locacao']; ?>" data-valor="<?php echo $i['valor_mensal_default']; ?>"><?php echo htmlspecialchars($i['nome']); ?></option><?php endforeach; ?>`;
        const opcoesAdminHtml = `<?php foreach ($tipos_admin as $i): ?><option value="<?php echo $i['id_custo_admin']; ?>" data-valor="<?php echo $i['valor_default']; ?>"><?php echo htmlspecialchars($i['nome']); ?></option><?php endforeach; ?>`;
        const marcasPorTipo = <?php echo json_encode($marcas_por_tipo); ?>;

        $(document).ready(function() {
            $('#id_cliente').select2({ theme: 'bootstrap-5' });
            $('.phone').mask('(00) 0000-0000');
            $('.celular').mask('(00) 00000-0000');

            $('#id_cliente').on('select2:select', function(e){
                var el = $(this).find(':selected');
                var ctt = el.data('celular') || el.data('telefone') || '';
                $('#contato_obra').val(el.data('nome').split(' ')[0] + (ctt ? ' - ' + ctt : ''));
            });

            // --- FUNÇÕES DE CÁLCULO ---
            function formatMoney(val) { return val.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); }
            function calcRow(row) {
                let tipo = row.data('tipo');
                let qtd = parseFloat(row.find('.qtd').val()) || 0;
                let val = parseFloat(row.find('.valor').val()) || 0;
                let tot = 0;
                if(tipo==='salario') {
                    let enc = parseFloat(row.find('.encargos').val()) || 0;
                    let dias = parseFloat(row.find('.dias').val()) || 0;
                    tot = (qtd * val * (1+(enc/100)) / 30) * dias;
                } else if(tipo==='estadia' || tipo==='locacao') {
                    let dias = parseFloat(row.find('.dias').val()) || 0;
                    tot = (tipo==='locacao') ? (qtd*val/30)*dias : qtd*val*dias;
                } else if(tipo==='consumo') {
                    let kml = parseFloat(row.find('.kml').val()) || 1;
                    let lit = parseFloat(row.find('.litro').val()) || 0;
                    let kmt = parseFloat(row.find('.km-total').val()) || 0;
                    tot = (kmt*lit/ (kml>0?kml:1) )*qtd;
                } else if(tipo==='admin') { tot = qtd*val; }
                row.find('.total-linha').text(formatMoney(tot));
                return tot;
            }

            function calcAll() {
                let tSal=0, tEst=0, tCon=0, tLoc=0, tAdm=0;
                $('#tbody-salarios tr').each(function(){ tSal+=calcRow($(this)); });
                $('#tbody-estadia tr').each(function(){ tEst+=calcRow($(this)); });
                $('#tbody-consumos tr').each(function(){ tCon+=calcRow($(this)); });
                $('#tbody-locacao tr').each(function(){ tLoc+=calcRow($(this)); });
                $('#tbody-admin tr').each(function(){ tAdm+=calcRow($(this)); });

                $('#total-secao-salarios').text(formatMoney(tSal)); $('#hidden_total_custos_salarios').val(tSal);
                $('#total-secao-estadia').text(formatMoney(tEst)); $('#hidden_total_custos_estadia').val(tEst);
                $('#total-secao-consumos').text(formatMoney(tCon)); $('#hidden_total_custos_consumos').val(tCon);
                $('#total-secao-locacao').text(formatMoney(tLoc)); $('#hidden_total_custos_locacao').val(tLoc);
                $('#total-secao-admin').text(formatMoney(tAdm)); $('#hidden_total_custos_admin').val(tAdm);

                let op = tSal+tEst+tCon+tLoc+tAdm;
                $('#total-custos-geral').text(formatMoney(op));
                
                let lucP = parseFloat($('#percentual_lucro').val()) || 0;
                let vLuc = op * (lucP/100);
                $('#valor-lucro').text('+ '+formatMoney(vLuc));
                $('#hidden_valor_lucro').val(vLuc);
                
                let sub = op + vLuc;
                $('#hidden_subtotal_com_lucro').val(sub);
                
                let desc = parseFloat($('#valor_desconto').val()) || 0;
                let fin = sub - desc;
                $('#valor-final-proposta').text(formatMoney(fin));
                $('#hidden_valor_final_proposta').val(fin);

                let mobP = parseFloat($('#mobilizacao_percentual').val()) || 0;
                let mobV = fin * (mobP/100);
                $('#mobilizacao_valor_display').val(formatMoney(mobV));
                $('#hidden_mobilizacao_valor').val(mobV);
                
                let resP = 100 - mobP;
                let resV = fin - mobV;
                $('#restante_percentual_display').text(resP);
                $('#restante_valor_display').val(formatMoney(resV));
                $('#hidden_restante_percentual').val(resP);
                $('#hidden_restante_valor').val(resV);
            }

            $('body').on('input', 'input', calcAll);
            $('body').on('change', 'select', function(){
                let opt = $(this).find(':selected');
                let row = $(this).closest('tr');
                if(opt.data('valor')) row.find('.valor').val(opt.data('valor'));
                if(opt.data('valor-litro')) row.find('.litro').val(opt.data('valor-litro'));
                if(opt.data('consumo-kml')) row.find('.kml').val(opt.data('consumo-kml'));
                if(row.find('.input-nome').length) row.find('.input-nome').val(opt.text().trim());
                calcAll();
            });
            $('body').on('click', '.remove-row', function(){ $(this).closest('tr').remove(); calcAll(); });

            function addR(id, opts, tipo) {
                let h = `<tr class="linha-calculo" data-tipo="${tipo}">`;
                if(tipo==='salario') h+=`<td><select name="salario_id_funcao[]" class="form-select form-select-sm">${opts}</select><input type="hidden" name="salario_nome[]" class="input-nome"></td><td><input type="number" name="salario_qtd[]" class="form-control form-control-sm qtd" value="1"></td><td><input type="number" name="salario_valor[]" class="form-control form-control-sm valor" value="0"></td><td><input type="number" name="encargos[]" class="form-control form-control-sm encargos" value="67"></td><td><input type="number" name="salario_dias[]" class="form-control form-control-sm dias" value="1"></td>`;
                else if(tipo==='estadia') h+=`<td><select name="estadia_id[]" class="form-select form-select-sm">${opts}</select><input type="hidden" name="estadia_nome[]" class="input-nome"></td><td><input type="number" name="estadia_qtd[]" class="form-control form-control-sm qtd" value="1"></td><td><input type="number" name="estadia_valor[]" class="form-control form-control-sm valor" value="0"></td><td><input type="number" name="estadia_dias[]" class="form-control form-control-sm dias" value="1"></td>`;
                else if(tipo==='consumo') h+=`<td><select name="consumo_id[]" class="form-select form-select-sm">${opts}</select><input type="hidden" name="consumo_nome[]" class="input-nome"></td><td><input type="number" name="consumo_qtd[]" class="form-control form-control-sm qtd" value="1"></td><td><input type="number" name="consumo_kml[]" class="form-control form-control-sm kml" value="10"></td><td><input type="number" name="consumo_litro[]" class="form-control form-control-sm litro" value="0"></td><td><input type="number" name="consumo_km_total[]" class="form-control form-control-sm km-total" value="100"></td>`;
                else if(tipo==='locacao') h+=`<td><select name="locacao_id[]" class="form-select form-select-sm">${opts}</select><input type="hidden" name="locacao_nome[]" class="input-nome"></td><td><select name="locacao_id_marca[]" class="form-select form-select-sm marca-select"><option value="">-</option></select></td><td><input type="number" name="locacao_qtd[]" class="form-control form-control-sm qtd" value="1"></td><td><input type="number" name="locacao_valor[]" class="form-control form-control-sm valor" value="0"></td><td><input type="number" name="locacao_dias[]" class="form-control form-control-sm dias" value="1"></td>`;
                else if(tipo==='admin') h+=`<td><select name="admin_id[]" class="form-select form-select-sm">${opts}</select><input type="hidden" name="admin_nome[]" class="input-nome"></td><td><input type="number" name="admin_qtd[]" class="form-control form-control-sm qtd" value="1"></td><td><input type="number" name="admin_valor[]" class="form-control form-control-sm valor" value="0"></td>`;
                
                h+=`<td class="total-linha text-end">R$ 0,00</td><td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="bi bi-trash"></i></button></td></tr>`;
                $('#'+id).append(h);
                $('#'+id+' tr:last select').first().trigger('change');
            }

            $('#add-salario').click(()=>addR('tbody-salarios', opcoesFuncaoHtml, 'salario'));
            $('#add-estadia').click(()=>addR('tbody-estadia', opcoesEstadiaHtml, 'estadia'));
            $('#add-consumo').click(()=>addR('tbody-consumos', opcoesConsumoHtml, 'consumo'));
            $('#add-locacao').click(()=>addR('tbody-locacao', opcoesLocacaoHtml, 'locacao'));
            $('#add-admin').click(()=>addR('tbody-admin', opcoesAdminHtml, 'admin'));

            // Aguarda renderização para calcular
            setTimeout(calcAll, 300);
        });
    </script>
</body>
</html>