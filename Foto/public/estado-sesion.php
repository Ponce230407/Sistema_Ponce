<?php
require_once __DIR__ . '/../../LOGIN/conexion.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
$esAdmin = ($_SESSION['rol'] ?? '') === 'admin';
$autenticado = $usuarioId > 0 || $esAdmin;
$nombre = '';
if ($usuarioId > 0) {
    try {
        $statement = $pdo->prepare('SELECT nombre FROM usuarios WHERE id = ?');
        $statement->execute([$usuarioId]);
        $nombre = (string) ($statement->fetchColumn() ?: '');
    } catch (Throwable $error) {
        error_log('No se pudo obtener el nombre de sesión: ' . $error->getMessage());
    }
} elseif ($esAdmin) {
    $nombre = (string) ($_SESSION['admin_email'] ?? 'Administración');
}

echo json_encode(['autenticado' => $autenticado, 'es_admin' => $esAdmin, 'nombre' => $nombre], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
