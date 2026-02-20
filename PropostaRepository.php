<?php
/**
 * PropostaRepository.php
 * Persistência centralizada e gerenciamento de revisões
 * Versão: 1.0.1-SchemaAware (Trigger Sync)
 */

require_once __DIR__ . '/ConnectionManager.php';
require_once __DIR__ . '/CalculadoraService.php';

class PropostaRepository 
{
    private $conn;
    private $calc;
    private $idCriador;
    private $schemaCache = null;
    
    public function __construct($conn = null) 
    {
        $this->conn = $conn ?? ConnectionManager::get();
        $this->calc = new CalculadoraService();
        
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $this->idCriador = $_SESSION['usuario_id'] ?? 0;
        
        // Ativa Autocura de Schema
        $this->autoHealSchema();
    }

    /**
     * Retorna a conexão mysqli ativa
     */
    public function getConn()
    {
        return $this->conn;
    }

    /**
     * Verifica e corrige o banco de dados se colunas vitais estiverem faltando
     */
    private function autoHealSchema()
    {
        $critical_cols = [
            'modelo_docx' => "VARCHAR(255) DEFAULT NULL AFTER id_servico",
            'config_docx_json' => "LONGTEXT DEFAULT NULL AFTER modelo_docx"
        ];

        // Só verifica se houver indício de erro ou em primeira carga (otimização leve)
        $cols = $this->getAvailableColumns();
        foreach ($critical_cols as $col => $definition) {
            if (!in_array($col, $cols)) {
                error_log("PropostaRepository: Auto-healing column '$col'...");
                $this->conn->query("ALTER TABLE Propostas ADD COLUMN $col $definition");
                $this->schemaCache = null; // Invalida cache
            }
        }
    }

    /**
     * Retorna a lista de colunas reais da tabela Propostas
     */
    private function getAvailableColumns()
    {
        if ($this->schemaCache !== null) return $this->schemaCache;
        
        $this->schemaCache = [];
        $res = $this->conn->query("SHOW COLUMNS FROM Propostas");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $this->schemaCache[] = $row['Field'];
            }
        }
        return $this->schemaCache;
    }
    
    /**
     * Override do método salvar para detectar e processar DOCX
     */
    public function salvar($dados, $idOriginal = null) 
    {
        $this->conn->begin_transaction();
        
        try {
            $totais = $this->calcularTotais($dados);
            
            // Se idOriginal existe, é uma revisão (Novo número com RV)
            // Se não, verifica se é um update simples (mesmo ID) ou nova proposta
            $idExistente = !empty($dados['id_proposta']) ? intval($dados['id_proposta']) : 0;
            
            if ($idOriginal) {
                $numero = $this->gerarNumeroRevisao($idOriginal);
                $id = $this->insertProposta($dados, $totais, $numero);
            } elseif ($idExistente > 0) {
                // Update simples (mantém número)
                $numero = $this->buscarNumero($idExistente);
                $this->updateProposta($idExistente, $dados, $totais);
                $id = $idExistente;
            } else {
                // Nova Proposta
                $numero = $this->gerarNumeroNovo($dados['empresa_proponente_nome'] ?? 'PROP');
                $id = $this->insertProposta($dados, $totais, $numero);
            }
            
            // Sincroniza Conteúdo Personalizado (Editor)
            $this->salvarConteudoPersonalizado($id, $dados);
            
            // Pós-processamento DOCX
            // Detecta modo DOCX pelos dados
            $isDocx = !empty($dados['docx_modelo_id']) || !empty($dados['modelo_docx']);
            if ($isDocx && !empty($dados['docx_blocos_serializado'])) {
                $blocos = json_decode($dados['docx_blocos_serializado'], true);
                if (is_array($blocos)) {
                    $this->salvarBlocosDocx($id, $blocos);
                }
            }

            $this->conn->commit();
            return $id;
            
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    public function buscarNumero($id)
    {
        $id = (int)$id;
        $sql = "SELECT numero_proposta FROM Propostas WHERE id_proposta = $id";
        $res = $this->conn->query($sql);
        $row = $res->fetch_assoc();
        return $row['numero_proposta'] ?? '';
    }

    /**
 * Busca dados completos de uma proposta pelo ID
 */
public function buscarPorId($id)
{
    $id = (int)$id;
    $sql = "SELECT p.*, c.nome_cliente, c.email as email_cliente, c.telefone as telefone_cliente, c.celular as celular_cliente,
                   s.nome as nome_servico, d.Empresa as nome_empresa,
                   p.modelo_docx,
                   p.docx_conteudo,
                   p.docx_blocos_count,
                   p.docx_ultima_edicao,
                   JSON_UNQUOTE(JSON_EXTRACT(p.docx_conteudo, '$')) as docx_blocos_array
            FROM Propostas p 
            LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
            LEFT JOIN Tipo_Servicos s ON p.id_servico = s.id_servico
            LEFT JOIN DadosEmpresa d ON p.id_criador = d.id_criador
            WHERE p.id_proposta = $id";
    $res = $this->conn->query($sql);
    if (!$res) {
        error_log("PropostaRepository::buscarPorId SQL FAIL: " . ($this->conn->error ?? 'erro desconhecido'));
        error_log("SQL: " . $sql);
        return null;
    }
    $dados = $res->fetch_assoc();
    
    if (!$dados) return null;
    
    // Processamento de DOCX JSON:
    if (!empty($dados['docx_conteudo'])) {
        $dados['docx_blocos'] = json_decode($dados['docx_conteudo'], true);
    }

    // Mapeamento de colunas DB → nomes esperados pelo renderizador HTML
    $dados['valor_proposta'] = $dados['valor_final_proposta'] ?? 0;
    $dados['valor_extenso'] = $dados['Valor_proposta_extenso'] ?? '';
    
    // Carrega conteúdos personalizados dos blocos (editor dinâmico)
    $sql2 = "SELECT block_id, conteudo_texto FROM Proposta_Conteudo_Personalizado WHERE id_proposta = $id";
    $res2 = $this->conn->query($sql2);
    if ($res2) {
        while ($row = $res2->fetch_assoc()) {
            $dados[$row['block_id'] . '_content'] = $row['conteudo_texto'];
            $dados[$row['block_id']] = $row['conteudo_texto'];
        }
    } else {
        error_log("PropostaRepository::buscarPorId WARN: falha ao buscar Proposta_Conteudo_Personalizado: " . ($this->conn->error ?? 'erro desconhecido'));
    }

    // Carrega Itens de Custo
    $dados['itens'] = [
        'salarios' => [], 'estadia' => [], 'consumo' => [], 'locacao' => [], 'admin' => []
    ];

    $res = $this->conn->query("SELECT * FROM Proposta_Salarios WHERE id_proposta = $id");
    if ($res) {
        while($row = $res->fetch_assoc()) $dados['itens']['salarios'][] = $row;
    } else {
        error_log("PropostaRepository::buscarPorId WARN: tabela Proposta_Salarios indisponível: " . ($this->conn->error ?? 'erro desconhecido'));
    }

    $res = $this->conn->query("SELECT * FROM Proposta_Estadia WHERE id_proposta = $id");
    if ($res) {
        while($row = $res->fetch_assoc()) $dados['itens']['estadia'][] = $row;
    } else {
        error_log("PropostaRepository::buscarPorId WARN: tabela Proposta_Estadia indisponível: " . ($this->conn->error ?? 'erro desconhecido'));
    }

    $res = $this->conn->query("SELECT * FROM Proposta_Consumos WHERE id_proposta = $id");
    if ($res) {
        while($row = $res->fetch_assoc()) $dados['itens']['consumo'][] = $row;
    } else {
        error_log("PropostaRepository::buscarPorId WARN: tabela Proposta_Consumos indisponível: " . ($this->conn->error ?? 'erro desconhecido'));
    }

    $res = $this->conn->query("SELECT * FROM Proposta_Locacao WHERE id_proposta = $id");
    if ($res) {
        while($row = $res->fetch_assoc()) $dados['itens']['locacao'][] = $row;
    } else {
        error_log("PropostaRepository::buscarPorId WARN: tabela Proposta_Locacao indisponível: " . ($this->conn->error ?? 'erro desconhecido'));
    }

    $res = $this->conn->query("SELECT * FROM Proposta_Custos_Administrativos WHERE id_proposta = $id");
    if ($res) {
        while($row = $res->fetch_assoc()) $dados['itens']['admin'][] = $row;
    } else {
        error_log("PropostaRepository::buscarPorId WARN: tabela Proposta_Custos_Administrativos indisponível: " . ($this->conn->error ?? 'erro desconhecido'));
    }
    
    return $dados;
}

/**
 * Retorna KPIs de propostas do usuário no mês atual
 */
public function getKPIs($idUsuario)
{
    $kpi = ['elaborada' => 0, 'enviada' => 0, 'aprovada' => 0, 'cancelada' => 0];
    $inicio_mes = date('Y-m-01 00:00:00');
    $fim_mes = date('Y-m-t 23:59:59');

    $sql = "SELECT status, count(*) as qtd 
            FROM Propostas 
            WHERE id_criador = ? 
            AND data_criacao BETWEEN ? AND ?
            GROUP BY status";
               
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('iss', $idUsuario, $inicio_mes, $fim_mes);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $st = mb_strtolower($row['status']);
        if (strpos($st, 'exclu') !== false || strpos($st, 'arquiv') !== false) continue;

        if (strpos($st, 'aprov') !== false || strpos($st, 'conclu') !== false || strpos($st, 'aceit') !== false) $kpi['aprovada'] += $row['qtd'];
        elseif (strpos($st, 'envia') !== false) $kpi['enviada'] += $row['qtd'];
        elseif (strpos($st, 'cancel') !== false || strpos($st, 'perdid') !== false) $kpi['cancelada'] += $row['qtd'];
        else $kpi['elaborada'] += $row['qtd'];
    }
    return $kpi;
}

/**
 * Lista propostas recentes do usuário
 */
public function listarRecentes($idUsuario, $limit = 50)
{
    $sql = "SELECT p.*, c.nome_cliente, d.Empresa as nome_empresa_proponente
            FROM Propostas p 
            LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
            LEFT JOIN DadosEmpresa d ON p.id_criador = d.id_criador
            WHERE p.id_criador = ? 
            ORDER BY p.data_criacao DESC LIMIT ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('ii', $idUsuario, $limit);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Atualiza apenas o status de uma proposta
 */
public function atualizarStatus($idProposta, $novoStatus)
{
    $id = (int)$idProposta;
    $status_banco = $novoStatus;
    if (strpos($novoStatus, 'Aceita') !== false) $status_banco = 'Aprovada';

    $stmt = $this->conn->prepare("UPDATE Propostas SET status = ? WHERE id_proposta = ?");
    $stmt->bind_param("si", $status_banco, $id);
    return $stmt->execute();
}

/**
 * Atualiza a data de criação de uma proposta
 */
public function atualizarData($idProposta, $novaData)
{
    $id = (int)$idProposta;
    $stmt = $this->conn->prepare("UPDATE Propostas SET data_criacao = CONCAT(?, ' ', DATE_FORMAT(data_criacao, '%H:%i:%s')) WHERE id_proposta = ?");
    $stmt->bind_param("si", $novaData, $id);
    return $stmt->execute();
}

/**
 * Lista clientes do usuário
 */
public function listarClientes($idUsuario, $limit = 500)
{
    $stmt = $this->conn->prepare("SELECT id_cliente, nome_cliente, empresa, telefone, celular, email FROM Clientes WHERE id_criador = ? ORDER BY nome_cliente ASC LIMIT ?");
    $stmt->bind_param('ii', $idUsuario, $limit);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Lista tipos de serviços disponíveis
 */
public function listarTipoServicos()
{
    $sql = "SELECT id_servico, nome, descricao FROM Tipo_Servicos ORDER BY nome ASC";
    return $this->conn->query($sql);
}

/**
 * Retorna todos os dados de apoio para o wizard de criação
 */
public function getAllLookupData($idUsuario)
{
    $data = [];
    
    // 1. Clientes
    $data['clientes'] = [];
    $res = $this->listarClientes($idUsuario);
    while($row = $res->fetch_assoc()) $data['clientes'][] = $row;
    
    // 2. Tabelas Auxiliares
    $tabelas = [
        'Tipo_Servicos' => ['id' => 'id_servico', 'nome' => 'nome', 'extra' => 'descricao'],
        'Tipo_Funcoes' => ['id' => 'id_funcao', 'nome' => 'nome', 'valor' => 'salario_base_default'],
        'Tipo_Estadia' => ['id' => 'id_estadia', 'nome' => 'nome', 'valor' => 'valor_unitario_default'],
        'Tipo_Consumo' => ['id' => 'id_consumo', 'nome' => 'nome', 'litro' => 'valor_litro_default', 'kml' => 'consumo_kml_default'],
        'Tipo_Locacao' => ['id' => 'id_locacao', 'nome' => 'nome', 'valor' => 'valor_mensal_default'],
        'Tipo_Custo_Admin' => ['id' => 'id_custo_admin', 'nome' => 'nome', 'valor' => 'valor_default'],
        'tipos_servico' => ['id' => 'id', 'nome' => 'nome', 'extra' => 'descricao', 'cor' => 'cor', 'icone' => 'icone']
    ];

    $data['arrays_js'] = [];
    foreach ($tabelas as $tbl => $cols) {
        $r = $this->conn->query("SELECT * FROM {$tbl} ORDER BY nome ASC");
        $data['arrays_js'][$tbl] = [];
        if ($r) {
            while ($row = $r->fetch_assoc()) {
                $item = ['id' => $row[$cols['id']], 'nome' => $row[$cols['nome']]];
                if (isset($cols['extra'])) $item['descricao'] = $row[$cols['extra']];
                if (isset($cols['valor'])) $item['valor'] = (float)$row[$cols['valor']];
                if (isset($cols['litro'])) $item['litro'] = (float)$row[$cols['litro']];
                if (isset($cols['kml'])) $item['kml'] = (float)$row[$cols['kml']];
                if (isset($cols['cor'])) $item['cor'] = $row[$cols['cor']];
                if (isset($cols['icone'])) $item['icone'] = $row[$cols['icone']];
                $data['arrays_js'][$tbl][] = $item;
            }
        }
    }

    // 3. Estados
    $data['estados'] = [];
    $r = $this->conn->query("SELECT sigla, nome FROM estados ORDER BY nome ASC");
    if ($r) while ($row = $r->fetch_assoc()) $data['estados'][] = $row;

    // 4. Marcas
    $data['marcas'] = [];
    $r = $this->conn->query("SELECT id_marca, id_locacao, nome_marca FROM Marcas ORDER BY nome_marca ASC");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $data['marcas'][$row['id_locacao']][] = ['id' => $row['id_marca'], 'nome' => $row['nome_marca']];
        }
    }

    // 5. Empresa (Dados e Endereço)
    $data['empresa'] = [];
    $data['empresa_endereco'] = '';
    $stmt = $this->conn->prepare("SELECT * FROM DadosEmpresa WHERE id_criador = ? LIMIT 1");
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $data['empresa'] = $row;
        $data['empresa_endereco'] = implode(', ', array_filter([$row['Endereco'], $row['Cidade'], $row['Estado']]));
    }

    return $data;
}

    private function salvarConteudoPersonalizado($id, $dados) 
    {
        foreach ($dados as $key => $value) {
            if (strpos($key, '_content') !== false) {
                $block_id = str_replace('_content', '', $key);
                $stmt = $this->conn->prepare("INSERT INTO Proposta_Conteudo_Personalizado (id_proposta, block_id, conteudo_texto) 
                                           VALUES (?, ?, ?) 
                                           ON DUPLICATE KEY UPDATE conteudo_texto = VALUES(conteudo_texto)");
                $stmt->bind_param('iss', $id, $block_id, $value);
                $stmt->execute();
            }
        }
    }

    private function calcularTotais(array $dados): array 
    {
        $items = [
            'salarios' => 0, 'estadia' => 0, 'consumos' => 0,
            'locacao' => 0, 'admin' => 0
        ];
        
        // Detecta se veio do editor dinâmico (sem itens de custo)
        $temItensCusto = !empty($dados['salario_id_funcao']) || !empty($dados['salarios'])
                       || !empty($dados['estadia_id']) || !empty($dados['estadias'])
                       || !empty($dados['consumo_id']) || !empty($dados['consumos']) 
                       || !empty($dados['locacao_id']) || !empty($dados['locacoes']) 
                       || !empty($dados['admin_id']) || !empty($dados['admin']);
        
        // Se NÃO tem itens de custo mas tem id_proposta, preserva valores do banco
        if (!$temItensCusto && !empty($dados['id_proposta'])) {
            $idProp = (int)$dados['id_proposta'];
            $res = $this->conn->query("SELECT valor_final_proposta, Valor_proposta_extenso, 
                mobilizacao_percentual, mobilizacao_valor, restante_percentual, restante_valor,
                total_custos_salarios, total_custos_estadia, total_custos_consumos, 
                total_custos_locacao, total_custos_admin,
                percentual_lucro, valor_lucro, subtotal_com_lucro, valor_desconto
                FROM Propostas WHERE id_proposta = $idProp");
            $existente = $res ? $res->fetch_assoc() : null;
            
            if ($existente && floatval($existente['valor_final_proposta']) > 0) {
                return [
                    'itens_total' => [
                        'salarios' => floatval($existente['total_custos_salarios']),
                        'estadia' => floatval($existente['total_custos_estadia']),
                        'consumos' => floatval($existente['total_custos_consumos']),
                        'locacao' => floatval($existente['total_custos_locacao']),
                        'admin' => floatval($existente['total_custos_admin'])
                    ],
                    'operacional' => floatval($existente['total_custos_salarios']) + floatval($existente['total_custos_estadia']) 
                                    + floatval($existente['total_custos_consumos']) + floatval($existente['total_custos_locacao']) 
                                    + floatval($existente['total_custos_admin']),
                    'lucro' => floatval($existente['valor_lucro']),
                    'subtotal' => floatval($existente['subtotal_com_lucro']),
                    'final' => floatval($existente['valor_final_proposta']),
                    'mobilizacao' => [
                        'mobilizacao_valor' => floatval($existente['mobilizacao_valor']),
                        'restante_percentual' => floatval($existente['restante_percentual']),
                        'restante_valor' => floatval($existente['restante_valor'])
                    ],
                    'extenso' => $existente['Valor_proposta_extenso'] ?? ''
                ];
            }
        }
        
        // Salários (Equipe)
        if (!empty($dados['salario_id_funcao'])) {
            foreach ($dados['salario_id_funcao'] as $i => $id) {
                if (!$id) continue;
                $items['salarios'] += $this->calc->calcularSalarios(
                    floatval($dados['salario_qtd'][$i] ?? 1),
                    floatval($dados['salario_valor'][$i] ?? 0),
                    floatval($dados['encargos'][$i] ?? 67),
                    intval($dados['salario_dias'][$i] ?? 1)
                );
            }
        } elseif (!empty($dados['salarios'])) {
            foreach($dados['salarios'] as $s) {
                $items['salarios'] += $this->calc->calcularSalarios(
                    floatval($s['quantidade'] ?? 1),
                    floatval($s['valor'] ?? 0),
                    floatval($s['encargos'] ?? 67),
                    intval($s['dias'] ?? 1)
                );
            }
        }
        
        // Estadia
        if (!empty($dados['estadia_id'])) {
            foreach ($dados['estadia_id'] as $i => $id) {
                if (!$id) continue;
                $items['estadia'] += $this->calc->calcularEstadia(
                    floatval($dados['estadia_qtd'][$i] ?? 1),
                    floatval($dados['estadia_valor'][$i] ?? 0),
                    intval($dados['estadia_dias'][$i] ?? 1)
                );
            }
        } elseif (!empty($dados['estadias'])) {
            foreach($dados['estadias'] as $e) {
                $items['estadia'] += $this->calc->calcularEstadia(
                    floatval($e['quantidade'] ?? 1),
                    floatval($e['valor'] ?? 0),
                    intval($e['noites'] ?? 1)
                );
            }
        }
        
        // Consumos
        if (!empty($dados['consumo_id'])) {
            foreach ($dados['consumo_id'] as $i => $id) {
                if (!$id) continue;
                $items['consumos'] += $this->calc->calcularConsumos(
                    floatval($dados['consumo_qtd'][$i] ?? 1),
                    floatval($dados['consumo_kml'][$i] ?? 10),
                    floatval($dados['consumo_litro'][$i] ?? 0),
                    floatval($dados['consumo_km_total'][$i] ?? 0)
                );
            }
        } elseif (!empty($dados['consumos'])) {
            foreach($dados['consumos'] as $c) {
                $items['consumos'] += $this->calc->calcularConsumos(
                    floatval($c['quantidade'] ?? 1),
                    floatval($c['kml'] ?? 10),
                    floatval($c['valor_litro'] ?? 0),
                    floatval($c['km'] ?? 0)
                );
            }
        }
        
        // Locação (Equipamentos)
        if (!empty($dados['locacao_id'])) {
            foreach ($dados['locacao_id'] as $i => $id) {
                if (!$id) continue;
                $items['locacao'] += $this->calc->calcularLocacao(
                    floatval($dados['locacao_qtd'][$i] ?? 1),
                    floatval($dados['locacao_valor'][$i] ?? 0),
                    intval($dados['locacao_dias'][$i] ?? 1)
                );
            }
        } elseif (!empty($dados['locacoes'])) {
            foreach($dados['locacoes'] as $l) {
                $items['locacao'] += $this->calc->calcularLocacao(
                    floatval($l['quantidade'] ?? 1),
                    floatval($l['valor'] ?? 0),
                    intval($l['dias'] ?? 1)
                );
            }
        }
        
        // Admin
        if (!empty($dados['admin_id'])) {
            foreach ($dados['admin_id'] as $i => $id) {
                if (!$id) continue;
                $items['admin'] += $this->calc->calcularAdmin(
                    floatval($dados['admin_qtd'][$i] ?? 1),
                    floatval($dados['admin_valor'][$i] ?? 0)
                );
            }
        } elseif (!empty($dados['admin'])) {
            foreach($dados['admin'] as $a) {
                $items['admin'] += $this->calc->calcularAdmin(
                    floatval($a['quantidade'] ?? 1),
                    floatval($a['valor'] ?? 0)
                );
            }
        }
        
        $operacional = array_sum($items);
        $fechamento = $this->calc->fecharProposta(
            $operacional,
            floatval($dados['percentual_lucro'] ?? 0),
            floatval($dados['valor_desconto'] ?? 0)
        );
        
        $mobilizacao = $this->calc->calcularMobilizacao(
            $fechamento['valor_final'],
            floatval($dados['mobilizacao_percentual'] ?? 30)
        );
        
        return [
            'itens_total' => $items,
            'operacional' => $operacional,
            'lucro' => $fechamento['valor_lucro'],
            'subtotal' => $fechamento['subtotal'],
            'final' => $fechamento['valor_final'],
            'mobilizacao' => $mobilizacao,
            'extenso' => $this->calc->valorPorExtenso($fechamento['valor_final'])
        ];
    }
    
    private function gerarNumeroNovo($empresa) 
    {
        $prefixo = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', trim($empresa))[0]));
        if (strlen($prefixo) < 2) $prefixo = 'PROP';
        $ano = date('Y');
        
        $stmt = $this->conn->prepare("
            SELECT numero_proposta FROM Propostas 
            WHERE numero_proposta LIKE CONCAT(?, '-', ?, '-%') 
            AND numero_proposta NOT LIKE '%-Rv%'
            ORDER BY CAST(SUBSTRING_INDEX(numero_proposta, '-', -1) AS UNSIGNED) DESC 
            LIMIT 1
        ");
        $stmt->bind_param('ss', $prefixo, $ano);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $seq = 1;
        if ($row = $res->fetch_assoc()) {
            $partes = explode('-', $row['numero_proposta']);
            $seq = intval(end($partes)) + 1;
        }
        
        return sprintf('%s-%s-%03d', $prefixo, $ano, $seq);
    }
    
    private function gerarNumeroRevisao($idOriginal) 
    {
        $stmt = $this->conn->prepare("SELECT numero_proposta FROM Propostas WHERE id_proposta = ?");
        $stmt->bind_param('i', $idOriginal);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows === 0) throw new Exception("Original não encontrada");
        
        $raiz = preg_replace('/-Rv\d+$/', '', $res->fetch_assoc()['numero_proposta']);
        $stmt = $this->conn->prepare("SELECT numero_proposta FROM Propostas WHERE numero_proposta LIKE ? ORDER BY CAST(SUBSTRING_INDEX(numero_proposta, 'Rv', -1) AS UNSIGNED) DESC LIMIT 1");
        $busca = $raiz . "-Rv%";
        $stmt->bind_param('s', $busca);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $rev = ($row = $res->fetch_assoc()) ? (intval(substr($row['numero_proposta'], strpos($row['numero_proposta'], 'Rv')+2)) + 1) : 1;
        return sprintf('%s-Rv%02d', $raiz, $rev);
    }
    
    private function insertProposta($dados, $totais, $numero) 
    {
        $isDemo = ($_SESSION['ambiente'] ?? 'producao') === 'demo' ? 1 : 0;
        
        $map = [
            'numero_proposta' => $numero,
            'id_cliente' => !empty($dados['id_cliente']) ? intval($dados['id_cliente']) : null,
            'id_criador' => $this->idCriador,
            'is_demo' => $isDemo,
            'nome_cliente_salvo' => $dados['nome_cliente_salvo'] ?? '',
            'empresa_cliente_salvo' => $dados['empresa_cliente'] ?? '',
            'email_salvo' => $dados['email_salvo'] ?? '',
            'telefone_salvo' => $dados['telefone_salvo'] ?? '',
            'celular_salvo' => $dados['celular_salvo'] ?? '',
            'whatsapp_salvo' => $dados['whatsapp_salvo'] ?? '',
            'empresa_proponente_nome' => $dados['empresa_proponente_nome'] ?? '',
            'empresa_proponente_cnpj' => $dados['empresa_proponente_cnpj'] ?? '',
            'id_servico' => !empty($dados['id_servico']) ? intval($dados['id_servico']) : null,
            'tipo_servico_id' => intval($dados['tipo_servico_id'] ?? 0),
            'contato_obra' => $dados['contato_obra'] ?? '',
            'finalidade' => $dados['finalidade'] ?? '',
            'tipo_levantamento' => $dados['tipo_levantamento'] ?? '',
            'area_obra' => $dados['area_obra'] ?? ($dados['area'] ?? ''),
            'unidade_area' => $dados['unidade_area'] ?? 'm²',
            'endereco_obra' => $dados['endereco_obra'] ?? ($dados['endereco'] ?? ''),
            'bairro_obra' => $dados['bairro_obra'] ?? ($dados['bairro'] ?? ''),
            'cidade_obra' => $dados['cidade_obra'] ?? ($dados['cidade'] ?? ''),
            'estado_obra' => $dados['estado_obra'] ?? ($dados['estado'] ?? ''),
            'prazo_execucao' => $dados['prazo_execucao'] ?? '',
            'dias_campo' => intval($dados['dias_campo'] ?? 0),
            'dias_escritorio' => intval($dados['dias_escritorio'] ?? 0),
            'total_custos_salarios' => $totais['itens_total']['salarios'],
            'total_custos_estadia' => $totais['itens_total']['estadia'],
            'total_custos_consumos' => $totais['itens_total']['consumos'],
            'total_custos_locacao' => $totais['itens_total']['locacao'],
            'total_custos_admin' => $totais['itens_total']['admin'],
            'percentual_lucro' => floatval($dados['percentual_lucro'] ?? 0),
            'valor_lucro' => $totais['lucro'],
            'subtotal_com_lucro' => $totais['subtotal'],
            'valor_desconto' => floatval($dados['valor_desconto'] ?? 0),
            'valor_final_proposta' => $totais['final'],
            'Valor_proposta_extenso' => $totais['extenso'],
            'mobilizacao_percentual' => floatval($dados['mobilizacao_percentual'] ?? 30),
            'mobilizacao_valor' => $totais['mobilizacao']['mobilizacao_valor'],
            'restante_percentual' => $totais['mobilizacao']['restante_percentual'],
            'restante_valor' => $totais['mobilizacao']['restante_valor'],
            'status' => $dados['status'] ?? 'Em elaboração',
            'tipo_terreno' => $dados['tipo_terreno'] ?? null,
            'cobertura_vegetal' => $dados['cobertura_vegetal'] ?? null,
            'acesso_local' => $dados['acesso_local'] ?? null,
            'restricoes_aereas' => $dados['restricoes_aereas'] ?? null,
            'coordenadas_gps' => $dados['coordenadas_gps'] ?? null,
            'modelo_docx' => $dados['modelo_docx'] ?? null
        ];

        // Filtra colunas que NÃO existem no banco para evitar crashes
        $available = $this->getAvailableColumns();
        $fields = [];
        $placeholders = [];
        $values = [];
        $types = "";

        foreach ($map as $col => $val) {
            if (in_array($col, $available)) {
                $fields[] = $col;
                $placeholders[] = "?";
                $values[] = $val;
                $types .= $this->getType($val);
            } else {
                error_log("PropostaRepository::insertProposta ATENÇÃO: Coluna '$col' ignorada por não existir no banco.");
            }
        }

        $sql = "INSERT INTO Propostas (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->conn->prepare($sql);
        
        // Dynamic call_user_func_array for bind_param
        $params = array_merge([$types], $values);
        $tmp = [];
        foreach($params as $i => $v) $tmp[$i] = &$params[$i];
        call_user_func_array([$stmt, 'bind_param'], $tmp);
        
        $stmt->execute();
        $id = $stmt->insert_id;
        $this->insertItens($id, $dados);
        return $id;
    }

    private function getType($val) {
        if (is_int($val)) return "i";
        if (is_float($val) || is_double($val)) return "d";
        return "s";
    }

    private function updateProposta($id, $dados, $totais) 
    {
        $map = [
            'id_cliente' => !empty($dados['id_cliente']) ? intval($dados['id_cliente']) : null,
            'nome_cliente_salvo' => $dados['nome_cliente_salvo'] ?? '',
            'empresa_cliente_salvo' => $dados['empresa_cliente'] ?? '',
            'email_salvo' => $dados['email_salvo'] ?? '',
            'telefone_salvo' => $dados['telefone_salvo'] ?? '',
            'celular_salvo' => $dados['celular_salvo'] ?? '',
            'whatsapp_salvo' => $dados['whatsapp_salvo'] ?? '',
            'id_servico' => intval($dados['id_servico']),
            'tipo_servico_id' => intval($dados['tipo_servico_id'] ?? 0),
            'contato_obra' => $dados['contato_obra'] ?? '',
            'finalidade' => $dados['finalidade'] ?? '',
            'tipo_levantamento' => $dados['tipo_levantamento'] ?? '',
            'area_obra' => $dados['area_obra'] ?? ($dados['area'] ?? ''),
            'unidade_area' => $dados['unidade_area'] ?? 'm²',
            'endereco_obra' => $dados['endereco_obra'] ?? ($dados['endereco'] ?? ''),
            'bairro_obra' => $dados['bairro_obra'] ?? ($dados['bairro'] ?? ''),
            'cidade_obra' => $dados['cidade_obra'] ?? ($dados['cidade'] ?? ''),
            'estado_obra' => $dados['estado_obra'] ?? ($dados['estado'] ?? ''),
            'prazo_execucao' => $dados['prazo_execucao'] ?? '',
            'dias_campo' => intval($dados['dias_campo'] ?? 0),
            'dias_escritorio' => intval($dados['dias_escritorio'] ?? 0),
            'status' => $dados['status'] ?? 'Em elaboração',
            'total_custos_salarios' => $totais['itens_total']['salarios'],
            'total_custos_estadia' => $totais['itens_total']['estadia'],
            'total_custos_consumos' => $totais['itens_total']['consumos'],
            'total_custos_locacao' => $totais['itens_total']['locacao'],
            'total_custos_admin' => $totais['itens_total']['admin'],
            'percentual_lucro' => floatval($dados['percentual_lucro'] ?? 0),
            'valor_lucro' => $totais['lucro'],
            'subtotal_com_lucro' => $totais['subtotal'],
            'valor_desconto' => floatval($dados['valor_desconto'] ?? 0),
            'valor_final_proposta' => $totais['final'],
            'Valor_proposta_extenso' => $totais['extenso'],
            'mobilizacao_percentual' => floatval($dados['mobilizacao_percentual'] ?? 30),
            'mobilizacao_valor' => $totais['mobilizacao']['mobilizacao_valor'],
            'restante_percentual' => $totais['mobilizacao']['restante_percentual'],
            'restante_valor' => $totais['mobilizacao']['restante_valor'],
            'tipo_terreno' => $dados['tipo_terreno'] ?? null,
            'cobertura_vegetal' => $dados['cobertura_vegetal'] ?? null,
            'acesso_local' => $dados['acesso_local'] ?? null,
            'restricoes_aereas' => $dados['restricoes_aereas'] ?? null,
            'coordenadas_gps' => $dados['coordenadas_gps'] ?? null,
            'modelo_docx' => $dados['modelo_docx'] ?? null,
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];

        $available = $this->getAvailableColumns();
        $sets = [];
        $values = [];
        $types = "";

        foreach ($map as $col => $val) {
            if (in_array($col, $available)) {
                $sets[] = "$col = ?";
                $values[] = $val;
                $types .= $this->getType($val);
            }
        }

        $sql = "UPDATE Propostas SET " . implode(', ', $sets) . " WHERE id_proposta = ?";
        $values[] = (int)$id;
        $types .= "i";

        $stmt = $this->conn->prepare($sql);
        $params = array_merge([$types], $values);
        $tmp = [];
        foreach($params as $i => $v) $tmp[$i] = &$params[$i];
        call_user_func_array([$stmt, 'bind_param'], $tmp);
        
        $stmt->execute();
        
        // Limpeza de itens permanece igual
        $this->conn->query("DELETE FROM Proposta_Salarios WHERE id_proposta = $id");
        $this->conn->query("DELETE FROM Proposta_Estadia WHERE id_proposta = $id");
        $this->conn->query("DELETE FROM Proposta_Consumos WHERE id_proposta = $id");
        $this->conn->query("DELETE FROM Proposta_Locacao WHERE id_proposta = $id");
        $this->conn->query("DELETE FROM Proposta_Custos_Administrativos WHERE id_proposta = $id");
        $this->insertItens($id, $dados);
    }
    
    private function insertItens($id, $dados) 
    {
        // 1. Salários
        if (!empty($dados['salario_id_funcao'])) {
            $stmt = $this->conn->prepare("INSERT INTO Proposta_Salarios (id_proposta, id_funcao, funcao, quantidade, salario_base, fator_encargos, dias) VALUES (?,?,?,?,?,?,?)");
            foreach($dados['salario_id_funcao'] as $i => $idFuncao) {
                if (!$idFuncao) continue;
                $f = 1 + (floatval($dados['encargos'][$i] ?? 67) / 100);
                $nome_s = $dados['salario_nome'][$i];
                $qtd_s = $dados['salario_qtd'][$i];
                $val_s = $dados['salario_valor'][$i];
                $dias_s = $dados['salario_dias'][$i];
                $stmt->bind_param('iisiddi', $id, $idFuncao, $nome_s, $qtd_s, $val_s, $f, $dias_s);
                $stmt->execute();
            }
        } elseif (!empty($dados['salarios'])) {
            $stmt = $this->conn->prepare("INSERT INTO Proposta_Salarios (id_proposta, id_funcao, funcao, quantidade, salario_base, fator_encargos, dias) VALUES (?,?,?,?,?,?,?)");
            foreach($dados['salarios'] as $s) {
                $idF = intval($s['funcao']);
                if (!$idF) continue;
                $nome = $s['funcao_nome'] ?? 'Profissional';
                $f = 1 + (floatval($s['encargos'] ?? 67) / 100);
                $stmt->bind_param('iisiddi', $id, $idF, $nome, $s['quantidade'], $s['valor'], $f, $s['dias']);
                $stmt->execute();
            }
        }

        // 2. Estadia
        if (!empty($dados['estadia_id'])) {
            $stmt = $this->conn->prepare("INSERT INTO Proposta_Estadia (id_proposta, id_estadia, tipo, quantidade, valor_unitario, dias) VALUES (?,?,?,?,?,?)");
            foreach($dados['estadia_id'] as $i => $idE) {
                if (!$idE) continue;
                $nome_e = $dados['estadia_nome'][$i];
                $qtd_e = $dados['estadia_qtd'][$i];
                $val_e = $dados['estadia_valor'][$i];
                $dias_e = $dados['estadia_dias'][$i];
                $stmt->bind_param('iisddi', $id, $idE, $nome_e, $qtd_e, $val_e, $dias_e);
                $stmt->execute();
            }
        } elseif (!empty($dados['estadias'])) {
            $stmt = $this->conn->prepare("INSERT INTO Proposta_Estadia (id_proposta, id_estadia, tipo, quantidade, valor_unitario, dias) VALUES (?,?,?,?,?,?)");
            foreach($dados['estadias'] as $e) {
                $idE = intval($e['tipo']);
                if (!$idE) continue;
                $nome = $e['tipo_nome'] ?? 'Estadia';
                $stmt->bind_param('iisddi', $id, $idE, $nome, $e['quantidade'], $e['valor'], $e['noites']);
                $stmt->execute();
            }
        }

        // 3. Consumos
        if (!empty($dados['consumo_id'])) {
            $stmt = $this->conn->prepare("INSERT INTO Proposta_Consumos (id_proposta, id_consumo, tipo, quantidade, consumo_kml, valor_litro, km_total) VALUES (?,?,?,?,?,?,?)");
            foreach($dados['consumo_id'] as $i => $idC) {
                if (!$idC) continue;
                $nome_c = $dados['consumo_nome'][$i];
                $qtd_c = $dados['consumo_qtd'][$i];
                $kml_c = $dados['consumo_kml'][$i];
                $val_c = $dados['consumo_litro'][$i];
                $km_c = $dados['consumo_km_total'][$i];
                $stmt->bind_param('iisdddd', $id, $idC, $nome_c, $qtd_c, $kml_c, $val_c, $km_c);
                $stmt->execute();
            }
        } elseif (!empty($dados['consumos'])) {
            $stmt = $this->conn->prepare("INSERT INTO Proposta_Consumos (id_proposta, id_consumo, tipo, quantidade, consumo_kml, valor_litro, km_total) VALUES (?,?,?,?,?,?,?)");
            foreach($dados['consumos'] as $c) {
                $idC = intval($c['tipo']);
                if (!$idC) continue;
                $nome = $c['tipo_nome'] ?? 'Combustível';
                $stmt->bind_param('iisdddd', $id, $idC, $nome, $c['quantidade'], $c['kml'], $c['valor_litro'], $c['km']);
                $stmt->execute();
            }
        }

        // 4. Locação
        if (!empty($dados['locacao_id'])) {
            $stmt = $this->conn->prepare("INSERT INTO Proposta_Locacao (id_proposta, id_locacao, id_marca, quantidade, valor_mensal, dias) VALUES (?,?,?,?,?,?)");
            foreach($dados['locacao_id'] as $i => $idL) {
                if (!$idL) continue;
                $idMarca = !empty($dados['locacao_id_marca'][$i]) ? intval($dados['locacao_id_marca'][$i]) : null;
                $qtd_l = $dados['locacao_qtd'][$i];
                $val_l = $dados['locacao_valor'][$i];
                $dias_l = $dados['locacao_dias'][$i];
                $stmt->bind_param('iiiidi', $id, $idL, $idMarca, $qtd_l, $val_l, $dias_l);
                $stmt->execute();
            }
        } elseif (!empty($dados['locacoes'])) {
            $stmt = $this->conn->prepare("INSERT INTO Proposta_Locacao (id_proposta, id_locacao, id_marca, quantidade, valor_mensal, dias) VALUES (?,?,?,?,?,?)");
            foreach($dados['locacoes'] as $l) {
                $idL = intval($l['tipo']);
                if (!$idL) continue;
                $idM = !empty($l['marca']) ? intval($l['marca']) : null;
                $stmt->bind_param('iiiidi', $id, $idL, $idM, $l['quantidade'], $l['valor'], $l['dias']);
                $stmt->execute();
            }
        }

        // 5. Admin
        if (!empty($dados['admin_id'])) {
            $stmt = $this->conn->prepare("INSERT INTO Proposta_Custos_Administrativos (id_proposta, id_custo_admin, tipo, quantidade, valor) VALUES (?,?,?,?,?)");
            foreach($dados['admin_id'] as $i => $idA) {
                if (!$idA) continue;
                $nome_a = $dados['admin_nome'][$i];
                $qtd_a = $dados['admin_qtd'][$i];
                $val_a = $dados['admin_valor'][$i];
                $stmt->bind_param('iisdd', $id, $idA, $nome_a, $qtd_a, $val_a);
                $stmt->execute();
            }
        } elseif (!empty($dados['admin'])) {
            $stmt = $this->conn->prepare("INSERT INTO Proposta_Custos_Administrativos (id_proposta, id_custo_admin, tipo, quantidade, valor) VALUES (?,?,?,?,?)");
            foreach($dados['admin'] as $a) {
                $idA = intval($a['tipo']);
                if (!$idA) continue;
                $nome = $a['tipo_nome'] ?? 'Admin';
                $stmt->bind_param('iisdd', $id, $idA, $nome, $a['quantidade'], $a['valor']);
                $stmt->execute();
            }
        }
    }

    /**
     * Remove uma proposta e seu arquivo DOCX físico
     */
    public function deletar($id, $idUsuario)
    {
        // 1. Busca metadados para apagar arquivo físico
        $stmt = $this->conn->prepare("SELECT numero_proposta, empresa_proponente_nome FROM Propostas WHERE id_proposta = ? AND id_criador = ?");
        $stmt->bind_param('ii', $id, $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();

        if ($row) {
            // Tenta apagar arquivo .docx
            $nomeEmpresa = trim(explode(' ', $row['empresa_proponente_nome'])[0]);
            $nomeLimpo = preg_replace('/[^a-zA-Z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $nomeEmpresa));
            $partes = explode('-', $row['numero_proposta']);
            $seq = end($partes);
            $ano = $partes[1] ?? date('Y');
            
            $arquivo = "{$nomeLimpo}-{$ano}-{$seq}.docx";
            $caminho = __DIR__ . '/propostas_emitidas/' . $arquivo;
            if (file_exists($caminho)) @unlink($caminho);

            // 2. Remove do banco (confia no ON DELETE CASCADE das tabelas filhas)
            $del = $this->conn->prepare("DELETE FROM Propostas WHERE id_proposta = ? AND id_criador = ?");
            $del->bind_param('ii', $id, $idUsuario);
            return $del->execute();
        }

        return false;
    }

    /**
     * Clona uma proposta e todos os seus itens de custo
     */
    public function duplicar($idOrigem, $idUsuario)
    {
        // 1. Busca dados da proposta original
        $sql = "SELECT * FROM Propostas WHERE id_proposta = ? AND id_criador = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $idOrigem, $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $origem = $res->fetch_assoc();

        if (!$origem) return false;

        // 2. Gera novo número
        $novoNumero = $this->gerarNumero($origem['empresa_proponente_nome']);

        $this->conn->begin_transaction();
        try {
            // 3. Insere nova proposta (Cópia)
            $sqlInsert = "INSERT INTO Propostas (
                numero_proposta, id_cliente, nome_cliente_salvo, email_salvo, telefone_salvo, celular_salvo, whatsapp_salvo,
                empresa_proponente_nome, empresa_proponente_cnpj, empresa_proponente_endereco, empresa_proponente_cidade, empresa_proponente_estado,
                empresa_proponente_banco, empresa_proponente_agencia, empresa_proponente_conta, empresa_proponente_pix,
                id_servico, contato_obra, finalidade, tipo_levantamento, area_obra, endereco_obra, bairro_obra, cidade_obra, estado_obra,
                prazo_execucao, dias_campo, dias_escritorio, status,
                total_custos_salarios, total_custos_estadia, total_custos_consumos, total_custos_locacao, total_custos_admin,
                percentual_lucro, valor_lucro, subtotal_com_lucro, valor_desconto, valor_final_proposta, Valor_proposta_extenso,
                mobilizacao_percentual, mobilizacao_valor, restante_percentual, restante_valor,
                id_criador, is_demo, data_criacao
            ) SELECT 
                ?, id_cliente, nome_cliente_salvo, email_salvo, telefone_salvo, celular_salvo, whatsapp_salvo,
                empresa_proponente_nome, empresa_proponente_cnpj, empresa_proponente_endereco, empresa_proponente_cidade, empresa_proponente_estado,
                empresa_proponente_banco, empresa_proponente_agencia, empresa_proponente_conta, empresa_proponente_pix,
                id_servico, contato_obra, finalidade, tipo_levantamento, area_obra, endereco_obra, bairro_obra, cidade_obra, estado_obra,
                prazo_execucao, dias_campo, dias_escritorio, 'Em elaboração',
                total_custos_salarios, total_custos_estadia, total_custos_consumos, total_custos_locacao, total_custos_admin,
                percentual_lucro, valor_lucro, subtotal_com_lucro, valor_desconto, valor_final_proposta, Valor_proposta_extenso,
                mobilizacao_percentual, mobilizacao_valor, restante_percentual, restante_valor,
                id_criador, is_demo, NOW()
            FROM Propostas WHERE id_proposta = ?";
            
            $stmtIns = $this->conn->prepare($sqlInsert);
            $stmtIns->bind_param('si', $novoNumero, $idOrigem);
            $stmtIns->execute();
            $idNovo = $this->conn->insert_id;

            // 4. Copia Itens Relacionados
            $tabelasItens = [
                'Proposta_Salarios' => 'id_funcao, funcao, quantidade, salario_base, fator_encargos, dias',
                'Proposta_Estadia' => 'id_estadia, tipo, quantidade, valor_unitario, dias',
                'Proposta_Consumos' => 'id_consumo, tipo, quantidade, consumo_kml, valor_litro, km_total',
                'Proposta_Locacao' => 'id_locacao, id_marca, quantidade, valor_mensal, dias',
                'Proposta_Custos_Administrativos' => 'id_custo_admin, tipo, quantidade, valor'
            ];

            foreach ($tabelasItens as $tab => $campos) {
                $this->conn->query("INSERT INTO $tab (id_proposta, $campos) SELECT $idNovo, $campos FROM $tab WHERE id_proposta = $idOrigem");
            }

            $this->conn->commit();
            return $idNovo;

        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // ==========================================
    // MÉTODOS DOCX V3.0
    // ==========================================

    /**
     * Associa um modelo DOCX a uma proposta
     */
    public function associarModeloDocx(int $idProposta, string $modeloId): bool {
        $sql = "UPDATE propostas SET 
                modelo_docx = :modelo,
                data_atualizacao = NOW()
                WHERE id_proposta = :id";
        
        $stmt = $this->conn->prepare('UPDATE Propostas SET modelo_docx = ?, data_atualizacao = NOW() WHERE id_proposta = ?');
        $stmt->bind_param('si', $modeloId, $idProposta);
        return $stmt->execute();
    }
    
    /**
     * Salva ou atualiza conteúdo de blocos DOCX
     */
    public function salvarBlocosDocx(int $idProposta, array $blocos): bool {
        // Salvar em campo JSON na tabela propostas
        $jsonBlocos = json_encode($blocos);
        $count = count($blocos);
        
        $sql = "UPDATE Propostas SET 
                docx_conteudo = ?,
                docx_blocos_count = ?,
                data_atualizacao = NOW()
                WHERE id_proposta = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sii', $jsonBlocos, $count, $idProposta);
        return $stmt->execute();
    }
    
    /**
     * Busca blocos DOCX salvos de uma proposta
     */
    public function buscarBlocosDocx(int $idProposta): ?array {
        $sql = "SELECT docx_conteudo, modelo_docx 
                FROM Propostas 
                WHERE id_proposta = ? AND docx_conteudo IS NOT NULL";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idProposta);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        
        if ($row && $row['docx_conteudo']) {
            return [
                'modelo' => $row['modelo_docx'],
                'blocos' => json_decode($row['docx_conteudo'], true)
            ];
        }
        
        return null;
    }
}
