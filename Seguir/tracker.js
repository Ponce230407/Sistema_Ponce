(() => {
    const API = '/Seguir/api/eventos.php';

    fetch('/Seguir/session-info.php', { credentials: 'same-origin', cache: 'no-store' })
        .then((response) => response.ok ? response.json() : null)
        .then((info) => {
            if (!info) return;
            document.querySelectorAll('[data-session-user]').forEach((node) => {
                node.textContent = info.logged_in && info.name ? `Hola, ${info.name}` : '';
            });
            document.querySelectorAll('[data-auth-link]').forEach((node) => { node.hidden = !info.logged_in; });
            document.querySelectorAll('[data-guest-link]').forEach((node) => { node.hidden = info.logged_in; });
            document.querySelectorAll('[data-admin-link]').forEach((node) => { node.hidden = !info.is_admin; });
        }).catch(() => {});

    function leerCookie(nombre) {
        const coincide = document.cookie.match(new RegExp('(?:^|; )' + nombre + '=([^;]*)'));
        return coincide ? coincide[1] : null;
    }
    function guardarCookie(nombre, valor, dias) {
        document.cookie = `${nombre}=${valor}; max-age=${dias * 86400}; path=/; SameSite=Lax`;
    }
    function nuevoId() {
        if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 3 | 8)).toString(16);
        });
    }
    function iniciar() {
        let visitanteId = leerCookie('visitante_id');
        if (!visitanteId) visitanteId = nuevoId();
        guardarCookie('visitante_id', visitanteId, 365);
        let sesionId = sessionStorage.getItem('sesion_id');
        if (!sesionId) { sesionId = nuevoId(); sessionStorage.setItem('sesion_id', sesionId); }
        function enviar(tipo, extra = {}) {
            const evento = {tipo, visitante_id: visitanteId, sesion_id: sesionId, pagina: location.pathname, ...extra};
            if (navigator.sendBeacon) navigator.sendBeacon(API, JSON.stringify(evento));
        }
        enviar('vista');
        document.addEventListener('click', (evento) => {
            const el = evento.target.closest('a, button, [data-track]');
            if (!el || el.closest('.aviso-cookies')) return;
            enviar('clic', {elemento: el.dataset.track || el.textContent.trim().slice(0, 60) || el.tagName});
        });
        let inicio = Date.now();
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                const segundos = Math.round((Date.now() - inicio) / 1000);
                if (segundos > 0) enviar('tiempo', {segundos});
            } else inicio = Date.now();
        });
    }
    function mostrarAviso() {
        const aviso = document.createElement('div');
        aviso.className = 'aviso-cookies';
        aviso.innerHTML = '<p>Si aceptas, guardamos métricas de páginas vistas, clics y tiempo de navegación. No guardamos lo que escribes en formularios.</p><button type="button" data-respuesta="si">Aceptar</button><button type="button" data-respuesta="no" class="secundario">Rechazar</button>';
        aviso.addEventListener('click', (evento) => {
            const respuesta = evento.target.dataset.respuesta;
            if (!respuesta) return;
            guardarCookie('analitica', respuesta, 180);
            aviso.remove();
            if (respuesta === 'si') iniciar();
        });
        document.body.appendChild(aviso);
    }
    const consentimiento = leerCookie('analitica');
    if (consentimiento === 'si') iniciar();
    else if (consentimiento === null) mostrarAviso();
})();
