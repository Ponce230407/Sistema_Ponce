(() => {
  const api = (document.querySelector('meta[name="chat-api-url"]')?.content || '').replace(/\/$/, '');
  const box = document.getElementById('messages');
  const form = document.getElementById('form');
  const field = document.getElementById('message');
  const send = document.getElementById('send');
  const connection = document.getElementById('connection');
  const status = document.getElementById('chat-status');
  if (!api) {
    connection.textContent = 'El chat compartido todavía no está configurado. Configura CHAT_API_URL en el servidor.';
    status.textContent = '● No conectado';
    send.disabled = true;
    return;
  }
  let lastId = 0;
  let loading = false;
  const senderId = localStorage.getItem('patitas-chat-sender') || crypto.randomUUID();
  localStorage.setItem('patitas-chat-sender', senderId);
  function draw(messages) {
    if (!messages.length && !box.children.length) {
      const p = document.createElement('p');
      p.className = 'empty';
      p.textContent = 'Aún no hay mensajes. Escribe para iniciar la conversación con la comunidad.';
      box.append(p);
      return;
    }
    box.querySelector('.empty')?.remove();
    for (const item of messages) {
      if (item.id <= lastId) continue;
      const bubble = document.createElement('div');
      bubble.className = 'bubble' + (item.sender_id === senderId ? ' mine' : '');
      bubble.textContent = item.text;
      const time = document.createElement('small');
      time.textContent = `${item.sender_id === senderId ? 'Tú' : 'Visitante'} · ${new Date(item.created_at).toLocaleTimeString('es-MX', {hour:'2-digit', minute:'2-digit'})}`;
      bubble.append(time);
      box.append(bubble);
      lastId = item.id;
    }
    box.scrollTop = box.scrollHeight;
  }
  async function refresh() {
    if (loading) return;
    loading = true;
    try {
      const response = await fetch(`${api}/messages?after=${lastId}`, {headers:{Accept:'application/json'}, cache:'no-store'});
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const result = await response.json();
      draw(result.messages || []);
      connection.textContent = 'Chat compartido conectado. Los mensajes nuevos aparecen automáticamente.';
      status.textContent = '● Disponible';
    } catch (error) {
      connection.textContent = 'No se pudo conectar al chat. Reintentando…';
      status.textContent = '● Reconectando';
    } finally { loading = false; }
  }
  form.addEventListener('submit', async event => {
    event.preventDefault();
    const text = field.value.trim();
    if (!text) return;
    send.disabled = true;
    try {
      const response = await fetch(`${api}/messages`, {
        method:'POST', headers:{'Content-Type':'application/json', Accept:'application/json'},
        body:JSON.stringify({text, sender_id:senderId})
      });
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || 'No se pudo enviar el mensaje.');
      field.value = '';
      await refresh();
      field.focus();
    } catch (error) {
      connection.textContent = error.message || 'No se pudo enviar el mensaje. Inténtalo otra vez.';
    } finally { send.disabled = false; }
  });
  refresh();
  window.setInterval(refresh, 2000);
})();
