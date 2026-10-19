<?php
$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
$database = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'railway';
$port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306';

// Usar MySQLi en lugar de PDO para que coincida con el resto de tu CRUD
$conexion = new mysqli($host, $user, $password, $database, $port);

if ($conexion->connect_error) {
    error_log('No se pudo conectar con la base de datos: ' . $conexion->connect_error);
    http_response_code(500);
    exit('No se pudo conectar a la base de datos. Verifica que MySQL esté activo y revisa la configuración DB_*.');
}

$conexion->set_charset("utf8mb4");

session_start();

function e($texto)
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
