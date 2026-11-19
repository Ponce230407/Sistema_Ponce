<?php
require_once __DIR__ . '/auth.php';
require_login();
$statement = $pdo->prepare('SELECT id, actividad, fecha, estado, creado_en FROM apoyos WHERE usuario_id = ? ORDER BY fecha DESC, id DESC');
$statement->execute([$_SESSION['usuario_id']]);
$apoyos = $statement->fetchAll(PDO::FETCH_ASSOC);
$mensaje = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Mis apoyos | Patitas con Hogar</title><link rel="stylesheet" href="estilos.css"></head><body class="pagina-app">
<?php require __DIR__ . '/menu.php'; ?>
<main class="contenedor"><section class="encabezado-pagina"><p class="ceja">Tu participación</p><h1>Mis apoyos agendados</h1><p>Aquí puedes consultar qué actividades elegiste y en qué fecha ayudarás.</p></section>
<?php if ($mensaje): ?><p class="exito"><?= e($mensaje) ?></p><?php endif; ?>
<?php if (!$apoyos): ?><section class="panel"><h2>Aún no tienes apoyos agendados</h2><p>Explora las formas de ayudar y elige una fecha para participar.</p><a class="boton" href="cursos.php">Ver formas de ayudar</a></section><?php else: ?><div class="lista-apoyos"><?php foreach ($apoyos as $apoyo): ?><article class="panel fila-apoyo"><div><span class="estado estado-<?= e($apoyo['estado']) ?>"><?= $apoyo['estado'] === 'activo' ? 'Agendado' : 'Cancelado' ?></span><h2><?= e($apoyo['actividad']) ?></h2><p>Fecha: <strong><?= e(date('d/m/Y', strtotime($apoyo['fecha']))) ?></strong></p></div><?php if ($apoyo['estado'] === 'activo'): ?><form method="post" action="inscribirse.php" onsubmit="return confirm('¿Quieres cancelar este apoyo?');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="accion" value="cancelar"><input type="hidden" name="apoyo_id" value="<?= (int) $apoyo['id'] ?>"><button class="boton-secundario" type="submit">Cancelar ayuda</button></form><?php endif; ?></article><?php endforeach; ?></div><?php endif; ?>
</main></body></html>
