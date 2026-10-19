<?php
include 'conexion.php'; // Al incluirlo, la variable $pdo ya estará lista para usarse aquí

// Resto de tu código de login o registro...
?>
require 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo   = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, nombre, password_hash FROM usuarios WHERE correo = ?');
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre']     = $usuario['nombre'];

        if (isset($_COOKIE['visitante_id'])) {
            $pdo->prepare('UPDATE eventos SET usuario_id = ? WHERE visitante_id = ? AND usuario_id IS NULL')
                ->execute([$usuario['id'], $_COOKIE['visitante_id']]);
        }

        header('Location: ../Seguir/bienvenida.php');
        exit;
    }
    $error = 'Correo o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <main class="tarjeta">
        <h1>Iniciar sesión</h1>

        <?php if (isset($_GET['registrado'])): ?>
            <p class="exito">Cuenta creada. Ya puedes iniciar sesión.</p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <label>Correo
                <input type="email" name="correo" required value="<?= e($_POST['correo'] ?? '') ?>">
            </label>
            <label>Contraseña
                <input type="password" name="password" required>
            </label>
            <button type="submit">Entrar</button>
        </form>

        <p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
        <nav class="enlaces-app" aria-label="Otras secciones">
            <a href="/Foto/public/index.html">Inicio</a>
            <a href="/ubicacion.html">Ubicación</a>
            <a href="/CRUD/productos.php">Productos</a>
            <a href="/Seguir/cursos.php">Cursos</a>
        </nav>
    </main>
    <script src="/Seguir/tracker.js"></script>
</body>
</html>
