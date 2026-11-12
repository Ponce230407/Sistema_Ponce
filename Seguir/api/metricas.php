<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');
if (!is_admin($pdo)) {
    http_response_code(empty($_SESSION['usuario_id']) ? 401 : 403);
    echo json_encode(['error' => 'No autorizado.']);
    exit;
}
require __DIR__ . '/api/metricas-original.php';
