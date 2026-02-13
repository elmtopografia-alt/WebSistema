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
            <input type="text" name="contato_obra" id="contato_obra" class="form-control" placeholder="Ex: Sr. João (Vigia)" autocomplete="off" autocorrect="off" autocapitalize="off">
        </div>

        <!-- Endereço: Layout Simplificamento -->
        <div class="form-group" style="grid-column: 1 / -1; margin-top: 1rem;">
            <div class="address-row">
                <div class="address-field address-field-main" style="flex: 2;">
                    <label class="form-label" for="endereco_rua">Local da Obra (Logradouro/Rua) *</label>
                    <input type="text" name="endereco" id="endereco_rua" class="form-control" placeholder="Rua, número, quilômetro..." required autocomplete="off">
                </div>
                <div class="address-field address-field-small" style="flex: 1;">
                    <label class="form-label" for="bairro">Bairro</label>
                    <input type="text" name="bairro" id="bairro" class="form-control" placeholder="Ex: Centro" autocomplete="off">
                </div>
            </div>

            <div class="address-row" style="margin-top: 0.75rem;">
                <div class="address-field address-field-medium">
                    <label class="form-label" for="cidade">Cidade *</label>
                    <input type="text" name="cidade" id="cidade" class="form-control" required autocomplete="off">
                </div>
                <div class="address-field address-field-tiny">
                    <label class="form-label" for="estado">Estado</label>
                    <select name="estado" id="estado" class="form-select" autocomplete="off" style="padding-right: 25px; background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 fill=%22%231e293b%22 class=%22bi bi-chevron-down%22 viewBox=%220 0 16 16%22%3E%3Cpath fill-rule=%22evenodd%22 d=%22M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 8px center; background-size: 12px;">
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
