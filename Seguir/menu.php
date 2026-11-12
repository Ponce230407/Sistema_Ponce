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
.site-dropdown{max-width:1140px;margin:0 auto 1.5rem;padding:.75rem 1rem;display:flex;justify-content:space-between;align-items:center;gap:12px;background:#365b45;color:#fff;border-radius:12px;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;line-height:1.35;flex-wrap:wrap;box-shadow:0 10px 26px rgba(17,42,31,.12)}
.site-dropdown-brand,.site-dropdown-status,.site-dropdown-details{min-width:0}
.site-dropdown-brand{font-weight:800;text-decoration:none;color:#fff;font-size:1.1rem;white-space:nowrap}
.site-dropdown-status{font-size:.84rem;opacity:.92;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.site-dropdown-details{margin-left:auto;position:relative}
.site-dropdown-details summary{display:flex;align-items:center;justify-content:center;gap:.45rem;padding:.6rem .9rem;border-radius:999px;background:rgba(255,255,255,.08);cursor:pointer;list-style:none;font-weight:700;white-space:nowrap}
.site-dropdown-details summary::-webkit-details-marker{display:none}
.site-dropdown-panel{position:absolute;top:calc(100% + .5rem);right:0;width:min(320px,calc(100vw - 2rem));display:grid;gap:.45rem;padding:.7rem;background:#fff;color:#1d3129;border:1px solid rgba(54,91,69,.14);border-radius:14px;box-shadow:0 18px 38px rgba(38,56,44,.14);z-index:50}
.site-dropdown-panel a{color:#1d3129;text-decoration:none;font-size:1rem;font-weight:700;padding:.6rem .8rem;border-radius:10px;display:flex;align-items:center;gap:.6rem}
.site-dropdown-panel a:hover{background:rgba(54,91,69,.08)}
@media (max-width:700px){
  .site-dropdown{padding:10px 12px;gap:8px}
  .site-dropdown-brand{font-size:.9rem;white-space:normal}
  .site-dropdown-status{font-size:.7rem;max-width:100%;white-space:normal}
  .site-dropdown-details{margin-left:0;width:100%}
  .site-dropdown-details summary{width:100%;justify-content:center}
  .site-dropdown-panel{left:0;right:auto;width:min(100%, 300px)}
}
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
