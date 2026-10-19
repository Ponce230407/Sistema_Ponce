const express = require('express');
const path = require('path');
const mysql = require('mysql2');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

// Railway supplies MYSQL*; DB_* also works for Render and local deployments.
const db = mysql.createConnection({
  host: process.env.DB_HOST || process.env.MYSQLHOST || 'localhost',
  user: process.env.DB_USER || process.env.MYSQLUSER || 'root',
  password: process.env.DB_PASSWORD || process.env.MYSQLPASSWORD || '',
  database: process.env.DB_NAME || process.env.MYSQLDATABASE || 'railway',
  port: process.env.DB_PORT || process.env.MYSQLPORT || 3306
});

db.connect((err) => {
  if (err) {
    console.error('Error al conectar a la base de datos MySQL:', err);
  } else {
    console.log('Conectado exitosamente a la base de datos MySQL');
  }
});

// Ruta para procesar la encuesta
app.post('/api/encuesta', (req, res) => {
  const { nombre_completo, p1_profesores, p2_alumnos, p3_instalaciones, p4_examenes, p5_comida } = req.body;

  const sql = `INSERT INTO respuestas_encuesta
               (nombre_completo, p1_profesores, p2_alumnos, p3_instalaciones, p4_examenes, p5_comida)
               VALUES (?, ?, ?, ?, ?, ?)`;

  db.query(sql, [nombre_completo || 'Encuesta web', p1_profesores, p2_alumnos, p3_instalaciones, p4_examenes, p5_comida], (err) => {
    if (err) {
      console.error('Error al guardar respuestas en BD:', err);
      return res.status(500).send('Error en el servidor al guardar la encuesta.');
    }
    // Redirección al regalo tras guardar
    res.redirect('/regalo.html');
  });
});

app.listen(PORT, () => {
  console.log(`Servidor local corriendo en http://localhost:${PORT}`);
});