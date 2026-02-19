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
    
    // Metadados do Sistema
    'data_hoje' => [
        'fonte' => 'sistema',
        'valor' => date('d/m/Y'),
        'label' => 'Data Atual'
    ],
    'numero_proposta' => [
        'fonte' => 'auto',
        'label' => 'Nº da Proposta',
        'observacao' => 'Gerado automaticamente pelo sistema'
    ]
];
