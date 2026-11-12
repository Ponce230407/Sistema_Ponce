// Rastreador de analítica y mejoras comunes de navegación.

(() => {
    const API = '/Seguir/api/eventos.php';

    // La portada es HTML estático; consultar la sesión permite mostrar claramente
    // que el usuario sigue dentro aunque haya vuelto al inicio.
    fetch('/Foto/public/estado-sesion.php', { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then((response) => response.ok ? response.json() : null)
        .then((estado) => {
            if (!estado || !estado.autenticado) return;
            document.querySelectorAll('[data-session-indicator]').forEach((badge) => {
                badge.textContent = estado.nombre ? `Sesión iniciada · ${estado.nombre}` : 'Sesión iniciada';
                badge.classList.add('is-visible');
            });
            document.querySelectorAll('[data-login-link]').forEach((link) => {
                link.href = estado.es_admin ? '/Seguir/admin.php' : '/Seguir/bienvenida.php';
                link.textContent = estado.es_admin ? '🛡️ Panel de administración' : '👤 Mi cuenta';
            });
            document.querySelectorAll('[data-register-link]').forEach((link) => link.remove());
        }).catch(() => {});

    // La portada conserva sus tarjetas de adopción por separado, pero la sección
    // comunitaria solo muestra recuerdos felices, nunca solicitudes de adopción.
    const community = document.getElementById('publicaciones');
    if (community) {
        const heading = community.parentElement.querySelector('h2');
        const intro = community.parentElement.querySelector('.sub');
        if (heading) heading.textContent = 'Momentos felices de la comunidad';
        if (intro) intro.textContent = 'Fotos y recuerdos compartidos por personas que quieren a sus perritos. No son publicaciones de adopción.';
        const renderExperiences = async () => {
            try {
                const response = await fetch('/Foto/public/perros.php', { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('No se pudieron cargar las experiencias.');
                const experiences = await response.json();
                community.replaceChildren();
                if (!Array.isArray(experiences) || !experiences.length) {
                    const empty = document.createElement('p');
                    empty.className = 'empty';
                    empty.textContent = 'Aún no hay experiencias compartidas. ¡Comparte la primera!';
                    community.append(empty);
                } else {
                    experiences.forEach((experience) => {
                        const card = document.createElement('article');
                        card.className = 'community-card';
                        const image = document.createElement('img');
                        image.src = experience.foto;
                        image.alt = `Foto de ${experience.nombre}`;
                        const title = document.createElement('h3');
                        title.textContent = experience.nombre;
                        const story = document.createElement('p');
                        story.textContent = experience.historia;
                        card.append(image, title, story);
                        community.append(card);
                    });
                }
            } catch {
                community.replaceChildren();
                const error = document.createElement('p');
                error.className = 'empty';
                error.textContent = 'Por el momento no se pudieron cargar las experiencias.';
                community.append(error);
            }
        };
        renderExperiences();
    }

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
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }

    function mostrarAviso() {
        const aviso = document.createElement('div');
        aviso.className = 'aviso-cookies';
        aviso.innerHTML = `<p>Usamos cookies para medir cómo se usa este sitio (páginas, clics y tiempo). No registramos lo que escribes en los formularios.</p><button type="button" data-respuesta="si">Aceptar</button><button type="button" data-respuesta="no" class="secundario">Rechazar</button>`;
        aviso.addEventListener('click', (evento) => {
            const respuesta = evento.target.dataset.respuesta;
            if (!respuesta) return;
            guardarCookie('analitica', respuesta, 180);
            aviso.remove();
            if (respuesta === 'si') iniciar();
        });
        document.body.appendChild(aviso);
    }

    const enlacePrivacidad = document.getElementById('cambiar-privacidad');
    if (enlacePrivacidad) {
        enlacePrivacidad.addEventListener('click', (evento) => {
            evento.preventDefault();
            guardarCookie('analitica', '', 0);
            location.reload();
        });
    }

    const consentimiento = leerCookie('analitica');
    if (consentimiento === 'si') iniciar();
    else if (consentimiento === null) mostrarAviso();

    function iniciar() {
        let visitanteId = leerCookie('visitante_id');
        if (!visitanteId) visitanteId = nuevoId();
        guardarCookie('visitante_id', visitanteId, 365);
        let sesionId = sessionStorage.getItem('sesion_id');
        if (!sesionId) {
            sesionId = nuevoId();
            sessionStorage.setItem('sesion_id', sesionId);
        }
        function enviar(tipo, extra = {}) {
            const evento = { tipo, visitante_id: visitanteId, sesion_id: sesionId, pagina: location.pathname, ...extra };
            navigator.sendBeacon(API, JSON.stringify(evento));
        }
        enviar('vista');
        document.addEventListener('click', (evento) => {
            const el = evento.target.closest('a, button, [data-track]');
            if (!el || el.closest('.aviso-cookies')) return;
            const nombre = el.dataset.track || el.textContent.trim().slice(0, 60) || el.tagName;
            enviar('clic', { elemento: nombre });
        });
        let inicio = Date.now();
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                const segundos = Math.round((Date.now() - inicio) / 1000);
                if (segundos > 0) enviar('tiempo', { segundos });
            } else inicio = Date.now();
        });
    }
})();
