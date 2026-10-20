const express = require('express');
const path = require('path');
const app = express();
const PORT = process.env.PORT || 3000;

// Servir archivos estáticos desde la carpeta 'public'
app.use(express.static(path.join(__dirname, 'public')));

// Endpoint para entregar la API Key de Google Maps de forma segura
app.get('/api/maps-config', (req, res) => {
  const apiKey = process.env.GOOGLE_MAPS_API_KEY;
  if (!apiKey) {
    return res.status(500).json({ error: 'La API Key de Google Maps no está configurada.' });
  }
  res.json({ apiKey });
});

// Ruta principal para servir ubicacion.html (o la que corresponda)
app.get('/ubicacion', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'ubicacion.html'));
});

app.listen(PORT, () => {
  console.log(`Servidor corriendo en el puerto ${PORT}`);
});
