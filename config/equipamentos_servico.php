<?php
// CONFIGURAÇÃO DE EQUIPAMENTOS POR TIPO DE SERVIÇO
// Adicione novos serviços aqui conforme necessário

return [
    'drone_fotogrametria' => [
        'estacao_total' => 'Não inclusa (somente se necessário para áreas de sombra de GPS)',
        'gps' => 'Par de Receptores GNSS RTK de Dupla Frequência (coleta de GCPs com precisão centimétrica)',
        'drone' => 'Aeronave VANT com câmera de alta resolução e sistema RTK embutido',
        'veiculo' => 'Incluso (acesso ao local da obra)'
    ],
    
    'topografia_tradicional' => [
        'estacao_total' => 'Trimble S5 ou equivalente (precisão angular 5")',
        'gps' => 'Receptor GNSS monofrequência para apoio',
        'drone' => 'Não aplicável',
        'veiculo' => 'Incluso'
    ],
    
    'georreferenciamento' => [
        'estacao_total' => 'Não aplicável',
        'gps' => 'Par de Receptores GNSS RTK/PPK de Dupla Frequência',
        'drone' => 'Não aplicável',
        'veiculo' => 'Incluso'
    ],
    
    'levantamento_cadastral' => [
        'estacao_total' => 'Estação Total eletrônica',
        'gps' => 'Receptor GNSS para apoio geodésico',
        'drone' => 'Opcional (consultar viabilidade)',
        'veiculo' => 'Incluso'
    ]
];
