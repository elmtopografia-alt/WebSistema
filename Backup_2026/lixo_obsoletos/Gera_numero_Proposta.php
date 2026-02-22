<?php
// Nome da página: Gerar_numero_Proposta.php
// Este será o conteúdo do futuro arquivo 'salvar_proposta.php'

require 'db.php'; // Inclui a conexão com o banco

// --- LÓGICA PARA GERAR O NÚMERO DA PROPOSTA ---

function gerarNumeroProposta($conn) {
    $prefixo = 'ELM';
    $ano = date('Y'); // Pega o ano atual, ex: 2025

    // Inicia uma transação para garantir que a operação seja segura
    // Isso previne que dois usuários peguem o mesmo número ao mesmo tempo
    $conn->begin_transaction();

    try {
        // 1. Busca o último número de proposta GERADO NESTE ANO
        $sql = "SELECT numero_proposta FROM Propostas 
                WHERE numero_proposta LIKE ? 
                ORDER BY id_proposta DESC LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $likeAno = "$prefixo-$ano-%";
        $stmt->bind_param('s', $likeAno);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ultimoNumero = 0;
        if ($result->num_rows > 0) {
            $ultimaProposta = $result->fetch_assoc();
            // Extrai a parte sequencial do número. Ex: de "ELM-2025-015", pega o "15"
            $partes = explode('-', $ultimaProposta['numero_proposta']);
            $ultimoNumero = intval(end($partes));
        }

        // 2. Incrementa o número
        $novoNumeroSequencial = $ultimoNumero + 1;

        // 3. Formata o número para ter 3 dígitos com zeros à esquerda (001, 002, ..., 010, ..., 100)
        $novoNumeroFormatado = str_pad($novoNumeroSequencial, 3, '0', STR_PAD_LEFT);

        // 4. Monta o código final
        $numeroFinal = "$prefixo-$ano-$novoNumeroFormatado";

        // Confirma a transação
        $conn->commit();

        return $numeroFinal;

    } catch (Exception $e) {
        // Se algo der errado, desfaz tudo
        $conn->rollback();
        // Lida com o erro, talvez mostrando uma mensagem ao usuário
        die("Erro ao gerar número da proposta: " . $e->getMessage());
    }
}


// --- COMO SERIA USADO AO SALVAR UMA PROPOSTA ---

// (Aqui viria o código que pega os dados do formulário: $_POST['nome_projeto'], etc.)

// 1. Gera o número ANTES de inserir no banco
$novoNumeroProposta = gerarNumeroProposta($conn);

echo "O número da nova proposta é: " . $novoNumeroProposta;

// 2. Agora, você faria o INSERT na tabela Propostas, incluindo este número
/*
$sql_insert = "INSERT INTO Propostas (numero_proposta, nome_projeto, ...) VALUES (?, ?, ...)";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param('ss...', $novoNumeroProposta, $_POST['nome_projeto'], ...);
$stmt_insert->execute();
*/

?>