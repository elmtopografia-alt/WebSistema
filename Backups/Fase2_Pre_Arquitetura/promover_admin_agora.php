<?php
// promover_admin_agora.php
// Promove o usuário elmyopografia@gmail.com para ADMIN

require_once 'db.php';

try {
    $conn = Database::getProd();
    $usuario = 'elmyopografia@gmail.com';

    $stmt = $conn->prepare("UPDATE Usuarios SET tipo_perfil = 'admin' WHERE usuario = ?");
    $stmt->bind_param('s', $usuario);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<h1>SUCESSO!</h1><p>O usuário <b>$usuario</b> agora é ADMIN.</p><p>Por favor, faça <b>LOGOUT e LOGIN</b> novamente para ver o botão.</p>";
    } else {
        echo "<h1>Atenção</h1><p>Nenhuma linha alterada. Talvez o usuário já seja admin ou não exista.</p>";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
