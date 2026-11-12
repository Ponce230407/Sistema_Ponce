<?php
require_once __DIR__ . '/conexion.php';
$error = '';
$adminEmail = strtolower(trim(getenv('ADMIN_EMAIL') ?: 'ponceitzel65@gmail.com'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = strtolower(trim((string) ($_POST['correo'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $return = (string) ($_POST['return'] ?? '');
    $returnPath = '/Seguir/bienvenida.php';
    if ($return !== '' && str_starts_with($return, '/') && !str_starts_with($return, '//') && !str_contains($return, "\n")) {
        $returnPath = $return;
    }

    if (getenv('ADMIN_PASSWORD') !== false && getenv('ADMIN_PASSWORD') !== ''
        && hash_equals($adminEmail, $correo)
        && hash_equals((string) getenv('ADMIN_PASSWORD'), $password)) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = 0;
        $_SESSION['nombre'] = 'Administrador';
        $_SESSION['rol'] = 'admin';
        $_SESSION['admin_email'] = $adminEmail;
        header('Location: ../Seguir/admin.php', true, 303);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, nombre, correo, password_hash FROM usuarios WHERE correo = ?');
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int) $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = 'usuario';
        unset($_SESSION['admin_email']);
        if (isset($_COOKIE['visitante_id'])) {
            try {
                $pdo->prepare('UPDATE eventos SET usuario_id = ? WHERE visitante_id = ? AND usuario_id IS NULL')->execute([$usuario['id'], $_COOKIE['visitante_id']]);
            } catch (PDOException $ignored) {
                // Analytics are optional and must not break successful sign-in.
            }
        }
        header('Location: ' . $returnPath, true, 303);
        exit;
    }
    $error = 'Correo o contraseña incorrectos. Verifica también la configuración de acceso del administrador.';
}
$return = (string) ($_GET['return'] ?? $_POST['return'] ?? '');
$returnSafe = ($return !== '' && str_starts_with($return, '/') && !str_starts_with($return, '//') && !str_contains($return, "\n")) ? $return : '';
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Iniciar sesión | Patitas con Hogar</title><link rel="stylesheet" href="estilos.css"></head><body><main class="tarjeta"><p><a href="/Foto/public/index.html">🐾 Patitas con Hogar</a></p><h1>Iniciar sesión</h1><?php if (isset($_GET['registrado'])): ?><p class="exito">Cuenta creada. Ya puedes iniciar sesión.</p><?php endif; ?><?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?><form method="post"><input type="hidden" name="return" value="<?= e($returnSafe) ?>"><label>Correo<input type="email" name="correo" required autocomplete="username" value="<?= e($_POST['correo'] ?? '') ?>"></label><label>Contraseña<input type="password" name="password" required autocomplete="current-password"></label><button type="submit">Entrar</button></form><p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p><nav class="enlaces-app" aria-label="Otras secciones"><a href="/Foto/public/index.html">Inicio</a><a href="/Foto/ubicacion.php">Contacto</a><a href="/Foto/public/encuesta.html">Encuesta</a></nav></main><script src="/Seguir/tracker.js"></script></body></html>
