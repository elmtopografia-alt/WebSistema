// Nome do arquivo: calculos.js
// VERSÃO DE RESTAURAÇÃO TOTAL - GARANTIDA E ESTÁVEL

document.addEventListener('DOMContentLoaded', function() {
    // Função para formatar um número como moeda brasileira (R$)
    const formatarMoeda = (valor) => { 
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor || 0); 
    };
    
    // Função principal que é chamada sempre que um valor é alterado no formulário
    const recalcularTudo = () => {
        let totaisSecoes = { salarios: 0, estadia: 0, consumos: 0, locacao: 0, admin: 0 };
        
        // --- CÁLCULOS EXPLÍCITOS PARA CADA SEÇÃO ---

        totaisSecoes.salarios = calcularTotalSecao('tbody-salarios', (linha) => { 
            const inputs = linha.querySelectorAll('input[type=number]');
            if (inputs.length < 4) return 0;
            const quant = parseFloat(inputs[0].value) || 0;
            const salario = parseFloat(inputs[1].value) || 0;
            const encargos = parseFloat(inputs[2].value) || 0;
            const dias = parseFloat(inputs[3].value) || 0;
            return (quant * salario * (1 + (encargos / 100)) / 30) * dias; 
        });

        totaisSecoes.estadia = calcularTotalSecao('tbody-estadia', (linha) => { 
            const inputs = linha.querySelectorAll('input[type=number]');
            if (inputs.length < 3) return 0;
            const quant = parseFloat(inputs[0].value) || 0;
            const valor = parseFloat(inputs[1].value) || 0;
            const dias = parseFloat(inputs[2].value) || 0;
            return quant * valor * dias; 
        });

        totaisSecoes.consumos = calcularTotalSecao('tbody-consumos', (linha) => { 
            const inputs = linha.querySelectorAll('input[type=number]');
            if (inputs.length < 4) return 0;
            const quant = parseFloat(inputs[0].value) || 0;
            const consumo = parseFloat(inputs[1].value) || 0;
            const valorLitro = parseFloat(inputs[2].value) || 0;
            const kmTotal = parseFloat(inputs[3].value) || 0;
            if (consumo === 0) return 0; 
            return (kmTotal * valorLitro / consumo) * quant; 
        });
        
        totaisSecoes.locacao = calcularTotalSecao('tbody-locacao', (linha) => { 
            const inputs = linha.querySelectorAll('input[type=number]');
            if (inputs.length < 3) return 0;
            const quant = parseFloat(inputs[0].value) || 0;
            const valorMensal = parseFloat(inputs[1].value) || 0;
            const dias = parseFloat(inputs[2].value) || 0;
            return (quant * valorMensal / 30) * dias; 
        });

        totaisSecoes.admin = calcularTotalSecao('tbody-admin', (linha) => { 
            const inputs = linha.querySelectorAll('input[type=number]');
            if (inputs.length < 2) return 0;
            const quant = parseFloat(inputs[0].value) || 0;
            const valor = parseFloat(inputs[1].value) || 0;
            return quant * valor; 
        });
        
        // --- ATUALIZAÇÃO DA INTERFACE ---
        document.getElementById('total-secao-salarios').textContent = `Total: ${formatarMoeda(totaisSecoes.salarios)}`;
        document.getElementById('total-secao-estadia').textContent = `Total: ${formatarMoeda(totaisSecoes.estadia)}`;
        document.getElementById('total-secao-consumos').textContent = `Total: ${formatarMoeda(totaisSecoes.consumos)}`;
        document.getElementById('total-secao-locacao').textContent = `Total: ${formatarMoeda(totaisSecoes.locacao)}`;
        document.getElementById('total-secao-admin').textContent = `Total: ${formatarMoeda(totaisSecoes.admin)}`;
        
        let totalCustosGeral = Object.values(totaisSecoes).reduce((sum, current) => sum + current, 0);
        document.getElementById('total-custos-geral').textContent = formatarMoeda(totalCustosGeral);
        const percLucro = parseFloat(document.getElementById('percentual-lucro').value) / 100 || 0;
        const valorLucro = totalCustosGeral * percLucro;
        document.getElementById('valor-lucro').textContent = formatarMoeda(valorLucro);
        const subtotalComLucro = totalCustosGeral + valorLucro;
        document.getElementById('subtotal-com-lucro').textContent = formatarMoeda(subtotalComLucro);
        const valorDesconto = parseFloat(document.getElementById('valor-desconto').value) || 0;
        const valorFinalProposta = subtotalComLucro - valorDesconto;
        document.getElementById('valor-final-proposta').textContent = formatarMoeda(valorFinalProposta);
        const percMobilizacao = parseFloat(document.getElementById('mobilizacao_percentual').value) || 0;
        const valorMobilizacao = valorFinalProposta * (percMobilizacao / 100);
        document.getElementById('mobilizacao_valor').textContent = formatarMoeda(valorMobilizacao);
        const percRestante = 100 - percMobilizacao;
        const valorRestante = valorFinalProposta - valorMobilizacao;
        document.getElementById('restante_percentual').textContent = percRestante.toFixed(0);
        document.getElementById('restante_valor').textContent = formatarMoeda(valorRestante);

        setHiddenValue('hidden_total_custos_salarios', totaisSecoes.salarios);
        setHiddenValue('hidden_total_custos_estadia', totaisSecoes.estadia);
        setHiddenValue('hidden_total_custos_consumos', totaisSecoes.consumos);
        setHiddenValue('hidden_total_custos_locacao', totaisSecoes.locacao);
        setHiddenValue('hidden_total_custos_admin', totaisSecoes.admin);
        setHiddenValue('hidden_valor_lucro', valorLucro);
        setHiddenValue('hidden_subtotal_com_lucro', subtotalComLucro);
        setHiddenValue('hidden_valor_final_proposta', valorFinalProposta);
        setHiddenValue('hidden_mobilizacao_valor', valorMobilizacao);
        setHiddenValue('hidden_restante_percentual', percRestante);
        setHiddenValue('hidden_restante_valor', valorRestante);
    };

    const calcularTotalSecao = (tbodyId, formulaCalculo) => { let total = 0; document.querySelectorAll(`#${tbodyId} .linha-custo`).forEach(linha => { const totalLinha = formulaCalculo(linha); linha.querySelector('.total-linha').textContent = formatarMoeda(totalLinha); total += totalLinha; }); return total; };
    const setHiddenValue = (id, valor) => { const campo = document.getElementById(id); if (campo) { campo.value = (valor || 0).toFixed(2); } };
    const setupEventListeners = () => { document.body.addEventListener('input', function(e) { if (e.target.classList.contains('recalc-trigger')) { recalcularTudo(); } }); document.body.addEventListener('change', function(e) { if (e.target.classList.contains('recalc-trigger-change') || e.target.classList.contains('autofill-select')) { recalcularTudo(); } }); };
    
    const addLinhaDinamica = (config) => {
        const tbody = document.getElementById(config.tbodyId);
        const novaLinha = document.createElement('tr');
        novaLinha.className = 'linha-custo';
        novaLinha.innerHTML = config.template;
        tbody.appendChild(novaLinha);
    };

    const setupLinhaDinamica = (config) => { 
        document.getElementById(config.btnAddId).addEventListener('click', () => { addLinhaDinamica(config); }); 
        const tbody = document.getElementById(config.tbodyId);
        tbody.addEventListener('click', function(e) { if (e.target && e.target.classList.contains('btn-remover')) { e.target.closest('.linha-custo').remove(); recalcularTudo(); } }); 
        tbody.addEventListener('change', function(e) {
            const select = e.target.closest('.autofill-select');
            if (!select) return;
            const option = select.options[select.selectedIndex];
            const linha = select.closest('.linha-custo');
            const valor = option.getAttribute('data-valor');
            if (valor && linha.querySelector('.autofill-valor')) {
                linha.querySelector('.autofill-valor').value = valor;
            }
        });
    };

    const templateSalario = `<td><select class="form-select form-select-sm autofill-select" name="salarios[id_funcao][]"><option value="">Selecione...</option>${opcoesFuncaoHtml}</select></td><td><input type="number" class="form-control form-control-sm recalc-trigger" name="salarios[quantidade][]" value="1" step="1"></td><td><input type="number" class="form-control form-control-sm recalc-trigger autofill-valor" name="salarios[salario_base][]" value="0" step="0.01"></td><td><input type="number" class="form-control form-control-sm recalc-trigger" name="salarios[encargos][]" value="67" step="0.01"></td><td><input type="number" class="form-control form-control-sm recalc-trigger" name="salarios[dias][]" value="1" step="0.1"></td><td class="total-linha">R$ 0,00</td><td><button type="button" class="btn btn-danger btn-sm btn-remover">X</button></td>`;
    const templateEstadia = `<td><select class="form-select form-select-sm autofill-select" name="estadia[id_estadia][]"><option value="">Selecione...</option>${opcoesEstadiaHtml}</select></td><td><input type="number" class="form-control form-control-sm recalc-trigger" name="estadia[quantidade][]" value="1" step="1"></td><td><input type="number" class="form-control form-control-sm recalc-trigger autofill-valor" name="estadia[valor_unitario][]" value="0" step="0.01"></td><td><input type="number" class="form-control form-control-sm recalc-trigger" name="estadia[dias][]" value="1" step="1"></td><td class="total-linha">R$ 0,00</td><td><button type="button" class="btn btn-danger btn-sm btn-remover">X</button></td>`;
    const templateConsumo = `<td><select class="form-select form-select-sm autofill-select" name="consumos[id_consumo][]"><option value="">Selecione...</option>${opcoesConsumoHtml}</select></td><td><input type="number" class="form-control form-control-sm recalc-trigger" name="consumos[quantidade][]" value="1" step="1"></td><td><input type="number" class="form-control form-control-sm recalc-trigger" name="consumos[consumo_kml][]" value="10" step="0.01"></td><td><input type="number" class="form-control form-control-sm recalc-trigger" name="consumos[valor_litro][]" value="6.00" step="0.01"></td><td><input type="number" class="form-control form-control-sm recalc-trigger" name="consumos[km_total][]" value="0" step="1"></td><td class="total-linha">R$ 0,00</td><td><button type="button" class="btn btn-danger btn-sm btn-remover">X</button></td>`;
    const templateLocacao = `<td><select class="form-select form-select-sm autofill-select" name="locacao[id_locacao][]"><option value="">Selecione...</option>${opcoesLocacaoHtml}</select></td><td><input type="text" class="form-control form-control-sm" name="locacao[modelo][]"></td><td><input type="number" class="form-control form-control-sm recalc-trigger" name="locacao[quantidade][]" value="1" step="1"></td><td><input type="number" class="form-control form-control-sm recalc-trigger autofill-valor" name="locacao[valor_mensal][]" value="0" step="0.01"></td><td><input type="number" class="form-control form-control-sm recalc-trigger" name="locacao[dias][]" value="1" step="1"></td><td class="total-linha">R$ 0,00</td><td><button type="button" class="btn btn-danger btn-sm btn-remover">X</button></td>`;
    const templateAdmin = `<td><select class="form-select form-select-sm autofill-select" name="admin[id_custo_admin][]"><option value="">Selecione...</option>${opcoesAdminHtml}</select></td><td><input type="number" class="form-control form-control-sm recalc-trigger" name="admin[quantidade][]" value="1" step="1"></td><td><input type="number" class="form-control form-control-sm recalc-trigger autofill-valor" name="admin[valor][]" value="0" step="0.01"></td><td class="total-linha">R$ 0,00</td><td><button type="button" class="btn btn-danger btn-sm btn-remover">X</button></td>`;

    const configs = {
        salarios: { tbodyId: 'tbody-salarios', btnAddId: 'add-salario', template: templateSalario },
        estadia: { tbodyId: 'tbody-estadia', btnAddId: 'add-estadia', template: templateEstadia },
        consumos: { tbodyId: 'tbody-consumos', btnAddId: 'add-consumo', template: templateConsumo },
        locacao: { tbodyId: 'tbody-locacao', btnAddId: 'add-locacao', template: templateLocacao },
        admin: { tbodyId: 'tbody-admin', btnAddId: 'add-admin', template: templateAdmin }
    };

    Object.values(configs).forEach(config => {
        setupLinhaDinamica(config);
    });
    
    setupEventListeners();
    recalcularTudo();
});
*Fim arquivo calculos.js*