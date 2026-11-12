<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
if (!empty($_SESSION['usuario_id'])) {
    echo json_encode(['logged_in' => true, 'name' => (string) ($_SESSION['nombre'] ?? '')], JSON_UNESCAPED_UNICODE);
} elseif (($_SESSION['rol'] ?? '') === 'admin') {
    echo json_encode(['logged_in' => true, 'name' => (string) ($_SESSION['nombre'] ?? 'Administración')], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['logged_in' => false, 'name' => ''], JSON_UNESCAPED_UNICODE);
}
