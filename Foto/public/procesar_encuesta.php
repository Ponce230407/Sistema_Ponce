<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido.');
}

$opciones = [
    'p1_profesores' => ['Explican súper bien', 'Asumen sabiduría', 'Exceso de tareas', 'Impuntuales'],
    'p2_alumnos' => ['El acarreador', 'El despistado', 'El diseñador', 'El fantasma'],
    'p3_instalaciones' => ['El Wi-Fi', 'Los contactos', 'Los baños', 'Las bancas'],
    'p4_examenes' => ['Estudio previo', 'Café y velocidad 2x', 'A la suerte', 'Aceptar destino'],
    'p5_comida' => ['Ganas de triunfar', 'Tacos y maruchan', 'Bebidas energéticas', 'Risas con amigos'],
];

$nombreCompleto = $_POST['nombre_completo'] ?? null;
if (!is_string($nombreCompleto)) {
    http_response_code(400);
    exit('Escribe tu nombre completo para continuar.');
}
$nombreCompleto = trim($nombreCompleto);
if (preg_match('/^.{1,150}$/u', $nombreCompleto) !== 1) {
    http_response_code(400);
    exit('Escribe un nombre completo válido de hasta 150 caracteres.');
}

$respuestas = [];
foreach ($opciones as $campo => $valoresPermitidos) {
    $respuesta = $_POST[$campo] ?? null;
    if (!is_string($respuesta) || !in_array($respuesta, $valoresPermitidos, true)) {
        http_response_code(400);
        exit('Falta una respuesta válida. Regresa a la encuesta e inténtalo de nuevo.');
    }
    $respuestas[] = $respuesta;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    require_once __DIR__ . '/../../CRUD/conexion.php';

    $conexion->query("CREATE TABLE IF NOT EXISTS respuestas_encuesta (
        id_respuesta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nombre_completo VARCHAR(150) NOT NULL,
        p1_profesores VARCHAR(100) NOT NULL,
        p2_alumnos VARCHAR(100) NOT NULL,
        p3_instalaciones VARCHAR(100) NOT NULL,
        p4_examenes VARCHAR(100) NOT NULL,
        p5_comida VARCHAR(100) NOT NULL,
        fecha_respuesta TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $columnaNombre = $conexion->query("SHOW COLUMNS FROM respuestas_encuesta WHERE Field = 'nombre_completo'");
    if ($columnaNombre->num_rows === 0) {
        $conexion->query("ALTER TABLE respuestas_encuesta ADD COLUMN nombre_completo VARCHAR(150) NOT NULL DEFAULT '' AFTER id_respuesta");
    }

    $stmt = $conexion->prepare('INSERT INTO respuestas_encuesta (nombre_completo, p1_profesores, p2_alumnos, p3_instalaciones, p4_examenes, p5_comida) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssss', $nombreCompleto, $respuestas[0], $respuestas[1], $respuestas[2], $respuestas[3], $respuestas[4]);
    $stmt->execute();

    header('Location: /Foto/public/regalo.html', true, 303);
    exit();
} catch (Throwable $error) {
    error_log('Error al guardar la encuesta: ' . $error->getMessage());
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="es"><meta charset="UTF-8"><title>Error al guardar</title><body><p>No se pudieron guardar tus respuestas. Verifica que MySQL esté activo e inténtalo de nuevo.</p><a href="encuesta.html">Volver a la encuesta</a></body></html>';
}