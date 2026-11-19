# Fixes, base de datos y galería

## Fotos de perritos

Importa `Seguir/perritos.sql` en la misma base MySQL a la que apunta `DB_NAME`/`MYSQLDATABASE` y que ya contiene `usuarios`. La tabla `fotos_perritos` guarda usuario, nombre, descripción, tipo de imagen, bytes de la foto, estado y fecha. No hace falta configurar una carpeta de uploads: las fotos se guardan en MySQL para que no se pierdan al redeplegar el servicio.

La página `Seguir/perritos.php` deja que cualquiera vea fotos publicadas; para subir se requiere iniciar sesión. Se aceptan JPEG, PNG y WebP de hasta 3 MB y 5000 × 5000 píxeles. Las subidas quedan pendientes y solo el administrador puede aprobarlas desde `Seguir/admin.php`. No se aceptan SVG ni otros archivos.

La cuenta MySQL del hosting debe tener permiso `CREATE` al importar la tabla y los límites PHP del hosting deben permitir al menos 3 MB por archivo. Para confirmar, revisa `upload_max_filesize` y `post_max_size` (este último debe ser mayor que 3 MB).

## Sesiones, colores y navegación

Login, Seguir y CRUD usan el bootstrap compartido `LOGIN/session.php`, con cookie de sesión en `/` para navegar por las distintas carpetas del mismo dominio. El diseño usa la paleta crema/verde de Patitas y el menú compartido. Las métricas de navegación se guardan solo si la persona acepta las cookies de analítica; no se recopilan campos escritos en formularios.

## Base principal

`database.sql` crea el esquema común y `Seguir/apoyos.sql` crea la agenda si la base ya existía. No uses `USE railway` si tu hosting asignó otro nombre: selecciona la base que realmente indican `DB_NAME` o `MYSQLDATABASE`.
