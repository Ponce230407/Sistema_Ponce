<?php
require_once __DIR__ . '/auth.php';
require_admin($pdo);

$hoy = $totalActivos = $totalUsuarios = $visitantes = 0;
$inscripciones = [];
$schemaWarning = '';
try {
    $hoy = (int) $pdo->query("SELECT COUNT(*) FROM apoyos WHERE estado = 'activo' AND fecha = CURDATE()")->fetchColumn();
    $totalActivos = (int) $pdo->query("SELECT COUNT(*) FROM apoyos WHERE estado = 'activo'")->fetchColumn();
    $inscripciones = $pdo->query('SELECT a.actividad, a.fecha, a.estado, u.nombre, u.correo FROM apoyos a JOIN usuarios u ON u.id = a.usuario_id ORDER BY a.fecha DESC, a.id DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $error) {
    error_log('No se pudo consultar apoyos: ' . $error->getMessage());
    $schemaWarning = 'Falta crear la tabla apoyos. Importa Seguir/apoyos.sql en la base de datos configurada para el sitio.';
}
$totalUsuarios = (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
try {
    $visitantes = (int) $pdo->query('SELECT COUNT(DISTINCT visitante_id) FROM eventos')->fetchColumn();
} catch (PDOException $error) {
    error_log('No se pudo consultar eventos: ' . $error->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Administración | Patitas con Hogar</title><link rel="stylesheet" href="estilos.css"></head>
<body class="pagina-app">
<?php require __DIR__ . '/menu.php'; ?>
<main class="contenedor">
  <section class="encabezado-pagina"><p class="ceja">Administración</p><h1>Actividad y seguimientos</h1><p>Resumen privado de participantes, fechas agendadas y métricas del sitio.</p></section>
  <?php if ($schemaWarning): ?><p class="aviso" role="alert"><?= e($schemaWarning) ?></p><?php endif; ?>
  <section class="kpis">
    <article class="kpi"><strong><?= $totalUsuarios ?></strong><span>Usuarios registrados</span></article>
    <article class="kpi"><strong><?= $totalActivos ?></strong><span>Apoyos activos</span></article>
    <article class="kpi"><strong><?= $hoy ?></strong><span>Apoyos para hoy</span></article>
    <article class="kpi"><strong><?= $visitantes ?></strong><span>Visitantes únicos</span></article>
  </section>
  <section class="panel tabla-panel"><h2>Inscripciones y fechas</h2><div class="tabla"><table><thead><tr><th>Participante</th><th>Correo</th><th>Forma de ayudar</th><th>Fecha</th><th>Estado</th></tr></thead><tbody>
  <?php foreach ($inscripciones as $registro): ?><tr><td><?= e($registro['nombre']) ?></td><td><?= e($registro['correo']) ?></td><td><?= e($registro['actividad']) ?></td><td><?= e(date('d/m/Y', strtotime($registro['fecha']))) ?></td><td><?= e($registro['estado']) ?></td></tr><?php endforeach; ?>
  <?php if (!$inscripciones): ?><tr><td colspan="5"><?= $schemaWarning ? 'La tabla de inscripciones todavía no está configurada.' : 'Todavía no hay inscripciones.' ?></td></tr><?php endif; ?>
  </tbody></table></div></section>
</main></body></html>
