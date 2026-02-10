<!-- STEP 2: ESCOPO -->
<div class="step-panel" id="step-2">
    <div class="step-actions">
        <a href="painel.php" class="btn-action" title="Voltar ao Painel">
            <i class="bi bi-house-door"></i> Painel
        </a>
    </div>
    <h1 class="section-title">O que será feito?</h1>
    <p class="section-subtitle">Defina o serviço e prazos.</p>

    <div class="form-row cols-2">
        <div class="form-group">
            <label class="form-label" for="id_servico">Tipo de Serviço *</label>
            <select class="form-select" name="id_servico" id="id_servico" required>
                <option value="">Selecione...</option>
                <?php if (!empty($servicos)): ?>
                    <?php foreach ($servicos as $s): ?>
                        <option value="<?= $s['id'] ?>" data-descricao="<?= htmlspecialchars($s['descricao'] ?? '') ?>" <?= (isset($_REQUEST['id_servico']) && $_REQUEST['id_servico'] == $s['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="tipo_levantamento">Título na Proposta</label>
            <input type="text" name="tipo_levantamento" id="tipo_levantamento" class="form-control" placeholder="Ex: Levantamento Planialtimétrico">
        </div>

        <!-- Linha Customizada: Finalidade (9) + Área (3) -->
        <div class="form-group" style="grid-column: 1 / -1;">
            <div class="row-custom-9-3" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <div style="flex: 9; min-width: 300px;">
                    <label class="form-label">Descrição / Finalidade</label>
                    <textarea class="form-control" name="finalidade" id="finalidade" rows="3" placeholder="Descreva o objetivo do trabalho..."></textarea>
                </div>
                <div style="flex: 3; min-width: 150px;">
                    <label class="form-label" for="area">Área</label>
                    <div class="d-flex gap-2">
                        <input type="number" name="area" id="area" class="form-control" placeholder="0.00" step="0.01" inputmode="decimal">
                        <select name="unidade_area" id="unidade_area" class="form-select" style="max-width: 100px;" aria-label="Unidade de medida">
                            <option value="m²" selected>m²</option>
                            <option value="ha">ha</option>
                            <option value="km²">km²</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Linha de Prazos (Agrupada) -->
        <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label mb-2">Cronograma (Dias Úteis)</label>
            <!-- Flexbox manual para garantir linha única -->
            <div style="display: flex; gap: 10px; width: 100%;">
                <div style="flex: 1;">
                    <label class="text-xs text-slate-500 font-bold uppercase" style="font-size: 10px;">Campo</label>
                    <input type="number" name="dias_campo" id="dias_campo" class="form-control recalc" value="1" min="0" inputmode="numeric">
                </div>
                <div style="flex: 1;">
                    <label class="text-xs text-slate-500 font-bold uppercase" style="font-size: 10px;">Escritório</label>
                    <input type="number" name="dias_escritorio" id="dias_escritorio" class="form-control recalc" value="4" min="0" inputmode="numeric">
                </div>
                <div style="flex: 1.5;">
                    <label class="text-xs text-slate-500 font-bold uppercase" style="font-size: 10px;">Prazo Final</label>
                    <input type="text" name="prazo_execucao" id="prazo_execucao" class="form-control bg-slate-100 font-bold text-slate-700" readonly style="font-size: 12px;">
                </div>
            </div>
        </div>
    </div>
</div>
