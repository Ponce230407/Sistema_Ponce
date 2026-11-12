<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/perros-db.php';

$userId = (int) $_SESSION['usuario_id'];
$uploadDir = __DIR__ . '/../public/uploads/perros';
$uploadUrl = '/Foto/public/uploads/perros';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? 'publish');
    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            $error = 'No se encontró esa publicación.';
        } else {
            $stmt = $pdo->prepare('SELECT foto FROM perros_publicados WHERE id = ? AND usuario_id = ?');
            $stmt->execute([$id, $userId]);
            $foto = $stmt->fetchColumn();
            if ($foto !== false) {
                $pdo->prepare('DELETE FROM perros_publicados WHERE id = ? AND usuario_id = ?')->execute([$id, $userId]);
                $file = __DIR__ . '/../public' . $foto;
                if (is_file($file)) { unlink($file); }
                $message = 'La publicación fue eliminada.';
            }
        }
    } else {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $edad = trim((string) ($_POST['edad'] ?? ''));
        $tamano = (string) ($_POST['tamano'] ?? '');
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $file = $_FILES['foto'] ?? null;
        if ($nombre === '' || mb_strlen($nombre) > 80 || $edad === '' || mb_strlen($edad) > 40
            || !in_array($tamano, ['Pequeño', 'Mediano', 'Grande'], true)
            || $descripcion === '' || mb_strlen($descripcion) > 1000) {
            $error = 'Completa los datos. El nombre admite hasta 80 caracteres y la descripción hasta 1000.';
        } elseif (!$file || $file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) {
            $error = 'Sube una foto JPG, PNG o WebP de máximo 5 MB.';
        } else {
            $imageInfo = @getimagesize($file['tmp_name']);
            $mime = $imageInfo['mime'] ?? '';
            $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($extensions[$mime])) {
                $error = 'El archivo debe ser una imagen JPG, PNG o WebP válida.';
            } elseif (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                $error = 'No se pudo preparar el espacio para fotos. Inténtalo más tarde.';
            } else {
                $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
                if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
                    $error = 'No se pudo guardar la foto. Inténtalo de nuevo.';
                } else {
                    $photoPath = $uploadUrl . '/' . $filename;
                    try {
                        $stmt = $pdo->prepare('INSERT INTO perros_publicados (usuario_id, nombre, edad, tamano, descripcion, foto) VALUES (?, ?, ?, ?, ?, ?)');
                        $stmt->execute([$userId, $nombre, $edad, $tamano, $descripcion, $photoPath]);
                        $message = '¡Tu perrito ya aparece en la lista pública!';
                    } catch (Throwable $dbError) {
                        @unlink($uploadDir . '/' . $filename);
                        throw $dbError;
                    }
                }
            }
        }
    }
}

$mine = $pdo->prepare('SELECT id, nombre, foto, creado_en FROM perros_publicados WHERE usuario_id = ? ORDER BY creado_en DESC');
$mine->execute([$userId]);
$myDogs = $mine->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Publicar un perrito | Patitas con Hogar</title><link rel="stylesheet" href="estilos.css"><style>
body{font-family:system-ui,sans-serif;background:#fff8ef;color:#28352e}.perros-wrap{max-width:900px;margin:2rem auto;padding:1rem}.form-card,.mis-card{background:white;border:1px solid #e9e3d9;border-radius:18px;padding:1.5rem;margin:1rem 0}.form-card label{display:block;font-weight:650;margin:.9rem 0}.form-card input,.form-card textarea,.form-card select{display:block;width:100%;padding:.75rem;border:1px solid #cbd4cc;border-radius:9px;margin-top:.35rem}.form-card button,.mis-card button{background:#365b45;color:white;padding:.7rem 1rem;border:0;border-radius:999px;font-weight:700;cursor:pointer}.alert{padding:.8rem;border-radius:10px;background:#e7f5e9}.alert.error{background:#ffebee}.mi-publicacion{display:flex;align-items:center;gap:1rem;margin:.75rem 0}.mi-publicacion img{width:80px;height:70px;object-fit:cover;border-radius:10px}
</style></head><body><?php require __DIR__ . '/menu.php'; ?><main class="perros-wrap"><p><a href="/Foto/public/index.html">← Volver al inicio</a></p><h1>Publica un perrito 🐾</h1><p>Comparte una foto y su historia para que más personas puedan conocerlo. No publiques teléfonos, domicilios ni información privada en la descripción.</p>
<?php if ($message): ?><p class="alert"><?= e($message) ?></p><?php endif; ?><?php if ($error): ?><p class="alert error"><?= e($error) ?></p><?php endif; ?>
<form class="form-card" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="publish"><label>Nombre del perrito<input name="nombre" maxlength="80" required value="<?= e($_POST['nombre'] ?? '') ?>"></label><label>Edad aproximada<input name="edad" maxlength="40" required placeholder="Ej. 2 años"></label><label>Tamaño<select name="tamano" required><option value="">Selecciona</option><?php foreach (['Pequeño','Mediano','Grande'] as $size): ?><option value="<?= e($size) ?>" <?= (($_POST['tamano'] ?? '') === $size) ? 'selected' : '' ?>><?= e($size) ?></option><?php endforeach; ?></select></label><label>Cuéntanos sobre él o ella<textarea name="descripcion" maxlength="1000" rows="5" required><?= e($_POST['descripcion'] ?? '') ?></textarea></label><label>Foto (JPG, PNG o WebP; máximo 5 MB)<input type="file" name="foto" accept="image/jpeg,image/png,image/webp" required></label><button type="submit">Publicar perrito</button></form>
<section class="mis-card"><h2>Mis publicaciones</h2><?php if (!$myDogs): ?><p>Aún no has publicado perritos.</p><?php endif; ?><?php foreach ($myDogs as $dog): ?><div class="mi-publicacion"><img src="<?= e($dog['foto']) ?>" alt=""><strong><?= e($dog['nombre']) ?></strong><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $dog['id'] ?>"><button type="submit">Eliminar</button></form></div><?php endforeach; ?></section></main><script src="/Seguir/tracker.js"></script></body></html>
