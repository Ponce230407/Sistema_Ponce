<?php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'railway';
$port = getenv('DB_PORT') ?: '3306';

$conexion = new mysqli($host, $user, $password, $dbname, $port);
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>
