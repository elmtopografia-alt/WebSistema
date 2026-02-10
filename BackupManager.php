<?php
// Arquivo: BackupManager.php
// Função: Gerencia processamento de backups, retenção e exportação de dados do cliente.
// Herança: Respeita rigorosamente o id_criador (Dono dos Dados).

require_once 'config.php';
require_once 'db.php';

class BackupManager {
    
    private $conn;
    private $id_criador;
    private $backupDir;

    public function __construct($id_criador) {
        $this->id_criador = $id_criador;
        $this->conn = Database::getProd(); // Sempre Prod para backup
        
        // Pasta base de backups protegida
        $this->backupDir = __DIR__ . '/backups/user_' . $id_criador . '/';
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
            // Cria .htaccess para segurança
            file_put_contents($this->backupDir . '.htaccess', 'Deny from all');
        }
    }

    /**
     * Gera um ZIP contendo JSONs de todas as tabelas do usuário
     * Estratégia: Dump Lógico em JSON (Portável e Legível)
     */
    public function gerarBackupCompleto() {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_v1_{$timestamp}.json";
        $filepath = $this->backupDir . $filename;

        $dados = [];

        // 1. Dados da Empresa
        $dados['DadosEmpresa'] = $this->fetchTable('DadosEmpresa', 'id_criador');

        // 2. Clientes
        $dados['Clientes'] = $this->fetchTable('Clientes', 'id_criador');

        // 3. Tipos de Serviços (Tabela Global - Sem filtro de usuário)
        $dados['Tipo_Servicos'] = $this->fetchTableGlobal('Tipo_Servicos');

        // 4. Propostas (E seus itens)
        $propostas = $this->fetchTable('Propostas', 'id_criador');
        $dados['Propostas'] = [];
        
        foreach ($propostas as $prop) {
            $id_prop = $prop['id_proposta'];
            // Busca itens relacionados
            $prop['Itens'] = [
                'Salarios' => $this->fetchSubTable('Proposta_Salarios', $id_prop),
                'Estadia'  => $this->fetchSubTable('Proposta_Estadia', $id_prop),
                'Consumos' => $this->fetchSubTable('Proposta_Consumos', $id_prop),
                'Locacao'  => $this->fetchSubTable('Proposta_Locacao', $id_prop),
                'Admin'    => $this->fetchSubTable('Proposta_Custos_Administrativos', $id_prop),
            ];
            $dados['Propostas'][] = $prop;
        }

        // Salva JSON
        file_put_contents($filepath, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Compacta (ZIP) para economizar espaço
        if (!class_exists('ZipArchive')) {
            // Fallback: Se não tiver ZIP, entrega o JSON mesmo (melhor que Crash)
            return $filepath; // Retorna o .json
        }

        $zipFile = $this->backupDir . "backup_full_{$timestamp}.zip";
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($filepath, $filename);
            
            // Opcional: Adicionar Logos
            // $this->addLogosToZip($zip);
            
            $zip->close();
            @unlink($filepath); // Remove o JSON bruto e suprime erro se falhar
            return $zipFile;
        } else {
             // Se falhar ao abrir o ZIP, retorna o JSON
             return $filepath;
        }

        return false;
    }

    /**
     * Aplica a política de retenção (Rotação)
     * Mantém: Últimos 7 dias + 4 semanas + 6 meses
     */
    public function aplicarPoliticaRetencao() {
        $files = glob($this->backupDir . '*.zip');
        $agora = time();
        $mantidos = 0;
        $excluidos = 0;

        foreach ($files as $file) {
            $fileTime = filemtime($file);
            $idadeDias = ($agora - $fileTime) / (60 * 60 * 24);
            $deveManter = false;

            // Regra 1: Últimos 7 dias (Diário)
            if ($idadeDias <= 7) {
                $deveManter = true;
            }
            // Regra 2: Últimas 4 semanas (1 por semana)
            elseif ($idadeDias <= 30 && date('w', $fileTime) == 0) { // Domingo
                $deveManter = true;
            }
            // Regra 3: Últimos 6 meses (1 por mês)
            elseif ($idadeDias <= 180 && date('d', $fileTime) == '01') { // Dia 1
                $deveManter = true;
            }

            if (!$deveManter) {
                unlink($file);
                $excluidos++;
            } else {
                $mantidos++;
            }
        }
        return ['mantidos' => $mantidos, 'excluidos' => $excluidos];
    }

    // --- Helpers Privados ---

    private function fetchTable($tabela, $campoFiltro) {
        $stmt = $this->conn->prepare("SELECT * FROM $tabela WHERE $campoFiltro = ?");
        $stmt->bind_param('i', $this->id_criador);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function fetchTableGlobal($tabela) {
        $result = $this->conn->query("SELECT * FROM $tabela");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function fetchSubTable($tabela, $id_proposta) {
        $stmt = $this->conn->prepare("SELECT * FROM $tabela WHERE id_proposta = ?");
        $stmt->bind_param('i', $id_proposta);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
