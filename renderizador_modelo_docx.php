<?php
/**
 * Renderizador de Ponte para Modelos DOCX
 * Facilita a integração de modelos gerados no editor_dinamico.php
 */

require_once __DIR__ . '/ResolvedorChavesSistema.php';
require_once __DIR__ . '/config_chaves_sistema.php';

class RenderizadorModeloDOCX {
    
    private $conn;
    private $configChaves;
    private $resolvedor;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->configChaves = require __DIR__ . '/config_chaves_sistema.php';
        $this->resolvedor = new ResolvedorChavesSistema($this->conn);
    }

    /**
     * Lista todos os modelos gerados disponíveis
     */
    public function listarModelos(): array {
        $diretorio = __DIR__ . '/modelos_gerados/';
        if (!is_dir($diretorio)) return [];
        
        $arquivos = glob($diretorio . 'Modelo*.php');
        $modelos = [];
        
        foreach ($arquivos as $arquivo) {
            $nome = str_replace(['Modelo', '.php'], '', basename($arquivo));
            // Ignora backups e arquivos sem nome válido
            if (empty($nome) || strpos($nome, '.') !== false) continue;
            $modelos[] = [
                'id'      => $nome,
                'nome'    => str_replace('_', ' ', $nome),
                'caminho' => $arquivo
            ];
        }
        
        return $modelos;
    }

    /**
     * Carrega e renderiza um modelo específico
     */
    public function renderizar(string $idModelo, int $idUsuario, array $dadosManuais): string {
        $arquivo = __DIR__ . '/modelos_gerados/Modelo' . $idModelo . '.php';
        
        if (!file_exists($arquivo)) {
            return "Erro: Modelo não encontrado ($idModelo)";
        }

        require_once $arquivo;
        // Novos modelos não têm namespace — busca sem e com namespace
        $classe = 'Modelo' . $idModelo;
        if (!class_exists($classe)) {
            $classe = '\\SGT\\Propostas\\Modelo' . $idModelo;
        }
        if (!class_exists($classe)) {
            return "Erro: Classe do modelo não carregada (Modelo{$idModelo})";
        }

        $modeloInstancia = new $classe();

                // --- INJEÇÃO DE CONTEÚDO CUSTOMIZADO (Editor Dinâmico) ---
        // Permite que o que foi editado no editor_dinamico.php substitua o texto estático do Word
        try {
            $refl = new \ReflectionClass($classe);
            if ($refl->hasProperty('blocos')) {
                $prop = $refl->getProperty('blocos');
                $prop->setAccessible(true);
                $blocos = $prop->getValue($modeloInstancia);

                $mudou = false;
                
                // Pega os blocos em JSON resolvidos pelo banco, se existirem
                $blocosSalvos = isset($dadosManuais['docx_blocos']) ? $dadosManuais['docx_blocos'] : [];

                foreach ($blocos as $index => &$b) {
                    $slug = (isset($b['subtipo']) ? $b['subtipo'] : 'bloco') . '_' . $index;
                    $docxChave = "docx_bloco_{$index}_content";
                    
                    // 1. Tenta buscar do JSON decodificado (docx_conteudo vindo do banco)
                    // No banco, salvamos o array indexado pelo próprio $index, e dentro tem 'conteudo'
                    $encontrouNoJson = false;
                    foreach ($blocosSalvos as $blocoSalvo) {
                        if (isset($blocoSalvo['index']) && $blocoSalvo['index'] == $index) {
                            $b['conteudo'] = $blocoSalvo['conteudo'];
                            $mudou = true;
                            $encontrouNoJson = true;
                            break;
                        }
                    }
                    
                    if ($encontrouNoJson) {
                        continue;
                    }

                    // 2. Se houver conteúdo customizado para este bloco direto no POST (formato V3)
                    if (isset($dadosManuais[$docxChave]) && !empty($dadosManuais[$docxChave])) {
                        $b['conteudo'] = $dadosManuais[$docxChave];
                        $mudou = true;
                    }
                    // 3. Fallback para nomes de slug antigos (_content)
                    elseif (isset($dadosManuais[$slug . '_content']) && !empty($dadosManuais[$slug . '_content'])) {
                        $b['conteudo'] = $dadosManuais[$slug . '_content'];
                        $mudou = true;
                    } 
                    // 4. Fallback final para a chave direta sem sufixo
                    elseif (isset($dadosManuais[$slug]) && !empty($dadosManuais[$slug]) && !isset($this->configChaves[$slug])) {
                        $b['conteudo'] = $dadosManuais[$slug];
                        $mudou = true;
                    }
                }

                if ($mudou) {
                    $prop->setValue($modeloInstancia, $blocos);
                }
            }
        } catch (\Exception $e) {
            error_log("Erro ao injetar conteúdo customizado no modelo DOCX: " . $e->getMessage());
        }
        // -------------------------------------------------------

        return $modeloInstancia->render($dadosManuais, $this->resolvedor, $idUsuario);
    }
    
    /**
     * Retorna os metadados do modelo (variáveis detectadas, etc)
     */
    public function obterMetadata(string $idModelo): ?array {
        $arquivo = __DIR__ . '/modelos_gerados/Modelo' . $idModelo . '.php';
        if (!file_exists($arquivo)) return null;

        require_once $arquivo;
        $classe = 'Modelo' . $idModelo;
        if (!class_exists($classe)) {
            $classe = '\\SGT\\Propostas\\Modelo' . $idModelo;
        }
        if (!class_exists($classe)) return null;

        $instancia = new $classe();
        // ModeloBase não tem getConfig() — retorna estrutura compatível
        return [
            'nome'      => $instancia->getNome(),
            'cor'       => $instancia->getCorAtiva(),
            'variaveis' => [],
            'blocos'    => []
        ];
    }
}
