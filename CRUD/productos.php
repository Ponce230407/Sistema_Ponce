<?php
include 'conexion.php';
$sql = "SELECT * FROM productos WHERE activo = 1 ORDER BY id_producto DESC";
$resultado = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <?php include 'menu.php'; ?>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Catálogo de Productos</h2>
            <a href="producto_form.php" class="btn btn-success">+ Nuevo Producto</a>
        </div>

        <table class="table table-striped table-hover shadow-sm bg-white rounded">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id_producto'] ?></td>
                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                    <td><?= htmlspecialchars($row['descripcion']) ?></td>
                    <td>$<?= number_format($row['precio'], 2) ?></td>
                    <td><?= $row['stock'] ?></td>
                    <td>
                        <a href="producto_form.php?id=<?= $row['id_producto'] ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="producto_eliminar.php?id=<?= $row['id_producto'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar producto?')">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="/Seguir/tracker.js"></script>
</body>
</html>