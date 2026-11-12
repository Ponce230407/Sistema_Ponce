<?php
require 'conexion.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id, nombre, password_hash FROM usuarios WHERE correo = ?');
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int) $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = 'usuario';
        if (isset($_COOKIE['visitante_id'])) {
            try {
                $pdo->prepare('UPDATE eventos SET usuario_id = ? WHERE visitante_id = ? AND usuario_id IS NULL')
                    ->execute([$usuario['id'], $_COOKIE['visitante_id']]);
            } catch (PDOException $ignored) {
                // El acceso sigue funcionando si las métricas aún no se han instalado.
            }
        }
        header('Location: ../Seguir/bienvenida.php');
        exit;
    }
    $error = 'Correo o contraseña incorrectos.';
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Iniciar sesión | Patitas con Hogar</title><link rel="stylesheet" href="estilos.css"></head><body><main class="tarjeta"><h1>Iniciar sesión</h1><?php if (isset($_GET['registrado'])): ?><p class="exito">Cuenta creada. Ya puedes iniciar sesión.</p><?php endif; ?><?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?><form method="post"><label>Correo<input type="email" name="correo" required autocomplete="email" value="<?= e($_POST['correo'] ?? '') ?>"></label><label>Contraseña<input type="password" name="password" required autocomplete="current-password"></label><button type="submit">Entrar</button></form><p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p><nav class="enlaces-app"><a href="/Foto/public/index.html">Inicio</a><a href="/Foto/public/donaciones.html">Donaciones</a><a href="/ubicacion.html">Contacto</a></nav></main><script src="/Seguir/tracker.js"></script></body></html>
