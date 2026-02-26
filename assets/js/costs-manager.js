/**
 * SGT CostsManager - Gerenciamento dinâmico de custos operacionais
 * Adiciona/remove itens e realiza cálculos em tempo real
 * VERSÃO CORRIGIDA v3.2
 */

const CostsManager = {
    counters: {
        salarios: 0,
        estadia: 0,
        consumos: 0,
        locacao: 0,
        admin: 0
    },

    init() {
        // NÃO cacheia templates aqui - cria dinamicamente quando necessário
        this.bindEvents();
        this.updateAllTotals();

        // Fase 3 Master-Detail: Restaura os itens salvos do banco na Edição
        setTimeout(() => this.carregarPlanilhaCompleta(), 100);
    },

    /**
     * Motor Master-Detail (Reconstruindo planilhas pela Memória do JS)
     * Lê os itens injetados através da tag SGT_DATA (Legado) ou SGT_EDIT_DATA (v2.0)
     */
    carregarPlanilhaCompleta() {
        const v2 = window.SGT_EDIT_DATA?.proposta?.planilha;
        const v1 = window.SGT_DATA?.itensSalvos;

        if (!v2 && (!v1 || Object.keys(v1).length === 0)) {
            console.log('ℹ️ Nenhuma planilha salva encontrada.');
            return;
        }

        console.log('🔄 Carregando itens salvos...', v2 ? '(Modo v2.0)' : '(Modo Legado)');

        const inject = (categoryName, list, fieldMapping) => {
            if (!list || !Array.isArray(list)) return;

            list.forEach(itemData => {
                this.addItem(categoryName);

                const container = document.getElementById(`list-${categoryName}`);
                const row = container.lastElementChild;
                if (!row) return;

                // Preencher select de Tipo/Função
                const typeSelect = row.querySelector('select[name$="[tipo]"], select[name$="[funcao]"]');
                if (typeSelect && fieldMapping.tipo) {
                    const tipoVal = itemData[fieldMapping.tipo];
                    if (tipoVal) {
                        if ($(typeSelect).hasClass('select2-hidden-accessible')) {
                            $(typeSelect).val(tipoVal).trigger('change');
                        } else {
                            typeSelect.value = tipoVal;
                            typeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                }

                // Preencher Marca (Locação)
                if (categoryName === 'locacao' && fieldMapping.marca) {
                    setTimeout(() => {
                        const marcaSelect = row.querySelector('select[name$="[marca]"]');
                        const marcaVal = itemData[fieldMapping.marca];
                        if (marcaSelect && marcaVal) {
                            if ($(marcaSelect).hasClass('select2-hidden-accessible')) {
                                $(marcaSelect).val(marcaVal).trigger('change');
                            } else {
                                marcaSelect.value = marcaVal;
                            }
                        }
                    }, 150);
                }

                // Preencher inputs mapeados
                Object.keys(fieldMapping.inputs).forEach(inputKey => {
                    const dbColName = fieldMapping.inputs[inputKey];
                    const inputEl = row.querySelector(`input[name$="[${inputKey}]"]`);
                    if (inputEl && itemData[dbColName] !== undefined && itemData[dbColName] !== null) {
                        inputEl.value = itemData[dbColName];
                        inputEl.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });

                this.calculateItemTotal(row);
            });
        };

        // Mapeamentos unificados
        inject('salarios', v2?.salarios || v1?.salarios, {
            tipo: v2 ? 'funcao' : 'id_funcao',
            inputs: { quantidade: 'quantidade', valor: 'valor', encargos: 'encargos', dias: 'dias' }
        });

        inject('estadia', v2?.estadias || v1?.estadia, {
            tipo: v2 ? 'tipo' : 'id_estadia',
            inputs: { quantidade: 'quantidade', valor: v2 ? 'valor' : 'valor_unitario', noites: v2 ? 'noites' : 'dias' }
        });

        inject('consumos', v2?.consumos || v1?.consumo, {
            tipo: v2 ? 'tipo' : 'id_consumo',
            inputs: { quantidade: 'quantidade', kml: v2 ? 'kml' : 'consumo_kml', valor_litro: v2 ? 'valor_litro' : 'valor_litro', km: v2 ? 'km' : 'km_total' }
        });

        inject('locacao', v2?.locacoes || v1?.locacao, {
            tipo: v2 ? 'tipo' : 'id_locacao',
            marca: v2 ? 'marca' : 'id_marca',
            inputs: { quantidade: 'quantidade', valor: v2 ? 'valor' : 'valor_mensal', dias: 'dias' }
        });

        inject('admin', v2?.admin || v1?.admin, {
            tipo: v2 ? 'tipo' : 'id_custo_admin',
            inputs: { quantidade: 'quantidade', valor: 'valor' }
        });

        setTimeout(() => this.updateAllTotals(), 500);
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
        if (target.tagName === 'SELECT') {
            const item = target.closest('.cost-item');
            if (item) {
                const option = target.options[target.selectedIndex];
                const valorDefault = option?.dataset?.valor;

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
                                    opt.value = m.id;
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
                    const litro = option?.dataset?.litro;
                    const kml = option?.dataset?.kml;
                    if (litro) item.querySelector('input[name*="[valor_litro]"]').value = litro;
                    if (kml) item.querySelector('input[name*="[kml]"]').value = kml;
                } else if (valorDefault) {
                    const inputValor = item.querySelector('input[name*="[valor]"]');
                    if (inputValor) inputValor.value = valorDefault;
                }

                this.calculateItemTotal(item);
            }
        }
    },

    handleClick(e) {
        const target = e.target;
        if (target.closest('.btn-remove')) {
            const item = target.closest('.cost-item');
            this.removeItem(item);
        }
        if (target.closest('.btn-km-small')) {
            const input = target.closest('.input-group').querySelector('input');
            input?.focus();
            if (window.openCalculator) window.openCalculator();
        }
    },

    addItem(type) {
        this.counters[type]++;
        const id = `${type}_${this.counters[type]}`;
        const container = document.getElementById(`list-${type}`);

        if (!container) return;

        // CRIA TEMPLATE DINAMICAMENTE (não cacheado)
        const html = this.getTemplate(type, id);
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

    /**
     * NOVO: Gera template dinamicamente com dados atuais do SGT_DATA
     */
    getTemplate(type, id) {
        const data = window.SGT_DATA || {};

        switch (type) {
            case 'salarios':
                return this.createSalarioHTML(id, data.opcoesFuncao || []);
            case 'estadia':
                return this.createEstadiaHTML(id, data.opcoesEstadia || []);
            case 'consumos':
                return this.createConsumoHTML(id, data.opcoesConsumo || []);
            case 'locacao':
                return this.createLocacaoHTML(id, data.opcoesLocacao || []);
            case 'admin':
                return this.createAdminHTML(id, data.opcoesAdmin || []);
            default:
                return '';
        }
    },

    createSalarioHTML(id, opcoes) {
        const optionsHtml = opcoes.map(f =>
            `<option value="${f.id}" data-valor="${f.valor}">${f.nome}</option>`
        ).join('');

        return `
            <div class="cost-item" data-id="${id}" data-type="salario">
                <div class="cost-icon"><i class="bi bi-person"></i></div>
                <div class="cost-details">
                    <div>
                        <label class="form-label">Função</label>
                        <select name="salarios[${id}][funcao]" class="form-select" required>
                            <option value="">Selecione...</option>
                            ${optionsHtml}
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Qtd</label>
                        <input type="number" name="salarios[${id}][quantidade]" class="form-control" value="1" min="1" required autocomplete="off">
                    </div>
                    <div>
                        <label class="form-label">Valor Unit.</label>
                        <input type="number" name="salarios[${id}][valor]" class="form-control" step="0.01" required autocomplete="off">
                    </div>
                    <div>
                        <label class="form-label">Encargos %</label>
                        <input type="number" name="salarios[${id}][encargos]" class="form-control" value="67" step="0.1" required autocomplete="off">
                    </div>
                    <div>
                        <label class="form-label">Dias</label>
                        <input type="number" name="salarios[${id}][dias]" class="form-control" value="1" min="1" required autocomplete="off">
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

    createEstadiaHTML(id, opcoes) {
        const optionsHtml = opcoes.map(e =>
            `<option value="${e.id}" data-valor="${e.valor}">${e.nome}</option>`
        ).join('');

        return `
            <div class="cost-item" data-id="${id}" data-type="estadia">
                <div class="cost-icon"><i class="bi bi-house"></i></div>
                <div class="cost-details">
                    <div>
                        <label class="form-label">Tipo</label>
                        <select name="estadias[${id}][tipo]" class="form-select" required>
                            <option value="">Selecione...</option>
                            ${optionsHtml}
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

    createConsumoHTML(id, opcoes) {
        const optionsHtml = opcoes.map(c =>
            `<option value="${c.id}" data-litro="${c.litro}" data-kml="${c.kml}">${c.nome}</option>`
        ).join('');

        return `
            <div class="cost-item" data-id="${id}" data-type="consumo">
                <div class="cost-icon"><i class="bi bi-fuel-pump"></i></div>
                <div class="cost-details cost-details-fuel">
                    <div>
                        <label class="form-label">Combustível</label>
                        <select name="consumos[${id}][tipo]" class="form-select" required>
                            <option value="">Selecione...</option>
                            ${optionsHtml}
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

    createLocacaoHTML(id, opcoes) {
        const optionsHtml = opcoes.map(l =>
            `<option value="${l.id}" data-valor="${l.valor}">${l.nome}</option>`
        ).join('');

        return `
            <div class="cost-item" data-id="${id}" data-type="locacao">
                <div class="cost-icon"><i class="bi bi-tools"></i></div>
                <div class="cost-details">
                    <div>
                        <label class="form-label">Equipamento</label>
                        <select name="locacoes[${id}][tipo]" class="form-select" required>
                            <option value="">Selecione...</option>
                            ${optionsHtml}
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

    createAdminHTML(id, opcoes) {
        const optionsHtml = opcoes.map(a =>
            `<option value="${a.id}" data-valor="${a.valor}">${a.nome}</option>`
        ).join('');

        return `
            <div class="cost-item" data-id="${id}" data-type="admin">
                <div class="cost-icon"><i class="bi bi-briefcase"></i></div>
                <div class="cost-details">
                    <div>
                        <label class="form-label">Custo</label>
                        <select name="admin[${id}][tipo]" class="form-select" required>
                            <option value="">Selecione...</option>
                            ${optionsHtml}
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
    },

    removeItem(item) {
        item.style.opacity = '0';
        setTimeout(() => {
            item.remove();
            this.updateAllTotals();
        }, 300);
    },

    initItemSelects(item) {
        $(item).find('select').select2({
            theme: 'default',
            width: '100%'
        });

        $(item).find('select').on('select2:select', (e) => {
            e.target.dispatchEvent(new Event('change', { bubbles: true }));
        });
    },

    calculateItemTotal(item) {
        const type = item.dataset.type;
        let total = 0;

        try {
            if (type === 'salario') {
                const qtd = parseFloat(item.querySelector('input[name*="[quantidade]"]')?.value) || 0;
                const valor = parseFloat(item.querySelector('input[name*="[valor]"]')?.value) || 0;
                const encargos = parseFloat(item.querySelector('input[name*="[encargos]"]')?.value) || 0;
                const dias = parseFloat(item.querySelector('input[name*="[dias]"]')?.value) || 0;

                total = (valor * (1 + encargos / 100) / 30) * qtd * dias;
                this.updateMealQuantities();

            } else if (type === 'estadia') {
                const qtd = parseFloat(item.querySelector('input[name*="[quantidade]"]')?.value) || 0;
                const valor = parseFloat(item.querySelector('input[name*="[valor]"]')?.value) || 0;
                const noites = parseFloat(item.querySelector('input[name*="[noites]"]')?.value) || 0;
                total = qtd * valor * noites;

            } else if (type === 'consumo') {
                const qtd = parseFloat(item.querySelector('input[name*="[quantidade]"]')?.value) || 0;
                const kml = parseFloat(item.querySelector('input[name*="[kml]"]')?.value) || 1;
                const preco = parseFloat(item.querySelector('input[name*="[valor_litro]"]')?.value) || 0;
                const km = parseFloat(item.querySelector('input[name*="[km]"]')?.value) || 0;

                if (kml > 0) total = (km / kml) * preco * qtd;

            } else if (type === 'locacao') {
                const qtd = parseFloat(item.querySelector('input[name*="[quantidade]"]')?.value) || 0;
                const valor = parseFloat(item.querySelector('input[name*="[valor]"]')?.value) || 0;
                const dias = parseFloat(item.querySelector('input[name*="[dias]"]')?.value) || 0;
                total = (qtd * valor / 30) * dias;

            } else if (type === 'admin') {
                const qtd = parseFloat(item.querySelector('input[name*="[quantidade]"]')?.value) || 0;
                const valor = parseFloat(item.querySelector('input[name*="[valor]"]')?.value) || 0;
                total = qtd * valor;
            }

            const display = item.querySelector('.summary-box-display');
            if (display) display.value = this.formatCurrency(total);

            item.dataset.total = total;

        } catch (e) {
            console.error('Erro cálculo item:', e);
        }

        this.updateAllTotals();
    },

    updateAllTotals() {
        const categories = {
            'salario': { list: 'list-salarios', hidden: 'hidden_total_custos_salarios', display: 'resumo-salarios-display' },
            'estadia': { list: 'list-estadia', hidden: 'hidden_total_custos_estadia', display: 'resumo-estadia-display' },
            'consumo': { list: 'list-consumos', hidden: 'hidden_total_custos_consumos', display: 'resumo-consumos-display' },
            'locacao': { list: 'list-locacao', hidden: 'hidden_total_custos_locacao', display: 'resumo-locacao-display' },
            'admin': { list: 'list-admin', hidden: 'hidden_total_custos_admin', display: 'resumo-admin-display' }
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

            const hidden = document.getElementById(config.hidden);
            if (hidden) hidden.value = catTotal.toFixed(2);

            const display = document.getElementById(config.display);
            if (display) display.textContent = this.formatCurrency(catTotal);

            totalGeralCustos += catTotal;
        }

        const totalDisplay = document.getElementById('total-custos-geral');
        if (totalDisplay) totalDisplay.textContent = this.formatCurrency(totalGeralCustos);

        this.totalCustosCache = totalGeralCustos;
        this.updateFinalTotals();
    },

    updateMealQuantities() {
        let totalDias = 0;
        const salarios = document.querySelectorAll('#list-salarios .cost-item');
        salarios.forEach(item => {
            const qtd = parseFloat(item.querySelector('input[name*="[quantidade]"]')?.value) || 0;
            const dias = parseFloat(item.querySelector('input[name*="[dias]"]')?.value) || 0;
            totalDias += qtd * dias;
        });

        const estadias = document.querySelectorAll('#list-estadia .cost-item');
        estadias.forEach(item => {
            const select = item.querySelector('select[name*="[tipo]"]');
            const text = select?.options[select.selectedIndex]?.text || '';
            const normalizedText = text.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

            if (normalizedText.includes('refeicao') || normalizedText.includes('alimentacao')) {
                const inputQtd = item.querySelector('input[name*="[quantidade]"]');
                if (inputQtd && inputQtd.value != totalDias) {
                    inputQtd.value = totalDias || 1;
                    this.calculateItemTotal(item);
                }
            }
        });
    },

    updateFinalTotals() {
        const totalCustos = this.totalCustosCache || 0;

        const margemPercent = parseFloat(document.getElementById('percentual_lucro')?.value || 30);
        const valorLucro = totalCustos * (margemPercent / 100);

        const elLucro = document.getElementById('valor-lucro');
        if (elLucro) elLucro.textContent = '+ ' + this.formatCurrency(valorLucro);

        const hiddenLucro = document.getElementById('hidden_valor_lucro');
        if (hiddenLucro) hiddenLucro.value = valorLucro.toFixed(2);

        const desconto = parseFloat(document.getElementById('valor_desconto')?.value || 0);

        const subtotal = totalCustos + valorLucro;
        const hiddenSubtotal = document.getElementById('hidden_subtotal_com_lucro');
        if (hiddenSubtotal) hiddenSubtotal.value = subtotal.toFixed(2);

        const valorFinal = subtotal - desconto;
        const elFinal = document.getElementById('valor-final-proposta');
        if (elFinal) elFinal.textContent = this.formatCurrency(valorFinal);

        const hiddenFinal = document.getElementById('hidden_valor_final_proposta');
        if (hiddenFinal) hiddenFinal.value = valorFinal.toFixed(2);

        const entradaPercent = parseFloat(document.getElementById('mobilizacao_percentual')?.value || 30);
        const entradaValor = valorFinal * (entradaPercent / 100);
        const restanteValor = valorFinal - entradaValor;
        const restantePercent = 100 - entradaPercent;

        const elMobValor = document.getElementById('mobilizacao_valor_display');
        if (elMobValor) elMobValor.value = this.formatCurrency(entradaValor);

        const hiddenMobValor = document.getElementById('hidden_mobilizacao_valor');
        if (hiddenMobValor) hiddenMobValor.value = entradaValor.toFixed(2);

        const elRestPercent = document.getElementById('restante_percentual_display');
        if (elRestPercent) elRestPercent.value = restantePercent;

        const hiddenRestPercent = document.getElementById('hidden_restante_percentual');
        if (hiddenRestPercent) hiddenRestPercent.value = restantePercent;

        const elRestValor = document.getElementById('restante_valor_display');
        if (elRestValor) elRestValor.value = this.formatCurrency(restanteValor);

        const hiddenRestValor = document.getElementById('hidden_restante_valor');
        if (hiddenRestValor) hiddenRestValor.value = restanteValor.toFixed(2);
    },

    formatCurrency(val) {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);
    },

    /**
     * Limpa todas as linhas de custos da planilha
     */
    clearAll() {
        console.log('🧹 Limpando planilha de custos...');
        document.querySelectorAll('.cost-item').forEach(item => item.remove());
        this.counters = { salarios: 0, estadia: 0, consumos: 0, locacao: 0, admin: 0 };
    },

    /**
     * Métodos auxiliares para carregar itens
     */
    addFuncao(data) {
        this.addItem('salarios');
        const row = document.getElementById('list-salarios')?.lastElementChild;
        if (!row) return;

        this._fillRow(row, data, {
            'funcao': 'funcao',
            'quantidade': 'quantidade',
            'valor': 'valor',
            'encargos': 'encargos',
            'dias': 'dias'
        });
    },

    addEstadia(data) {
        this.addItem('estadia');
        const row = document.getElementById('list-estadia')?.lastElementChild;
        if (!row) return;

        this._fillRow(row, data, {
            'tipo': 'tipo',
            'quantidade': 'quantidade',
            'valor': 'valor',
            'noites': 'noites'
        });
    },

    addConsumo(data) {
        this.addItem('consumos');
        const row = document.getElementById('list-consumos')?.lastElementChild;
        if (!row) return;

        this._fillRow(row, data, {
            'tipo': 'tipo',
            'quantidade': 'quantidade',
            'kml': 'kml',
            'valor_litro': 'valor_litro',
            'km': 'km'
        });
    },

    addLocacao(data) {
        this.addItem('locacao');
        const row = document.getElementById('list-locacao')?.lastElementChild;
        if (!row) return;

        this._fillRow(row, data, {
            'tipo': 'tipo',
            'marca': 'marca',
            'quantidade': 'quantidade',
            'valor': 'valor',
            'dias': 'dias'
        });
    },

    addAdmin(data) {
        this.addItem('admin');
        const row = document.getElementById('list-admin')?.lastElementChild;
        if (!row) return;

        this._fillRow(row, data, {
            'tipo': 'tipo',
            'quantidade': 'quantidade',
            'valor': 'valor',
            'periodo': 'periodo'
        });
    },

    /**
     * Preenche os campos de uma linha baseado no mapeamento
     * @private
     */
    _fillRow(row, data, mapping) {
        Object.entries(mapping).forEach(([formField, dbField]) => {
            const val = data[dbField];
            if (val === undefined || val === null) return;

            const input = row.querySelector(`[name$="[${formField}]"]`);
            if (!input) return;

            if (input.tagName === 'SELECT') {
                if ($(input).hasClass('select2-hidden-accessible')) {
                    $(input).val(val).trigger('change');
                } else {
                    input.value = val;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            } else {
                input.value = val;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        this.calculateItemTotal(row);
    }
};

window.CostsManager = CostsManager;
