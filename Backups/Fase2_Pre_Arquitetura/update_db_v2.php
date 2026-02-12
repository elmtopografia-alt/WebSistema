<?php
require_once 'config/config.php';
require_once 'config/database.php';

echo "Iniciando atualização do banco de dados...\n";

try {
    $db = new Database();
    
    // 1. Adicionar colunas na tabela temas_personalizados (se não existirem)
    echo "Atualizando tabela 'temas_personalizados'...\n";
    
    // Verificar se as colunas já existem
    $columns = $db->query("SHOW COLUMNS FROM temas_personalizados LIKE 'slug'")->fetch();
    if (!$columns) {
        $sql = "ALTER TABLE temas_personalizados ADD COLUMN (
            slug VARCHAR(50) UNIQUE,
            icone VARCHAR(50) DEFAULT 'palette',
            is_padrao TINYINT(1) DEFAULT 0,
            is_sistema TINYINT(1) DEFAULT 0,
            preview_imagem VARCHAR(255),
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_modificacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            criado_por VARCHAR(100),
            tags TEXT,
            descricao_tecnica TEXT
        )";
        $db->query($sql);
        echo " - Colunas adicionadas com sucesso.\n";
    } else {
        echo " - Colunas já existem.\n";
    }

    // 2. Criar tabela temas_historico
    echo "Verificando tabela 'temas_historico'...\n";
    $db->query("CREATE TABLE IF NOT EXISTS temas_historico (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tema_id INT,
        acao ENUM('criar', 'editar', 'ativar', 'excluir', 'importar', 'exportar', 'duplicar'),
        dados_anteriores JSON,
        dados_novos JSON,
        usuario VARCHAR(100),
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (tema_id) REFERENCES temas_personalizados(id) ON DELETE SET NULL
    )");
    echo " - Tabela 'temas_historico' verificada/criada.\n";

    // 3. Criar tabela temas_backups
    echo "Verificando tabela 'temas_backups'...\n";
    $db->query("CREATE TABLE IF NOT EXISTS temas_backups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100),
        descricao TEXT,
        arquivo VARCHAR(255),
        tamanho INT,
        checksum VARCHAR(64),
        tipo ENUM('automatico', 'manual', 'pre_atualizacao'),
        restaurado_em TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo " - Tabela 'temas_backups' verificada/criada.\n";

    // 4. Inserir temas do sistema
    echo "Inserindo temas padrão do sistema...\n";
    
    $temasSistema = [
        [
            'nome' => 'GEOMETRPOLE Padrão',
            'slug' => 'padrao',
            'descricao' => 'Tema oficial com cores institucionais azuis',
            'icone' => 'palette',
            'is_sistema' => 1,
            'cor_primaria' => '#2c3e50',
            'cor_secundaria' => '#34495e',
            'cor_destaque' => '#3498db',
            'ativo' => 1
        ],
        [
            'nome' => 'GEOMETRPOLE Escuro',
            'slug' => 'escuro',
            'descricao' => 'Versão escura moderna para impressão econômica',
            'icone' => 'moon',
            'is_sistema' => 1,
            'cor_primaria' => '#1a1a2e',
            'cor_secundaria' => '#16213e',
            'cor_destaque' => '#0f3460',
            'ativo' => 0
        ],
        [
            'nome' => 'GEOMETRPOLE Minimalista',
            'slug' => 'minimalista',
            'descricao' => 'Design limpo com poucos elementos visuais',
            'icone' => 'minimize',
            'is_sistema' => 1,
            'cor_primaria' => '#000000',
            'cor_secundaria' => '#333333',
            'cor_destaque' => '#666666',
            'ativo' => 0
        ],
        [
            'nome' => 'GEOMETRPOLE Natureza',
            'slug' => 'natureza',
            'descricao' => 'Tons verdes para empresas de meio ambiente',
            'icone' => 'leaf',
            'is_sistema' => 1,
            'cor_primaria' => '#2d5016',
            'cor_secundaria' => '#3a6b1f',
            'cor_destaque' => '#52b788',
            'ativo' => 0
        ],
        [
            'nome' => 'GEOMETRPOLE Tecnologia',
            'slug' => 'tech',
            'descricao' => 'Cores vibrantes para startups de tecnologia',
            'icone' => 'cpu',
            'is_sistema' => 1,
            'cor_primaria' => '#0f0f23',
            'cor_secundaria' => '#1a1a3e',
            'cor_destaque' => '#00d4aa',
            'ativo' => 0
        ]
    ];

    foreach ($temasSistema as $tema) {
        // Verificar se tema já existe pelo slug
        $stmt = $db->query("SELECT id FROM temas_personalizados WHERE slug = ?", [$tema['slug']]);
        if (!$stmt->fetch()) {
            $sql = "INSERT INTO temas_personalizados 
                (nome, slug, descricao, icone, is_sistema, cor_primaria, cor_secundaria, cor_destaque, ativo) 
                VALUES 
                ('{$tema['nome']}', '{$tema['slug']}', '{$tema['descricao']}', '{$tema['icone']}', 
                {$tema['is_sistema']}, '{$tema['cor_primaria']}', '{$tema['cor_secundaria']}', 
                '{$tema['cor_destaque']}', {$tema['ativo']})";
            $db->query($sql);
            echo " - Tema '{$tema['nome']}' inserido.\n";
        } else {
             // Opcional: Atualizar se já existe para garantir integridade, mas vamos pular
             echo " - Tema '{$tema['nome']}' já existe.\n";
        }
    }

    echo "✅ Atualização do banco de dados concluída!\n";

} catch (Exception $e) {
    echo "❌ Erro durante a atualização: " . $e->getMessage() . "\n";
}
?>
