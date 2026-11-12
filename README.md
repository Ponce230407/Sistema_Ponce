# Patitas con Hogar 🐾

Aplicación web para compartir información sobre adopciones, contacto y actividades de la comunidad.

## Funcionalidades

- Inicio público con las publicaciones de perros compartidas por usuarios.
- Publicación de perros con foto desde una cuenta registrada; cada usuario puede eliminar sus propias publicaciones.
- Consultas por WhatsApp sin necesidad de iniciar sesión.
- Contacto y ubicación configurables mediante variables de entorno.
- Registro, acceso de colaboradores, métricas e inventario.

## Configuración

Se requiere PHP 8.2 con PDO MySQL y una base MySQL/MariaDB con las credenciales DB_* configuradas. En Render, configura también:

- `WHATSAPP_NUMBER`: número internacional de WhatsApp, solo dígitos y con código de país (sin `+` ni espacios).
- `CONTACT_EMAIL`: correo público de contacto (opcional).
- `REFUGIO_DIRECCION`: dirección verificada para mostrar el enlace de Maps (opcional).

El número y la dirección no se inventan: si no se configuran, la página explica que los datos aún no están disponibles. Los enlaces “Preguntar por…” son públicos y no redirigen al login.

La tabla `perros_publicados` se prepara automáticamente al abrir la sección de publicaciones (la cuenta MySQL debe permitir crear tablas). También se incluye en `database.sql` para su creación manual.

## Fotos y almacenamiento

Las fotos aceptadas son JPG, PNG y WebP, con un máximo de 5 MB. Se validan como imágenes y reciben nombres aleatorios. `Foto/public/uploads/perros` debe ser escribible por PHP. En servicios con disco efímero, como el plan gratuito de Render, las imágenes locales se pueden perder al reiniciar o volver a desplegar; para conservarlas en producción configura un disco persistente o almacenamiento de objetos.

Los usuarios deben iniciar sesión para publicar; las consultas y la navegación pública no requieren cuenta. Las descripciones se escapan al mostrarse y se pide no incluir datos personales.
