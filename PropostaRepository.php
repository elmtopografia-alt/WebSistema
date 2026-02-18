<?php
/**
 * PropostaRepository.php
 * Persistência centralizada e gerenciamento de revisões
 */

require_once __DIR__ . '/ConnectionManager.php';
require_once __DIR__ . '/CalculadoraService.php';

class PropostaRepository 
{
    private $conn;
    private $calc;
    private $idCriador;
    
    public function __construct($conn = null) 
    {
        $this->conn = $conn ?? ConnectionManager::get();
        $this->calc = new CalculadoraService();
        
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $this->idCriador = $_SESSION['usuario_id'] ?? 0;
        
        if ($this->idCriador === 0) {
            throw new Exception('Usuário não autenticado');
        }
    }
    
    /**
     * Salva proposta (nova ou revisão)
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
    $sql = "SELECT p.*, c.nome_cliente, c.email as email_cliente, c.telefone as telefone_cliente, c.celular as celular_cliente 
            FROM Propostas p 
            LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
            WHERE p.id_proposta = $id";
    $res = $this->conn->query($sql);
    $dados = $res ? $res->fetch_assoc() : null;
    
    if (!$dados) return null;
    
    // Mapeamento de colunas DB → nomes esperados pelo renderizador HTML
    $dados['valor_proposta'] = $dados['valor_final_proposta'] ?? 0;
    $dados['valor_extenso'] = $dados['Valor_proposta_extenso'] ?? '';
    
    // Carrega conteúdos personalizados dos blocos (editor dinâmico)
    $sql2 = "SELECT block_id, conteudo_texto FROM Proposta_Conteudo_Personalizado WHERE id_proposta = $id";
    $res2 = $this->conn->query($sql2);
    if ($res2) {
        while ($row = $res2->fetch_assoc()) {
            $dados[$row['block_id'] . '_content'] = $row['conteudo_texto'];
            // Alias para facilitar no DOCX (sem o sufixo _content no contexto)
            $dados[$row['block_id']] = $row['conteudo_texto'];
        }
    }
    
    return $dados;
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
        $temItensCusto = !empty($dados['salario_id_funcao']) || !empty($dados['estadia_id']) 
                       || !empty($dados['consumo_id']) || !empty($dados['locacao_id']) 
                       || !empty($dados['admin_id']);
        
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
        }
        
        if (!empty($dados['estadia_id'])) {
            foreach ($dados['estadia_id'] as $i => $id) {
                if (!$id) continue;
                $items['estadia'] += $this->calc->calcularEstadia(
                    floatval($dados['estadia_qtd'][$i] ?? 1),
                    floatval($dados['estadia_valor'][$i] ?? 0),
                    intval($dados['estadia_dias'][$i] ?? 1)
                );
            }
        }
        
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
        }
        
        if (!empty($dados['locacao_id'])) {
            foreach ($dados['locacao_id'] as $i => $id) {
                if (!$id) continue;
                $items['locacao'] += $this->calc->calcularLocacao(
                    floatval($dados['locacao_qtd'][$i] ?? 1),
                    floatval($dados['locacao_valor'][$i] ?? 0),
                    intval($dados['locacao_dias'][$i] ?? 1)
                );
            }
        }
        
        if (!empty($dados['admin_id'])) {
            foreach ($dados['admin_id'] as $i => $id) {
                if (!$id) continue;
                $items['admin'] += $this->calc->calcularAdmin(
                    floatval($dados['admin_qtd'][$i] ?? 1),
                    floatval($dados['admin_valor'][$i] ?? 0)
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
        $sql = "INSERT INTO Propostas (
            numero_proposta, id_cliente, id_criador, is_demo,
            nome_cliente_salvo, empresa_cliente_salvo, email_salvo, telefone_salvo, celular_salvo, whatsapp_salvo,
            empresa_proponente_nome, empresa_proponente_cnpj,
            id_servico, tipo_servico_id, contato_obra, finalidade, tipo_levantamento,
            area_obra, unidade_area, endereco_obra, bairro_obra, cidade_obra, estado_obra,
            prazo_execucao, dias_campo, dias_escritorio,
            total_custos_salarios, total_custos_estadia, total_custos_consumos, 
            total_custos_locacao, total_custos_admin,
            percentual_lucro, valor_lucro, subtotal_com_lucro, 
            valor_desconto, valor_final_proposta, Valor_proposta_extenso,
            mobilizacao_percentual, mobilizacao_valor, 
            restante_percentual, restante_valor, status,
            tipo_terreno, cobertura_vegetal, acesso_local, restricoes_aereas, coordenadas_gps,
            modelo_docx
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        
        $stmt = $this->conn->prepare($sql);
        $isDemo = ($_SESSION['ambiente'] ?? 'producao') === 'demo' ? 1 : 0;

        // Extração de variáveis para bind_param (PHP 7.4 exige referências de variáveis)
        $id_cliente = !empty($dados['id_cliente']) ? intval($dados['id_cliente']) : null;
        $id_criador = $this->idCriador;
        $nome_cliente_salvo = $dados['nome_cliente_salvo'] ?? '';
        $empresa_cliente = $dados['empresa_cliente'] ?? '';
        $email_salvo = $dados['email_salvo'] ?? '';
        $telefone_salvo = $dados['telefone_salvo'] ?? '';
        $celular_salvo = $dados['celular_salvo'] ?? '';
        $whatsapp_salvo = $dados['whatsapp_salvo'] ?? '';
        $empresa_proponente_nome = $dados['empresa_proponente_nome'] ?? '';
        $empresa_proponente_cnpj = $dados['empresa_proponente_cnpj'] ?? '';
        $id_servico = !empty($dados['id_servico']) ? intval($dados['id_servico']) : null;
        $tipo_servico_id = intval($dados['tipo_servico_id'] ?? 0);
        $contato_obra = $dados['contato_obra'] ?? '';
        $finalidade = $dados['finalidade'] ?? '';
        $tipo_levantamento = $dados['tipo_levantamento'] ?? '';
        $area_obra = $dados['area_obra'] ?? ($dados['area'] ?? '');
        $unidade_area = $dados['unidade_area'] ?? 'm²';
        $endereco_obra = $dados['endereco_obra'] ?? ($dados['endereco'] ?? '');
        $bairro_obra = $dados['bairro_obra'] ?? ($dados['bairro'] ?? '');
        $cidade_obra = $dados['cidade_obra'] ?? ($dados['cidade'] ?? '');
        $estado_obra = $dados['estado_obra'] ?? ($dados['estado'] ?? '');
        $prazo_execucao = $dados['prazo_execucao'] ?? '';
        $dias_campo = intval($dados['dias_campo'] ?? 0);
        $dias_escritorio = intval($dados['dias_escritorio'] ?? 0);
        $total_salarios = $totais['itens_total']['salarios'];
        $total_estadia = $totais['itens_total']['estadia'];
        $total_consumos = $totais['itens_total']['consumos'];
        $total_locacao = $totais['itens_total']['locacao'];
        $total_admin = $totais['itens_total']['admin'];
        $perc_lucro = floatval($dados['percentual_lucro'] ?? 0);
        $val_lucro = $totais['lucro'];
        $subtotal = $totais['subtotal'];
        $val_desconto = floatval($dados['valor_desconto'] ?? 0);
        $val_final = $totais['final'];
        $val_extenso = $totais['extenso'];
        $mob_perc = floatval($dados['mobilizacao_percentual'] ?? 30);
        $mob_val = $totais['mobilizacao']['mobilizacao_valor'];
        $rest_perc = $totais['mobilizacao']['restante_percentual'];
        $rest_val = $totais['mobilizacao']['restante_valor'];
        $status = $dados['status'] ?? 'Em elaboração';
        $tipo_terreno = $dados['tipo_terreno'] ?? null;
        $veg = $dados['cobertura_vegetal'] ?? null;
        $acesso = $dados['acesso_local'] ?? null;
        $restr = $dados['restricoes_aereas'] ?? null;
        $gps = $dados['coordenadas_gps'] ?? null;
        $modelo_docx = $dados['modelo_docx'] ?? null;

        $stmt->bind_param('siiissssssssiissssssssssiiddddddddddsddddsssssss',
            $numero, $id_cliente, $id_criador, $isDemo,
            $nome_cliente_salvo, $empresa_cliente, $email_salvo, $telefone_salvo, $celular_salvo, $whatsapp_salvo,
            $empresa_proponente_nome, $empresa_proponente_cnpj,
            $id_servico, $tipo_servico_id, $contato_obra, $finalidade, $tipo_levantamento,
            $area_obra, $unidade_area, $endereco_obra, $bairro_obra, $cidade_obra, $estado_obra,
            $prazo_execucao, $dias_campo, $dias_escritorio,
            $total_salarios, $total_estadia, $total_consumos, $total_locacao, $total_admin,
            $perc_lucro, $val_lucro, $subtotal, $val_desconto, $val_final, $val_extenso,
            $mob_perc, $mob_val, $rest_perc, $rest_val,
            $status, $tipo_terreno, $veg, $acesso, $restr, $gps,
            $modelo_docx
        );
        
        $stmt->execute();
        $id = $stmt->insert_id;
        $this->insertItens($id, $dados);
        return $id;
    }

    private function updateProposta($id, $dados, $totais) 
    {
        $sql = "UPDATE Propostas SET 
            id_cliente=?, nome_cliente_salvo=?, empresa_cliente_salvo=?, email_salvo=?, telefone_salvo=?, celular_salvo=?, whatsapp_salvo=?,
            id_servico=?, tipo_servico_id=?, contato_obra=?, finalidade=?, tipo_levantamento=?, area_obra=?, unidade_area=?, endereco_obra=?, bairro_obra=?, cidade_obra=?, estado_obra=?,
            prazo_execucao=?, dias_campo=?, dias_escritorio=?, status=?,
            total_custos_salarios=?, total_custos_estadia=?, total_custos_consumos=?, total_custos_locacao=?, total_custos_admin=?,
            percentual_lucro=?, valor_lucro=?, subtotal_com_lucro=?, valor_desconto=?, valor_final_proposta=?, Valor_proposta_extenso=?,
            mobilizacao_percentual=?, mobilizacao_valor=?, restante_percentual=?, restante_valor=?,
            tipo_terreno=?, cobertura_vegetal=?, acesso_local=?, restricoes_aereas=?, coordenadas_gps=?, 
            modelo_docx=?, data_atualizacao=NOW()
            WHERE id_proposta=?";
        
        $stmt = $this->conn->prepare($sql);

        // Extração de variáveis para bind_param
        $id_cliente = !empty($dados['id_cliente']) ? intval($dados['id_cliente']) : null;
        $nome_cliente_salvo = $dados['nome_cliente_salvo'] ?? '';
        $empresa_cliente = $dados['empresa_cliente'] ?? '';
        $email_salvo = $dados['email_salvo'] ?? '';
        $telefone_salvo = $dados['telefone_salvo'] ?? '';
        $celular_salvo = $dados['celular_salvo'] ?? '';
        $whatsapp_salvo = $dados['whatsapp_salvo'] ?? '';
        $id_servico = intval($dados['id_servico']);
        $tipo_servico_id = intval($dados['tipo_servico_id'] ?? 0);
        $contato_obra = $dados['contato_obra'] ?? '';
        $finalidade = $dados['finalidade'] ?? '';
        $tipo_levantamento = $dados['tipo_levantamento'] ?? '';
        $area_obra = $dados['area_obra'] ?? ($dados['area'] ?? '');
        $unidade_area = $dados['unidade_area'] ?? 'm²';
        $endereco_obra = $dados['endereco_obra'] ?? ($dados['endereco'] ?? '');
        $bairro_obra = $dados['bairro_obra'] ?? ($dados['bairro'] ?? '');
        $cidade_obra = $dados['cidade_obra'] ?? ($dados['cidade'] ?? '');
        $estado_obra = $dados['estado_obra'] ?? ($dados['estado'] ?? '');
        $prazo_execucao = $dados['prazo_execucao'] ?? '';
        $dias_campo = intval($dados['dias_campo'] ?? 0);
        $dias_escritorio = intval($dados['dias_escritorio'] ?? 0);
        $status = $dados['status'] ?? 'Em elaboração';
        $total_salarios = $totais['itens_total']['salarios'];
        $total_estadia = $totais['itens_total']['estadia'];
        $total_consumos = $totais['itens_total']['consumos'];
        $total_locacao = $totais['itens_total']['locacao'];
        $total_admin = $totais['itens_total']['admin'];
        $perc_lucro = floatval($dados['percentual_lucro'] ?? 0);
        $val_lucro = $totais['lucro'];
        $subtotal = $totais['subtotal'];
        $val_desconto = floatval($dados['valor_desconto'] ?? 0);
        $val_final = $totais['final'];
        $val_extenso = $totais['extenso'];
        $mob_perc = floatval($dados['mobilizacao_percentual'] ?? 30);
        $mob_val = $totais['mobilizacao']['mobilizacao_valor'];
        $rest_perc = $totais['mobilizacao']['restante_percentual'];
        $rest_val = $totais['mobilizacao']['restante_valor'];
        $tipo_terreno = $dados['tipo_terreno'] ?? null;
        $veg = $dados['cobertura_vegetal'] ?? null;
        $acesso = $dados['acesso_local'] ?? null;
        $restr = $dados['restricoes_aereas'] ?? null;
        $gps = $dados['coordenadas_gps'] ?? null;
        $modelo_docx = $dados['modelo_docx'] ?? null;

        $stmt->bind_param('issssssiissssssssssiisddddddddddsddddsssssssi',
            $id_cliente, $nome_cliente_salvo, $empresa_cliente, $email_salvo, $telefone_salvo, $celular_salvo, $whatsapp_salvo,
            $id_servico, $tipo_servico_id, $contato_obra, $finalidade, $tipo_levantamento,
            $area_obra, $unidade_area, $endereco_obra, $bairro_obra, $cidade_obra, $estado_obra,
            $prazo_execucao, $dias_campo, $dias_escritorio,
            $status,
            $total_salarios, $total_estadia, $total_consumos, $total_locacao, $total_admin,
            $perc_lucro, $val_lucro, $subtotal, $val_desconto, $val_final, $val_extenso,
            $mob_perc, $mob_val, $rest_perc, $rest_val,
            $tipo_terreno, $veg, $acesso, $restr, $gps,
            $modelo_docx,
            $id
        );
        $stmt->execute();
        
        // Limpa e reinseri itens
        $this->conn->query("DELETE FROM Proposta_Salarios WHERE id_proposta = $id");
        $this->conn->query("DELETE FROM Proposta_Estadia WHERE id_proposta = $id");
        $this->conn->query("DELETE FROM Proposta_Consumos WHERE id_proposta = $id");
        $this->conn->query("DELETE FROM Proposta_Locacao WHERE id_proposta = $id");
        $this->conn->query("DELETE FROM Proposta_Custos_Administrativos WHERE id_proposta = $id");
        $this->insertItens($id, $dados);
    }
    
    private function insertItens($id, $dados) 
    {
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
        }
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
        }
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
        }
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
        }
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
        }
    }
}
