// calculos.js
document.addEventListener('DOMContentLoaded', function() {
    const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v || 0);

    const updateNameInput = (select) => {
        const row = select.closest('tr');
        const nameInput = row.querySelector('.item-nome-hidden');
        if (nameInput && select.options[select.selectedIndex]) {
            nameInput.value = select.options[select.selectedIndex].text;
        }
    };

    const recalcularTudo = () => {
        let totais = { salarios: 0, estadia: 0, consumos: 0, locacao: 0, admin: 0 };

        // Salários
        document.querySelectorAll('#tbody-salarios tr').forEach(row => {
            const inputs = row.querySelectorAll('input');
            const qtd = parseFloat(inputs[1].value) || 0;
            const base = parseFloat(inputs[2].value) || 0;
            const enc = parseFloat(inputs[3].value) || 0;
            const dias = parseFloat(inputs[4].value) || 0;
            const sub = (qtd * base * (1 + enc/100) / 30) * dias;
            row.querySelector('.total-linha').textContent = formatarMoeda(sub);
            totais.salarios += sub;
        });

        // Estadia
        document.querySelectorAll('#tbody-estadia tr').forEach(row => {
            const inputs = row.querySelectorAll('input');
            const qtd = parseFloat(inputs[1].value) || 0;
            const val = parseFloat(inputs[2].value) || 0;
            const dias = parseFloat(inputs[3].value) || 0;
            const sub = qtd * val * dias;
            row.querySelector('.total-linha').textContent = formatarMoeda(sub);
            totais.estadia += sub;
        });

        // Consumos
        document.querySelectorAll('#tbody-consumos tr').forEach(row => {
            const inputs = row.querySelectorAll('input');
            const qtd = parseFloat(inputs[1].value) || 0;
            const kml = parseFloat(inputs[2].value) || 1;
            const lit = parseFloat(inputs[3].value) || 0;
            const kmt = parseFloat(inputs[4].value) || 0;
            const sub = (kml > 0) ? (kmt * lit / kml) * qtd : 0;
            row.querySelector('.total-linha').textContent = formatarMoeda(sub);
            totais.consumos += sub;
        });

        // Locacao
        document.querySelectorAll('#tbody-locacao tr').forEach(row => {
            const inputs = row.querySelectorAll('input');
            // inputs[0] é hidden nome, inputs[1] qtd, inputs[2] valor, inputs[3] dias
            const qtd = parseFloat(inputs[1].value) || 0;
            const val = parseFloat(inputs[2].value) || 0;
            const dias = parseFloat(inputs[3].value) || 0;
            const sub = (qtd * val / 30) * dias;
            row.querySelector('.total-linha').textContent = formatarMoeda(sub);
            totais.locacao += sub;
        });

        // Admin
        document.querySelectorAll('#tbody-admin tr').forEach(row => {
            const inputs = row.querySelectorAll('input');
            const qtd = parseFloat(inputs[1].value) || 0;
            const val = parseFloat(inputs[2].value) || 0;
            const sub = qtd * val;
            row.querySelector('.total-linha').textContent = formatarMoeda(sub);
            totais.admin += sub;
        });

        // Atualiza Totais Visuais
        document.getElementById('total-secao-salarios').textContent = formatarMoeda(totais.salarios);
        document.getElementById('total-secao-estadia').textContent = formatarMoeda(totais.estadia);
        document.getElementById('total-secao-consumos').textContent = formatarMoeda(totais.consumos);
        document.getElementById('total-secao-locacao').textContent = formatarMoeda(totais.locacao);
        document.getElementById('total-secao-admin').textContent = formatarMoeda(totais.admin);

        // Totais Gerais
        const geral = Object.values(totais).reduce((a,b)=>a+b,0);
        document.getElementById('total-custos-geral').textContent = formatarMoeda(geral);

        const lucroPerc = parseFloat(document.getElementById('percentual_lucro').value) || 0;
        const lucroValor = geral * (lucroPerc/100);
        document.getElementById('valor-lucro').textContent = '+ ' + formatarMoeda(lucroValor);

        const subtotal = geral + lucroValor;
        const desconto = parseFloat(document.getElementById('valor_desconto').value) || 0;
        const final = subtotal - desconto;
        
        document.getElementById('valor-final-proposta').textContent = formatarMoeda(final);

        // Condições
        const mobPerc = parseFloat(document.getElementById('mobilizacao_percentual').value) || 0;
        const mobVal = final * (mobPerc/100);
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

    const addRow = (tbodyId, template) => {
        const tbody = document.getElementById(tbodyId);
        const tr = document.createElement('tr');
        tr.innerHTML = template;
        tbody.appendChild(tr);
        // Trigger select change to set initial hidden name
        const sel = tr.querySelector('select');
        if(sel) { 
            updateNameInput(sel);
            // Auto-fill values
            sel.addEventListener('change', function() {
                updateNameInput(this);
                const opt = this.options[this.selectedIndex];
                const val = opt.getAttribute('data-valor');
                if(val) {
                   const valInput = tr.querySelector('.autofill-valor');
                   if(valInput) valInput.value = val;
                }
                // Specific for Consumo
                const lit = opt.getAttribute('data-valor-litro');
                const kml = opt.getAttribute('data-consumo-kml');
                if(lit) tr.querySelectorAll('input')[3].value = lit;
                if(kml) tr.querySelectorAll('input')[2].value = kml;
                
                recalcularTudo();
            });
            // Auto-populate marcas for locacao
            if(tbodyId === 'tbody-locacao') {
                sel.addEventListener('change', function(){
                    const idLoc = this.value;
                    const tdMarca = tr.querySelector('.td-marca');
                    let htmlMarca = '<select name="locacao_id_marca[]" class="form-select form-select-sm"><option value="">-</option>';
                    if(marcasPorTipo[idLoc]) {
                        marcasPorTipo[idLoc].forEach(m => {
                            htmlMarca += `<option value="${m.id_marca}">${m.nome_marca}</option>`;
                        });
                    }
                    htmlMarca += '</select>';
                    tdMarca.innerHTML = htmlMarca;
                });
            }
        }
        recalcularTudo();
    };

    // Templates ajustados para name="campo[]"
    const tSalario = `<td><select name="salario_id_funcao[]" class="form-select form-select-sm"><option value="">Selecione</option>${opcoesFuncaoHtml}</select><input type="hidden" name="salario_nome[]" class="item-nome-hidden"></td><td><input type="number" name="salario_qtd[]" class="form-control form-control-sm recalc" value="1"></td><td><input type="number" name="salario_valor[]" class="form-control form-control-sm recalc autofill-valor" step="0.01"></td><td><input type="number" name="encargos[]" class="form-control form-control-sm recalc" value="67"></td><td><input type="number" name="salario_dias[]" class="form-control form-control-sm recalc" value="1"></td><td class="total-linha">0,00</td><td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>`;
    
    const tEstadia = `<td><select name="estadia_id[]" class="form-select form-select-sm"><option value="">Selecione</option>${opcoesEstadiaHtml}</select><input type="hidden" name="estadia_nome[]" class="item-nome-hidden"></td><td><input type="number" name="estadia_qtd[]" class="form-control form-control-sm recalc" value="1"></td><td><input type="number" name="estadia_valor[]" class="form-control form-control-sm recalc autofill-valor" step="0.01"></td><td><input type="number" name="estadia_dias[]" class="form-control form-control-sm recalc" value="1"></td><td class="total-linha">0,00</td><td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>`;
    
    const tConsumo = `<td><select name="consumo_id[]" class="form-select form-select-sm"><option value="">Selecione</option>${opcoesConsumoHtml}</select><input type="hidden" name="consumo_nome[]" class="item-nome-hidden"></td><td><input type="number" name="consumo_qtd[]" class="form-control form-control-sm recalc" value="1"></td><td><input type="number" name="consumo_kml[]" class="form-control form-control-sm recalc" step="0.1"></td><td><input type="number" name="consumo_litro[]" class="form-control form-control-sm recalc" step="0.01"></td><td><input type="number" name="consumo_km_total[]" class="form-control form-control-sm recalc" value="0"></td><td class="total-linha">0,00</td><td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>`;
    
    const tLocacao = `<td><select name="locacao_id[]" class="form-select form-select-sm"><option value="">Selecione</option>${opcoesLocacaoHtml}</select><input type="hidden" name="locacao_nome[]" class="item-nome-hidden"></td><td class="td-marca">-</td><td><input type="number" name="locacao_qtd[]" class="form-control form-control-sm recalc" value="1"></td><td><input type="number" name="locacao_valor[]" class="form-control form-control-sm recalc autofill-valor" step="0.01"></td><td><input type="number" name="locacao_dias[]" class="form-control form-control-sm recalc" value="1"></td><td class="total-linha">0,00</td><td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>`;
    
    const tAdmin = `<td><select name="admin_id[]" class="form-select form-select-sm"><option value="">Selecione</option>${opcoesAdminHtml}</select><input type="hidden" name="admin_nome[]" class="item-nome-hidden"></td><td><input type="number" name="admin_qtd[]" class="form-control form-control-sm recalc" value="1"></td><td><input type="number" name="admin_valor[]" class="form-control form-control-sm recalc autofill-valor" step="0.01"></td><td class="total-linha">0,00</td><td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>`;

    document.getElementById('add-salario').onclick = () => addRow('tbody-salarios', tSalario);
    document.getElementById('add-estadia').onclick = () => addRow('tbody-estadia', tEstadia);
    document.getElementById('add-consumo').onclick = () => addRow('tbody-consumos', tConsumo);
    document.getElementById('add-locacao').onclick = () => addRow('tbody-locacao', tLocacao);
    document.getElementById('add-admin').onclick = () => addRow('tbody-admin', tAdmin);

    document.body.addEventListener('input', e => { if(e.target.classList.contains('recalc')) recalcularTudo(); });
    document.body.addEventListener('click', e => { 
        if(e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
            recalcularTudo();
        }
    });

    // Inputs globais de totais
    ['percentual_lucro', 'valor_desconto', 'mobilizacao_percentual'].forEach(id => {
        document.getElementById(id).addEventListener('input', recalcularTudo);
    });
});