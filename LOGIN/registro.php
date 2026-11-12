<?php
require 'conexion.php';

$error = '';
$adminEmail = strtolower(trim((string) (getenv('ADMIN_EMAIL') ?: 'ponceitzel65@gmail.com')));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $correo = strtolower(trim((string) ($_POST['correo'] ?? '')));
    $sexo = (string) ($_POST['sexo'] ?? '');
    $edad = filter_var($_POST['edad'] ?? null, FILTER_VALIDATE_INT);
    $carrera = trim((string) ($_POST['carrera'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmar = (string) ($_POST['confirmar'] ?? '');

    if ($correo !== '' && hash_equals($adminEmail, $correo)) {
        $error = 'Ese correo está reservado para la cuenta administradora y no puede registrarse.';
    } elseif ($nombre === '' || $carrera === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)
        || !in_array($sexo, ['Hombre', 'Mujer', 'Otro'], true) || $edad === false || $edad < 15 || $edad > 99) {
        $error = 'Revisa que todos los datos sean válidos.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($password !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = 'INSERT INTO usuarios (nombre, correo, sexo, edad, carrera, password_hash) VALUES (?, ?, ?, ?, ?, ?)';
        try {
            $pdo->prepare($sql)->execute([$nombre, $correo, $sexo, $edad, $carrera, $hash]);
            header('Location: login.php?registrado=1');
            exit;
        } catch (PDOException $ex) {
            if ($ex->getCode() !== '23000') {
                throw $ex;
            }
            $error = 'Ese correo ya está registrado.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Únete como colaborador | Patitas con Hogar</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <main class="tarjeta">
        <p><a href="/Foto/public/index.html">🐾 Patitas con Hogar</a></p>
        <h1>Únete como colaborador</h1>
        <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
        <form method="post" action="registro.php" autocomplete="on">
            <label>Nombre
                <input type="text" name="nombre" required maxlength="100" autocomplete="name" value="<?= e($_POST['nombre'] ?? '') ?>">
            </label>
            <label>Correo
                <input type="email" name="correo" required maxlength="150" autocomplete="email" value="<?= e($_POST['correo'] ?? '') ?>">
            </label>
            <label>Sexo
                <select name="sexo" required>
                    <option value="">Selecciona una opción</option>
                    <?php foreach (['Hombre', 'Mujer', 'Otro'] as $opcion): ?>
                        <option value="<?= e($opcion) ?>" <?= (($_POST['sexo'] ?? '') === $opcion) ? 'selected' : '' ?>><?= e($opcion) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Edad
                <input type="number" name="edad" min="15" max="99" required value="<?= e($_POST['edad'] ?? '') ?>">
            </label>
            <label>Ocupación o carrera
                <input type="text" name="carrera" required maxlength="100" value="<?= e($_POST['carrera'] ?? '') ?>">
            </label>
            <label>Contraseña
                <input type="password" name="password" required minlength="8" autocomplete="new-password">
            </label>
            <label>Confirma tu contraseña
                <input type="password" name="confirmar" required minlength="8" autocomplete="new-password">
            </label>
            <button type="submit">Crear cuenta</button>
        </form>
        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
        <nav class="enlaces-app" aria-label="Otras secciones">
            <a href="/Foto/public/index.html">Inicio</a>
            <a href="/ubicacion.html">Ubicación</a>
            <a href="/Foto/public/encuesta.html">Encuesta</a>
        </nav>
    </main>
</body>
</html>
