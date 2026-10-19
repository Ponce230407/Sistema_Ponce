<?php
require_once 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $correo    = trim($_POST['correo'] ?? '');
    $sexo      = $_POST['sexo'] ?? '';
    $edad      = (int) ($_POST['edad'] ?? 0);
    $carrera   = trim($_POST['carrera'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($nombre === '' || $carrera === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || !in_array($sexo, ['Hombre', 'Mujer', 'Otro'], true) || $edad < 15 || $edad > 99) {
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
    <title>Crear cuenta</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <main class="tarjeta">
        <h1>Crear cuenta</h1>

        <?php if (!empty($error)): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required value="<?= e($_POST['nombre'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo" required value="<?= e($_POST['correo'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="sexo">Sexo</label>
                <select id="sexo" name="sexo" required>
                    <option value="">Selecciona...</option>
                    <option value="Hombre" <?= (($_POST['sexo'] ?? '') === 'Hombre') ? 'selected' : '' ?>>Hombre</option>
                    <option value="Mujer" <?= (($_POST['sexo'] ?? '') === 'Mujer') ? 'selected' : '' ?>>Mujer</option>
                    <option value="Otro" <?= (($_POST['sexo'] ?? '') === 'Otro') ? 'selected' : '' ?>>Otro</option>
                </select>
            </div>

            <div class="campo">
                <label for="edad">Edad</label>
                <input type="number" id="edad" name="edad" min="15" max="99" required value="<?= e($_POST['edad'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="carrera">Carrera</label>
                <input type="text" id="carrera" name="carrera" required value="<?= e($_POST['carrera'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="campo">
                <label for="confirmar">Confirmar contraseña</label>
                <input type="password" id="confirmar" name="confirmar" required>
            </div>

            <button type="submit">Registrarse</button>
        </form>

        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    </main>
</body>
</html>
