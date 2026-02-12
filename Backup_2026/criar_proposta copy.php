<?php
// Nome do Arquivo: criar_proposta.php
// Função: Formulário de criação com Design "Azul ELM" (Vivid Blue), Mobile Otimizado e Coluna Fixa.

session_start();
require_once 'config.php';
require_once 'db.php';

// Validação de Sessão
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
$id_usuario = $_SESSION['usuario_id'];

try {
    // 1. Clientes
    $stmt_cli = $conn->prepare("SELECT id_cliente, nome_cliente, telefone, celular FROM Clientes WHERE id_criador = ? ORDER BY id_cliente DESC");
    $stmt_cli->bind_param('i', $id_usuario);
    $stmt_cli->execute();
    $clientes_res = $stmt_cli->get_result();
    
    // 2. Serviços
    $servicos_res = $conn->query("SELECT id_servico, nome, descricao FROM Tipo_Servicos ORDER BY nome ASC");
    
    // 3. Estados
    $estados = [];
    $result_estados = $conn->query("SELECT nome, sigla FROM estados ORDER BY nome ASC");
    if($result_estados) while ($row = $result_estados->fetch_assoc()) { $estados[] = $row; }
    
    // 4. Arrays para JS
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
    <meta charset="UTF-8"><title>Nova Proposta | SGT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        body { background-color: #e9ecef; color: #343a40; font-family: 'Segoe UI', sans-serif; }
        
        /* CABEÇALHO AZUL ELM (VIVO) */
        .header-premium {
            /* Gradiente Azul Vivo e Tecnológico */
            background: linear-gradient(135deg, #0051ce 0%, #1e87f0 100%);
            color: white;
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        /* Efeito de brilho sutil no fundo */
        .header-premium::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Ícone estilo App */
        .app-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            backdrop-filter: blur(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        /* Botão de Voltar translúcido */
        .btn-glass {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(5px);
            transition: all 0.3s;
        }
        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }

        /* Formulários */
        .form-section { background: #f8f9fa; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; border: 1px solid #dee2e6; }
        .section-title { border-bottom: 2px solid #dee2e6; padding-bottom: 10px; margin-bottom: 20px; font-weight: 700; color: #495057; font-size: 1.1rem; }
        .total-box { background: #e2e6ea; padding: 20px; border-radius: 8px; text-align: right; border: 1px solid #ced4da; }
        label { font-weight: 600; color: #495057; margin-bottom: 5px; font-size: 0.9rem; }
        .form-control, .form-select { background-color: #fff; border-color: #ced4da; }
        .form-control:focus, .form-select:focus { background-color: #fff; border-color: #86b7fe; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15); }

        @media (max-width: 768px) {
            .form-control, .form-select, input, select, textarea { font-size: 16px !important; padding: 8px 6px !important; height: auto !important; }
            .form-section { padding: 15px; }
            .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 10px; border-right: 1px solid #ddd; position: relative; }
            table .total-linha, table th:nth-last-child(2) { position: sticky; right: 45px; background-color: #fffbe6; z-index: 10; font-weight: bold; border-left: 2px solid #dee2e6; box-shadow: -2px 0 5px rgba(0,0,0,0.05); }
            table td:last-child, table th:last-child { position: sticky; right: 0; background-color: #f8f9fa; z-index: 10; }
            .table input[type="number"] { min-width: 60px; }
            .table select { min-width: 110px; }
            /* Ajuste mobile do header */
            .header-premium .d-flex { flex-direction: column; align-items: flex-start !important; }
            .header-premium .btn-glass { width: 100%; margin-top: 15px; }
        }
    </style>
</head>
<body>
    
    <div class="container mt-4 mb-5">
        
        <!-- CABEÇALHO AZUL ELM -->
        <div class="card shadow-lg mb-4 rounded-4 overflow-hidden header-premium">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                
                <div class="d-flex align-items-center gap-3">
                    <!-- Ícone em destaque -->
                    <div class="app-icon">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    
                    <!-- Textos -->
                    <div>
                        <div class="text-white-50 fw-bold small text-uppercase ls-1">
                            Sistema SGT
                        </div>
                        <h2 class="fw-bold mb-0 text-white">
                            Nova Proposta
                        </h2>
                        <p class="text-white-50 mb-0 small mt-1">
                            Preencha os dados para gerar o orçamento oficial.
                        </p>
                    </div>
                </div>
                
                <!-- Botão Voltar Glass -->
                <div>
                    <a href="index.php" class="btn btn-glass btn-sm px-4 py-2 fw-bold rounded-pill">
                        <i class="bi bi-arrow-left me-2"></i> Voltar
                    </a>
                </div>
            </div>
            <!-- Barra de Progresso Decorativa (Azul Claro) -->
            <div class="progress" style="height: 4px; background: rgba(0,0,0,0.1);">
                <div class="progress-bar bg-info" role="progressbar" style="width: 25%"></div>
            </div>
        </div>
        <!-- FIM CABEÇALHO -->

        <form action="salvar_proposta.php" method="POST" id="form-proposta">
            
            <div class="form-section">
                <h5 class="section-title"><i class="bi bi-person-badge me-2"></i>Dados do Cliente e Obra</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Cliente</label>
                        <select class="form-select" name="id_cliente" id="id_cliente" required>
                            <option value="">Selecione...</option>
                            <?php if($clientes_res) mysqli_data_seek($clientes_res, 0); while ($c = $clientes_res->fetch_assoc()): ?>
                                <option value="<?= $c['id_cliente'] ?>" data-nome="<?= htmlspecialchars($c['nome_cliente']) ?>" data-celular="<?= htmlspecialchars($c['celular']) ?>" data-telefone="<?= htmlspecialchars($c['telefone']) ?>">
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
                                <option value="<?= $s['id_servico'] ?>" data-descricao="<?= htmlspecialchars($s['descricao']) ?>"><?= htmlspecialchars($s['nome']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-12"><label>Finalidade / Escopo</label><textarea class="form-control" name="finalidade" id="finalidade" rows="2" placeholder="Descreva o objetivo do serviço..."></textarea></div>
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

            <div class="form-section">
                <h5 class="section-title"><i class="bi bi-calculator me-2"></i>Composição de Custos</h5>
                <div class="alert alert-info d-md-none py-1 small mb-3"><i class="bi bi-arrows-expand"></i> Arraste a tabela para ver detalhes. O Total fica fixo.</div>
                
                <div class="mb-4">
                    <h6 class="fw-bold text-secondary">Equipe e Salários</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm border-secondary" style="min-width: 600px;">
                            <thead class="table-secondary"><tr><th>Função</th><th>Qtd</th><th>Base</th><th>Encargos</th><th>Dias</th><th>Total</th><th></th></tr></thead>
                            <tbody id="tbody-salarios"></tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-salario"><i class="bi bi-plus-lg"></i> Adicionar</button>
                    <span class="float-end fw-bold text-dark" id="total-secao-salarios">R$ 0,00</span>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-secondary">Estadia e Alimentação</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm border-secondary" style="min-width: 550px;">
                            <thead class="table-secondary"><tr><th>Tipo</th><th>Qtd</th><th>Valor Unit.</th><th>Dias</th><th>Total</th><th></th></tr></thead>
                            <tbody id="tbody-estadia"></tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-estadia"><i class="bi bi-plus-lg"></i> Adicionar</button>
                    <span class="float-end fw-bold text-dark" id="total-secao-estadia">R$ 0,00</span>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-secondary">Combustível</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm border-secondary" style="min-width: 600px;">
                            <thead class="table-secondary"><tr><th>Tipo</th><th>Qtd</th><th>Km/L</th><th>R$/L</th><th>Km Total</th><th>Total</th><th></th></tr></thead>
                            <tbody id="tbody-consumos"></tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-consumo"><i class="bi bi-plus-lg"></i> Adicionar</button>
                    <span class="float-end fw-bold text-dark" id="total-secao-consumos">R$ 0,00</span>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-secondary">Equipamentos / Locação</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm border-secondary" style="min-width: 650px;">
                            <thead class="table-secondary"><tr><th>Equipamento</th><th>Marca</th><th>Qtd</th><th>Mensal</th><th>Dias</th><th>Total</th><th></th></tr></thead>
                            <tbody id="tbody-locacao"></tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-locacao"><i class="bi bi-plus-lg"></i> Adicionar</button>
                    <span class="float-end fw-bold text-dark" id="total-secao-locacao">R$ 0,00</span>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-secondary">Custos Administrativos</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm border-secondary" style="min-width: 450px;">
                            <thead class="table-secondary"><tr><th>Item</th><th>Qtd</th><th>Valor</th><th>Total</th><th></th></tr></thead>
                            <tbody id="tbody-admin"></tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-admin"><i class="bi bi-plus-lg"></i> Adicionar</button>
                    <span class="float-end fw-bold text-dark" id="total-secao-admin">R$ 0,00</span>
                </div>
            </div>

            <div class="form-section">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-secondary">Condições de Pagamento</h6>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-secondary text-white">Entrada %</span>
                            <input type="number" name="mobilizacao_percentual" id="mobilizacao_percentual" class="form-control" value="30" step="1">
                            <input type="text" name="mobilizacao_valor" id="mobilizacao_valor_display" class="form-control bg-light fw-bold" readonly>
                        </div>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-secondary text-white">Restante %</span>
                            <span class="form-control bg-light" id="restante_percentual_display">70</span>
                            <input type="text" name="restante_valor" id="restante_valor_display" class="form-control bg-light fw-bold" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="total-box shadow-sm mt-3 mt-md-0">
                            <p class="mb-2">Custo Operacional: <strong id="total-custos-geral">R$ 0,00</strong></p>
                            
                            <div class="input-group input-group-sm mb-2 justify-content-end">
                                <span class="input-group-text fw-bold">Lucro %</span>
                                <input type="number" name="percentual_lucro" id="percentual_lucro" class="form-control text-end" style="max-width: 80px" value="30" step="0.1">
                                <span class="input-group-text text-success fw-bold" id="valor-lucro">+ R$ 0,00</span>
                            </div>
                            
                            <div class="input-group input-group-sm mb-2 justify-content-end">
                                <span class="input-group-text fw-bold text-danger">Desconto R$</span>
                                <input type="number" name="valor_desconto" id="valor_desconto" class="form-control text-end text-danger fw-bold" style="max-width: 100px" value="0" step="0.01">
                            </div>
                            
                            <hr>
                            <h3 class="text-primary mt-2 mb-0" id="valor-final-proposta">R$ 0,00</h3>
                            <small class="text-muted">Valor Final para o Cliente</small>
                        </div>
                    </div>
                </div>
            </div>

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
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-lg fw-bold">
                    <i class="bi bi-file-earmark-check me-2"></i> Salvar e Gerar Proposta
                </button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
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
            // Inicializa Select2
            $('#id_cliente').select2({ theme: 'bootstrap-5' });
            
            $('#id_cliente').on('select2:select', function(e){
                var element = $(this).find(':selected');
                var nomeCompleto = element.data('nome') || '';
                var celular = element.data('celular') || '';
                var telefone = element.data('telefone') || '';
                
                var primeiroNome = nomeCompleto.split(' ')[0];
                var contatoNum = celular ? celular : telefone;
                
                var textoFinal = primeiroNome;
                if(contatoNum) textoFinal += ' - ' + contatoNum;
                
                $('#contato_obra').val(textoFinal);
            });

            $('#id_servico').on('change', function(){
                var txt = $(this).find(':selected').text();
                if(!$('#finalidade').val()) $('#finalidade').val($(this).find(':selected').data('descricao'));
                $('#tipo_levantamento').val('Levantamento ' + txt);
            });

            aplicarMascaras();
            $('body').on('focus', 'input[name="telefone_salvo"], input[name="celular_salvo"]', function(){
                $(this).mask('(00) 00000-0000');
            });
        });

        function aplicarMascaras() {
            $('.phone').mask('(00) 0000-0000');
            $('.celular').mask('(00) 00000-0000');
            $('.cpf').mask('000.000.000-00', {reverse: true});
            $('.cnpj').mask('00.000.000/0000-00', {reverse: true});
        }
    </script>
</body>
</html>