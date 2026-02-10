<!-- STEP 1: CLIENTE -->
<div class="step-panel active" id="step-1">
    <div class="step-actions">
        <a href="painel.php" class="btn-action" title="Voltar ao Painel">
            <i class="bi bi-house-door"></i> Painel
        </a>
    </div>
    <h1 class="section-title">Quem é o Cliente?</h1>
    <p class="section-subtitle">Selecione o cliente e local da obra.</p>

    <div class="form-row cols-2">
        <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="id_cliente">Cliente *</label>
            <div class="cliente-selector-wrapper">
                <select class="form-select" name="id_cliente" id="id_cliente" required>
                    <option value="">Buscar cliente...</option>
                    <?php if (!empty($clientes)): ?>
                        <?php foreach ($clientes as $c):
                            $contato = explode(' ', $c['nome_cliente'])[0] . ' - ' . ($c['celular'] ?: $c['telefone'] ?: 'Sem contato');
                        ?>
                        <option value="<?= $c['id_cliente'] ?>" data-contato="<?= htmlspecialchars($contato) ?>" <?= (isset($_REQUEST['id_cliente']) && $_REQUEST['id_cliente'] == $c['id_cliente']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome_cliente']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <button type="button" class="btn btn-success btn-novo-cliente" id="btn-novo-cliente" title="Cadastrar novo cliente">
                    <i class="bi bi-plus-lg"></i>
                    <span class="btn-text-desktop">Novo</span>
                </button>
            </div>
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="contato_obra">Contato na Obra</label>
            <input type="text" name="contato_obra" id="contato_obra" class="form-control" placeholder="Ex: Sr. João (Vigia)">
        </div>

        <!-- Endereço: Layout responsivo -->
        <div class="form-group" style="grid-column: 1 / -1;">
            <div class="address-row">
                <!-- Endereço da Obra -->
                <div class="address-field address-field-large">
                    <label class="form-label" for="endereco">Endereço da Obra *</label>
                    <input type="text" name="endereco" id="endereco" class="form-control" placeholder="Rua, número, complemento..." required autocomplete="off">
                </div>
                <!-- Bairro -->
                <div class="address-field address-field-small">
                    <label class="form-label" for="bairro">Bairro</label>
                    <input type="text" name="bairro" id="bairro" class="form-control" autocomplete="off">
                </div>
                <!-- Cidade -->
                <div class="address-field address-field-medium">
                    <label class="form-label" for="cidade">Cidade *</label>
                    <input type="text" name="cidade" id="cidade" class="form-control" required autocomplete="off">
                </div>
                <!-- Estado -->
                <div class="address-field address-field-tiny">
                    <label class="form-label" for="estado">Estado</label>
                    <select name="estado" id="estado" class="form-select" autocomplete="off">
                        <?php if (!empty($estados)): ?>
                            <?php foreach ($estados as $e): ?>
                                <option value="<?= $e['sigla'] ?>" <?= $e['sigla'] === 'MG' ? 'selected' : '' ?>><?= $e['sigla'] ?> - <?= $e['nome'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
