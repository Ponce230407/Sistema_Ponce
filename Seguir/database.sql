-- La base railway debe existir y contener la tabla usuarios.
-- Para crear todas las tablas del proyecto, importar App_Web/database.sql.
USE railway;

CREATE TABLE IF NOT EXISTS eventos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    visitante_id CHAR(36)    NOT NULL,              -- cookie visitante_id: el navegador
    sesion_id    CHAR(36)    NOT NULL,              -- sessionStorage: la visita (pestaña)
    usuario_id   INT         NULL,                  -- NULL mientras el visitante es anónimo
    tipo         ENUM('vista', 'clic', 'tiempo') NOT NULL,
    pagina       VARCHAR(200) NOT NULL,
    elemento     VARCHAR(100) NULL,                 -- solo en clics
    segundos     SMALLINT UNSIGNED NULL,            -- solo en tiempo
    creado_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX (visitante_id),
    INDEX (sesion_id)
);
