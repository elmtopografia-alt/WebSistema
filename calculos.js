// calculos.js - Versão Premium Wizard
document.addEventListener('DOMContentLoaded', function () {
    const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v || 0);
    const parseMoney = (v) => {
        if (!v) return 0;
        if (typeof v === 'number') return v;
        // Se for string vinda de input number (ex: "1234.56") ou float padrão
        if (/^-?\d+(\.\d+)?$/.test(v)) return parseFloat(v);
        // Se for formato BR (ex: "1.234,56")
        return parseFloat(v.replace(/\./g, '').replace(',', '.')) || 0;
    };

    const getVal = (input) => {
        return input.valueAsNumber || parseMoney(input.value);
    };

    const updateNameInput = (select) => {
        const item = select.closest('.cost-item');
        const nameInput = item.querySelector('.item-nome-hidden');
        if (nameInput && select.options[select.selectedIndex]) {
            nameInput.value = select.options[select.selectedIndex].text;
        }
    };

    const recalcularTudo = () => {
        let totais = { salarios: 0, estadia: 0, consumos: 0, locacao: 0, admin: 0 };
        let totalDiasEquipe = 0; // Acumulador de dias da equipe

        // Helper para somar custos de uma lista
        const somarLista = (listId, tipo) => {
            let subtotal = 0;
            const container = document.getElementById(listId);
            if (!container) return 0;

            container.querySelectorAll('.cost-item').forEach(item => {
                const inputs = item.querySelectorAll('input');
                // A ordem dos inputs depende do template. Vamos buscar por classe ou ordem.
                // Mas como os templates variam, vamos manter a lógica por tipo.

                let valLinha = 0;

                if (tipo === 'salarios') {
                    // [0]hidden_nome, [1]qtd, [2]valor, [3]encargos, [4]dias
                    const qtd = getVal(inputs[1]);
                    const base = getVal(inputs[2]);
                    const enc = getVal(inputs[3]);
                    const dias = getVal(inputs[4]);
                    valLinha = (qtd * base * (1 + enc / 100) / 30) * dias;

                    // Acumula dias x quantidade para refeição (Total de "Diárias de Trabalho")
                    totalDiasEquipe += (qtd * dias);
                }
                else if (tipo === 'estadia') {
                    // [0]hidden_nome, [1]qtd, [2]valor, [3]dias

                    // Verifica se é Refeição/Alimentação e atualiza Qtd
                    const select = item.querySelector('select');
                    if (select && select.options[select.selectedIndex]) {
                        const text = select.options[select.selectedIndex].text.toLowerCase();
                        // Palavras-chave para identificar item de refeição
                        if (text.includes('refeição') || text.includes('refeicao') || text.includes('alimentação') || text.includes('alimentacao')) {
                            // Atualiza apenas se houver dias calculados na equipe
                            if (totalDiasEquipe > 0) {
                                inputs[1].value = totalDiasEquipe;
                                // Se o campo Dias estiver vazio ou 0, define como 1 para o cálculo funcionar (Qtd x Valor x 1)
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
                    // [0]hidden_nome, [1]qtd, [2]kml, [3]litro, [4]km_total
                    const qtd = getVal(inputs[1]);
                    const kml = getVal(inputs[2]) || 1;
                    const lit = getVal(inputs[3]);
                    const kmt = getVal(inputs[4]);
                    valLinha = (kml > 0) ? (kmt * lit / kml) * qtd : 0;
                }
                else if (tipo === 'locacao') {
                    // [0]hidden_nome, [1]qtd, [2]valor, [3]dias
                    const qtd = getVal(inputs[1]);
                    const val = getVal(inputs[2]);
                    const dias = getVal(inputs[3]);
                    valLinha = (qtd * val / 30) * dias;
                }
                else if (tipo === 'admin') {
                    // [0]hidden_nome, [1]qtd, [2]valor
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

        // Atualiza Resumos no Step 4
        const updateSummary = (id, value) => {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = formatarMoeda(value);
                // Oculta a linha se o valor for zero
                if (value > 0.01) {
                    el.parentElement.style.display = 'flex';
                    el.parentElement.classList.remove('d-none');
                } else {
                    el.parentElement.style.display = 'none';
                    el.parentElement.classList.add('d-none');
                }
            }
        };

        updateSummary('resumo-salarios', totais.salarios);
        updateSummary('resumo-estadia', totais.estadia);
        updateSummary('resumo-consumos', totais.consumos);
        updateSummary('resumo-locacao', totais.locacao);
        updateSummary('resumo-admin', totais.admin);

        // Totais Gerais
        const geral = Object.values(totais).reduce((a, b) => a + b, 0);
        document.getElementById('total-custos-geral').textContent = formatarMoeda(geral);

        // Live Preview Widget
        const liveTotal = document.getElementById('live-total');
        if (liveTotal) liveTotal.textContent = formatarMoeda(geral);

        // Cálculos Finais (Lucro, Desconto)
        const lucroPerc = parseFloat(document.getElementById('percentual_lucro').value) || 0;
        const lucroValor = geral * (lucroPerc / 100);
        document.getElementById('valor-lucro').textContent = '+ ' + formatarMoeda(lucroValor);

        const subtotal = geral + lucroValor;
        const desconto = parseFloat(document.getElementById('valor_desconto').value) || 0;
        const final = subtotal - desconto;

        document.getElementById('valor-final-proposta').textContent = formatarMoeda(final);

        // Condições
        const mobPerc = parseFloat(document.getElementById('mobilizacao_percentual').value) || 0;
        const mobVal = final * (mobPerc / 100);
        const restPerc = 100 - mobPerc;
        const restVal = final - mobVal;

        document.getElementById('mobilizacao_valor_display').value = formatarMoeda(mobVal);
        document.getElementById('restante_percentual_display').textContent = restPerc.toFixed(0);
        document.getElementById('restante_valor_display').value = formatarMoeda(restVal);

        // Hidden inputs para PHP
        document.getElementById('hidden_total_custos_salarios').value = totais.salarios;
        document.getElementById('hidden_total_custos_estadia').value = totais.estadia;
        document.getElementById('hidden_total_custos_consumos').value = totais.consumos;
        document.getElementById('hidden_total_custos_locacao').value = totais.locacao;
        document.getElementById('hidden_total_custos_admin').value = totais.admin;
        document.getElementById('hidden_valor_lucro').value = lucroValor;
        document.getElementById('hidden_subtotal_com_lucro').value = subtotal;
        document.getElementById('hidden_valor_final_proposta').value = final;
        document.getElementById('hidden_mobilizacao_valor').value = mobVal;
        document.getElementById('hidden_restante_percentual').value = restPerc;
        document.getElementById('hidden_restante_valor').value = restVal;
    };

    const addRow = (listId, template) => {
        const list = document.getElementById(listId);
        const div = document.createElement('div');
        div.className = 'cost-item';
        div.innerHTML = template;
        list.appendChild(div);

        // Trigger select change to set initial hidden name
        const sel = div.querySelector('select');
        if (sel) {
            updateNameInput(sel);
            // Auto-fill values
            sel.addEventListener('change', function () {
                updateNameInput(this);
                const opt = this.options[this.selectedIndex];
                const val = opt.getAttribute('data-valor');
                if (val) {
                    const valInput = div.querySelector('.autofill-valor');
                    if (valInput) valInput.value = val;
                }
                // Specific for Consumo
                const lit = opt.getAttribute('data-valor-litro');
                const kml = opt.getAttribute('data-consumo-kml');
                if (lit) div.querySelectorAll('input')[3].value = lit;
                if (kml) div.querySelectorAll('input')[2].value = kml;

                recalcularTudo();
            });
            // Auto-populate marcas for locacao
            if (listId === 'list-locacao') {
                sel.addEventListener('change', function () {
                    const idLoc = this.value;
                    const divMarca = div.querySelector('.div-marca');
                    let htmlMarca = '<label class="small text-muted">Marca</label><select name="locacao_id_marca[]" class="form-select form-select-sm"><option value="">-</option>';
                    if (marcasPorTipo[idLoc]) {
                        marcasPorTipo[idLoc].forEach(m => {
                            htmlMarca += `<option value="${m.id_marca}">${m.nome_marca}</option>`;
                        });
                    }
                    htmlMarca += '</select>';
                    divMarca.innerHTML = htmlMarca;
                });
            }
        }
        recalcularTudo();
    };

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
        <div class="cost-details">
            <div><label class="small text-muted">Combustível</label><select name="consumo_id[]" class="form-select form-select-sm"><option value="">Selecione</option>${opcoesConsumoHtml}</select><input type="hidden" name="consumo_nome[]" class="item-nome-hidden"></div>
            <div><label class="small text-muted">Qtd Veic</label><input type="number" name="consumo_qtd[]" class="form-control form-control-sm recalc" value="1"></div>
            <div><label class="small text-muted">Km/L</label><input type="number" name="consumo_kml[]" class="form-control form-control-sm recalc" step="0.1"></div>
            <div><label class="small text-muted">R$/L</label><input type="number" name="consumo_litro[]" class="form-control form-control-sm recalc" step="0.01"></div>
            <div>
                <label class="small text-muted">Km Total</label>
                <div class="input-group input-group-sm">
                    <input type="number" name="consumo_km_total[]" class="form-control recalc" value="0">
                    <button class="btn btn-outline-secondary" type="button" onclick="calcularDistancia(this)" title="Calcular Distância"><i class="bi bi-calculator"></i></button>
                    <button class="btn btn-outline-primary" type="button" onclick="verMapa(this)" title="Ver Rota no Mapa"><i class="bi bi-map"></i></button>
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
            <div class="div-marca"><label class="small text-muted">Marca</label><span class="d-block text-muted small">-</span></div>
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

    document.body.addEventListener('input', e => { if (e.target.classList.contains('recalc')) recalcularTudo(); });
    document.body.addEventListener('click', e => {
        if (e.target.closest('.remove-row')) {
            e.target.closest('.cost-item').remove();
            recalcularTudo();
        }
    });

    // Inputs globais de totais
    ['percentual_lucro', 'valor_desconto', 'mobilizacao_percentual'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', recalcularTudo);
    });

    // --- CÁLCULO DE PRAZO DE ENTREGA ---
    const calcPrazo = () => {
        const campo = parseInt(document.getElementById('dias_campo')?.value) || 0;
        const escritorio = parseInt(document.getElementById('dias_escritorio')?.value) || 0;
        const total = campo + escritorio;
        const inputPrazo = document.getElementById('prazo_execucao');

        if (inputPrazo) {
            // Mantém o texto "dias úteis após campo" se já estiver lá, ou adiciona
            // Mas o usuário pediu "1+4=5", então vamos colocar o número e o texto padrão
            inputPrazo.value = `${total} dias úteis após campo`;
        }
    };

    const elCampo = document.getElementById('dias_campo');
    const elEscritorio = document.getElementById('dias_escritorio');

    if (elCampo) elCampo.addEventListener('input', calcPrazo);
    if (elEscritorio) elEscritorio.addEventListener('input', calcPrazo);

    // Executa uma vez ao carregar se for criar_proposta (onde os valores já vêm preenchidos)
    // Mas cuidado para não sobrescrever em edição se o usuário já mudou o texto manualmente
    // Vamos rodar apenas se o input de prazo estiver vazio ou contiver o texto padrão antigo
    // Ou melhor, vamos rodar sempre que mudar os dias. No load, deixamos como está (PHP define).
});