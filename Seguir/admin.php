<?php
require_once __DIR__ . '/auth.php';
require_admin($pdo);
$hoy = (int) $pdo->query("SELECT COUNT(*) FROM apoyos WHERE estado = 'activo' AND fecha = CURDATE()")->fetchColumn();
$totalActivos = (int) $pdo->query("SELECT COUNT(*) FROM apoyos WHERE estado = 'activo'")->fetchColumn();
$totalUsuarios = (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
$inscripciones = $pdo->query("SELECT a.actividad, a.fecha, a.estado, u.nombre, u.correo FROM apoyos a JOIN usuarios u ON u.id = a.usuario_id ORDER BY a.fecha DESC, a.id DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
$eventos = $pdo->query("SELECT COUNT(DISTINCT visitante_id) visitantes, COUNT(DISTINCT sesion_id) sesiones, COALESCE(SUM(tipo = 'vista'),0) vistas, COALESCE(SUM(tipo = 'clic'),0) clics FROM eventos")->fetch(PDO::FETCH_ASSOC) ?: [];
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Administración | Patitas con Hogar</title><link rel="stylesheet" href="estilos.css"></head>
<body class="pagina-app">
<?php require __DIR__ . '/menu.php'; ?>
<main class="contenedor">
<section class="encabezado-pagina"><p class="ceja">Administración</p><h1>Actividad y seguimientos</h1><p>Resumen privado de participantes, fechas agendadas y métricas del sitio.</p></section>
<section class="kpis">
<article class="kpi"><strong><?= $totalUsuarios ?></strong><span>Usuarios registrados</span></article>
<article class="kpi"><strong><?= $totalActivos ?></strong><span>Apoyos activos</span></article>
<article class="kpi"><strong><?= $hoy ?></strong><span>Apoyos para hoy</span></article>
<article class="kpi"><strong><?= (int) ($eventos['visitantes'] ?? 0) ?></strong><span>Visitantes únicos</span></article>
<article class="kpi"><strong><?= (int) ($eventos['sesiones'] ?? 0) ?></strong><span>Sesiones</span></article>
<article class="kpi"><strong><?= (int) ($eventos['vistas'] ?? 0) ?></strong><span>Páginas vistas</span></article>
<article class="kpi"><strong><?= (int) ($eventos['clics'] ?? 0) ?></strong><span>Clics</span></article>
</section>
<section class="panel tabla-panel" style="margin-top:26px"><h2>Personas inscritas y fechas para ayudar</h2><div class="tabla"><table><thead><tr><th>Participante</th><th>Correo</th><th>Forma de ayudar</th><th>Fecha</th><th>Estado</th></tr></thead><tbody>
<?php foreach ($inscripciones as $registro): ?><tr><td><?= e($registro['nombre']) ?></td><td><?= e($registro['correo']) ?></td><td><?= e($registro['actividad']) ?></td><td><?= e(date('d/m/Y', strtotime($registro['fecha']))) ?></td><td><?= e($registro['estado']) ?></td></tr><?php endforeach; ?>
<?php if (!$inscripciones): ?><tr><td colspan="5">Todavía no hay inscripciones.</td></tr><?php endif; ?>
</tbody></table></div></section>
</main></body></html>
