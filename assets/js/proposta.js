/**
 * SGT Proposta - Inicialização principal
 * Orquestra todos os módulos
 */

document.addEventListener('DOMContentLoaded', () => {

    // 1. Inicializa utilitários primeiro
    console.log('🚀 SGT Propostas - Inicializando...');

    // 2. Inicializa módulos na ordem correta
    try {
        // Utilitários já disponíveis via global SGTUtils

        // Calculadora
        if (typeof Calculator !== 'undefined') {
            Calculator.init();
            console.log('✅ Calculadora inicializada');
        }

        // Wizard
        if (typeof Wizard !== 'undefined') {
            Wizard.init();
            console.log('✅ Wizard inicializado');
        }

        // Modal de Cliente
        if (typeof ClienteModal !== 'undefined') {
            ClienteModal.init();
            console.log('✅ Modal de Cliente inicializado');
        }

        // Gestão de Custos
        if (typeof CostsManager !== 'undefined') {
            CostsManager.init();
            console.log('✅ CostsManager inicializado');
        }

        // AutoSave (inicializa por último)
        if (typeof AutoSave !== 'undefined') {
            // Delay para não interferir na carga inicial
            setTimeout(() => {
                AutoSave.init();
                console.log('✅ AutoSave inicializado');
            }, 500);
        }

        // Monitoramento de conexão
        // Monitoramento de conexão
        initConnectionMonitor();
        initScheduleCalculation(); // Inicia cálculo de prazos


        console.log('✨ Sistema pronto!');

    } catch (error) {
        console.error('❌ Erro na inicialização:', error);
        SGTUtils.showToast('Erro ao inicializar sistema', 'error');
    }
});



/**
 * Lógica de cálculo do Cronograma (Passo 2)
 */
function initScheduleCalculation() {
    const inputs = document.querySelectorAll('.recalc');
    const output = document.getElementById('prazo_execucao');

    if (!inputs.length || !output) return;

    const calc = () => {
        let total = 0;
        inputs.forEach(input => {
            total += parseInt(input.value) || 0;
        });
        output.value = total + ' dias úteis';
    };

    inputs.forEach(input => {
        input.addEventListener('input', calc);
    });

    // Calc inicial
    calc();
    console.log('✅ Schedule Calculation inicializado');
}

/**
 * Monitoramento de conexão
 */
function initConnectionMonitor() {
    window.addEventListener('online', () => {
        SGTUtils.showToast('Conexão restabelecida! 🟢', 'success');
    });

    window.addEventListener('offline', () => {
        SGTUtils.showToast('Você está offline. 🔴', 'warning');
    });
}

/**
 * Função global para ir para o editor (chamada do HTML)
 */
window.irParaEditor = function () {
    const form = document.getElementById('form-proposta');
    if (!form) return;

    // Validação final antes de enviar
    if (!window.Wizard?.validate()) return;

    // --- MAPPING FIX: Converter inputs aninhados (CostsManager) para Flat Arrays (salvar_proposta) ---
    // Remove inputs ocultos antigos se houver (para evitar duplicação em múltiplos cliques)
    document.querySelectorAll('.legacy-hidden-mapper').forEach(el => el.remove());

    const createHidden = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name + (name.includes('formato_saida') ? '' : '[]'); // Array notation except for special flags
        input.value = value;
        input.className = 'legacy-hidden-mapper';
        form.appendChild(input);
    };

    // 0. Validação de custo (Evitar proposta vazia)
    const temSalario = document.querySelectorAll('#list-salarios .cost-item').length > 0;
    const temEstadia = document.querySelectorAll('#list-estadia .cost-item').length > 0;
    const temConsumo = document.querySelectorAll('#list-consumos .cost-item').length > 0;
    const temLocacao = document.querySelectorAll('#list-locacao .cost-item').length > 0;
    const temAdmin = document.querySelectorAll('#list-admin .cost-item').length > 0;

    if (!temSalario && !temEstadia && !temConsumo && !temLocacao && !temAdmin) {
        SGTUtils.showToast('Adicione pelo menos um item de custo (Salário, Estadia ou Equipamento)', 'error');
        // Redireciona para aba de custos se necessário
        if (typeof Wizard !== 'undefined' && Wizard.current !== 3) {
            Wizard.goTo(3);
        }
        return;
    }

    // Sinalizar redirecionamento para o editor avançado após salvar
    createHidden('formato_saida', 'editor');

    // FORÇAR MODO DOCX (Provisório até o usuário implementar Select de Modelos na View)
    // Isso garante que salvar_proposta.php crie corretamente a relação no banco
    const selectModelo = document.querySelector('select[name="modelo_docx"]');
    const modeloAtivo = selectModelo ? selectModelo.value : 'PropostaDrone';
    createHidden('modelo_docx', modeloAtivo);

    // 1. Salários
    document.querySelectorAll('#list-salarios .cost-item').forEach(row => {
        createHidden('salario_id_funcao', row.querySelector('select[name*="[funcao]"]')?.value || '');
        createHidden('salario_nome', row.querySelector('select[name*="[funcao]"] option:checked')?.text || '');
        createHidden('salario_qtd', row.querySelector('input[name*="[quantidade]"]')?.value || 0);
        createHidden('salario_valor', row.querySelector('input[name*="[valor]"]')?.value || 0);
        createHidden('encargos', row.querySelector('input[name*="[encargos]"]')?.value || 0);
        createHidden('salario_dias', row.querySelector('input[name*="[dias]"]')?.value || 0);
    });

    // 2. Estadia
    document.querySelectorAll('#list-estadia .cost-item').forEach(row => {
        createHidden('estadia_id', row.querySelector('select[name*="[tipo]"]')?.value || '');
        createHidden('estadia_nome', row.querySelector('select[name*="[tipo]"] option:checked')?.text || ''); // NECESSÁRIO PARA DB
        createHidden('estadia_qtd', row.querySelector('input[name*="[quantidade]"]')?.value || 0);
        createHidden('estadia_valor', row.querySelector('input[name*="[valor]"]')?.value || 0);
        createHidden('estadia_dias', row.querySelector('input[name*="[noites]"]')?.value || 0);
    });

    // 3. Consumos
    document.querySelectorAll('#list-consumos .cost-item').forEach(row => {
        createHidden('consumo_id', row.querySelector('select[name*="[tipo]"]')?.value || '');
        createHidden('consumo_nome', row.querySelector('select[name*="[tipo]"] option:checked')?.text || ''); // NECESSÁRIO PARA DB
        createHidden('consumo_qtd', row.querySelector('input[name*="[quantidade]"]')?.value || 0);
        createHidden('consumo_kml', row.querySelector('input[name*="[kml]"]')?.value || 0);
        createHidden('consumo_litro', row.querySelector('input[name*="[valor_litro]"]')?.value || 0);
        createHidden('consumo_km_total', row.querySelector('input[name*="[km]"]')?.value || 0);
    });

    // 4. Locação
    document.querySelectorAll('#list-locacao .cost-item').forEach(row => {
        createHidden('locacao_id', row.querySelector('select[name*="[tipo]"]')?.value || '');
        createHidden('locacao_nome', row.querySelector('select[name*="[tipo]"] option:checked')?.text || ''); // NECESSÁRIO PARA DB
        createHidden('locacao_id_marca', row.querySelector('select[name*="[marca]"]')?.value || '');
        createHidden('locacao_qtd', row.querySelector('input[name*="[quantidade]"]')?.value || 0);
        createHidden('locacao_valor', row.querySelector('input[name*="[valor]"]')?.value || 0);
        createHidden('locacao_dias', row.querySelector('input[name*="[dias]"]')?.value || 0);
    });

    // 5. Admin
    document.querySelectorAll('#list-admin .cost-item').forEach(row => {
        createHidden('admin_id', row.querySelector('select[name*="[tipo]"]')?.value || '');
        createHidden('admin_nome', row.querySelector('select[name*="[tipo]"] option:checked')?.text || ''); // NECESSÁRIO PARA DB
        createHidden('admin_qtd', row.querySelector('input[name*="[quantidade]"]')?.value || 0);
        createHidden('admin_valor', row.querySelector('input[name*="[valor]"]')?.value || 0);
    });

    // Define ação: Se existe id_proposta, é uma atualização (Overwrite), senão é novo (Insert)
    const idProposta = document.getElementById('hidden_id_proposta')?.value;
    const isEdit = !!idProposta;

    if (isEdit) {
        const msg = "ATENÇÃO: Ir para o Editor Avançado atualizará esta proposta no banco de dados agora.\n\n" +
                    "Caso tenha feito alterações nos itens acima, elas serão salvas.\n\n" +
                    "Deseja continuar?";
        if (!confirm(msg)) return;
        form.action = 'atualizar_proposta.php';
        
        // Garante que o ID da proposta vá no POST mesmo se o input estiver fora do form
        if (!form.querySelector('input[name="id_proposta"]')) {
            createHidden('id_proposta', idProposta);
        }
    } else {
        form.action = 'salvar_proposta.php';
    }

    form.submit();
};

/**
 * Função global para abrir calculadora (chamada do HTML)
 */
window.openCalculator = function () {
    Calculator?.open();
};

/**
 * Função global para fechar calculadora
 */
window.closeCalculator = function () {
    Calculator?.close();
};

/**
 * Função global para calcular distância (Redireciona para Google Maps conforme preferência do usuário)
 */
window.verMapa = function () {
    const endObra = document.querySelector('input[name="endereco"]').value;
    const cidadeObra = document.querySelector('input[name="cidade"]').value;
    const estadoObra = document.querySelector('select[name="estado"]').value;

    if (!endObra || !cidadeObra) {
        SGTUtils.showToast('Por favor, preencha o Endereço e Cidade da Obra no Passo 1.', 'warning');
        return;
    }

    const enderecoEmpresa = window.SGT_DATA?.enderecoEmpresa;
    if (!enderecoEmpresa) {
        alert('Endereço da empresa não configurado.');
        return;
    }

    const origem = encodeURIComponent(enderecoEmpresa);
    const destino = encodeURIComponent(`${endObra}, ${cidadeObra} - ${estadoObra}`);

    const url = `https://www.google.com/maps/dir/?api=1&origin=${origem}&destination=${destino}`;
    window.open(url, '_blank');
};

/* --- CÁLCULO DE DISTÂNCIA E MAPA (Restaurado) --- */
window.calcularDistancia = async (btn) => {
    const row = btn.closest('.cost-item');
    const inputKm = row.querySelector('input[name*="[km]"]');
    const originalText = btn.innerHTML;

    // 1. Obter Endereços
    const endObra = document.querySelector('input[name="endereco"]').value;
    const cidadeObra = document.querySelector('input[name="cidade"]').value;
    const estadoObra = document.querySelector('select[name="estado"]').value;

    if (!endObra || !cidadeObra) {
        alert('Por favor, preencha o Endereço e Cidade da Obra no Passo 1.');
        return;
    }

    const enderecoEmpresa = window.SGT_DATA?.enderecoEmpresa;
    if (!enderecoEmpresa) {
        alert('Endereço da empresa não configurado.');
        return;
    }

    const origem = enderecoEmpresa;
    const destino = `${endObra}, ${cidadeObra} - ${estadoObra}`;

    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';

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
        const urlRoute = `https://router.project-osrm.org/route/v1/driving/${coordOrigem.lon},${coordOrigem.lat};${coordDestino.lon},${coordDestino.lat}?overview=false`;
        const resRoute = await fetch(urlRoute);
        const dataRoute = await resRoute.json();

        if (dataRoute.code !== 'Ok' || !dataRoute.routes || dataRoute.routes.length === 0) {
            throw new Error('Não foi possível calcular a rota.');
        }

        const distanciaMetros = dataRoute.routes[0].distance;
        const distanciaKm = (distanciaMetros / 1000).toFixed(1);

        // Ida e Volta (x2)
        const kmTotal = (distanciaKm * 2).toFixed(1);

        if (inputKm) {
            inputKm.value = kmTotal;
            // Trigger change event manually since CostsManager uses 'input' event delegation
            inputKm.dispatchEvent(new Event('input', { bubbles: true }));
        }

        SGTUtils.showToast(`Distância calculada: ${distanciaKm} km (Ida). Definido ${kmTotal} km (Ida e Volta).`, 'success');

    } catch (error) {
        console.error(error);
        alert('Erro ao calcular: ' + error.message + '\n\nTente inserir manualmente.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
};
