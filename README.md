# Patitas con Hogar 🐾

Aplicación web para compartir información sobre adopciones, contacto y actividades de la comunidad.

## Funcionalidades

- Inicio público con las publicaciones de perros compartidas por usuarios.
- Publicación de perros con foto desde una cuenta registrada; cada usuario puede eliminar sus propias publicaciones.
- Consultas por WhatsApp sin necesidad de iniciar sesión.
- Contacto y ubicación configurables mediante variables de entorno.
- Registro, acceso de colaboradores, métricas e inventario.
- Chat temporal compartido entre visitantes mediante un servicio REST Flask en memoria, sin base de datos.

## Configuración

Se requiere PHP 8.2 con PDO MySQL y una base MySQL/MariaDB con las credenciales DB_* configuradas. En Render, configura también:

- `WHATSAPP_NUMBER`: número internacional de WhatsApp, solo dígitos y con código de país (opcional).
- `CONTACT_EMAIL`: correo público de contacto (opcional).
- `REFUGIO_DIRECCION`: dirección verificada para mostrar el mapa (opcional).
- `CHAT_API_URL`: URL pública del servicio Flask de chat; véase [`chat_api/README.md`](chat_api/README.md).

El número y la dirección no se inventan: si no se configuran, la página explica que los datos aún no están disponibles. Los enlaces de consulta son públicos.

## Fotos y almacenamiento

Las fotos aceptadas son JPG, PNG y WebP, con un máximo de 5 MB. Se validan como imágenes y se guardan en la base de datos. Para compartir una foto desde la cámara, pulsa “Activar cámara” y concede el permiso que solicita el navegador; requiere HTTPS o localhost. Si no hay cámara disponible o el navegador bloquea el permiso, se puede seleccionar una imagen desde el dispositivo.

## Chat sin base de datos

El servicio REST de `chat_api/` conserva hasta 500 mensajes en memoria durante un máximo de 24 horas. Al reiniciar o escalar el servicio, se pierde el historial. Para ejecutar y comprobar la conversación entre dos navegadores, sigue las instrucciones de [`chat_api/README.md`](chat_api/README.md).
