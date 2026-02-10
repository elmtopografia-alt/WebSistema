<!-- STEP 3: CUSTOS -->
<div class="step-panel" id="step-3">
    <div class="step-actions">
        <a href="painel.php" class="btn-action" title="Voltar ao Painel">
            <i class="bi bi-house-door"></i> Painel
        </a>
        <button type="button" class="btn-action btn-action-calc" onclick="openCalculator()" title="Abrir Calculadora">
            <i class="bi bi-calculator"></i> Calculadora
        </button>
    </div>
    <h1 class="section-title">Custos Operacionais</h1>
    <p class="section-subtitle">Adicione os recursos necessários.</p>

    <!-- Tabs -->
    <div class="cost-tabs" id="cost-tabs">
        <button type="button" class="cost-tab active" data-tab="equipe">
            <i class="bi bi-people"></i> Equipe
        </button>
        <button type="button" class="cost-tab" data-tab="estadia">
            <i class="bi bi-house"></i> Estadia
        </button>
        <button type="button" class="cost-tab" data-tab="consumo">
            <i class="bi bi-fuel-pump"></i> Combustível
        </button>
        <button type="button" class="cost-tab" data-tab="equipamentos">
            <i class="bi bi-tools"></i> Equipamentos
        </button>
        <button type="button" class="cost-tab" data-tab="admin">
            <i class="bi bi-briefcase"></i> Admin
        </button>
    </div>

    <!-- Panels -->
    <div class="cost-panels">
        <div class="cost-panel active" id="panel-equipe">
            <div id="list-salarios" class="cost-list"></div>
            <button type="button" class="btn btn-add" id="add-salario">
                <i class="bi bi-plus-lg"></i> Adicionar Profissional
            </button>
        </div>

        <div class="cost-panel hidden" id="panel-estadia">
            <div id="list-estadia" class="cost-list"></div>
            <button type="button" class="btn btn-add" id="add-estadia">
                <i class="bi bi-plus-lg"></i> Adicionar Estadia
            </button>
        </div>

        <div class="cost-panel hidden" id="panel-consumo">
            <div id="list-consumos" class="cost-list"></div>
            <button type="button" class="btn btn-add" id="add-consumo">
                <i class="bi bi-plus-lg"></i> Adicionar Combustível
            </button>
        </div>

        <div class="cost-panel hidden" id="panel-equipamentos">
            <div id="list-locacao" class="cost-list"></div>
            <button type="button" class="btn btn-add" id="add-locacao">
                <i class="bi bi-plus-lg"></i> Adicionar Equipamento
            </button>
        </div>

        <div class="cost-panel hidden" id="panel-admin">
            <div id="list-admin" class="cost-list"></div>
            <button type="button" class="btn btn-add" id="add-admin">
                <i class="bi bi-plus-lg"></i> Adicionar Custo Admin
            </button>
        </div>
    </div>
</div>
