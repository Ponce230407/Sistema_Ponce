<?php
require 'conexion.php';
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../LOGIN/login.php');
    exit;
}
$actividades = [
    ['Jornadas de paseo', 'Acompaña a los perros con paseos tranquilos y tiempo de socialización.', 'Voluntariado'],
    ['Apoyo en el refugio', 'Ayuda con tareas cotidianas, orden y preparación de espacios seguros.', 'En el refugio'],
    ['Colecta de alimento', 'Organiza o apoya la recolección de alimento y artículos de cuidado.', 'Comunidad'],
    ['Hogares temporales', 'Ofrece un espacio amable mientras un perro espera su familia definitiva.', 'En casa'],
    ['Ferias de adopción', 'Ayuda a compartir información y conectar perros con familias responsables.', 'Eventos'],
    ['Difusión responsable', 'Comparte historias y recomendaciones de bienestar animal.', 'Desde cualquier lugar'],
];
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Formas de ayudar | Patitas con Hogar</title><link rel="stylesheet" href="estilos.css"><style>.cursos{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:16px}.curso{padding:20px;border:1px solid #e9e3d9;border-radius:16px;background:#fff}.curso h2{font-size:1.15rem}.duracion{color:#66736a;font-size:.9rem}.interes{border:0;border-radius:999px;background:#365b45;color:#fff;padding:10px 15px;font-weight:700;cursor:pointer}.interes.marcado{background:#e8f0e7;color:#244331}</style></head><body><main class="tarjeta ancha"><?php include 'menu.php'; ?><h1>Formas de ayudar</h1><p>Hay muchas maneras de apoyar a los perros rescatados. Elige la que más se adapte a ti; el equipo confirmará las oportunidades disponibles.</p><div class="cursos"><?php foreach ($actividades as [$titulo, $descripcion, $tipo]): ?><article class="curso"><h2><?= e($titulo) ?></h2><p><?= e($descripcion) ?></p><p class="duracion"><?= e($tipo) ?></p><button type="button" class="interes" data-track="Me interesa: <?= e($titulo) ?>">Me interesa</button></article><?php endforeach; ?></div><p><a href="../ubicacion.html">Contactar al equipo para conocer los próximos pasos →</a></p></main><script>document.querySelectorAll('.interes').forEach((boton)=>boton.addEventListener('click',()=>{boton.textContent='✓ Gracias por ofrecerte';boton.classList.add('marcado')}));</script><script src="tracker.js"></script></body></html>
