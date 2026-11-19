<?php
$nombre = trim((string) ($_GET['perro'] ?? 'un perrito'));
$nombre = mb_substr($nombre, 0, 80, 'UTF-8');
$numero = preg_replace('/\D+/', '', (string) getenv('WHATSAPP_NUMBER'));
if ($numero === '') {
    header('Location: /Foto/ubicacion.php?contacto=pendiente', true, 303);
    exit;
}
$mensaje = 'Hola, quisiera preguntar por ' . $nombre . ' que vi en Patitas con Hogar.';
header('Location: https://wa.me/' . $numero . '?text=' . rawurlencode($mensaje), true, 302);
exit;
