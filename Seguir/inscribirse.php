<?php
require_once __DIR__ . '/auth.php';
require_login();
verify_csrf();

$usuarioId = (int) $_SESSION['usuario_id'];
if (($_POST['accion'] ?? '') === 'cancelar') {
    $apoyoId = filter_input(INPUT_POST, 'apoyo_id', FILTER_VALIDATE_INT);
    if (!$apoyoId) {
        http_response_code(400);
        exit('Apoyo no válido.');
    }
    $statement = $pdo->prepare("UPDATE apoyos SET estado = 'cancelado' WHERE id = ? AND usuario_id = ? AND estado = 'activo'");
    $statement->execute([$apoyoId, $usuarioId]);
    $_SESSION['flash_message'] = $statement->rowCount() ? 'Tu apoyo se canceló correctamente.' : 'No se encontró un apoyo activo para cancelar.';
    header('Location: dashboard.php');
    exit;
}

$actividad = trim($_POST['actividad'] ?? '');
$fecha = $_POST['fecha'] ?? '';
$permitidas = ['Paseo y socialización', 'Apoyo en el refugio', 'Colecta de alimento', 'Hogar temporal', 'Feria de adopción', 'Difusión responsable'];
$date = DateTime::createFromFormat('Y-m-d', $fecha);
if (!in_array($actividad, $permitidas, true) || !$date || $date->format('Y-m-d') !== $fecha || $fecha < date('Y-m-d')) {
    http_response_code(400);
    exit('Selecciona una actividad y una fecha válida.');
}

$exists = $pdo->prepare("SELECT id FROM apoyos WHERE usuario_id = ? AND actividad = ? AND fecha = ? AND estado = 'activo' LIMIT 1");
$exists->execute([$usuarioId, $actividad, $fecha]);
if ($exists->fetchColumn()) {
    $_SESSION['flash_message'] = 'Ya tienes esta actividad agendada para esa fecha.';
} else {
    $insert = $pdo->prepare("INSERT INTO apoyos (usuario_id, actividad, fecha, estado) VALUES (?, ?, ?, 'activo')");
    $insert->execute([$usuarioId, $actividad, $fecha]);
    $_SESSION['flash_message'] = '¡Gracias! Tu apoyo quedó agendado.';
}
header('Location: dashboard.php');
exit;
