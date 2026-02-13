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
        <!-- Novo Campo: Tipo de Serviço (Classificação Interna) -->
        <div class="form-group">
            <label class="form-label" style="display:flex; align-items:center; gap:5px;">
                <i class="bi bi-tag-fill text-secondary"></i> Classificação <small class="text-muted fw-normal">(Interno)</small>
            </label>
            <select class="form-select" name="tipo_servico_id" id="tipo_servico_id">
                <option value="">-- Selecione --</option>
                <?php 
                $tipos = $dados_cache['arrays_js']['tipos_servico'] ?? [];
                if (!empty($tipos)): 
                    foreach ($tipos as $t): ?>
                        <option value="<?= $t['id'] ?>" 
                                data-cor="<?= htmlspecialchars($t['cor'] ?? '#666') ?>"
                                data-icone="<?= htmlspecialchars($t['icone'] ?? 'tag') ?>"
                                <?= (isset($proposta['tipo_servico_id']) && $proposta['tipo_servico_id'] == $t['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['nome']) ?>
                        </option>
                    <?php endforeach; 
                endif; ?>
            </select>
            
            <!-- Preview do Badge (Perfumaria UI) -->
            <div id="tipoPreview" style="margin-top: 8px; min-height: 24px;"></div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const select = document.getElementById('tipo_servico_id');
                const preview = document.getElementById('tipoPreview');
                
                function updateBadge() {
                    const option = select.options[select.selectedIndex];
                    if (select.value && option) {
                        const cor = option.getAttribute('data-cor') || '#95a5a6';
                        const icone = option.getAttribute('data-icone') || 'tag';
                        const nome = option.text;
                        
                        preview.innerHTML = `
                            <span class="badge-tipo" style="background:${cor}; display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:500; color:white;">
                                <i class="bi bi-${icone}" style="font-size:10px;"></i> ${nome}
                            </span>
                        `;
                    } else {
                        preview.innerHTML = '';
                    }
                }
                
                if(select) {
                    select.addEventListener('change', updateBadge);
                    // Run once on load
                    setTimeout(updateBadge, 500); 
                }
            });
            </script>
        </div>

        <div class="form-group">
            <label class="form-label" for="id_servico">Modelo de Proposta *</label>
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
                    <div style="display: flex; align-items: stretch;">
                        <input type="text" name="area" id="area" class="form-control" placeholder="0.00" inputmode="decimal" 
                               style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: none;">
                        <select name="unidade_area" id="unidade_area" class="form-select" 
                                style="max-width: 80px; border-top-left-radius: 0; border-bottom-left-radius: 0; background-color: rgba(255,255,255,0.05); color: #f97316; font-weight: bold; border-left: 1px solid rgba(255,255,255,0.1);" aria-label="Unidade de medida">
                            <option value="m²">m²</option>
                            <option value="ha">ha</option>
                            <option value="km²">km²</option>
                        </select>
                    </div>
                    <div id="areaPreview" class="text-[10px] text-slate-500 mt-1 ml-1 font-medium">
                        Preview: <span class="text-tech font-bold" id="areaCombinedValue">0.00 m²</span>
                    </div>
                </div>
                <script>
                function updateAreaPreview() {
                    const val = document.getElementById('area').value.trim() || '0.00';
                    const unit = document.getElementById('unidade_area').value;
                    document.getElementById('areaCombinedValue').textContent = val + ' ' + unit;
                }

                document.getElementById('area').addEventListener('input', function() {
                    let val = this.value.toLowerCase().trim();
                    const unitSelect = document.getElementById('unidade_area');
                    
                    // Detecção automática ao digitar
                    let detected = false;
                    if (val.endsWith('ha')) {
                        unitSelect.value = 'ha';
                        this.value = val.replace('ha', '').trim();
                        detected = true;
                    } else if (val.endsWith('m2') || val.endsWith('m²')) {
                        unitSelect.value = 'm²';
                        this.value = val.replace(/m2|m²/g, '').trim();
                        detected = true;
                    } else if (val.endsWith('km2') || val.endsWith('km²')) {
                        unitSelect.value = 'km²';
                        this.value = val.replace(/km2|km²/g, '').trim();
                        detected = true;
                    }

                    updateAreaPreview();
                });

                document.getElementById('unidade_area').addEventListener('change', updateAreaPreview);
                // Initial run
                setTimeout(updateAreaPreview, 100);
                </script>
            </div>
        </div>

        <!-- Novos Campos: Drone e Condições (Solicitados) -->
        <div class="form-group" style="grid-column: 1 / -1; margin-top: 10px;">
            <label class="form-label mb-2"><i class="bi bi-geo-alt-fill text-warning"></i> Condições do Local e Voo</label>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                
                <!-- 1. Acesso Local -->
                <div>
                    <label class="form-label" for="acesso_local">Acesso Local</label>
                    <select name="acesso_local" id="acesso_local" class="form-select">
                        <option value="">-- Selecione --</option>
                        <option value="fácil – via asfaltada">Fácil – Via Asfaltada</option>
                        <option value="médio – estrada de terra trafegável">Médio – Estrada de Terra</option>
                        <option value="difícil – trilha / acesso restrito">Difícil – Trilha / Acesso Restrito</option>
                        <option value="acesso controlado – portaria / vigilância">Acesso Controlado (Portaria)</option>
                        <option value="somente pedestre">Somente Pedestre</option>
                        <option value="acesso por propriedade privada">Acesso por Propriedade Privada</option>
                        <option value="acesso por estrada vicinal">Acesso por Estrada Vicinal</option>
                    </select>
                </div>

                <!-- 2. Cobertura Vegetal -->
                <div>
                    <label class="form-label" for="cobertura_vegetal">Cobertura Vegetal</label>
                    <select name="cobertura_vegetal" id="cobertura_vegetal" class="form-select">
                        <option value="">-- Selecione --</option>
                        <option value="área limpa / solo exposto">Área Limpa / Solo Exposto</option>
                        <option value="pastagem baixa">Pastagem Baixa</option>
                        <option value="vegetação rasteira">Vegetação Rasteira</option>
                        <option value="vegetação média (arbustos)">Vegetação Média (Arbustos)</option>
                        <option value="vegetação densa">Vegetação Densa</option>
                        <option value="mata fechada">Mata Fechada</option>
                        <option value="área urbana">Área Urbana</option>
                        <option value="área parcialmente arborizada">Área Parcialmente Arborizada</option>
                        <option value="reflorestamento / eucalipto">Reflorestamento / Eucalipto</option>
                        <option value="cultura agrícola">Cultura Agrícola</option>
                    </select>
                </div>

                <!-- 3. Condições do Terreno -->
                <div>
                    <label class="form-label" for="tipo_terreno">Tipo de Terreno</label>
                    <select name="tipo_terreno" id="tipo_terreno" class="form-select">
                        <option value="">-- Selecione --</option>
                        <option value="plano">Plano</option>
                        <option value="levemente ondulado">Levemente Ondulado</option>
                        <option value="ondulado">Ondulado</option>
                        <option value="fortemente ondulado">Fortemente Ondulado</option>
                        <option value="montanhoso">Montanhoso</option>
                        <option value="terreno irregular">Terreno Irregular</option>
                        <option value="área alagadiça">Área Alagadiça</option>
                        <option value="solo arenoso">Solo Arenoso</option>
                        <option value="solo argiloso">Solo Argiloso</option>
                        <option value="área urbanizada">Área Urbanizada</option>
                        <option value="área em terraplenagem">Área em Terraplenagem</option>
                        <option value="presença de taludes">Presença de Taludes</option>
                    </select>
                </div>

                <!-- 4. Restrições Aéreas -->
                <div>
                    <label class="form-label" for="restricoes_aereas">Restrições Aéreas</label>
                    <select name="restricoes_aereas" id="restricoes_aereas" class="form-select">
                        <option value="">-- Selecione --</option>
                        <option value="nenhuma restrição aparente">Nenhuma Restrição Aparente</option>
                        <option value="proximidade de rede elétrica">Rede Elétrica Próxima</option>
                        <option value="proximidade de torres de comunicação">Torres de Comunicação</option>
                        <option value="área urbana com edificações altas">Edificações Altas</option>
                        <option value="presença de pessoas no entorno">Pessoas no Entorno</option>
                        <option value="proximidade de rodovia">Proximidade de Rodovia</option>
                        <option value="proximidade de aeroporto / heliponto">Aeroporto / Heliponto</option>
                        <option value="área militar">Área Militar</option>
                        <option value="área ambiental protegida">Área Ambiental Protegida</option>
                        <option value="voo condicionado à autorização">Requer Autorização de Voo</option>
                        <option value="espaço aéreo controlado">Espaço Aéreo Controlado</option>
                        <option value="obstáculos verticais (árvores / postes)">Obstáculos Verticais</option>
                    </select>
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

<!-- Script de Sugestão Automática -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const campoAlvo = document.getElementById('tipo_levantamento'); // Campo de título
    const selectTipo = document.getElementById('tipo_servico_id');
    const preview = document.getElementById('tipoPreview');

    if (campoAlvo && selectTipo) {
        let timeout = null;

        campoAlvo.addEventListener('input', function() {
            const texto = this.value.trim();
            
            // Limpar timeout anterior
            if (timeout) clearTimeout(timeout);
            
            // Só sugerir se não tiver tipo selecionado ou se quiser sobrescrever
            if (texto.length < 4) return;

            timeout = setTimeout(() => {
                // Mostrar loading discreto
                if(preview) preview.innerHTML = '<span style="font-size:10px; color:#999"><i class="bi bi-magic"></i> Analisando...</span>';

                const formData = new FormData();
                formData.append('servico', texto);

                fetch('sugerir_tipo.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.sugestao) {
                        const tipo = data.sugestao;
                        
                        // Verificar se já não está selecionado
                        if (selectTipo.value != tipo.id) {
                            selectTipo.value = tipo.id;
                            // Disparar evento change para atualizar o badge normal
                            selectTipo.dispatchEvent(new Event('change'));
                            
                            // Adicionar aviso de sugestão
                            setTimeout(() => {
                                if(preview) {
                                    const currentContent = preview.innerHTML;
                                    preview.innerHTML = currentContent + ` <span style="font-size:10px; color:#10b981; margin-left:5px;"><i class="bi bi-stars"></i> Sugerido</span>`;
                                }
                            }, 100);
                        }
                    } else {
                        // Se não tem sugestão
                        if (selectTipo.value == "" && preview) {
                            preview.innerHTML = "";
                        } else {
                            selectTipo.dispatchEvent(new Event('change'));
                        }
                    }
                })
                .catch(err => {
                    console.error('Erro na sugestão:', err);
                    if (selectTipo.value == "" && preview) preview.innerHTML = "";
                });
            }, 800); 
        });
    }
});
</script>
</div>
