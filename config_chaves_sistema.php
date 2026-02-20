<?php
/**
 * Chaves reservadas do SGT Propostas
 * Mapeadas conforme a estrutura real do banco de dados (Usuarios e DadosEmpresa)
 */

return [
    // Dados da Empresa (tabela DadosEmpresa)
    'empresa_nome' => [
        'fonte' => 'banco',
        'tabela' => 'DadosEmpresa',
        'campo' => 'Empresa',
        'label' => 'Nome da Empresa',
        'obrigatorio' => true
    ],
    'empresa_cnpj' => [
        'fonte' => 'banco',
        'tabela' => 'DadosEmpresa',
        'campo' => 'CNPJ',
        'label' => 'CNPJ',
        'mascara' => 'cnpj'
    ],
    'empresa_endereco' => [
        'fonte' => 'banco',
        'tabela' => 'DadosEmpresa',
        'campo' => 'Endereco',
        'label' => 'Endereço Completo'
    ],
    'empresa_cidade' => [
        'fonte' => 'banco',
        'tabela' => 'DadosEmpresa',
        'campo' => 'Cidade',
        'label' => 'Cidade'
    ],
    'empresa_uf' => [
        'fonte' => 'banco',
        'tabela' => 'DadosEmpresa',
        'campo' => 'Estado',
        'label' => 'Estado (UF)'
    ],
    'empresa_telefone' => [
        'fonte' => 'banco',
        'tabela' => 'DadosEmpresa',
        'campo' => 'Telefone',
        'label' => 'Telefone Fixo'
    ],
    'empresa_whatsapp' => [
        'fonte' => 'banco',
        'tabela' => 'DadosEmpresa',
        'campo' => 'Whatsapp',
        'label' => 'WhatsApp'
    ],
    'logo_empresa' => [
        'fonte' => 'banco',
        'tabela' => 'DadosEmpresa',
        'campo' => 'Logo',
        'label' => 'Logo da Empresa (Caminho)',
        'tipo' => 'imagem'
    ],
    'logo' => [
        'fonte' => 'banco',
        'tabela' => 'DadosEmpresa',
        'campo' => 'Logo',
        'label' => 'Logo da Empresa (Alias)',
        'tipo' => 'imagem'
    ],
    'empresa_logo' => [
        'fonte' => 'banco',
        'tabela' => 'DadosEmpresa',
        'campo' => 'Logo',
        'label' => 'Caminho do Logo',
        'tipo' => 'imagem'
    ],
    
    // Dados do Usuário (tabela Usuarios)
    'usuario_nome' => [
        'fonte' => 'banco',
        'tabela' => 'Usuarios',
        'campo' => 'nome_completo',
        'label' => 'Nome do Profissional'
    ],
    'usuario_email' => [
        'fonte' => 'banco',
        'tabela' => 'Usuarios',
        'campo' => 'usuario',
        'label' => 'E-mail Profissional',
        'tipo' => 'email'
    ],

    // --- DADOS DA PROPOSTA / CLIENTE (Novas Chaves "Indexadas") ---
    'nome_cliente_salvo' => ['fonte' => 'manual', 'label' => 'Nome do Cliente'],
    'nome_cliente'       => ['fonte' => 'manual', 'label' => 'Nome do Cliente (Alias)'],
    'email_salvo'        => ['fonte' => 'manual', 'label' => 'E-mail do Cliente'],
    'telefone_salvo'     => ['fonte' => 'manual', 'label' => 'Telefone do Cliente'],
    'celular_salvo'      => ['fonte' => 'manual', 'label' => 'Celular do Cliente'],
    'whatsapp_salvo'     => ['fonte' => 'manual', 'label' => 'WhatsApp do Cliente'],
    
    // Obra/Projeto
    'tipo_levantamento'  => ['fonte' => 'manual', 'label' => 'Tipo de Levantamento'],
    'finalidade'         => ['fonte' => 'manual', 'label' => 'Finalidade do Serviço'],
    'endereco_obra'      => ['fonte' => 'manual', 'label' => 'Endereço da Obra'],
    'bairro_obra'        => ['fonte' => 'manual', 'label' => 'Bairro da Obra'],
    'cidade_obra'        => ['fonte' => 'manual', 'label' => 'Cidade da Obra'],
    'estado_obra'        => ['fonte' => 'manual', 'label' => 'Estado da Obra'],
    'area_obra'          => ['fonte' => 'manual', 'label' => 'Área da Obra'],
    'unidade_area'       => ['fonte' => 'manual', 'label' => 'Unidade (m²/ha)'],
    
    // Condições Técnicas
    'tipo_terreno'       => ['fonte' => 'manual', 'label' => 'Tipo de Terreno'],
    'cobertura_vegetal'  => ['fonte' => 'manual', 'label' => 'Cobertura Vegetal'],
    'acesso_local'       => ['fonte' => 'manual', 'label' => 'Acesso ao Local'],
    'restricoes_aereas'  => ['fonte' => 'manual', 'label' => 'Restrições Aéreas'],
    
    // Financeiro/Prazos
    'ValorProposta'      => ['fonte' => 'manual', 'label' => 'Valor Total (R$)'],
    'ValorExtenso'       => ['fonte' => 'manual', 'label' => 'Valor por Extenso'],
    'prazo_execucao'     => ['fonte' => 'manual', 'label' => 'Prazo de Execução'],
    'dias_campo'         => ['fonte' => 'manual', 'label' => 'Dias de Campo'],
    'dias_escritorio'    => ['fonte' => 'manual', 'label' => 'Dias de Escritório'],
    
    // Metadados do Sistema
    'data_hoje' => [
        'fonte' => 'sistema',
        'valor' => date('d/m/Y'),
        'label' => 'Data Atual'
    ],
    'DExrenso' => [
        'fonte' => 'sistema',
        'valor' => date('d/m/Y'), // Pode ser melhorado para extenso real no resolvedor
        'label' => 'Data por Extenso'
    ],
    'Cidade' => [
        'fonte' => 'banco',
        'tabela' => 'DadosEmpresa',
        'campo' => 'Cidade',
        'label' => 'Cidade Sede'
    ],
    'numero_proposta' => [
        'fonte' => 'auto',
        'label' => 'Nº da Proposta',
        'observacao' => 'Gerado automaticamente pelo sistema'
    ]
];
