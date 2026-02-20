<?php
/**
 * API: Registrar consentimiento de cookies
 * Endpoint: POST /api/consentimiento.php
 * 
 * INTEGRACIÓN:
 * Copiar este archivo a: /ruta-del-proyecto/api/consentimiento.php
 * Ajustar la ruta del require de conexión a BD según tu estructura.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Ajustar según tu estructura de conexión a BD
// require_once __DIR__ . '/../includes/db.php';
// require_once __DIR__ . '/../config/database.php';

// --- CONEXIÓN (ajustar credenciales) ---
$host = 'localhost';
$dbname = 'regalospurranque'; // Ajustar
$user = 'root';               // Ajustar
$pass = '';                    // Ajustar

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}
// --- FIN CONEXIÓN ---

$tipo = $_POST['tipo'] ?? '';
$tipos_validos = ['cookies_esenciales', 'cookies_todas'];

if (!in_array($tipo, $tipos_validos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo inválido']);
    exit;
}

$session_id = session_id() ?: 'sin_sesion';
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512);

try {
    $stmt = $pdo->prepare(
        "INSERT INTO consentimientos (session_id, ip, tipo, user_agent) 
         VALUES (:session_id, :ip, :tipo, :user_agent)"
    );
    $stmt->execute([
        ':session_id' => $session_id,
        ':ip'         => $ip,
        ':tipo'       => $tipo,
        ':user_agent' => $user_agent,
    ]);

    echo json_encode(['ok' => true, 'tipo' => $tipo]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar consentimiento']);
}
