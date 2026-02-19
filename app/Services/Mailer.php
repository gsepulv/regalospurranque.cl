<?php
namespace App\Services;

/**
 * Servicio de envío de emails usando PHP mail()
 * Compatible con hosting compartido (HostGator)
 */
class Mailer
{
    private string $fromEmail;
    private string $fromName;
    private string $replyTo;

    public function __construct()
    {
        $this->fromName  = SITE_NAME;
        $this->fromEmail = $this->getConfig('email_from', 'no-reply@' . $this->getDomain());
        $this->replyTo   = $this->getConfig('email_reply_to', $this->fromEmail);
    }

    /**
     * Enviar email con template
     */
    public function send(string $to, string $subject, string $template, array $data = []): bool
    {
        // Verificar que las notificaciones estén habilitadas
        if (!$this->isEnabled()) {
            return false;
        }

        $body = $this->renderTemplate($template, $data);
        if ($body === null) {
            $this->logError("Template no encontrado: {$template}");
            return false;
        }

        $headers = $this->buildHeaders();

        $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        $sent = @mail($to, $subject, $body, $headers);

        // Registrar en log
        $this->logNotification($to, $subject, $template, $sent, $data);

        return $sent;
    }

    /**
     * Enviar email a todos los admins
     */
    public function sendToAdmins(string $subject, string $template, array $data = []): int
    {
        $db = \App\Core\Database::getInstance();
        $admins = $db->fetchAll(
            "SELECT email FROM admin_usuarios WHERE rol = 'admin' AND activo = 1"
        );

        $sent = 0;
        foreach ($admins as $admin) {
            if ($this->send($admin['email'], $subject, $template, $data)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Renderizar template con layout
     */
    private function renderTemplate(string $template, array $data): ?string
    {
        $templatePath = BASE_PATH . '/views/emails/' . $template . '.php';
        if (!file_exists($templatePath)) {
            return null;
        }

        // Agregar variables globales
        $data['siteName']  = SITE_NAME;
        $data['siteUrl']   = SITE_URL;
        $data['year']      = date('Y');
        $data['logoUrl']   = SITE_URL . '/assets/img/config/logo.png';

        // Renderizar contenido del template
        extract($data, EXTR_SKIP);
        ob_start();
        include $templatePath;
        $content = ob_get_clean();

        // Envolver en layout
        $layoutPath = BASE_PATH . '/views/emails/layout.php';
        if (file_exists($layoutPath)) {
            $data['emailContent'] = $content;
            extract($data, EXTR_SKIP);
            ob_start();
            include $layoutPath;
            $content = ob_get_clean();
        }

        return $content;
    }

    /**
     * Construir headers del email
     */
    private function buildHeaders(): string
    {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: {$this->replyTo}\r\n";
        $headers .= "X-Mailer: " . SITE_NAME . "/" . APP_VERSION . "\r\n";

        return $headers;
    }

    /**
     * Verificar si las notificaciones están habilitadas
     */
    private function isEnabled(): bool
    {
        return (bool) $this->getConfig('notificaciones_activas', '1');
    }

    /**
     * Obtener configuración de la BD
     */
    private function getConfig(string $key, string $default = ''): string
    {
        try {
            $db = \App\Core\Database::getInstance();
            $row = $db->fetch(
                "SELECT valor FROM configuracion WHERE clave = ?",
                [$key]
            );
            return $row['valor'] ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Obtener dominio del SITE_URL
     */
    private function getDomain(): string
    {
        return parse_url(SITE_URL, PHP_URL_HOST) ?? 'localhost';
    }

    /**
     * Registrar notificación en BD
     */
    private function logNotification(string $to, string $subject, string $template, bool $sent, array $data): void
    {
        try {
            $db = \App\Core\Database::getInstance();
            $db->insert('notificaciones_log', [
                'destinatario' => $to,
                'asunto'       => mb_substr($subject, 0, 255),
                'template'     => $template,
                'estado'       => $sent ? 'enviado' : 'fallido',
                'datos'        => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Throwable $e) {
            $this->logError("Error registrando notificación: " . $e->getMessage());
        }
    }

    /**
     * Registrar error en log de PHP
     */
    private function logError(string $message): void
    {
        $logFile = BASE_PATH . '/storage/logs/mailer.log';
        $line = "[" . date('Y-m-d H:i:s') . "] {$message}\n";
        @file_put_contents($logFile, $line, FILE_APPEND);
    }
}
