-- La base railway debe existir. Ejecutar en ella o importar App_Web/database.sql
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
);
