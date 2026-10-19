<?php
include 'conexion.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $conexion->prepare("UPDATE productos SET activo = 0 WHERE id_producto = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: productos.php");
exit();
?>