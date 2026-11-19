<?php
require_once __DIR__ . '/conexion.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(404); exit; }
try {
    $stmt = $pdo->prepare("SELECT tipo_mime, imagen FROM fotos_perritos WHERE id = ? AND (estado = 'publicado' OR (estado = 'pendiente' AND ? = 1 AND (($_SESSION['rol'] ?? '') = 'admin' OR (SELECT correo FROM usuarios WHERE id = ?) = ?)))");
    $uid = (int) ($_SESSION['usuario_id'] ?? 0);
    $email = '';
    if ($uid > 0) {
        $lookup = $pdo->prepare('SELECT correo FROM usuarios WHERE id = ?');
        $lookup->execute([$uid]);
        $email = (string) ($lookup->fetchColumn() ?: '');
    }
    $adminEmail = strtolower(trim((string) (getenv('ADMIN_EMAIL') ?: '')));
    $isAdmin = (($_SESSION['rol'] ?? '') === 'admin') || ($adminEmail !== '' && strtolower(trim($email)) === $adminEmail);
    $stmt->execute([$id, isset($_GET['revision']) ? 1 : 0, $uid, $adminEmail]);
    $foto = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$foto || (isset($_GET['revision']) && !$isAdmin)) { http_response_code(404); exit; }
    if (!in_array($foto['tipo_mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) { http_response_code(415); exit; }
    header('Content-Type: ' . $foto['tipo_mime']);
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: public, max-age=3600');
    header('Content-Length: ' . strlen($foto['imagen']));
    echo $foto['imagen'];
} catch (PDOException $exception) {
    error_log('No se pudo servir foto de galería: ' . $exception->getMessage());
    http_response_code(404);
}
