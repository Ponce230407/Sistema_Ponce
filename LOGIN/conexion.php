<?php
$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
$database = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'railway';
$port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306';

// Keep the same PHP login session while navigating through every project folder.
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', (string) (30 * 24 * 60 * 60));
    session_set_cookie_params([
        'lifetime' => 30 * 24 * 60 * 60,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $error) {
    error_log('No se pudo conectar con la base de datos: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo conectar a la base de datos. Verifica la configuración DB_* del hosting.');
}

function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}
