<?php
/**
 * RESOLVEDOR DE CHAVES DO SISTEMA v3.1
 * Corrige: Variáveis de cliente, valor extenso (não multiplica por mil)
 */

class ResolvedorChavesSistema
{
    private ;
    private ;
    
    public function __construct()
    {
        ->conn = ;
    }
    
    /**
     * Resolve todas as variáveis necessárias para o modelo
     */
    public function resolver(, ,  = [])
    {
        ->idUsuario = ;
         = [];
        
        // Dados da empresa (do usuário logado)
         = ->getDadosEmpresa();
        
        // Dados do cliente (se houver id_cliente nos dados manuais)
         = [];
        if (!empty(['id_cliente'])) {
             = ->getDadosCliente(['id_cliente']);
        }
        
        foreach ( as ) {
            [] = ->resolverVariavel(, , , );
        }
        
        return ;
    }
    
    private function resolverVariavel(, , , )
    {
        // Prioridade 1: Dados manuais (do formulário)
        if (isset([]) && !empty([])) {
            return ->formatarValor(, []);
        }
        
        // Mapeamento de aliases para dados do cliente
         = [
            'nome_cliente_salvo' => 'nome',
            'nome_cliente' => 'nome',
            'cliente_nome' => 'nome',
            'email_salvo' => 'email',
            'email_cliente' => 'email',
            'cliente_email' => 'email',
            'telefone_salvo' => 'telefone',
            'telefone_cliente' => 'telefone',
            'cliente_telefone' => 'telefone',
            'celular_salvo' => 'celular',
            'celular_cliente' => 'celular',
            'cliente_celular' => 'celular',
            'whatsapp_salvo' => 'whatsapp',
            'whatsapp_cliente' => 'whatsapp',
            'cliente_whatsapp' => 'whatsapp',
        ];
        
        // Se é uma variável de cliente
        if (isset([])) {
             = [];
            return [] ?? [{}];
        }
        
        // Mapeamento de aliases para dados da obra/local
         = [
            'endereco_obra' => 'endereco',
            'obra_endereco' => 'endereco',
            'bairro_obra' => 'bairro',
            'obra_bairro' => 'bairro',
            'cidade_obra' => 'cidade',
            'obra_cidade' => 'cidade',
            'estado_obra' => 'estado',
            'obra_estado' => 'estado',
            'uf_obra' => 'estado',
            'area_obra' => 'area',
            'AreaEstimada' => 'area',
        ];
        
        if (isset([])) {
             = [];
            return [] ?? [] ?? [{}];
        }
        
        // Variáveis de empresa
         = [
            'Empresa' => 'nome',
            'empresa' => 'nome',
            'nome_empresa' => 'nome',
            'CNPJ' => 'cnpj',
            'cnpj' => 'cnpj',
            'Banco' => 'banco',
            'banco' => 'banco',
            'Agencia' => 'agencia',
            'agencia' => 'agencia',
            'Conta' => 'conta',
            'conta' => 'conta',
            'PIX' => 'pix',
            'pix' => 'pix',
            'whatsapp' => 'whatsapp',
        ];
        
        if (isset([])) {
             = [];
            return [] ?? [{}];
        }
        
        // Valores financeiros - CORREÇÃO IMPORTANTE AQUI
        if ( === 'ValorProposta' ||  === 'valor_proposta') {
             = ['valor_final'] ?? ['valor'] ?? 0;
            return ->formatarMoeda();
        }
        
        if ( === 'ValorExtenso') {
             = ['valor_final'] ?? ['valor'] ?? 0;
            return ->valorPorExtenso();
        }
        
        // Data por extenso
        if ( === 'DExrenso' ||  === 'data_extenso' ||  === 'DataExtenso') {
            return ->dataPorExtenso();
        }
        
        if ( === 'Cidade') {
            return ['cidade'] ?? '[Cidade]';
        }
        
        // Número da proposta
        if ( === 'numero_proposta') {
            return ['numero'] ?? ['id'] ?? date('Y') . '001';
        }
        
        // Campos específicos do drone/topo
         = [
            'TipoTerreno', 'CoberturaVegetal', 'AcessoLocal', 
            'RestricoesAereas', 'Drone', 'GPS', 'Estacao_Total', 
            'Veiculo', 'finalidade', 'unidade_area'
        ];
        
        if (in_array(, )) {
            return [strtolower()] ?? [] ?? [{}];
        }
        
        // Pagamentos
        if ( === 'mobilizacao_percentual') {
            return ['mobilizacao_percentual'] ?? '30';
        }
        if ( === 'mobilizacao_valor') {
             = ['valor_final'] ?? 0;
             = ['mobilizacao_percentual'] ?? 30;
            return ->formatarMoeda( * ( / 100));
        }
        if ( === 'restante_percentual') {
             = ['mobilizacao_percentual'] ?? 30;
            return (100 - );
        }
        if ( === 'restante_valor') {
             = ['valor_final'] ?? 0;
             = ['mobilizacao_percentual'] ?? 30;
            return ->formatarMoeda( * ((100 - ) / 100));
        }
        
        // Fallback
        return [{}];
    }
    
    // CORREÇÃO PARA MYSQLI (Sem PDO)
    private function getDadosEmpresa()
    {
        try {
            // Verifica o nome real da tabela (pode ser  empresas ou Empresas com letra maiúscula na query normal do sistema)
             = ->conn->prepare(
 SELECT e.* 
 FROM Usuarios u
 LEFT JOIN Empresas e ON u.id_empresa = e.id_empresa 
 WHERE u.id_usuario = ?
 );
            if (!) { error_log(GetDadosEmpresa PREPARE FAIL:  . ->conn->error); return []; }
            ->bind_param(i, );
            ->execute();
             = ->get_result();
            return ->fetch_assoc() ?: [];
        } catch (Exception ) {
            error_log(Erro ao buscar empresa:  . ->getMessage());
            return [];
        }
    }
    
    private function getDadosCliente()
    {
        try {
             = ->conn->prepare(
 SELECT nome_cliente as nome, email, telefone, celular, whatsapp, 
 endereco, bairro, cidade, uf as estado 
 FROM Clientes 
 WHERE id_cliente = ?
 );
            if (!) return [];
            ->bind_param(i, );
            ->execute();
             = ->get_result();
            return ->fetch_assoc() ?: [];
        } catch (Exception ) {
            error_log(Erro ao buscar cliente:  . ->getMessage());
            return [];
        }
    }
    
    /**
     * Formata valor monetário corretamente
     */
    private function formatarMoeda()
    {
        // Remove qualquer formatação anterior
         = preg_replace('/[^0-9.,-]/', '', (string));
        
        // Se tem vírgula e ponto, assume formato brasileiro
        if (strpos(, ',') !== false && strpos(, '.') !== false) {
             = str_replace('.', '', );
             = str_replace(',', '.', );
        } 
        // Se só tem vírgula, troca por ponto
        elseif (strpos(, ',') !== false) {
             = str_replace(',', '.', );
        }
        
         = floatval();
        return 'R$ ' . number_format(, 2, ',', '.');
    }
    
    /**
     * CORREÇÃO CRÍTICA: Valor por extenso sem multiplicar por mil
     */
    private function valorPorExtenso()
    {
        // Limpa o valor
         = preg_replace('/[^0-9.,-]/', '', (string));
        
        // Converte para float corretamente
        if (strpos(, ',') !== false && strpos(, '.') !== false) {
             = str_replace('.', '', );
             = str_replace(',', '.', );
        } elseif (strpos(, ',') !== false) {
             = str_replace(',', '.', );
        }
        
         = floatval();
        
        if ( == 0) {
            return 'ZERO REAIS';
        }
        
        // Usa NumberFormatter do PHP (extensão intl) se existir
        if (class_exists('NumberFormatter')) {
             = new NumberFormatter(pt_BR, NumberFormatter::SPELLOUT);
             = ->format();
            return mb_strtoupper( .  REAIS);
        }
        
        // Fallback manual se intl não estiver disponível
        return ->valorPorExtensoManual();
    }
    
    /**
     * Implementação manual caso extensão intl não esteja disponível
     */
    private function valorPorExtensoManual()
    {
         = ['', 'UM', 'DOIS', 'TRÊS', 'QUATRO', 'CINCO', 'SEIS', 'SETE', 'OITO', 'NOVE'];
         = ['', 'DEZ', 'VINTE', 'TRINTA', 'QUARENTA', 'CINQUENTA', 'SESSENTA', 'SETENTA', 'OITENTA', 'NOVENTA'];
         = ['DEZ', 'ONZE', 'DOZE', 'TREZE', 'QUATORZE', 'QUINZE', 'DEZESSEIS', 'DEZESSETE', 'DEZOITO', 'DEZENOVE'];
         = ['', 'CENTO', 'DUZENTOS', 'TREZENTOS', 'QUATROCENTOS', 'QUINHENTOS', 'SEISCENTOS', 'SETECENTOS', 'OITOCENTOS', 'NOVECENTOS'];
        
         = explode('.', number_format(, 2, '.', ''));
         = intval([0]);
         = intval([1] ?? 0);
        
        if ( == 0 &&  == 0) {
            return 'ZERO REAIS';
        }
        
         = '';
        
        // Processa milhões
        if ( >= 1000000) {
             = intval( / 1000000);
             .= ->numeroParaExtenso(, , , , ) . ' MILHÃO' . ( > 1 ? 'ES' : '') . ' ';
             %= 1000000;
        }
        
        // Processa milhares
        if ( >= 1000) {
             = intval( / 1000);
            if ( == 1) {
                 .= 'MIL ';
            } else {
                 .= ->numeroParaExtenso(, , , , ) . ' MIL ';
            }
             %= 1000;
        }
        
        // Processa centenas/unidades
        if ( > 0) {
            if ( == 100) {
                 .= 'CEM ';
            } else {
                 .= ->numeroParaExtenso(, , , , ) . ' ';
            }
        }
        
         = trim();
        
        if (empty()) {
             = 'ZERO';
        }
        
         .= ' REAIS';
        
        // Adiciona centavos se houver
        if ( > 0) {
             .= ' E ' . ->numeroParaExtenso(, , , , ) . ' CENTAVOS';
        }
        
        return ;
    }
    
    private function numeroParaExtenso(, , , , )
    {
         = '';
        
         = intval( / 100);
         =  % 100;
        
        if ( > 0) {
             .= [] . ' ';
        }
        
        if ( > 0) {
            if ( < 10) {
                 .= [];
            } elseif ( < 20) {
                 .= [ - 10];
            } else {
                 = intval( / 10);
                 =  % 10;
                 .= [];
                if ( > 0) {
                     .= ' E ' . [];
                }
            }
        }
        
        return trim();
    }
    
    private function dataPorExtenso()
    {
         = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
        ];
        
        return date('d') . ' de ' . [intval(date('m'))] . ' de ' . date('Y');
    }
    
    private function formatarValor(, )
    {
        // Se é campo de valor, garante formatação correta
        if (stripos(, 'valor') !== false || stripos(, 'preco') !== false) {
            return ->formatarMoeda();
        }
        return ;
    }
}
