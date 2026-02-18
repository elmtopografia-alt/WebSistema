<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposta Comercial - Drone (Piloto Completo)</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body { padding: 0 !important; background: white !important; }
            .container { max-width: 100% !important; box-shadow: none !important; margin: 0 !important; }
            .page-break { page-break-after: always; display: block; height: 1px; }
            .no-print { display: none !important; }
            .fase-box { border: 1px solid #ddd !important; }
        }
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .proposta-container { 
            background: white; 
            max-width: 210mm; /* A4 */
            margin: 40px auto; 
            padding: 3rem; 
            box-shadow: 0 0 25px rgba(0,0,0,0.08); 
            border-radius: 8px;
        }
        h1, h2, h3, h4, h5 { font-weight: 700; color: #2c3e50; }
        p, li { line-height: 1.6; color: #555; text-align: justify; }
        
        .titulo-secao { 
            border-bottom: 2px solid #fd7e14; /* Laranja da marca */
            padding-bottom: 0.5rem; 
            margin-top: 2.5rem; 
            margin-bottom: 1.5rem; 
            text-transform: uppercase;
            font-size: 1.15rem;
            color: #fd7e14;
            letter-spacing: 0.05em;
        }
        
        .fase-box {
            background: #f8f9fa;
            border-left: 4px solid #fd7e14;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
        }
        .fase-titulo { font-weight: bold; color: #fd7e14; margin-bottom: 0.5rem; text-transform: uppercase; font-size: 0.9rem; }
        
        .valor-total { font-size: 2.5rem; font-weight: 800; color: #fd7e14; }
        .assinatura-linha { border-top: 1px solid #333; width: 60%; margin: 10px auto; }
    </style>
</head>
<body>

    <div class="container proposta-container">
        
        <!-- CABEÇALHO -->
        <div class="row align-items-center mb-5 border-bottom pb-4">
            <div class="col-md-6">
                <h2 class="text-uppercase mb-0 font-weight-bold" style="color:#fd7e14;">{{ Empresa }}</h2>
                <small class="text-muted font-weight-bold">Topografia e Engenharia de Precisão</small>
            </div>
            <div class="col-md-6 text-right">
                <h4 class="mb-0 text-dark">PROPOSTA COMERCIAL</h4>
                <p class="mb-0 text-muted"><strong>Ref:</strong> {{ numero_proposta }}</p>
                <p class="mb-0 small">{{ Cidade }}, {{ DataExtenso }}</p>
            </div>
        </div>

        <!-- CLIENTE & OBRA -->
        <div class="row mb-5">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="card bg-light border-0 h-100 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-uppercase font-weight-bold mb-3" style="color:#fd7e14;">Dados do Cliente</h6>
                        <p class="mb-1 text-dark"><strong>{{ nome_cliente_salvo }}</strong></p>
                        <p class="mb-1">{{ email_salvo }}</p>
                        <p class="mb-0">{{ telefone_salvo }} / {{ celular_salvo }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                 <div class="card bg-light border-0 h-100 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-uppercase font-weight-bold mb-3" style="color:#fd7e14;">Local da Obra</h6>
                        <p class="mb-1">{{ endereco_obra }}</p>
                        <p class="mb-1">{{ bairro_obra }} - {{ cidade_obra }}/{{ estado_obra }}</p>
                        <p class="mb-0"><strong>Área Estimada:</strong> {{ area_obra }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. APRESENTAÇÃO -->
        <h3 class="titulo-secao">1. Apresentação e Entendimento</h3>
        <p>A <strong>{{ Empresa }}</strong> apresenta esta proposta técnica visando a execução de levantamento topográfico planialtimétrico através de <strong>Aerofotogrametria com Drones (VANTs)</strong>.</p>
        <p>Diferente de captação de imagens meramente ilustrativas, este serviço trata-se de <strong>Engenharia de Precisão</strong>. O objetivo é gerar uma representação digital fiel do terreno, com coordenadas exatas (Latitude, Longitude e Altitude), servindo de base legal e técnica para projetos de arquitetura, loteamentos, regularização fundiária e cálculos de volume.</p>

        <!-- 2. METODOLOGIA -->
        <h3 class="titulo-secao">2. Metodologia de Trabalho</h3>
        <p class="mb-4">Para garantir validade topográfica, seguimos um rigoroso fluxo dividido em etapas de campo e escritório:</p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="fase-box h-100">
                    <div class="fase-titulo">Fase 1: Planejamento (Escritório)</div>
                    <p class="small mb-0">Definição de altura de voo, GSD (resolução) e sobreposição de imagens para garantir estereoscopia 3D perfeita.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="fase-box h-100">
                    <div class="fase-titulo">Fase 2: Apoio Terrestre (Campo)</div>
                    <p class="small mb-0">Implantação de alvos no solo e coleta de coordenadas com <strong>GPS Geodésico RTK</strong> para amarração precisa.</p>
                </div>
            </div>
            <div class="col-md-6 mt-3">
                <div class="fase-box h-100">
                    <div class="fase-titulo">Fase 3: Voo (Campo)</div>
                    <p class="small mb-0">Execução do voo autônomo com captura de imagens nadir e oblíquas.</p>
                </div>
            </div>
            <div class="col-md-6 mt-3">
                <div class="fase-box h-100">
                    <div class="fase-titulo">Fase 4: Processamento (Workstation)</div>
                    <p class="small mb-0">Alinhamento, Nuvem de Pontos Densa, Georreferenciamento e Ortomosaico.</p>
                </div>
            </div>
            <div class="col-12 mt-3">
                <div class="fase-box">
                    <div class="fase-titulo">Fase 5: Vetorização (CAD)</div>
                    <p class="small mb-0">Desenho técnico final: Guias, edificações, árvores e geração das <strong>Curvas de Nível</strong>.</p>
                </div>
            </div>
        </div>

        <div class="page-break"></div>

        <!-- 3. PRODUTOS ENTREGUES -->
        <h3 class="titulo-secao">3. Produtos Entregues</h3>
        <ul class="list-group list-group-flush mb-4">
            <li class="list-group-item bg-transparent pl-0"><i class="text-warning mr-2">●</i> <strong>Ortomosaico Georreferenciado (TIF/JPG):</strong> Imagem de alta resolução em escala real.</li>
            <li class="list-group-item bg-transparent pl-0"><i class="text-warning mr-2">●</i> <strong>MDT (Modelo Digital de Terreno):</strong> Terreno 3D limpo (sem vegetação/prédios).</li>
            <li class="list-group-item bg-transparent pl-0"><i class="text-warning mr-2">●</i> <strong>Curvas de Nível (DWG):</strong> Topografia para AutoCAD (equidistância customizável).</li>
            <li class="list-group-item bg-transparent pl-0"><i class="text-warning mr-2">●</i> <strong>Planta Topográfica (PDF):</strong> Mapa finalizado com legendas e carimbo.</li>
            <li class="list-group-item bg-transparent pl-0"><i class="text-warning mr-2">●</i> <strong>ART:</strong> Anotação de Responsabilidade Técnica junto ao CREA.</li>
        </ul>

        <!-- 4. PRAZOS -->
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h3 class="titulo-secao text-center border-0 mb-3">4. Prazos Estimados</h3>
                <p class="text-muted small mb-4">Sujeito a condições climáticas favoráveis para voo.</p>
            </div>
            <div class="col-md-10">
                <table class="table table-bordered table-sm text-center shadow-sm">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col" class="w-25">Etapa</th>
                            <th scope="col">Descrição</th>
                            <th scope="col" class="w-25">Prazo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td class="align-middle font-weight-bold">Mobilização</td><td class="text-left font-weight-light">Planejamento e ida a campo</td><td class="align-middle">Até 2 dias</td></tr>
                        <tr><td class="align-middle font-weight-bold">Campo</td><td class="text-left font-weight-light">Voo e Pontos de Controle</td><td class="align-middle">1 dia</td></tr>
                        <tr><td class="align-middle font-weight-bold">Escritório</td><td class="text-left font-weight-light">Processamento e Desenho</td><td class="align-middle">3 a 5 dias</td></tr>
                        <tr class="table-warning font-weight-bold" style="color:#fd7e14;">
                            <td colspan="2" class="text-right pr-4">TOTAL ESTIMADO</td>
                            <td>7 a 12 dias úteis</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="page-break"></div>

        <!-- 5. INVESTIMENTO -->
        <h3 class="titulo-secao text-center border-0 mb-4">5. Investimento</h3>
        <div class="row justify-content-center mb-5">
            <div class="col-md-8 text-center">
                <div class="card border-warning mb-3 shadow-sm">
                    <div class="card-header bg-warning text-white font-weight-bold text-uppercase" style="letter-spacing: 1px;">Valor Total dos Serviços</div>
                    <div class="card-body py-4">
                        <h2 class="valor-total card-title mb-0">R$ {{ ValorProposta }}</h2>
                        <p class="card-text text-muted font-italic mt-2">({{ ValorExtenso }})</p>
                    </div>
                </div>
                <p class="text-muted small font-italic">Este investimento reflete o custo-benefício da tecnologia: maior riqueza de dados em menor tempo.</p>
            </div>
        </div>

        <!-- 6. PAGAMENTO E DADOS BANCÁRIOS -->
        <div class="row mb-5">
            <div class="col-md-6">
                <h6 class="font-weight-bold mb-3" style="color:#fd7e14;">CONDIÇÕES DE PAGAMENTO</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><strong>Sinal (Mobilização):</strong> {{ mobilizacao_percentual }}% - R$ {{ mobilizacao_valor }}</li>
                    <li><strong>Entrega Final:</strong> {{ restante_percentual }}% - R$ {{ restante_valor }}</li>
                </ul>
            </div>
            <div class="col-md-6 border-left pl-4">
                <h6 class="font-weight-bold mb-3" style="color:#fd7e14;">DADOS BANCÁRIOS</h6>
                <p class="mb-1"><strong>Banco:</strong> {{ Banco }}</p>
                <p class="mb-1"><strong>Agência:</strong> {{ Agencia }} | <strong>Conta:</strong> {{ Conta }}</p>
                <p class="mb-1"><strong>PIX:</strong> {{ PIX }}</p>
                <p class="mb-0"><strong>Favorecido:</strong> {{ Empresa }}</p>
            </div>
        </div>

        <!-- 7. EQUIPAMENTOS -->
        <h3 class="titulo-secao">7. Equipamentos Previstos</h3>
        <div class="row">
            <div class="col-md-6 mb-2">
                <div class="d-flex align-items-center bg-light p-2 rounded border">
                    <div class="mr-3 text-warning h4 mb-0">✈</div>
                    <div><strong>Drone:</strong> {{ Drone }}</div>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <div class="d-flex align-items-center bg-light p-2 rounded border">
                    <div class="mr-3 text-warning h4 mb-0">📡</div>
                    <div><strong>GPS RTK:</strong> {{ GPS }}</div>
                </div>
            </div>
        </div>

        <div class="page-break"></div>

        <!-- 8. CONSIDERAÇÕES -->
        <h3 class="titulo-secao">8. Considerações Finais</h3>
        <p>Esta proposta tem validade de 15 dias. A <strong>{{ Empresa }}</strong> coloca-se à disposição para sanar quaisquer dúvidas técnicas. Garantimos que o produto final entregue será uma ferramenta robusta para o desenvolvimento do seu projeto.</p>

        <!-- ASSINATURA -->
        <div class="row mt-5 mb-5 justify-content-center align-items-center">
            <div class="col-md-8 text-center">
                
                <p class="font-italic mb-5 text-secondary" style="font-size: 1.1rem;">Atenciosamente,</p>
                
                <!-- Espaço da Assinatura -->
                <div style="height: 80px;"></div>
                
                <!-- Linha -->
                <div class="assinatura-linha"></div>
                
                <!-- Dados -->
                <h5 class="font-weight-bold text-dark mb-1">{{ Empresa }}</h5>
                
                <?php if(!empty('{{ CNPJ }}')): ?>
                    <p class="mb-0 text-muted small">Topografia e Engenharia</p>
                    <p class="mb-0 text-muted small">CNPJ: {{ CNPJ }}</p>
                <?php endif; ?>
                
                <?php if(!empty('{{ whatsapp }}')): ?>
                    <p class="mb-0 text-muted small">{{ whatsapp }}</p>
                <?php endif; ?>
                
            </div>
        </div>

    </div> <!-- /container -->
</body>
</html>
