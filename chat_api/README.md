# Chat REST temporal sin base de datos

Este pequeño servicio Flask ofrece una sala compartida por REST en memoria: `GET /messages?after=<id>`, `POST /messages` y `GET /health`. Los navegadores consultan mensajes nuevos cada dos segundos. El proceso descarta mensajes después de 24 horas y limita la sala a 500 mensajes. No crea archivos ni usa base de datos; reiniciar o escalar el proceso borra o separa la memoria, por lo que se configura una sola instancia.

## Ejecutar localmente

```bash
cd chat_api
python -m venv .venv
# Windows: .venv\\Scripts\\activate
# macOS/Linux: source .venv/bin/activate
pip install -r requirements.txt
python app.py
```

Comprueba `http://localhost:5000/health`. Para verificar comunicación entre usuarios, abre dos sesiones/navegadores en `/Seguir/chat.php` y envía mensajes desde uno; deben aparecer en ambos. El PHP de desarrollo debe tener `CHAT_API_URL=http://localhost:5000`.

También se puede probar con curl:

```bash
curl http://localhost:5000/health
curl -H 'Content-Type: application/json' -d '{"text":"Hola desde usuario 1","sender_id":"usuario-1"}' http://localhost:5000/messages
curl 'http://localhost:5000/messages?after=0'
```

## Render

`render.yaml` define un servicio Python separado llamado `patitas-chat-api`. Configura `CHAT_API_URL` en el servicio PHP con la URL pública que Render asigne al servicio Flask (por ejemplo, `https://patitas-chat-api.onrender.com`). El navegador llama directamente a ese servicio; el API permite CORS público porque no usa cookies ni datos de autenticación. La sala es pública y no se debe usar para datos personales.

En planes con suspensión por inactividad, el primer arranque puede tardar. La memoria es efímera: el historial se pierde al reiniciar, desplegar o escalar el servicio; este comportamiento es parte del requisito de no usar base de datos.
