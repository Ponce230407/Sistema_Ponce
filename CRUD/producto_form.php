<?php
require_once __DIR__ . '/../Seguir/auth.php';
require_once __DIR__ . '/../LOGIN/conexion.php';
require_admin($pdo);
require_once __DIR__ . '/conexion.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
$product = null;
if ($id) { $stmt = $conexion->prepare('SELECT id_producto, nombre, descripcion FROM productos WHERE id_producto=? AND activo=1'); $stmt->bind_param('i',$id); $stmt->execute(); $product=$stmt->get_result()->fetch_assoc(); if(!$product){http_response_code(404);exit('Producto no encontrado.');} }
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf(); $name=trim((string)($_POST['nombre']??'')); $description=trim((string)($_POST['descripcion']??''));
    if($name==='' || mb_strlen($name)>150 || mb_strlen($description)>2000){$error='Escribe un nombre válido (máximo 150 caracteres) y una descripción de hasta 2000 caracteres.';}
    else { if($id){$stmt=$conexion->prepare('UPDATE productos SET nombre=?, descripcion=? WHERE id_producto=?');$stmt->bind_param('ssi',$name,$description,$id);}else{$stmt=$conexion->prepare('INSERT INTO productos (nombre, descripcion, precio, stock) VALUES (?, ?, 0, 0)');$stmt->bind_param('ss',$name,$description);} $stmt->execute();header('Location: productos.php',true,303);exit; }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?=$id?'Editar':'Agregar'?> producto</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:#fff8ef;color:#28352e}.btn-green{background:#365b45;color:white}</style></head><body class="p-3 p-md-4"><?php require __DIR__.'/menu.php'; ?><main class="container" style="max-width:720px"><h1 class="h2"><?=$id?'Editar':'Agregar'?> producto</h1><p class="text-secondary">Solo el administrador puede modificar el catálogo visible para donantes.</p><?php if($error): ?><p class="alert alert-danger"><?=e($error)?></p><?php endif; ?><form method="post" class="bg-white border rounded-4 p-4"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><label class="form-label">Nombre<input class="form-control" name="nombre" maxlength="150" required value="<?=e($_POST['nombre']??$product['nombre']??'')?>"></label><label class="form-label mt-3">Descripción<textarea class="form-control" name="descripcion" rows="4" maxlength="2000"><?=e($_POST['descripcion']??$product['descripcion']??'')?></textarea></label><div class="mt-4 d-flex gap-2"><button class="btn btn-green">Guardar</button><a class="btn btn-outline-secondary" href="productos.php">Cancelar</a></div></form></main><script src="/Seguir/tracker.js" defer></script></body></html>
