<?php
// ARQUIVO: criar_proposta.php
// VERSÃO: DEBUG (Sem Iframe, Sem JS de Sucesso falso)

session_start();
require_once 'config.php';
require_once 'db.php';
require_once 'valida_demo.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
$id_usuario = $_SESSION['usuario_id'];

// Carrega dados para os selects
try {
    // Busca Clientes do usuário
    $stmt_cli = $conn->prepare("SELECT id_cliente, nome_cliente, telefone, celular FROM Clientes WHERE id_criador = ? ORDER BY nome_cliente ASC");
    $stmt_cli->bind_param('i', $id_usuario);
    $stmt_cli->execute();
    $clientes_res = $stmt_cli->get_result();
    
    // Tabelas Globais
    $servicos_res = $conn->query("SELECT id_servico, nome, descricao FROM Tipo_Servicos ORDER BY nome ASC");
    
    $estados = [];
    $result_estados = $conn->query("SELECT nome, sigla FROM estados ORDER BY nome ASC");
    if($result_estados) while ($row = $result_estados->fetch_assoc()) { $estados[] = $row; }
    
    // Arrays para JS
    $tipos_funcao = []; $result = $conn->query("SELECT * FROM Tipo_Funcoes ORDER BY nome ASC");
    if($result) while ($row = $result->fetch_assoc()) { $tipos_funcao[] = $row; }
    
    $tipos_estadia = []; $result = $conn->query("SELECT * FROM Tipo_Estadia ORDER BY nome ASC");
    if($result) while ($row = $result->fetch_assoc()) { $tipos_estadia[] = $row; }
    
    $tipos_consumo = []; $result = $conn->query("SELECT * FROM Tipo_Consumo ORDER BY nome ASC");
    if($result) while ($row = $result->fetch_assoc()) { $tipos_consumo[] = $row; }
    
    $tipos_locacao = []; $result = $conn->query("SELECT * FROM Tipo_Locacao ORDER BY nome ASC");
    if($result) while ($row = $result->fetch_assoc()) { $tipos_locacao[] = $row; }
    
    $tipos_admin = []; $result = $conn->query("SELECT * FROM Tipo_Custo_Admin ORDER BY nome ASC");
    if($result) while ($row = $result->fetch_assoc()) { $tipos_admin[] = $row; }
    
    $marcas_por_tipo = [];
    $result_marcas = $conn->query("SELECT id_marca, id_locacao, nome_marca FROM Marcas ORDER BY nome_marca ASC");
    if($result_marcas) while ($row = $result_marcas->fetch_assoc()) {
        $marcas_por_tipo[$row['id_locacao']][] = $row;
    }
} catch (Exception $e) { 
    die("Erro ao carregar dados: " . $e->getMessage()); 
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
       <!-- CSS 5 -->
    <link rel="stylesheet" href="assets/css/estilo.css">
    <meta charset="UTF-8"><title>Nova Proposta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .form-section { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; border: 1px solid #e2e8f0; }
        .section-title { border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 20px; font-weight: bold; color: #2c3e50; }
        .total-box { background: #e8f4fd; padding: 15px; border-radius: 8px; text-align: right; border: 1px solid #b6d4fe; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4 mb-5">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Nova Proposta</h3>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>

        <!-- REMOVIDO: target="iframe-download" -->
        <form action="salvar_proposta.php" method="POST" id="form-proposta">
            
            <!-- Seção 1: Cliente e Obra -->
            <div class="form-section">
                <h5 class="section-title">Dados Iniciais</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Cliente</label>
                        <select class="form-select" name="id_cliente" id="id_cliente" required>
                            <option value="">Selecione...</option>
                            <?php if($clientes_res) mysqli_data_seek($clientes_res, 0); while ($c = $clientes_res->fetch_assoc()): 
                                $ct = explode(' ', $c['nome_cliente'])[0] . ' ' . $c['telefone']; ?>
                                <option value="<?= $c['id_cliente'] ?>" data-contato="<?= htmlspecialchars($ct) ?>"><?= htmlspecialchars($c['nome_cliente']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Serviço</label>
                        <select class="form-select" name="id_servico" id="id_servico" required>
                            <option value="">Selecione...</option>
                            <?php if($servicos_res) mysqli_data_seek($servicos_res, 0); while ($s = $servicos_res->fetch_assoc()): ?>
                                <option value="<?= $s['id_servico'] ?>" data-descricao="<?= htmlspecialchars($s['descricao']) ?>"><?= htmlspecialchars($s['nome']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label>Finalidade / Escopo</label>
                        <textarea class="form-control" name="finalidade" id="finalidade" rows="2"></textarea>
                    </div>
                    
                    <div class="col-md-4"><label>Contato Obra</label><input type="text" name="contato_obra" id="contato_obra" class="form-control"></div>
                    <div class="col-md-4"><label>Tipo Levantamento</label><input type="text" name="tipo_levantamento" id="tipo_levantamento" class="form-control"></div>
                    <div class="col-md-4"><label>Área (m²/ha)</label><input type="text" name="area" class="form-control"></div>
                    
                    <div class="col-md-4"><label>Endereço</label><input type="text" name="endereco" class="form-control"></div>
                    <div class="col-md-3"><label>Bairro</label><input type="text" name="bairro" class="form-control"></div>
                    <div class="col-md-3"><label>Cidade</label><input type="text" name="cidade" class="form-control"></div>
                    <div class="col-md-2"><label>UF</label>
                        <select name="estado" class="form-select">
                            <?php foreach($estados as $e): ?>
                                <option value="<?= $e['sigla'] ?>" <?= $e['sigla']=='MG'?'selected':'' ?>><?= $e['sigla'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4"><label>Dias Campo</label><input type="number" name="dias_campo" class="form-control" value="1"></div>
                    <div class="col-md-4"><label>Dias Escritório</label><input type="number" name="dias_escritorio" class="form-control" value="1"></div>
                    <div class="col-md-4"><label>Prazo Total</label><input type="text" name="prazo_execucao" class="form-control" value="5 dias"></div>
                </div>
            </div>

            <!-- Seção 2: Custos (Tabelas) -->
            <div class="form-section">
                <h5 class="section-title">Composição de Custos</h5>
                
                <!-- Salários -->
                <div class="mb-4">
                    <h6>Equipe e Salários</h6>
                    <table class="table table-bordered table-sm">
                        <thead><tr><th>Função</th><th>Qtd</th><th>Base</th><th>Encargos</th><th>Dias</th><th>Total</th><th></th></tr></thead>
                        <tbody id="tbody-salarios"></tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-salario">+ Adicionar</button>
                    <span class="float-end fw-bold" id="total-secao-salarios">R$ 0,00</span>
                </div>

                <!-- Estadia -->
                <div class="mb-4">
                    <h6>Estadia e Alimentação</h6>
                    <table class="table table-bordered table-sm">
                        <thead><tr><th>Tipo</th><th>Qtd</th><th>Valor Unit.</th><th>Dias</th><th>Total</th><th></th></tr></thead>
                        <tbody id="tbody-estadia"></tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-estadia">+ Adicionar</button>
                    <span class="float-end fw-bold" id="total-secao-estadia">R$ 0,00</span>
                </div>

                <!-- Consumos -->
                <div class="mb-4">
                    <h6>Combustível</h6>
                    <table class="table table-bordered table-sm">
                        <thead><tr><th>Tipo</th><th>Qtd</th><th>Km/L</th><th>R$/L</th><th>Km Total</th><th>Total</th><th></th></tr></thead>
                        <tbody id="tbody-consumos"></tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-consumo">+ Adicionar</button>
                    <span class="float-end fw-bold" id="total-secao-consumos">R$ 0,00</span>
                </div>

                <!-- Locação -->
                <div class="mb-4">
                    <h6>Equipamentos / Locação</h6>
                    <table class="table table-bordered table-sm">
                        <thead><tr><th>Equipamento</th><th>Marca</th><th>Qtd</th><th>Mensal</th><th>Dias</th><th>Total</th><th></th></tr></thead>
                        <tbody id="tbody-locacao"></tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-locacao">+ Adicionar</button>
                    <span class="float-end fw-bold" id="total-secao-locacao">R$ 0,00</span>
                </div>

                <!-- Admin -->
                <div class="mb-4">
                    <h6>Custos Administrativos</h6>
                    <table class="table table-bordered table-sm">
                        <thead><tr><th>Item</th><th>Qtd</th><th>Valor</th><th>Total</th><th></th></tr></thead>
                        <tbody id="tbody-admin"></tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-admin">+ Adicionar</button>
                    <span class="float-end fw-bold" id="total-secao-admin">R$ 0,00</span>
                </div>
            </div>

            <!-- Seção 3: Totais -->
            <div class="form-section">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Condições</h6>
                        <div class="input-group mb-2">
                            <span class="input-group-text">Entrada %</span>
                            <input type="number" name="mobilizacao_percentual" id="mobilizacao_percentual" class="form-control" value="30" step="0.1">
                            <input type="text" name="mobilizacao_valor" id="mobilizacao_valor_display" class="form-control bg-light" readonly>
                        </div>
                        <div class="input-group mb-2">
                            <span class="input-group-text">Restante %</span>
                            <span class="form-control bg-light" id="restante_percentual_display">70</span>
                            <input type="text" name="restante_valor" id="restante_valor_display" class="form-control bg-light" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="total-box">
                            <p>Custo Operacional: <strong id="total-custos-geral">R$ 0,00</strong></p>
                            <div class="input-group input-group-sm mb-2 justify-content-end">
                                <span class="input-group-text">Lucro %</span>
                                <input type="number" name="percentual_lucro" id="percentual_lucro" class="form-control" style="max-width: 80px" value="30" step="0.1">
                                <span class="input-group-text text-success fw-bold" id="valor-lucro">+ R$ 0,00</span>
                            </div>
                            <div class="input-group input-group-sm mb-2 justify-content-end">
                                <span class="input-group-text">Desconto R$</span>
                                <input type="number" name="valor_desconto" id="valor_desconto" class="form-control" style="max-width: 100px" value="0" step="0.01">
                            </div>
                            <h3 class="text-primary mt-2" id="valor-final-proposta">R$ 0,00</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inputs Hidden para Cálculos Visuais (O Backend recalcula, mas mantemos para JS funcionar) -->
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
                <button type="submit" class="btn btn-success btn-lg px-5">Salvar e Gerar Proposta</button>
            </div>
        </form>
    </div>

    <!-- SEM IFRAME -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        const opcoesFuncaoHtml = `<?php foreach ($tipos_funcao as $item): ?><option value="<?php echo $item['id_funcao']; ?>" data-valor="<?php echo $item['salario_base_default']; ?>"><?php echo htmlspecialchars($item['nome']); ?></option><?php endforeach; ?>`;
        const opcoesEstadiaHtml = `<?php foreach ($tipos_estadia as $item): ?><option value="<?php echo $item['id_estadia']; ?>" data-valor="<?php echo $item['valor_unitario_default']; ?>"><?php echo htmlspecialchars($item['nome']); ?></option><?php endforeach; ?>`;
        const opcoesConsumoHtml = `<?php foreach ($tipos_consumo as $item): ?><option value="<?php echo $item['id_consumo']; ?>" data-valor-litro="<?php echo $item['valor_litro_default']; ?>" data-consumo-kml="<?php echo $item['consumo_kml_default']; ?>"><?php echo htmlspecialchars($item['nome']); ?></option><?php endforeach; ?>`;
        const opcoesLocacaoHtml = `<?php foreach ($tipos_locacao as $item): ?><option value="<?php echo $item['id_locacao']; ?>" data-valor="<?php echo $item['valor_mensal_default']; ?>"><?php echo htmlspecialchars($item['nome']); ?></option><?php endforeach; ?>`;
        const opcoesAdminHtml = `<?php foreach ($tipos_admin as $item): ?><option value="<?php echo $item['id_custo_admin']; ?>" data-valor="<?php echo $item['valor_default']; ?>"><?php echo htmlspecialchars($item['nome']); ?></option><?php endforeach; ?>`;
        const marcasPorTipo = <?php echo json_encode($marcas_por_tipo); ?>;
    </script>
    <script src="calculos.js"></script>
    <script>
        $(document).ready(function() {
            $('#id_cliente').select2({ theme: 'bootstrap-5' });
            $('#id_cliente').on('select2:select', function(e){
                $('#contato_obra').val($(this).find(':selected').data('contato'));
            });
            $('#id_servico').on('change', function(){
                var txt = $(this).find(':selected').text();
                if(!$('#finalidade').val()) $('#finalidade').val($(this).find(':selected').data('descricao'));
                $('#tipo_levantamento').val('Levantamento ' + txt);
            });
            // REMOVIDO: Função de submit que escondia o form
        });
    </script>
</body>
</html>