<?php
// Render usa DB_*; MYSQL* se conserva para proveedores y entornos locales existentes.
$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'railway';
$port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306';

$conexion = new mysqli($host, $user, $password, $dbname, $port);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>