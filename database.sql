-- Ejecutar seleccionando la base railway en phpMyAdmin.
-- Este es el esquema común para LOGIN, seguimiento, productos y encuesta.
USE railway;

CREATE TABLE IF NOT EXISTS usuarios (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(100) NOT NULL,
    correo        VARCHAR(150) NOT NULL UNIQUE,
    sexo          ENUM('Hombre', 'Mujer', 'Otro') NOT NULL,
    edad          TINYINT UNSIGNED NOT NULL,
    carrera       VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    creado_en     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS eventos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    visitante_id CHAR(36) NOT NULL,
    sesion_id    CHAR(36) NOT NULL,
    usuario_id   INT NULL,
    tipo         ENUM('vista', 'clic', 'tiempo') NOT NULL,
    pagina       VARCHAR(200) NOT NULL,
    elemento     VARCHAR(100) NULL,
    segundos     SMALLINT UNSIGNED NULL,
    creado_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX (visitante_id),
    INDEX (sesion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS productos (
    id_producto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    precio      DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    stock       INT UNSIGNED NOT NULL DEFAULT 0,
    activo      TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS respuestas_encuesta (
    id_respuesta     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_completo  VARCHAR(150) NOT NULL,
    p1_profesores    VARCHAR(100) NOT NULL,
    p2_alumnos       VARCHAR(100) NOT NULL,
    p3_instalaciones VARCHAR(100) NOT NULL,
    p4_examenes      VARCHAR(100) NOT NULL,
    p5_comida        VARCHAR(100) NOT NULL,
    fecha_respuesta  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
