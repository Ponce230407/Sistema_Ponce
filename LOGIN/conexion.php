<?php
// Use cookies consistently on localhost and behind HTTPS reverse proxies (Render).
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

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
} catch (PDOException $error) {
    error_log('No se pudo conectar con la base de datos: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo conectar a la base de datos. Verifica que MySQL esté activo y revisa la configuración DB_*.');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}
