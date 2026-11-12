<?php
require_once __DIR__ . '/../../LOGIN/conexion.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS experiencias_perritos (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(80) NOT NULL,
        historia VARCHAR(1000) NOT NULL,
        foto VARCHAR(255) NOT NULL,
        creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_experiencias_creado (creado_en)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $statement = $pdo->query('SELECT id, nombre, historia, foto, creado_en FROM experiencias_perritos ORDER BY creado_en DESC, id DESC LIMIT 60');
    echo json_encode($statement->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $error) {
    error_log('No se pudieron cargar las experiencias: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No fue posible cargar las experiencias.']);
}
