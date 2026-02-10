// calculos.js - Versão Premium Wizard
// calculos.js - Versão Premium Wizard (Otimizada)
document.addEventListener('DOMContentLoaded', function () {

    // --- OTIMIZAÇÃO: DOM CACHE & DEBOUNCE ---
    const DOM_CACHE = {
        store: new Map(),
        get(id) {
            if (!this.store.has(id)) {
                const el = document.getElementById(id);
                if (el) this.store.set(id, el);
            }
            return this.store.get(id);
        },
        clear() {
            this.store.clear();
            console.log('DOM Cache limpo');
        }
    };

    // --- POPULAR VARIÁVEIS DO PHP (SGT_DATA) ---
    const SGT = window.SGT_DATA || {};
    const buildOptions = (data) => {
        if (!data) return '';
        return data.map(i => {
            let ext = '';
            if (i.valor) ext += ` data-valor="${i.valor}"`;
            if (i.litro) ext += ` data-valor-litro="${i.litro}"`;
            if (i.kml) ext += ` data-consumo-kml="${i.kml}"`;
            return `<option value="${i.id}"${ext}>${i.nome}</option>`;
        }).join('');
    };

    const opcoesFuncaoHtml = buildOptions(SGT.opcoesFuncao);
    const opcoesEstadiaHtml = buildOptions(SGT.opcoesEstadia);
    const opcoesConsumoHtml = buildOptions(SGT.opcoesConsumo);
    const opcoesLocacaoHtml = buildOptions(SGT.opcoesLocacao);
    const opcoesAdminHtml = buildOptions(SGT.opcoesAdmin);

    // Variáveis usadas no resto do script
    const marcasPorTipo = SGT.marcasPorTipo || {};
    const enderecoEmpresa = SGT.enderecoEmpresa || '';

    const debounce = (func, wait) => {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    };

    const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v || 0);
    const parseMoney = (v) => {
        if (!v) return 0;
        if (typeof v === 'number') return v;
        if (/^-?\d+(\.\d+)?$/.test(v)) return parseFloat(v);
        return parseFloat(v.replace(/\./g, '').replace(',', '.')) || 0;
    };

    const getVal = (input) => input.valueAsNumber || parseMoney(input.value);

    const updateNameInput = (select) => {
        const item = select.closest('.cost-item');
        const nameInput = item.querySelector('.item-nome-hidden');
        if (nameInput && select.options[select.selectedIndex]) {
            nameInput.value = select.options[select.selectedIndex].text;
        }
    };

    const recalcularTudo = () => {
        // 1. Calcula Prazo (Prioridade Alta - Independente de Custos)
        try {
            const elCampo = document.getElementById('dias_campo');
            const elEscritorio = document.getElementById('dias_escritorio');
            const elPrazo = document.getElementById('prazo_execucao');

            const diasCampo = parseInt(elCampo?.value) || 0;
            const diasEscritorio = parseInt(elEscritorio?.value) || 0;
            const totalDias = diasCampo + diasEscritorio;

            if (elPrazo) {
                // Se total = 0, deixa vazio ou texto padrão? Usuário prefere ver resultado.
                // Se 1+1=2, mostra. Se 0+0=0, mostra vazio? Melhor mostrar feedback sempre que > 0.
                elPrazo.value = totalDias > 0 ? `${totalDias} dias úteis após campo` : '';
            }
        } catch (e) { console.error('Erro ao calcular prazo:', e); }

        // 2. Calcula Custos
        // console.time('Calculo'); // Debug performance
        let totais = { salarios: 0, estadia: 0, consumos: 0, locacao: 0, admin: 0 };
        let totalDiasEquipe = 0;

        const somarLista = (listId, tipo) => {
            let subtotal = 0;
            const container = DOM_CACHE.get(listId); // Usa Cache
            if (!container) return 0;

            // QuerySelectorAll não é cacheado pois itens mudam dinamicamente
            container.querySelectorAll('.cost-item').forEach(item => {
                const inputs = item.querySelectorAll('input');
                let valLinha = 0;

                if (tipo === 'salarios') {
                    const qtd = getVal(inputs[1]);
                    const base = getVal(inputs[2]);
                    const enc = getVal(inputs[3]);
                    const dias = getVal(inputs[4]);
                    valLinha = (qtd * base * (1 + enc / 100) / 30) * dias;
                    totalDiasEquipe += (qtd * dias);
                }
                else if (tipo === 'estadia') {
                    const select = item.querySelector('select');
                    if (select && select.options[select.selectedIndex]) {
                        const text = select.options[select.selectedIndex].text.toLowerCase();
                        if (text.includes('refeição') || text.includes('refeicao') || text.includes('alimentação') || text.includes('alimentacao')) {
                            if (totalDiasEquipe > 0) {
                                inputs[1].value = totalDiasEquipe;
                                if (getVal(inputs[3]) === 0) inputs[3].value = 1;
                            }
                        }
                    }
                    const qtd = getVal(inputs[1]);
                    const val = getVal(inputs[2]);
                    const dias = getVal(inputs[3]);
                    valLinha = qtd * val * dias;
                }
                else if (tipo === 'consumos') {
                    const qtd = getVal(inputs[1]);
                    const kml = getVal(inputs[2]) || 1;
                    const lit = getVal(inputs[3]);
                    const kmt = getVal(inputs[4]);
                    valLinha = (kml > 0) ? (kmt * lit / kml) * qtd : 0;
                }
                else if (tipo === 'locacao') {
                    const qtd = getVal(inputs[1]);
                    const val = getVal(inputs[2]);
                    const dias = getVal(inputs[3]);
                    valLinha = (qtd * val / 30) * dias;
                }
                else if (tipo === 'admin') {
                    const qtd = getVal(inputs[1]);
                    const val = getVal(inputs[2]);
                    valLinha = qtd * val;
                }

                item.querySelector('.total-linha').textContent = formatarMoeda(valLinha);
                subtotal += valLinha;
            });
            return subtotal;
        };

        totais.salarios = somarLista('list-salarios', 'salarios');
        totais.estadia = somarLista('list-estadia', 'estadia');
        totais.consumos = somarLista('list-consumos', 'consumos');
        totais.locacao = somarLista('list-locacao', 'locacao');
        totais.admin = somarLista('list-admin', 'admin');

        // Calcula Prazo de Entrega (Já processado no início da função)

        const updateSummary = (id, value) => {
            const el = DOM_CACHE.get(id); // Usa Cache
            if (el) {
                el.textContent = formatarMoeda(value);
                if (value > 0.01) {
                    el.parentElement.style.display = 'flex';
                    el.parentElement.classList.remove('d-none');
                } else {
                    el.parentElement.style.display = 'none';
                    el.parentElement.classList.add('d-none');
                }
            }
            // Also update the display element for Step 4 summary box
            const displayEl = DOM_CACHE.get(id + '-display');
            if (displayEl) {
                displayEl.textContent = formatarMoeda(value);
            }
        };

        updateSummary('resumo-salarios', totais.salarios);
        updateSummary('resumo-estadia', totais.estadia);
        updateSummary('resumo-consumos', totais.consumos);
        updateSummary('resumo-locacao', totais.locacao);
        updateSummary('resumo-admin', totais.admin);

        const geral = Object.values(totais).reduce((a, b) => a + b, 0);
        const elTotalGeral = DOM_CACHE.get('total-custos-geral');
        if (elTotalGeral) elTotalGeral.textContent = formatarMoeda(geral);

        const liveTotal = DOM_CACHE.get('live-total');
        if (liveTotal) liveTotal.textContent = formatarMoeda(geral);

        // Inputs e Totais Finais
        const elLucro = DOM_CACHE.get('percentual_lucro');
        const lucroPerc = parseFloat(elLucro ? elLucro.value : 0) || 0;
        const lucroValor = geral * (lucroPerc / 100);

        const elValorLucro = DOM_CACHE.get('valor-lucro');
        if (elValorLucro) elValorLucro.textContent = '+ ' + formatarMoeda(lucroValor);

        const subtotal = geral + lucroValor;
        const elDesconto = DOM_CACHE.get('valor_desconto');
        const desconto = parseFloat(elDesconto ? elDesconto.value : 0) || 0;
        const final = subtotal - desconto;

        const elValorFinal = DOM_CACHE.get('valor-final-proposta');
        if (elValorFinal) elValorFinal.textContent = formatarMoeda(final);

        const elMobPerc = DOM_CACHE.get('mobilizacao_percentual');
        const mobPerc = parseFloat(elMobPerc ? elMobPerc.value : 0) || 0;
        const mobVal = final * (mobPerc / 100);
        const restPerc = 100 - mobPerc;
        const restVal = final - mobVal;

        const elMobValDisp = DOM_CACHE.get('mobilizacao_valor_display');
        if (elMobValDisp) elMobValDisp.value = formatarMoeda(mobVal);

        const elRestPercDisp = DOM_CACHE.get('restante_percentual_display');
        if (elRestPercDisp) elRestPercDisp.value = restPerc.toFixed(0);

        const elRestValDisp = DOM_CACHE.get('restante_valor_display');
        if (elRestValDisp) elRestValDisp.value = formatarMoeda(restVal);

        // Hidden inputs
        const setHidden = (id, val) => { const el = DOM_CACHE.get(id); if (el) el.value = val; };
        setHidden('hidden_total_custos_salarios', totais.salarios);
        setHidden('hidden_total_custos_estadia', totais.estadia);
        setHidden('hidden_total_custos_consumos', totais.consumos);
        setHidden('hidden_total_custos_locacao', totais.locacao);
        setHidden('hidden_total_custos_admin', totais.admin);
        setHidden('hidden_valor_lucro', lucroValor);
        setHidden('hidden_subtotal_com_lucro', subtotal);
        setHidden('hidden_valor_final_proposta', final);
        setHidden('hidden_mobilizacao_valor', mobVal);
        setHidden('hidden_restante_percentual', restPerc);
        setHidden('hidden_restante_valor', restVal);

        // console.timeEnd('Calculo');
    };

    // Debounce no recálculo para evitar travamento em digitação rápida
    const debouncedRecalc = debounce(recalcularTudo, 50);

    const addRow = (listId, template) => {
        const list = DOM_CACHE.get(listId) || document.getElementById(listId);
        const div = document.createElement('div');
        div.className = 'cost-item';
        div.innerHTML = template;
        list.appendChild(div);

        const sel = div.querySelector('select');
        if (sel) {
            updateNameInput(sel);
            sel.addEventListener('change', function () {
                updateNameInput(this);
                const opt = this.options[this.selectedIndex];
                const val = opt.getAttribute('data-valor');
                if (val) {
                    const valInput = div.querySelector('.autofill-valor');
                    if (valInput) valInput.value = val;
                }
                const lit = opt.getAttribute('data-valor-litro');
                const kml = opt.getAttribute('data-consumo-kml');
                if (lit) div.querySelectorAll('input')[3].value = lit;
                if (kml) div.querySelectorAll('input')[2].value = kml;

                recalcularTudo(); // Mudança de combo é imediata
            });
            if (listId === 'list-locacao') {
                sel.addEventListener('change', function () {
                    const idLoc = this.value;
                    // Find the select inside div-marca directly
                    const selectMarca = div.querySelector('.div-marca select');

                    let htmlOps = '<option value="">-</option>';
                    if (marcasPorTipo[idLoc] && marcasPorTipo[idLoc].length > 0) {
                        marcasPorTipo[idLoc].forEach(m => {
                            htmlOps += `<option value="${m.id}">${m.nome}</option>`;
                        });
                        selectMarca.innerHTML = htmlOps;
                        selectMarca.disabled = false; // Enable it
                    } else {
                        selectMarca.innerHTML = '<option value="">-</option>';
                        selectMarca.disabled = true; // Disable if no brands
                    }
                });
            }
        }
        recalcularTudo();
    };

    // [...] (Mantendo funções de Distância e Mapa iguais, assume-se que usam document.querySelector que é ok)
    // Se quiser otimizar lá também pode, mas calcularDistancia é on-click, não crítico.

    // Templates e Listeners de botão mantidos...
    // Vou usar os mesmos templates que já estavam (o usuário não pediu para mudar templates)


    // --- CÁLCULO DE DISTÂNCIA (Nominatim + OSRM) ---
    window.calcularDistancia = async (btn) => {
        const row = btn.closest('.cost-item');
        const inputKm = row.querySelector('input[name="consumo_km_total[]"]');
        const originalText = btn.innerHTML;

        // 1. Obter Endereços
        const endObra = document.querySelector('input[name="endereco"]').value;
        const cidadeObra = document.querySelector('input[name="cidade"]').value;
        const estadoObra = document.querySelector('select[name="estado"]').value;

        if (!endObra || !cidadeObra) {
            alert('Por favor, preencha o Endereço e Cidade da Obra no Passo 1 antes de calcular.');
            return;
        }

        if (!enderecoEmpresa) {
            alert('Endereço da empresa não configurado em "Minha Empresa".');
            return;
        }

        const origem = enderecoEmpresa; // Já vem formatado do PHP
        const destino = `${endObra}, ${cidadeObra} - ${estadoObra}`;

        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> ...';

        try {
            // 2. Geocoding (Nominatim)
            const getCoords = async (address) => {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`;
                const res = await fetch(url);
                const data = await res.json();
                if (data && data.length > 0) return { lat: data[0].lat, lon: data[0].lon };
                throw new Error(`Endereço não encontrado: ${address}`);
            };

            const [coordOrigem, coordDestino] = await Promise.all([
                getCoords(origem),
                getCoords(destino)
            ]);

            // 3. Routing (OSRM)
            // OSRM espera: lon,lat;lon,lat
            const urlRoute = `https://router.project-osrm.org/route/v1/driving/${coordOrigem.lon},${coordOrigem.lat};${coordDestino.lon},${coordDestino.lat}?overview=false`;
            const resRoute = await fetch(urlRoute);
            const dataRoute = await resRoute.json();

            if (dataRoute.code !== 'Ok' || !dataRoute.routes || dataRoute.routes.length === 0) {
                throw new Error('Não foi possível calcular a rota.');
            }

            const distanciaMetros = dataRoute.routes[0].distance;
            const distanciaKm = (distanciaMetros / 1000).toFixed(1);

            // Ida e Volta (Opcional, mas comum em orçamentos)
            // Vamos perguntar ou assumir Ida e Volta? O padrão costuma ser Km Total percorrido.
            // Se o usuário quiser ida e volta, ele dobra. Mas vamos entregar a distância da rota.
            // MELHORIA: Multiplicar por 2 automaticamente? O campo diz "Km Total".
            // Vamos colocar a distância de IDA e VOLTA (x2) pois é custo de deslocamento.

            const kmTotal = (distanciaKm * 2).toFixed(1);

            inputKm.value = kmTotal;
            recalcularTudo();
            alert(`Distância calculada: ${distanciaKm} km (Ida). Definido ${kmTotal} km (Ida e Volta).`);

        } catch (error) {
            console.error(error);
            alert('Erro ao calcular: ' + error.message + '\n\nTente inserir manualmente.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    };

    // --- VISUALIZAR MAPA (Google Maps) ---
    window.verMapa = (btn) => {
        const endObra = document.querySelector('input[name="endereco"]').value;
        const cidadeObra = document.querySelector('input[name="cidade"]').value;
        const estadoObra = document.querySelector('select[name="estado"]').value;

        if (!endObra || !cidadeObra) {
            alert('Por favor, preencha o Endereço e Cidade da Obra no Passo 1.');
            return;
        }

        if (!enderecoEmpresa) {
            alert('Endereço da empresa não configurado.');
            return;
        }

        const origem = encodeURIComponent(enderecoEmpresa);
        const destino = encodeURIComponent(`${endObra}, ${cidadeObra} - ${estadoObra}`);

        // Abre Google Maps em nova aba
        const url = `https://www.google.com/maps/dir/?api=1&origin=${origem}&destination=${destino}`;
        window.open(url, '_blank');
    };

    // Templates (Cost Cards)
    const tSalario = `
        <div class="cost-icon"><i class="bi bi-person"></i></div>
        <div class="cost-details">
            <div><label class="small text-muted">Função</label><select name="salario_id_funcao[]" class="form-select form-select-sm"><option value="">Selecione</option>${opcoesFuncaoHtml}</select><input type="hidden" name="salario_nome[]" class="item-nome-hidden"></div>
            <div><label class="small text-muted">Qtd</label><input type="number" name="salario_qtd[]" class="form-control form-control-sm recalc" value="1"></div>
            <div><label class="small text-muted">Salário Base</label><input type="number" name="salario_valor[]" class="form-control form-control-sm recalc autofill-valor" step="0.01"></div>
            <div><label class="small text-muted">Enc %</label><input type="number" name="encargos[]" class="form-control form-control-sm recalc" value="67"></div>
            <div><label class="small text-muted">Dias</label><input type="number" name="salario_dias[]" class="form-control form-control-sm recalc" value="1"></div>
        </div>
        <div class="cost-total total-linha">0,00</div>
        <button type="button" class="btn btn-link text-danger remove-row"><i class="bi bi-trash"></i></button>
    `;

    const tEstadia = `
        <div class="cost-icon"><i class="bi bi-house"></i></div>
        <div class="cost-details">
            <div><label class="small text-muted">Tipo</label><select name="estadia_id[]" class="form-select form-select-sm"><option value="">Selecione</option>${opcoesEstadiaHtml}</select><input type="hidden" name="estadia_nome[]" class="item-nome-hidden"></div>
            <div><label class="small text-muted">Qtd</label><input type="number" name="estadia_qtd[]" class="form-control form-control-sm recalc" value="1"></div>
            <div><label class="small text-muted">Valor R$</label><input type="number" name="estadia_valor[]" class="form-control form-control-sm recalc autofill-valor" step="0.01"></div>
            <div><label class="small text-muted">Dias</label><input type="number" name="estadia_dias[]" class="form-control form-control-sm recalc" value="1"></div>
            <div></div> <!-- Spacer -->
        </div>
        <div class="cost-total total-linha">0,00</div>
        <button type="button" class="btn btn-link text-danger remove-row"><i class="bi bi-trash"></i></button>
    `;

    const tConsumo = `
        <div class="cost-icon"><i class="bi bi-fuel-pump"></i></div>
        <div class="cost-details cost-details-fuel" style="display: grid; grid-template-columns: 110px 80px 80px 80px 1fr !important; gap: 0.5rem; align-items: flex-end; width: 100%;">
            <div><label class="small text-muted">Combustível</label><select name="consumo_id[]" class="form-select form-select-sm"><option value="">Selecione</option>${opcoesConsumoHtml}</select><input type="hidden" name="consumo_nome[]" class="item-nome-hidden"></div>
            <div><label class="small text-muted">Qtd Veic</label><input type="number" name="consumo_qtd[]" class="form-control form-control-sm recalc" value="1"></div>
            <div><label class="small text-muted">Km/L</label><input type="number" name="consumo_kml[]" class="form-control form-control-sm recalc" step="0.1"></div>
            <div><label class="small text-muted">R$/L</label><input type="number" name="consumo_litro[]" class="form-control form-control-sm recalc" step="0.01"></div>
            <div>
                <label class="small text-muted">Km Total</label>
                <div class="input-group input-group-sm">
                    <input type="number" name="consumo_km_total[]" class="form-control recalc" value="0">
                    <button class="btn btn-outline-secondary btn-km-small" type="button" onclick="calcularDistancia(this)" title="Calcular Distância"><i class="bi bi-calculator"></i></button>
                    <button class="btn btn-outline-primary btn-km-small" type="button" onclick="verMapa(this)" title="Ver Rota no Mapa"><i class="bi bi-map"></i></button>
                </div>
            </div>
        </div>
        <div class="cost-total total-linha">0,00</div>
        <button type="button" class="btn btn-link text-danger remove-row"><i class="bi bi-trash"></i></button>
    `;

    const tLocacao = `
        <div class="cost-icon"><i class="bi bi-tools"></i></div>
        <div class="cost-details" style="grid-template-columns: 3fr 2.5fr 1fr 1.5fr 1fr;">
            <div><label class="small text-muted">Equipamento</label><select name="locacao_id[]" class="form-select form-select-sm"><option value="">Selecione</option>${opcoesLocacaoHtml}</select><input type="hidden" name="locacao_nome[]" class="item-nome-hidden"></div>
            <div class="div-marca"><label class="small text-muted">Marca</label><select name="locacao_id_marca[]" class="form-select form-select-sm" disabled><option value="">-</option></select></div>
            <div><label class="small text-muted">Qtd</label><input type="number" name="locacao_qtd[]" class="form-control form-control-sm recalc" value="1"></div>
            <div><label class="small text-muted">Mensal R$</label><input type="number" name="locacao_valor[]" class="form-control form-control-sm recalc autofill-valor" step="0.01"></div>
            <div><label class="small text-muted">Dias</label><input type="number" name="locacao_dias[]" class="form-control form-control-sm recalc" value="1"></div>
        </div>
        <div class="cost-total total-linha">0,00</div>
        <button type="button" class="btn btn-link text-danger remove-row"><i class="bi bi-trash"></i></button>
    `;

    const tAdmin = `
        <div class="cost-icon"><i class="bi bi-briefcase"></i></div>
        <div class="cost-details">
            <div><label class="small text-muted">Item</label><select name="admin_id[]" class="form-select form-select-sm"><option value="">Selecione</option>${opcoesAdminHtml}</select><input type="hidden" name="admin_nome[]" class="item-nome-hidden"></div>
            <div><label class="small text-muted">Qtd</label><input type="number" name="admin_qtd[]" class="form-control form-control-sm recalc" value="1"></div>
            <div><label class="small text-muted">Valor R$</label><input type="number" name="admin_valor[]" class="form-control form-control-sm recalc autofill-valor" step="0.01"></div>
            <div></div><div></div>
        </div>
        <div class="cost-total total-linha">0,00</div>
        <button type="button" class="btn btn-link text-danger remove-row"><i class="bi bi-trash"></i></button>
    `;

    document.getElementById('add-salario').onclick = () => addRow('list-salarios', tSalario);
    document.getElementById('add-estadia').onclick = () => addRow('list-estadia', tEstadia);
    document.getElementById('add-consumo').onclick = () => addRow('list-consumos', tConsumo);
    document.getElementById('add-locacao').onclick = () => addRow('list-locacao', tLocacao);
    document.getElementById('add-admin').onclick = () => addRow('list-admin', tAdmin);

    // Event Delegation com Debounce
    document.body.addEventListener('input', e => {
        if (e.target.classList.contains('recalc')) debouncedRecalc();
    });

    document.body.addEventListener('click', e => {
        if (e.target.closest('.remove-row')) {
            e.target.closest('.cost-item').remove();
            recalcularTudo(); // Remoção é imediata
        }
    });

    // Inputs globais de totais (Debounced)
    ['percentual_lucro', 'valor_desconto', 'mobilizacao_percentual', 'dias_campo', 'dias_escritorio'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', debouncedRecalc);
    });

    // Evento Customizado para Recálculo Externo (Otimizado)
    window.addEventListener('sgt:dadosCarregados', () => {
        console.log('Evento sgt:dadosCarregados recebido. Limpando cache e recalculando...');
        DOM_CACHE.clear(); // Limpa cache para garantir que novos elementos sejam pegos
        recalcularTudo();  // Recalcula tudo
    });

    // --- FINALIZAÇÃO ---
    // Recalcula ao carregar para garantir estados iniciais
    setTimeout(recalcularTudo, 500);

});