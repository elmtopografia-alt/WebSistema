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
 * Função global para ir para o editor após persistência real no BD
 */
window.irParaEditor = async function () {
    const form = document.getElementById('form-proposta');
    const btnFinish = document.getElementById('btn-finish');

    if (!form || !btnFinish) return;

    // 1. Validação do Cliente (Obrigatório)
    const idCliente = document.getElementById('id_cliente')?.value;
    if (!idCliente || idCliente === '') {
        alert('Por favor, selecione um cliente na Etapa 1.');
        if (typeof Wizard !== 'undefined' && typeof Wizard.goTo === 'function') {
            Wizard.goTo(1);
        }
        return;
    }

    // 2. Validação do Valor Final
    const valorFinalInput = document.getElementById('valor-final-proposta');
    const valorFinalText = valorFinalInput ? valorFinalInput.innerText.replace('R$', '').replace(/\./g, '').replace(',', '.') : '0';
    const valorFinal = parseFloat(valorFinalText);

    if (isNaN(valorFinal) || valorFinal <= 0) {
        if (!confirm('O valor final da proposta parece estar zerado. Deseja continuar mesmo assim?')) {
            return;
        }
    }

    // 3. Validação geral do Wizard
    if (typeof window.Wizard?.validate === 'function' && !window.Wizard.validate()) {
        return;
    }

    // Prepara botão (Feedback visual)
    const originalBtnHTML = btnFinish.innerHTML;
    btnFinish.disabled = true;
    btnFinish.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Salvando no Banco...';

    try {
        // Coleta TODOS os dados do formulário via FormData
        const formData = new FormData(form);

        // COR TEMA e MODELO DOCX - Já estão nos hidden inputs do Step 2
        const corAtiva = document.getElementById('hidden_cor')?.value || 'verde';
        const modeloAtivo = document.getElementById('hidden_modelo_docx')?.value || 'PropostaDrone';

        // Garante que estão no FormData (fallback caso o DOM mude)
        if (!formData.has('cor')) formData.append('cor', corAtiva);
        if (!formData.has('modelo_docx')) formData.append('modelo_docx', modeloAtivo);

        // Dispara requisição AJAX com fetch API nativa
        const response = await fetch('salvar_rascunho.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        // Parse Seguro
        let data;
        const textResponse = await response.text();
        try {
            data = JSON.parse(textResponse);
        } catch (parseError) {
            console.error("Erro parsing JSON. Raw content:", textResponse);
            throw new Error('A resposta do servidor foi inválida (não JSON). Tente novamente.');
        }

        // Verifica sucesso na resposta
        if (!data.success || !data.id_proposta) {
            throw new Error(data.message || data.error || 'Falha ao obter ID da proposta.');
        }

        // Sucesso = Redirecionamento Final
        btnFinish.innerHTML = '<i class="bi bi-check-circle"></i> Salvo! Abrindo editor...';

        const idGerado = data.id_proposta;
        // Prioriza URL do servidor, mas reconstrói se falhar
        const urlRedirect = data.redirect_url || `editor_dinamico.php?id=${idGerado}&modelo_docx=${encodeURIComponent(modeloAtivo)}&cor=${encodeURIComponent(corAtiva)}`;

        // Redireciona
        setTimeout(() => {
            window.location.href = urlRedirect;
        }, 800);

    } catch (error) {
        console.error('Erro no fluxo de persistência:', error);
        alert('Erro ao salvar proposta: ' + error.message);

        // Restaura botão em caso de falha (não sai da tela)
        btnFinish.disabled = false;
        btnFinish.innerHTML = originalBtnHTML;
    }
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
