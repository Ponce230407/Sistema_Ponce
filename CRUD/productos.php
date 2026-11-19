<?php
require_once __DIR__ . '/../Seguir/auth.php';
require_once __DIR__ . '/../LOGIN/conexion.php';
$admin = is_admin($pdo);
$pdo->exec("CREATE TABLE IF NOT EXISTS donaciones_interes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    producto_id INT UNSIGNED NOT NULL,
    usuario_id INT NULL,
    nombre VARCHAR(100) NOT NULL,
    contacto VARCHAR(190) NOT NULL,
    cantidad INT UNSIGNED NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_donaciones_producto (producto_id), INDEX idx_donaciones_fecha (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$notice = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'ofrecer') {
    verify_csrf();
    $name = trim((string)($_POST['nombre'] ?? ''));
    $contact = trim((string)($_POST['contacto'] ?? ''));
    $ids = $_POST['productos'] ?? [];
    if (!is_array($ids) || !$ids || $name === '' || strlen($name) > 100 || $contact === '' || strlen($contact) > 190) {
        $error = 'Escribe tu nombre, un medio de contacto y selecciona al menos un insumo.';
    } else {
        $selected = array_values(array_unique(array_filter(array_map('intval', $ids), static fn($id) => $id > 0)));
        $check = $pdo->prepare('SELECT id_producto FROM productos WHERE activo = 1 AND id_producto = ?');
        $insert = $pdo->prepare('INSERT INTO donaciones_interes (producto_id, usuario_id, nombre, contacto, cantidad) VALUES (?, ?, ?, ?, ?)');
        $saved = 0;
        foreach ($selected as $productId) {
            $check->execute([$productId]);
            if (!$check->fetchColumn()) continue;
            $quantity = max(1, min(999, (int)($_POST['cantidad'][$productId] ?? 1)));
            $userId = (int)($_SESSION['usuario_id'] ?? 0);
            $insert->execute([$productId, $userId > 0 ? $userId : null, $name, $contact, $quantity]);
            $saved++;
        }
if ($saved) {
    if (!empty($_SESSION['usuario_id'])) {
        header('Location: /Seguir/dashboard.php?donacion=ok');
        exit;
    }
    $notice = '¡Gracias! El refugio recibió tu oferta de donación y se pondrá en contacto contigo.';
}
}
$products = $pdo->query('SELECT id_producto, nombre, descripcion FROM productos WHERE activo = 1 ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);
$csrf = csrf_token();
$offers = $admin ? $pdo->query('SELECT d.nombre, d.contacto, d.cantidad, d.creado_en, p.nombre AS producto FROM donaciones_interes d JOIN productos p ON p.id_producto=d.producto_id ORDER BY d.creado_en DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC) : [];
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Insumos del refugio | Patitas con Hogar</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:#fff8ef;color:#28352e}.item{height:100%;border:1px solid #e5e0d6;border-radius:16px;padding:18px;background:white}.item input[type=checkbox]{width:21px;height:21px;accent-color:#365b45}.btn-green{background:#365b45;color:white}.btn-green:hover{background:#294936;color:white}</style></head><body class="p-3 p-md-4"><?php require __DIR__.'/menu.php'; ?><main class="container"><header class="mb-4"><h1>Insumos del refugio</h1><p class="text-secondary">El administrador mantiene el catálogo. Elige los productos que te gustaría donar y nos pondremos en contacto contigo.</p></header>
<?php if($notice): ?><div class="alert alert-success" role="status"><?=e($notice)?></div><?php endif; ?><?php if($error): ?><div class="alert alert-danger" role="alert"><?=e($error)?></div><?php endif; ?>
<?php if($admin): ?><div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h4 mb-0">Administrar catálogo</h2><a href="producto_form.php" class="btn btn-green">+ Agregar producto</a></div><?php endif; ?>
<?php if(!$products): ?><p class="alert alert-info">Aún no hay insumos publicados. El administrador puede agregar productos.</p><?php else: ?><form method="post"><input type="hidden" name="accion" value="ofrecer"><input type="hidden" name="csrf_token" value="<?=e($csrf)?>"><div class="row g-3"><?php foreach($products as $product): ?><div class="col-12 col-md-6 col-lg-4"><article class="item"><label class="d-flex gap-3 align-items-start"><input type="checkbox" name="productos[]" value="<?=(int)$product['id_producto']?>"><span><strong><?=e($product['nombre'])?></strong><br><span class="text-secondary"><?=e($product['descripcion'] ?: 'Insumo necesario para el cuidado de nuestros perros.')?></span></span></label><label class="mt-3 small">Cantidad que puedes donar <input class="form-control" type="number" min="1" max="999" value="1" name="cantidad[<?=(int)$product['id_producto']?>]"></label><?php if($admin): ?><div class="mt-3 d-flex gap-2"><a class="btn btn-sm btn-outline-secondary" href="producto_form.php?id=<?=(int)$product['id_producto']?>">Editar</a><form method="post" action="producto_eliminar.php" onsubmit="return confirm('¿Eliminar este producto del catálogo?')"><input type="hidden" name="id" value="<?=(int)$product['id_producto']?>"><input type="hidden" name="csrf_token" value="<?=e($csrf)?>"><button class="btn btn-sm btn-outline-danger">Eliminar</button></form></div><?php endif; ?></article></div><?php endforeach; ?></div><section class="bg-white border rounded-4 p-3 p-md-4 mt-4"><h2 class="h4">¿Qué puedes donar?</h2><p class="text-secondary">Selecciona uno o varios productos; no necesitas crear una cuenta.</p><div class="row g-3"><label class="col-md-4">Tu nombre<input class="form-control" name="nombre" maxlength="100" required></label><label class="col-md-5">Correo o teléfono de contacto<input class="form-control" name="contacto" maxlength="190" required></label><div class="col-md-3 d-flex align-items-end"><button class="btn btn-green w-100">Ofrecer donación</button></div></div></section></form><?php endif; ?>
<?php if($admin && $offers): ?><section class="mt-5"><h2 class="h4">Ofertas de donación recientes</h2><div class="table-responsive"><table class="table bg-white"><thead><tr><th>Producto</th><th>Persona</th><th>Contacto</th><th>Cantidad</th><th>Fecha</th></tr></thead><tbody><?php foreach($offers as $offer): ?><tr><td><?=e($offer['producto'])?></td><td><?=e($offer['nombre'])?></td><td><?=e($offer['contacto'])?></td><td><?=(int)$offer['cantidad']?></td><td><?=e($offer['creado_en'])?></td></tr><?php endforeach; ?></tbody></table></div></section><?php endif; ?></main><script src="/Seguir/tracker.js" defer></script></body></html>
