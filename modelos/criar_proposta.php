<?php
// PASSO 1: LÓGICA PARA BUSCAR OS DADOS NO BANCO
// =================================================

// Inclui nossa conexão com o banco de dados
require 'db.php';

// Prepara um array para guardar a lista de estados
$estados = [];

// Cria a consulta SQL para buscar nome e sigla dos estados, ordenados por nome
$sql_estados = "SELECT nome, sigla FROM estados ORDER BY nome ASC";

// Executa a consulta
$resultado_estados = $conn->query($sql_estados);

// Verifica se a consulta retornou resultados
if ($resultado_estados && $resultado_estados->num_rows > 0) {
    // Loop para guardar cada estado no nosso array
    while ($row = $resultado_estados->fetch_assoc()) {
        $estados[] = $row;
    }
}

// Futuramente, faremos o mesmo para carregar Clientes e Tipos de Serviço dinamicamente
// $clientes = carregar_clientes_do_banco();
// $tipos_servico = carregar_servicos_do_banco();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Proposta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-responsive { overflow-x: auto; }
        .total-geral { font-size: 1.2rem; font-weight: bold; }
        .form-control-sm { padding: 0.25rem 0.5rem; }
        input[type=number] { text-align: right; }
        .total-linha, .total-secao { font-weight: bold; text-align: right; }
    </style>
</head>
<body>
<div class="container mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Criação de Proposta</h1>
        <a href="index.php" class="btn btn-secondary">Voltar ao Painel</a>
    </div>

    <!-- DADOS GERAIS -->
    <div class="card mb-4">
        <div class="card-header">Dados Gerais da Proposta</div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Linha 1: Cliente, Serviço, Nome -->
                <div class="col-md-4">
                    <label for="id_cliente" class="form-label">Cliente</label>
                    <select class="form-select" id="id_cliente">
                        <option selected disabled value="">Selecione um cliente...</option>
                        <option value="1">Cliente Exemplo 1</option>
                        <option value="2">Outra Empresa Ltda</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="id_servico" class="form-label">Tipo de Serviço</label>
                    <select class="form-select" id="id_servico">
                        <option selected disabled value="">Selecione um serviço...</option>
                        <option value="1">Planimétrico</option>
                        <option value="2">Usucapião</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="nome_projeto" class="form-label">Nome do Projeto</label>
                    <input type="text" class="form-control" id="nome_projeto" placeholder="Ex: Levantamento Fazenda Sta. Maria">
                </div>

                <!-- Linha 2: Novos campos da OBRA -->
                <div class="col-md-3">
                    <label for="area_obra" class="form-label">Área da Obra</label>
                    <input type="text" class="form-control" id="area_obra" placeholder="Ex: 150,00 m² ou 25 ha">
                </div>
                 <div class="col-md-6">
                    <label for="endereco_obra" class="form-label">Endereço da Obra</label>
                    <input type="text" class="form-control" id="endereco_obra" placeholder="Rua, Número, Complemento">
                </div>
                <div class="col-md-3">
                    <label for="bairro_obra" class="form-label">Bairro</label>
                    <input type="text" class="form-control" id="bairro_obra">
                </div>

                <!-- Linha 3: Novos campos da OBRA -->
                <div class="col-md-4">
                    <label for="cidade_obra" class="form-label">Cidade</label>
                    <input type="text" class="form-control" id="cidade_obra">
                </div>
                <div class="col-md-2">
                    <label for="estado_obra" class="form-label">Estado</label>
                    <!-- PASSO 2: CAMPO DE ESTADO SUBSTITUÍDO -->
                    <select class="form-select" id="estado_obra">
                        <option value="">--</option>
                        <?php foreach ($estados as $estado): ?>
                            <?php
                                // Lógica para deixar Minas Gerais pré-selecionado
                                $selected = ($estado['sigla'] === 'MG') ? 'selected' : '';
                            ?>
                            <option value="<?= htmlspecialchars($estado['sigla']) ?>" <?= $selected ?>>
                                <?= htmlspecialchars($estado['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div class="col-md-6">
                    <label for="contato_projeto" class="form-label">Contato no Projeto</label>
                    <input type="text" class="form-control" id="contato_projeto" placeholder="Nome do responsável a contatar">
                </div>

                <!-- Linha 4: Finalidade -->
                <div class="col-12">
                     <label for="finalidade" class="form-label">Finalidade do Serviço</label>
                     <textarea class="form-control" id="finalidade" rows="2" placeholder="Descrever o objetivo do serviço..."></textarea>
                </div>

                <!-- Linha 5: Prazos -->
                <div class="col-md-4">
                    <label for="prazo_execucao" class="form-label">Prazo de Execução</label>
                    <input type="text" class="form-control" id="prazo_execucao" placeholder="Ex: 15 dias úteis">
                </div>
                <div class="col-md-4">
                    <label for="dias_campo" class="form-label">Dias de Campo (Estimado)</label>
                    <input type="number" class="form-control" id="dias_campo" placeholder="Ex: 2">
                </div>
                <div class="col-md-4">
                    <label for="dias_escritorio" class="form-label">Dias de Escritório (Estimado)</label>
                    <input type="number" class="form-control" id="dias_escritorio" placeholder="Ex: 5">
                </div>
            </div>
        </div>
    </div>

    <!-- O RESTANTE DO FORMULÁRIO (SEÇÕES DE CUSTOS E TOTAIS) CONTINUA EXATAMENTE O MESMO -->
    <!-- ... (cole o restante do seu código aqui, a partir de <div class="accordion" id="accordion-custos">) ... -->

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="calculos.js"></script>
</body>
</html>