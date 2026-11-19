<?php
require_once __DIR__ . '/../Seguir/auth.php';
require_once __DIR__ . '/../LOGIN/conexion.php';
require_admin($pdo);
require_once __DIR__ . '/conexion.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);header('Allow: POST');exit('Método no permitido.');}
verify_csrf();$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if($id){$stmt=$conexion->prepare('UPDATE productos SET activo=0 WHERE id_producto=?');$stmt->bind_param('i',$id);$stmt->execute();}
header('Location: productos.php',true,303);exit;
