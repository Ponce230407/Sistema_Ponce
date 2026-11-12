<?php
require_once __DIR__ . '/conexion.php';

function require_login(): void
{
    if (empty($_SESSION['usuario_id']) && ($_SESSION['rol'] ?? '') !== 'admin') {
        $returnTo = $_SERVER['REQUEST_URI'] ?? '/Seguir/bienvenida.php';
        header('Location: /LOGIN/login.php?return=' . rawurlencode($returnTo));
        exit;
    }
}

function is_admin(PDO $pdo): bool
{
    $configuredEmail = strtolower(trim(getenv('ADMIN_EMAIL') ?: 'ponceitzel65@gmail.com'));
    if (($_SESSION['rol'] ?? '') === 'admin') {
        return isset($_SESSION['admin_email'])
            && hash_equals($configuredEmail, strtolower(trim((string) $_SESSION['admin_email'])));
    }

    if (empty($_SESSION['usuario_id'])) {
        return false;
    }

    $statement = $pdo->prepare('SELECT correo FROM usuarios WHERE id = ?');
    $statement->execute([$_SESSION['usuario_id']]);
    $email = $statement->fetchColumn();
    return $email && hash_equals($configuredEmail, strtolower(trim((string) $email)));
}

function require_admin(PDO $pdo): void
{
    require_login();
    if (!is_admin($pdo)) {
        http_response_code(403);
        exit('Esta sección está disponible únicamente para el administrador.');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submitted)) {
        http_response_code(400);
        exit('La solicitud expiró. Recarga la página e inténtalo de nuevo.');
    }
}
