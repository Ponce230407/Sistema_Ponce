<?php
require_once __DIR__ . '/../../Seguir/perros-db.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    $statement = $pdo->query('SELECT p.id, p.nombre, p.edad, p.tamano, p.descripcion, p.foto, u.nombre AS autor FROM perros_publicados p JOIN usuarios u ON u.id = p.usuario_id ORDER BY p.creado_en DESC, p.id DESC LIMIT 60');
    echo json_encode($statement->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $error) {
    error_log('No se pudieron cargar las publicaciones: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No fue posible cargar las publicaciones.']);
}
