<?php
// Centralized session settings: every PHP section must include this bootstrap
// rather than starting a session with different cookie parameters.
if (session_status() === PHP_SESSION_NONE) {
    session_name('patitas_session');
    ini_set('session.gc_maxlifetime', (string) (30 * 24 * 60 * 60));
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
    session_set_cookie_params([
        'lifetime' => 30 * 24 * 60 * 60,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
