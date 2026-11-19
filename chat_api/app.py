from datetime import datetime, timedelta, timezone
from threading import Lock
from flask import Flask, jsonify, request

app = Flask(__name__)
app.config['MAX_CONTENT_LENGTH'] = 8 * 1024
messages = []
next_id = 1
lock = Lock()
RETENTION = timedelta(hours=24)
MAX_MESSAGES = 500


def cors(response):
    # The room is intentionally public and does not use cookies or credentials.
    # A wildcard origin lets the PHP site call this separately deployed service.
    response.headers['Access-Control-Allow-Origin'] = '*'
    response.headers['Access-Control-Allow-Methods'] = 'GET, POST, OPTIONS'
    response.headers['Access-Control-Allow-Headers'] = 'Content-Type, Accept'
    response.headers['Cache-Control'] = 'no-store'
    return response


@app.after_request
def add_cors(response):
    return cors(response)


@app.route('/health', methods=['GET', 'OPTIONS'])
def health():
    if request.method == 'OPTIONS':
        return ('', 204)
    return jsonify({'status': 'ok', 'service': 'patitas-chat-rest', 'storage': 'memory-only'})


@app.route('/messages', methods=['GET', 'POST', 'OPTIONS'])
def chat_messages():
    global next_id
    if request.method == 'OPTIONS':
        return ('', 204)
    now = datetime.now(timezone.utc)
    with lock:
        messages[:] = [item for item in messages if now - item['_created'] < RETENTION]
        if request.method == 'GET':
            try:
                after = max(0, int(request.args.get('after', '0')))
            except ValueError:
                return jsonify({'error': 'El parámetro after debe ser un entero.'}), 400
            visible = [
                {key: value for key, value in item.items() if not key.startswith('_')}
                for item in messages if item['id'] > after
            ]
            return jsonify({'messages': visible})

        payload = request.get_json(silent=True) or {}
        text = payload.get('text')
        sender_id = payload.get('sender_id')
        if not isinstance(text, str) or not text.strip() or len(text.strip()) > 500:
            return jsonify({'error': 'El mensaje debe tener entre 1 y 500 caracteres.'}), 400
        if not isinstance(sender_id, str) or not sender_id.strip() or len(sender_id) > 80:
            return jsonify({'error': 'Falta un identificador de usuario válido.'}), 400
        item = {
            'id': next_id,
            'text': text.strip(),
            'sender_id': sender_id.strip(),
            'created_at': now.isoformat(timespec='seconds'),
            '_created': now,
        }
        next_id += 1
        messages.append(item)
        if len(messages) > MAX_MESSAGES:
            del messages[:-MAX_MESSAGES]
        return jsonify({key: value for key, value in item.items() if not key.startswith('_')}), 201


if __name__ == '__main__':
    from os import environ
    app.run(host='0.0.0.0', port=int(environ.get('PORT', '5000')))
