<?php
require_once 'config.php';
require_once 'db.php';

$tests = [
    ['u' => 'renato_prod@gmail.com', 'p' => 'Ren@2026', 'env' => 'prod'],
    ['u' => 'edivaldo@elmtopografia.com.br', 'p' => 'Elm@2026', 'env' => 'prod'],
    ['u' => 'contato_demo@elmtopografia.com.br', 'p' => 'Contato@2026', 'env' => 'demo']
];

echo "<pre>";
foreach ($tests as $t) {
    echo "Testing {$t['u']} in {$t['env']}... ";
    
    $conn = ($t['env'] == 'prod') ? Database::getProd() : Database::getDemo();
    $stmt = $conn->prepare("SELECT senha, id_usuario, tipo_perfil FROM Usuarios WHERE usuario = ?");
    $stmt->bind_param('s', $t['u']);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    
    if (!$user) {
        echo "User NOT FOUND.\n";
        continue;
    }
    
    if (password_verify($t['p'], $user['senha'])) {
        echo "HASH VISUAL MATCH [OK] (ID: {$user['id_usuario']}, Perfil: {$user['tipo_perfil']})\n";
    } else {
        echo "HASH MISMATCH [FAIL]. DB Hash: " . substr($user['senha'], 0, 10) . "...\n";
        
        // Debug lengths
        echo "Pass Len: " . strlen($t['p']) . "\n";
        echo "Hash Len: " . strlen($user['senha']) . "\n";
    }
}
echo "</pre>";
?>
