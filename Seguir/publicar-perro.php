<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../LOGIN/conexion.php';

// La publicación de experiencias felices es pública; no requiere iniciar sesión.
$pdo->exec("CREATE TABLE IF NOT EXISTS experiencias_perritos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    historia VARCHAR(1000) NOT NULL,
    foto VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_experiencias_creado (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$uploadDir = dirname(__DIR__) . '/Foto/public/uploads/experiencias';
$uploadUrl = '/Foto/public/uploads/experiencias';
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $historia = trim((string) ($_POST['historia'] ?? ''));
    $foto = $_FILES['foto'] ?? null;
    if ($nombre === '' || mb_strlen($nombre) > 80 || $historia === '' || mb_strlen($historia) > 1000) {
        $error = 'Escribe el nombre de tu perrito y cuéntanos una experiencia feliz (máximo 1000 caracteres).';
    } elseif (!$foto || !isset($foto['error']) || $foto['error'] !== UPLOAD_ERR_OK || $foto['size'] > 5 * 1024 * 1024) {
        $error = 'Selecciona una foto JPG, PNG o WebP de máximo 5 MB.';
    } else {
        $imageInfo = @getimagesize($foto['tmp_name']);
        $mime = $imageInfo['mime'] ?? '';
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mime])) {
            $error = 'El archivo debe ser una imagen JPG, PNG o WebP válida.';
        } elseif (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            $error = 'No se pudo preparar el espacio para fotos. Inténtalo más tarde.';
        } else {
            $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
            if (!move_uploaded_file($foto['tmp_name'], $uploadDir . '/' . $filename)) {
                $error = 'No se pudo guardar la foto. Inténtalo de nuevo.';
            } else {
                try {
                    $statement = $pdo->prepare('INSERT INTO experiencias_perritos (nombre, historia, foto) VALUES (?, ?, ?)');
                    $statement->execute([$nombre, $historia, $uploadUrl . '/' . $filename]);
                    $message = '¡Gracias! Tu experiencia ya está compartida con la comunidad.';
                } catch (Throwable $dbError) {
                    @unlink($uploadDir . '/' . $filename);
                    error_log('No se pudo guardar la experiencia: ' . $dbError->getMessage());
                    $error = 'No se pudo guardar tu publicación. Inténtalo de nuevo más tarde.';
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="description" content="Comparte fotos y momentos felices con tu perrito."><title>Comparte tu experiencia | Patitas con Hogar</title><style>
:root{--green:#365b45;--ink:#28352e;--muted:#66736a;--cream:#fff8ef;--line:#e9e3d9;--orange:#ef9c55}*{box-sizing:border-box}body{margin:0;background:var(--cream);color:var(--ink);font:16px/1.6 system-ui,sans-serif}.experience-wrap{max-width:900px;margin:2rem auto;padding:1rem}.experience-card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:clamp(20px,4vw,36px);box-shadow:0 8px 26px #28352e0a}.experience-card h1{color:var(--green)}.experience-form{display:grid;gap:18px;margin-top:24px}.experience-form label{display:grid;gap:8px;font-weight:700}.experience-form input,.experience-form textarea{width:100%;padding:13px;border:1px solid var(--line);border-radius:12px;font:inherit}.experience-form button,.back-link{display:inline-block;background:var(--green);color:white;border:0;border-radius:999px;padding:12px 20px;font:inherit;font-weight:750;text-decoration:none;cursor:pointer}.notice,.error{padding:12px 16px;border-radius:10px;background:#e8f4e8}.error{background:#fff0eb;color:#8b2e18}.helper{color:var(--muted)}
</style></head><body><?php require __DIR__ . '/menu.php'; ?><main class="experience-wrap"><section class="experience-card"><p><a href="/Foto/public/index.html">← Volver al inicio</a></p><h1>Comparte la experiencia de tu perrito 🐾</h1><p>Sube una foto y cuéntanos un momento feliz o un recuerdo especial. Esta sección es para celebrar a nuestros perritos; no es para publicar animales en adopción ni solicitar rescates. Cualquier persona puede compartir una experiencia, sin crear una cuenta.</p><?php if ($message): ?><p class="notice" role="status"><?= e($message) ?></p><?php endif; ?><?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?><form class="experience-form" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Nombre de tu perrito<input name="nombre" maxlength="80" required value="<?= e($_POST['nombre'] ?? '') ?>"></label><label>Cuéntanos el momento feliz<textarea name="historia" maxlength="1000" rows="5" required><?= e($_POST['historia'] ?? '') ?></textarea></label><label>Foto (JPG, PNG o WebP; máximo 5 MB)<input type="file" name="foto" accept="image/jpeg,image/png,image/webp" required></label><p class="helper">No incluyas teléfonos, direcciones ni información privada.</p><button type="submit">Compartir experiencia 🐾</button></form></section></main><script src="/Seguir/tracker.js"></script></body></html>
