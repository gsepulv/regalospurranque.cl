<?php
/**
 * Script temporal para configurar SMTP en producción
 *
 * USO: Ejecutar UNA sola vez desde el navegador y luego ELIMINAR
 * URL:  https://v2.regalos.purranque.info/setup-smtp-produccion.php
 *
 * Seguridad: requiere token en la URL para ejecutar
 * Ejemplo:   setup-smtp-produccion.php?token=regalos2026smtp
 */

// ── Protección por token ──
$TOKEN = 'regalos2026smtp';
if (($_GET['token'] ?? '') !== $TOKEN) {
    http_response_code(403);
    die('Acceso denegado. Usa ?token=...');
}

// ── Cargar configuración de BD ──
require_once __DIR__ . '/config/database.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// ── INSERTs SMTP ──
$configs = [
    // Nuevas claves SMTP
    ['mail_driver',       'smtp',                        'notificaciones'],
    ['mail_host',         'smtp.gmail.com',              'notificaciones'],
    ['mail_port',         '587',                         'notificaciones'],
    ['mail_encryption',   'tls',                         'notificaciones'],
    ['mail_username',     'regalospurranque@gmail.com',  'notificaciones'],
    ['mail_from_name',    'Regalos Purranque',           'notificaciones'],
    ['mail_from_address', 'regalospurranque@gmail.com',  'notificaciones'],
    // Actualizar claves existentes
    ['email_from',        'regalospurranque@gmail.com',  'notificaciones'],
    ['email_reply_to',    'regalospurranque@gmail.com',  'notificaciones'],
    ['sitio_email',       'regalospurranque@gmail.com',  'general'],
];

$sql = "INSERT INTO configuracion (clave, valor, grupo) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE valor = VALUES(valor)";
$stmt = $pdo->prepare($sql);

echo "<pre style='font-family:monospace;font-size:14px;'>\n";
echo "=== Configuración SMTP para producción ===\n\n";

$ok = 0;
foreach ($configs as [$clave, $valor, $grupo]) {
    try {
        $stmt->execute([$clave, $valor, $grupo]);
        echo "OK   {$clave} = {$valor}\n";
        $ok++;
    } catch (PDOException $e) {
        echo "FAIL {$clave}: {$e->getMessage()}\n";
    }
}

echo "\n{$ok}/" . count($configs) . " claves configuradas.\n";

// ── Verificar ──
echo "\n=== Verificación ===\n";
$rows = $pdo->query(
    "SELECT clave, valor FROM configuracion
     WHERE clave LIKE 'mail_%' OR clave IN ('email_from','email_reply_to','sitio_email')
     ORDER BY clave"
)->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    echo "{$row['clave']} = {$row['valor']}\n";
}

echo "\n=== Recordatorio ===\n";
echo "1. Crear config/mail.php en el servidor con la contraseña SMTP\n";
echo "2. ELIMINAR este archivo del servidor: rm setup-smtp-produccion.php\n";
echo "</pre>";
