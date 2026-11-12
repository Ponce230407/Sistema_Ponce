<?php
include 'conexion.php';
$id = $_GET['id'] ?? null;
$producto = null;
if ($id) {
    $stmt = $conexion->prepare("SELECT * FROM productos WHERE id_producto = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = (float) ($_POST['precio'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    if ($id) {
        $stmt = $conexion->prepare("UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=? WHERE id_producto=?");
        $stmt->bind_param("ssdii", $nombre, $descripcion, $precio, $stock, $id);
    } else {
        $stmt = $conexion->prepare("INSERT INTO productos (nombre, descripcion, precio, stock) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $nombre, $descripcion, $precio, $stock);
    }
    $stmt->execute();
    header("Location: productos.php");
    exit();
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= $producto ? 'Editar' : 'Registrar' ?> insumo | Patitas con Hogar</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:#fff8ef!important;color:#28352e}.card{border:0;border-radius:16px;overflow:hidden}.card-header{background:#365b45!important}</style></head><body class="p-4"><?php include 'menu.php'; ?><main class="container col-md-7"><div class="card shadow-sm"><div class="card-header text-white"><h1 class="h4 mb-0"><?= $producto ? 'Editar insumo' : 'Registrar un insumo' ?></h1></div><div class="card-body"><form method="POST"><div class="mb-3"><label class="form-label" for="nombre">Nombre del insumo</label><input id="nombre" type="text" name="nombre" class="form-control" maxlength="150" value="<?= htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div><div class="mb-3"><label class="form-label" for="descripcion">Descripción</label><textarea id="descripcion" name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($producto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label" for="precio">Costo unitario ($)</label><input id="precio" type="number" step="0.01" min="0" name="precio" class="form-control" value="<?= htmlspecialchars((string) ($producto['precio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div><div class="col-md-6 mb-3"><label class="form-label" for="stock">Cantidad disponible</label><input id="stock" type="number" min="0" name="stock" class="form-control" value="<?= htmlspecialchars((string) ($producto['stock'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div></div><a href="productos.php" class="btn btn-outline-secondary">Cancelar</a> <button type="submit" class="btn btn-success">Guardar insumo</button></form></div></div></main><script src="/Seguir/tracker.js"></script></body></html>
