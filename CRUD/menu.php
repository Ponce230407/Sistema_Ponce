<style>
    .crud-menu {
        max-width: 1140px;
        margin: 0 auto 1.5rem;
        padding: 0.75rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #243746;
        color: #fff;
        border-radius: 6px;
    }

    .crud-menu a {
        color: inherit;
        text-decoration: none;
    }

    .crud-menu-brand {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        font-weight: 700;
    }

    .crud-menu-photo {
        width: 42px;
        height: 42px;
        border: 2px solid #fff;
        border-radius: 50%;
        object-fit: cover;
    }

    .crud-menu details {
        position: relative;
    }

    .crud-menu summary {
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        list-style: none;
        border: 1px solid #ffffff80;
        border-radius: 4px;
    }

    .crud-menu summary::-webkit-details-marker {
        display: none;
    }

    .crud-menu summary::after {
        content: " \25BE";
    }

    .crud-menu-links {
        position: absolute;
        top: calc(100% + 0.35rem);
        right: 0;
        z-index: 10;
        min-width: 190px;
        padding: 0.35rem 0;
        background: #fff;
        color: #243746;
        border: 1px solid #d5dde2;
        border-radius: 4px;
        box-shadow: 0 4px 12px #0002;
    }

    .crud-menu-links a {
        display: block;
        padding: 0.6rem 0.8rem;
    }

    .crud-menu-links a:hover,
    .crud-menu-links a:focus {
        background: #edf2f5;
    }
</style>

<nav class="crud-menu" aria-label="Navegación principal">
    <a class="crud-menu-brand" href="productos.php">
        <img class="crud-menu-photo" src="../Foto/public/foto.jpg" alt="Fotografía de Itzel">
        <span>App Web</span>
    </a>
    <details>
        <summary>Ir a</summary>
        <div class="crud-menu-links">
            <a href="../ubicacion.html">Ubicación</a>
            <a href="productos.php">CRUD de productos</a>
            <a href="../Foto/public/encuesta.html">Encuesta</a>
            <a href="../LOGIN/login.php">Iniciar sesión</a>
            <a href="../Seguir/">Seguimiento</a>
        </div>
    </details>
</nav>