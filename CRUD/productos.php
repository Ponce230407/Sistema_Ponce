<?php
include 'conexion.php';
$sql = "SELECT * FROM productos WHERE activo = 1 ORDER BY id_producto DESC";
$resultado = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Insumos del refugio | Patitas con Hogar</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:#fff8ef!important;color:#28352e}.table,.card{border-radius:14px;overflow:hidden}.table thead{background:#365b45;color:#fff}.btn-success{background:#365b45;border-color:#365b45}</style></head>
<body class="p-4"><?php include 'menu.php'; ?><div class="container"><div class="d-flex justify-content-between align-items-center mb-3 gap-3 flex-wrap"><div><h1 class="h2">Insumos del refugio</h1><p class="text-secondary mb-0">Lleva el control del alimento y los artículos que ayudan a cuidar a nuestros perros.</p></div><a href="producto_form.php" class="btn btn-success">+ Añadir insumo</a></div>
<div class="table-responsive"><table class="table table-striped table-hover shadow-sm bg-white"><thead><tr><th>ID</th><th>Insumo</th><th>Descripción</th><th>Precio</th><th>Existencias</th><th>Acciones</th></tr></thead><tbody>
<?php while($row = $resultado->fetch_assoc()): ?><tr><td><?= (int) $row['id_producto'] ?></td><td><?= htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($row['descripcion'], ENT_QUOTES, 'UTF-8') ?></td><td>$<?= number_format((float) $row['precio'], 2) ?></td><td><?= (int) $row['stock'] ?></td><td><a href="producto_form.php?id=<?= (int) $row['id_producto'] ?>" class="btn btn-warning btn-sm">Editar</a> <a href="producto_eliminar.php?id=<?= (int) $row['id_producto'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Retirar este insumo del inventario?')">Retirar</a></td></tr><?php endwhile; ?>
</tbody></table></div><p class="text-secondary small">Los precios y existencias son datos internos de gestión del refugio.</p></div><script src="/Seguir/tracker.js"></script></body></html>
