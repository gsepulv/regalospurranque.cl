<?php
namespace App\Services;

/**
 * Orquestador de notificaciones
 * Centraliza el envío de emails según tipo de evento
 */
class Notification
{
    private static ?Mailer $mailer = null;

    private static function mailer(): Mailer
    {
        if (self::$mailer === null) {
            self::$mailer = new Mailer();
        }
        return self::$mailer;
    }

    /**
     * Nueva reseña creada (notifica a admins)
     */
    public static function nuevaResena(array $resena, array $comercio): bool
    {
        if (!self::isEventEnabled('notif_nueva_resena')) return false;

        return self::mailer()->sendToAdmins(
            "Nueva reseña para {$comercio['nombre']}",
            'nueva-resena',
            [
                'resena'   => $resena,
                'comercio' => $comercio,
            ]
        ) > 0;
    }

    /**
     * Reseña aprobada (notifica al autor si dejó email)
     */
    public static function resenaAprobada(array $resena): bool
    {
        if (!self::isEventEnabled('notif_resena_aprobada')) return false;
        if (empty($resena['email_autor'])) return false;

        $comercio = self::getComercio($resena['comercio_id']);

        return self::mailer()->send(
            $resena['email_autor'],
            "Tu reseña ha sido aprobada — " . SITE_NAME,
            'resena-aprobada',
            [
                'resena'   => $resena,
                'comercio' => $comercio,
            ]
        );
    }

    /**
     * Reseña rechazada (notifica al autor si dejó email)
     */
    public static function resenaRechazada(array $resena): bool
    {
        if (!self::isEventEnabled('notif_resena_rechazada')) return false;
        if (empty($resena['email_autor'])) return false;

        $comercio = self::getComercio($resena['comercio_id']);

        return self::mailer()->send(
            $resena['email_autor'],
            "Sobre tu reseña en " . SITE_NAME,
            'resena-rechazada',
            [
                'resena'   => $resena,
                'comercio' => $comercio,
            ]
        );
    }

    /**
     * Respuesta a reseña (notifica al autor si dejó email)
     */
    public static function resenaRespondida(array $resena, string $respuesta): bool
    {
        if (!self::isEventEnabled('notif_resena_respuesta')) return false;
        if (empty($resena['email_autor'])) return false;

        $comercio = self::getComercio($resena['comercio_id']);

        return self::mailer()->send(
            $resena['email_autor'],
            "Han respondido a tu reseña — " . SITE_NAME,
            'resena-respuesta',
            [
                'resena'    => $resena,
                'comercio'  => $comercio,
                'respuesta' => $respuesta,
            ]
        );
    }

    /**
     * Reseña reportada (notifica a admins)
     */
    public static function resenaReportada(array $resena, array $reporte): bool
    {
        if (!self::isEventEnabled('notif_reporte_resena')) return false;

        $comercio = self::getComercio($resena['comercio_id']);

        return self::mailer()->sendToAdmins(
            "Reseña reportada en {$comercio['nombre']}",
            'reporte-resena',
            [
                'resena'   => $resena,
                'comercio' => $comercio,
                'reporte'  => $reporte,
            ]
        ) > 0;
    }

    /**
     * Nuevo comercio creado (notifica a admins)
     */
    public static function nuevoComercio(array $comercio): bool
    {
        if (!self::isEventEnabled('notif_nuevo_comercio')) return false;

        return self::mailer()->sendToAdmins(
            "Nuevo comercio: {$comercio['nombre']}",
            'nuevo-comercio',
            ['comercio' => $comercio]
        ) > 0;
    }

    /**
     * Bienvenida a comercio (al email del comercio)
     */
    public static function bienvenidaComercio(array $comercio): bool
    {
        if (!self::isEventEnabled('notif_bienvenida_comercio')) return false;
        if (empty($comercio['email'])) return false;

        return self::mailer()->send(
            $comercio['email'],
            "Bienvenido a " . SITE_NAME,
            'comercio-bienvenida',
            ['comercio' => $comercio]
        );
    }

    /**
     * Backup completado (notifica a admins)
     */
    public static function backupCompletado(string $tipo, string $archivo, string $tamano): bool
    {
        if (!self::isEventEnabled('notif_backup')) return false;

        return self::mailer()->sendToAdmins(
            "Backup {$tipo} completado",
            'backup-completado',
            [
                'tipo'    => $tipo,
                'archivo' => $archivo,
                'tamano'  => $tamano,
            ]
        ) > 0;
    }

    /**
     * Error de sistema (notifica a admins)
     */
    public static function errorSistema(string $mensaje, string $detalle = ''): bool
    {
        if (!self::isEventEnabled('notif_error_sistema')) return false;

        return self::mailer()->sendToAdmins(
            "Error en " . SITE_NAME,
            'error-sistema',
            [
                'mensaje' => $mensaje,
                'detalle' => $detalle,
                'fecha'   => date('d/m/Y H:i:s'),
            ]
        ) > 0;
    }

    /**
     * Resumen semanal (notifica a admins)
     */
    public static function resumenSemanal(array $stats): bool
    {
        if (!self::isEventEnabled('notif_resumen_semanal')) return false;

        return self::mailer()->sendToAdmins(
            "Resumen semanal — " . SITE_NAME,
            'resumen-semanal',
            ['stats' => $stats]
        ) > 0;
    }

    /**
     * Fecha próxima (notifica a admins)
     */
    public static function fechaProxima(array $fecha): bool
    {
        if (!self::isEventEnabled('notif_fecha_proxima')) return false;

        return self::mailer()->sendToAdmins(
            "Fecha próxima: {$fecha['nombre']}",
            'fecha-proxima',
            ['fecha' => $fecha]
        ) > 0;
    }

    /**
     * Nuevo mensaje de contacto (notifica a admins)
     */
    public static function nuevoMensajeContacto(array $datos): bool
    {
        if (!self::isEventEnabled('notif_contacto')) return false;

        return self::mailer()->sendToAdmins(
            "Nuevo mensaje de contacto: {$datos['asunto']}",
            'contacto-mensaje',
            ['datos' => $datos]
        ) > 0;
    }

    /**
     * Acuse de recibo de contacto (al remitente)
     */
    public static function acuseReciboContacto(array $datos): bool
    {
        if (!self::isEventEnabled('notif_acuse_contacto')) return false;

        return self::mailer()->send(
            $datos['email'],
            "Hemos recibido tu mensaje — " . SITE_NAME,
            'contacto-acuse',
            ['datos' => $datos]
        );
    }

    /**
     * Instrucciones de registro (al remitente que consulta sobre registro)
     */
    public static function instruccionesRegistro(array $datos): bool
    {
        if (!self::isEventEnabled('notif_instrucciones_registro')) return false;

        return self::mailer()->send(
            $datos['email'],
            "Cómo registrar tu comercio — " . SITE_NAME,
            'contacto-instrucciones-registro',
            [
                'datos'       => $datos,
                'registroUrl' => SITE_URL . '/registrar-comercio',
            ]
        );
    }

    /**
     * Comercio aprobado (al comerciante que lo registró)
     */
    public static function comercioAprobado(array $comercio): bool
    {
        if (!self::isEventEnabled('notif_comercio_aprobado')) return false;
        if (empty($comercio['registrado_por'])) return false;

        $usuario = \App\Models\AdminUsuario::find((int)$comercio['registrado_por']);
        if (!$usuario || empty($usuario['email'])) return false;

        return self::mailer()->send(
            $usuario['email'],
            "Tu comercio ha sido aprobado — " . SITE_NAME,
            'comercio-aprobado',
            ['comercio' => $comercio]
        );
    }

    /**
     * Comercio rechazado (al comerciante que lo registró)
     */
    public static function comercioRechazado(array $comercio, string $motivo = ''): bool
    {
        if (!self::isEventEnabled('notif_comercio_rechazado')) return false;
        if (empty($comercio['registrado_por'])) return false;

        $usuario = \App\Models\AdminUsuario::find((int)$comercio['registrado_por']);
        if (!$usuario || empty($usuario['email'])) return false;

        return self::mailer()->send(
            $usuario['email'],
            "Sobre tu comercio en " . SITE_NAME,
            'comercio-rechazado',
            [
                'comercio' => $comercio,
                'motivo'   => $motivo,
            ]
        );
    }

    /**
     * Solicitud ARCO — notifica a admins
     */
    public static function solicitudArcoAdmin(array $solicitud): bool
    {
        return self::mailer()->sendToAdmins(
            "Solicitud ARCO #{$solicitud['id']} — {$solicitud['tipo_texto']}",
            'arco-admin',
            ['solicitud' => $solicitud]
        ) > 0;
    }

    /**
     * Solicitud ARCO — confirmación al solicitante
     */
    public static function solicitudArcoConfirmacion(string $email, array $solicitud): bool
    {
        return self::mailer()->send(
            $email,
            "Solicitud recibida #{$solicitud['id']} — " . SITE_NAME,
            'arco-confirmacion',
            ['solicitud' => $solicitud]
        );
    }

    /**
     * Registro de comercio por comerciante (notifica a admins)
     */
    public static function registroComercianteAdmin(int $comercioId, string $nombreComercio): bool
    {
        if (!self::isEventEnabled('notif_nuevo_comercio')) return false;

        return self::mailer()->sendToAdmins(
            "Nuevo comercio registrado: {$nombreComercio}",
            'registro-comerciante-admin',
            [
                'comercioId'     => $comercioId,
                'nombreComercio' => $nombreComercio,
            ]
        ) > 0;
    }

    /**
     * Cambios pendientes de comerciante (notifica a admins)
     */
    public static function cambiosPendientesAdmin(int $comercioId, string $nombreComercio): bool
    {
        return self::mailer()->sendToAdmins(
            "Cambios pendientes: {$nombreComercio}",
            'cambios-pendientes-admin',
            [
                'comercioId'     => $comercioId,
                'nombreComercio' => $nombreComercio,
            ]
        ) > 0;
    }

    // ══════════════════════════════════════════════════════════
    // RENOVACIONES
    // ══════════════════════════════════════════════════════════

    /**
     * Nueva solicitud de renovación (notifica a admins)
     */
    public static function renovacionNuevaAdmin(array $comercio, array $plan): bool
    {
        if (!self::isEventEnabled('notif_renovacion_nueva')) return false;

        return self::mailer()->sendToAdmins(
            "Solicitud de renovación: {$comercio['nombre']}",
            'renovacion-nueva-admin',
            [
                'comercio' => $comercio,
                'plan'     => $plan,
            ]
        ) > 0;
    }

    /**
     * Renovación aprobada (notifica al comerciante)
     */
    public static function renovacionAprobada(array $renovacion): bool
    {
        if (!self::isEventEnabled('notif_renovacion_aprobada')) return false;

        $usuario = \App\Models\AdminUsuario::find((int)$renovacion['usuario_id']);
        if (!$usuario || empty($usuario['email'])) return false;

        $comercio = self::getComercio($renovacion['comercio_id']);
        $plan = \App\Models\PlanConfig::findBySlug($renovacion['plan_solicitado']);

        return self::mailer()->send(
            $usuario['email'],
            "Tu renovación ha sido aprobada — " . SITE_NAME,
            'renovacion-aprobada',
            [
                'renovacion' => $renovacion,
                'comercio'   => $comercio,
                'plan'       => $plan,
                'usuario'    => $usuario,
            ]
        );
    }

    /**
     * Renovación rechazada (notifica al comerciante)
     */
    public static function renovacionRechazada(array $renovacion): bool
    {
        if (!self::isEventEnabled('notif_renovacion_rechazada')) return false;

        $usuario = \App\Models\AdminUsuario::find((int)$renovacion['usuario_id']);
        if (!$usuario || empty($usuario['email'])) return false;

        $comercio = self::getComercio($renovacion['comercio_id']);

        return self::mailer()->send(
            $usuario['email'],
            "Sobre tu solicitud de renovación — " . SITE_NAME,
            'renovacion-rechazada',
            [
                'renovacion' => $renovacion,
                'comercio'   => $comercio,
                'usuario'    => $usuario,
            ]
        );
    }

    /**
     * Email de prueba
     */
    public static function test(string $email): bool
    {
        return self::mailer()->send(
            $email,
            "Email de prueba — " . SITE_NAME,
            'test',
            ['email' => $email]
        );
    }

    /**
     * Verificar si un evento de notificación está habilitado
     */
    private static function isEventEnabled(string $key): bool
    {
        try {
            $db = \App\Core\Database::getInstance();
            $row = $db->fetch("SELECT valor FROM configuracion WHERE clave = ?", [$key]);
            return ($row['valor'] ?? '1') === '1';
        } catch (\Throwable $e) {
            return true; // Por defecto habilitado
        }
    }

    /**
     * Obtener datos de comercio por ID
     */
    private static function getComercio(int $id): array
    {
        $db = \App\Core\Database::getInstance();
        return $db->fetch("SELECT * FROM comercios WHERE id = ?", [$id]) ?? [];
    }
}
