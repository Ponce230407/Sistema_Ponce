<?php
require_once __DIR__ . '/auth.php';

// Admin review actions are POST-only and protected against CSRF.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin($pdo);
    verify_csrf();
    $fotoId = filter_input(INPUT_POST, 'foto_id', FILTER_VALIDATE_INT);
    $estado = $_POST['estado'] ?? '';
    if (!$fotoId || !in_array($estado, ['publicado', 'rechazado'], true)) {
        http_response_code(400);
        exit('Solicitud de moderación no válida.');
    }
    $stmt = $pdo->prepare("UPDATE fotos_perritos SET estado = ? WHERE id = ? AND estado = 'pendiente'");
    $stmt->execute([$estado, $fotoId]);
    $_SESSION['flash_message'] = $stmt->rowCount() ? 'La revisión de la foto se guardó.' : 'La foto ya había sido revisada.';
    header('Location: admin.php#fotos-pendientes');
    exit;
}

require_admin($pdo);
$hoy = $totalActivos = $totalUsuarios = $visitantes = 0;
$inscripciones = $fotosPendientes = [];
$schemaWarning = '';
try {
    $hoy = (int) $pdo->query("SELECT COUNT(*) FROM apoyos WHERE estado = 'activo' AND fecha = CURDATE()")->fetchColumn();
    $totalActivos = (int) $pdo->query("SELECT COUNT(*) FROM apoyos WHERE estado = 'activo'")->fetchColumn();
    $inscripciones = $pdo->query('SELECT a.actividad, a.fecha, a.estado, u.nombre, u.correo FROM apoyos a JOIN usuarios u ON u.id = a.usuario_id ORDER BY a.fecha DESC, a.id DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $error) {
    error_log('No se pudo consultar apoyos: ' . $error->getMessage());
    $schemaWarning = 'Falta crear la tabla apoyos. Importa Seguir/apoyos.sql en la base de datos configurada.';
}
$totalUsuarios = (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
try { $visitantes = (int) $pdo->query('SELECT COUNT(DISTINCT visitante_id) FROM eventos')->fetchColumn(); } catch (PDOException $ignored) {}
try {
    $fotosPendientes = $pdo->query("SELECT f.id, f.nombre_perrito, f.descripcion, f.creado_en, u.nombre AS usuario FROM fotos_perritos f JOIN usuarios u ON u.id = f.usuario_id WHERE f.estado = 'pendiente' ORDER BY f.creado_en ASC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $error) {
    error_log('No se pudo consultar fotos pendientes: ' . $error->getMessage());
}
$mensaje = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Administración | Patitas con Hogar</title><link rel="stylesheet" href="estilos.css"></head>
<body class="pagina-app">
<?php require __DIR__ . '/menu.php'; ?>
<main class="contenedor">
<section class="encabezado-pagina"><p class="ceja">Administración</p><h1>Actividad y seguimientos</h1><p>Resumen privado de participantes, fechas agendadas y métricas del sitio.</p></section>
<?php if ($mensaje): ?><p class="exito" role="status"><?= e($mensaje) ?></p><?php endif; ?>
<?php if ($schemaWarning): ?><p class="aviso" role="alert"><?= e($schemaWarning) ?></p><?php endif; ?>
<section class="kpis"><article class="kpi"><strong><?= $totalUsuarios ?></strong><span>Usuarios registrados</span></article><article class="kpi"><strong><?= $totalActivos ?></strong><span>Apoyos activos</span></article><article class="kpi"><strong><?= $hoy ?></strong><span>Apoyos para hoy</span></article><article class="kpi"><strong><?= $visitantes ?></strong><span>Visitantes únicos</span></article></section>
<section class="panel tabla-panel"><h2>Inscripciones y fechas</h2><div class="tabla"><table><thead><tr><th>Participante</th><th>Correo</th><th>Forma de ayudar</th><th>Fecha</th><th>Estado</th></tr></thead><tbody><?php foreach ($inscripciones as $registro): ?><tr><td><?= e($registro['nombre']) ?></td><td><?= e($registro['correo']) ?></td><td><?= e($registro['actividad']) ?></td><td><?= e(date('d/m/Y', strtotime($registro['fecha']))) ?></td><td><?= e($registro['estado']) ?></td></tr><?php endforeach; ?><?php if (!$inscripciones): ?><tr><td colspan="5"><?= $schemaWarning ? 'La tabla de inscripciones todavía no está configurada.' : 'Todavía no hay inscripciones.' ?></td></tr><?php endif; ?></tbody></table></div></section>
<section class="panel tabla-panel" id="fotos-pendientes"><h2>Fotos de perritos por revisar</h2><p>Aprueba las fotos adecuadas para mostrarlas en la galería pública.</p><?php if (!$fotosPendientes): ?><p>No hay fotos pendientes de revisión. Si la tabla no existe, importa <code>Seguir/perritos.sql</code>.</p><?php else: ?><div class="admin-photo-grid"><?php foreach ($fotosPendientes as $foto): ?><article class="photo-card"><img src="foto.php?id=<?= (int) $foto['id'] ?>&amp;revision=1" alt="Foto enviada de <?= e($foto['nombre_perrito']) ?>"><div class="photo-card-body"><h3><?= e($foto['nombre_perrito']) ?></h3><p><?= e($foto['descripcion'] ?: 'Sin descripción') ?></p><small>Enviada por <?= e($foto['usuario']) ?> · <?= e(date('d/m/Y', strtotime($foto['creado_en']))) ?></small><div class="photo-actions"><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="foto_id" value="<?= (int) $foto['id'] ?>"><input type="hidden" name="estado" value="publicado"><button type="submit">Aprobar</button></form><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="foto_id" value="<?= (int) $foto['id'] ?>"><input type="hidden" name="estado" value="rechazado"><button class="boton-secundario" type="submit">Rechazar</button></form></div></div></article><?php endforeach; ?></div><?php endif; ?></section>
</main></body></html>
