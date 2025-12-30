<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Gera Proposta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .fade-in { animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        /* Estilo para simular o modal de email */
        .email-modal {
            background: #fff;
            border-left: 5px solid #3b82f6;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <!-- NAVIGATION -->
    <nav class="bg-indigo-900 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar"></i> Sistema Gera Proposta
            </div>
            <div class="space-x-4">
                <button onclick="router('home')" class="hover:text-indigo-300 transition">Solicitação</button>
                <button onclick="router('admin')" class="hover:text-indigo-300 transition">Painel Admin (MySQL)</button>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTAINER -->
    <div class="container mx-auto p-6 min-h-screen">
        
        <!-- VIEW: FORMULÁRIO DE SOLICITAÇÃO (HOME) -->
        <div id="view-home" class="view-section max-w-lg mx-auto bg-white p-8 rounded-lg shadow-lg fade-in">
            <h2 class="text-2xl font-bold mb-6 text-indigo-800 text-center">Solicitar Acesso</h2>
            <form id="accessForm" onsubmit="handleFormSubmit(event)">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nome Completo</label>
                    <input type="text" id="nome" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Seu nome">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" id="email" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="seu@email.com">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Solicitação</label>
                    <select id="tipo" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="gera_proposta">Gera Proposta (Versão Completa)</option>
                        <option value="gera_proposta_demo">Gera Proposta Demo (Teste 5 dias)</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 transition duration-300">
                    Enviar Solicitação
                </button>
            </form>
        </div>

        <!-- VIEW: PLANOS (PAGAMENTO) -->
        <div id="view-plans" class="view-section hidden fade-in">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-800">Escolha seu Plano</h2>
                <p class="text-gray-600 mt-2">Finalize sua assinatura para liberar o acesso ao Gera Proposta.</p>
                <!-- Botão de simulação de usuário vindo do demo -->
                <div id="upgrade-alert" class="hidden mt-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 max-w-2xl mx-auto text-left">
                    <p class="font-bold">Atenção</p>
                    <p>Seu período de demonstração expirou. Escolha um plano abaixo para manter seus dados.</p>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Plano Mensal -->
                <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition overflow-hidden border border-gray-200">
                    <div class="p-8 text-center">
                        <h3 class="text-xl font-bold text-gray-500">Mensal</h3>
                        <div class="text-4xl font-bold text-indigo-600 my-4">R$ 49,90</div>
                        <p class="text-gray-500 text-sm mb-6">Cobrado mensalmente</p>
                        <ul class="text-left text-gray-600 mb-8 space-y-2">
                            <li><i class="fa-solid fa-check text-green-500 mr-2"></i>Acesso completo</li>
                            <li><i class="fa-solid fa-check text-green-500 mr-2"></i>Suporte Básico</li>
                        </ul>
                        <button onclick="processPayment('Mensal')" class="w-full bg-blue-500 text-white font-bold py-2 rounded hover:bg-blue-600 transition">
                            <i class="fa-solid fa-credit-card mr-2"></i> Mercado Pago
                        </button>
                    </div>
                </div>

                <!-- Plano Trimestral -->
                <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition overflow-hidden border-2 border-indigo-500 relative transform md:-translate-y-4">
                    <div class="absolute top-0 right-0 bg-indigo-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">MAIS POPULAR</div>
                    <div class="p-8 text-center">
                        <h3 class="text-xl font-bold text-indigo-600">Trimestral</h3>
                        <div class="text-4xl font-bold text-indigo-600 my-4">R$ 129,90</div>
                        <p class="text-gray-500 text-sm mb-6">Economize 15%</p>
                        <ul class="text-left text-gray-600 mb-8 space-y-2">
                            <li><i class="fa-solid fa-check text-green-500 mr-2"></i>Acesso completo</li>
                            <li><i class="fa-solid fa-check text-green-500 mr-2"></i>Suporte Prioritário</li>
                        </ul>
                        <button onclick="processPayment('Trimestral')" class="w-full bg-blue-600 text-white font-bold py-3 rounded hover:bg-blue-700 transition">
                            <i class="fa-solid fa-credit-card mr-2"></i> Mercado Pago
                        </button>
                    </div>
                </div>

                <!-- Plano Anual -->
                <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition overflow-hidden border border-gray-200">
                    <div class="p-8 text-center">
                        <h3 class="text-xl font-bold text-gray-500">Anual</h3>
                        <div class="text-4xl font-bold text-indigo-600 my-4">R$ 499,90</div>
                        <p class="text-gray-500 text-sm mb-6">Economize 20%</p>
                        <ul class="text-left text-gray-600 mb-8 space-y-2">
                            <li><i class="fa-solid fa-check text-green-500 mr-2"></i>Acesso VIP</li>
                            <li><i class="fa-solid fa-check text-green-500 mr-2"></i>Consultoria inclusa</li>
                        </ul>
                        <button onclick="processPayment('Anual')" class="w-full bg-blue-500 text-white font-bold py-2 rounded hover:bg-blue-600 transition">
                            <i class="fa-solid fa-credit-card mr-2"></i> Mercado Pago
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW: ADMIN (SIMULAÇÃO MYSQL) -->
        <div id="view-admin" class="view-section hidden fade-in">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Painel Administrativo - Banco de Dados</h2>

            <!-- Tabela Demanda -->
            <div class="bg-white rounded-lg shadow mb-8 overflow-hidden">
                <div class="bg-indigo-600 p-4 flex justify-between items-center">
                    <h3 class="text-white font-bold text-lg"><i class="fa-solid fa-database mr-2"></i> Tabela: demanda (Usuários Pagos)</h3>
                    <span class="text-indigo-200 text-sm">Gera Proposta Completo</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm uppercase">
                                <th class="p-4 border-b">ID</th>
                                <th class="p-4 border-b">Nome</th>
                                <th class="p-4 border-b">Email</th>
                                <th class="p-4 border-b">Usuário</th>
                                <th class="p-4 border-b">Plano</th>
                                <th class="p-4 border-b">Status</th>
                            </tr>
                        </thead>
                        <tbody id="table-demanda-body">
                            <!-- JS preencherá aqui -->
                        </tbody>
                    </table>
                </div>
                <div id="empty-demanda" class="p-4 text-center text-gray-500 italic">Nenhum registro encontrado.</div>
            </div>

            <!-- Tabela Proposta -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-orange-500 p-4 flex justify-between items-center">
                    <h3 class="text-white font-bold text-lg"><i class="fa-solid fa-database mr-2"></i> Tabela: proposta (Usuários Demo)</h3>
                    <span class="text-orange-100 text-sm">Gera Proposta Demo</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm uppercase">
                                <th class="p-4 border-b">ID</th>
                                <th class="p-4 border-b">Nome</th>
                                <th class="p-4 border-b">Email</th>
                                <th class="p-4 border-b">Senha Temp.</th>
                                <th class="p-4 border-b">Expira em</th>
                                <th class="p-4 border-b">Ações (Simulação)</th>
                            </tr>
                        </thead>
                        <tbody id="table-proposta-body">
                            <!-- JS preencherá aqui -->
                        </tbody>
                    </table>
                </div>
                <div id="empty-proposta" class="p-4 text-center text-gray-500 italic">Nenhum registro encontrado.</div>
            </div>
        </div>

    </div>

    <!-- MODAL DE EMAIL SIMULADO -->
    <div id="email-modal" class="fixed bottom-5 right-5 max-w-md w-full transform translate-y-full transition-transform duration-500 z-50">
        <div class="email-modal rounded-lg p-4 shadow-2xl relative">
            <button onclick="closeEmail()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600"><i class="fa-solid fa-times"></i></button>
            <div class="flex items-start mb-3">
                <div class="bg-blue-100 p-2 rounded-full mr-3 text-blue-600"><i class="fa-solid fa-envelope"></i></div>
                <div>
                    <h4 class="font-bold text-gray-800">Novo Email Recebido</h4>
                    <p class="text-xs text-gray-500">De: sistema@geraproposta.com</p>
                </div>
            </div>
            <div class="bg-gray-50 p-3 rounded text-sm text-gray-700 border border-gray-200 font-mono" id="email-content">
                <!-- Conteúdo do email -->
            </div>
        </div>
    </div>

    <script>
        // --- 1. CONFIGURAÇÃO E DADOS (Simulando MySQL) ---
        
        // Estrutura das tabelas "MySQL" na memória
        const db = {
            demanda: [], // Usuários Pagos
            proposta: [] // Usuários Demo
        };

        // Variável "Sessão" temporária para o fluxo de compra
        let tempUserSession = null;

        // --- 2. ROTEAMENTO E UI ---

        function router(viewName) {
            // Esconde todas as views
            document.querySelectorAll('.view-section').forEach(el => el.classList.add('hidden'));
            // Mostra a selecionada
            document.getElementById(`view-${viewName}`).classList.remove('hidden');
            
            if(viewName === 'admin') renderTables();
        }

        function showEmail(subject, bodyHtml) {
            const modal = document.getElementById('email-modal');
            const content = document.getElementById('email-content');
            content.innerHTML = `<strong class="block mb-2">${subject}</strong>${bodyHtml}`;
            modal.classList.remove('translate-y-full');
            
            // Auto-hide após 10 segundos
            setTimeout(() => closeEmail(), 10000);
        }

        function closeEmail() {
            document.getElementById('email-modal').classList.add('translate-y-full');
        }

        // --- 3. LÓGICA DE NEGÓCIO (PHP Logic) ---

        // Funções Auxiliares
        function generateRandomString(length) {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let result = '';
            for ( let i = 0; i < length; i++ ) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        }

        function generateStrongPassword() {
            return generateRandomString(8) + '!@#'; // Garante caractere especial simulado
        }

        // Processamento do Formulário Inicial
        function handleFormSubmit(e) {
            e.preventDefault();
            const nome = document.getElementById('nome').value;
            const email = document.getElementById('email').value;
            const tipo = document.getElementById('tipo').value;

            if (tipo === 'gera_proposta') {
                // FLUXO 1: GERA PROPOSTA (PAGO)
                // Guarda dados na "sessão" (Variants)
                tempUserSession = { nome, email, origin: 'direct' };
                
                // Redireciona para Planos
                document.getElementById('upgrade-alert').classList.add('hidden');
                router('plans');

            } else {
                // FLUXO 2: GERA PROPOSTA DEMO
                // Lógica de "PHP": Inserir direto no MySQL tabela Proposta
                
                const senhaTemp = generateRandomString(8);
                const dataHoje = new Date();
                const dataExpira = new Date(dataHoje);
                dataExpira.setDate(dataHoje.getDate() + 5); // +5 dias

                const novoRegistro = {
                    id: db.proposta.length + 1,
                    nome: nome,
                    email: email,
                    senha: senhaTemp,
                    data_criacao: dataHoje.toISOString(),
                    data_expira: dataExpira.toISOString(),
                    status: 'ativo'
                };

                db.proposta.push(novoRegistro);

                // Disparar Email
                const emailBody = `
                    Olá ${nome},<br>
                    Seu acesso DEMO foi criado.<br>
                    <strong>Login:</strong> ${email}<br>
                    <strong>Senha:</strong> ${senhaTemp}<br>
                    <span style="color:red">Válido por 5 dias.</span>
                `;
                showEmail("Acesso Demo Liberado", emailBody);

                // Limpa form e mostra Admin para provar gravação
                e.target.reset();
                alert("Cadastro Demo realizado! Verifique a simulação de email no canto inferior.");
                router('admin');
            }
        }

        // Processamento de Pagamento (Planos)
        function processPayment(planoEscolhido) {
            if (!tempUserSession) {
                alert("Erro de sessão. Comece novamente.");
                router('home');
                return;
            }

            // Simula processamento do Mercado Pago...
            const loading = confirm(`Simulando pagamento do plano ${planoEscolhido} no Mercado Pago...\nClique em OK para Aprovar o pagamento.`);
            
            if (loading) {
                // Pagamento Aprovado -> Gravar na tabela Demanda
                const usuarioGerado = 'US' + generateRandomString(6).toUpperCase();
                const senhaForte = generateStrongPassword();

                // Verifica se veio de um upgrade (estava na tabela proposta)
                if (tempUserSession.origin === 'upgrade') {
                     // Atualiza status na tabela proposta para 'convertido'
                     const propIndex = db.proposta.findIndex(u => u.id === tempUserSession.oldId);
                     if(propIndex > -1) db.proposta[propIndex].status = 'convertido (migrado)';
                }

                const novoCliente = {
                    id: db.demanda.length + 1,
                    nome: tempUserSession.nome,
                    email: tempUserSession.email,
                    usuario: usuarioGerado,
                    senha: senhaForte, // Na vida real, seria um hash
                    plano: planoEscolhido,
                    status: 'ativo'
                };

                db.demanda.push(novoCliente);

                // Envia Email Final
                const emailBody = `
                    Pagamento confirmado (${planoEscolhido})!<br>
                    Aqui estão seus dados definitivos:<br>
                    <strong>Usuario:</strong> ${usuarioGerado}<br>
                    <strong>Senha:</strong> ${senhaForte}<br>
                    Acesse o Gera Proposta agora.
                `;
                showEmail("Bem-vindo ao Gera Proposta", emailBody);

                tempUserSession = null; // Limpa sessão
                alert("Assinatura realizada com sucesso! Dados enviados por email.");
                router('admin');
            }
        }

        // --- 4. FUNÇÕES DE SIMULAÇÃO (ADMIN & UPGRADE) ---

        // Renderiza as tabelas no painel admin
        function renderTables() {
            // Render Demanda
            const tbodyDemanda = document.getElementById('table-demanda-body');
            tbodyDemanda.innerHTML = '';
            if (db.demanda.length === 0) {
                document.getElementById('empty-demanda').style.display = 'block';
            } else {
                document.getElementById('empty-demanda').style.display = 'none';
                db.demanda.forEach(row => {
                    const tr = `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-4">${row.id}</td>
                            <td class="p-4 font-bold text-gray-700">${row.nome}</td>
                            <td class="p-4 text-gray-600">${row.email}</td>
                            <td class="p-4"><span class="bg-blue-100 text-blue-800 py-1 px-2 rounded text-xs font-mono">${row.usuario}</span></td>
                            <td class="p-4">${row.plano}</td>
                            <td class="p-4"><span class="text-green-600 font-bold text-xs border border-green-600 px-2 py-1 rounded">ATIVO</span></td>
                        </tr>
                    `;
                    tbodyDemanda.innerHTML += tr;
                });
            }

            // Render Proposta
            const tbodyProposta = document.getElementById('table-proposta-body');
            tbodyProposta.innerHTML = '';
            if (db.proposta.length === 0) {
                document.getElementById('empty-proposta').style.display = 'block';
            } else {
                document.getElementById('empty-proposta').style.display = 'none';
                db.proposta.forEach(row => {
                    
                    // Lógica para verificar se expirou (Simulação)
                    const hoje = new Date();
                    const expira = new Date(row.data_expira);
                    const expirado = hoje > expira;
                    
                    let statusBadge = expirado 
                        ? `<span class="bg-red-100 text-red-800 py-1 px-2 rounded text-xs font-bold">EXPIRADO</span>` 
                        : `<span class="bg-orange-100 text-orange-800 py-1 px-2 rounded text-xs font-bold">EM TESTE</span>`;

                    if(row.status.includes('migrado')) {
                        statusBadge = `<span class="bg-gray-100 text-gray-500 py-1 px-2 rounded text-xs">MIGRADO</span>`;
                    }

                    // Ações: Botões para simular o comportamento do usuário
                    let actions = '';
                    if (!row.status.includes('migrado')) {
                        actions = `
                        <div class="flex flex-col gap-2">
                            <button onclick="simularExpiracao(${row.id})" class="text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-gray-700" title="Avança data para simular expiração">
                                <i class="fa-solid fa-clock-rotate-left"></i> Envelhecer 6 dias
                            </button>
                            <button onclick="simularLoginUsuario(${row.id})" class="text-xs bg-green-100 hover:bg-green-200 text-green-800 px-2 py-1 rounded font-bold border border-green-300">
                                <i class="fa-solid fa-right-to-bracket"></i> Login Usuário
                            </button>
                        </div>
                        `;
                    }

                    const tr = `
                        <tr class="border-b hover:bg-gray-50 ${row.status.includes('migrado') ? 'opacity-50' : ''}">
                            <td class="p-4">${row.id}</td>
                            <td class="p-4">${row.nome}</td>
                            <td class="p-4 text-xs text-gray-500">${row.email}</td>
                            <td class="p-4 font-mono text-xs">${row.senha}</td>
                            <td class="p-4 text-xs">
                                ${new Date(row.data_expira).toLocaleDateString()}<br>
                                ${statusBadge}
                            </td>
                            <td class="p-4">
                                ${actions}
                            </td>
                        </tr>
                    `;
                    tbodyProposta.innerHTML += tr;
                });
            }
        }

        // Função para testar a expiração (força a data para o passado)
        function simularExpiracao(id) {
            const user = db.proposta.find(u => u.id === id);
            if (user) {
                const passado = new Date();
                passado.setDate(passado.getDate() - 6); // Coloca data de expiração 6 dias atrás
                user.data_expira = passado.toISOString();
                renderTables(); // Atualiza UI
                alert(`Data de expiração do usuário ${user.nome} alterada para o passado.`);
            }
        }

        // Função que simula o usuário tentando logar
        function simularLoginUsuario(id) {
            const user = db.proposta.find(u => u.id === id);
            if (!user) return;

            const hoje = new Date();
            const expira = new Date(user.data_expira);

            if (hoje > expira) {
                // EXPIRADO: Mensagem do sistema e redirecionamento para Planos
                const aceitar = confirm(`[SISTEMA]: Olá ${user.nome}. Seu período de 5 dias expirou.\n\nDeseja se tornar um usuário do Gera Proposta agora? (Isso o levará aos planos)`);
                
                if (aceitar) {
                    // Prepara sessão para upgrade
                    tempUserSession = {
                        nome: user.nome,
                        email: user.email,
                        origin: 'upgrade',
                        oldId: user.id
                    };
                    
                    // Mostra alerta visual na página de planos
                    document.getElementById('upgrade-alert').classList.remove('hidden');
                    router('plans');
                }
            } else {
                // AINDA VÁLIDO
                alert(`[SISTEMA]: Login bem sucedido! Você ainda tem ${(Math.ceil((expira - hoje)/(1000*60*60*24)))} dias de acesso Demo.`);
            }
        }

        // Inicializa na  Home
        router('home');

    </script>
</body>
</html>