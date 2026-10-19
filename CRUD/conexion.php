<?php
$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
$database = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'railway';
$port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    // Si se conecta con éxito, imprimirá esto temporalmente:
    echo "¡Conexión exitosa a la base de datos!";
} catch (PDOException $error) {
    // Esto mostrará el error exacto que está devolviendo Railway
    echo "<h3 style='color:red;'>Error exacto de PDO:</h3>";
    echo "<pre>" . htmlspecialchars($error->getMessage()) . "</pre>";
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
