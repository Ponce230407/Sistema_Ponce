<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$apiKey = getenv('GOOGLE_MAPS_API_KEY');
if (!$apiKey) {
    http_response_code(503);
    echo json_encode(['error' => 'El mapa no está configurado.']);
    exit;
}

echo json_encode(['apiKey' => $apiKey], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
