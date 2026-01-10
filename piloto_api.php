<?php
// ARQUIVO: piloto_api.php
require_once 'db.php'; 

$acao = $_GET['acao'] ?? '';

if ($acao == 'detalhes') {
    $mes = $_GET['mes'];
    $status = $_GET['status']; // Vem do JS (ex: "Em elaboração")

    // Nota: Como agora os nomes no gráfico são idênticos ao banco,
    // não precisamos mais daquele monte de "if/else".
    // Mas vamos manter uma segurança básica para caracteres especiais.
    
    $status_db = $conn->real_escape_string($status);
    $mes_db = $conn->real_escape_string($mes);

    $sql = "SELECT * FROM sgt_piloto_teste 
            WHERE DATE_FORMAT(data_criacao, '%m/%Y') = '$mes_db' 
            AND status = '$status_db'
            ORDER BY valor DESC";
    
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Cor do valor baseada no status
            $corValor = 'text-dark';
            if ($row['status'] == 'Aprovada') $corValor = 'text-success';
            if ($row['status'] == 'Cancelada') $corValor = 'text-danger';
            if ($row['status'] == 'Enviada') $corValor = 'text-primary';

            echo "<tr>
                    <td>
                        <strong>{$row['cliente']}</strong><br>
                        <small class='text-muted'>{$row['status']}</small>
                    </td>
                    <td>" . date('d/m/Y', strtotime($row['data_criacao'])) . "</td>
                    <td class='text-end fw-bold $corValor'>R$ " . number_format($row['valor'], 2, ',', '.') . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3' class='text-center text-muted'>Nenhuma proposta encontrada.</td></tr>";
    }
}
?>