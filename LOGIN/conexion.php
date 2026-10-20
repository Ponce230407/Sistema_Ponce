$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'railway';
$port = getenv('DB_PORT') ?: '3306';
$pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4", $user, $password,
               [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
session_start();
function e($texto) { return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); }
