<?php
// CRUD was starting a PHP session with default settings while login used custom
// parameters. Use the same bootstrap so all sections share the same cookie/session.
require_once __DIR__ . '/../LOGIN/session.php';
$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
$database = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'railway';
$port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conexion = new mysqli($host, $user, $password, $database, (int) $port);
    $conexion->set_charset('utf8mb4');
} catch (mysqli_sql_exception $error) {
    error_log('No se pudo conectar con MySQLi: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo conectar a la base de datos. Verifica la configuración DB_* del hosting.');
}
