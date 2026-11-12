<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido.');
}

// Se mantienen los nombres de campos existentes para conservar la compatibilidad con la base de datos.
$opciones = [
    'p1_profesores' => ['Redes sociales', 'Recomendación', 'Evento comunitario', 'Búsqueda en internet'],
    'p2_alumnos' => ['Voluntariado', 'Donación de alimento', 'Hogar temporal', 'Adopción responsable'],
    'p3_instalaciones' => ['Adopción responsable', 'Bienestar animal', 'Tenencia responsable', 'Voluntariado'],
    'p4_examenes' => ['Jornada de paseo', 'Recolección de alimento', 'Feria de adopción', 'Taller de cuidados'],
    'p5_comida' => ['Compromiso de por vida', 'Tiempo y paciencia', 'Cuidados veterinarios', 'Espacio y convivencia'],
];

$nombreCompleto = $_POST['nombre_completo'] ?? null;
if (!is_string($nombreCompleto)) {
    http_response_code(400);
    exit('Escribe tu nombre para continuar.');
}
$nombreCompleto = trim($nombreCompleto);
if (preg_match('/^.{1,150}$/u', $nombreCompleto) !== 1) {
    http_response_code(400);
    exit('Escribe un nombre válido de hasta 150 caracteres.');
}

$respuestas = [];
foreach ($opciones as $campo => $valoresPermitidos) {
    $respuesta = $_POST[$campo] ?? null;
    if (!is_string($respuesta) || !in_array($respuesta, $valoresPermitidos, true)) {
        http_response_code(400);
        exit('Falta una respuesta válida. Regresa al formulario e inténtalo de nuevo.');
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
    echo '<!DOCTYPE html><html lang="es"><meta charset="UTF-8"><title>Error al guardar</title><body><p>No se pudo guardar tu opinión. Inténtalo de nuevo más tarde.</p><a href="encuesta.html">Volver al formulario</a></body></html>';
}
