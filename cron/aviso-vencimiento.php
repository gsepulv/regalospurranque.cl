<?php
/**
 * Cron: Avisos de vencimiento de plan
 * Envía email al comerciante a 7, 3 y 1 día(s) del vencimiento.
 * Envía resumen diario al admin con comercios próximos a vencer (7 días).
 * Ejecutar vía cPanel: una vez al día a las 08:00
 * Comando: php /home/purranque/v2.regalos.purranque.info/cron/aviso-vencimiento.php
 */

// Solo ejecución desde CLI
if (php_sapi_name() !== 'cli') {
    die('Solo CLI');
}

// Bootstrap mínimo
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/app.php';
require BASE_PATH . '/config/database.php';

spl_autoload_register(function (string $class) {
    $path = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    $path = str_replace('/App/', '/app/', $path);
    if (file_exists($path)) require_once $path;
});

use App\Core\Database;
use App\Services\Mailer;

// Lock file para evitar ejecuciones simultáneas
$lockFile = BASE_PATH . '/storage/logs/cron-aviso-vencimiento.lock';
$lockFp = fopen($lockFile, 'c');
if (!$lockFp || !flock($lockFp, LOCK_EX | LOCK_NB)) {
    echo "[" . date('Y-m-d H:i:s') . "] Otra instancia ya está ejecutándose. Saliendo.\n";
    exit(0);
}

// Helper para log persistente
function cronLog(string $msg): void {
    $line = "[" . date('Y-m-d H:i:s') . "] {$msg}\n";
    echo $line;
    $logDir = BASE_PATH . '/storage/logs';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    file_put_contents($logDir . '/cron-aviso-vencimiento.log', $line, FILE_APPEND | LOCK_EX);
}

try {
    $db = Database::getInstance();
    $mailer = new Mailer();
    $hoy = date('Y-m-d');
    $diasAviso = [7, 3, 1];

    cronLog("Iniciando avisos de vencimiento...");

    // ── Avisos individuales al comerciante ────────────────────
    $totalEnviados = 0;

    foreach ($diasAviso as $dias) {
        $fechaVencimiento = date('Y-m-d', strtotime("+{$dias} days"));

        // Comercios activos (no freemium) cuyo plan vence en exactamente $dias días
        $comercios = $db->fetchAll(
            "SELECT c.id, c.nombre, c.slug, c.plan, c.plan_fin, c.registrado_por,
                    u.email as usuario_email, u.nombre as usuario_nombre
             FROM comercios c
             INNER JOIN admin_usuarios u ON c.registrado_por = u.id
             WHERE c.activo = 1
               AND c.plan != 'freemium'
               AND c.plan_fin = ?
               AND u.activo = 1
               AND u.email IS NOT NULL",
            [$fechaVencimiento]
        );

        foreach ($comercios as $c) {
            $templateKey = "aviso-vencimiento-{$dias}d";

            // Verificar si ya se envió este aviso hoy para este comercio
            $yaEnviado = $db->fetch(
                "SELECT id FROM notificaciones_log
                 WHERE template = ? AND destinatario = ? AND DATE(created_at) = ?",
                [$templateKey, $c['usuario_email'], $hoy]
            );

            if ($yaEnviado) {
                cronLog("Ya enviado: {$c['nombre']} ({$dias}d) a {$c['usuario_email']}. Omitiendo.");
                continue;
            }

            $enviado = $mailer->send(
                $c['usuario_email'],
                "Tu plan vence en {$dias} día" . ($dias > 1 ? 's' : '') . " — " . SITE_NAME,
                'aviso-vencimiento',
                [
                    'comercio'       => $c,
                    'diasRestantes'  => $dias,
                    'fechaVencimiento' => $c['plan_fin'],
                    'usuario_nombre' => $c['usuario_nombre'],
                ]
            );

            if ($enviado) {
                $totalEnviados++;
                cronLog("Aviso {$dias}d enviado: {$c['nombre']} -> {$c['usuario_email']}");
            } else {
                cronLog("FALLO aviso {$dias}d: {$c['nombre']} -> {$c['usuario_email']}");
            }
        }
    }

    cronLog("Avisos a comerciantes: {$totalEnviados} enviados.");

    // ── Resumen diario al admin ──────────────────────────────
    $en7Dias = date('Y-m-d', strtotime('+7 days'));

    $porVencer = $db->fetchAll(
        "SELECT c.id, c.nombre, c.slug, c.plan, c.plan_fin,
                DATEDIFF(c.plan_fin, CURDATE()) as dias_restantes
         FROM comercios c
         WHERE c.activo = 1
           AND c.plan != 'freemium'
           AND c.plan_fin IS NOT NULL
           AND c.plan_fin BETWEEN CURDATE() AND ?
         ORDER BY c.plan_fin ASC",
        [$en7Dias]
    );

    if (!empty($porVencer)) {
        // Solo enviar resumen si no se envió hoy
        $resumenEnviado = $db->fetch(
            "SELECT id FROM notificaciones_log
             WHERE template = 'aviso-vencimiento-resumen-admin' AND DATE(created_at) = ?",
            [$hoy]
        );

        if (!$resumenEnviado) {
            $enviados = $mailer->sendToAdmins(
                count($porVencer) . " comercio(s) por vencer en los próximos 7 días — " . SITE_NAME,
                'aviso-vencimiento-admin',
                ['comercios' => $porVencer]
            );
            cronLog("Resumen admin enviado a {$enviados} admin(s). Comercios por vencer: " . count($porVencer));
        } else {
            cronLog("Resumen admin ya enviado hoy. Omitiendo.");
        }
    } else {
        cronLog("No hay comercios por vencer en los próximos 7 días.");
    }

    // Registrar en admin_log
    $db->insert('admin_log', [
        'usuario_id'     => null,
        'usuario_nombre' => 'cron',
        'modulo'         => 'comercios',
        'accion'         => 'aviso_vencimiento',
        'entidad_tipo'   => 'comercio',
        'entidad_id'     => null,
        'detalle'        => "Avisos vencimiento: {$totalEnviados} enviados. Por vencer (7d): " . count($porVencer ?? []),
        'ip'             => '127.0.0.1',
    ]);

    cronLog("Proceso completado.");

} catch (\Throwable $e) {
    cronLog("ERROR: " . $e->getMessage());
    exit(1);
} finally {
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    @unlink($lockFile);
}
