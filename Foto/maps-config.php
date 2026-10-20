<?php
header('Content-Type: application/json');

// Lee la variable de entorno configurada en Render
$apiKey = getenv('GOOGLE_MAPS_API_KEY');

if (!$apiKey) {
    // Si no está definida, intenta con $_ENV por si acaso
    $apiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';
}

echo json_encode(['apiKey' => $apiKey]);
?>
