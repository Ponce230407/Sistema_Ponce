# Aplicación de Login con PHP, MySQL, HTML y JS

Módulo de registro e inicio de sesión integrado con el resto del sitio en `App_Web`. `LOGIN` y `Seguir` comparten usuarios, sesión PHP y base de datos.

---

## 1. ¿Qué hace la aplicación?

1. El usuario **se registra** con nombre, correo, sexo, edad, carrera y contraseña.
2. La contraseña **no se guarda tal cual**: se guarda su **hash** (bcrypt).
3. El usuario **inicia sesión** con correo y contraseña.
4. Si los datos son correctos, entra a una **página protegida** con sus datos.
5. Puede **cerrar sesión**.

## 2. Tecnologías y su papel

| Pieza | Papel |
|---|---|
| **HTML** | Estructura de los formularios y las páginas. |
| **CSS** | Apariencia (`estilos.css`). |
| **JavaScript** | Validación en el navegador: que las dos contraseñas coincidan (`app.js`). |
| **PHP** | Lógica del servidor: recibe formularios, valida, calcula el hash, consulta la BD y maneja sesiones. |
| **MySQL / MariaDB** | Base de datos donde se guardan los usuarios. |
| **XAMPP** | Paquete que instala Apache, PHP y MySQL en Windows. |
| **Cloudflare Tunnel** | Publica la app en una URL pública `https://….trycloudflare.com`. |

## 3. Estructura de archivos

```
App_Web/
├── LOGIN/              ← registro e inicio de sesión
├── Seguir/             ← cursos y métricas; usa la sesión de LOGIN
├── CRUD/               ← catálogo de productos
└── Foto/               ← sitio principal, encuesta y ubicación
LOGIN/
├── database.sql       ← crea la base de datos y la tabla
├── conexion.php       ← conexión a MySQL + inicio de sesión PHP + función e()
├── index.php          ← redirige a login.php
├── registro.php       ← formulario y lógica de registro
├── login.php          ← formulario y lógica de inicio de sesión
├── bienvenida.php     ← página protegida
├── logout.php         ← cierra la sesión
├── app.js             ← validación del lado del cliente
├── estilos.css        ← estilos
└── EXPLICACION.md     ← este documento
```

No copies `LOGIN` por separado: sus páginas enlazan con `Seguir` y el resto del sitio. Publica la carpeta completa `App_Web` como raíz del servidor web.

## 4. Flujo de la aplicación

```
 LOGIN/index.php ──► login.php ◄────────┐
                 │   ▲                  │
       ¿sin cuenta?  │ registrado=1     │
                 ▼   │                  │
             registro.php               │
                                        │
 LOGIN/login.php ──(correo+contraseña OK)──► Seguir/bienvenida.php ──► logout.php
```

- `Seguir/bienvenida.php` revisa si existe `$_SESSION['usuario_id']`; si no existe, regresa al login.
- Después de procesar un formulario se redirige con `header('Location: …')` (patrón **Post/Redirect/Get**). Así, al recargar la página, el navegador no reenvía el formulario.

---

## 5. Base de datos (`database.sql`)

```sql
CREATE TABLE usuarios (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(100) NOT NULL,
    correo        VARCHAR(150) NOT NULL UNIQUE,
    sexo          ENUM('Hombre','Mujer','Otro') NOT NULL,
    edad          TINYINT UNSIGNED NOT NULL,
    carrera       VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    creado_en     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Puntos para comentar en clase:

- **`correo UNIQUE`**: la propia base de datos impide correos repetidos. Si se intenta repetir uno, MySQL lanza el error `23000`, que `registro.php` captura para mostrar *"Ese correo ya está registrado"*.
- **`ENUM`**: limita `sexo` a tres valores válidos.
- **`TINYINT UNSIGNED`**: guarda de 0 a 255, más que suficiente para una edad.
- **`password_hash VARCHAR(255)`**: no se llama `password` porque **nunca** contiene la contraseña. Se usan 255 caracteres porque PHP recomienda ese tamaño por si el algoritmo de hash cambia en el futuro.
- **`utf8mb4`**: soporta acentos, ñ y emojis.

## 6. Conexión (`conexion.php`)

```php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'railway';
$port = getenv('DB_PORT') ?: '3306';
$pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4", $user, $password,
               [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
session_start();
function e($texto) { return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); }
```

- **PDO** es la forma estándar y moderna de conectarse a una base de datos desde PHP.
- `ERRMODE_EXCEPTION`: ante cualquier error de SQL se lanza una excepción, en vez de fallar en silencio.
- `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME` y `DB_PORT`: configuración de MySQL; también se leen las variables `MYSQLHOST`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE` y `MYSQLPORT` de Railway. En local, si no hay variables, se usa `localhost`, `root` y la base `railway`.
- `Seguir/conexion.php` incluye esta misma conexión, por eso el login y las métricas usan los mismos usuarios y sesión.
- PHP debe tener habilitado `pdo_mysql`; `Dockerfile.render` lo instala. `DB_NAME` debe ser `railway`, donde se ejecuta el esquema común `App_Web/database.sql`.
- `session_start()`: activa las sesiones. Va aquí porque todas las páginas que usan la BD también usan la sesión.
- `e()`: función corta para **escapar** texto antes de mostrarlo en HTML (ver sección 9).

## 7. Registro (`registro.php`)

Pasos que ocurren cuando llega el formulario (`$_SERVER['REQUEST_METHOD'] === 'POST'`):

1. **Leer los datos** de `$_POST` con `?? ''` para evitar avisos si falta alguno, y `trim()` para quitar espacios.
2. **Validar en el servidor**:
   - nombre y carrera no vacíos;
   - correo con formato válido (`filter_var(..., FILTER_VALIDATE_EMAIL)`);
   - sexo dentro de la lista permitida;
   - edad entre 15 y 99;
   - contraseña de al menos 8 caracteres y que coincida con la confirmación.
3. **Calcular el hash** de la contraseña:
   ```php
   $hash = password_hash($password, PASSWORD_DEFAULT);
   ```
4. **Insertar** con una **consulta preparada** (`?` como marcadores).
5. Si todo sale bien, **redirigir** a `login.php?registrado=1`. Si el correo ya existe, mostrar el error.

> **¿Por qué validar dos veces (JS y PHP)?** La validación con JavaScript y HTML (`required`, `minlength`, `type="email"`) es para la **comodidad** del usuario, porque responde al instante. La de PHP es la que da **seguridad**: cualquiera puede desactivar JavaScript o enviar peticiones con herramientas como Postman o curl. **Nunca hay que confiar solo en el cliente.**

## 8. Hash de contraseñas: el corazón del tema

### ¿Qué es un hash?

Es una función **de un solo sentido**: a partir de la contraseña produce un texto de longitud fija, y **no existe forma de revertirlo** para recuperar la contraseña original.

```
"MiClave123"  ──password_hash──►  $2y$10$Qe7fX9...k3L2wV8uZpA1bO  (60 caracteres)
```

### Anatomía de un hash bcrypt

```
$2y$10$Qe7fX9mRb1pT3cH8nW0aLe5Gq7yJk2sD4fV6hN8mB0xC1zA3sE5uK
 │   │  └────── sal (22 car.) ────┘└────── hash (31 car.) ──────┘
 │   └─ costo: 2^10 = 1024 rondas
 └─ algoritmo: bcrypt
```

- **Sal (salt)**: un valor aleatorio distinto en cada registro. Por eso **dos usuarios con la misma contraseña tienen hashes diferentes**. Es una buena demostración en clase: registrar a dos usuarios con la misma clave y comparar en phpMyAdmin.
- **Costo**: hace el cálculo deliberadamente **lento**. Para un login apenas se nota, pero un atacante que quiera probar millones de contraseñas tardaría muchísimo.
- La sal y el costo **viajan dentro del propio hash**, así que no hace falta guardarlos en otra columna.

### ¿Cómo se verifica al iniciar sesión?

```php
password_verify($passwordEscrita, $hashGuardado)  // true o false
```

PHP toma la sal y el costo del hash guardado, vuelve a calcular el hash de la contraseña escrita y compara ambos resultados.

### ¿Por qué no usar MD5 o SHA-1?

Son algoritmos **rápidos** y **sin sal**: con GPUs se prueban miles de millones de combinaciones por segundo, y existen tablas precalculadas (*rainbow tables*). **Nunca deben usarse para contraseñas.** `password_hash` / `password_verify` es lo recomendado oficialmente en PHP.

## 9. Inicio de sesión (`login.php`)

```php
$stmt = $pdo->prepare('SELECT id, nombre, password_hash FROM usuarios WHERE correo = ?');
$stmt->execute([$correo]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario && password_verify($password, $usuario['password_hash'])) {
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuario['id'];
    ...
}
$error = 'Correo o contraseña incorrectos.';
```

- Se busca al usuario **por correo**. Después se compara la contraseña con `password_verify`; nunca se compara en SQL.
- **Mensaje genérico**: no se dice si lo que falló fue el correo o la contraseña. Si se dijera, un atacante podría averiguar qué correos están registrados.
- **`session_regenerate_id(true)`**: genera un ID de sesión nuevo al entrar. Esto previene la **fijación de sesión** (*session fixation*), en la que un atacante obliga a la víctima a usar un ID de sesión que él ya conoce.

### ¿Qué es una sesión?

HTTP **no tiene memoria**: cada petición es independiente. PHP lo resuelve así:

1. Crea un archivo en el servidor con los datos (`$_SESSION`).
2. Envía al navegador una cookie `PHPSESSID` con un identificador aleatorio.
3. En cada petición siguiente, el navegador manda esa cookie y PHP recupera los datos.

Así, `bienvenida.php` sabe quién eres revisando `$_SESSION['usuario_id']`.

## 10. Página protegida y cierre de sesión

- **`bienvenida.php`**: si no hay sesión, redirige al login y termina con `exit`. **El `exit` es importante**: sin él, PHP seguiría ejecutando y enviaría la página protegida de todas formas. Si hay sesión, consulta los datos del usuario y los muestra.
- **`logout.php`**: vacía `$_SESSION`, destruye la sesión con `session_destroy()` y regresa al login.

## 11. Seguridad aplicada (resumen)

| Amenaza | Defensa en el código |
|---|---|
| Robo de la BD ⇒ contraseñas expuestas | `password_hash` (bcrypt con sal y costo) |
| **Inyección SQL** (`' OR 1=1 --`) | Consultas preparadas con `?` (`prepare` + `execute`) |
| **XSS**: alguien se registra con el nombre `<script>…</script>` | `e()` / `htmlspecialchars` al imprimir cualquier dato |
| Fijación de sesión | `session_regenerate_id(true)` |
| Enumeración de usuarios | Mensaje de error genérico en el login |
| Correos duplicados | Restricción `UNIQUE` en la BD |
| Datos inválidos | Validación en el servidor, no solo en JS |

> **Demostración de XSS para clase:** registra a un usuario con el nombre `<b>Hola</b>`. Gracias a `e()`, en la bienvenida se ve literalmente `<b>Hola</b>` en lugar de texto en negritas. Si quitas `e()`, el HTML se interpreta, y lo mismo pasaría con un `<script>`.

---

## 12. Puesta en marcha local (paso a paso)

1. **Iniciar MySQL** en el *XAMPP Control Panel*.
2. En Railway, selecciona la base existente `railway` y ejecuta una vez el contenido de `App_Web/database.sql`. Crea todas las tablas requeridas por el proyecto sin crear otra base de datos.
3. Copiar el contenido completo de `App_Web` a `C:\xampp\htdocs\App_Web\`, conservando las carpetas `LOGIN`, `Seguir`, `CRUD` y `Foto`.
4. Iniciar el servidor para que `App_Web` sea la raíz del sitio:
   `C:\xampp\php\php.exe -S localhost:8000 -t C:\xampp\htdocs\App_Web`
5. Probar `http://localhost:8000/LOGIN/` y luego navegar a `http://localhost:8000/Seguir/`.

En Render, configura las variables `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME` y `DB_PORT` en el panel del servicio. Si Render se conecta a Railway, usa el host y el puerto públicos de `MYSQL_PUBLIC_URL`; el host `MYSQLHOST` de Railway es interno y no se puede resolver desde Render. `DB_NAME` debe ser `railway`. No guardes credenciales en el código ni en archivos del proyecto.

### ¿Por qué `htdocs`?

El servidor debe usar `App_Web` como raíz para que las rutas compartidas (`/LOGIN`, `/Seguir`, `/CRUD` y `/Foto`) funcionen desde todas las páginas.

---

## 13. Compartir la app por internet con Cloudflare Tunnel

> Esta sección parte **desde cero**: no hace falta saber nada de redes ni de Cloudflare.

### 13.1 Palabras clave antes de empezar

| Término | Explicación sencilla |
|---|---|
| **Servidor** | Programa que espera peticiones y responde con páginas. En nuestro caso, PHP. |
| **`localhost`** | Significa **"esta misma computadora"**. Siempre apunta al equipo desde el que se escribe. |
| **Puerto** | Número que distingue a los programas que escuchan en una misma PC, como las extensiones de un conmutador telefónico. Apache usa el 80, MySQL el 3306 y nuestro servidor PHP el 8000. |
| **IP privada** | Dirección de tu PC **dentro** de tu red (ej. `192.168.1.25`). Solo sirve dentro de esa red. |
| **IP pública** | Dirección con la que tu red completa sale a internet. La comparten todos los equipos de tu casa o escuela. |
| **Router** | Aparato que conecta tu red con internet y decide qué tráfico entra y qué sale. |
| **URL** | Dirección web completa, como `https://algo.trycloudflare.com/login.php`. |
| **Terminal** | Ventana donde se escriben comandos (en Windows: **PowerShell**). |

### 13.2 El problema: ¿por qué mis alumnos no pueden entrar a `localhost`?

Si un alumno escribe `http://localhost:8000` en **su** celular, el celular busca la página **en sí mismo**, no en tu PC. Por eso no funciona.

**Una analogía: el edificio de departamentos**

- Tu red (casa o escuela) es un **edificio**.
- Tu PC es **un departamento** dentro de ese edificio.
- El **router** es la **recepción** del edificio.
- Desde afuera solo se conoce la dirección del edificio (la IP pública), **no el número de departamento**.
- La recepción **no deja pasar visitas que nadie pidió**: si alguien llega preguntando por "la app de login", no sabe a qué departamento mandarlo y no lo deja entrar.

La solución tradicional es **abrir puertos**: pedirle a la recepción que mande al departamento X a toda visita que pregunte por la puerta 8000. Pero eso requiere:

- acceso a la configuración del router (en la escuela casi nunca se tiene);
- que el proveedor de internet dé una IP pública propia (muchos no la dan);
- y deja tu PC expuesta directamente a internet.

### 13.3 ¿Qué es Cloudflare?

**Cloudflare** es una empresa que opera una de las redes más grandes de internet: una enorme cantidad de sitios web pasan por sus servidores para ser más rápidos y seguros. Entre sus servicios gratuitos está **Cloudflare Tunnel**, que resuelve justo nuestro problema.

### 13.4 ¿Qué es un túnel?

Siguiendo con la analogía: en vez de esperar a que las visitas entren al edificio, **tú sales y haces una llamada** a una oficina de Cloudflare, que sí tiene una dirección pública conocida por todo el mundo. **Esa llamada se queda abierta.**

- Cuando alguien llega a la oficina de Cloudflare y pregunta por tu app, Cloudflare **le pasa el mensaje por esa llamada**.
- Tu PC responde **por la misma llamada**, y Cloudflare le entrega la respuesta al visitante.
- La recepción (router) no bloquea nada, porque **la llamada la hiciste tú desde adentro**: es una conexión **saliente**, como cuando abres cualquier página web.

```
   Alumno (celular / otra PC)                Cloudflare                          Tu PC
  ─────────────────────────────    ──────────────────────────────    ──────────────────────────────
                                                                       ┌───────────────────────┐
   Navegador ──── HTTPS ────────►  https://xxxx.trycloudflare.com      │ cloudflared           │
                                          │                            │   │                   │
                                          └══════ túnel ═══════════════►   ▼                   │
                                            (conexión que abrió tu PC) │ PHP en localhost:8000 │
                                                                       │   │                   │
                                                                       │   ▼                   │
                                                                       │ MySQL (railway)       │
                                                                       └───────────────────────┘
```

**Recorrido de una visita, paso a paso:**

1. El alumno abre `https://xxxx.trycloudflare.com` en su celular.
2. La petición llega a los servidores de Cloudflare.
3. Cloudflare la envía por el túnel a tu PC.
4. El programa `cloudflared` la recibe y se la pasa a PHP en `localhost:8000`.
5. PHP ejecuta `login.php`, consulta MySQL si hace falta y genera el HTML.
6. La respuesta regresa por el túnel a Cloudflare, y de ahí al celular del alumno.

Todo esto ocurre en fracciones de segundo.

**Ventaja extra:** la URL usa **HTTPS** (el candado del navegador). Cloudflare pone el certificado de seguridad sin que tengas que configurar nada.

### 13.5 Las piezas que intervienen

| Pieza | Qué es |
|---|---|
| **`cloudflared`** | Programa que se instala en tu PC y abre el túnel. La "d" final viene de *daemon* (programa que trabaja en segundo plano). |
| **Quick Tunnel** | Modalidad **gratuita y sin cuenta**. Con un solo comando obtienes una URL temporal. Es la que usamos. |
| **`trycloudflare.com`** | Dominio donde Cloudflare crea las URL de los Quick Tunnels, con nombres aleatorios como `strengths-newspaper-arlington-trace.trycloudflare.com`. |

### 13.6 Instalar `cloudflared` (solo una vez por computadora)

> En la PC del profesor **ya está instalado** (versión 2026.10.0), así que este paso puede saltarse ahí.

1. Abre **PowerShell**: pulsa la tecla **Windows**, escribe `PowerShell` y pulsa **Enter**.
2. Comprueba si ya está instalado:
   ```powershell
   cloudflared --version
   ```
   - Si aparece algo como `cloudflared version 2026.10.0`, **ya está listo**.
   - Si dice que *no se reconoce como un comando*, instálalo con el siguiente paso.
3. Instálalo con **winget** (el instalador de programas de Windows):
   ```powershell
   winget install --id Cloudflare.cloudflared
   ```
   Si pide confirmación, acepta.
4. **Cierra PowerShell y ábrelo de nuevo**, para que reconozca el programa recién instalado, y repite el paso 2.

*Alternativa sin winget:* descargar `cloudflared-windows-amd64.msi` desde <https://github.com/cloudflare/cloudflared/releases> e instalarlo con doble clic.

### 13.7 ⚠️ Antes de compartir: qué **no** hay que publicar

Lo que esté conectado al túnel **lo puede ver cualquiera que tenga el enlace**.

Si conectaras el túnel a Apache (`http://localhost:80`), se publicaría **todo** `C:\xampp\htdocs`, **incluido phpMyAdmin**. Además, como las visitas del túnel llegan a través de `cloudflared`, que está en tu misma PC, XAMPP creería que eres tú y **les daría acceso**. Con `root` sin contraseña, **cualquiera podría ver o borrar tus bases de datos**.

Por eso **no usamos Apache para compartir**. Usamos el **servidor integrado de PHP** apuntando **solo** a la carpeta `login`. Así, lo único visible desde internet es nuestra app.

| Conectar el túnel a… | ¿Qué queda público? | ¿Seguro? |
|---|---|---|
| `http://localhost:80` (Apache) | Todo `htdocs` + phpMyAdmin | ❌ No |
| `http://localhost:8000` (PHP con `-t …\App_Web`) | El sitio completo, sin exponer phpMyAdmin | ✅ Sí |

### 13.8 Paso a paso para compartir la app

**Requisitos previos:**

- [ ] MySQL encendido (botón **Start** en el XAMPP Control Panel).
- [ ] Todo el proyecto copiado a `C:\xampp\htdocs\App_Web\` (sección 12).
- [ ] Las tablas creadas con `LOGIN/database.sql` y `Seguir/database.sql`.

Vas a necesitar **dos ventanas de PowerShell abiertas al mismo tiempo**. Cada una ejecuta un programa que se queda trabajando, así que **no hay que cerrarlas** mientras dure la clase.

#### Paso 1. Encender la app (PowerShell n.º 1)

```powershell
C:\xampp\php\php.exe -S localhost:8000 -t C:\xampp\htdocs\App_Web
```

| Parte del comando | Significado |
|---|---|
| `C:\xampp\php\php.exe` | El programa PHP que viene con XAMPP. |
| `-S localhost:8000` | "Arranca un **S**ervidor web en esta PC, puerto 8000". |
| `-t C:\xampp\htdocs\App_Web` | "Sirve solo la carpeta del proyecto integrado" (*t* de *target*, destino). |

Verás algo como:

```
[Mon Oct 12 08:00:00 2026] PHP 8.2.12 Development Server (http://localhost:8000) started
```

✅ **Comprueba:** abre `http://localhost:8000/LOGIN/` en tu navegador. Debe aparecer **Iniciar sesión**.

#### Paso 2. Abrir el túnel (PowerShell n.º 2)

Abre **otra** ventana de PowerShell y escribe:

```powershell
cloudflared tunnel --url http://localhost:8000
```

Significa: "abre un túnel y manda todo lo que llegue a **mi** `localhost:8000`".

Aparecerán **muchas líneas en inglés**. Algunas empiezan con `INF` (información) y otras con `WRN` (advertencia). **Es normal y no son errores.** Por ejemplo, este aviso se puede ignorar:

```
WRN Cannot determine default configuration path. No file [config.yml config.yaml] ...
```

**La única parte que importa** es este recuadro:

```
INF +--------------------------------------------------------------------------------------------+
INF |  Your quick Tunnel has been created! Visit it at (it may take some time to be reachable):  |
INF |  https://strengths-newspaper-arlington-trace.trycloudflare.com                             |
INF +--------------------------------------------------------------------------------------------+
```

("Tu túnel rápido fue creado. Visítalo en… puede tardar un poco en estar disponible".)

Para copiar la URL, selecciónala con el mouse y pulsa **clic derecho** (o `Ctrl + C`).

> ⏱️ Espera **entre 10 y 30 segundos** antes de abrirla. Si la abres de inmediato, puede marcar error.

#### Paso 3. Probarla tú primero

Abre la URL en **tu celular con datos móviles** (con el Wi-Fi apagado). Si carga la página de login, la estás viendo **desde internet**, igual que la verán tus alumnos.

#### Paso 4. Compartir el enlace

- Pégalo en WhatsApp, Classroom, Teams o en el pizarrón.
- **Código QR:** en Chrome o Edge, abre la URL, haz clic derecho sobre la página y elige **"Crear código QR para esta página"**. Luego proyéctalo para que lo escaneen.

#### Paso 5. Durante la clase

En la **ventana n.º 1** (PHP) aparece **una línea por cada visita**:

```
[::1]:52344 Accepted
[::1]:52344 [302]: POST /registro.php
[::1]:52345 [200]: GET /login.php?registrado=1
```

Sirve para mostrar en vivo lo que recibe el servidor: un `POST` al enviar el formulario, el código `302` de la redirección, etc.

#### Paso 6. Terminar

Haz clic en cada ventana y pulsa **`Ctrl + C`**, primero en la del túnel y luego en la de PHP. El enlace **deja de funcionar al instante**.

### 13.9 Preguntas frecuentes

**¿Cuesta algo? ¿Necesito una cuenta?**
No. El Quick Tunnel es gratuito y no requiere registrarse.

**¿La URL siempre es la misma?**
No. **Cambia cada vez** que ejecutas el comando del paso 2. Si cierras el túnel y lo vuelves a abrir, tendrás que compartir la nueva URL.

**¿Funciona si apago o suspendo la PC?**
No. La app vive en tu PC: si se apaga, se suspende, se cierra la tapa de la laptop o se va el internet, el enlace deja de responder. Conviene desactivar la suspensión automática durante la clase. Si solo se corta el internet un momento, `cloudflared` se reconecta solo.

**¿Mis alumnos necesitan instalar algo?**
No. Solo necesitan un navegador.

**¿Cuántas personas pueden usarla a la vez?**
Para un grupo de clase funciona bien. El servidor integrado de PHP atiende las peticiones **de una en una**, pero cada petición dura milisegundos. Para cientos de usuarios simultáneos se usaría Apache o un servidor real.

**¿Cloudflare puede ver los datos?**
Técnicamente el tráfico pasa por sus servidores. Para una práctica de clase no es problema, pero **pide a los alumnos que no usen contraseñas reales** que usen en otros sitios.

**¿Se puede tener una URL fija con nombre propio?**
Sí, con un **Named Tunnel**: requiere una cuenta gratuita de Cloudflare y un dominio propio (ej. `login.midominio.com`). Es la opción para algo permanente, pero está fuera del alcance de esta práctica.

### 13.10 Problemas comunes

| Lo que ves | Qué significa | Solución |
|---|---|---|
| `cloudflared` *no se reconoce como un comando* | No está instalado, o PowerShell se abrió antes de instalarlo | Instalarlo (13.6) y **reabrir** PowerShell. |
| Página de Cloudflare con **Error 1033** | El túnel ya no existe | Revisar que la ventana n.º 2 siga abierta; si se cerró, repetir el paso 2 y usar la **nueva** URL. |
| Página de Cloudflare con **Error 502 / Bad gateway** | El túnel funciona, pero no encuentra la app | La ventana n.º 1 (PHP) está cerrada o el puerto no coincide (8000 en ambos comandos). |
| La URL no abre en los primeros segundos | El túnel aún se está activando | Esperar 10–30 segundos y recargar. |
| *Failed to listen on localhost:8000* | Otro programa usa el puerto 8000 | Usar otro número **en ambos comandos**, ej. `8001`. |
| *SQLSTATE[HY000] [2002]* | MySQL está apagado | Encenderlo en XAMPP. |
| *Access denied for user 'root'* | Las credenciales no coinciden | Configurar `DB_USER` y `DB_PASSWORD` en el entorno. |
| *Unknown database 'railway'* | El servidor o la base configurada no corresponden | Verificar `DB_NAME` o `MYSQLDATABASE` y seleccionar la base correcta. |
| Funciona en tu PC pero no en el celular | Estás compartiendo `localhost` en vez de la URL de Cloudflare | Compartir la URL `https://….trycloudflare.com`. |

### 13.11 Resumen rápido

```text
0. XAMPP: MySQL encendido.
1. PowerShell n.º 1:  C:\xampp\php\php.exe -S localhost:8000 -t C:\xampp\htdocs\App_Web
2. PowerShell n.º 2:  cloudflared tunnel --url http://localhost:8000
3. Copiar la URL https://….trycloudflare.com, esperar unos segundos y probarla en el celular.
4. Compartirla (enlace o código QR).
5. Al terminar: Ctrl + C en ambas ventanas.
```

---

## 14. Pruebas sugeridas en clase

1. Registrar un usuario, ir a phpMyAdmin y ver que la columna `password_hash` empieza con `$2y$10$…`.
2. Registrar a **dos usuarios con la misma contraseña** y comparar sus hashes: son distintos gracias a la sal.
3. Intentar registrar un **correo repetido**.
4. Iniciar sesión con una **contraseña incorrecta** y ver el mensaje genérico.
5. Abrir `bienvenida.php` **sin haber iniciado sesión**: redirige al login.
6. Iniciar sesión, cerrar sesión y pulsar **"Atrás"** en el navegador: al recargar, ya no deja entrar.
7. Desactivar JavaScript y enviar contraseñas que no coinciden: **PHP también lo detecta**.
8. Probar en el login el correo `' OR '1'='1`: la consulta preparada lo trata como texto normal.
9. Registrar el nombre `<b>Hola</b>` para ver el escape contra XSS.
10. Abrir la URL de Cloudflare desde un celular con **datos móviles** (no Wi-Fi) para demostrar que es acceso desde internet.
