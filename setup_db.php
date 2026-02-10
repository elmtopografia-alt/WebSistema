<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    // 1. Connect without DB to create it
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating database...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS sistemas_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database systems_web created/checked.\n";
    
    // 2. Connect to the new DB
    $pdo = new PDO("mysql:host=$host;dbname=sistemas_web", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 3. Read and execute SQL file
    $sqlFile = 'setup_leads_prospeccao.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $pdo->exec($sql);
        echo "SQL file executed successfully. Table created.\n";
    } else {
        echo "CTX Error: SQL file not found.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
