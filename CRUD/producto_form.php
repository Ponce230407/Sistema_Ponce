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
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];

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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $producto ? 'Editar' : 'Nuevo' ?> Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <?php include 'menu.php'; ?>
    <div class="container col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4><?= $producto ? 'Editar' : 'Nuevo' ?> Producto</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="<?= $producto['nombre'] ?? '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control"><?= $producto['descripcion'] ?? '' ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Precio ($)</label>
                            <input type="number" step="0.01" name="precio" class="form-control" value="<?= $producto['precio'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" value="<?= $producto['stock'] ?? '' ?>" required>
                        </div>
                    </div>
                    <a href="productos.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
    <script src="/Seguir/tracker.js"></script>
</body>
</html>