<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
$loggedIn = !empty($_SESSION['usuario_id']) || (($_SESSION['rol'] ?? '') === 'admin');
$name = $loggedIn ? (string) ($_SESSION['nombre'] ?? '') : '';
$isAdmin = (($_SESSION['rol'] ?? '') === 'admin');
if (!$isAdmin && $loggedIn && !empty($_SESSION['usuario_id'])) {
    try {
        $configuredEmail = strtolower(trim((string) (getenv('ADMIN_EMAIL') ?: '')));
        if ($configuredEmail !== '') {
            $stmt = $pdo->prepare('SELECT correo FROM usuarios WHERE id = ?');
            $stmt->execute([(int) $_SESSION['usuario_id']]);
            $email = $stmt->fetchColumn();
            $isAdmin = $email !== false && hash_equals($configuredEmail, strtolower(trim((string) $email)));
        }
    } catch (PDOException $ignored) {
        $isAdmin = false;
    }
}
echo json_encode(['logged_in' => $loggedIn, 'name' => $name, 'is_admin' => $isAdmin], JSON_UNESCAPED_UNICODE);
