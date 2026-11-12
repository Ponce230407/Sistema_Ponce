<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../LOGIN/conexion.php';
require_login();

$pdo->exec("CREATE TABLE IF NOT EXISTS experiencias_perritos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    nombre VARCHAR(80) NOT NULL,
    historia VARCHAR(1000) NOT NULL,
    foto VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_experiencias_creado (creado_en),
    INDEX idx_experiencias_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
if (!$pdo->query("SHOW COLUMNS FROM experiencias_perritos LIKE 'usuario_id'")->fetch()) {
    $pdo->exec('ALTER TABLE experiencias_perritos ADD usuario_id INT NULL AFTER id');
    $pdo->exec('ALTER TABLE experiencias_perritos ADD INDEX idx_experiencias_usuario (usuario_id)');
}

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
        $error = 'Escribe el nombre de tu mascota y cuéntanos un momento feliz (máximo 1000 caracteres).';
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
                    $statement = $pdo->prepare('INSERT INTO experiencias_perritos (usuario_id, nombre, historia, foto) VALUES (?, ?, ?, ?)');
                    $statement->execute([(int) $_SESSION['usuario_id'], $nombre, $historia, $uploadUrl . '/' . $filename]);
                    header('Location: /Seguir/publicar-perro.php?publicada=1', true, 303);
                    exit;
                } catch (Throwable $dbError) {
                    @unlink($uploadDir . '/' . $filename);
                    error_log('No se pudo guardar la experiencia: ' . $dbError->getMessage());
                    $error = 'No se pudo guardar tu publicación. Inténtalo de nuevo más tarde.';
                }
            }
        }
    }
}
if (isset($_GET['publicada'])) $message = '¡Listo! Tu momento feliz ya está compartido con la comunidad.';
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="description" content="Comparte fotos y momentos felices con tu mascota."><title>Comparte un momento feliz | Patitas con Hogar</title>
<style>
:root{--green:#365b45;--ink:#28352e;--muted:#66736a;--cream:#fff8ef;--line:#e9e3d9;--orange:#ef9c55}*{box-sizing:border-box}body{margin:0;background:var(--cream);color:var(--ink);font:16px/1.6 system-ui,-apple-system,"Segoe UI",sans-serif}.experience-wrap{width:min(760px,100%);margin:0 auto;padding:24px 18px 56px}.experience-card{padding:clamp(20px,5vw,36px);background:#fff;border:1px solid var(--line);border-radius:24px;box-shadow:0 16px 36px rgba(40,53,46,.08)}h1{margin:.2em 0;color:var(--green);font-size:clamp(2rem,7vw,3rem);line-height:1.1}.experience-card p{color:var(--muted)}.experience-form{display:grid;gap:16px;margin-top:24px}.experience-form label{display:grid;gap:7px;font-weight:700}.experience-form input,.experience-form textarea{width:100%;padding:12px 14px;border:1px solid #d6d0c7;border-radius:12px;font:inherit}.experience-form textarea{min-height:110px;resize:vertical}.experience-form button{justify-self:start;padding:13px 20px;border:0;border-radius:12px;background:var(--green);color:white;font:inherit;font-weight:800;cursor:pointer}.notice,.error{padding:12px 14px;border-radius:12px}.notice{background:#edf9ee;color:#23543a}.error{background:#fff1f1;color:#7d2d2a}.back{color:var(--green);font-weight:700}
</style></head><body><?php require __DIR__ . '/menu.php'; ?><main class="experience-wrap"><section class="experience-card"><p><a class="back" href="/Foto/public/index.html">← Volver a la comunidad</a></p><h1>Comparte un momento feliz 🐾</h1><p>Sube una foto y cuéntanos un recuerdo bonito con tu mascota. La comunidad podrá verlo y dejarle un corazón. Esta sección es para celebrar a nuestras mascotas, no para publicar animales en adopción.</p><?php if ($message): ?><p class="notice" role="status"><?= e($message) ?></p><?php endif; ?><?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?><form class="experience-form" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Nombre de tu mascota<input name="nombre" maxlength="80" required></label><label>Cuéntanos el momento feliz<textarea name="historia" maxlength="1000" required></textarea></label><label>Foto (JPG, PNG o WebP, máximo 5 MB)<input type="file" name="foto" accept="image/jpeg,image/png,image/webp" required></label><button type="submit">Compartir mi momento</button></form></section></main></body></html>
