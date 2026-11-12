// Navegación, historias de la comunidad y analítica básica.
(() => {
    fetch('/Foto/public/estado-sesion.php', { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then((response) => response.ok ? response.json() : null)
        .then((state) => {
            if (!state || !state.autenticado) return;
            document.querySelectorAll('[data-session-indicator]').forEach((badge) => {
                badge.textContent = state.nombre ? `Sesión iniciada · ${state.nombre}` : 'Sesión iniciada';
                badge.classList.add('is-visible');
            });
            document.querySelectorAll('[data-login-link]').forEach((link) => {
                link.href = state.es_admin ? '/Seguir/admin.php' : '/Seguir/bienvenida.php';
                link.textContent = state.es_admin ? '🛡️ Panel de administración' : '👤 Mi cuenta';
            });
            document.querySelectorAll('[data-register-link]').forEach((link) => link.remove());
        }).catch(() => {});

    const community = document.getElementById('publicaciones');
    if (community) {
        const heading = community.parentElement.querySelector('h2');
        const intro = community.parentElement.querySelector('.sub');
        if (heading) heading.textContent = 'Momentos felices de la comunidad';
        if (intro) intro.textContent = 'Comparte recuerdos bonitos con tus mascotas, mira los de otras personas y deja un corazón.';
        community.innerHTML = '<p class="empty">Cargando momentos felices…</p>';
        const style = document.createElement('style');
        style.textContent = '.community-card{padding-bottom:16px}.community-card h3,.community-card p{margin-left:18px;margin-right:18px}.community-card h3{margin-top:16px}.community-actions{display:flex;align-items:center;gap:10px;margin:0 18px}.community-actions button{border:0;border-radius:999px;padding:9px 14px;font:inherit;font-weight:750;cursor:pointer}.heart-button{background:#fff0f1;color:#9e2941}.heart-button[aria-pressed="true"]{background:#ffe0e5;color:#a31535}.delete-button{background:#f4eee4;color:#543d32}.community-actions button:disabled{opacity:.6;cursor:wait}';
        document.head.append(style);
        let csrf = '';
        const loadExperiences = async () => {
            try {
                const response = await fetch('/Foto/public/experiencias.php', { credentials: 'same-origin', headers: { Accept: 'application/json' }, cache: 'no-store' });
                if (!response.ok) throw new Error();
                const data = await response.json();
                csrf = data.csrf || csrf;
                community.replaceChildren();
                if (!Array.isArray(data.experiencias) || data.experiencias.length === 0) {
                    const empty = document.createElement('p'); empty.className = 'empty'; empty.textContent = 'Aún no hay momentos compartidos. ¡Comparte el primero!'; community.append(empty); return;
                }
                data.experiencias.forEach((item) => {
                    const card = document.createElement('article'); card.className = 'community-card';
                    const image = document.createElement('img'); image.src = item.foto; image.alt = `Foto de ${item.nombre}`; image.loading = 'lazy';
                    const title = document.createElement('h3'); title.textContent = item.nombre;
                    const story = document.createElement('p'); story.textContent = item.historia;
                    const actions = document.createElement('div'); actions.className = 'community-actions';
                    const heart = document.createElement('button'); heart.type = 'button'; heart.className = 'heart-button'; heart.setAttribute('aria-pressed', Number(item.me_gusta) === 1 ? 'true' : 'false'); heart.textContent = `❤️ ${Number(item.corazones) || 0}`; heart.setAttribute('aria-label', `Dar corazón. ${Number(item.corazones) || 0} corazones`);
                    heart.addEventListener('click', async () => {
                        heart.disabled = true;
                        try {
                            const body = new URLSearchParams({ accion: 'corazon', id: item.id, csrf_token: csrf });
                            const result = await fetch('/Foto/public/experiencias.php', { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' }, body });
                            const updated = await result.json(); if (!result.ok) throw new Error(updated.error || 'No se pudo guardar el corazón.');
                            item.me_gusta = updated.me_gusta ? 1 : 0; item.corazones = updated.corazones;
                            heart.setAttribute('aria-pressed', updated.me_gusta ? 'true' : 'false'); heart.textContent = `❤️ ${updated.corazones}`;
                        } catch (error) { alert(error.message); } finally { heart.disabled = false; }
                    });
                    actions.append(heart);
                    if (Number(item.puede_borrar) === 1) {
                        const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'delete-button'; remove.textContent = '🗑️ Borrar';
                        remove.addEventListener('click', async () => {
                            if (!confirm('¿Seguro que quieres borrar esta foto?')) return;
                            remove.disabled = true;
                            try {
                                const body = new URLSearchParams({ accion: 'borrar', id: item.id, csrf_token: csrf });
                                const result = await fetch('/Foto/public/experiencias.php', { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' }, body });
                                const updated = await result.json(); if (!result.ok) throw new Error(updated.error || 'No se pudo borrar la foto.');
                                card.remove(); if (!community.querySelector('.community-card')) loadExperiences();
                            } catch (error) { alert(error.message); remove.disabled = false; }
                        });
                        actions.append(remove);
                    }
                    card.append(image, title, story, actions); community.append(card);
                });
            } catch {
                community.replaceChildren(); const error = document.createElement('p'); error.className = 'empty'; error.textContent = 'Por el momento no se pudieron cargar los momentos felices.'; community.append(error);
            }
        };
        loadExperiences();
    }

    const consent = document.cookie.match(/(?:^|; )analitica=([^;]*)/);
    if (consent && decodeURIComponent(consent[1]) === 'si') {
        let visitor = document.cookie.match(/(?:^|; )visitante_id=([^;]*)/);
        if (!visitor) {
            visitor = [null, window.crypto?.randomUUID?.() || `${Date.now()}-${Math.random()}`];
            document.cookie = `visitante_id=${encodeURIComponent(visitor[1])}; max-age=31536000; path=/; SameSite=Lax`;
        }
        const session = sessionStorage.getItem('sesion_id') || (window.crypto?.randomUUID?.() || `${Date.now()}-${Math.random()}`);
        sessionStorage.setItem('sesion_id', session);
        const send = (tipo, extra = {}) => navigator.sendBeacon('/Seguir/api/eventos.php', JSON.stringify({ tipo, visitante_id: decodeURIComponent(visitor[1]), sesion_id: session, pagina: location.pathname, ...extra }));
        send('vista');
        document.addEventListener('click', (event) => {
            const element = event.target.closest('a, button, [data-track]'); if (!element || element.closest('.aviso-cookies')) return;
            send('clic', { elemento: (element.dataset.track || element.textContent.trim()).slice(0, 60) || element.tagName });
        });
    } else if (!consent) {
        const notice = document.createElement('aside'); notice.className = 'aviso-cookies';
        notice.style.cssText = 'position:fixed;z-index:1000;left:16px;right:16px;bottom:16px;max-width:620px;margin:auto;padding:16px;background:#fff;border:1px solid #e9e3d9;border-radius:16px;box-shadow:0 10px 32px #0002;color:#28352e';
        const text = document.createElement('p'); text.textContent = 'Usamos cookies opcionales para medir visitas y clics. No registramos lo que escribes en formularios.';
        const buttons = document.createElement('div'); buttons.style.cssText = 'display:flex;gap:10px;justify-content:flex-end';
        [['si', 'Aceptar'], ['no', 'Rechazar']].forEach(([value, label]) => { const button = document.createElement('button'); button.type = 'button'; button.textContent = label; button.dataset.respuesta = value; button.style.cssText = 'padding:9px 14px;border:0;border-radius:9px;background:#365b45;color:white;font-weight:700'; button.addEventListener('click', () => { document.cookie = `analitica=${value}; max-age=15552000; path=/; SameSite=Lax`; notice.remove(); location.reload(); }); buttons.append(button); });
        notice.append(text, buttons); document.body.append(notice);
    }
})();
