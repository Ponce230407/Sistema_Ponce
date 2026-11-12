<?php
require_once __DIR__ . '/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    verify_csrf();
    $nombre = trim((string) ($_POST['nombre_perrito'] ?? ''));
    $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
    $file = $_FILES['foto'] ?? null;

    if (mb_strlen($nombre) < 1 || mb_strlen($nombre) > 80) {
        $error = 'Escribe un nombre de hasta 80 caracteres.';
    } elseif (mb_strlen($descripcion) > 500) {
        $error = 'La descripción no puede superar 500 caracteres.';
    } elseif (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Selecciona una imagen válida (máximo 3 MB).';
    } elseif ($file['size'] < 1 || $file['size'] > 3 * 1024 * 1024) {
        $error = 'La imagen debe pesar 3 MB o menos.';
    } else {
        $tmp = $file['tmp_name'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $dimensions = @getimagesize($tmp);
        if (!in_array($mime, $allowed, true) || !$dimensions || $dimensions[0] < 1 || $dimensions[1] < 1 || $dimensions[0] > 5000 || $dimensions[1] > 5000 || $dimensions['mime'] !== $mime) {
            $error = 'Usa una imagen JPG, PNG o WebP válida, de máximo 5000 × 5000 píxeles.';
        } else {
            try {
                $bytes = file_get_contents($tmp);
                $stmt = $pdo->prepare("INSERT INTO fotos_perritos (usuario_id, nombre_perrito, descripcion, tipo_mime, imagen, estado) VALUES (?, ?, ?, ?, ?, 'pendiente')");
                $stmt->bindValue(1, (int) $_SESSION['usuario_id'], PDO::PARAM_INT);
                $stmt->bindValue(2, $nombre, PDO::PARAM_STR);
                $stmt->bindValue(3, $descripcion, PDO::PARAM_STR);
                $stmt->bindValue(4, $mime, PDO::PARAM_STR);
                $stmt->bindValue(5, $bytes, PDO::PARAM_LOB);
                $stmt->execute();
                $_SESSION['flash_message'] = '¡Gracias! Tu foto se guardó y aparecerá en la galería después de ser revisada.';
                header('Location: perritos.php', true, 303);
                exit;
            } catch (PDOException $exception) {
                error_log('No se pudo guardar la foto del perrito: ' . $exception->getMessage());
                $error = 'No se pudo guardar la foto. Inténtalo más tarde.';
            }
        }
    }
}

$mensaje = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);
try {
    $fotos = $pdo->query("SELECT id, nombre_perrito, descripcion, creado_en FROM fotos_perritos WHERE estado = 'publicado' ORDER BY creado_en DESC LIMIT 120")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    error_log('No se pudo cargar galería de fotos: ' . $exception->getMessage());
    $fotos = [];
    $schemaError = true;
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#fff8ef"><title>Galería de perritos | Patitas con Hogar><link rel="stylesheet" href="estilos.css"></head><body class="pagina-app">
<?php require __DIR__ . '/menu.php'; ?>
<main class="contenedor"><section class="encabezado-pagina"><p class="ceja">Nuestra comunidad</p><h1>Galería de perritos</h1><p>Comparte una foto de tu perrito o mira las historias que la comunidad ha enviado.</p></section>
<?php if ($mensaje): ?><p class="exito" role="status"><?= e($mensaje) ?></p><?php endif; ?>
<?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
<?php if (!empty($schemaError)): ?><p class="aviso" role="alert">La galería necesita su tabla. Importa <code>Seguir/perritos.sql</code> en la base configurada para el sitio.</p><?php endif; ?>
<section class="panel upload-panel"><h2>Sube la foto de tu perrito</h2><?php if (!empty($_SESSION['usuario_id']) || (($_SESSION['rol'] ?? '') === 'admin')): ?><form method="post" enctype="multipart/form-data" class="form-inscripcion"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Nombre del perrito<input type="text" name="nombre_perrito" maxlength="80" required value="<?= e($_POST['nombre_perrito'] ?? '') ?>"></label><label>Cuéntanos algo sobre él (opcional)<textarea name="descripcion" rows="3" maxlength="500"><?= e($_POST['descripcion'] ?? '') ?></textarea></label><label>Foto (JPG, PNG o WebP, máximo 3 MB)<input type="file" name="foto" accept="image/jpeg,image/png,image/webp" required></label><button type="submit">Enviar foto para revisión</button></form><p class="small-note">Por seguridad, las fotos se muestran públicamente después de la aprobación del equipo.</p><?php else: ?><p>Inicia sesión para compartir una foto. No necesitas iniciar sesión para ver la galería.</p><a class="boton" href="/LOGIN/login.php">Iniciar sesión para subir</a><?php endif; ?></section>
<section class="gallery-section"><h2>Fotos compartidas</h2><?php if (!$fotos): ?><div class="panel"><p>Aún no hay fotos publicadas. ¡Puedes ser la primera persona en compartir una!</p></div><?php else: ?><div class="photo-grid"><?php foreach ($fotos as $foto): ?><article class="photo-card"><img loading="lazy" src="foto.php?id=<?= (int) $foto['id'] ?>" alt="Foto de <?= e($foto['nombre_perrito']) ?>"><div class="photo-card-body"><h3><?= e($foto['nombre_perrito']) ?></h3><?php if ($foto['descripcion']): ?><p><?= nl2br(e($foto['descripcion'])) ?></p><?php endif; ?><small>Compartida el <?= e(date('d/m/Y', strtotime($foto['creado_en']))) ?></small></div></article><?php endforeach; ?></div><?php endif; ?></section>
</main><script src="tracker.js"></script></body></html>
