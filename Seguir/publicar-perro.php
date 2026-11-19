<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../LOGIN/conexion.php';
require_login();
$pdo->exec("CREATE TABLE IF NOT EXISTS experiencias_perritos (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, usuario_id INT NULL, nombre VARCHAR(80) NOT NULL, historia VARCHAR(1000) NOT NULL, foto VARCHAR(255) NOT NULL, foto_datos MEDIUMBLOB NULL, foto_mime VARCHAR(32) NULL, creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX idx_experiencias_creado (creado_en), INDEX idx_experiencias_usuario (usuario_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
foreach (['usuario_id' => 'INT NULL AFTER id', 'foto_datos' => 'MEDIUMBLOB NULL', 'foto_mime' => 'VARCHAR(32) NULL'] as $column => $definition) {
    $check = $pdo->prepare('SHOW COLUMNS FROM experiencias_perritos LIKE ?');
    $check->execute([$column]);
    if (!$check->fetch()) $pdo->exec("ALTER TABLE experiencias_perritos ADD COLUMN {$column} {$definition}");
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['nombre'] ?? ''));
    $story = trim((string) ($_POST['historia'] ?? ''));
    $photo = $_FILES['foto'] ?? null;
    if ($name === '' || mb_strlen($name) > 80 || $story === '' || mb_strlen($story) > 1000) {
        $error = 'Escribe el nombre de tu mascota y cuéntanos un momento feliz (máximo 1000 caracteres).';
    } elseif (!$photo || ($photo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($photo['size'] ?? 0) > 5 * 1024 * 1024) {
        $error = 'Selecciona o toma una foto JPG, PNG o WebP de máximo 5 MB.';
    } else {
        $image = @getimagesize($photo['tmp_name']);
        $mime = $image['mime'] ?? '';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $error = 'El archivo debe ser una imagen JPG, PNG o WebP válida.';
        } else {
            $bytes = file_get_contents($photo['tmp_name']);
            if ($bytes === false) {
                $error = 'No se pudo leer la foto. Inténtalo de nuevo.';
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO experiencias_perritos (usuario_id,nombre,historia,foto,foto_datos,foto_mime) VALUES (?,?,?,'',?,?)");
                    $stmt->bindValue(1, (int) $_SESSION['usuario_id'], PDO::PARAM_INT);
                    $stmt->bindValue(2, $name);
                    $stmt->bindValue(3, $story);
                    $stmt->bindValue(4, $bytes, PDO::PARAM_LOB);
                    $stmt->bindValue(5, $mime);
                    $stmt->execute();
                    $id = (int) $pdo->lastInsertId();
                    $pdo->prepare('UPDATE experiencias_perritos SET foto=? WHERE id=?')->execute(['/Foto/public/experiencias.php?imagen=' . $id, $id]);
                    header('Location: /Seguir/publicar-perro.php?publicada=1', true, 303);
                    exit;
                } catch (Throwable $exception) {
                    error_log('No se pudo guardar la experiencia: ' . $exception->getMessage());
                    $error = 'No se pudo guardar tu publicación. Inténtalo de nuevo más tarde.';
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Comparte un momento feliz | Patitas con Hogar</title>
  <style>
    :root{--green:#365b45;--ink:#28352e;--muted:#66736a;--cream:#fff8ef;--line:#e9e3d9}
    *{box-sizing:border-box}body{margin:0;background:var(--cream);color:var(--ink);font:16px/1.6 system-ui,sans-serif}
    .wrap{width:min(760px,calc(100% - 32px));margin:28px auto}.card{background:#fff;border:1px solid var(--line);border-radius:20px;padding:clamp(20px,4vw,36px);box-shadow:0 12px 32px #26382c12}
    .back{color:var(--green);font-weight:700}h1{font-size:clamp(1.8rem,4vw,2.7rem);line-height:1.15}.field{display:block;margin:18px 0;font-weight:700}.field input,.field textarea{display:block;width:100%;margin-top:7px;padding:12px;border:1px solid #c9d2ca;border-radius:10px;font:inherit}.field textarea{min-height:130px;resize:vertical}.field small,.hint{display:block;color:var(--muted);font-weight:400;font-size:.9rem}
    .actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:16px}.button{border:0;border-radius:999px;background:var(--green);color:white;padding:12px 20px;font:700 1rem system-ui;cursor:pointer}.button.secondary{background:#e8efe7;color:var(--green)}.button:disabled{opacity:.55;cursor:wait}
    .notice,.error,.camera-status{padding:12px 16px;border-radius:10px;margin:14px 0}.notice{background:#e6f4e8}.error{background:#fff0ed;color:#922d21}.camera-status{background:#f4eee4}.camera-panel{display:none;margin-top:16px}.camera-panel.active{display:block}.camera-panel video{width:100%;max-height:420px;object-fit:cover;background:#17231b;border-radius:14px}.preview{display:none;max-width:100%;max-height:300px;border-radius:12px;margin:14px 0}.preview.visible{display:block}
  </style>
</head>
<body>
<?php require __DIR__ . '/menu.php'; ?>
<main class="wrap"><section class="card">
  <a class="back" href="/Foto/public/index.html#comunidad">← Volver a la comunidad</a>
  <h1>Comparte un momento feliz 🐾</h1>
  <p>Cuéntanos una historia bonita y agrega una foto de tu mascota. Al activar la cámara, el navegador te pedirá permiso antes de usarla.</p>
  <?php if (isset($_GET['publicada'])): ?><p class="notice">¡Listo! Tu momento feliz ya está compartido con la comunidad.</p><?php endif; ?>
  <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
  <form id="post-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label class="field">Nombre de tu mascota<input name="nombre" maxlength="80" required value="<?= e($_POST['nombre'] ?? '') ?>"></label>
    <label class="field">Tu historia<textarea name="historia" maxlength="1000" required><?= e($_POST['historia'] ?? '') ?></textarea></label>
    <label class="field" for="foto">Foto de tu mascota<input id="foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp" required><small>Elige una imagen o toma una foto con la cámara. JPG, PNG o WebP, máximo 5 MB.</small></label>
    <img id="preview" class="preview" alt="Vista previa de la foto seleccionada">
    <div id="camera-panel" class="camera-panel"><video id="camera-video" autoplay playsinline muted></video><p id="camera-status" class="camera-status" role="status">Solicitando permiso para usar la cámara…</p><div class="actions"><button class="button" type="button" id="take-photo" disabled>Tomar foto</button><button class="button secondary" type="button" id="stop-camera">Cerrar cámara</button></div></div>
    <div class="actions"><button class="button secondary" type="button" id="start-camera">📷 Activar cámara</button><button class="button" type="submit" id="submit-post">Compartir momento</button></div>
  </form>
</section></main>
<script>
(() => {
  const input = document.getElementById('foto');
  const preview = document.getElementById('preview');
  const panel = document.getElementById('camera-panel');
  const video = document.getElementById('camera-video');
  const status = document.getElementById('camera-status');
  const take = document.getElementById('take-photo');
  const start = document.getElementById('start-camera');
  let stream = null;
  let previewUrl = null;
  function stopCamera() {
    if (stream) stream.getTracks().forEach(track => track.stop());
    stream = null;
    video.srcObject = null;
    take.disabled = true;
    panel.classList.remove('active');
    start.disabled = false;
  }
  start.addEventListener('click', async () => {
    if (!navigator.mediaDevices?.getUserMedia) {
      status.textContent = 'Este navegador no permite acceso directo a la cámara. Usa el selector de fotos; en móviles puede ofrecer tomar una foto.';
      panel.classList.add('active');
      return;
    }
    start.disabled = true;
    panel.classList.add('active');
    status.textContent = 'Acepta el permiso de cámara que muestra el navegador.';
    try {
      stream = await navigator.mediaDevices.getUserMedia({video: {facingMode: {ideal: 'environment'}}, audio: false});
      video.srcObject = stream;
      await video.play();
      status.textContent = 'Cámara activa. Encuadra a tu mascota y toma la foto.';
      take.disabled = false;
    } catch (error) {
      status.textContent = error.name === 'NotAllowedError'
        ? 'No se concedió el permiso. Permite la cámara en los ajustes del navegador e inténtalo otra vez.'
        : 'No se pudo abrir la cámara. Comprueba que el dispositivo tenga una cámara disponible.';
      start.disabled = false;
    }
  });
  document.getElementById('stop-camera').addEventListener('click', stopCamera);
  take.addEventListener('click', () => {
    if (!video.videoWidth || !video.videoHeight) return;
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    canvas.toBlob(blob => {
      if (!blob) { status.textContent = 'No se pudo crear la foto. Inténtalo de nuevo.'; return; }
      const file = new File([blob], 'foto-mascota.jpg', {type: 'image/jpeg', lastModified: Date.now()});
      const transfer = new DataTransfer();
      transfer.items.add(file);
      input.files = transfer.files;
      input.dispatchEvent(new Event('change', {bubbles: true}));
      status.textContent = 'Foto lista para compartir.';
      stopCamera();
    }, 'image/jpeg', .88);
  });
  input.addEventListener('change', () => {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    if (input.files?.[0]) {
      previewUrl = URL.createObjectURL(input.files[0]);
      preview.src = previewUrl;
      preview.classList.add('visible');
    } else preview.classList.remove('visible');
  });
  document.getElementById('post-form').addEventListener('submit', stopCamera);
  window.addEventListener('pagehide', stopCamera);
})();
</script>
<script src="/Seguir/tracker.js" defer></script>
</body></html>
