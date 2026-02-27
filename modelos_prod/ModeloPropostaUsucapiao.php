<?php
/**
 * MODELO GERADO - SGT Template Engine v2
 * Fonte: PropostaUsucapiao.docx | Gerado em: 27/02/2026 09:40
 */

require_once __DIR__ . '/../core/ModeloBase.php';

class ModeloPropostaUsucapiao extends ModeloBase
{
    const COR_PADRAO = 'verde';

    public function getNome(): string
    {
        return 'PropostaUsucapiao';
    }

    protected function definirBlocos(): array
    {
        return array (
  0 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => 'Proposta de Serviços',
    'nivel' => 1
  ),
  1 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => 'Levantamento Topográfico para Usucapião',
    'nivel' => 1
  ),
  2 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Proposta Nº: ${numero_proposta}',
    'estilo' => 'normal'
  ),
  3 => 
  array (
    'tipo' => 'texto',
    'conteudo' => '${Cidade}, ${DExrenso}',
    'estilo' => 'normal'
  ),
  4 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => 'Dados do Cliente',
    'nivel' => 2
  ),
  5 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Nome: ${nome_cliente_salvo}',
    'estilo' => 'normal'
  ),
  6 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'E-mail: ${email_salvo}',
    'estilo' => 'normal'
  ),
  7 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Telefone: ${telefone_salvo} / ${celular_salvo}',
    'estilo' => 'normal'
  ),
  8 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'WhatsApp: ${whatsapp_salvo}',
    'estilo' => 'normal'
  ),
  9 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => 'Local da Obra',
    'nivel' => 2
  ),
  10 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Endereço: ${endereco_obra}',
    'estilo' => 'normal'
  ),
  11 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Bairro: ${bairro_obra}',
    'estilo' => 'normal'
  ),
  12 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Cidade: ${cidade_obra}',
    'estilo' => 'normal'
  ),
  13 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Estado: ${estado_obra}',
    'estilo' => 'normal'
  ),
  14 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => '1. Apresentação',
    'nivel' => 2
  ),
  15 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'A ${Empresa} é uma empresa prestadora de serviços técnicos especializada exclusivamente em Engenharia de Agrimensura e Topografia.',
    'estilo' => 'normal'
  ),
  16 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Nosso foco é a elaboração precisa das peças técnicas (plantas, memoriais e laudos) que servem como base física para o processo. Ressaltamos que nossa atuação limita-se estritamente à engenharia. Não somos um escritório de advocacia e não realizamos a regularização jurídica do imóvel.',
    'estilo' => 'normal'
  ),
  17 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => 'O Papel da Topografia no Usucapião',
    'nivel' => 3
  ),
  18 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Para que o cliente possa dar entrada em seu processo de usucapião (seja judicial ou extrajudicial) com seu advogado, é indispensável apresentar a "fotografia técnica" do imóvel.',
    'estilo' => 'normal'
  ),
  19 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Nosso trabalho é fornecer essa prova material: definimos com exatidão o perímetro, a área ocupada e os limites físicos. Entregamos os documentos de engenharia necessários para que o proprietário e seu corpo jurídico cuidem dos trâmites legais de regularização junto aos órgãos competentes.',
    'estilo' => 'normal'
  ),
  20 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => '2. Finalidade',
    'nivel' => 2
  ),
  21 => 
  array (
    'tipo' => 'texto',
    'conteudo' => '${finalidade}',
    'estilo' => 'normal'
  ),
  22 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Delimitar com precisão a área objeto da posse (conforme ocupação real constatada no campo).',
    'estilo' => 'normal'
  ),
  23 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Indicar confrontantes na planta e memorial, com base exclusivamente nas informações e documentos (Matrículas/Transcrições) fornecidos pelo Cliente.',
    'estilo' => 'normal'
  ),
  24 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Determinar perímetro, área e coordenadas georreferenciadas conforme normas técnicas (SIRGAS2000).',
    'estilo' => 'normal'
  ),
  25 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Produzir as peças técnicas (Planta e Memorial) que o cliente entregará ao seu advogado para instruir o processo.',
    'estilo' => 'normal'
  ),
  26 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Evitar inconsistências técnicas que possam atrapalhar o andamento do processo conduzido pelo cliente.',
    'estilo' => 'normal'
  ),
  27 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => '3. Escopo do Serviço',
    'nivel' => 2
  ),
  28 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'O serviço contratado refere-se única e exclusivamente ao Levantamento Topográfico para ${tipo_levantamento}, incluindo a emissão de ART (Anotação de Responsabilidade Técnica) junto ao CREA.',
    'estilo' => 'normal'
  ),
  29 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'O que ESTÁ incluído (Serviços de Engenharia):',
    'estilo' => 'normal'
  ),
  30 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Medição em campo.',
    'estilo' => 'normal'
  ),
  31 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Confecção de plantas e memoriais descritivos.',
    'estilo' => 'normal'
  ),
  32 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Transcrição dos dados dos confrontantes (vizinhos) para o desenho, baseada na documentação entregue pelo cliente.',
    'estilo' => 'normal'
  ),
  33 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Emissão de ART de serviço topográfico.',
    'estilo' => 'normal'
  ),
  34 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'O que NÃO ESTÁ incluído (Serviços Jurídicos/Despachante):',
    'estilo' => 'normal'
  ),
  35 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Não realizamos protocolização de processos em cartórios ou prefeituras.',
    'estilo' => 'normal'
  ),
  36 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Não prestamos assessoria jurídica ou advocatícia.',
    'estilo' => 'normal'
  ),
  37 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Não realizamos coleta de assinaturas de confrontantes.',
    'estilo' => 'normal'
  ),
  38 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Não solicitamos documentos diretamente aos vizinhos (a documentação dos confrontantes deve ser providenciada pelo cliente).',
    'estilo' => 'normal'
  ),
  39 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Não garantimos a titulação da propriedade, pois isso depende da análise jurídica e do deferimento do juiz ou oficial de registro.',
    'estilo' => 'normal'
  ),
  40 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => 'Normas Técnicas Adotadas:',
    'nivel' => 3
  ),
  41 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'ABNT NBR 13.133: Execução de Levantamento Topográfico.',
    'estilo' => 'normal'
  ),
  42 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Sistema de Referência: SIRGAS2000 UTM.',
    'estilo' => 'normal'
  ),
  43 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => '4. Metodologia',
    'nivel' => 2
  ),
  44 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Nossa metodologia de trabalho garante a precisão técnica necessária para que seus documentos de engenharia sejam aceitos sem ressalvas técnicas.',
    'estilo' => 'normal'
  ),
  45 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => '4.1. Metodologia Aplicada no Campo',
    'nivel' => 3
  ),
  46 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Os procedimentos de campo seguem etapas rigorosas:',
    'estilo' => 'normal'
  ),
  47 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Reconhecimento prévio da área: Verificação dos acessos e limites aparentes.',
    'estilo' => 'normal'
  ),
  48 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Implantação de marcos e referência: Utilização de tecnologia de medição (Estação Total/GNSS).',
    'estilo' => 'normal'
  ),
  49 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Levantamento dos vértices do perímetro ocupado: Pontos coletados observando muros, cercas e limites indicados pelo cliente.',
    'estilo' => 'normal'
  ),
  50 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Medição de benfeitorias: Cadastro de edificações e feições relevantes.',
    'estilo' => 'normal'
  ),
  51 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Registro fotográfico: Documentação visual da visita técnica.',
    'estilo' => 'normal'
  ),
  52 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => '4.2. Trabalhos de Escritório',
    'nivel' => 3
  ),
  53 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Após a coleta em campo, realizamos:',
    'estilo' => 'normal'
  ),
  54 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Processamento dos dados e cálculos geodésicos.',
    'estilo' => 'normal'
  ),
  55 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Identificação de Confrontantes: Inserção dos nomes e dados dos vizinhos na planta, conforme Matrículas ou informações fornecidas pelo cliente.',
    'estilo' => 'normal'
  ),
  56 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Geração da Planta Topográfica contendo as medidas de engenharia.',
    'estilo' => 'normal'
  ),
  57 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Elaboração do Memorial Descritivo (texto técnico com coordenadas e azimutes).',
    'estilo' => 'normal'
  ),
  58 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Organização do material técnico para entrega ao cliente.',
    'estilo' => 'normal'
  ),
  59 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => '5. Material Entregue',
    'nivel' => 2
  ),
  60 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Ao final da prestação do serviço topográfico, entregamos ao cliente:',
    'estilo' => 'normal'
  ),
  61 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Planta Topográfica, assinada por engenheiro habilitado e com ART.',
    'estilo' => 'normal'
  ),
  62 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Memorial Descritivo técnico com área, perímetro e coordenadas.',
    'estilo' => 'normal'
  ),
  63 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Arquivo digital em formatos PDF e DWG.',
    'estilo' => 'normal'
  ),
  64 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Relatório fotográfico técnico da área.',
    'estilo' => 'normal'
  ),
  65 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'ART (Anotação de Responsabilidade Técnica) quitada, referente ao serviço de medição.',
    'estilo' => 'normal'
  ),
  66 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => '6. Equipamentos Previstos',
    'nivel' => 2
  ),
  67 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Para a execução da medição, utilizaremos equipamentos calibrados:',
    'estilo' => 'normal'
  ),
  68 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Veículo: ${Veiculo}',
    'estilo' => 'normal'
  ),
  69 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Estação Total: ${Estacao_Total}',
    'estilo' => 'normal'
  ),
  70 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Receptores GNSS: ${GPS}',
    'estilo' => 'normal'
  ),
  71 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Drone (se aplicável): ${Drone}',
    'estilo' => 'normal'
  ),
  72 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => '7. Investimento',
    'nivel' => 2
  ),
  73 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'O valor total para execução dos serviços topográficos descritos é de: ${ValorProposta} (${ValorExtenso})',
    'estilo' => 'normal'
  ),
  74 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Este investimento refere-se à elaboração da documentação técnica de engenharia. Com este material em mãos, o cliente terá a base técnica necessária para contratar seu advogado e buscar a regularização do imóvel junto aos órgãos competentes.',
    'estilo' => 'normal'
  ),
  75 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => '8. Condições de Pagamento',
    'nivel' => 2
  ),
  76 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Mobilização: ${mobilizacao_percentual}% – ${mobilizacao_valor} (Pagamento no aceite da proposta para início de campo).',
    'estilo' => 'normal'
  ),
  77 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Restante: ${restante_percentual}% – ${restante_valor} (Pagamento na entrega dos documentos técnicos).',
    'estilo' => 'normal'
  ),
  78 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => '9. Dados Bancários',
    'nivel' => 2
  ),
  79 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Banco: ${Banco}',
    'estilo' => 'normal'
  ),
  80 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Agência: ${Agencia}',
    'estilo' => 'normal'
  ),
  81 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Conta Corrente: ${Conta}',
    'estilo' => 'normal'
  ),
  82 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Titular: ${Empresa}',
    'estilo' => 'normal'
  ),
  83 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => 'CNPJ: ${CNPJ}',
    'nivel' => 1
  ),
  84 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Chave PIX: ${PIX}',
    'estilo' => 'normal'
  ),
  85 => 
  array (
    'tipo' => 'titulo',
    'conteudo' => '10. Considerações Finais',
    'nivel' => 2
  ),
  86 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Agradecemos a oportunidade de apresentar nossa proposta de serviços técnicos.',
    'estilo' => 'normal'
  ),
  87 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Reforçamos que a ${Empresa} atua exclusivamente na esfera da engenharia, fornecendo a precisão métrica necessária. Estamos à disposição para entregar um trabalho topográfico de excelência, deixando a documentação técnica pronta para que o senhor(a) possa dar andamento aos trâmites legais de regularização sob sua gestão.',
    'estilo' => 'normal'
  ),
  88 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Atenciosamente,',
    'estilo' => 'normal'
  ),
  89 => 
  array (
    'tipo' => 'texto',
    'conteudo' => '____________________________________',
    'estilo' => 'normal'
  ),
  90 => 
  array (
    'tipo' => 'texto',
    'conteudo' => '${Empresa}',
    'estilo' => 'normal'
  ),
  91 => 
  array (
    'tipo' => 'texto',
    'conteudo' => 'Contato: ${whatsapp}',
    'estilo' => 'normal'
  )
);
    }
}