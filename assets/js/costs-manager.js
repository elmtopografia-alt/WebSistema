/**
 * SGT CostsManager - Gerenciamento dinâmico de custos operacionais
 * Adiciona/remove itens e realiza cálculos em tempo real
 */

const CostsManager = {
    counters: {
        salarios: 0,
        estadia: 0,
        consumos: 0,
        locacao: 0,
        admin: 0
    },

    templates: {},

    init() {
        this.cacheTemplates();
        this.bindEvents();
        // Inicializa totais zerados
        this.updateAllTotals();

        // Fase 3 Master-Detail: Restaura os itens salvos do banco na Edição
        setTimeout(() => this.loadSavedItems(), 100);
    },

    /**
     * Motor Master-Detail (Reconstruindo planilhas pela Memória do JS)
     * Lê os itens injetados através da tag SGT_DATA no criar_proposta.php
     */
    loadSavedItems() {
        if (!window.SGT_DATA || !window.SGT_DATA.itensSalvos) return;
        const saved = window.SGT_DATA.itensSalvos;
        
        // Helper para injetar a linha e setar os valores
        const inject = (categoryName, list, fieldMapping) => {
            if (!list || !Array.isArray(list)) return;
            
            list.forEach(itemData => {
                // Emula um clique no botão "Adicionar" daquele painel
                this.addItem(categoryName);
                
                // Pega a linha recém-criada (a última do container)
                const container = document.getElementById(`list-${categoryName}`);
                const row = container.lastElementChild;
                if (!row) return;

                // Preencher select de Tipo
                const typeSelect = row.querySelector('select[name$="[tipo]"], select[name$="[funcao]"]');
                if (typeSelect && fieldMapping.tipo) {
                    typeSelect.value = itemData[fieldMapping.tipo];
                    // Dispara select2 nativo pra frente se estiver hidratado
                    if ($(typeSelect).hasClass('select2-hidden-accessible')) {
                        $(typeSelect).val(itemData[fieldMapping.tipo]).trigger('change');
                    } else {
                        typeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }

                // Preencher Marca (Locação) se houver timeout (pra dar tempo do select pai inflar a marca)
                if (categoryName === 'locacao' && fieldMapping.marca) {
                    setTimeout(() => {
                        const marcaSelect = row.querySelector('select[name$="[marca]"]');
                        if (marcaSelect) {
                            marcaSelect.value = itemData[fieldMapping.marca];
                            if ($(marcaSelect).hasClass('select2-hidden-accessible')) {
                                $(marcaSelect).val(itemData[fieldMapping.marca]).trigger('change');
                            }
                        }
                    }, 100);
                }

                // Dispara preenchimento dos inputs de texto/number mapeados
                Object.keys(fieldMapping.inputs).forEach(inputKey => {
                    const dbColName = fieldMapping.inputs[inputKey];
                    const inputEl = row.querySelector(`input[name$="[${inputKey}]"]`);
                    if (inputEl && itemData[dbColName] !== undefined) {
                        inputEl.value = itemData[dbColName];
                    }
                });

                // Força o recálculo do total dessa linha recém hidratada
                this.calculateItemTotal(row);
            });
        };

        // 1. Salários
        inject('salarios', saved.salarios, {
            tipo: 'id_funcao',
            inputs: { quantidade: 'quantidade', valor: 'salario_base', encargos: 'fator_encargos', dias: 'dias' }
        });

        // 2. Estadia
        inject('estadia', saved.estadia, {
            tipo: 'id_estadia',
            inputs: { quantidade: 'quantidade', valor: 'valor_unitario', noites: 'dias' }
        });

        // 3. Consumo
        inject('consumos', saved.consumo, {
            tipo: 'id_consumo',
            inputs: { quantidade: 'quantidade', kml: 'consumo_kml', valor_litro: 'valor_litro', km: 'km_total' }
        });

        // 4. Locação
        inject('locacao', saved.locacao, {
            tipo: 'id_locacao',
            marca: 'id_marca',
            inputs: { quantidade: 'quantidade', valor: 'valor_mensal', dias: 'dias' }
        });

        // 5. Admin
        inject('admin', saved.admin, {
            tipo: 'id_custo_admin',
            inputs: { quantidade: 'quantidade', valor: 'valor' }
        });

        // Após preencher a memória de todos, manda o total geral atualizar
        setTimeout(() => this.updateAllTotals(), 300);
    },

    cacheTemplates() {
        this.templates.salarios = this.createSalarioTemplate();
        this.templates.estadia = this.createEstadiaTemplate();
        this.templates.consumos = this.createConsumoTemplate();
        this.templates.locacao = this.createLocacaoTemplate();
        this.templates.admin = this.createAdminTemplate();
    },

    bindEvents() {
        // Botões de adicionar
        document.getElementById('add-salario')?.addEventListener('click', () => this.addItem('salarios'));
        document.getElementById('add-estadia')?.addEventListener('click', () => this.addItem('estadia'));
        document.getElementById('add-consumo')?.addEventListener('click', () => this.addItem('consumos'));
        document.getElementById('add-locacao')?.addEventListener('click', () => this.addItem('locacao'));
        document.getElementById('add-admin')?.addEventListener('click', () => this.addItem('admin'));

        // Delegação de eventos para cálculos e interações
        const panels = document.querySelector('.cost-panels');
        if (panels) {
            panels.addEventListener('input', (e) => this.handleInput(e));
            panels.addEventListener('change', (e) => this.handleChange(e));
            panels.addEventListener('click', (e) => this.handleClick(e));
        }

        // Listener para mudanças na margem de lucro/desconto (Step 4)
        // Isso deveria estar em outro lugar, mas como CostsManager gerencia valores...
        // Melhor deixar aqui ou criar um FinanceManager? Vou deixar aqui por enquanto.
        const step4 = document.getElementById('step-4');
        if (step4) {
            step4.addEventListener('input', (e) => this.updateFinalTotals());
        }
    },

    handleInput(e) {
        const target = e.target;
        if (target.matches('input[type="number"]')) {
            const item = target.closest('.cost-item');
            if (item) this.calculateItemTotal(item);
        }
    },

    handleChange(e) {
        const target = e.target;
        // Se mudou o select de tipo (preencher valor default)
        if (target.tagName === 'SELECT') {
            const item = target.closest('.cost-item');
            if (item) {
                const option = target.options[target.selectedIndex];
                const valorDefault = option.dataset.valor;

                // Se for estadia (Refeição), atualiza qtd
                if (item.dataset.type === 'estadia') {
                    this.updateMealQuantities();
                }

                // Se for locação, atualiza marca (dinâmico)
                if (item.dataset.type === 'locacao') {
                    if (target.name.includes('[tipo]')) {
                        const marcaSelect = item.querySelector('select[name*="[marca]"]');
                        const selectedId = target.value;

                        if (marcaSelect) {
                            marcaSelect.innerHTML = '<option value="">Selecione...</option>';
                            marcaSelect.disabled = true;

                            if (selectedId && window.SGT_DATA?.marcasPorTipo?.[selectedId]) {
                                window.SGT_DATA.marcasPorTipo[selectedId].forEach(m => {
                                    const opt = document.createElement('option');
                                    opt.value = m.id; // CORREÇÃO: Usando ID para salvar corretamente no banco
                                    opt.textContent = m.nome;
                                    marcaSelect.appendChild(opt);
                                });
                                marcaSelect.disabled = false;
                            }
                        }
                    }
                }

                // Se for combustível, tem lógica extra
                if (item.dataset.type === 'consumo') {
                    const litro = option.dataset.litro;
                    const kml = option.dataset.kml;
                    if (litro) item.querySelector('input[name*="[valor_litro]"]').value = litro;
                    if (kml) item.querySelector('input[name*="[kml]"]').value = kml;
                } else if (valorDefault) {
                    // Preenche valor unitário se existir campo
                    const inputValor = item.querySelector('input[name*="[valor]"]');
                    if (inputValor) inputValor.value = valorDefault;
                }

                this.calculateItemTotal(item);
            }
        }
    },

    handleClick(e) {
        const target = e.target;
        // Remover item
        if (target.closest('.btn-remove')) {
            const item = target.closest('.cost-item');
            this.removeItem(item);
        }
        // Calcular KM (botão calculadora no consumo)
        if (target.closest('.btn-km-small')) {
            // Lógica de chamar modal calculadora se necessário, ou apenas focar
            // Por enquanto, apenas foca no input
            const input = target.closest('.input-group').querySelector('input');
            input.focus();
            if (window.openCalculator) window.openCalculator();
        }
    },

    addItem(type) {
        this.counters[type]++;
        const id = `${type}_${this.counters[type]}`;
        const container = document.getElementById(`list-${type}`);

        if (!container || !this.templates[type]) return;

        const html = this.templates[type](id);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        const item = wrapper.firstElementChild;

        container.appendChild(item);

        // Animação
        item.style.opacity = '0';
        requestAnimationFrame(() => {
            item.style.transition = 'opacity 0.3s ease';
            item.style.opacity = '1';
        });

        this.initItemSelects(item);
    },

    removeItem(item) {
        const type = item.dataset.type; // salario, estadia...
        // Mapear singular para plural se necessário, ou recalcular tudo

        item.style.opacity = '0';
        setTimeout(() => {
            item.remove();
            this.updateAllTotals(); // Recalcula tudo após remover
        }, 300);
    },

    initItemSelects(item) {
        $(item).find('select').select2({
            theme: 'default',
            width: '100%'
        });
        // Select2 não dispara 'change' nativo bubble, precisa capturar via jQuery
        $(item).find('select').on('select2:select', (e) => {
            // Dispara evento nativo para nosso handler
            e.target.dispatchEvent(new Event('change', { bubbles: true }));
        });
    },

    calculateItemTotal(item) {
        const type = item.dataset.type;
        let total = 0;

        try {
            if (type === 'salario') {
                const qtd = parseFloat(item.querySelector('input[name*="[quantidade]"]').value) || 0;
                const valor = parseFloat(item.querySelector('input[name*="[valor]"]').value) || 0;
                const encargos = parseFloat(item.querySelector('input[name*="[encargos]"]').value) || 0;
                const dias = parseFloat(item.querySelector('input[name*="[dias]"]').value) || 0;

                // (Salario * (1 + Encargos%) / 30) * Qtd * Dias
                total = (valor * (1 + encargos / 100) / 30) * qtd * dias;

                // Atualiza Refeições quando muda equipe
                this.updateMealQuantities();

            } else if (type === 'estadia') {
                const qtd = parseFloat(item.querySelector('input[name*="[quantidade]"]').value) || 0;
                const valor = parseFloat(item.querySelector('input[name*="[valor]"]').value) || 0;
                const noites = parseFloat(item.querySelector('input[name*="[noites]"]').value) || 0;
                total = qtd * valor * noites;

            } else if (type === 'consumo') {
                const qtd = parseFloat(item.querySelector('input[name*="[quantidade]"]').value) || 0;
                const kml = parseFloat(item.querySelector('input[name*="[kml]"]').value) || 1;
                const preco = parseFloat(item.querySelector('input[name*="[valor_litro]"]').value) || 0;
                const km = parseFloat(item.querySelector('input[name*="[km]"]').value) || 0;

                // (KM / KmL) * Preço * QtdVeiculos
                if (kml > 0) total = (km / kml) * preco * qtd;

            } else if (type === 'locacao') {
                const qtd = parseFloat(item.querySelector('input[name*="[quantidade]"]').value) || 0;
                const valor = parseFloat(item.querySelector('input[name*="[valor]"]').value) || 0;
                const dias = parseFloat(item.querySelector('input[name*="[dias]"]').value) || 0;
                // Valor mensal / 30 * dias
                total = (qtd * valor / 30) * dias;

            } else if (type === 'admin') {
                const qtd = parseFloat(item.querySelector('input[name*="[quantidade]"]').value) || 0;
                const valor = parseFloat(item.querySelector('input[name*="[valor]"]').value) || 0;
                total = qtd * valor;
            }

            // Atualiza input visual do item
            const display = item.querySelector('.summary-box-display');
            if (display) display.value = this.formatCurrency(total);

            // Guarda valor bruto num data attribute para somar depois (opcional, ou recalcular tudo)
            item.dataset.total = total;

        } catch (e) {
            console.error('Erro cálculo item:', e);
        }

        this.updateAllTotals();
    },

    updateAllTotals() {
        // Categorias map: ID da lista -> ID do hidden
        const categories = {
            'salario': { list: 'list-salarios', hidden: 'hidden_total_custos_salarios', display: 'resumo-salarios-display' },
            'estadia': { list: 'list-estadia', hidden: 'hidden_total_custos_estadia', display: 'resumo-estadia-display' },
            'consumo': { list: 'list-consumos', hidden: 'hidden_total_custos_consumos', display: 'resumo-consumos-display' },
            'locacao': { list: 'list-locacao', hidden: 'hidden_total_custos_locacao', display: 'resumo-locacao-display' },
            'admin': { list: 'list-admin', hidden: 'hidden_total_custos_admin', display: 'resumo-admin-display' }
            // Ops, admin display id pode variar, vou checar partial
        };

        let totalGeralCustos = 0;

        for (const [type, config] of Object.entries(categories)) {
            let catTotal = 0;
            const container = document.getElementById(config.list);
            if (container) {
                const items = container.querySelectorAll('.cost-item');
                items.forEach(item => {
                    catTotal += parseFloat(item.dataset.total || 0);
                });
            }

            // Atualiza Hidden
            const hidden = document.getElementById(config.hidden);
            if (hidden) hidden.value = catTotal.toFixed(2);

            // Atualiza Display no Step 4 (Resumo)
            const display = document.getElementById(config.display);
            if (display) display.textContent = this.formatCurrency(catTotal);
            // Para admin, o ID no partial step 3 não existe display lá, mas no step 4 sim?
            // No partial step 4: resumo-salarios-display, resumo-estadia-display, resumo-consumos-display, resumo-locacao-display.
            // Falta resumo-admin-display no HTML do Step 4? 
            // O HTML do Step 4 mostra 4 linhas. Admin está "escondido" ou somado?
            // No original, admin era somado. Vou manter apenas 4 se o original era assim, ou somar admin no geral.
            // Vou somar ao totalGeral.

            totalGeralCustos += catTotal;
        }

        // Atualiza Total Geral de Custos (Step 4)
        const totalDisplay = document.getElementById('total-custos-geral');
        if (totalDisplay) totalDisplay.textContent = this.formatCurrency(totalGeralCustos);

        this.totalCustosCache = totalGeralCustos;
        this.updateFinalTotals();
    },

    updateMealQuantities() {
        // Calcula total dias da equipe (Qtd * Dias)
        let totalDias = 0;
        const salarios = document.querySelectorAll('#list-salarios .cost-item');
        salarios.forEach(item => {
            const qtd = parseFloat(item.querySelector('input[name*="[quantidade]"]').value) || 0;
            const dias = parseFloat(item.querySelector('input[name*="[dias]"]').value) || 0;
            totalDias += qtd * dias;
        });

        // Atualiza estadias "Refeição" ou "Alimentação"
        const estadias = document.querySelectorAll('#list-estadia .cost-item');
        estadias.forEach(item => {
            const select = item.querySelector('select[name*="[tipo]"]');
            const text = select.options[select.selectedIndex]?.text || '';
            const normalizedText = text.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

            if (normalizedText.includes('refeicao') || normalizedText.includes('alimentacao')) {
                const inputQtd = item.querySelector('input[name*="[quantidade]"]');
                // Só atualiza qtd (dias/noites geralmente é 1 para refeição consolidada, ou o usuário ajusta)
                // A lógica é: Qtd Refeições = Total Dias Trabalhados
                if (inputQtd && inputQtd.value != totalDias) {
                    inputQtd.value = totalDias || 1;
                    // Recalcula total da linha sem loop infinito (pois chamará updateAllTotals, não updateMealQuantities de novo se type for estadia)
                    // Mas updateMealQuantities é chamado no calculateItemTotal de SALARIO.
                    // Aqui estamos em ESTADIA.
                    this.calculateItemTotal(item);
                }
            }
        });
    },

    updateFinalTotals() {
        const totalCustos = this.totalCustosCache || 0;

        // Lucro
        const margemPercent = parseFloat(document.getElementById('percentual_lucro')?.value || 30);
        const valorLucro = totalCustos * (margemPercent / 100);

        document.getElementById('valor-lucro').textContent = '+ ' + this.formatCurrency(valorLucro);
        document.getElementById('hidden_valor_lucro').value = valorLucro.toFixed(2);

        // Desconto
        const desconto = parseFloat(document.getElementById('valor_desconto')?.value || 0);

        // Subtotal com lucro
        const subtotal = totalCustos + valorLucro;
        document.getElementById('hidden_subtotal_com_lucro').value = subtotal.toFixed(2);

        // Valor Final
        const valorFinal = subtotal - desconto;
        document.getElementById('valor-final-proposta').textContent = this.formatCurrency(valorFinal);
        document.getElementById('hidden_valor_final_proposta').value = valorFinal.toFixed(2);

        // Condições Pagamento
        const entradaPercent = parseFloat(document.getElementById('mobilizacao_percentual')?.value || 30);
        const entradaValor = valorFinal * (entradaPercent / 100);
        const restanteValor = valorFinal - entradaValor;
        const restantePercent = 100 - entradaPercent;

        document.getElementById('mobilizacao_valor_display').value = this.formatCurrency(entradaValor);
        document.getElementById('hidden_mobilizacao_valor').value = entradaValor.toFixed(2);

        document.getElementById('restante_percentual_display').value = restantePercent;
        document.getElementById('hidden_restante_percentual').value = restantePercent;

        document.getElementById('restante_valor_display').value = this.formatCurrency(restanteValor);
        document.getElementById('hidden_restante_valor').value = restanteValor.toFixed(2);
    },

    formatCurrency(val) {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);
    },

    templates: {},

    // ... createTemplates (manter os mesmos do arquivo anterior) ...
    createSalarioTemplate() {
        return (id) => `
            <div class="cost-item" data-id="${id}" data-type="salario">
                <div class="cost-icon"><i class="bi bi-person"></i></div>
                <div class="cost-details">
                    <div>
                        <label class="form-label">Função</label>
                        <select name="salarios[${id}][funcao]" class="form-select" required>
                            <option value="">Selecione...</option>
                            ${window.SGT_DATA?.opcoesFuncao?.map(f =>
            `<option value="${f.id}" data-valor="${f.valor}">${f.nome}</option>`
        ).join('') || ''}
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Qtd</label>
                        <input type="number" name="salarios[\${id}][quantidade]" class="form-control" value="1" min="1" required autocomplete="off">
                    </div>
                    <div>
                        <label class="form-label">Valor Unit.</label>
                        <input type="number" name="salarios[\${id}][valor]" class="form-control" step="0.01" required autocomplete="off">
                    </div>
                    <div>
                        <label class="form-label">Encargos %</label>
                        <input type="number" name="salarios[\${id}][encargos]" class="form-control" value="67" step="0.1" required autocomplete="off">
                    </div>
                    <div>
                        <label class="form-label">Dias</label>
                        <input type="number" name="salarios[\${id}][dias]" class="form-control" value="1" min="1" required autocomplete="off">
                    </div>
                    <div>
                        <label class="form-label">Total</label>
                        <input type="text" class="form-control summary-box-display" readonly value="R$ 0,00">
                    </div>
                </div>
                <button type="button" class="btn-remove" title="Remover">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
    },

    createEstadiaTemplate() {
        return (id) => `
            <div class="cost-item" data-id="${id}" data-type="estadia">
                <div class="cost-icon"><i class="bi bi-house"></i></div>
                <div class="cost-details">
                    <div>
                        <label class="form-label">Tipo</label>
                        <select name="estadias[${id}][tipo]" class="form-select" required>
                            <option value="">Selecione...</option>
                            ${window.SGT_DATA?.opcoesEstadia?.map(e =>
            `<option value="${e.id}" data-valor="${e.valor}">${e.nome}</option>`
        ).join('') || ''}
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Qtd</label>
                        <input type="number" name="estadias[${id}][quantidade]" class="form-control" value="1" min="1" required>
                    </div>
                    <div>
                        <label class="form-label">Valor Unit.</label>
                        <input type="number" name="estadias[${id}][valor]" class="form-control" step="0.01" required>
                    </div>
                    <div>
                        <label class="form-label">Dias</label>
                        <input type="number" name="estadias[${id}][noites]" class="form-control" value="1" min="1" required>
                    </div>
                    <div>
                        <label class="form-label">Total</label>
                        <input type="text" class="form-control summary-box-display" readonly value="R$ 0,00">
                    </div>
                </div>
                <button type="button" class="btn-remove" title="Remover">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
    },

    createConsumoTemplate() {
        return (id) => `
            <div class="cost-item" data-id="${id}" data-type="consumo">
                <div class="cost-icon"><i class="bi bi-fuel-pump"></i></div>
                <div class="cost-details cost-details-fuel">
                    <div>
                        <label class="form-label">Combustível</label>
                        <select name="consumos[${id}][tipo]" class="form-select" required>
                            <option value="">Selecione...</option>
                            ${window.SGT_DATA?.opcoesConsumo?.map(c =>
            `<option value="${c.id}" data-litro="${c.litro}" data-kml="${c.kml}">${c.nome}</option>`
        ).join('') || ''}
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Qtd</label>
                        <input type="number" name="consumos[${id}][quantidade]" class="form-control" value="1" min="1" required>
                    </div>
                    <div>
                        <label class="form-label">Km/L</label>
                        <input type="number" name="consumos[${id}][kml]" class="form-control" step="0.1" required>
                    </div>
                    <div>
                        <label class="form-label">R$/L</label>
                        <input type="number" name="consumos[${id}][valor_litro]" class="form-control" step="0.01" required>
                    </div>
                    <div>
                        <label class="form-label">KM Total</label>
                        <div class="input-group">
                            <input type="number" name="consumos[${id}][km]" class="form-control" required>
                            <button type="button" class="btn btn-outline btn-km-small" onclick="window.calcularDistancia(this)" title="Calcular Distância">
                                <i class="bi bi-calculator"></i>
                            </button>
                            <button type="button" class="btn btn-outline btn-km-small" onclick="window.verMapa(this)" title="Ver Rota">
                                <i class="bi bi-map"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Total</label>
                        <input type="text" class="form-control summary-box-display" readonly value="R$ 0,00">
                    </div>
                </div>
                <button type="button" class="btn-remove" title="Remover">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
    },

    createLocacaoTemplate() {
        return (id) => `
            <div class="cost-item" data-id="${id}" data-type="locacao">
                <div class="cost-icon"><i class="bi bi-tools"></i></div>
                <div class="cost-details">
                    <div>
                        <label class="form-label">Equipamento</label>
                        <select name="locacoes[${id}][tipo]" class="form-select" required>
                            <option value="">Selecione...</option>
                            ${window.SGT_DATA?.opcoesLocacao?.map(l =>
            `<option value="${l.id}" data-valor="${l.valor}">${l.nome}</option>`
        ).join('') || ''}
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Marca/Modelo</label>
                        <select name="locacoes[${id}][marca]" class="form-select" disabled>
                            <option value="">Selecione Equipamento...</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Qtd</label>
                        <input type="number" name="locacoes[${id}][quantidade]" class="form-control" value="1" min="1" required>
                    </div>
                    <div>
                        <label class="form-label">Valor/Mês</label>
                        <input type="number" name="locacoes[${id}][valor]" class="form-control" step="0.01" required>
                    </div>
                    <div>
                        <label class="form-label">Dias</label>
                        <input type="number" name="locacoes[${id}][dias]" class="form-control" value="1" min="1" required>
                    </div>
                    <div>
                        <label class="form-label">Total</label>
                        <input type="text" class="form-control summary-box-display" readonly value="R$ 0,00">
                    </div>
                </div>
                <button type="button" class="btn-remove" title="Remover">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
    },

    createAdminTemplate() {
        return (id) => `
            <div class="cost-item" data-id="${id}" data-type="admin">
                <div class="cost-icon"><i class="bi bi-briefcase"></i></div>
                <div class="cost-details">
                    <div>
                        <label class="form-label">Custo</label>
                        <select name="admin[${id}][tipo]" class="form-select" required>
                            <option value="">Selecione...</option>
                            ${window.SGT_DATA?.opcoesAdmin?.map(a =>
            `<option value="${a.id}" data-valor="${a.valor}">${a.nome}</option>`
        ).join('') || ''}
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Qtd</label>
                        <input type="number" name="admin[${id}][quantidade]" class="form-control" value="1" min="1" required>
                    </div>
                    <div>
                        <label class="form-label">Valor</label>
                        <input type="number" name="admin[${id}][valor]" class="form-control" step="0.01" required>
                    </div>
                    <div>
                        <label class="form-label">Período</label>
                        <input type="text" name="admin[${id}][periodo]" class="form-control" placeholder="Ex: Mensal">
                    </div>
                    <div>
                        <label class="form-label">Total</label>
                        <input type="text" class="form-control summary-box-display" readonly value="R$ 0,00">
                    </div>
                </div>
                <button type="button" class="btn-remove" title="Remover">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
    }
};

window.CostsManager = CostsManager;
