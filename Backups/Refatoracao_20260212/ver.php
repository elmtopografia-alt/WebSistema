<?php
// 1. INCLUI O ARQUIVO DE CONEXÃO
//    (Garanta que este arquivo esteja na mesma pasta ou ajuste o caminho)
require_once 'db.php';

// 2. DEFINE A CONSULTA SQL
$sql = "SELECT * FROM Propostas ORDER BY id DESC"; // Puxa todas as colunas. Opcional: "ORDER BY id DESC" para ver as mais novas primeiro.

// 3. EXECUTA A CONSULTA
$result = $conn->query($sql);

// 4. VERIFICA SE A CONSULTA FALHOU
if (!$result) {
    die("❌ Erro ao consultar o banco de dados: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Propostas</title>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse; /* Remove espaços entre as células */
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        th, td {
            padding: 12px 15px; /* Espaçamento interno */
            border: 1px solid #ddd; /* Bordas das células */
            text-align: left; /* Alinhamento do texto */
        }
        thead {
            background-color: #007bff; /* Cor de fundo do cabeçalho */
            color: #ffffff; /* Cor do texto do cabeçalho */
        }
        tr:nth-child(even) {
            background-color: #f9f9f9; /* Cor de fundo alternada (zebrado) */
        }
        tr:hover {
            background-color: #f1f1f1; /* Cor ao passar o mouse */
        }
        .no-results {
            text-align: center;
            color: #777;
            padding: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>

    <h1>📋 Lista de Propostas</h1>

    <?php
    // 5. VERIFICA SE HÁ RESULTADOS
    if ($result->num_rows > 0) :
    ?>
        <table>
            <thead>
                <tr>
                    <?php
                    // Pega os nomes das colunas (campos)
                    $fields = $result->fetch_fields();
                    foreach ($fields as $field) :
                    ?>
                        <th><?php echo htmlspecialchars($field->name); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            
            <tbody>
                <?php
                // Loop por cada linha de resultado
                while ($row = $result->fetch_assoc()) :
                ?>
                    <tr>
                        <?php
                        // Loop por cada coluna da linha atual
                        foreach ($fields as $field) :
                            $fieldName = $field->name;
                        ?>
                            <td><?php echo htmlspecialchars($row[$fieldName]); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php
    // 6. MENSAGEM CASO A TABELA ESTEJA VAZIA
    else :
    ?>
        <p class="no-results">Nenhuma proposta encontrada no banco de dados.</p>
    <?php
    endif;
    
    // 7. FECHA A CONEXÃO
    $conn->close();
    ?>

</body>
</html>