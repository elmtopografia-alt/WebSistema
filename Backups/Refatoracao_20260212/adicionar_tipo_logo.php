<?php

/**
 * Script para adicionar coluna tipo_logo na tabela DadosEmpresa
 * 
 * Tipos:
 * - 'completa' = Logo horizontal que já contém o título (mostrar só logo)
 * - 'simples' = Logo pequena/ícone (adicionar título "PROPOSTA TÉCNICA COMERCIAL")
 */

require_once 'db.php';

$conn = Database::getProd();

// Verifica se a coluna já existe
$result = $conn->query("SHOW COLUMNS FROM DadosEmpresa LIKE 'tipo_logo'");

if ($result->num_rows == 0) {
    // Adiciona a coluna
    $sql = "ALTER TABLE DadosEmpresa ADD COLUMN tipo_logo ENUM('completa', 'simples') DEFAULT 'simples' 
            COMMENT 'completa=logo horizontal com texto, simples=icone/logo pequena que precisa de titulo'";

    if ($conn->query($sql)) {
        echo "✅ Coluna 'tipo_logo' adicionada com sucesso!\n\n";
        echo "Valores possíveis:\n";
        echo "  • 'completa' - Para logos horizontais que já contêm o título da empresa\n";
        echo "  • 'simples' (padrão) - Para logos pequenas/ícones que precisam do título extra\n\n";
        echo "Acesse 'Minha Empresa' para configurar o tipo de logo.\n";
    } else {
        echo "❌ Erro ao adicionar coluna: " . $conn->error;
    }
} else {
    echo "ℹ️ Coluna 'tipo_logo' já existe na tabela DadosEmpresa.\n";
}

// Exemplo de como atualizar
echo "\n--- Para atualizar via SQL ---\n";
echo "UPDATE DadosEmpresa SET tipo_logo = 'completa' WHERE id_criador = SEU_ID;\n";
