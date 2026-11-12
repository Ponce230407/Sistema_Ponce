<?php
require_once __DIR__ . '/conexion.php';

function require_login(): void
{
    if (empty($_SESSION['usuario_id'])) {
        header('Location: ../LOGIN/login.php');
        exit;
    }
}

function is_admin(PDO $pdo): bool
{
    $configuredEmail = getenv('ADMIN_EMAIL');
    if (!$configuredEmail || empty($_SESSION['usuario_id'])) {
        return false;
    }
    $statement = $pdo->prepare('SELECT correo FROM usuarios WHERE id = ?');
    $statement->execute([$_SESSION['usuario_id']]);
    $email = $statement->fetchColumn();
    return $email && hash_equals(strtolower(trim($configuredEmail)), strtolower(trim($email)));
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
