<?php
/**
 * Script temporal para reenviar instrucciones de registro a 4 contactos
 *
 * USO: Ejecutar UNA sola vez desde el navegador y luego ELIMINAR
 * URL:  https://v2.regalos.purranque.info/reenviar-instrucciones.php?token=regalos2026correos
 */

// ── Protección por token ──
$TOKEN = 'regalos2026correos';
if (($_GET['token'] ?? '') !== $TOKEN) {
    http_response_code(403);
    die('Acceso denegado. Usa ?token=...');
}

// ── Bootstrap mínimo ──
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/Core/Database.php';
require_once BASE_PATH . '/app/Models/Configuracion.php';
require_once BASE_PATH . '/app/Models/AdminUsuario.php';
require_once BASE_PATH . '/app/Services/Mailer.php';
require_once BASE_PATH . '/app/Services/Notification.php';

// ── Contactos ──
$contactos = [
    ['nombre' => 'Ximena Ojeda Barria',   'email' => 'ximenaojedameme@gmail.com'],
    ['nombre' => 'Lorena Pérez Paredes',   'email' => 'lorenaperezparedes5@gmail.com'],
    ['nombre' => 'Vanesa Torres Mora',     'email' => 'betytorresmora@gmail.com'],
    ['nombre' => 'Jorge Levet',            'email' => 'jorgelevetr@hotmail.com'],
];

echo "<pre style='font-family:monospace;font-size:14px;'>\n";
echo "=== Reenvío de instrucciones de registro ===\n\n";

$ok = 0;
$fail = 0;
$mailer = new \App\Services\Mailer();

foreach ($contactos as $c) {
    $result = $mailer->send(
        $c['email'],
        'Cómo registrar tu comercio — ' . SITE_NAME,
        'contacto-instrucciones-registro',
        [
            'datos' => [
                'nombre' => $c['nombre'],
                'email'  => $c['email'],
                'asunto' => 'Instrucciones de registro',
            ],
            'registroUrl' => SITE_URL . '/registrar-comercio',
        ]
    );

    if ($result) {
        echo "OK   -> {$c['nombre']} &lt;{$c['email']}&gt;\n";
        $ok++;
    } else {
        echo "FAIL -> {$c['nombre']} &lt;{$c['email']}&gt;\n";
        $fail++;
    }

    sleep(2);
}

echo "\nResultado: {$ok} enviados, {$fail} fallidos\n";
echo "\n=== ELIMINAR este archivo del servidor ===\n";
echo "rm " . __FILE__ . "\n";
echo "</pre>";
