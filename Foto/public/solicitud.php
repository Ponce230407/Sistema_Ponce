<?php
require_once __DIR__ . '/../../Seguir/auth.php';

function solicitud_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$tipo = $_GET['tipo'] ?? 'adopcion';
$tipos = ['adopcion', 'voluntariado'];
if (!in_array($tipo, $tipos, true)) {
    $tipo = 'adopcion';
}
$perro = trim((string) ($_GET['perro'] ?? ''));
$error = '';
$enviada = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $correo = trim((string) ($_POST['correo'] ?? ''));
    $telefono = trim((string) ($_POST['telefono'] ?? ''));
    $tipo = (string) ($_POST['tipo'] ?? '');
    $perro = trim((string) ($_POST['perro'] ?? ''));
    $motivo = trim((string) ($_POST['motivo'] ?? ''));
    $hogar = trim((string) ($_POST['hogar'] ?? ''));
    $disponibilidad = trim((string) ($_POST['disponibilidad'] ?? ''));

    if (!in_array($tipo, $tipos, true)) {
        $error = 'Selecciona si tu solicitud es de adopción o voluntariado.';
    } elseif ($nombre === '' || mb_strlen($nombre) > 120 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Escribe tu nombre y un correo electrónico válido.';
    } elseif (mb_strlen($correo) > 190 || mb_strlen($telefono) > 40 || mb_strlen($perro) > 100 || mb_strlen($motivo) < 10 || mb_strlen($motivo) > 2000 || mb_strlen($hogar) > 1000 || mb_strlen($disponibilidad) > 500) {
        $error = 'Revisa tus respuestas. El motivo debe tener entre 10 y 2000 caracteres.';
    } elseif ($tipo === 'adopcion' && ($perro === '' || $hogar === '')) {
        $error = 'Para adopción indica qué perro te interesó y dónde viviría.';
    } else {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS solicitudes_adopcion_voluntariado (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(120) NOT NULL,
                correo VARCHAR(190) NOT NULL,
                telefono VARCHAR(40) NOT NULL DEFAULT '',
                tipo ENUM('adopcion','voluntariado') NOT NULL,
                perro VARCHAR(100) NOT NULL DEFAULT '',
                motivo TEXT NOT NULL,
                hogar TEXT NOT NULL,
                disponibilidad VARCHAR(500) NOT NULL DEFAULT '',
                estado ENUM('pendiente','revisada','contactada') NOT NULL DEFAULT 'pendiente',
                creada_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $statement = $pdo->prepare('INSERT INTO solicitudes_adopcion_voluntariado (nombre, correo, telefono, tipo, perro, motivo, hogar, disponibilidad) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $statement->execute([$nombre, $correo, $telefono, $tipo, $perro, $motivo, $hogar, $disponibilidad]);

            $etiquetaTipo = $tipo === 'adopcion' ? 'Adopción' : 'Voluntariado';
            $mensaje = "Hola, envié una solicitud de {$etiquetaTipo} desde Patitas con Hogar.\n\n";
            $mensaje .= "Nombre: {$nombre}\nCorreo: {$correo}\nTeléfono: " . ($telefono !== '' ? $telefono : 'No indicado') . "\n";
            if ($tipo === 'adopcion') {
                $mensaje .= "Perro que me interesó: {$perro}\nDónde viviría: {$hogar}\n";
            }
            $mensaje .= "Motivo: {$motivo}\nDisponibilidad: " . ($disponibilidad !== '' ? $disponibilidad : 'No indicada') . "\n\nPor favor, revisen mi solicitud y contáctenme.";

            // WhatsApp requiere el número en formato internacional: México (52) + los 10 dígitos.
            header('Location: https://wa.me/525537574716?text=' . rawurlencode($mensaje), true, 303);
            exit;
        } catch (Throwable $exception) {
            error_log('No se pudo guardar la solicitud: ' . $exception->getMessage());
            $error = 'No pudimos enviar tu solicitud en este momento. Inténtalo más tarde.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#fff8ef">
  <title><?= $enviada ? 'Solicitud enviada' : 'Solicitud de adopción o voluntariado' ?> | Patitas con Hogar</title>
  <style>
    :root{--ink:#28352e;--muted:#66736a;--cream:#fff8ef;--green:#365b45;--orange:#ef9c55;--line:#e9e3d9;--soft:#f4eee4}*{box-sizing:border-box}body{margin:0;background:var(--cream);color:var(--ink);font-family:system-ui,sans-serif;line-height:1.6}.wrap{width:min(760px,calc(100% - 32px));margin:auto}.nav{padding:20px 0}.nav a{font-weight:800;color:var(--green);text-decoration:none}.panel{background:#fff;border:1px solid var(--line);border-radius:20px;padding:clamp(20px,5vw,36px);margin:18px 0 48px;box-shadow:0 12px 36px #26382c12}h1{font-size:clamp(2rem,6vw,3rem);line-height:1.1;margin:.3em 0}p{color:var(--muted)}label{display:block;font-weight:700;margin:18px 0 6px}input,select,textarea{width:100%;padding:12px;border:1px solid #cfcfc7;border-radius:10px;font:inherit;color:var(--ink)}textarea{min-height:120px;resize:vertical}.help{font-size:.9rem}.button{display:inline-block;background:var(--green);color:white;text-decoration:none;border:0;border-radius:999px;padding:12px 20px;font:inherit;font-weight:750;cursor:pointer;margin-top:20px}.button.secondary{background:var(--soft);color:var(--green)}.error{padding:12px;background:#fff0ed;border-radius:10px;color:#8e2c20}.success{padding:16px;background:#edf7ef;border-radius:12px}.hidden{display:none!important}
  </style>
</head>
<body>
  <header class="wrap nav"><a href="/Foto/public/index.html">🐾 Patitas con Hogar</a></header>
  <main class="wrap"><section class="panel">
    <?php if ($enviada): ?>
      <h1>¡Gracias por escribirnos! 🐾</h1>
      <div class="success"><p>Tu solicitud fue recibida y quedó pendiente de revisión por el equipo. Revisaremos tus respuestas y nos pondremos en contacto contigo al correo que indicaste.</p></div>
      <a class="button" href="/Foto/public/index.html">Volver al inicio</a>
    <?php else: ?>
      <p>ADOPCIONES Y VOLUNTARIADO</p><h1>Cuéntanos sobre tu interés.</h1>
      <p>Completa esta encuesta para que podamos conocer tu situación y orientarte. La solicitud será revisada por el equipo; enviarla no confirma una adopción ni una plaza de voluntariado.</p>
      <?php if ($error !== ''): ?><p class="error" role="alert"><?= solicitud_h($error) ?></p><?php endif; ?>
      <form method="post" action="solicitud.php" id="solicitud-form">
        <input type="hidden" name="csrf_token" value="<?= solicitud_h(csrf_token()) ?>">
        <label for="tipo">¿Qué te interesa?</label>
        <select id="tipo" name="tipo" required><option value="adopcion" <?= $tipo === 'adopcion' ? 'selected' : '' ?>>Adopción</option><option value="voluntariado" <?= $tipo === 'voluntariado' ? 'selected' : '' ?>>Voluntariado</option></select>
        <label for="nombre">Tu nombre completo</label><input id="nombre" name="nombre" maxlength="120" autocomplete="name" required value="<?= solicitud_h($_POST['nombre'] ?? '') ?>">
        <label for="correo">Correo electrónico</label><input id="correo" name="correo" type="email" maxlength="190" autocomplete="email" required value="<?= solicitud_h($_POST['correo'] ?? '') ?>">
        <label for="telefono">Teléfono (opcional)</label><input id="telefono" name="telefono" type="tel" maxlength="40" autocomplete="tel" value="<?= solicitud_h($_POST['telefono'] ?? '') ?>">
        <div id="adopcion-fields">
          <label for="perro">¿Qué perro te interesó?</label><input id="perro" name="perro" maxlength="100" value="<?= solicitud_h($perro) ?>" placeholder="Nombre del perrito">
          <label for="hogar">¿Dónde viviría el perro y con quién?</label><textarea id="hogar" name="hogar" maxlength="1000" placeholder="Cuéntanos sobre tu hogar, las personas con quienes viviría y si hay otras mascotas."><?= solicitud_h($_POST['hogar'] ?? '') ?></textarea>
        </div>
        <label for="motivo" id="motivo-label">¿Por qué te interesa adoptar o participar?</label><textarea id="motivo" name="motivo" minlength="10" maxlength="2000" required placeholder="Cuéntanos tus motivos y cualquier detalle que debamos conocer."><?= solicitud_h($_POST['motivo'] ?? '') ?></textarea>
        <label for="disponibilidad">¿Qué disponibilidad tienes? (opcional)</label><textarea id="disponibilidad" name="disponibilidad" maxlength="500" placeholder="Días, horarios u otra información útil."><?= solicitud_h($_POST['disponibilidad'] ?? '') ?></textarea>
        <p class="help">Tus datos se guardarán para revisar la solicitud y se incluirán en el mensaje que se abrirá en WhatsApp.</p>
        <button class="button" type="submit">Continuar a WhatsApp y enviar solicitud 🐾</button>
      </form>
      <a class="button secondary" href="/Foto/ubicacion.html">Volver a contacto</a>
    <?php endif; ?>
  </section></main>
<script>
const tipo=document.getElementById('tipo');
if(tipo){const fields=document.getElementById('adopcion-fields');const perro=document.getElementById('perro');const hogar=document.getElementById('hogar');const update=()=>{const adoption=tipo.value==='adopcion';fields.classList.toggle('hidden',!adoption);perro.required=adoption;hogar.required=adoption;};tipo.addEventListener('change',update);update();}
</script>
</body>
</html>
