<?php
require 'conexion.php';
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../LOGIN/login.php');
    exit;
}
$stmt = $pdo->prepare('SELECT nombre, correo, sexo, edad, carrera FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Área de colaboradores | Patitas con Hogar</title><link rel="stylesheet" href="estilos.css"></head><body><main class="tarjeta"><?php include 'menu.php'; ?><h1>¡Hola, <?= e($usuario['nombre']) ?>! 🐾</h1><p>Gracias por formar parte de la comunidad de Patitas con Hogar. Desde aquí puedes conocer actividades y maneras de apoyar.</p><ul><li><strong>Correo:</strong> <?= e($usuario['correo']) ?></li><li><strong>Perfil:</strong> <?= e($usuario['carrera']) ?></li></ul><a class="boton" href="cursos.php" data-track="Ver formas de ayudar">Ver formas de ayudar</a><p><a href="logout.php">Cerrar sesión</a></p></main><script src="tracker.js"></script></body></html>
