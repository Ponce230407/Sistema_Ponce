<?php
// Copiar a: C:\xampp\htdocs\seguir\api\eventos.php
// Servicio que RECIBE eventos:  POST api/eventos.php  (cuerpo JSON)

require __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    exit;
}

// sendBeacon manda el JSON como texto plano, así que se lee el cuerpo crudo.
$datos = json_decode(file_get_contents('php://input'), true);

$tipo       = $datos['tipo'] ?? '';
$visitante  = $datos['visitante_id'] ?? '';
$sesion     = $datos['sesion_id'] ?? '';
$patronUuid = '/^[0-9a-f-]{36}$/';

// Nunca confiar en el cliente: cualquiera puede mandar un POST con curl o Postman.
if (!is_array($datos) || !in_array($tipo, ['vista', 'clic', 'tiempo'], true)
    || !preg_match($patronUuid, $visitante) || !preg_match($patronUuid, $sesion)) {
    http_response_code(400); // Petición incorrecta
    exit;
}

$pagina   = mb_substr((string) ($datos['pagina'] ?? ''), 0, 200);
$elemento = $tipo === 'clic' ? mb_substr((string) ($datos['elemento'] ?? ''), 0, 100) : null;
$segundos = $tipo === 'tiempo' ? max(0, min(3600, (int) ($datos['segundos'] ?? 0))) : null;

// ¿Quién es? Lo decide el SERVIDOR con la sesión de PHP, no lo que diga el navegador.
$usuarioId = $_SESSION['usuario_id'] ?? null;

$sql = 'INSERT INTO eventos (visitante_id, sesion_id, usuario_id, tipo, pagina, elemento, segundos)
        VALUES (?, ?, ?, ?, ?, ?, ?)';
$pdo->prepare($sql)->execute([$visitante, $sesion, $usuarioId, $tipo, $pagina, $elemento, $segundos]);

http_response_code(204); // Guardado; no hay nada que responder
