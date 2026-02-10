<?php
// ==========================================================
// ARQUIVO: gerador_dados.php
// OBJETIVO: Gerador de Dados para Testes (Refinado)
// VERSÃO: Arquivo Único | Atualização: Campos Separados e Sexo
// ==========================================================

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'session_validator.php';

$id_usuario = $_SESSION['usuario_id'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT | Gerador de Dados</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web" defer></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], display: ['Exo 2', 'sans-serif'] },
                    colors: { background: '#0a0f1a', surface: '#111827', primary: '#f97316' }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0a0f1a; color: #f8fafc; }
        .glass { background: rgba(17, 24, 39, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glass-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .input-dark { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: white; }
        .input-dark:focus { border-color: #f97316; outline: none; }
        .result-field { position: relative; cursor: pointer; transition: all 0.2s; }
        .result-field:hover { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.2); }
        .result-field:active { transform: scale(0.99); }
        .result-label { font-size: 0.7rem; text-transform: uppercase; color: #94a3b8; font-weight: 600; margin-bottom: 2px; }
        .result-value { font-family: monospace; font-size: 0.95rem; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .copy-toast { position: fixed; bottom: 20px; right: 20px; background: #22c55e; color: white; padding: 10px 20px; border-radius: 8px; transform: translateY(100px); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); z-index: 100; font-weight: 600; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .copy-toast.show { transform: translateY(0); }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">

    <!-- Toast de Cópia -->
    <div id="toast" class="copy-toast">
        <i class="ph ph-check-circle"></i> Copiado!
    </div>

    <!-- Referência Navbar (opcional, mantendo simples) -->
    <nav class="border-b border-white/10 bg-surface/50 backdrop-blur-md px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-2">
            <i class="ph ph-flask text-2xl text-orange-500"></i>
            <span class="font-display font-bold text-xl">Gerador de Dados</span>
        </div>
        <a href="painel.php" class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Voltar ao Painel</a>
    </nav>

    <main class="flex-1 max-w-6xl mx-auto w-full px-4 py-8">
        
        <!-- Controle de Abas -->
        <div class="flex justify-center mb-8">
            <div class="bg-surface/50 p-1 rounded-xl border border-white/10 inline-flex">
                <button onclick="switchTab('fisica')" id="btn-fisica" class="px-6 py-2 rounded-lg text-sm font-bold transition-all bg-orange-600 text-white shadow-lg">
                    Pessoa Física
                </button>
                <button onclick="switchTab('juridica')" id="btn-juridica" class="px-6 py-2 rounded-lg text-sm font-bold transition-all text-slate-400 hover:text-white">
                    Pessoa Jurídica
                </button>
            </div>
        </div>

        <!-- ABA: PESSOA FÍSICA -->
        <div id="tab-fisica" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Configurações (Esquerda) -->
            <div class="lg:col-span-4 space-y-6">
                <div class="glass-card p-6 rounded-2xl border-l-4 border-orange-500">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <i class="ph ph-sliders"></i> Configuração
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Sexo -->
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Sexo</label>
                            <div class="grid grid-cols-2 gap-2 mt-1">
                                <label class="cursor-pointer">
                                    <input type="radio" name="sexo" value="M" checked class="peer sr-only">
                                    <div class="bg-black/20 border border-white/10 rounded-lg p-3 text-center text-slate-400 peer-checked:bg-blue-500/20 peer-checked:border-blue-500 peer-checked:text-blue-400 transition-all hover:bg-white/5">
                                        <i class="ph ph-gender-male"></i> Masc
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="sexo" value="F" class="peer sr-only">
                                    <div class="bg-black/20 border border-white/10 rounded-lg p-3 text-center text-slate-400 peer-checked:bg-pink-500/20 peer-checked:border-pink-500 peer-checked:text-pink-400 transition-all hover:bg-white/5">
                                        <i class="ph ph-gender-female"></i> Fem
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Localização -->
                        <div class="grid grid-cols-3 gap-2">
                            <div class="col-span-1">
                                <label class="text-xs font-bold text-slate-500 uppercase">UF</label>
                                <select id="pf-uf" class="w-full input-dark rounded-lg p-2.5 mt-1" onchange="Gerador.carregarCidades('pf')">
                                    <option value="SP">SP</option>
                                    <!-- JS popula resto -->
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="text-xs font-bold text-slate-500 uppercase">Cidade</label>
                                <select id="pf-cidade" class="w-full input-dark rounded-lg p-2.5 mt-1">
                                    <option value="">Aleatória</option>
                                </select>
                            </div>
                        </div>

                        <button onclick="Gerador.gerarPessoa()" class="w-full bg-orange-600 hover:bg-orange-500 text-white font-bold py-3 rounded-xl shadow-lg shadow-orange-600/20 transition-all active:scale-95 flex items-center justify-center gap-2 mt-4">
                            <i class="ph ph-arrows-clockwise text-xl"></i> Gerar Dados
                        </button>
                    </div>
                </div>
            </div>

            <!-- Resultados (Direita) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Cartão Identidade -->
                <div class="glass-card p-6 rounded-2xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="ph ph-identification-card text-9xl text-white"></i>
                    </div>
                    
                    <h3 class="text-sm font-bold text-blue-400 uppercase tracking-wider mb-4">Identificação Pessoal</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Nome -->
                        <div class="md:col-span-2 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pf-res-nome')">
                            <div class="result-label">Nome Completo</div>
                            <div id="pf-res-nome" class="text-xl font-bold text-white">...</div>
                        </div>

                        <!-- CPF -->
                        <div class="p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pf-res-cpf')">
                            <div class="result-label">CPF</div>
                            <div id="pf-res-cpf" class="text-lg font-mono text-green-400 font-bold">...</div>
                        </div>

                        <!-- RG -->
                        <div class="p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pf-res-rg')">
                            <div class="result-label">RG</div>
                            <div id="pf-res-rg" class="text-lg font-mono">...</div>
                        </div>

                        <!-- Email (Extra) -->
                        <div class="md:col-span-2 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pf-res-email')">
                            <div class="result-label">E-mail Gerado</div>
                            <div id="pf-res-email" class="text-sm text-slate-300">...</div>
                        </div>
                    </div>
                </div>

                <!-- Cartão Endereço -->
                <div class="glass-card p-6 rounded-2xl">
                    <h3 class="text-sm font-bold text-orange-400 uppercase tracking-wider mb-4">Endereço (Campos Separados)</h3>
                    
                    <div class="grid grid-cols-12 gap-4">
                        <!-- Logradouro e Número Check -->
                        <div class="col-span-12 md:col-span-9 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pf-res-logradouro')">
                            <div class="result-label">Logradouro (Rua/Av)</div>
                            <div id="pf-res-logradouro" class="text-white">...</div>
                        </div>
                        <div class="col-span-12 md:col-span-3 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pf-res-numero')">
                            <div class="result-label">Número</div>
                            <div id="pf-res-numero" class="text-white font-mono">...</div>
                        </div>

                        <!-- Bairro -->
                        <div class="col-span-12 md:col-span-5 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pf-res-bairro')">
                            <div class="result-label">Bairro</div>
                            <div id="pf-res-bairro" class="text-white">...</div>
                        </div>

                        <!-- Cidade -->
                        <div class="col-span-12 md:col-span-5 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pf-res-cidade')">
                            <div class="result-label">Cidade</div>
                            <div id="pf-res-cidade" class="text-white">...</div>
                        </div>

                        <!-- UF -->
                        <div class="col-span-6 md:col-span-2 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pf-res-uf')">
                            <div class="result-label">UF</div>
                            <div id="pf-res-uf" class="text-white font-bold text-center">...</div>
                        </div>

                        <!-- CEP -->
                        <div class="col-span-6 md:col-span-12 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pf-res-cep')">
                            <div class="result-label">CEP</div>
                            <div id="pf-res-cep" class="text-white font-mono text-yellow-400">...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ABA: PESSOA JURÍDICA -->
        <div id="tab-juridica" class="hidden grid grid-cols-1 lg:grid-cols-12 gap-8">
             <div class="lg:col-span-4 space-y-6">
                <div class="glass-card p-6 rounded-2xl border-l-4 border-blue-500">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <i class="ph ph-buildings"></i> Configuração PJ
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Busca CEP -->
                        <div class="bg-black/30 p-3 rounded-xl border border-white/10">
                            <label class="text-xs font-bold text-slate-500 uppercase block mb-2">Usar Endereço Real (Busca CEP)</label>
                            <div class="flex gap-2">
                                <input type="text" id="pj-cep-busca" placeholder="00000-000" class="w-full input-dark rounded-lg p-2 text-sm mask-cep">
                                <button onclick="Gerador.gerarEmpresa(true)" class="bg-blue-600 hover:bg-blue-500 text-white p-2 rounded-lg">
                                    <i class="ph ph-magnifying-glass"></i>
                                </button>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1">Deixe vazio para aleatório</p>
                        </div>
                        
                         <!-- Localização -->
                        <div class="grid grid-cols-3 gap-2">
                            <div class="col-span-1">
                                <label class="text-xs font-bold text-slate-500 uppercase">UF</label>
                                <select id="pj-uf" class="w-full input-dark rounded-lg p-2.5 mt-1" onchange="Gerador.carregarCidades('pj')">
                                    <!-- JS -->
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="text-xs font-bold text-slate-500 uppercase">Cidade</label>
                                <select id="pj-cidade" class="w-full input-dark rounded-lg p-2.5 mt-1">
                                    <option value="">Aleatória</option>
                                </select>
                            </div>
                        </div>

                        <button onclick="Gerador.gerarEmpresa()" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-600/20 transition-all active:scale-95 flex items-center justify-center gap-2 mt-4">
                            <i class="ph ph-arrows-clockwise text-xl"></i> Gerar Empresa
                        </button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8 space-y-6">
                 <!-- Dados Empresa -->
                <div class="glass-card p-6 rounded-2xl">
                    <h3 class="text-sm font-bold text-blue-400 uppercase tracking-wider mb-4">Dados da Empresa</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pj-res-nome')">
                            <div class="result-label">Razão Social</div>
                            <div id="pj-res-nome" class="text-xl font-bold text-white">...</div>
                        </div>

                        <div class="p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pj-res-cnpj')">
                            <div class="result-label">CNPJ</div>
                            <div id="pj-res-cnpj" class="text-lg font-mono text-green-400 font-bold">...</div>
                        </div>

                        <div class="p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pj-res-ie')">
                            <div class="result-label">Inscrição Estadual</div>
                            <div id="pj-res-ie" class="text-lg font-mono">...</div>
                        </div>
                    </div>
                </div>

                <!-- Endereço Empresa -->
                <div class="glass-card p-6 rounded-2xl">
                    <h3 class="text-sm font-bold text-orange-400 uppercase tracking-wider mb-4">Localização (Campos Separados)</h3>
                    
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 md:col-span-9 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pj-res-logradouro')">
                            <div class="result-label">Logradouro</div>
                            <div id="pj-res-logradouro" class="text-white">...</div>
                        </div>
                        <div class="col-span-12 md:col-span-3 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pj-res-numero')">
                            <div class="result-label">Número</div>
                            <div id="pj-res-numero" class="text-white font-mono">...</div>
                        </div>
                        <div class="col-span-12 md:col-span-5 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pj-res-bairro')">
                            <div class="result-label">Bairro</div>
                            <div id="pj-res-bairro" class="text-white">...</div>
                        </div>
                        <div class="col-span-12 md:col-span-5 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pj-res-cidade')">
                            <div class="result-label">Cidade</div>
                            <div id="pj-res-cidade" class="text-white">...</div>
                        </div>
                        <div class="col-span-6 md:col-span-2 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pj-res-uf')">
                            <div class="result-label">UF</div>
                            <div id="pj-res-uf" class="text-white font-bold text-center">...</div>
                        </div>
                        <div class="col-span-6 md:col-span-12 p-3 bg-black/20 rounded-xl border border-white/5 result-field" onclick="copy(this, '#pj-res-cep')">
                            <div class="result-label">CEP</div>
                            <div id="pj-res-cep" class="text-white font-mono text-yellow-400">...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script>
    /**
     * SGT Gerador de Dados - Engine v2.0
     */
    const Gerador = {
        db: {
            estados: [
                { sigla: 'SP', nome: 'São Paulo' }, { sigla: 'RJ', nome: 'Rio de Janeiro' }, 
                { sigla: 'MG', nome: 'Minas Gerais' }, { sigla: 'RS', nome: 'Rio Grande do Sul' },
                { sigla: 'PR', nome: 'Paraná' }, { sigla: 'BA', nome: 'Bahia' },
                { sigla: 'SC', nome: 'Santa Catarina' }, { sigla: 'GO', nome: 'Goiás' },
                { sigla: 'PE', nome: 'Pernambuco' }, { sigla: 'CE', nome: 'Ceará' }
                // Pode adicionar mais
            ],
            cidades: {
                'SP': ['São Paulo', 'Campinas', 'Santos', 'Sorocaba'],
                'RJ': ['Rio de Janeiro', 'Niterói', 'Duque de Caxias'],
                'MG': ['Belo Horizonte', 'Uberlândia', 'Juiz de Fora'],
                'PR': ['Curitiba', 'Londrina'],
                'RS': ['Porto Alegre', 'Caxias do Sul']
            },
            nomes: {
                'M': ['João', 'Pedro', 'Lucas', 'Gabriel', 'Mateus', 'Enzo', 'Miguel', 'Arthur', 'Davi', 'Rafael', 'Bruno', 'Carlos', 'Eduardo'],
                'F': ['Maria', 'Ana', 'Julia', 'Larissa', 'Beatriz', 'Alice', 'Laura', 'Manuela', 'Sophia', 'Isabela', 'Camila', 'Fernanda']
            },
            sobrenomes: ['Silva', 'Santos', 'Oliveira', 'Souza', 'Rodrigues', 'Ferreira', 'Alves', 'Pereira', 'Lima', 'Gomes', 'Costa', 'Ribeiro'],
            empresaPr: ['Tech', 'Grupo', 'Soluções', 'Indústria', 'Comércio', 'Construtora', 'Consultoria', 'Sistemas', 'Atacadista'],
            empresaSf: ['Ltda', 'S.A.', 'ME', 'Eireli', 'Tecnologia', 'Brasil', 'Global', 'Logística'],
            
            // Dados de Rua Real para Capitais (Fallback sem API)
            ruasReais: {
                'São Paulo': [
                    { log: 'Avenida Paulista', bai: 'Bela Vista', cep: '01310-100' },
                    { log: 'Rua Augusta', bai: 'Consolação', cep: '01305-000' }
                ],
                'Rio de Janeiro': [
                    { log: 'Avenida Atlântica', bai: 'Copacabana', cep: '22021-001' }
                ],
                'Belo Horizonte': [
                    { log: 'Avenida Afonso Pena', bai: 'Centro', cep: '30130-000' }
                ]
            }
        },

        init() {
            this.popularEstados();
            document.getElementById('pf-uf').value = 'SP';
            document.getElementById('pj-uf').value = 'SP';
            this.carregarCidades('pf');
            this.carregarCidades('pj');
            this.gerarPessoa();
        },

        popularEstados() {
            const opts = this.db.estados.map(e => `<option value="${e.sigla}">${e.sigla} - ${e.nome}</option>`).join('');
            document.getElementById('pf-uf').innerHTML = opts;
            document.getElementById('pj-uf').innerHTML = opts;
        },

        carregarCidades(tipo) {
            const uf = document.getElementById(tipo+'-uf').value;
            const select = document.getElementById(tipo+'-cidade');
            const lista = this.db.cidades[uf] || [];
            
            if (lista.length > 0) {
                select.innerHTML = '<option value="">Aleatória</option>' + lista.map(c => `<option value="${c}">${c}</option>`).join('');
            } else {
                select.innerHTML = '<option value="">Capital</option>';
            }
        },

        rand(arr) { return arr[Math.floor(Math.random() * arr.length)]; },

        gerarPessoa() {
            // Nomes
            const sexo = document.querySelector('input[name="sexo"]:checked').value;
            const nome = this.rand(this.db.nomes[sexo]);
            const sobre = this.rand(this.db.sobrenomes) + ' ' + this.rand(this.db.sobrenomes);
            
            this.setVal('#pf-res-nome', `${nome} ${sobre}`);
            this.setVal('#pf-res-cpf', this.gerarCPF());
            this.setVal('#pf-res-rg', this.gerarRG());
            this.setVal('#pf-res-email', `${nome.toLowerCase()}.${this.rand(this.db.sobrenomes).toLowerCase()}@email.com`);

            // Endereço
            const uf = document.getElementById('pf-uf').value;
            const cidade = document.getElementById('pf-cidade').value || (this.db.cidades[uf] ? this.rand(this.db.cidades[uf]) : 'Capital');
            
            this.gerarEndereco(uf, cidade, null).then(end => {
                this.setVal('#pf-res-logradouro', end.logradouro);
                this.setVal('#pf-res-numero', end.numero);
                this.setVal('#pf-res-bairro', end.bairro);
                this.setVal('#pf-res-cidade', end.cidade);
                this.setVal('#pf-res-uf', end.uf);
                this.setVal('#pf-res-cep', end.cep);
            });
        },

        gerarEmpresa(buscarCep = false) {
            // Dados Empresa
            const nome = this.rand(this.db.empresaPr) + ' ' + this.rand(this.db.sobrenomes) + ' ' + this.rand(this.db.empresaSf);
            this.setVal('#pj-res-nome', nome);
            this.setVal('#pj-res-cnpj', this.gerarCNPJ());
            this.setVal('#pj-res-ie', Math.floor(Math.random()*999999999));

            // Endereço
            let cepBusca = null;
            if (buscarCep) {
                const val = document.getElementById('pj-cep-busca').value.replace(/\D/g, '');
                if (val.length === 8) cepBusca = val;
            }

            const uf = document.getElementById('pj-uf').value;
            const cidade = document.getElementById('pj-cidade').value || 'Capital';

            this.gerarEndereco(uf, cidade, cepBusca).then(end => {
                this.setVal('#pj-res-logradouro', end.logradouro);
                this.setVal('#pj-res-numero', end.numero);
                this.setVal('#pj-res-bairro', end.bairro);
                this.setVal('#pj-res-cidade', end.cidade);
                this.setVal('#pj-res-uf', end.uf);
                this.setVal('#pj-res-cep', end.cep);
            });
        },

        // Core Address Logic
        async gerarEndereco(uf, cidade, cepEspecifico) {
            // 1. CEP Real
            if (cepEspecifico) {
               try {
                   const res = await fetch(`https://viacep.com.br/ws/${cepEspecifico}/json/`);
                   const data = await res.json();
                   if (!data.erro) {
                       return {
                           logradouro: data.logradouro,
                           numero: Math.floor(Math.random()*1000)+10,
                           bairro: data.bairro,
                           cidade: data.localidade,
                           uf: data.uf,
                           cep: data.cep
                       };
                   }
               } catch (e) {
                   console.error("Erro CEP", e);
               }
            }

            // 2. Mock Realista (Ruas Reais DB)
            if (this.db.ruasReais[cidade]) {
                const rua = this.rand(this.db.ruasReais[cidade]);
                return {
                    logradouro: rua.log,
                    numero: Math.floor(Math.random()*1000)+10,
                    bairro: rua.bai,
                    cidade: cidade,
                    uf: uf,
                    cep: rua.cep
                };
            }

            // 3. Generico
            return {
                logradouro: 'Rua Exemplo Fictício',
                numero: Math.floor(Math.random()*500)+1,
                bairro: 'Centro',
                cidade: cidade,
                uf: uf,
                cep: '00000-000'
            };
        },

        // Utils
        setVal(sel, val) { document.querySelector(sel).innerText = val; },
        gerarCPF() {
            const r = () => Math.floor(Math.random()*9);
            const n = Array.from({length:9}, r);
            let d1 = 11 - (n.reduce((a,v,i)=>a+v*(10-i),0) % 11); if(d1>9)d1=0;
            let d2 = 11 - ([...n,d1].reduce((a,v,i)=>a+v*(11-i),0) % 11); if(d2>9)d2=0;
            return `${n.slice(0,3).join('')}.${n.slice(3,6).join('')}.${n.slice(6,9).join('')}-${d1}${d2}`;
        },
        gerarCNPJ() {
            const r = () => Math.floor(Math.random()*9);
            const n = [...Array.from({length:8}, r), 0,0,0,1];
            let d1 = 11 - (n.reduce((a,v,i) => a + v * ([5,4,3,2,9,8,7,6,5,4,3,2][i]), 0) % 11); if(d1>9)d1=0;
            let d2 = 11 - ([...n,d1].reduce((a,v,i) => a + v * ([6,5,4,3,2,9,8,7,6,5,4,3,2][i]), 0) % 11); if(d2>9)d2=0;
            return `${n.slice(0,2).join('')}.${n.slice(2,5).join('')}.${n.slice(5,8).join('')}/${n.slice(8,12).join('')}-${d1}${d2}`;
        },
         gerarRG() {
            const r = () => Math.floor(Math.random()*9);
            return `${r()}${r()}.${r()}${r()}${r()}.${r()}${r()}${r()}-${r()}`;
        }
    };

    // UI Logic
    function switchTab(tab) {
        document.getElementById('tab-fisica').classList.add('hidden');
        document.getElementById('tab-juridica').classList.add('hidden');
        document.getElementById('tab-'+tab).classList.remove('hidden');
        
        document.getElementById('btn-fisica').className = tab==='fisica' ? 'px-6 py-2 rounded-lg text-sm font-bold transition-all bg-orange-600 text-white shadow-lg' : 'px-6 py-2 rounded-lg text-sm font-bold transition-all text-slate-400 hover:text-white';
        document.getElementById('btn-juridica').className = tab==='juridica' ? 'px-6 py-2 rounded-lg text-sm font-bold transition-all bg-blue-600 text-white shadow-lg' : 'px-6 py-2 rounded-lg text-sm font-bold transition-all text-slate-400 hover:text-white';
    }

    function copy(el, targetSel) {
        const text = document.querySelector(targetSel).innerText;
        navigator.clipboard.writeText(text);
        
        // Efeito Visual
        el.style.borderColor = '#4ade80';
        setTimeout(() => el.style.borderColor = '', 300);

        const toast = document.getElementById('toast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2000);
    }

    document.addEventListener('DOMContentLoaded', () => Gerador.init());
    
    // Mask CEP
    document.querySelectorAll('.mask-cep').forEach(input => {
        input.addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g,'');
            if(v.length>5) v = v.slice(0,5)+'-'+v.slice(5,8);
            e.target.value = v;
        });
    });
    </script>
</body>
</html>
