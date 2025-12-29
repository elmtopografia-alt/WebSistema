// Nome do Arquivo: recalculo.js
// Função: Motor exclusivo para EDIÇÃO de propostas.
// Diferencial: Recebe dados do banco, popula os inputs e calcula imediatamente.

// 1. Formatação
const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v || 0);

// 2. Atualiza nome do select hidden
function updateNameInput(select) {
    const row = select.closest('tr');
    const hidden = row.querySelector('.item-nome-hidden');
    if (hidden && select.selectedIndex >= 0) hidden.value = select.options[select.selectedIndex].text;
}

// 3. O Cérebro Matemático (Igual ao original, para manter consistência)
function recalcularTudo() {
    let tSal=0, tEst=0, tCon=0, tLoc=0, tAdm=0;

    // Função auxiliar de leitura segura
    const ler = (row, selector) => {
        let val = row.querySelector(selector).value;
        return parseFloat(val) || 0;
    };

    // Salários
    document.querySelectorAll('#tbody-salarios tr').forEach(r => {
        let qtd = ler(r, '.qtd');
        let val = ler(r, '.val');
        let enc = ler(r, '.enc');
        let dias = ler(r, '.dias');
        let tot = (qtd * val * (1 + enc/100) / 30) * dias;
        r.querySelector('.total-linha').innerText = formatarMoeda(tot);
        tSal += tot;
    });

    // Estadia
    document.querySelectorAll('#tbody-estadia tr').forEach(r => {
        let qtd = ler(r, '.qtd');
        let val = ler(r, '.val');
        let dias = ler(r, '.dias');
        let tot = qtd * val * dias;
        r.querySelector('.total-linha').innerText = formatarMoeda(tot);
        tEst += tot;
    });

    // Consumo
    document.querySelectorAll('#tbody-consumos tr').forEach(r => {
        let qtd = ler(r, '.qtd');
        let kml = ler(r, '.kml') || 1;
        let lit = ler(r, '.litro');
        let km = ler(r, '.kmtotal');
        let tot = (kml > 0) ? (km * lit / kml) * qtd : 0;
        r.querySelector('.total-linha').innerText = formatarMoeda(tot);
        tCon += tot;
    });

    // Locação
    document.querySelectorAll('#tbody-locacao tr').forEach(r => {
        let qtd = ler(r, '.qtd');
        let val = ler(r, '.val');
        let dias = ler(r, '.dias');
        let tot = (qtd * val / 30) * dias;
        r.querySelector('.total-linha').innerText = formatarMoeda(tot);
        tLoc += tot;
    });

    // Admin
    document.querySelectorAll('#tbody-admin tr').forEach(r => {
        let qtd = ler(r, '.qtd');
        let val = ler(r, '.val');
        let tot = qtd * val;
        r.querySelector('.total-linha').innerText = formatarMoeda(tot);
        tAdm += tot;
    });

    // Atualiza Totais Visuais e Hidden
    document.getElementById('total-secao-salarios').innerText = formatarMoeda(tSal);
    document.getElementById('total-secao-estadia').innerText = formatarMoeda(tEst);
    document.getElementById('total-secao-consumos').innerText = formatarMoeda(tCon);
    document.getElementById('total-secao-locacao').innerText = formatarMoeda(tLoc);
    document.getElementById('total-secao-admin').innerText = formatarMoeda(tAdm);

    document.getElementById('hidden_total_custos_salarios').value = tSal;
    document.getElementById('hidden_total_custos_estadia').value = tEst;
    document.getElementById('hidden_total_custos_consumos').value = tCon;
    document.getElementById('hidden_total_custos_locacao').value = tLoc;
    document.getElementById('hidden_total_custos_admin').value = tAdm;

    // Totais Finais
    let geral = tSal + tEst + tCon + tLoc + tAdm;
    document.getElementById('total-custos-geral').innerText = formatarMoeda(geral);

    let lucroPerc = parseFloat(document.getElementById('percentual_lucro').value)||0;
    let lucroVal = geral * (lucroPerc/100);
    document.getElementById('valor-lucro').innerText = '+ ' + formatarMoeda(lucroVal);
    document.getElementById('hidden_valor_lucro').value = lucroVal;

    let sub = geral + lucroVal;
    document.getElementById('hidden_subtotal_com_lucro').value = sub;

    let desc = parseFloat(document.getElementById('valor_desconto').value)||0;
    let final = sub - desc;
    document.getElementById('valor-final-proposta').innerText = formatarMoeda(final);
    document.getElementById('hidden_valor_final_proposta').value = final;

    let mobP = parseFloat(document.getElementById('mobilizacao_percentual').value)||0;
    let mobV = final * (mobP/100);
    document.getElementById('mobilizacao_valor_display').value = formatarMoeda(mobV);
    document.getElementById('hidden_mobilizacao_valor').value = mobV;

    let restP = 100 - mobP;
    document.getElementById('restante_percentual_display').innerText = restP;
    document.getElementById('restante_valor_display').value = formatarMoeda(final - mobV);
    document.getElementById('hidden_restante_percentual').value = restP;
    document.getElementById('hidden_restante_valor').value = final - mobV;
}

// 4. Função para Adicionar Linha VAZIA (Click do Botão)
function addRow(tbodyId, template) {
    const tbody = document.getElementById(tbodyId);
    const tr = document.createElement('tr');
    tr.classList.add('item-row');
    tr.innerHTML = template;
    tbody.appendChild(tr);
    configurarEventos(tr);
    recalcularTudo();
}

// 5. Configura Listeners na Linha
function configurarEventos(tr) {
    const inputs = tr.querySelectorAll('input');
    inputs.forEach(i => i.addEventListener('input', recalcularTudo));
    
    const sel = tr.querySelector('select');
    if(sel) {
        updateNameInput(sel);
        sel.addEventListener('change', function() {
            updateNameInput(this);
            // Autofill se não for preenchimento automático inicial
            if(!tr.classList.contains('loading')) {
                const opt = this.options[this.selectedIndex];
                const val = opt.getAttribute('data-valor');
                if(val) tr.querySelector('.autofill-valor').value = val;
                
                const lit = opt.getAttribute('data-valor-litro');
                const kml = opt.getAttribute('data-consumo-kml');
                if(lit) tr.querySelector('.litro').value = lit;
                if(kml) tr.querySelector('.kml').value = kml;
            }
            recalcularTudo();
        });
    }
}

// 6. O GRANDE DIFERENCIAL: Carrega Dados do Banco
function carregarDadosIniciais(dados, tipo, tbodyId, template) {
    const tbody = document.getElementById(tbodyId);
    
    dados.forEach(d => {
        const tr = document.createElement('tr');
        tr.classList.add('item-row');
        tr.classList.add('loading'); // Marca para não disparar autofill do select
        tr.innerHTML = template;
        tbody.appendChild(tr);

        // Preenche os campos baseado no tipo
        if(tipo === 'salario') {
            tr.querySelector('select').value = d.id_funcao;
            tr.querySelector('.qtd').value = d.quantidade;
            tr.querySelector('.val').value = d.salario_base; // Já vem do PHP com ponto
            tr.querySelector('.enc').value = Math.round((d.fator_encargos - 1) * 100);
            tr.querySelector('.dias').value = d.dias;
        } 
        else if(tipo === 'estadia') {
            tr.querySelector('select').value = d.id_estadia;
            tr.querySelector('.qtd').value = d.quantidade;
            tr.querySelector('.val').value = d.valor_unitario;
            tr.querySelector('.dias').value = d.dias;
        }
        else if(tipo === 'consumo') {
            tr.querySelector('select').value = d.id_consumo;
            tr.querySelector('.qtd').value = d.quantidade;
            tr.querySelector('.kml').value = d.consumo_kml;
            tr.querySelector('.litro').value = d.valor_litro;
            tr.querySelector('.kmtotal').value = d.km_total;
        }
        else if(tipo === 'locacao') {
            tr.querySelector('select').value = d.id_locacao;
            tr.querySelector('.qtd').value = d.quantidade;
            tr.querySelector('.val').value = d.valor_mensal;
            tr.querySelector('.dias').value = d.dias;
            
            // Popula marca
            let idLoc = d.id_locacao;
            let tdMarca = tr.querySelector('.td-marca');
            if(window.marcasPorTipo && window.marcasPorTipo[idLoc]) {
                let htmlMarca = '<select name="locacao_id_marca[]" class="form-select form-select-sm"><option value="">-</option>';
                window.marcasPorTipo[idLoc].forEach(m => {
                    let sel = (m.id_marca == d.id_marca) ? 'selected' : '';
                    htmlMarca += `<option value="${m.id_marca}" ${sel}>${m.nome_marca}</option>`;
                });
                htmlMarca += '</select>';
                tdMarca.innerHTML = htmlMarca;
            }
        }
        else if(tipo === 'admin') {
            tr.querySelector('select').value = d.id_custo_admin;
            tr.querySelector('.qtd').value = d.quantidade;
            tr.querySelector('.val').value = d.valor;
        }

        // Atualiza nome hidden
        updateNameInput(tr.querySelector('select'));
        
        // Ativa eventos
        configurarEventos(tr);
        tr.classList.remove('loading');
    });
}

// Global Listener para Inputs de Totais
document.addEventListener('input', function(e) {
    if(e.target.id == 'percentual_lucro' || e.target.id == 'valor_desconto' || e.target.id == 'mobilizacao_percentual') {
        recalcularTudo();
    }
});

// Remove Row Global
window.removeRow = function(btn) {
    btn.closest('tr').remove();
    recalcularTudo();
}