<?php
/**
 * MODELO: Proposta Drone v2
 * Topografia e Mapeamento Aéreo
 *
 * Utiliza a arquitetura SGT Template Engine v2.
 * Integrado com ResolvedorChavesSistema.php existente.
 */

require_once __DIR__ . '/../core/ModeloBase.php';

class PropostaDrone extends ModeloBase
{
    public function getNome(): string
    {
        return 'Proposta Drone - Topografia e Mapeamento Aéreo';
    }

    protected function definirBlocos(): array
    {
        return [
            // ── CABEÇALHO ────────────────────────────────────────────────────
            $this->titulo('Proposta de Serviços – Topografia e Mapeamento Aéreo', 1),
            $this->texto('Proposta Nº: ${numero_proposta}', 'destaque'),
            $this->texto('${Cidade}, ${DataExtenso}'),

            // ── DADOS DO CLIENTE ─────────────────────────────────────────────
            $this->titulo('Dados do Cliente'),
            $this->dados([
                'nome'      => '${nome_cliente_salvo}',
                'e-mail'    => '${email_salvo}',
                'telefone'  => '${telefone_salvo}',
                'celular'   => '${celular_salvo}',
                'whatsapp'  => '${whatsapp_salvo}'
            ]),

            // ── LOCAL DA OBRA ────────────────────────────────────────────────
            $this->titulo('Local da Obra'),
            $this->dados([
                'endereço'       => '${endereco_obra}',
                'bairro'         => '${bairro_obra}',
                'cidade/estado'  => '${cidade_obra} - ${estado_obra}',
                'área estimada'  => '${AreaEstimada}'
            ]),

            // ── SEÇÕES ───────────────────────────────────────────────────────
            $this->titulo('1. Apresentação'),
            $this->texto('A ${Empresa} apresenta esta proposta técnica visando a execução de levantamento topográfico planialtimétrico através de Aerofotogrametria com Drones (VANTs), utilizando equipamentos de última geração e profissionais capacitados.'),

            $this->titulo('2. Finalidade'),
            $this->texto('${finalidade}'),

            $this->titulo('3. Escopo do Serviço'),
            $this->texto('Execução de levantamento na área de ${AreaEstimada}, localizada em ${cidade_obra} - ${estado_obra}:'),
            $this->lista([
                'Tipo de Terreno: ${TipoTerreno}',
                'Cobertura Vegetal: ${CoberturaVegetal}',
                'Acesso: ${AcessoLocal}',
                'Restrições Aéreas: ${RestricoesAereas}'
            ]),

            $this->titulo('4. Metodologia'),
            $this->texto('Seguimos rigoroso fluxo de trabalho dividido em fases:'),

            $this->titulo('FASE 1: Planejamento', 3),
            $this->texto('Estudo da área via satélite, definição do plano de voo, configuração do equipamento e obtenção das autorizações necessárias (DECEA/ANAC).'),

            $this->titulo('FASE 2: Pontos de Controle', 3),
            $this->texto('Distribuição e materialização de alvos em campo, coleta de coordenadas com GPS de dupla frequência RTK/PPK para máxima acurácia.'),

            $this->titulo('FASE 3: Execução do Voo', 3),
            $this->texto('Captura de imagens com sobreposição longitudinal e lateral adequada, garantindo cobertura total da área e overlapping necessário para processamento fotogramétrico.'),

            $this->titulo('FASE 4: Processamento', 3),
            $this->texto('Geração de nuvem de pontos densa, ortomosaico georreferenciado, MDT e vetorização das curvas de nível em software especializado (Agisoft Metashape / Pix4D).'),

            $this->titulo('5. Equipamentos'),
            $this->lista([
                'Aeronave: ${Drone}',
                'GPS Geodésico: ${GPS} (RTK/PPK)',
                'Estação Total: ${Estacao_Total}',
                'Veículo de Apoio: ${Veiculo}'
            ]),

            $this->titulo('6. Produtos Entregues'),
            $this->lista([
                'Ortomosaico Georreferenciado (TIF/JPG)',
                'MDT – Modelo Digital do Terreno',
                'Curvas de Nível (DWG/DXF)',
                'Planta Topográfica (PDF + DWG)',
                'Relatório de Processamento Fotogramétrico',
                'ART – Anotação de Responsabilidade Técnica'
            ]),

            $this->titulo('7. Prazos'),
            $this->tabela([
                ['Etapa', 'Descrição', 'Prazo'],
                ['Mobilização', 'Planejamento e logística de campo', 'Até 02 dias'],
                ['Campo', 'Instalação de pontos e execução do voo', '01 dia'],
                ['Processamento', 'Geração dos modelos digitais', '03 a 05 dias'],
                ['Desenho CAD', 'Vetorização e entrega final', '03 a 05 dias'],
                ['TOTAL', 'Do aceite à entrega dos arquivos', '07 a 12 dias']
            ]),

            $this->titulo('8. Investimento'),
            $this->texto('R$ ${ValorProposta} (${ValorExtenso})', 'valor'),

            $this->titulo('9. Condições de Pagamento'),
            $this->dados([
                'mobilização'   => '${mobilizacao_percentual}% – R$ ${mobilizacao_valor} (entrada)',
                'entrega final' => '${restante_percentual}% – R$ ${restante_valor} (na entrega dos arquivos)'
            ]),

            $this->titulo('Dados Bancários'),
            $this->dados([
                'banco'    => '${Banco}',
                'agência'  => '${Agencia}',
                'conta'    => '${Conta}',
                'titular'  => '${Empresa} | CNPJ: ${CNPJ}',
                'pix'      => '${PIX}'
            ]),

            $this->titulo('10. Considerações Finais'),
            $this->texto('Esta proposta tem validade de 15 (quinze) dias corridos a partir da data de emissão. A ${Empresa} coloca-se à disposição para quaisquer esclarecimentos adicionais.'),

            // ── ASSINATURA ────────────────────────────────────────────────────
            $this->html("
                <div style='text-align:center;margin-top:3rem;'>
                    <p style='margin-bottom:3rem;'>Atenciosamente,</p>
                    <p style='border-top:1px solid var(--sgt-cinza-700);width:280px;display:inline-block;padding-top:0.5rem;'>
                        <strong>\${Empresa}</strong><br>
                        WhatsApp: \${whatsapp}
                    </p>
                </div>
            ")
        ];
    }
}
