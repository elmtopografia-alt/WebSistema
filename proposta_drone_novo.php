<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposta <?= $vars['numero_proposta'] ?? '' ?> - <?= $vars['nome_cliente_salvo'] ?? '' ?></title>
    
    <!-- CSS INLINE (GARANTIA DE VISUALIZAÇÃO) -->
    <style>
/* ============================================
   PROPOSTA DRONE - CSS COMPLETO
   SGT Propostas - Levantamento Topográfico
   ============================================ */

/* -------------------------------------------
   1. VARIÁVEIS GLOBAIS
   ------------------------------------------- */
:root {
    /* Cores Primárias */
    --cor-primaria: #2563eb;
    --cor-primaria-hover: #1d4ed8;
    --cor-primaria-light: #dbeafe;

    /* Cores de Status */
    --cor-sucesso: #10b981;
    --cor-sucesso-light: #d1fae5;
    --cor-alerta: #f59e0b;
    --cor-alerta-light: #fef3c7;
    --cor-erro: #ef4444;
    --cor-erro-light: #fee2e2;

    /* Cores Neutras */
    --cor-fundo: #f8fafc;
    --cor-branco: #ffffff;
    --cor-borda: #e2e8f0;
    --cor-texto: #1e293b;
    --cor-texto-secundario: #64748b;

    /* Sombras */
    --sombra-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --sombra-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --sombra-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);

    /* Espaçamento */
    --espaco-xs: 0.5rem;
    --espaco-sm: 0.75rem;
    --espaco-md: 1rem;
    --espaco-lg: 1.5rem;
    --espaco-xl: 2rem;

    /* Bordas */
    --raio-sm: 0.375rem;
    --raio-md: 0.5rem;
    --raio-lg: 0.75rem;

    /* Transições */
    --transicao: all 0.2s ease-in-out;
}

/* -------------------------------------------
   2. RESET E BASE
   ------------------------------------------- */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    background-color: var(--cor-fundo);
    color: var(--cor-texto);
    line-height: 1.6;
}

/* -------------------------------------------
   3. TOPO INSTITUCIONAL
   ------------------------------------------- */
.topo-institucional {
    background: linear-gradient(135deg, var(--cor-branco) 0%, var(--cor-fundo) 100%);
    border-bottom: 3px solid var(--cor-primaria);
    padding: var(--espaco-xl) 0;
    margin-bottom: var(--espaco-xl);
    box-shadow: var(--sombra-md);
}

.topo-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--espaco-md);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--espaco-xl);
}

/* Área da Logo */
.logo-area {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: var(--espaco-md);
}

.logo-placeholder {
    width: 120px;
    height: 80px;
    background: var(--cor-primaria-light);
    border: 2px dashed var(--cor-primaria);
    border-radius: var(--raio-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--cor-primaria);
    font-size: 0.75rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.logo-placeholder::before {
    content: "LOGO";
    font-weight: 700;
    letter-spacing: 0.1em;
}

.logo-img {
    max-width: 150px;
    max-height: 90px;
    object-fit: contain;
}

/* Área do Título */
.titulo-area {
    flex: 1;
    text-align: right;
}

.titulo-principal {
    font-size: 2rem;
    font-weight: 800;
    color: var(--cor-primaria);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: var(--espaco-xs);
    line-height: 1.2;
}

.titulo-sub {
    font-size: 1.125rem;
    color: var(--cor-texto-secundario);
    font-weight: 500;
}

.titulo-linha {
    width: 100px;
    height: 4px;
    background: linear-gradient(90deg, var(--cor-primaria) 0%, var(--cor-sucesso) 100%);
    margin-left: auto;
    margin-top: var(--espaco-sm);
    border-radius: 2px;
}

/* -------------------------------------------
   4. CONTAINER PRINCIPAL
   ------------------------------------------- */
.container-proposta {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--espaco-md) var(--espaco-xl);
}

/* -------------------------------------------
   5. PROPOSTA HEADER
   ------------------------------------------- */
.proposta-header {
    background: var(--cor-branco);
    border-radius: var(--raio-lg);
    padding: var(--espaco-xl);
    margin-bottom: var(--espaco-lg);
    box-shadow: var(--sombra-md);
    border-left: 4px solid var(--cor-primaria);
}

.proposta-titulo {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--cor-texto);
    margin-bottom: var(--espaco-sm);
    display: flex;
    align-items: center;
    gap: var(--espaco-sm);
}

.proposta-titulo::before {
    content: "🚁";
    font-size: 1.75rem;
}

.proposta-meta {
    display: flex;
    gap: var(--espaco-lg);
    color: var(--cor-texto-secundario);
    font-size: 0.875rem;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: var(--espaco-xs);
}

/* -------------------------------------------
   6. SEÇÕES
   ------------------------------------------- */
.secao-container {
    background: var(--cor-branco);
    border-radius: var(--raio-lg);
    margin-bottom: var(--espaco-lg);
    box-shadow: var(--sombra-sm);
    border: 1px solid var(--cor-borda);
    overflow: hidden;
    transition: var(--transicao);
}

.secao-container:hover {
    box-shadow: var(--sombra-md);
}

.secao-header {
    background: linear-gradient(135deg, var(--cor-primaria-light) 0%, var(--cor-branco) 100%);
    padding: var(--espaco-md) var(--espaco-lg);
    border-bottom: 1px solid var(--cor-borda);
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    user-select: none;
}

.secao-header:hover {
    background: linear-gradient(135deg, var(--cor-primaria-light) 0%, #f1f5f9 100%);
}

.secao-titulo {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--cor-primaria);
    display: flex;
    align-items: center;
    gap: var(--espaco-sm);
}

/* Ícones por seção */
.secao-titulo.dados-cliente::before {
    content: "👤";
}

.secao-titulo.dados-imovel::before {
    content: "🏞️";
}

.secao-titulo.escopo-servico::before {
    content: "📋";
}

.secao-titulo.equipamentos::before {
    content: "🛸";
}

.secao-titulo.cronograma::before {
    content: "📅";
}

.secao-titulo.investimento::before {
    content: "💰";
}

.secao-titulo.termos::before {
    content: "⚖️";
}

.secao-badge {
    background: var(--cor-primaria);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.secao-body {
    padding: var(--espaco-lg);
}

/* -------------------------------------------
   7. FORMULÁRIOS E CAMPOS
   ------------------------------------------- */
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--espaco-md);
}

.campo-grupo {
    display: flex;
    flex-direction: column;
    gap: var(--espaco-xs);
}

.campo-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--cor-texto-secundario);
}

.campo-input {
    padding: var(--espaco-sm) var(--espaco-md);
    border: 1px solid var(--cor-borda);
    border-radius: var(--raio-md);
    font-size: 0.875rem;
    transition: var(--transicao);
    background: var(--cor-fundo);
    width: 100%;
}

.campo-input:focus {
    outline: none;
    border-color: var(--cor-primaria);
    box-shadow: 0 0 0 3px var(--cor-primaria-light);
}

select.campo-input {
    cursor: pointer;
}

/* -------------------------------------------
   8. COMPONENTES ESPECÍFICOS
   ------------------------------------------- */

/* Cards de Equipamento */
.equipamento-card {
    border: 2px solid var(--cor-borda);
    border-radius: var(--raio-md);
    padding: var(--espaco-md);
    display: flex;
    align-items: center;
    gap: var(--espaco-md);
    cursor: pointer;
    transition: var(--transicao);
}

.equipamento-card:hover {
    border-color: var(--cor-primaria);
    background: var(--cor-primaria-light);
}

.equipamento-card.selecionado {
    border-color: var(--cor-primaria);
    background: var(--cor-primaria-light);
    position: relative;
}

.equipamento-card.selecionado::after {
    content: "✓";
    position: absolute;
    top: -8px;
    right: -8px;
    background: var(--cor-primaria);
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
}

/* Timeline do Cronograma */
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--cor-borda);
}

.timeline-item {
    position: relative;
    padding-bottom: var(--espaco-lg);
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -1.75rem;
    top: 0.25rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--cor-primaria);
    border: 3px solid var(--cor-branco);
    box-shadow: var(--sombra-sm);
}

/* Tabela de Investimento */
.tabela-investimento {
    width: 100%;
    border-collapse: collapse;
    margin-top: var(--espaco-md);
}

.tabela-investimento th,
.tabela-investimento td {
    padding: var(--espaco-sm) var(--espaco-md);
    text-align: left;
    border-bottom: 1px solid var(--cor-borda);
}

.tabela-investimento th {
    background: var(--cor-fundo);
    font-weight: 600;
    color: var(--cor-texto-secundario);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.tabela-investimento tr:hover {
    background: var(--cor-fundo);
}

.tabela-investimento td:nth-child(5),
.tabela-investimento th:nth-child(5) {
    text-align: right;
}

.valor-total {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--cor-primaria);
    text-align: right;
    margin-top: var(--espaco-md);
    padding-top: var(--espaco-md);
    border-top: 2px solid var(--cor-primaria);
}

/* -------------------------------------------
   9. AÇÕES (STICKY FOOTER)
   ------------------------------------------- */
.acoes-container {
    position: sticky;
    bottom: 0;
    background: var(--cor-branco);
    padding: var(--espaco-md) var(--espaco-lg);
    border-top: 1px solid var(--cor-borda);
    box-shadow: 0 -4px 6px -1px rgb(0 0 0 / 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 0 calc(-1 * var(--espaco-md));
    margin-top: var(--espaco-xl);
    z-index: 100;
}

/* Botões */
.btn {
    padding: var(--espaco-sm) var(--espaco-lg);
    border-radius: var(--raio-md);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transicao);
    border: none;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: var(--espaco-xs);
    text-decoration: none;
}

.btn-primario {
    background: var(--cor-primaria);
    color: white;
}

.btn-primario:hover {
    background: var(--cor-primaria-hover);
    transform: translateY(-1px);
    box-shadow: var(--sombra-md);
}

.btn-secundario {
    background: var(--cor-branco);
    color: var(--cor-texto);
    border: 1px solid var(--cor-borda);
}

.btn-secundario:hover {
    background: var(--cor-fundo);
}

/* -------------------------------------------
   10. UTILITÁRIOS
   ------------------------------------------- */
.hidden {
    display: none !important;
}

.fade-in {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* -------------------------------------------
   11. RESPONSIVIDADE
   ------------------------------------------- */
@media (max-width: 768px) {
    .topo-container {
        flex-direction: column;
        text-align: center;
        gap: var(--espaco-md);
    }

    .titulo-area {
        text-align: center;
    }

    .titulo-linha {
        margin: var(--espaco-sm) auto 0;
    }

    .titulo-principal {
        font-size: 1.5rem;
    }

    .container-proposta {
        margin: 1rem auto;
    }

    .proposta-header {
        padding: var(--espaco-md);
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .acoes-container {
        flex-direction: column;
        gap: var(--espaco-md);
    }

    .proposta-meta {
        flex-direction: column;
        gap: var(--espaco-sm);
    }
}

/* Ajuste para telas muito pequenas */
@media (max-width: 480px) {
    .secao-header {
        flex-direction: column;
        gap: var(--espaco-sm);
        text-align: center;
    }

    .equipamento-card {
        flex-direction: column;
        text-align: center;
    }
}
    </style>
        @media print {
            .topo-institucional, .acoes-container, .secao-header span.secao-badge { display: none !important; }
            .secao-body { display: block !important; padding: 0 !important; }
            .secao-container { border: none !important; box-shadow: none !important; margin-bottom: 20px !important; }
            .proposta-header { border: 1px solid #ddd !important; }
            .campo-input { border: none !important; background: none !important; padding: 0 !important; font-weight: bold; }
            body { font-size: 11pt; }
            textarea { resize: none; overflow: hidden; height: auto; }
        }
        /* Ajuste para transformar inputs em texto de exibição na visualização */
        .campo-valor-static {
            font-weight: 600;
            color: var(--cor-texto);
            padding: var(--espaco-sm) 0;
            border-bottom: 1px dashed var(--cor-borda);
            min-height: 2.5rem;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>

    <!-- ============================================
         TOPO INSTITUCIONAL
         ============================================ -->
    <header class="topo-institucional">
        <div class="topo-container">
            
            <!-- LOGO -->
            <div class="logo-area">
                <?php if (!empty($logo) && file_exists($logo)): ?>
                    <img src="<?= $logo ?>" class="logo-img" alt="<?= $vars['Empresa'] ?? 'Empresa' ?>">
                <?php else: ?>
                    <div class="logo-placeholder" title="<?= $vars['Empresa'] ?? '' ?>"></div>
                <?php endif; ?>
            </div>

            <!-- TÍTULO -->
            <div class="titulo-area">
                <h1 class="titulo-principal">Proposta Técnica Comercial</h1>
                <div class="titulo-sub">Levantamento Topográfico com Drone</div>
                <div class="titulo-linha"></div>
            </div>

        </div>
    </header>

    <!-- ============================================
         CONTAINER PRINCIPAL
         ============================================ -->
    <div class="container-proposta">
        
        <!-- PROPOSTA HEADER -->
        <div class="proposta-header">
            <h2 class="proposta-titulo">Levantamento Aerofotogramétrico</h2>
            <div class="proposta-meta">
                <span class="meta-item">📄 Nº <strong><?= $vars['numero_proposta'] ?? '' ?></strong></span>
                <span class="meta-item">📅 Emissão: <strong><?= $vars['DataExtenso'] ?? '' ?></strong></span>
                <span class="meta-item">⏱️ Validade: <strong>15 dias</strong></span>
                <span class="meta-item">👤 Consultor: <strong><?= $vars['Empresa'] ?? '' ?></strong></span>
            </div>
        </div>

        <!-- SEÇÃO 1: DADOS DO CLIENTE -->
        <div class="secao-container" id="secao-cliente">
            <div class="secao-header" onclick="toggleSecao('cliente')">
                <h3 class="secao-titulo dados-cliente">Dados do Cliente</h3>
                <span class="secao-badge">Cliente</span>
            </div>
            <div class="secao-body" id="body-cliente">
                <div class="form-grid">
                    <div class="campo-grupo">
                        <label class="campo-label">Cliente / Razão Social</label>
                        <div class="campo-valor-static"><?= $vars['nome_cliente_salvo'] ?? '' ?></div>
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">E-mail</label>
                        <div class="campo-valor-static"><?= $vars['email_salvo'] ?? '' ?></div>
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Telefone / Contato</label>
                        <div class="campo-valor-static"><?= $vars['telefone_salvo'] ?? '' ?> <?= $vars['celular_salvo'] ?? '' ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEÇÃO 2: DADOS DO IMÓVEL -->
        <div class="secao-container" id="secao-imovel">
            <div class="secao-header" onclick="toggleSecao('imovel')">
                <h3 class="secao-titulo dados-imovel">Dados do Imóvel / Área</h3>
                <span class="secao-badge">Local</span>
            </div>
            <div class="secao-body" id="body-imovel">
                <div class="form-grid">
                    <div class="campo-grupo">
                        <label class="campo-label">Endereço Completo</label>
                        <div class="campo-valor-static"><?= $vars['endereco_obra'] ?? '' ?></div>
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Localização</label>
                        <div class="campo-valor-static"><?= $vars['bairro_obra'] ?? '' ?> - <?= $vars['cidade_obra'] ?? '' ?>/<?= $vars['estado_obra'] ?? '' ?></div>
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Área Aproximada</label>
                        <div class="campo-valor-static"><?= $vars['area_obra'] ?? '' ?> <?= $vars['unidade_area'] ?? 'm²' ?></div>
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Tipo de Terreno / Acesso</label>
                        <div class="campo-valor-static"><?= $vars['TipoTerreno'] ?? '' ?> / <?= $vars['AcessoLocal'] ?? '' ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEÇÃO 3: ESCOPO DO SERVIÇO -->
        <div class="secao-container" id="secao-escopo">
            <div class="secao-header" onclick="toggleSecao('escopo')">
                <h3 class="secao-titulo escopo-servico">Escopo do Serviço</h3>
                <span class="secao-badge">Detalhes</span>
            </div>
            <div class="secao-body" id="body-escopo">
                <p>Execução de levantamento topográfico planialtimétrico através de Aerofotogrametria com Drones (VANTs), visando gerar representação digital fiel do terreno com coordenadas exatas.</p>
                <br>
                <div class="form-grid">
                    <div class="campo-grupo">
                        <label class="campo-label">Tipo de Levantamento / Finalidade</label>
                        <div class="campo-valor-static"><?= $vars['finalidade'] ?? '' ?></div>
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Entregáveis Principais</label>
                        <div class="campo-valor-static">Ortomosaico, MDT, Curvas de Nível e Planta PDF</div>
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Descrição Detalhada</label>
                        <div class="campo-valor-static" style="height: auto; display: block;"><?= $vars['escopo_servico'] ?? '' ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEÇÃO 4: EQUIPAMENTOS -->
        <div class="secao-container" id="secao-equipamentos">
            <div class="secao-header" onclick="toggleSecao('equipamentos')">
                <h3 class="secao-titulo equipamentos">Equipamentos Utilizados</h3>
                <span class="secao-badge">Tecnologia</span>
            </div>
            <div class="secao-body" id="body-equipamentos">
                <div class="form-grid">
                    
                    <?php if(!empty($vars['Drone'])): ?>
                    <div class="equipamento-card selecionado" onclick="toggleEquipamento(this)">
                        <div style="font-size: 2rem;">🛸</div>
                        <div>
                            <strong>Drone / VANT</strong>
                            <div style="font-size: 0.875rem; color: var(--cor-texto-secundario);">
                                <?= $vars['Drone'] ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if(!empty($vars['GPS'])): ?>
                    <div class="equipamento-card selecionado" onclick="toggleEquipamento(this)">
                        <div style="font-size: 2rem;">📡</div>
                        <div>
                            <strong>Receptor GNSS RTK</strong>
                            <div style="font-size: 0.875rem; color: var(--cor-texto-secundario);">
                                <?= $vars['GPS'] ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if(!empty($vars['Estacao_Total'])): ?>
                    <div class="equipamento-card selecionado" onclick="toggleEquipamento(this)">
                        <div style="font-size: 2rem;">🔭</div>
                        <div>
                            <strong>Estação Total</strong>
                            <div style="font-size: 0.875rem; color: var(--cor-texto-secundario);">
                                <?= $vars['Estacao_Total'] ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="equipamento-card selecionado">
                        <div style="font-size: 2rem;">💻</div>
                        <div>
                            <strong>Softwares</strong>
                            <div style="font-size: 0.875rem; color: var(--cor-texto-secundario);">
                                Processamento e Vetorização CAD/GIS
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- SEÇÃO 5: CRONOGRAMA -->
        <div class="secao-container" id="secao-cronograma">
            <div class="secao-header" onclick="toggleSecao('cronograma')">
                <h3 class="secao-titulo cronograma">Cronograma de Execução</h3>
                <span class="secao-badge"><?= $vars['prazo_execucao'] ?? '' ?></span>
            </div>
            <div class="secao-body" id="body-cronograma">
                <div class="timeline">
                    <div class="timeline-item">
                        <strong>1. Planejamento e Mobilização</strong>
                        <div style="color: var(--cor-texto-secundario); font-size: 0.875rem;">
                            Planejamento de voo, análise DECEA/ANAC e deslocamento até a área.
                        </div>
                    </div>
                    <div class="timeline-item">
                        <strong>2. Execução de Campo (<?= $vars['dias_campo'] ?? '0' ?> dias)</strong>
                        <div style="color: var(--cor-texto-secundario); font-size: 0.875rem;">
                            Implantação de pontos de controle (GCPs) e voo aerofotogramétrico.
                        </div>
                    </div>
                    <div class="timeline-item">
                        <strong>3. Processamento e Escritório (<?= $vars['dias_escritorio'] ?? '0' ?> dias)</strong>
                        <div style="color: var(--cor-texto-secundario); font-size: 0.875rem;">
                            Geração da nuvem de pontos, ortomosaico e desenho técnico (CAD).
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEÇÃO 6: INVESTIMENTO -->
        <div class="secao-container" id="secao-investimento">
            <div class="secao-header" onclick="toggleSecao('investimento')">
                <h3 class="secao-titulo investimento">Investimento</h3>
                <span class="secao-badge">Total</span>
            </div>
            <div class="secao-body" id="body-investimento">
                <table class="tabela-investimento">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Serviços de Topografia e Mapeamento Aéreo conforme escopo</td>
                            <td style="text-align: right;"><?= $vars['ValorProposta'] ?? 'R$ 0,00' ?></td>
                        </tr>
                    </tbody>
                </table>
                <div class="valor-total">
                    TOTAL: <?= $vars['ValorProposta'] ?? 'R$ 0,00' ?>
                    <div style="font-size:0.9rem; font-weight:normal; color:#666; margin-top:5px;">(<?= $vars['ValorExtenso'] ?? '' ?>)</div>
                </div>
            </div>
        </div>

        <!-- SEÇÃO 7: TERMOS -->
        <div class="secao-container" id="secao-termos">
            <div class="secao-header" onclick="toggleSecao('termos')">
                <h3 class="secao-titulo termos">Termos e Condições</h3>
                <span class="secao-badge">Pagamento</span>
            </div>
            <div class="secao-body" id="body-termos">
                <div class="form-grid">
                    <div class="campo-grupo">
                        <label class="campo-label">Condições de Pagamento</label>
                        <div class="campo-valor-static">
                            Entrada: <?= $vars['mobilizacao_percentual'] ?? '0' ?>% (<?= $vars['mobilizacao_valor'] ?? 'R$ 0,00' ?>)<br>
                            Final: <?= $vars['restante_percentual'] ?? '0' ?>% (<?= $vars['restante_valor'] ?? 'R$ 0,00' ?>)
                        </div>
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label">Prazo de Execução</label>
                        <div class="campo-valor-static"><?= $vars['prazo_execucao'] ?? '' ?></div>
                    </div>
                </div>
                <div style="margin-top: var(--espaco-md); padding: var(--espaco-md); background: var(--cor-fundo); border-radius: var(--raio-md); font-size: 0.875rem; color: var(--cor-texto-secundario);">
                    <strong>Dados Bancários:</strong><br>
                    Banco: <?= $vars['Banco'] ?? '' ?> | Ag: <?= $vars['Agencia'] ?? '' ?> | Conta: <?= $vars['Conta'] ?? '' ?><br>
                    PIX: <?= $vars['PIX'] ?? '' ?><br>
                    Favorecido: <?= $vars['Empresa'] ?? '' ?><br><br>
                    <strong>Observações:</strong><br>
                    • Preço válido por 15 dias<br>
                    • Sujeito a condições meteorológicas para voo
                </div>
            </div>
        </div>

        <!-- AÇÕES -->
        <div class="acoes-container">
            <button type="button" class="btn btn-secundario" onclick="window.print()">
                🖨️ Imprimir / Salvar PDF
            </button>
            <button type="button" class="btn btn-primario" onclick="alert('Esta proposta já está salva.')">
                ✅ Proposta Emitida
            </button>
        </div>

    </div>

    <!-- ============================================
         JAVASCRIPT
         ============================================ -->
    <script>
        // Toggle seções (colapsar/expandir)
        function toggleSecao(id) {
            const body = document.getElementById(`body-${id}`);
            if(body) body.classList.toggle('hidden');
        }

        // Toggle seleção de equipamento
        function toggleEquipamento(element) {
            // Em modo visualização, não faz nada ou apenas efeito visual
            // element.classList.toggle('selecionado');
        }
    </script>

</body>
</html>
