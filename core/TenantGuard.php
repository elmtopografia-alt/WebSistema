<?php
// Arquivo: core/TenantGuard.php
// Título: Guardião de Multi-tenancy
// Função: Centralizar a lógica de segurança para evitar vazamento de dados entre usuários.

class TenantGuard {

    /**
     * Retorna um RESULT SET (mysqli_result) garantindo que os dados pertencem ao usuário.
     * Substitui o padrão repetitivo: SELECT * FROM X WHERE id_criador = ?
     * 
     * @param mysqli $conn Conexão ativa
     * @param string $table Nome da tabela
     * @param int $id_criador ID do usuário dono dos dados
     * @param string $columns Colunas para selecionar (padrão '*')
     * @param string $extraCondition SQL extra (ex: "AND status = 'ativo'")
     * @return mysqli_result Result set para iteração
     */
    public static function getScopedResult($conn, $table, $id_criador, $columns = '*', $extraCondition = '', $orderBy = '') {
        // Validação básica de segurança contra SQL Injection no nome da tabela/colunas
        // (Assume-se que $table e $columns vêm do código, não do usuário, mas previne erros básicos)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new Exception("TenantGuard Security Alert: Invalid table name.");
        }

        $sql = "SELECT $columns FROM $table WHERE id_criador = ?";
        
        if ($extraCondition) {
            $sql .= " " . $extraCondition;
        }

        if ($orderBy) {
             $sql .= " ORDER BY " . $orderBy;
        }

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("TenantGuard DB Error: " . $conn->error);
        }

        $stmt->bind_param('i', $id_criador);
        $stmt->execute();
        
        return $stmt->get_result();
    }

    /**
     * Verifica se um recurso específico pertence ao usuário.
     * Útil para rotas de EDIÇÃO/EXCLUSÃO (ex: editar_proposta.php?id=123)
     * 
     * @param mysqli $conn
     * @param string $table
     * @param int $id_resource ID do registro (Primary Key)
     * @param string $pkColumn Nome da coluna Primary Key (ex: id_proposta)
     * @param int $id_criador ID do usuário logado
     * @return bool True se for dono, False se não for
     */
    public static function isOwner($conn, $table, $id_resource, $pkColumn, $id_criador) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $pkColumn)) {
            throw new Exception("TenantGuard Security Alert: Invalid identifiers.");
        }

        $sql = "SELECT 1 FROM $table WHERE $pkColumn = ? AND id_criador = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $id_resource, $id_criador);
        $stmt->execute();
        $stmt->store_result();
        
        return ($stmt->num_rows > 0);
    }
}
?>
