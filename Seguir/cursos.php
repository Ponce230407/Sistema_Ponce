<?php
require_once __DIR__ . '/auth.php';
require_login();

$actividades = [
    'Paseo y socialización',
    'Apoyo en el refugio',
    'Colecta de alimento',
    'Hogar temporal',
    'Feria de adopción',
    'Difusión responsable',
];
$mensaje = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Formas de ayudar | Patitas con Hogar</title><link rel="stylesheet" href="estilos.css">
</head>
<body class="pagina-app">
<header class="app-header"><a class="marca" href="/Foto/public/index.html">🐾 Patitas con Hogar</a><nav class="menu"><a href="bienvenida.php">Mi cuenta</a><a href="cursos.php" aria-current="page">Formas de ayudar</a><a href="dashboard.php">Mis apoyos</a><a href="../LOGIN/logout.php">Salir</a></nav></header>
<main class="contenedor">
  <section class="encabezado-pagina"><p class="ceja">Súmate a la causa</p><h1>Elige cómo quieres ayudar</h1><p>Selecciona una actividad y la fecha en que te gustaría participar. Puedes cancelar tu inscripción desde “Mis apoyos”.</p></section>
  <?php if ($mensaje): ?><p class="exito"><?= e($mensaje) ?></p><?php endif; ?>
  <div class="grid-actividades">
  <?php foreach ($actividades as $actividad): ?>
    <article class="panel actividad"><span class="icono-pata" aria-hidden="true">🐾</span><h2><?= e($actividad) ?></h2><p>Tu tiempo y entusiasmo pueden hacer una diferencia para los animales que esperan un hogar.</p>
      <form method="post" action="inscribirse.php" class="form-inscripcion">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="actividad" value="<?= e($actividad) ?>">
        <label>Fecha para ayudar<input type="date" name="fecha" min="<?= date('Y-m-d') ?>" required></label>
        <button type="submit">Agendar apoyo</button>
      </form>
    </article>
  <?php endforeach; ?>
  </div>
</main>
</body></html>
