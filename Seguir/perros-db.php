<?php
require_once __DIR__ . '/../LOGIN/conexion.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS perros_publicados (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        nombre VARCHAR(80) NOT NULL,
        edad VARCHAR(40) NOT NULL,
        tamano VARCHAR(30) NOT NULL,
        descripcion VARCHAR(1000) NOT NULL,
        foto VARCHAR(255) NOT NULL,
        creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_perros_creado (creado_en),
        INDEX idx_perros_usuario (usuario_id),
        CONSTRAINT fk_perros_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (PDOException $error) {
    error_log('No se pudo preparar la tabla de publicaciones de perros: ' . $error->getMessage());
    http_response_code(503);
    exit('Las publicaciones no están disponibles en este momento. Comprueba los permisos de la base de datos.');
}
