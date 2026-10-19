# Métricas de usuario: ¿qué hace la gente en mi sitio?

**Continuación del ejercicio LOGIN.** A la app de registro e inicio de sesión se le agrega un sistema de analítica propio, un "mini Google Analytics", que responde:

- ¿Qué páginas visita el usuario?
- ¿Dónde hace clic?
- ¿Cuánto tiempo pasa en cada página?
- ¿Quién es? Primero es anónimo; al iniciar sesión se sabe su nombre y su carrera.

Usa la base de datos compartida **`railway`** y la misma tabla `usuarios` que LOGIN: quien ya se registró puede entrar directamente.

---

## 1. La idea principal: las cookies no rastrean clics

Una cookie solo es un **identificador**, como una pulsera en un evento. Por sí sola no ve nada. El sistema necesita tres piezas:

| Pieza | Pregunta que responde | Dónde está |
|---|---|---|
| **1. Identificar** | ¿Es la misma persona de antes? | Cookie `visitante_id` + `sessionStorage` (`tracker.js`) |
| **2. Observar** | ¿Qué hizo? | Eventos del DOM en JavaScript (`tracker.js`) |
| **3. Registrar y consultar** | ¿Dónde se guarda? ¿Cómo lo veo? | **Servicios web** en PHP (`api/eventos.php`, `api/metricas.php`) |

```
 Navegador del usuario                                 Servidor (PHP + MySQL)
 ───────────────────────────────                       ──────────────────────────────────
  cookie visitante_id = 6c89…
  tracker.js escucha:
    · vista de página      ── POST api/eventos.php ──►  valida ─► INSERT INTO eventos
    · clic                    (JSON con sendBeacon)      + usuario_id de $_SESSION
    · tiempo visible

 dashboard.php + panel.js  ── GET api/metricas.php ──►  SELECT … GROUP BY … JOIN usuarios
   (tablas que se          ◄──────── JSON ───────────
    refrescan cada 5 s)
```

Aquí está el tema de la materia: **el navegador y el servidor se comunican mediante servicios que intercambian JSON**. El panel no genera HTML desde PHP: pide los datos a un servicio y los dibuja con JavaScript.

## 2. Estructura de archivos

```
SEGUIR/
├── database.sql       ← NUEVO: agrega la tabla eventos a railway
├── tracker.js         ← NUEVO: aviso de cookies + identificar + observar
├── api/
│   ├── eventos.php    ← NUEVO: servicio que RECIBE eventos (POST)
│   └── metricas.php   ← NUEVO: servicio que ENTREGA métricas (GET, JSON)
├── dashboard.php      ← NUEVO: panel de métricas (HTML vacío)
├── panel.js           ← NUEVO: consume api/metricas.php y llena el panel
├── cursos.php         ← NUEVO: catálogo con botones (para tener dónde hacer clic)
├── menu.php           ← NUEVO: menú de las páginas protegidas
├── login.php          ← CAMBIA: vincula al visitante anónimo con el usuario
├── bienvenida.php     ← CAMBIA: menú + botón al catálogo + tracker
├── registro.php       ← CAMBIA: incluye tracker.js
├── estilos.css        ← CAMBIA: estilos del menú, el panel y el aviso
└── conexion.php, index.php, logout.php, app.js   ← igual que en LOGIN
```

Destino: **`C:\xampp\htdocs\seguir\`**, con la subcarpeta `api\` incluida.

## 3. Base de datos: la tabla `eventos`

```sql
CREATE TABLE eventos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    visitante_id CHAR(36)  NOT NULL,     -- el navegador (cookie)
    sesion_id    CHAR(36)  NOT NULL,     -- la visita (pestaña)
    usuario_id   INT       NULL,         -- NULL = anónimo
    tipo         ENUM('vista','clic','tiempo') NOT NULL,
    pagina       VARCHAR(200) NOT NULL,
    elemento     VARCHAR(100) NULL,      -- solo en clics
    segundos     SMALLINT UNSIGNED NULL, -- solo en tiempo
    creado_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);
```

- **Una fila = un evento.** Es el mismo modelo que usan Google Analytics y otras herramientas de analítica: se guarda lo que pasó y las métricas se **calculan** después con SQL.
- **`usuario_id NULL`**: permite registrar a visitantes que todavía no inician sesión.
- **`FOREIGN KEY … ON DELETE SET NULL`**: si se borra un usuario, sus eventos se quedan pero vuelven a ser anónimos.

## 4. Tres identificadores distintos (punto clave)

| Identificador | Dónde vive | Cuánto dura | Representa |
|---|---|---|---|
| `visitante_id` | Cookie creada por JS | 1 año | **El navegador** (la misma PC o celular) |
| `sesion_id` | `sessionStorage` | Hasta cerrar la pestaña | **Una visita** |
| `usuario_id` | `$_SESSION` en el servidor (cookie `PHPSESSID`) | Hasta cerrar sesión | **La persona** que inició sesión |

> **Pregunta para clase:** si dos alumnos usan la misma PC del laboratorio, ¿cuántos *visitantes* cuenta el sistema? ¿Y cuántos *usuarios*?

## 5. `tracker.js` paso a paso

### 5.1 Consentimiento primero
Si no existe la cookie `analitica`, se muestra el aviso. **Solo si el usuario acepta se crea `visitante_id` y se empieza a rastrear.** Si rechaza, no se envía nada. El enlace **Privacidad** del menú borra la decisión para volver a preguntar.

### 5.2 Los tres eventos
```js
enviar('vista');                                   // al cargar la página

document.addEventListener('click', (evento) => {   // clics en a, button o [data-track]
    const el = evento.target.closest('a, button, [data-track]');
    ...
    enviar('clic', { elemento: nombre });
});

document.addEventListener('visibilitychange', ...) // tiempo mientras la pestaña está visible
```

- **Un solo listener en `document`** atrapa los clics de toda la página gracias a la **propagación (bubbling)**. No hace falta poner un listener en cada botón.
- **`data-track`**: da un nombre fijo al elemento. En `cursos.php` el botón cambia su texto a "✓ Te avisaremos" al pulsarlo. Sin `data-track`, el rastreador registraría el texto ya cambiado.
- **Tiempo visible**: si el usuario cambia de pestaña, el reloj se detiene. Es más honesto que medir "desde que llegó hasta que se fue".

### 5.3 ¿Por qué `sendBeacon` y no `fetch`?
Cuando el usuario hace clic en un enlace, el navegador **abandona la página** y puede cancelar las peticiones `fetch` que estén pendientes. `navigator.sendBeacon` está pensado para esto: el navegador **garantiza** el envío aunque la página se cierre.

### 5.4 Lo que NO se registra
Lo que se escribe en los formularios (correos, contraseñas). Es una decisión de diseño y también de ética.

## 6. Servicio que recibe: `api/eventos.php`

| Situación | Respuesta HTTP |
|---|---|
| Petición con método distinto a POST | **405** Method Not Allowed |
| JSON inválido, tipo desconocido o ID con formato incorrecto | **400** Bad Request |
| Evento guardado | **204** No Content |

Puntos clave:
- **Nunca confiar en el cliente**: cualquiera puede mandar un POST con curl o Postman. Por eso se valida el tipo, el formato de los ID y el tamaño de los textos, y se acota `segundos` entre 0 y 3600.
- **¿Quién es el usuario? Lo decide el servidor** (`$_SESSION['usuario_id']`), no un campo del JSON. Si el navegador pudiera mandar `usuario_id`, cualquiera podría hacerse pasar por otro.
- Consultas preparadas, igual que en LOGIN.

## 7. De anónimo a identificado (`login.php`)

```php
if (isset($_COOKIE['visitante_id'])) {
    $pdo->prepare('UPDATE eventos SET usuario_id = ? WHERE visitante_id = ? AND usuario_id IS NULL')
        ->execute([$usuario['id'], $_COOKIE['visitante_id']]);
}
```

En el momento del login, todo lo que ese navegador hizo como anónimo (ver el login, ir a registro, pulsar "Registrarme") se asigna al usuario. Las plataformas comerciales llaman a esto **unificación de identidad** (*identity stitching*).

> **Para discutir:** en una PC compartida, ¿qué problema tiene esta regla?

## 8. Servicio que entrega: `api/metricas.php`

Responde **401** si no hay sesión. Si la hay, devuelve un JSON con:

| Clave | Qué contiene | SQL interesante |
|---|---|---|
| `resumen` | visitantes, sesiones, usuarios, vistas, clics, tiempo promedio | `COUNT(DISTINCT …)`, `SUM(tipo = 'clic')`, subconsulta para el promedio por sesión |
| `paginas` | vistas, clics y tiempo por página | `GROUP BY pagina` |
| `clics` | elementos más clicados | `WHERE tipo = 'clic' GROUP BY elemento` |
| `usuarios` | actividad por persona | `LEFT JOIN usuarios` (los anónimos salen juntos) |
| `carreras` | actividad por carrera | `JOIN usuarios … GROUP BY carrera` (**usa los datos del registro de LOGIN**) |
| `recientes` | últimos 15 eventos | `ORDER BY id DESC LIMIT 15` |

**Truco de MySQL:** `SUM(tipo = 'clic')` cuenta los clics porque una comparación vale 1 si es verdadera y 0 si es falsa.

Se puede abrir directo en el navegador (`/api/metricas.php`) para ver el JSON crudo, o probar en Postman.

## 9. El panel: `dashboard.php` + `panel.js`

- `dashboard.php` solo trae tablas vacías. `panel.js` hace `fetch('api/metricas.php')` cada **5 segundos** y las llena. Proyectado en clase, se ve **en vivo** cómo llegan los clics de los alumnos.
- **XSS almacenado**: el campo `elemento` viene de los navegadores, y alguien podría mandar `<img src=x onerror=alert(1)>` con curl. Por eso el panel usa **`textContent`** y nunca `innerHTML`. Es el mismo principio que la función `e()` de LOGIN, ahora del lado de JavaScript.

## 10. Puesta en marcha

1. XAMPP: **MySQL** encendido.
2. Seleccionar la base `railway` y ejecutar una vez el esquema común `App_Web/database.sql`, que crea las tablas de login, seguimiento, productos y encuesta.
3. Copiar todo el proyecto a `C:\xampp\htdocs\App_Web\`, manteniendo juntas las carpetas `LOGIN`, `Seguir`, `CRUD` y `Foto`.
4. Iniciar el servidor con `C:\xampp\php\php.exe -S localhost:8000 -t C:\xampp\htdocs\App_Web`.
5. Abrir `http://localhost:8000/Seguir/`; el inicio de sesión lleva a la bienvenida y cursos.

**Para compartir con Cloudflare** (igual que en LOGIN, solo cambia la carpeta):
```text
PowerShell n.º 1:  C:\xampp\php\php.exe -S localhost:8000 -t C:\xampp\htdocs\App_Web
PowerShell n.º 2:  cloudflared tunnel --url http://localhost:8000
```

## 11. Guía de prueba (para el profesor, antes de la clase)

1. Abrir `http://localhost/seguir` en una **ventana de incógnito** → debe salir el aviso de cookies.
2. **Aceptar**. Con F12 → *Application* → *Cookies*, comprobar que existen `analitica=si` y `visitante_id`.
3. Ir a **Regístrate** y volver; después **iniciar sesión**.
4. En F12 → *Network*, filtrar por `eventos`: cada clic genera un POST con respuesta **204**.
5. Ir a **Cursos** y pulsar varios **Me interesa**.
6. Abrir **Métricas**: en *Actividad reciente* aparecen también los eventos de **antes** del login, ya con tu nombre (sección 7).
7. Abrir otro navegador o el celular con el panel a la vista: los eventos nuevos aparecen en máximo 5 segundos.
8. En otra ventana de incógnito pulsar **Rechazar** y navegar: en Network **no** debe aparecer ningún `eventos`.
9. Abrir `http://localhost/seguir/api/metricas.php` sin sesión → `401`. Con sesión → el JSON.
10. Cambiar de pestaña 10 segundos y volver: ese tiempo no se cuenta.

Para **reiniciar los datos** antes de clase: `TRUNCATE railway.eventos;` en phpMyAdmin.

## 12. Ideas para extender (retos opcionales)

- **Mapa de calor**: guardar las coordenadas `x, y` del clic y dibujarlas sobre la página.
- **Profundidad de scroll**: ¿hasta qué porcentaje de la página bajan?
- **Recorrido de una sesión**: endpoint `GET api/sesion.php?id=…` que regrese los pasos en orden.
- **Filtro por fechas** en `api/metricas.php` (`?desde=…&hasta=…`).
- **Panel solo para administradores**: hoy cualquier usuario con sesión puede ver las métricas de todos.
- **Derecho de cancelación**: un botón "borrar mis datos" que elimine los eventos del usuario.
