<?php
// Menú común de navegación de páginas PHP autenticadas.
$isLoggedIn = !empty($_SESSION['usuario_id']) || (($_SESSION['rol'] ?? '') === 'admin');
$isAdministrator = $isLoggedIn && function_exists('is_admin') && is_admin($pdo);
?>
<header class="app-header">
  <div class="marca-sesion"><a class="marca" href="/Foto/public/index.html">🐾 Patitas con Hogar</a><?php if ($isLoggedIn): ?><span class="nombre-sesion">Hola, <?= e($_SESSION['nombre'] ?? 'Usuario') ?></span><?php endif; ?></div>
  <details class="menu-dropdown">
    <summary class="menu-toggle" aria-label="Abrir menú de navegación">Menú ☰</summary>
    <nav class="menu-content" aria-label="Navegación principal">
      <a href="/Foto/public/index.html">🏠 Inicio</a><a href="/Foto/public/donaciones.html">💛 Donaciones / colaborar</a><a href="/ubicacion.html">📍 Contacto y ubicación</a><a href="/Foto/public/encuesta.html">🐶 Encuesta</a><a href="/CRUD/productos.php">📦 Insumos del refugio</a>
      <?php if ($isLoggedIn): ?><a href="/Seguir/bienvenida.php">👤 Mi cuenta</a><a href="/Seguir/cursos.php">🤝 Formas de ayudar</a><a href="/Seguir/dashboard.php">📅 Mis apoyos</a><?php if ($isAdministrator): ?><a href="/Seguir/admin.php">🛡️ Administración</a><?php endif; ?><a href="/LOGIN/logout.php">🚪 Cerrar sesión</a><?php else: ?><a href="/LOGIN/login.php">🔑 Iniciar sesión</a><a href="/LOGIN/registro.php">📝 Crear cuenta</a><?php endif; ?>
    </nav>
  </details>
</header>
