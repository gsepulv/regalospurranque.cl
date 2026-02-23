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
    public static function nuevaResena(array $resena, array $comercio): void
    {
        if (!self::isEventEnabled('notif_nueva_resena')) return;

        self::mailer()->sendToAdmins(
            "Nueva reseña para {$comercio['nombre']}",
            'nueva-resena',
            [
                'resena'   => $resena,
                'comercio' => $comercio,
            ]
        );
    }

    /**
     * Reseña aprobada (notifica al autor si dejó email)
     */
    public static function resenaAprobada(array $resena): void
    {
        if (!self::isEventEnabled('notif_resena_aprobada')) return;
        if (empty($resena['email_autor'])) return;

        $comercio = self::getComercio($resena['comercio_id']);

        self::mailer()->send(
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
    public static function resenaRechazada(array $resena): void
    {
        if (!self::isEventEnabled('notif_resena_rechazada')) return;
        if (empty($resena['email_autor'])) return;

        $comercio = self::getComercio($resena['comercio_id']);

        self::mailer()->send(
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
    public static function resenaRespondida(array $resena, string $respuesta): void
    {
        if (!self::isEventEnabled('notif_resena_respuesta')) return;
        if (empty($resena['email_autor'])) return;

        $comercio = self::getComercio($resena['comercio_id']);

        self::mailer()->send(
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
       public static function resenaReportada(array $resena, array $reporte): void
    {
        if (!self::isEventEnabled('notif_reporte_resena')) return;

        $comercio = self::getComercio($resena['comercio_id']);

        self::mailer()->sendToAdmins(
            "Reseña reportada en {$comercio['nombre']}",
            'reporte-resena',
            [
                'resena'   => $resena,
                'comercio' => $comercio,
                'reporte'  => $reporte,
            ]
        );
    }

    /**
     * Nuevo comercio creado (notifica a admins)
     */
    public static function nuevoComercio(array $comercio): void
    {
        if (!self::isEventEnabled('notif_nuevo_comercio')) return;

        self::mailer()->sendToAdmins(
            "Nuevo comercio: {$comercio['nombre']}",
            'nuevo-comercio',
            ['comercio' => $comercio]
        );
    }

    /**
     * Bienvenida a comercio (al email del comercio)
     */
    public static function bienvenidaComercio(array $comercio): void
    {
        if (!self::isEventEnabled('notif_bienvenida_comercio')) return;
        if (empty($comercio['email'])) return;

        self::mailer()->send(
            $comercio['email'],
            "Bienvenido a " . SITE_NAME,
            'comercio-bienvenida',
            ['comercio' => $comercio]
        );
    }

    /**
     * Backup completado (notifica a admins)
     */
    public static function backupCompletado(string $tipo, string $archivo, string $tamano): void
    {
        if (!self::isEventEnabled('notif_backup')) return;

        self::mailer()->sendToAdmins(
            "Backup {$tipo} completado",
            'backup-completado',
            [
                'tipo'    => $tipo,
                'archivo' => $archivo,
                'tamano'  => $tamano,
            ]
        );
    }

    /**
     * Error de sistema (notifica a admins)
     */
    public static function errorSistema(string $mensaje, string $detalle = ''): void
    {
        if (!self::isEventEnabled('notif_error_sistema')) return;

        self::mailer()->sendToAdmins(
            "Error en " . SITE_NAME,
            'error-sistema',
            [
                'mensaje' => $mensaje,
                'detalle' => $detalle,
                'fecha'   => date('d/m/Y H:i:s'),
            ]
        );
    }

    /**
     * Resumen semanal (notifica a admins)
     */
    public static function resumenSemanal(array $stats): void
    {
        if (!self::isEventEnabled('notif_resumen_semanal')) return;

        self::mailer()->sendToAdmins(
            "Resumen semanal — " . SITE_NAME,
            'resumen-semanal',
            ['stats' => $stats]
        );
    }

    /**
     * Fecha próxima (notifica a admins)
     */
    public static function fechaProxima(array $fecha): void
    {
        if (!self::isEventEnabled('notif_fecha_proxima')) return;

        self::mailer()->sendToAdmins(
            "Fecha próxima: {$fecha['nombre']}",
            'fecha-proxima',
            ['fecha' => $fecha]
        );
    }

    /**
     * Nuevo mensaje de contacto (notifica a admins)
     */
    public static function nuevoMensajeContacto(array $datos): void
    {
        if (!self::isEventEnabled('notif_contacto')) return;

        self::mailer()->sendToAdmins(
            "Nuevo mensaje de contacto: {$datos['asunto']}",
            'contacto-mensaje',
            ['datos' => $datos]
        );
    }

    /**
     * Acuse de recibo de contacto (al remitente)
     */
    public static function acuseReciboContacto(array $datos): void
    {
        if (!self::isEventEnabled('notif_acuse_contacto')) return;

        self::mailer()->send(
            $datos['email'],
            "Hemos recibido tu mensaje — " . SITE_NAME,
            'contacto-acuse',
            ['datos' => $datos]
        );
    }

    /**
     * Instrucciones de registro (al remitente que consulta sobre registro)
     */
    public static function instruccionesRegistro(array $datos): void
    {
        if (!self::isEventEnabled('notif_instrucciones_registro')) return;

        self::mailer()->send(
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
    public static function comercioAprobado(array $comercio): void
    {
        if (!self::isEventEnabled('notif_comercio_aprobado')) return;
        if (empty($comercio['registrado_por'])) return;

        $usuario = \App\Models\AdminUsuario::find((int)$comercio['registrado_por']);
        if (!$usuario || empty($usuario['email'])) return;

        self::mailer()->send(
            $usuario['email'],
            "Tu comercio ha sido aprobado — " . SITE_NAME,
            'comercio-aprobado',
            ['comercio' => $comercio]
        );
    }

    /**
     * Comercio rechazado (al comerciante que lo registró)
     */
    public static function comercioRechazado(array $comercio, string $motivo = ''): void
    {
        if (!self::isEventEnabled('notif_comercio_rechazado')) return;
        if (empty($comercio['registrado_por'])) return;

        $usuario = \App\Models\AdminUsuario::find((int)$comercio['registrado_por']);
        if (!$usuario || empty($usuario['email'])) return;

        self::mailer()->send(
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
    public static function solicitudArcoAdmin(array $solicitud): void
    {
        self::mailer()->sendToAdmins(
            "Solicitud ARCO #{$solicitud['id']} — {$solicitud['tipo_texto']}",
            'arco-admin',
            ['solicitud' => $solicitud]
        );
    }

    /**
     * Solicitud ARCO — confirmación al solicitante
     */
    public static function solicitudArcoConfirmacion(string $email, array $solicitud): void
    {
        self::mailer()->send(
            $email,
            "Solicitud recibida #{$solicitud['id']} — " . SITE_NAME,
            'arco-confirmacion',
            ['solicitud' => $solicitud]
        );
    }

    /**
     * Registro de comercio por comerciante (notifica a admins)
     */
    public static function registroComercianteAdmin(int $comercioId, string $nombreComercio): void
    {
        if (!self::isEventEnabled('notif_nuevo_comercio')) return;

        self::mailer()->sendToAdmins(
            "Nuevo comercio registrado: {$nombreComercio}",
            'registro-comerciante-admin',
            [
                'comercioId'     => $comercioId,
                'nombreComercio' => $nombreComercio,
            ]
        );
    }

    /**
     * Cambios pendientes de comerciante (notifica a admins)
     */
    public static function cambiosPendientesAdmin(int $comercioId, string $nombreComercio): void
    {
        self::mailer()->sendToAdmins(
            "Cambios pendientes: {$nombreComercio}",
            'cambios-pendientes-admin',
            [
                'comercioId'     => $comercioId,
                'nombreComercio' => $nombreComercio,
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
