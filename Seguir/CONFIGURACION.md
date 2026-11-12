# Solución de errores y configuración de la base de datos

El panel y la agenda necesitan la tabla `apoyos` en **la misma base MySQL a la que se conecta la aplicación** (la variable `DB_NAME` o `MYSQLDATABASE`; el nombre no siempre es `railway`).

## Instalar o actualizar el esquema

- En una instalación nueva, importa `database.sql` en la base de datos de la aplicación.
- Si ya tienes la base y los usuarios, selecciona esa misma base en phpMyAdmin y ejecuta `Seguir/apoyos.sql`. El archivo usa `CREATE TABLE IF NOT EXISTS`, así que no elimina registros existentes.
- Verifica en phpMyAdmin que aparezcan las tablas `usuarios`, `eventos` y `apoyos`. En SQL puedes ejecutar `SHOW TABLES;` y `DESCRIBE apoyos;`.

La tabla guarda `id`, `usuario_id` (relación con `usuarios.id`), `actividad`, `fecha`, `estado`, `creado_en` y `actualizado_en`. No agregues columnas a `usuarios` para corregir el error mostrado: falta la tabla independiente `apoyos`.

`Seguir/admin.php` ahora muestra una advertencia de configuración en vez de una pantalla fatal si falta la tabla. Para activar el acceso de administrador, configura `ADMIN_EMAIL` en el hosting con el correo de una cuenta registrada. No guardes contraseñas ni secretos en el repositorio.
