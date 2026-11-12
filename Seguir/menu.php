<?php
$isLoggedIn = !empty($_SESSION['usuario_id']) || (($_SESSION['rol'] ?? '') === 'admin');
$isAdministrator = isset($pdo) && function_exists('is_admin') && is_admin($pdo);
$menuUserName = '';
if ($isLoggedIn && isset($pdo) && !empty($_SESSION['usuario_id'])) {
    $nameQuery = $pdo->prepare('SELECT nombre FROM usuarios WHERE id = ?');
    $nameQuery->execute([(int) $_SESSION['usuario_id']]);
    $menuUserName = (string) ($nameQuery->fetchColumn() ?: '');
}
?>
<style>
.site-dropdown{max-width:1140px;margin:0 auto 1.5rem;padding:.75rem 1rem;display:flex;justify-content:space-between;align-items:center;background:#365b45;color:#fff;border-radius:12px;font-family:system-ui,sans-serif;position:relative;box-sizing:border-box}.site-dropdown-brand{color:#fff;text-decoration:none;font-weight:800}.site-dropdown-details{position:relative}.site-dropdown-details summary{list-style:none;cursor:pointer;background:#ef9c55;color:#30251a;padding:.7rem 1.2rem;border-radius:999px;font-weight:750}.site-dropdown-panel{position:absolute;right:0;top:calc(100% + 4px);z-index:30;min-width:260px;padding:.5rem;background:#fff;border-radius:14px;box-shadow:0 12px 30px #0002}.site-dropdown-panel a{display:block;padding:.7rem .85rem;color:#28352e;text-decoration:none;border-radius:8px}.site-dropdown-panel a:hover{background:#f4eee4}.site-dropdown-status{margin-left:auto;margin-right:1rem;color:#e5f4e3;font-size:.9rem;font-weight:700}.site-dropdown-status:before{content:'● ';color:#a7e4a0}@media(max-width:600px){.site-dropdown{flex-wrap:wrap;gap:.7rem}}
</style>
<nav class="site-dropdown" aria-label="Navegación principal">
  <a class="site-dropdown-brand" href="/Foto/public/index.html">🐾 Patitas con Hogar</a>
  <?php if ($isLoggedIn): ?><span class="site-dropdown-status">Sesión iniciada<?= $menuUserName !== '' ? ' · ' . e($menuUserName) : '' ?></span><?php endif; ?>
  <details class="site-dropdown-details">
    <summary aria-label="Abrir menú de navegación">Menú ☰</summary>
    <div class="site-dropdown-panel">
      <a href="/Foto/public/index.html">🏠 Inicio</a>
      <a href="/Foto/ubicacion.php">📍 Contacto y ubicación</a>
      <a href="/Foto/public/encuesta.html">🐶 Cuéntanos tu experiencia</a>
      <a href="/Seguir/publicar-perro.php">🐾 Comparte la experiencia de tu perrito</a>
      <a href="/CRUD/productos.php">📦 Insumos del refugio</a>
      <?php if ($isLoggedIn): ?>
        <a href="/Seguir/bienvenida.php">👤 Mi cuenta</a>
        <a href="/Seguir/cursos.php">🤝 Formas de ayudar</a>
        <a href="/Seguir/dashboard.php">📅 Mis apoyos</a>
        <?php if ($isAdministrator): ?><a href="/Seguir/admin.php">🛡️ Administración</a><?php endif; ?>
        <a href="/LOGIN/logout.php">🚪 Cerrar sesión</a>
      <?php else: ?>
        <a href="/LOGIN/login.php">🔑 Iniciar sesión</a>
        <a href="/LOGIN/registro.php">💛 Quiero colaborar</a>
      <?php endif; ?>
    </div>
  </details>
</nav>
