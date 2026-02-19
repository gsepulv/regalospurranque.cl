<?php
namespace App\Services;

use App\Core\Database;

/**
 * Logger de acciones administrativas
 * Registra toda acción CRUD en admin_log
 */
class Logger
{
    /**
     * Registrar una acción
     */
    public static function log(
        string $modulo,
        string $accion,
        string $entidadTipo,
        int    $entidadId,
        string $detalle = '',
        ?array $datosAntes = null,
        ?array $datosDespues = null
    ): void {
        try {
            $admin = $_SESSION['admin'] ?? null;

            Database::getInstance()->insert('admin_log', [
                'usuario_id'     => $admin['id'] ?? 0,
                'usuario_nombre' => $admin['nombre'] ?? 'Sistema',
                'modulo'         => $modulo,
                'accion'         => $accion,
                'entidad_tipo'   => $entidadTipo,
                'entidad_id'     => $entidadId,
                'detalle'        => $detalle,
                'datos_antes'    => $datosAntes ? json_encode($datosAntes, JSON_UNESCAPED_UNICODE) : null,
                'datos_despues'  => $datosDespues ? json_encode($datosDespues, JSON_UNESCAPED_UNICODE) : null,
                'ip'             => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // No interrumpir la ejecución si falla el log
            // Escribir a archivo como fallback
            $logLine = sprintf(
                "[%s] %s | %s.%s | %s #%d | %s\n",
                date('Y-m-d H:i:s'),
                $admin['nombre'] ?? 'Sistema',
                $modulo,
                $accion,
                $entidadTipo,
                $entidadId,
                $detalle
            );
            @file_put_contents(
                LOG_PATH . '/admin_' . date('Y-m') . '.log',
                $logLine,
                FILE_APPEND | LOCK_EX
            );
        }
    }
}
