<?php
require_once __DIR__ . '/auth.php';
require_login();
$stmt = $pdo->prepare('SELECT nombre, correo, sexo, edad, carrera FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$usuario) { header('Location: ../LOGIN/logout.php'); exit; }
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Mi cuenta | Patitas con Hogar</title><link rel="stylesheet" href="estilos.css"></head><body class="pagina-app">
<header class="app-header"><a class="marca" href="/Foto/public/index.html">🐾 Patitas con Hogar</a><nav class="menu"><a href="bienvenida.php" aria-current="page">Mi cuenta</a><a href="cursos.php">Formas de ayudar</a><a href="dashboard.php">Mis apoyos</a><a href="../LOGIN/logout.php">Salir</a></nav></header>
<main class="contenedor"><section class="encabezado-pagina"><p class="ceja">Bienvenido/a</p><h1>Hola, <?= e($usuario['nombre']) ?></h1><p>Desde aquí puedes ver las actividades que te interesan y administrar tus fechas de ayuda.</p></section>
<div class="grid-resumen"><section class="panel"><h2>Tu perfil</h2><dl class="datos-perfil"><dt>Correo</dt><dd><?= e($usuario['correo']) ?></dd><dt>Carrera</dt><dd><?= e($usuario['carrera']) ?></dd></dl></section><section class="panel"><h2>¿Cómo quieres participar?</h2><p>Agenda un paseo, ayuda en el refugio, participa en una colecta o elige otra forma de apoyar.</p><a class="boton" href="cursos.php">Explorar formas de ayudar</a></section><section class="panel"><h2>Consulta tus fechas</h2><p>Revisa tus apoyos agendados o cancela una actividad si cambian tus planes.</p><a class="boton boton-secundario" href="dashboard.php">Ver mis apoyos</a></section></div></main></body></html>
