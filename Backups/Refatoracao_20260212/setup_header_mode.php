<?php
// ARQUIVO: setup_header_mode.php
// FUNÇÃO: Adiciona a coluna header_logo_mode na tabela DadosEmpresa
// Valores possíveis: 'full' (logo completo) ou 'icon' (logo simples/ícone)

require_once 'config.php';
require_once 'db.php';

echo "<html><head><title>Setup Header Mode</title>";
echo "<style>
    body { font-family: 'Segoe UI', sans-serif; background: #0a2e5c; color: #fff; padding: 40px; }
    .container { max-width: 600px; margin: 0 auto; background: rgba(255,255,255,0.1); padding: 30px; border-radius: 16px; }
    h2 { color: #FF7518; margin-bottom: 20px; }
    .success { color: #4ade80; margin: 10px 0; padding: 10px; background: rgba(74,222,128,0.1); border-radius: 8px; }
    .info { color: #4fc3f7; margin: 10px 0; padding: 10px; background: rgba(79,195,247,0.1); border-radius: 8px; }
    .error { color: #f87171; margin: 10px 0; padding: 10px; background: rgba(248,113,113,0.1); border-radius: 8px; }
    a { color: #FF7518; }
</style></head><body>";
echo "<div class='container'>";
echo "<h2>🖼️ Setup: Modo de Logo no Header</h2>";

try {
    // Atualizar ambos os bancos (Produção e Demo)
    $databases = [
        'Produção' => Database::getProd(),
        'Demo' => Database::getDemo()
    ];

    foreach ($databases as $dbName => $conn) {
        echo "<h3>📦 Banco: $dbName</h3>";

        // Verificar se a coluna já existe
        $checkColumn = $conn->query("SHOW COLUMNS FROM DadosEmpresa LIKE 'header_logo_mode'");

        if ($checkColumn->num_rows > 0) {
            echo "<div class='info'>ℹ️ Coluna 'header_logo_mode' já existe.</div>";
        } else {
            // Adicionar a coluna
            $sql = "ALTER TABLE DadosEmpresa 
                    ADD COLUMN header_logo_mode ENUM('full', 'icon') DEFAULT 'full' 
                    COMMENT 'Modo de exibição do logo no header: full=completo, icon=compacto'";

            if ($conn->query($sql)) {
                echo "<div class='success'>✅ Coluna 'header_logo_mode' adicionada com sucesso!</div>";
            } else {
                throw new Exception("Erro ao adicionar coluna: " . $conn->error);
            }
        }

        // Verificar se a coluna logo_icon_caminho já existe
        $checkIconColumn = $conn->query("SHOW COLUMNS FROM DadosEmpresa LIKE 'logo_icon_caminho'");

        if ($checkIconColumn->num_rows > 0) {
            echo "<div class='info'>ℹ️ Coluna 'logo_icon_caminho' já existe.</div>";
        } else {
            // Adicionar a coluna para o caminho do ícone
            $sql2 = "ALTER TABLE DadosEmpresa 
                     ADD COLUMN logo_icon_caminho VARCHAR(255) DEFAULT NULL 
                     COMMENT 'Caminho do logo ícone/compacto'";

            if ($conn->query($sql2)) {
                echo "<div class='success'>✅ Coluna 'logo_icon_caminho' adicionada com sucesso!</div>";
            } else {
                throw new Exception("Erro ao adicionar coluna logo_icon_caminho: " . $conn->error);
            }
        }
    }

    echo "<hr style='border-color: rgba(255,255,255,0.1); margin: 20px 0;'>";
    echo "<div class='success'><strong>🎉 Setup concluído com sucesso!</strong></div>";
    echo "<p>Agora você pode configurar o modo do logo em <a href='minha_empresa.php'>Minha Empresa</a>.</p>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div></body></html>";
