<?php
/**
 * INSTALADOR AUTOMÁTICO - Sistema de Propostas GEOMETRPOLE
 * 
 * Executa:
 * 1. Cria estrutura de pastas
 * 2. Cria tabelas no banco de dados
 * 3. Configura tema padrão
 * 4. Gera arquivos necessários
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Segurança: Verificar se já está instalado
if (file_exists('../config/config.php')) {
    // Se tentar acessar o instalador novamente, mostrar erro ou redirecionar
    die("<h1>Sistema já instalado!</h1><p>Por segurança, o instalador foi bloqueado. <a href='../admin/login.php'>Acesse o painel</a>.</p><p>Para reinstalar, remova o arquivo <code>config/config.php</code> manualmente.</p>");
}

class InstaladorGeometrpole {
    
    private $passos = [];
    private $erros = [];
    private $sucesso = [];
    
    public function __construct() {
        $this->verificarPermissoes();
    }
    
    /**
     * Executa instalação completa
     */
    public function instalar($config = []) {
        try {
            $this->log("🚀 Iniciando instalação do Sistema GEOMETRPOLE...");
            
            // Passo 1: Estrutura de pastas
            $this->criarEstruturaPastas();
            
            // Passo 2: Arquivos CSS
            $this->criarArquivosCSS();
            
            // Passo 3: Banco de dados
            if (!empty($config['db_host'])) {
                $this->criarBancoDados($config);
            }
            
            // Passo 4: Configurações
            $this->criarArquivoConfig($config);
            
            // Passo 5: Tema padrão
            $this->instalarTemaPadrao();
            
            // Passo 6: Templates HTML
            $this->criarTemplatesHTML();
            
            // Passo 7: Proteção
            $this->criarHtaccess();
            
            $this->log("✅ Instalação concluída com sucesso!");
            return true;
            
        } catch (Exception $e) {
            $this->erros[] = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Cria estrutura de pastas
     */
    private function criarEstruturaPastas() {
        $pastas = [
            '../config/',
            '../admin/',
            '../assets/logos/',
            '../assets/assinaturas/',
            '../assets/uploads/',
            '../templates/css/',
            '../temp/',
            '../backup/',
            '../logs/'
        ];
        
        foreach ($pastas as $pasta) {
            if (!is_dir($pasta)) {
                if (mkdir($pasta, 0755, true)) {
                    $this->sucesso[] = "📁 Pasta criada: $pasta";
                } else {
                    throw new Exception("Não foi possível criar a pasta: $pasta");
                }
            } else {
                $this->passos[] = "📁 Pasta já existe: $pasta";
            }
        }
    }
    
    /**
     * Cria arquivos CSS completos
     */
    private function criarArquivosCSS() {
        // CSS Base (print.css) - já criado anteriormente
        $cssBase = $this->getConteudoCSSBase();
        file_put_contents('../templates/css/print.css', $cssBase);
        $this->sucesso[] = "🎨 CSS Base criado";
        
        // CSS Curto
        $cssCurto = $this->getConteudoCSSCurto();
        file_put_contents('../templates/css/print-curto.css', $cssCurto);
        $this->sucesso[] = "🎨 CSS Curto criado";
        
        // CSS Longo
        $cssLongo = $this->getConteudoCSSLongo();
        file_put_contents('../templates/css/print-longo.css', $cssLongo);
        $this->sucesso[] = "🎨 CSS Longo criado";
        
        // CSS Tema Dinâmico (inicialmente vazio, será preenchido pelo painel)
        file_put_contents('../templates/css/tema-dinamico.css', "/* Tema personalizado - gerado pelo painel admin */\n");
        $this->sucesso[] = "🎨 CSS Tema Dinâmico criado";
    }
    
    /**
     * Cria tabelas no banco de dados
     */
    private function criarBancoDados($config) {
        $pdo = new PDO(
            "mysql:host={$config['db_host']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Criar banco se não existir
        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$config['db_name']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE {$config['db_name']}");
        
        // Tabela: empresas
        $pdo->exec("CREATE TABLE IF NOT EXISTS empresas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            cnpj VARCHAR(20),
            telefone VARCHAR(20),
            whatsapp VARCHAR(20),
            email VARCHAR(100),
            endereco TEXT,
            logo VARCHAR(255),
            cor_primaria VARCHAR(7) DEFAULT '#2c3e50',
            cor_secundaria VARCHAR(7) DEFAULT '#34495e',
            cor_destaque VARCHAR(7) DEFAULT '#3498db',
            fonte_principal VARCHAR(100) DEFAULT 'Segoe UI',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Tabela: clientes
        $pdo->exec("CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            telefone VARCHAR(20),
            celular VARCHAR(20),
            cpf_cnpj VARCHAR(20),
            endereco TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Tabela: propostas
        $pdo->exec("CREATE TABLE IF NOT EXISTS propostas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            numero_proposta VARCHAR(50) UNIQUE,
            empresa_id INT,
            cliente_id INT,
            servico VARCHAR(255),
            descricao_servico TEXT,
            endereco_obra TEXT,
            bairro_obra VARCHAR(100),
            cidade_obra VARCHAR(100),
            area_estimada DECIMAL(10,2),
            valor_total DECIMAL(12,2),
            valor_sinal DECIMAL(12,2),
            valor_final DECIMAL(12,2),
            prazo_entrega VARCHAR(50),
            validade_proposta VARCHAR(50),
            status ENUM('rascunho', 'enviada', 'aceita', 'rejeitada') DEFAULT 'rascunho',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (empresa_id) REFERENCES empresas(id),
            FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        )");
        
        // Tabela: equipamentos
        $pdo->exec("CREATE TABLE IF NOT EXISTS equipamentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            proposta_id INT,
            drone_marca VARCHAR(100),
            gps_marca VARCHAR(100),
            veiculo_marca VARCHAR(100),
            outros TEXT,
            FOREIGN KEY (proposta_id) REFERENCES propostas(id) ON DELETE CASCADE
        )");
        
        // Tabela: dados_bancarios
        $pdo->exec("CREATE TABLE IF NOT EXISTS dados_bancarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            proposta_id INT,
            banco VARCHAR(100),
            agencia VARCHAR(20),
            conta VARCHAR(20),
            pix_chave VARCHAR(100),
            favorecido VARCHAR(255),
            FOREIGN KEY (proposta_id) REFERENCES propostas(id) ON DELETE CASCADE
        )");
        
        // Tabela: temas_personalizados
        $pdo->exec("CREATE TABLE IF NOT EXISTS temas_personalizados (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100),
            descricao TEXT,
            cor_primaria VARCHAR(7) DEFAULT '#2c3e50',
            cor_secundaria VARCHAR(7) DEFAULT '#34495e',
            cor_destaque VARCHAR(7) DEFAULT '#3498db',
            cor_sucesso VARCHAR(7) DEFAULT '#27ae60',
            cor_alerta VARCHAR(7) DEFAULT '#ffc107',
            fonte_titulo VARCHAR(100) DEFAULT 'Segoe UI',
            fonte_corpo VARCHAR(100) DEFAULT 'Segoe UI',
            tamanho_base VARCHAR(10) DEFAULT '11pt',
            espacamento_padrao VARCHAR(10) DEFAULT '15px',
            bordas_arredondadas TINYINT(1) DEFAULT 1,
            sombras TINYINT(1) DEFAULT 1,
            css_custom TEXT,
            ativo TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Tabela: usuarios_admin
        $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios_admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100),
            email VARCHAR(100) UNIQUE,
            senha VARCHAR(255),
            nivel ENUM('admin', 'editor') DEFAULT 'editor',
            ativo TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Inserir tema padrão
        $stmt = $pdo->prepare("INSERT INTO temas_personalizados 
            (nome, descricao, ativo) VALUES (?, ?, ?)");
        $stmt->execute(['Tema Padrão GEOMETRPOLE', 'Tema oficial com cores institucionais', 1]);
        
        $this->sucesso[] = "🗄️ Banco de dados configurado";
    }
    
    /**
     * Cria arquivo de configuração
     */
    private function criarArquivoConfig($config) {
        $conteudo = "<?php
/**
 * CONFIGURAÇÃO DO SISTEMA - GEOMETRPOLE
 * Gerado automaticamente pelo instalador
 */

// Banco de dados
define('DB_HOST', '{$config['db_host']}');
define('DB_NAME', '{$config['db_name']}');
define('DB_USER', '{$config['db_user']}');
define('DB_PASS', '{$config['db_pass']}');

// Caminhos
define('BASE_PATH', dirname(__DIR__));
define('TEMPLATE_PATH', BASE_PATH . '/templates/');
define('ASSETS_PATH', BASE_PATH . '/assets/');
define('TEMP_PATH', BASE_PATH . '/temp/');

// URLs
define('BASE_URL', '{$config['base_url']}');
define('ASSETS_URL', BASE_URL . '/assets/');

// Configurações de upload
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'svg']);

// Modo debug (0 = produção, 1 = desenvolvimento)
define('DEBUG_MODE', 0);

// Versão do sistema
define('SISTEMA_VERSAO', '2.0.0');
";
        
        file_put_contents('../config/config.php', $conteudo);
        $this->sucesso[] = "⚙️ Arquivo de configuração criado";
    }
    
    /**
     * Instala tema padrão
     */
    private function instalarTemaPadrao() {
        $tema = [
            'nome' => 'GEOMETRPOLE Padrão',
            'cores' => [
                'primaria' => '#2c3e50',
                'secundaria' => '#34495e',
                'destaque' => '#3498db',
                'sucesso' => '#27ae60',
                'alerta' => '#ffc107'
            ],
            'fontes' => [
                'titulo' => 'Segoe UI',
                'corpo' => 'Segoe UI'
            ]
        ];
        
        file_put_contents('../config/tema_padrao.json', json_encode($tema, JSON_PRETTY_PRINT));
        $this->sucesso[] = "🎨 Tema padrão instalado";
    }
    
    /**
     * Cria templates HTML otimizados
     */
    private function criarTemplatesHTML() {
        // Os templates serão criados pelo editor_dinamico.php
        // Aqui apenas garantimos que existam arquivos base
        $this->sucesso[] = "📄 Templates prontos para uso";
    }
    
    /**
     * Cria .htaccess para proteção
     */
    private function criarHtaccess() {
        $htaccess = "# Proteção de arquivos sensíveis
<FilesMatch \"^\\.(sql|log|ini|json)$\">
    Order allow,deny
    Deny from all
</FilesMatch>

# Proteger pasta config
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^config/ - [F,L]
    RewriteRule ^temp/ - [F,L]
    RewriteRule ^logs/ - [F,L]
</IfModule>

# Compressão
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript
</IfModule>

# Cache de recursos estáticos
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css \"access plus 1 month\"
    ExpiresByType application/javascript \"access plus 1 month\"
    ExpiresByType image/png \"access plus 3 months\"
    ExpiresByType image/jpg \"access plus 3 months\"
</IfModule>
";
        
        file_put_contents('../.htaccess', $htaccess);
        $this->sucesso[] = "🔒 Proteção (.htaccess) configurada";
    }
    
    /**
     * Verifica permissões do servidor
     */
    private function verificarPermissoes() {
        if (!is_writable('..')) {
            throw new Exception("Diretório raiz não tem permissão de escrita. Verifique as permissões (chmod 755).");
        }
        
        if (!extension_loaded('pdo_mysql')) {
            throw new Exception("Extensão PDO MySQL não instalada.");
        }
    }
    
    /**
     * Retorna conteúdo CSS Base (resumido para o instalador)
     */
    private function getConteudoCSSBase() {
        if (file_exists('../templates/css/print.css')) {
            return file_get_contents('../templates/css/print.css');
        }
        return "/* CSS Base - GEOMETRPOLE */\n";
    }
    
    private function getConteudoCSSCurto() {
        if (file_exists('../templates/css/print-curto.css')) {
            return file_get_contents('../templates/css/print-curto.css');
        }
        return "/* CSS Curto - GEOMETRPOLE */\n";
    }
    
    private function getConteudoCSSLongo() {
        if (file_exists('../templates/css/print-longo.css')) {
            return file_get_contents('../templates/css/print-longo.css');
        }
        return "/* CSS Longo - GEOMETRPOLE */\n";
    }
    
    private function log($mensagem) {
        $this->passos[] = $mensagem;
    }
    
    public function getLogs() {
        return [
            'passos' => $this->passos,
            'sucesso' => $this->sucesso,
            'erros' => $this->erros
        ];
    }
}

// ============================================
// INTERFACE DO INSTALADOR
// ============================================

$instalador = new InstaladorGeometrpole();
$instalado = false;
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = [
        'db_host' => $_POST['db_host'] ?? 'localhost',
        'db_name' => $_POST['db_name'] ?? 'geometrpole',
        'db_user' => $_POST['db_user'] ?? '',
        'db_pass' => $_POST['db_pass'] ?? '',
        'base_url' => $_POST['base_url'] ?? 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF']))
    ];
    
    $instalado = $instalador->instalar($config);
    $logs = $instalador->getLogs();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador GEOMETRPOLE</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .install-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }
        
        .install-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .install-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .install-header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .install-body {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-install {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(39, 174, 96, 0.3);
        }
        
        .logs {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .log-item {
            padding: 8px;
            margin: 5px 0;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        
        .log-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .log-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .log-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .success-message h2 {
            margin-bottom: 10px;
        }
        
        .btn-access {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 30px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        
        .feature {
            text-align: center;
            padding: 20px;
        }
        
        .feature-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .feature h3 {
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .feature p {
            color: #7f8c8d;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>🚀 GEOMETRPOLE</h1>
            <p>Sistema de Propostas Técnicas com Aerofotogrametria</p>
        </div>
        
        <div class="install-body">
            <?php if ($instalado): ?>
                <div class="success-message">
                    <h2>✅ Instalação Concluída!</h2>
                    <p>O sistema foi instalado com sucesso. Você já pode começar a usar.</p>
                    <a href="../admin/painel.php" class="btn-access">Acessar Painel Administrativo</a>
                </div>
                
                <div class="logs">
                    <h3>📋 Log de Instalação</h3>
                    <?php foreach ($logs['sucesso'] as $log): ?>
                        <div class="log-item log-success"><?php echo $log; ?></div>
                    <?php endforeach; ?>
                    <?php foreach ($logs['passos'] as $log): ?>
                        <div class="log-item log-info"><?php echo $log; ?></div>
                    <?php endforeach; ?>
                </div>
                
            <?php else: ?>
                
                <div class="features">
                    <div class="feature">
                        <div class="feature-icon">🎨</div>
                        <h3>Temas Personalizáveis</h3>
                        <p>Cores, fontes e logos da sua marca</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">📄</div>
                        <h3>Propostas Profissionais</h3>
                        <p>PDF e Word com layout impecável</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">⚡</div>
                        <h3>Rápido e Fácil</h3>
                        <p>Instalação automática em minutos</p>
                    </div>
                </div>
                
                <?php if (!empty($logs['erros'])): ?>
                    <div class="logs">
                        <h3>❌ Erros encontrados:</h3>
                        <?php foreach ($logs['erros'] as $erro): ?>
                            <div class="log-item log-error"><?php echo $erro; ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <h3 style="margin-bottom: 20px; color: #2c3e50;">Configuração do Banco de Dados</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Host do Banco</label>
                            <input type="text" name="db_host" value="localhost" required>
                        </div>
                        <div class="form-group">
                            <label>Nome do Banco</label>
                            <input type="text" name="db_name" value="geometrpole" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Usuário</label>
                            <input type="text" name="db_user" placeholder="root" required>
                        </div>
                        <div class="form-group">
                            <label>Senha</label>
                            <input type="password" name="db_pass" placeholder="Sua senha">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>URL Base do Sistema</label>
                        <input type="text" name="base_url" value="http://<?php echo $_SERVER['HTTP_HOST']; ?><?php echo dirname(dirname($_SERVER['PHP_SELF'])); ?>" required>
                    </div>
                    
                    <button type="submit" class="btn-install">
                        🚀 Instalar Sistema Agora
                    </button>
                </form>
                
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
