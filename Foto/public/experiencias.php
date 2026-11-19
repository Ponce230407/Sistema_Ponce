<?php
require_once __DIR__ . '/../../Seguir/auth.php';

function prepararExperiencias(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS experiencias_perritos (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NULL,
        nombre VARCHAR(80) NOT NULL,
        historia VARCHAR(1000) NOT NULL,
        foto VARCHAR(255) NOT NULL,
        foto_datos MEDIUMBLOB NULL,
        foto_mime VARCHAR(32) NULL,
        creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_experiencias_creado (creado_en),
        INDEX idx_experiencias_usuario (usuario_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    foreach ([
        'usuario_id' => 'INT NULL AFTER id',
        'foto_datos' => 'MEDIUMBLOB NULL',
        'foto_mime' => 'VARCHAR(32) NULL',
    ] as $column => $definition) {
        $check = $pdo->prepare('SHOW COLUMNS FROM experiencias_perritos LIKE ?');
        $check->execute([$column]);
        if (!$check->fetch()) {
            $pdo->exec("ALTER TABLE experiencias_perritos ADD COLUMN {$column} {$definition}");
        }
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS experiencias_corazones (
        experiencia_id INT UNSIGNED NOT NULL,
        visitante_token CHAR(64) NOT NULL,
        creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (experiencia_id, visitante_token),
        INDEX idx_corazones_experiencia (experiencia_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function responder(int $status, array $data): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

try {
    prepararExperiencias($pdo);

    if (isset($_GET['imagen'])) {
        $id = filter_var($_GET['imagen'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$id) { http_response_code(404); exit; }
        $image = $pdo->prepare('SELECT foto, foto_datos, foto_mime FROM experiencias_perritos WHERE id = ?');
        $image->execute([$id]);
        $row = $image->fetch(PDO::FETCH_ASSOC);
        if (!$row) { http_response_code(404); exit; }
        if (!empty($row['foto_datos']) && in_array($row['foto_mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
            header('Content-Type: ' . $row['foto_mime']);
            header('Cache-Control: public, max-age=86400');
            header('X-Content-Type-Options: nosniff');
            echo $row['foto_datos'];
            exit;
        }
        $legacy = (string) $row['foto'];
        $prefix = '/Foto/public/uploads/experiencias/';
        $filename = basename(parse_url($legacy, PHP_URL_PATH) ?: '');
        $legacyPath = __DIR__ . '/uploads/experiencias/' . $filename;
        if (str_starts_with($legacy, $prefix) && is_file($legacyPath)) {
            header('Location: ' . $legacy, true, 302);
            exit;
        }
        http_response_code(404);
        header('Content-Type: image/svg+xml; charset=utf-8');
        exit('<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600"><rect width="800" height="600" fill="#f4eee4"/><text x="400" y="290" text-anchor="middle" font-family="sans-serif" font-size="30" fill="#365b45">Foto no disponible</text><text x="400" y="340" text-anchor="middle" font-family="sans-serif" font-size="20" fill="#66736a">Vuelve a compartir este momento feliz</text></svg>');
    }

    header('Cache-Control: no-store');
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (empty($_SESSION['experiencias_visitante'])) $_SESSION['experiencias_visitante'] = bin2hex(random_bytes(32));
        $visitorToken = $_SESSION['experiencias_visitante'];
        $userId = (int) ($_SESSION['usuario_id'] ?? 0);
        $admin = is_admin($pdo);
        $statement = $pdo->prepare("SELECT e.id, e.usuario_id, e.nombre, e.historia,
            CASE WHEN e.foto_datos IS NOT NULL THEN CONCAT('/Foto/public/experiencias.php?imagen=', e.id) ELSE e.foto END AS foto,
            e.creado_en,
            (SELECT COUNT(*) FROM experiencias_corazones c WHERE c.experiencia_id = e.id) AS corazones,
            EXISTS(SELECT 1 FROM experiencias_corazones c WHERE c.experiencia_id = e.id AND c.visitante_token = ?) AS me_gusta,
            (e.usuario_id = ? OR ?) AS puede_borrar
            FROM experiencias_perritos e ORDER BY e.creado_en DESC, e.id DESC LIMIT 60");
        $statement->execute([$visitorToken, $userId, $admin ? 1 : 0]);
        responder(200, ['csrf' => csrf_token(), 'experiencias' => $statement->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: GET, POST');
        responder(405, ['error' => 'Método no permitido.']);
    }

    verify_csrf();
    $action = (string) ($_POST['accion'] ?? '');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) responder(400, ['error' => 'Publicación no válida.']);

    if ($action === 'corazon') {
        if (empty($_SESSION['experiencias_visitante'])) $_SESSION['experiencias_visitante'] = bin2hex(random_bytes(32));
        $exists = $pdo->prepare('SELECT 1 FROM experiencias_perritos WHERE id = ?');
        $exists->execute([$id]);
        if (!$exists->fetchColumn()) responder(404, ['error' => 'La publicación ya no existe.']);
        $token = $_SESSION['experiencias_visitante'];
        $liked = $pdo->prepare('SELECT 1 FROM experiencias_corazones WHERE experiencia_id = ? AND visitante_token = ?');
        $liked->execute([$id, $token]);
        if ($liked->fetchColumn()) {
            $pdo->prepare('DELETE FROM experiencias_corazones WHERE experiencia_id = ? AND visitante_token = ?')->execute([$id, $token]);
            $nowLiked = false;
        } else {
            $pdo->prepare('INSERT INTO experiencias_corazones (experiencia_id, visitante_token) VALUES (?, ?)')->execute([$id, $token]);
            $nowLiked = true;
        }
        $count = $pdo->prepare('SELECT COUNT(*) FROM experiencias_corazones WHERE experiencia_id = ?');
        $count->execute([$id]);
        responder(200, ['me_gusta' => $nowLiked, 'corazones' => (int) $count->fetchColumn()]);
    }

    if ($action === 'borrar') {
        $userId = (int) ($_SESSION['usuario_id'] ?? 0);
        $admin = is_admin($pdo);
        if ($userId < 1 && !$admin) responder(401, ['error' => 'Inicia sesión para borrar publicaciones.']);
        $statement = $pdo->prepare('SELECT usuario_id, foto FROM experiencias_perritos WHERE id = ?');
        $statement->execute([$id]);
        $experience = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$experience) responder(404, ['error' => 'La publicación ya no existe.']);
        if ((int) $experience['usuario_id'] !== $userId && !$admin) responder(403, ['error' => 'Solo puedes borrar tus publicaciones.']);
        $pdo->beginTransaction();
        $pdo->prepare('DELETE FROM experiencias_corazones WHERE experiencia_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM experiencias_perritos WHERE id = ?')->execute([$id]);
        $pdo->commit();
        $prefix = '/Foto/public/uploads/experiencias/';
        if (str_starts_with((string) $experience['foto'], $prefix)) {
            $filename = basename((string) $experience['foto']);
            if (preg_match('/\A[a-f0-9]{32}\.(?:jpg|png|webp)\z/', $filename)) @unlink(__DIR__ . '/uploads/experiencias/' . $filename);
        }
        responder(200, ['ok' => true]);
    }

    responder(400, ['error' => 'Acción desconocida.']);
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Error en las experiencias de la comunidad: ' . $error->getMessage());
    responder(500, ['error' => 'No se pudo completar la operación. Inténtalo más tarde.']);
}
