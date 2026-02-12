<?php
// seed_real.php
// USE ESTE SCRIPT PARA INSERIR NÚMEROS REAIS PARA TESTE
declare(strict_types=1);
require 'conexao.php';
require 'validador_formato.php';

// EDITE AQUI SEUS NÚMEROS DE TESTE
$meusLeads = [
    [
        'nome' => 'Minha Empresa Teste',
        'site' => 'https://meusite.com.br',
        'whatsapp' => '5511999999999', // Coloque seu número real
        'ramo' => 'Teste Interno'
    ],
    // Adicione mais se quiser...
];

echo "Processando leads reais...\n";

foreach ($meusLeads as $lead) {
    // Valida
    $validado = ValidadorBR::validar($lead['whatsapp']);
    
    if (!$validado) {
        echo "[!] Número Inválido: {$lead['whatsapp']} - Ignorado.\n";
        continue;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO leads_prospeccao 
            (nome_empresa, site_origem, ramo_atuacao, whatsapp, metodo_captura, status_envio)
            VALUES (?, ?, ?, ?, 'wa_link', 'PENDENTE')
            ON DUPLICATE KEY UPDATE whatsapp = VALUES(whatsapp), status_envio = 'PENDENTE'
        ");
        
        $stmt->execute([
            $lead['nome'],
            $lead['site'],
            $lead['ramo'],
            $validado
        ]);
        
        echo "[+] Sucesso: {$lead['nome']} inserido/atualizado.\n";
        
    } catch (Exception $e) {
        echo "[x] Erro: " . $e->getMessage() . "\n";
    }
}
