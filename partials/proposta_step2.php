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
        <!-- Novo Campo: Tipo de Serviço (Classificação Interna) — usa mesmos itens do Painel de Serviços -->
        <div class="form-group">
            <label class="form-label" style="display:flex; align-items:center; gap:5px;">
                <i class="bi bi-tag-fill text-secondary"></i> Classificação <small class="text-muted fw-normal">(Interno)</small>
            </label>
            <select class="form-select" name="tipo_servico_id" id="tipo_servico_id">
                <option value="">-- Selecione --</option>
                <?php
                // ✅ UNIFICAÇÃO: Usando a mesma lista de serviços para a classificação técnica
                foreach ($servicos as $s): 
                    // Fallback para modelos que não tenham cor/icone definidos na tabela principal
                    $cor = $s['cor'] ?? '#10b981'; // Default verde SGT
                    $icone = $s['icone'] ?? 'briefcase';
                    $modelo = $s['modelo'] ?? 'ModeloPropostaDrone.docx';
                ?>
                    <option value="<?= $s['id'] ?>"
                            data-cor="<?= $cor ?>"
                            data-icone="<?= $icone ?>"
                            data-modelo="<?= $modelo ?>"
                            data-descricao="<?= htmlspecialchars($s['descricao'] ?? '') ?>"
                            <?= (isset($proposta['tipo_servico_id']) && $proposta['tipo_servico_id'] == $s['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <!-- Hidden inputs para persistir cor e modelo baseado na classificação -->
            <input type="hidden" name="cor" id="hidden_cor" value="<?= htmlspecialchars($proposta['cor'] ?? 'verde') ?>">
            <input type="hidden" name="modelo_docx" id="hidden_modelo_docx" value="<?= htmlspecialchars($proposta['modelo_docx'] ?? 'PropostaDrone') ?>">

            <!-- Preview do Badge (Perfumaria UI) -->
            <div id="tipoPreview" style="margin-top: 8px; min-height: 24px;"></div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const select = document.getElementById('tipo_servico_id');
                const preview = document.getElementById('tipoPreview');
                const tituloInput = document.getElementById('tipo_levantamento');
                const hiddenIdServico = document.getElementById('id_servico');
                const hiddenCor = document.getElementById('hidden_cor');
                const hiddenModelo = document.getElementById('hidden_modelo_docx');
                
                function getColorName(hex) {
                    if (!hex) return 'verde';
                    hex = hex.toLowerCase();
                    // Mapeamento para os temas do SGT Premium (verde, azul, laranja/marrom, cinza)
                    const map = {
                        'verde': ['#10b981', '#059669', '#16a34a', '#10b981'],
                        'azul': ['#3b82f6', '#2563eb', '#1d4ed8', '#1e3a8a', '#1e40af'],
                        'laranja': ['#f97316', '#ea580c', '#c2410c', '#f59e0b', '#d97706'],
                        'cinza': ['#64748b', '#475569', '#1e293b', '#94a3b8', '#334155']
                    };
                    
                    for (const [name, colors] of Object.entries(map)) {
                        if (colors.includes(hex)) return name;
                    }
                    
                    if (hex.startsWith('#1') || hex.startsWith('#0')) return 'verde';
                    if (hex.startsWith('#3') || hex.startsWith('#2') || hex.startsWith('#1e3')) return 'azul';
                    if (hex.startsWith('#f') || hex.startsWith('#e') || hex.startsWith('#d')) return 'laranja';
                    
                    return 'cinza';
                }

                function updateBadge() {
                    const option = select.options[select.selectedIndex];
                    if (select.value && option) {
                        const hexCor = option.getAttribute('data-cor') || '#10b981';
                        const icone = option.getAttribute('data-icone') || 'tag';
                        const modeloRaw = option.getAttribute('data-modelo') || 'ModeloPropostaDrone.docx';
                        const nome = option.text;
                        
                        // 1. Atualiza Cor Visual (Hex)
                        preview.innerHTML = `
                            <span class="badge-tipo" style="background:${hexCor}; display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:500; color:white;">
                                <i class="bi bi-${icone}" style="font-size:10px;"></i> ${nome}
                            </span>
                        `;

                        // 2. Atualiza Hidden Inputs para o Banco
                        let corName = getColorName(hexCor);
                        if (hiddenCor) hiddenCor.value = corName;
                        
                        // Normaliza modelo: tira prefixo e ext
                        let modeloBase = modeloRaw.replace('Modelo', '').replace('.docx', '');
                        if (!modeloBase) modeloBase = 'PropostaDrone';
                        
                        // O SGT usa modelos com a cor no nome (ex: PropostaDroneVerde)
                        // Para laranja, o modelo físico costuma ser "Marrom"
                        let corSufixo = corName.charAt(0).toUpperCase() + corName.slice(1);
                        if (corName === 'laranja') corSufixo = 'Marrom'; // Compatibilidade com arquivos físicos
                        
                        const modeloFinal = modeloBase + corSufixo;
                        if (hiddenModelo) hiddenModelo.value = modeloFinal;

                        // ✅ Sincroniza Rádio de Cores com base na Classificação (Sugestão automática)
                        const radios = document.getElementsByName('cor_visual');
                        radios.forEach(r => {
                            if(r.value === corName) r.checked = true;
                        });
                        if (hiddenCor) hiddenCor.value = corName;

                        // 3. PREENCHER AUTOMÁTICO (Título da Proposta e Finalidade/Descricao)
                        if (tituloInput && (!tituloInput.dataset.modificado || tituloInput.value === '')) {
                            tituloInput.value = 'Levantamento + ' + nome;
                        }
                        const finalidadeInput = document.getElementById('finalidade');
                        if (finalidadeInput && (!finalidadeInput.dataset.modificado || finalidadeInput.value === '')) {
                            finalidadeInput.value = option.getAttribute('data-descricao') || '';
                        }

                        // Sincronizar com ID Serviço original
                        if (hiddenIdServico) hiddenIdServico.value = select.value;
                    } else {
                        preview.innerHTML = '';
                        if (hiddenIdServico) hiddenIdServico.value = '';
                    }
                }
                
                if(select) {
                    select.addEventListener('change', updateBadge);
                    // Run once on load
                    setTimeout(updateBadge, 500); 
                }

                if (tituloInput) {
                    tituloInput.addEventListener('input', () => {
                        tituloInput.dataset.modificado = 'true';
                    });
                }
                const finalidadeInput = document.getElementById('finalidade');
                if (finalidadeInput) {
                    finalidadeInput.addEventListener('input', () => {
                        finalidadeInput.dataset.modificado = 'true';
                    });
                }
            });
            </script>
        </div>

        <div class="form-group">
            <label class="form-label" for="tipo_levantamento">Título na Proposta</label>
            <input type="text" name="tipo_levantamento" id="tipo_levantamento" class="form-control"
                   value="<?= htmlspecialchars($proposta['tipo_levantamento'] ?? '') ?>"
                   placeholder="Ex: Levantamento Planialtimétrico">
        </div>

        <!-- Hidden input for id_servico to satisfy backend needs without showing duplicate field -->
        <input type="hidden" name="id_servico" id="id_servico" value="<?= htmlspecialchars($proposta['id_servico'] ?? '') ?>">

        <!-- Seletor de Tema Visual (Garante a liberdade do usuário) -->
        <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 5px;">
            <label class="form-label"><i class="bi bi-palette-fill text-tech"></i> Identidade Visual da Proposta</label>
            <div style="display: flex; gap: 1rem; align-items: center; background: rgba(255,255,255,0.03); padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                
                <label class="color-option" style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <input type="radio" name="cor_visual" value="verde" <?= ($proposta['cor'] ?? 'verde') === 'verde' ? 'checked' : '' ?> onchange="atualizarCorProposta('verde')">
                    <span style="width: 24px; height: 24px; background: #10b981; border-radius: 50%; border: 2px solid rgba(255,255,255,0.2); display: inline-block;"></span>
                    <span>Verde SGT</span>
                </label>

                <label class="color-option" style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <input type="radio" name="cor_visual" value="azul" <?= ($proposta['cor'] ?? '') === 'azul' ? 'checked' : '' ?> onchange="atualizarCorProposta('azul')">
                    <span style="width: 24px; height: 24px; background: #3b82f6; border-radius: 50%; border: 2px solid rgba(255,255,255,0.2); display: inline-block;"></span>
                    <span>Azul Tech</span>
                </label>

                <label class="color-option" style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <input type="radio" name="cor_visual" value="laranja" <?= ($proposta['cor'] ?? '') === 'laranja' ? 'checked' : '' ?> onchange="atualizarCorProposta('laranja')">
                    <span style="width: 24px; height: 24px; background: #f97316; border-radius: 50%; border: 2px solid rgba(255,255,255,0.2); display: inline-block;"></span>
                    <span>Laranja Energia</span>
                </label>

                <label class="color-option" style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <input type="radio" name="cor_visual" value="cinza" <?= ($proposta['cor'] ?? '') === 'cinza' ? 'checked' : '' ?> onchange="atualizarCorProposta('cinza')">
                    <span style="width: 24px; height: 24px; background: #64748b; border-radius: 50%; border: 2px solid rgba(255,255,255,0.2); display: inline-block;"></span>
                    <span>Institucional</span>
                </label>

            </div>
            <script>
                function atualizarCorProposta(cor) {
                    const hCor = document.getElementById('hidden_cor');
                    if (hCor) hCor.value = cor;
                }
            </script>
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
            <div class="row-custom-9-3" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <div style="flex: 9; min-width: 300px;">
                    <label class="form-label">Descrição / Finalidade</label>
                    <textarea class="form-control" name="finalidade" id="finalidade" rows="3" placeholder="Descreva o objetivo do trabalho..."><?= htmlspecialchars($proposta['finalidade'] ?? '') ?></textarea>
                </div>
                <div style="flex: 3; min-width: 150px;">
                    <label class="form-label" for="area">Área</label>
                    <div style="display: flex; align-items: stretch;">
                        <input type="text" name="area" id="area" class="form-control" placeholder="0.00" inputmode="decimal" 
                               value="<?= htmlspecialchars($proposta['area'] ?? $proposta['area_obra'] ?? '') ?>"
                               style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: none;">
                        <select name="unidade_area" id="unidade_area" class="form-select" 
                                style="max-width: 80px; border-top-left-radius: 0; border-bottom-left-radius: 0; background-color: rgba(255,255,255,0.05); color: #f97316; font-weight: bold; border-left: 1px solid rgba(255,255,255,0.1);" aria-label="Unidade de medida">
                            <?php $ua = $proposta['unidade_area'] ?? 'm²'; ?>
                            <option value="m²" <?= $ua === 'm²' ? 'selected' : '' ?>>m²</option>
                            <option value="ha" <?= $ua === 'ha' ? 'selected' : '' ?>>ha</option>
                            <option value="km²" <?= $ua === 'km²' ? 'selected' : '' ?>>km²</option>
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
                    <?php $ac = $proposta['acesso_local'] ?? ''; ?>
                    <select name="acesso_local" id="acesso_local" class="form-select">
                        <option value="">-- Selecione --</option>
                        <option value="fácil – via asfaltada" <?= $ac==='fácil – via asfaltada'?'selected':'' ?>>Fácil – Via Asfaltada</option>
                        <option value="médio – estrada de terra trafegável" <?= $ac==='médio – estrada de terra trafegável'?'selected':'' ?>>Médio – Estrada de Terra</option>
                        <option value="difícil – trilha / acesso restrito" <?= $ac==='difícil – trilha / acesso restrito'?'selected':'' ?>>Difícil – Trilha / Acesso Restrito</option>
                        <option value="acesso controlado – portaria / vigilância" <?= $ac==='acesso controlado – portaria / vigilância'?'selected':'' ?>>Acesso Controlado (Portaria)</option>
                        <option value="somente pedestre" <?= $ac==='somente pedestre'?'selected':'' ?>>Somente Pedestre</option>
                        <option value="acesso por propriedade privada" <?= $ac==='acesso por propriedade privada'?'selected':'' ?>>Acesso por Propriedade Privada</option>
                        <option value="acesso por estrada vicinal" <?= $ac==='acesso por estrada vicinal'?'selected':'' ?>>Acesso por Estrada Vicinal</option>
                    </select>
                </div>

                <!-- 2. Cobertura Vegetal -->
                <div>
                    <label class="form-label" for="cobertura_vegetal">Cobertura Vegetal</label>
                    <?php $cv = $proposta['cobertura_vegetal'] ?? ''; ?>
                    <select name="cobertura_vegetal" id="cobertura_vegetal" class="form-select">
                        <option value="">-- Selecione --</option>
                        <option value="área limpa / solo exposto" <?= $cv==='área limpa / solo exposto'?'selected':'' ?>>Área Limpa / Solo Exposto</option>
                        <option value="pastagem baixa" <?= $cv==='pastagem baixa'?'selected':'' ?>>Pastagem Baixa</option>
                        <option value="vegetação rasteira" <?= $cv==='vegetação rasteira'?'selected':'' ?>>Vegetação Rasteira</option>
                        <option value="vegetação média (arbustos)" <?= $cv==='vegetação média (arbustos)'?'selected':'' ?>>Vegetação Média (Arbustos)</option>
                        <option value="vegetação densa" <?= $cv==='vegetação densa'?'selected':'' ?>>Vegetação Densa</option>
                        <option value="mata fechada" <?= $cv==='mata fechada'?'selected':'' ?>>Mata Fechada</option>
                        <option value="área urbana" <?= $cv==='área urbana'?'selected':'' ?>>Área Urbana</option>
                        <option value="área parcialmente arborizada" <?= $cv==='área parcialmente arborizada'?'selected':'' ?>>Área Parcialmente Arborizada</option>
                        <option value="reflorestamento / eucalipto" <?= $cv==='reflorestamento / eucalipto'?'selected':'' ?>>Reflorestamento / Eucalipto</option>
                        <option value="cultura agrícola" <?= $cv==='cultura agrícola'?'selected':'' ?>>Cultura Agrícola</option>
                    </select>
                </div>

                <!-- 3. Condições do Terreno -->
                <div>
                    <label class="form-label" for="tipo_terreno">Tipo de Terreno</label>
                    <?php $tt = $proposta['tipo_terreno'] ?? ''; ?>
                    <select name="tipo_terreno" id="tipo_terreno" class="form-select">
                        <option value="">-- Selecione --</option>
                        <option value="plano" <?= $tt==='plano'?'selected':'' ?>>Plano</option>
                        <option value="levemente ondulado" <?= $tt==='levemente ondulado'?'selected':'' ?>>Levemente Ondulado</option>
                        <option value="ondulado" <?= $tt==='ondulado'?'selected':'' ?>>Ondulado</option>
                        <option value="fortemente ondulado" <?= $tt==='fortemente ondulado'?'selected':'' ?>>Fortemente Ondulado</option>
                        <option value="montanhoso" <?= $tt==='montanhoso'?'selected':'' ?>>Montanhoso</option>
                        <option value="terreno irregular" <?= $tt==='terreno irregular'?'selected':'' ?>>Terreno Irregular</option>
                        <option value="área alagadiça" <?= $tt==='área alagadiça'?'selected':'' ?>>Área Alagadiça</option>
                        <option value="solo arenoso" <?= $tt==='solo arenoso'?'selected':'' ?>>Solo Arenoso</option>
                        <option value="solo argiloso" <?= $tt==='solo argiloso'?'selected':'' ?>>Solo Argiloso</option>
                        <option value="área urbanizada" <?= $tt==='área urbanizada'?'selected':'' ?>>Área Urbanizada</option>
                        <option value="área em terraplenagem" <?= $tt==='área em terraplenagem'?'selected':'' ?>>Área em Terraplenagem</option>
                        <option value="presença de taludes" <?= $tt==='presença de taludes'?'selected':'' ?>>Presença de Taludes</option>
                    </select>
                </div>

                <!-- 4. Restrições Aéreas -->
                <div>
                    <label class="form-label" for="restricoes_aereas">Restrições Aéreas</label>
                    <?php $ra = $proposta['restricoes_aereas'] ?? ''; ?>
                    <select name="restricoes_aereas" id="restricoes_aereas" class="form-select">
                        <option value="">-- Selecione --</option>
                        <option value="nenhuma restrição aparente" <?= $ra==='nenhuma restrição aparente'?'selected':'' ?>>Nenhuma Restrição Aparente</option>
                        <option value="proximidade de rede elétrica" <?= $ra==='proximidade de rede elétrica'?'selected':'' ?>>Rede Elétrica Próxima</option>
                        <option value="proximidade de torres de comunicação" <?= $ra==='proximidade de torres de comunicação'?'selected':'' ?>>Torres de Comunicação</option>
                        <option value="área urbana com edificações altas" <?= $ra==='área urbana com edificações altas'?'selected':'' ?>>Edificações Altas</option>
                        <option value="presença de pessoas no entorno" <?= $ra==='presença de pessoas no entorno'?'selected':'' ?>>Pessoas no Entorno</option>
                        <option value="proximidade de rodovia" <?= $ra==='proximidade de rodovia'?'selected':'' ?>>Proximidade de Rodovia</option>
                        <option value="proximidade de aeroporto / heliponto" <?= $ra==='proximidade de aeroporto / heliponto'?'selected':'' ?>>Aeroporto / Heliponto</option>
                        <option value="área militar" <?= $ra==='área militar'?'selected':'' ?>>Área Militar</option>
                        <option value="área ambiental protegida" <?= $ra==='área ambiental protegida'?'selected':'' ?>>Área Ambiental Protegida</option>
                        <option value="voo condicionado à autorização" <?= $ra==='voo condicionado à autorização'?'selected':'' ?>>Requer Autorização de Voo</option>
                        <option value="espaço aéreo controlado" <?= $ra==='espaço aéreo controlado'?'selected':'' ?>>Espaço Aéreo Controlado</option>
                        <option value="obstáculos verticais (árvores / postes)" <?= $ra==='obstáculos verticais (árvores / postes)'?'selected':'' ?>>Obstáculos Verticais</option>
                    </select>
                </div>

            </div>
        </div>
        
        <!-- ======================= RESTAURAÇÃO: COORDENADAS ======================= -->
        <div class="form-group" style="grid-column: 1 / -1; margin-top: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                <!-- Coordenadas GPS Mapeamento -->
                <div style="grid-column: 1 / -1; margin-bottom: 5px;">
                    <label class="form-label" for="coordenadas_gps">Coordenadas da Área / Marco (Opcional)</label>
                    <input type="text" name="coordenadas_gps" id="coordenadas_gps" class="form-control"
                           value="<?= htmlspecialchars($proposta['coordenadas_gps'] ?? '') ?>"
                           placeholder="Ex: -15.793889, -47.882778">
                </div>
            </div>
        </div>
        <!-- ======================= FIM RESTAURAÇÃO ======================= -->

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
