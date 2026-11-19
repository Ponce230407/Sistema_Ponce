// Navegación, historias de la comunidad, consentimiento de cookies y analítica básica.
(() => {
    const consentKey = 'analitica';
    const readConsent = () => document.cookie.match(/(?:^|; )analitica=([^;]*)/);
    const consent = readConsent();
    if (!consent) {
        const style = document.createElement('style');
        style.textContent = `.aviso-cookies{position:fixed!important;z-index:10000!important;left:0!important;right:0!important;bottom:0!important;width:100%!important;max-width:none!important;margin:0!important;padding:18px 28px!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:22px!important;background:#291117!important;border:2px solid #f3b51b!important;border-radius:15px 15px 0 0!important;color:#fff!important;font:16px/1.5 system-ui,sans-serif!important;box-shadow:0 -8px 30px #0003!important}.aviso-cookies p{margin:0!important;max-width:760px!important}.aviso-cookies .cookie-actions{display:flex;gap:12px;flex-shrink:0}.aviso-cookies button{border:2px solid #fff;background:transparent;color:#fff;border-radius:12px;padding:12px 24px;font:700 16px system-ui;cursor:pointer}.aviso-cookies button[data-respuesta="si"]{background:#f3b51b;border-color:#f3b51b;color:#291117}.aviso-cookies button:hover{filter:brightness(1.08)}@media(max-width:650px){.aviso-cookies{flex-direction:column;align-items:stretch;padding:16px!important}.aviso-cookies .cookie-actions{justify-content:stretch}.aviso-cookies button{flex:1;padding:10px 12px}}`;
        document.head.append(style);
        const notice = document.createElement('aside');
        notice.className = 'aviso-cookies';
        notice.setAttribute('aria-label', 'Preferencias de cookies');
        const text = document.createElement('p');
        text.textContent = 'Usamos cookies opcionales para medir cómo se usa este sitio (páginas, clics y tiempo). No registramos lo que escribes en los formularios.';
        const actions = document.createElement('div'); actions.className = 'cookie-actions';
        [['si', 'Aceptar'], ['no', 'Rechazar']].forEach(([value, label]) => {
            const button = document.createElement('button'); button.type = 'button'; button.textContent = label; button.dataset.respuesta = value;
            button.addEventListener('click', () => {
                document.cookie = `${consentKey}=${value}; max-age=31536000; path=/; SameSite=Lax`;
                notice.remove();
                if (value === 'si') startAnalytics();
            });
            actions.append(button);
        });
        notice.append(text, actions); document.body.append(notice);
    } else if (decodeURIComponent(consent[1]) === 'si') startAnalytics();

    function startAnalytics() {
        const existing = document.cookie.match(/(?:^|; )visitante_id=([^;]*)/);
        let visitor = existing && existing[1];
        if (!visitor) { visitor = window.crypto?.randomUUID?.() || `${Date.now()}-${Math.random()}`; document.cookie = `visitante_id=${encodeURIComponent(visitor)}; max-age=31536000; path=/; SameSite=Lax`; }
        const session = sessionStorage.getItem('sesion_id') || (window.crypto?.randomUUID?.() || `${Date.now()}-${Math.random()}`); sessionStorage.setItem('sesion_id', session);
        const send = (tipo, extra = {}) => navigator.sendBeacon('/Seguir/api/eventos.php', new Blob([JSON.stringify({ tipo, visitante_id: decodeURIComponent(visitor), sesion_id: session, pagina: location.pathname, ...extra })], { type: 'application/json' }));
        send('vista');
        document.addEventListener('click', (event) => { const element = event.target.closest('a, button, [data-track]'); if (!element || element.closest('.aviso-cookies')) return; send('clic', { elemento: (element.getAttribute('data-track') || element.textContent || element.getAttribute('aria-label') || element.tagName).trim().slice(0, 100) }); });
        let seconds = 0; const timer = setInterval(() => { if (!document.hidden) seconds++; }, 1000);
        window.addEventListener('pagehide', () => { clearInterval(timer); if (seconds > 0) send('tiempo', { segundos: seconds }); });
    }

    fetch('/Foto/public/estado-sesion.php', { credentials: 'same-origin', headers: { Accept: 'application/json' } }).then((response) => response.ok ? response.json() : null).then((state) => {
        if (!state || !state.autenticado) return;
        document.querySelectorAll('[data-session-indicator]').forEach((badge) => { badge.textContent = state.nombre ? `Sesión iniciada · ${state.nombre}` : 'Sesión iniciada'; badge.classList.add('is-visible'); });
        document.querySelectorAll('[data-login-link]').forEach((link) => { link.href = state.es_admin ? '/Seguir/admin.php' : '/Seguir/bienvenida.php'; link.textContent = state.es_admin ? '🛡️ Panel de administración' : '🐾 Mi cuenta'; });
        document.querySelectorAll('[data-register-link]').forEach((link) => link.remove());
    }).catch(() => {});

    const community = document.getElementById('publicaciones');
    if (community) {
        const heading = community.parentElement.querySelector('h2'); const intro = community.parentElement.querySelector('.sub');
        if (heading) heading.textContent = 'Momentos felices de la comunidad';
        if (intro) intro.textContent = 'Comparte recuerdos bonitos con tus mascotas, mira los de otras personas y deja un corazón.';
        community.innerHTML = '<p class="empty">Cargando momentos felices…</p>';
        const style = document.createElement('style'); style.textContent = '.community-card{max-width:420px;margin:12px auto;padding-bottom:12px}.community-card img{display:block;width:100%;height:300px;object-fit:cover;object-position:center;image-rendering:auto}.community-card h3,.community-card p{margin-left:18px;margin-right:18px}.community-card h3{margin-top:12px;font-size:1.25rem}.community-card p{font-size:.92rem}.community-actions{display:flex;gap:10px;margin:12px 18px}.community-actions button,.community-share{border:0;border-radius:999px;background:#365b45;color:white;padding:10px 16px;font-weight:700;cursor:pointer;text-decoration:none}.community-share{display:inline-block;margin:8px 0 18px}.community-actions .delete-button{background:#a52a21}.community-actions button:disabled{opacity:.6}@media(max-width:600px){.community-card img{height:auto;max-height:340px;aspect-ratio:4/3}}'; document.head.append(style);
        const share = document.createElement('a'); share.className = 'community-share'; share.href = '/Seguir/publicar-perro.php'; share.textContent = '📸 Comparte un momento feliz'; community.before(share);
        let csrf = '';
        const loadExperiences = async () => {
            try {
                const response = await fetch('/Foto/public/experiencias.php', { credentials: 'same-origin', headers: { Accept: 'application/json' }, cache: 'no-store' }); if (!response.ok) throw new Error();
                const data = await response.json(); csrf = data.csrf || csrf; community.replaceChildren();
                if (!Array.isArray(data.experiencias) || data.experiencias.length === 0) { const empty = document.createElement('p'); empty.className = 'empty'; empty.textContent = 'Aún no hay momentos compartidos. ¡Comparte el primero!'; community.append(empty); return; }
                data.experiencias.forEach((item) => {
                    const card = document.createElement('article'); card.className = 'community-card'; const image = document.createElement('img'); image.src = item.foto; image.alt = `Foto de ${item.nombre}`; image.loading = 'lazy';
                    const title = document.createElement('h3'); title.textContent = item.nombre; const story = document.createElement('p'); story.textContent = item.historia; const actions = document.createElement('div'); actions.className = 'community-actions';
                    const heart = document.createElement('button'); heart.type = 'button'; heart.className = 'heart-button'; heart.setAttribute('aria-pressed', Number(item.me_gusta) === 1 ? 'true' : 'false'); heart.textContent = `${Number(item.me_gusta) === 1 ? '💚' : '🤍'} ${item.corazones}`;
                    heart.addEventListener('click', async () => { heart.disabled = true; try { const body = new URLSearchParams({ accion: 'corazon', id: item.id, csrf_token: csrf }); const result = await fetch('/Foto/public/experiencias.php', { method: 'POST', credentials: 'same-origin', body }); if (!result.ok) throw new Error(); const state = await result.json(); heart.setAttribute('aria-pressed', state.me_gusta ? 'true' : 'false'); heart.textContent = `${state.me_gusta ? '💚' : '🤍'} ${state.corazones}`; } catch { alert('No se pudo actualizar el corazón.'); } finally { heart.disabled = false; } }); actions.append(heart);
                    if (Number(item.puede_borrar) === 1) { const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'delete-button'; remove.textContent = '🗑️ Borrar'; remove.addEventListener('click', async () => { if (!confirm('¿Seguro que quieres borrar esta foto?')) return; remove.disabled = true; try { const body = new URLSearchParams({ accion: 'borrar', id: item.id, csrf_token: csrf }); const result = await fetch('/Foto/public/experiencias.php', { method: 'POST', credentials: 'same-origin', body }); if (!result.ok) throw new Error(); loadExperiences(); } catch { alert('No se pudo borrar la publicación.'); remove.disabled = false; } }); actions.append(remove); }
                    card.append(image, title, story, actions); community.append(card);
                });
            } catch { community.replaceChildren(); const error = document.createElement('p'); error.className = 'empty'; error.textContent = 'Por el momento no se pudieron cargar los momentos felices.'; community.append(error); }
        };
        loadExperiences();
    }
})();
