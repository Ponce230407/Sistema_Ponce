<?php
require 'conexion.php';

$error = ''; // <--- Inicializamos la variable aquí para evitar el Warning

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare('SELECT id, nombre, password_hash FROM usuarios WHERE correo = ?');
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        
        if (isset($_COOKIE['visitante_id'])) {
            $pdo->prepare('UPDATE eventos SET usuario_id = ? WHERE visitante_id = ? AND usuario_id IS NULL')
                ->execute([$usuario['id'], $_COOKIE['visitante_id']]);
        }
        
        header('Location: ../Seguir/bienvenida.php');
        exit;
    }
    
    $error = 'Correo o contraseña incorrectos.';
}
?>
