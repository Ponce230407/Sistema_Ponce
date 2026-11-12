<?php
require_once __DIR__ . '/auth.php';
require_login();
$stmt = $pdo->prepare('SELECT nombre, correo, sexo, edad, carrera FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$usuario) { header('Location: ../LOGIN/logout.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mi cuenta | Patitas con Hogar</title>
  <link rel="stylesheet" href="estilos.css">
</head>
<body class="pagina-app">
  <header class="app-header">
    <a class="marca" href="/Foto/public/index.html">🐾 Patitas con Hogar</a>
    <details class="menu-dropdown">
      <summary class="menu-toggle">Menú ☰</summary>
      <div class="menu-content">
        <a href="bienvenida.php">👤 Mi cuenta</a>
        <a href="cursos.php">📅 Horarios</a>
        <a href="dashboard.php">📊 Métricas</a>
        <a href="admin.php">🛡️ Administrador</a>
        <a href="/LOGIN/logout.php">🚪 Cerrar sesión</a>
      </div>
    </details>
  </header>
  <main class="contenedor">
    <section class="encabezado-pagina">
      <p class="ceja">Bienvenido/a</p>
      <h1>Hola, <?= e($usuario['nombre']) ?></h1>
      <p>Desde aquí puedes ver las actividades que te interesan y gestionar tu participación.</p>
    </section>
    <div class="grid-resumen">
      <section class="panel">
        <h2>Tu perfil</h2>
        <dl class="datos-perfil">
          <dt>Correo</dt>
          <dd><?= e($usuario['correo']) ?></dd>
          <dt>Carrera</dt>
          <dd><?= e($usuario['carrera']) ?></dd>
          <dt>Edad</dt>
          <dd><?= (int) $usuario['edad'] ?></dd>
          <dt>Sexo</dt>
          <dd><?= e($usuario['sexo']) ?></dd>
        </dl>
      </section>
      <section class="panel">
        <h2>Acciones rápidas</h2>
        <p>Consulta tus horarios agendados, explora maneras de ayudar y revisa tus métricas de apoyo.</p>
        <p><a class="boton" href="cursos.php">Ver formas de ayudar</a></p>
        <p><a class="boton" href="dashboard.php">Mis apoyos</a></p>
      </section>
    </div>
  </main>
</body>
</html>
