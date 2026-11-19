<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../LOGIN/conexion.php';
$chatApi = rtrim((string) getenv('CHAT_API_URL'), '/');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="chat-api-url" content="<?= e($chatApi) ?>">
  <title>Chat temporal | Patitas con Hogar</title>
  <style>
    :root{--ink:#28352e;--green:#365b45;--cream:#fff8ef;--line:#e9e3d9;--soft:#f4eee4;--muted:#66736a}
    *{box-sizing:border-box}body{margin:0;background:var(--cream);color:var(--ink);font:16px/1.55 system-ui,sans-serif}.wrap{width:min(1000px,calc(100% - 32px));margin:24px auto}
    .chat{height:min(75vh,720px);min-height:500px;background:#fff;border:1px solid var(--line);border-radius:18px;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 12px 32px #26382c12}.chat-head{padding:18px 22px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;gap:12px}.chat-head h2{font-size:1.2rem;margin:0}.status{font-size:.85rem;color:var(--green)}.messages{flex:1;overflow:auto;padding:20px;display:flex;flex-direction:column;gap:12px}.bubble{max-width:80%;padding:11px 15px;border-radius:16px;background:var(--soft);align-self:flex-start;overflow-wrap:anywhere;white-space:pre-wrap}.bubble.mine{background:var(--green);color:#fff;align-self:flex-end}.bubble small{display:block;font-size:.72rem;opacity:.76;margin-top:3px}.empty{color:var(--muted);text-align:center;margin:auto}.composer{border-top:1px solid var(--line);padding:14px;display:flex;gap:10px}.composer input{min-width:0;flex:1;padding:12px;border:1px solid #c9d2ca;border-radius:10px;font:inherit}.composer button{background:var(--green);color:#fff;border:0;border-radius:9px;padding:11px 18px;font:700 1rem system-ui;cursor:pointer}.hint{color:var(--muted);font-size:.92rem}.back{color:var(--green);font-weight:700}.connection{font-size:.88rem;color:var(--muted)}@media(max-width:560px){.wrap{margin:16px auto}.chat{min-height:65vh}.chat-head{padding:14px}.composer{padding:10px}.composer button{padding:10px}}
  </style>
</head>
<body>
<?php require __DIR__ . '/menu.php'; ?>
<main class="wrap">
  <a class="back" href="/Foto/public/index.html">← Volver al inicio</a>
  <h1>Chat temporal de atención</h1>
  <p class="hint">Los mensajes de este chat se comparten en tiempo real con las demás personas que abran esta sala y se eliminan al pasar 24 horas. No compartas datos personales o sensibles.</p>
  <p class="connection" id="connection" role="status">Conectando al servicio del chat…</p>
  <section class="chat" aria-label="Chat temporal compartido">
    <header class="chat-head"><h2>🐾 Patitas con Hogar</h2><span class="status" id="chat-status">● Conectando</span></header>
    <div class="messages" id="messages" aria-live="polite" aria-relevant="additions"></div>
    <form class="composer" id="form"><input id="message" maxlength="500" placeholder="Escribe un mensaje…" aria-label="Escribe un mensaje" autocomplete="off" required><button id="send" type="submit">Enviar</button></form>
  </section>
</main>
<script src="/Seguir/chat.js?v=1" defer></script>
<script src="/Seguir/tracker.js" defer></script>
</body></html>
