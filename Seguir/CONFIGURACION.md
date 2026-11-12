# Configuración privada del administrador

El panel `Seguir/admin.php` y la API de métricas están reservados a una cuenta existente cuyo correo coincida con la variable de entorno `ADMIN_EMAIL`. Configura esa variable desde el panel de secretos de tu proveedor de hosting; no guardes contraseñas ni credenciales de correo en Git, código fuente o capturas. Registra la cuenta mediante el formulario normal y usa una contraseña nueva y única.

Para activar la agenda de apoyos, importa `Seguir/apoyos.sql` en la misma base MySQL que contiene la tabla `usuarios`. Las personas registradas de forma normal tendrán acceso únicamente a sus propias inscripciones; pueden agendar una fecha y cancelar sus apoyos activos. El panel de administración muestra el listado de inscritos y el estado de cada apoyo.
