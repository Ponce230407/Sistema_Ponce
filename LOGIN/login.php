<?php
require 'conexion.php';

$error = '';
$adminEmail = strtolower(trim(getenv('ADMIN_EMAIL') ?: 'ponceitzel65@gmail.com'));
$adminPassword = getenv('ADMIN_PASSWORD');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $password = $_POST['password'] ?? '';

    // The administrator password is provided as a Render environment secret, never committed here.
    if ($adminPassword !== false && $adminPassword !== ''
        && hash_equals($adminEmail, $correo)
        && hash_equals($adminPassword, $password)) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = 0;
        $_SESSION['nombre'] = 'Administrador';
        $_SESSION['rol'] = 'admin';
        $_SESSION['admin_email'] = $adminEmail;
        header('Location: ../Seguir/admin.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, nombre, correo, password_hash FROM usuarios WHERE correo = ?');
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int) $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        if (hash_equals($adminEmail, strtolower(trim($usuario['correo']))) && $adminPassword !== false && $adminPassword !== '') {
            $_SESSION['rol'] = 'admin';
            $_SESSION['admin_email'] = $adminEmail;
            header('Location: ../Seguir/admin.php');
        } else {
            $_SESSION['rol'] = 'usuario';
            header('Location: ../Seguir/bienvenida.php');
        }
        if (isset($_COOKIE['visitante_id'])) {
            $pdo->prepare('UPDATE eventos SET usuario_id = ? WHERE visitante_id = ? AND usuario_id IS NULL')
                ->execute([$usuario['id'], $_COOKIE['visitante_id']]);
        }
        exit;
    }

    $error = 'Correo o contraseña incorrectos. Verifica también la configuración de acceso del administrador.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | Patitas con Hogar</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <main class="tarjeta">
        <p><a href="/Foto/public/index.html">🐾 Patitas con Hogar</a></p>
        <h1>Iniciar sesión</h1>
        <?php if (isset($_GET['registrado'])): ?><p class="exito">Cuenta creada. Ya puedes iniciar sesión.</p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
        <form method="post">
            <label>Correo<input type="email" name="correo" required autocomplete="username" value="<?= e($_POST['correo'] ?? '') ?>"></label>
            <label>Contraseña<input type="password" name="password" required autocomplete="current-password"></label>
            <button type="submit">Entrar</button>
        </form>
        <p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
        <nav class="enlaces-app" aria-label="Otras secciones">
            <a href="/Foto/public/index.html">Inicio</a><a href="/ubicacion.html">Ubicación</a><a href="/CRUD/productos.php">Insumos</a><a href="/Foto/public/encuesta.html">Encuesta</a>
        </nav>
    </main>
    <script src="/Seguir/tracker.js"></script>
</body>
</html>
