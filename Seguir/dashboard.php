<?php
// Métricas internas del sitio. El panel.js obtiene los datos desde api/metricas.php.
require 'conexion.php';
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../LOGIN/login.php');
    exit;
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Métricas | Patitas con Hogar</title><link rel="stylesheet" href="estilos.css"></head><body><main class="tarjeta ancha"><?php include 'menu.php'; ?><h1>Métricas del sitio</h1><p class="nota">Estadísticas internas para conocer la actividad de nuestra comunidad. Se actualizan cada 5 segundos. <span id="estado"></span></p><section class="kpis" id="kpis"></section><h2>Actividad reciente</h2><div class="tabla"><table id="tabla-recientes"></table></div><h2>Páginas visitadas</h2><div class="tabla"><table id="tabla-paginas"></table></div><h2>Interacciones</h2><div class="tabla"><table id="tabla-clics"></table></div><h2>Colaboradores</h2><div class="tabla"><table id="tabla-usuarios"></table></div><h2>Perfiles de colaboración</h2><div class="tabla"><table id="tabla-carreras"></table></div></main><script src="panel.js"></script><script src="tracker.js"></script></body></html>
