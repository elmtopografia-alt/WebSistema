<?php
/**
 * EXPORTAR TEMA - GEOMETRPOLE
 * Gera arquivo JSON para download ou compartilhamento
 */

require_once '../config/config.php';
require_once '../config/database.php';

// Autenticação
require_once 'auth_check.php';

session_start();

$id = intval($_GET['id'] ?? 0);
$db = new Database();

$tema = $db->query("SELECT * FROM temas_personalizados WHERE id = ?", [$id])->fetch();

if (!$tema) {
    die("Tema não encontrado");
}

// Preparar dados para exportação
$exportacao = [
    'versao_exportacao' => '2.0.0',
    'tipo' => 'tema_individual',
    'data_exportacao' => date('Y-m-d H:i:s'),
    'exportado_por' => $_SESSION['usuario'] ?? 'sistema',
    'tema' => [
        'nome' => $tema['nome'],
        'slug' => $tema['slug'],
        'descricao' => $tema['descricao'],
        'icone' => $tema['icone'],
        'cores' => [
            'primaria' => $tema['cor_primaria'],
            'secundaria' => $tema['cor_secundaria'],
            'destaque' => $tema['cor_destaque'],
            'sucesso' => $tema['cor_sucesso'],
            'alerta' => $tema['cor_alerta']
        ],
        'tipografia' => [
            'titulo' => $tema['fonte_titulo'],
            'corpo' => $tema['fonte_corpo'],
            'tamanho_base' => $tema['tamanho_base']
        ],
        'layout' => [
            'espacamento' => $tema['espacamento_padrao'],
            'bordas_arredondadas' => (bool)$tema['bordas_arredondadas'],
            'sombras' => (bool)$tema['sombras']
        ],
        'css_custom' => $tema['css_custom']
    ]
];

$json = json_encode($exportacao, JSON_PRETTY_PRINT);
$filename = 'tema_' . $tema['slug'] . '_' . date('Y-m-d') . '.json';

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($json));

echo $json;
?>
