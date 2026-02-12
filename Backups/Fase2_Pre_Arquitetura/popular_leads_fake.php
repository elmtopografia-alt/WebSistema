<?php
// popular_leads_fake.php
declare(strict_types=1);
require 'conexao.php';
require 'validador_formato.php';

$ramos = ['Topografia','Agrimensura','Drone','Engenharia'];
$estados = ['SP','PR','MG','RJ', 'SC', 'RS'];
// DDDs correspondentes aos estados acima (simplificado)
$ddds = [
    'SP' => ['11','12','19'],
    'PR' => ['41','42','43'],
    'MG' => ['31','32','35'],
    'RJ' => ['21','22','24'],
    'SC' => ['47','48','49'],
    'RS' => ['51','53','54']
];

echo "Iniciando seed com números BR válidos...\n";

for($i=0; $i<20; $i++) {
    $estado = $estados[array_rand($estados)];
    $ddd = $ddds[$estado][array_rand($ddds[$estado])];
    
    $nome = $ramos[array_rand($ramos)].' '.chr(65+$i);
    $site = 'https://exemplo'.strtolower(str_replace(' ','',$nome)).'.com.br';
    
    // Gera número compatível com regra BR (DDD + 9 + 8 dígitos)
    // 55 + DDD + 9 + XXXX-XXXX
    $numero = '55' . $ddd . '9' . rand(7000, 9999) . rand(1000, 9999);
    
    // Validação extra (redundante aqui, mas boa prática)
    if (!ValidadorBR::validar($numero)) continue;
    
    try {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO leads_prospeccao 
            (nome_empresa, site_origem, ramo_atuacao, whatsapp, metodo_captura)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $nome,
            $site,
            $ramos[array_rand($ramos)].' ('.$estado.')',
            $numero,
            ['wa_link','public_form'][rand(0,1)]
        ]);
        echo ".";
    } catch (Exception $e) {
        echo "x";
    }
}

echo "\nOK: 20 leads processados.\n";
