// Rastreador de analítica: se incluye en TODAS las páginas.

(() => {
    const API = '/Seguir/api/eventos.php';

    function leerCookie(nombre) {
        const coincide = document.cookie.match(new RegExp('(?:^|; )' + nombre + '=([^;]*)'));
        return coincide ? coincide[1] : null;
    }

    function guardarCookie(nombre, valor, dias) {
        document.cookie = `${nombre}=${valor}; max-age=${dias * 86400}; path=/; SameSite=Lax`;
    }

    // crypto.randomUUID solo existe en HTTPS o localhost; si no, se genera uno a mano.
    function nuevoId() {
        if (window.crypto && crypto.randomUUID) {
            return crypto.randomUUID();
        }
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }

    // ---------- 1. Consentimiento ----------
    function mostrarAviso() {
        const aviso = document.createElement('div');
        aviso.className = 'aviso-cookies';
        aviso.innerHTML = `
            <p>Usamos cookies para medir cómo se usa este sitio (páginas, clics y tiempo).
               No registramos lo que escribes en los formularios.</p>
            <button type="button" data-respuesta="si">Aceptar</button>
            <button type="button" data-respuesta="no" class="secundario">Rechazar</button>`;
        aviso.addEventListener('click', (evento) => {
            const respuesta = evento.target.dataset.respuesta;
            if (!respuesta) return;
            guardarCookie('analitica', respuesta, 180);
            aviso.remove();
            if (respuesta === 'si') iniciar();
        });
        document.body.appendChild(aviso);
    }

    // Enlace "Privacidad" del menú: borra la decisión para volver a preguntar.
    const enlacePrivacidad = document.getElementById('cambiar-privacidad');
    if (enlacePrivacidad) {
        enlacePrivacidad.addEventListener('click', (evento) => {
            evento.preventDefault();
            guardarCookie('analitica', '', 0);
            location.reload();
        });
    }

    const consentimiento = leerCookie('analitica');
    if (consentimiento === 'si') {
        iniciar();
    } else if (consentimiento === null) {
        mostrarAviso();
    }
    // Si es 'no', no se hace nada: no se rastrea.

    // ---------- 2. Identificar y observar ----------
    function iniciar() {
        // Visitante: dura 1 año en una cookie (mismo navegador = mismo visitante).
        let visitanteId = leerCookie('visitante_id');
        if (!visitanteId) {
            visitanteId = nuevoId();
        }
        guardarCookie('visitante_id', visitanteId, 365);

        // Sesión de navegación: dura mientras la pestaña esté abierta.
        let sesionId = sessionStorage.getItem('sesion_id');
        if (!sesionId) {
            sesionId = nuevoId();
            sessionStorage.setItem('sesion_id', sesionId);
        }

        // sendBeacon garantiza el envío aunque el usuario cambie de página o cierre la pestaña.
        function enviar(tipo, extra = {}) {
            const evento = {
                tipo,
                visitante_id: visitanteId,
                sesion_id: sesionId,
                pagina: location.pathname,
                ...extra
            };
            navigator.sendBeacon(API, JSON.stringify(evento));
        }

        // a) Vista de página
        enviar('vista');

        // b) Clics en enlaces y botones.
        //    data-track da un nombre fijo; si no hay, se usa el texto visible.
        document.addEventListener('click', (evento) => {
            const el = evento.target.closest('a, button, [data-track]');
            if (!el || el.closest('.aviso-cookies')) return;
            const nombre = el.dataset.track || el.textContent.trim().slice(0, 60) || el.tagName;
            enviar('clic', { elemento: nombre });
        });

        // c) Tiempo activo: solo cuenta mientras la pestaña está visible.
        let inicio = Date.now();
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                const segundos = Math.round((Date.now() - inicio) / 1000);
                if (segundos > 0) enviar('tiempo', { segundos });
            } else {
                inicio = Date.now();
            }
        });
    }
})();
