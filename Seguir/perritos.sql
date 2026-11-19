-- Galería de perritos; ejecutar en la misma base que contiene usuarios.
CREATE TABLE IF NOT EXISTS fotos_perritos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre_perrito VARCHAR(80) NOT NULL,
    descripcion VARCHAR(500) NOT NULL DEFAULT '',
    tipo_mime ENUM('image/jpeg', 'image/png', 'image/webp') NOT NULL,
    imagen MEDIUMBLOB NOT NULL,
    estado ENUM('pendiente', 'publicado', 'rechazado') NOT NULL DEFAULT 'pendiente',
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_fotos_perritos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_fotos_estado_fecha (estado, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
