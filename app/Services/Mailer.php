<?php
namespace App\Services;

// PHPMailer (incluido manualmente, sin Composer)
require_once BASE_PATH . '/lib/PHPMailer/Exception.php';
require_once BASE_PATH . '/lib/PHPMailer/PHPMailer.php';
require_once BASE_PATH . '/lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Servicio de envío de emails
 * Soporta SMTP (PHPMailer) con fallback a mail() de PHP
 */
class Mailer
{
    private string $fromEmail;
    private string $fromName;
    private string $replyTo;
    private array $configCache = [];

    public function __construct()
    {
        $this->fromName  = $this->getConfig('mail_from_name', SITE_NAME);
        $this->fromEmail = $this->getConfig('mail_from_address',
            $this->getConfig('email_from', 'no-reply@' . $this->getDomain())
        );
        $this->replyTo = $this->getConfig('email_reply_to', $this->fromEmail);
    }

    /**
     * Enviar email con template
     */
    public function send(string $to, string $subject, string $template, array $data = []): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $body = $this->renderTemplate($template, $data);
        if ($body === null) {
            $this->logError("Template no encontrado: {$template}");
            return false;
        }

        $driver = $this->getConfig('mail_driver', 'mail');

        $sent = false;
        $method = 'mail()';

        // Intentar SMTP si está configurado
        if ($driver === 'smtp') {
            $sent = $this->sendSmtp($to, $subject, $body);
            $method = 'smtp';

            // Fallback a mail() si SMTP falla
            if (!$sent) {
                $this->logError("SMTP falló para {$to}, intentando fallback con mail()");
                $sent = $this->sendNative($to, $subject, $body);
                $method = $sent ? 'mail()-fallback' : 'fallido';
            }
        } else {
            $sent = $this->sendNative($to, $subject, $body);
        }

        $this->logNotification($to, $subject, $template, $sent, $data, $method);

        return $sent;
    }

    /**
     * Enviar email con contenido HTML directo (sin template de archivo)
     */
    public function sendHtml(string $to, string $subject, string $htmlContent, string $templateLabel = 'respuesta-manual', array $extraData = []): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $body = $this->wrapInLayout($htmlContent);
        if ($body === null) {
            $body = $htmlContent;
        }

        $driver = $this->getConfig('mail_driver', 'mail');

        $sent = false;
        $method = 'mail()';

        if ($driver === 'smtp') {
            $sent = $this->sendSmtp($to, $subject, $body);
            $method = 'smtp';

            if (!$sent) {
                $this->logError("SMTP falló para {$to}, intentando fallback con mail()");
                $sent = $this->sendNative($to, $subject, $body);
                $method = $sent ? 'mail()-fallback' : 'fallido';
            }
        } else {
            $sent = $this->sendNative($to, $subject, $body);
        }

        $this->logNotification($to, $subject, $templateLabel, $sent, $extraData, $method);

        return $sent;
    }

    /**
     * Envolver HTML en el layout de email
     */
    private function wrapInLayout(string $htmlContent): ?string
    {
        $layoutPath = BASE_PATH . '/views/emails/layout.php';
        if (!file_exists($layoutPath)) {
            return null;
        }

        $siteName     = SITE_NAME;
        $siteUrl      = SITE_URL;
        $year         = date('Y');
        $emailContent = $htmlContent;

        ob_start();
        include $layoutPath;
        return ob_get_clean();
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
     * Enviar vía SMTP con PHPMailer
     */
    private function sendSmtp(string $to, string $subject, string $body): bool
    {
        try {
            $smtpConfig = $this->loadSmtpConfig();
            if (empty($smtpConfig['password'])) {
                $this->logError("SMTP: contraseña no configurada en config/mail.php");
                return false;
            }

            $mail = new PHPMailer(true);

            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host       = $smtpConfig['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpConfig['username'];
            $mail->Password   = $smtpConfig['password'];
            $mail->SMTPSecure = $smtpConfig['encryption'];
            $mail->Port       = (int) $smtpConfig['port'];
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';

            // Remitente y destinatario
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addReplyTo($this->replyTo, $this->fromName);
            $mail->addAddress($to);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $this->htmlToPlainText($body);

            $mail->send();
            return true;

        } catch (PHPMailerException $e) {
            $this->logError("SMTP Error [{$to}]: " . $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            $this->logError("SMTP Error inesperado [{$to}]: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar con mail() nativo de PHP (multipart con text/plain)
     */
    private function sendNative(string $to, string $subject, string $body): bool
    {
        $boundary = 'boundary_' . bin2hex(random_bytes(16));
        $plainText = $this->htmlToPlainText($body);

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: {$this->replyTo}\r\n";
        $headers .= "X-Mailer: " . SITE_NAME . "/" . APP_VERSION . "\r\n";

        $message  = "--{$boundary}\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $message .= chunk_split(base64_encode($plainText)) . "\r\n";
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $message .= chunk_split(base64_encode($body)) . "\r\n";
        $message .= "--{$boundary}--\r\n";

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        return @mail($to, $encodedSubject, $message, $headers);
    }

    /**
     * Cargar configuración SMTP desde BD + archivo local
     */
    private function loadSmtpConfig(): array
    {
        $config = [
            'host'       => $this->getConfig('mail_host', 'smtp.gmail.com'),
            'port'       => $this->getConfig('mail_port', '587'),
            'encryption' => $this->getConfig('mail_encryption', 'tls'),
            'username'   => $this->getConfig('mail_username', ''),
            'password'   => '',
        ];

        // La contraseña se lee del archivo local (fuera del repo)
        $mailConfigFile = BASE_PATH . '/config/mail.php';
        if (file_exists($mailConfigFile)) {
            $fileConfig = include $mailConfigFile;
            if (is_array($fileConfig) && !empty($fileConfig['smtp_password'])) {
                $config['password'] = $fileConfig['smtp_password'];
            }
        }

        return $config;
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
     * Convertir HTML a texto plano para AltBody
     */
    private function htmlToPlainText(string $html): string
    {
        $text = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $text = preg_replace('/<\/p>/i', "\n\n", $text);
        $text = preg_replace('/<\/h[1-6]>/i', "\n\n", $text);
        $text = preg_replace('/<\/tr>/i', "\n", $text);
        $text = preg_replace('/<\/li>/i', "\n", $text);
        $text = preg_replace('/<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', '$2 ($1)', $text);
        $text = preg_replace('/<hr[^>]*>/i', "\n---\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
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
        if (isset($this->configCache[$key])) {
            return $this->configCache[$key];
        }

        try {
            $db = \App\Core\Database::getInstance();
            $row = $db->fetch(
                "SELECT valor FROM configuracion WHERE clave = ?",
                [$key]
            );
            $value = $row['valor'] ?? $default;
        } catch (\Throwable $e) {
            $value = $default;
        }

        $this->configCache[$key] = $value;
        return $value;
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
    private function logNotification(string $to, string $subject, string $template, bool $sent, array $data, string $method = ''): void
    {
        try {
            $db = \App\Core\Database::getInstance();
            $db->insert('notificaciones_log', [
                'destinatario' => $to,
                'asunto'       => mb_substr($subject, 0, 255),
                'template'     => $template,
                'estado'       => $sent ? 'enviado' : 'fallido',
                'site_id'      => \App\Services\SiteManager::getInstance()->getSiteId(),
                'datos'        => json_encode(
                    array_merge($data, $method ? ['_metodo' => $method] : []),
                    JSON_UNESCAPED_UNICODE
                ),
            ]);
        } catch (\Throwable $e) {
            $this->logError("Error registrando notificación: " . $e->getMessage());
        }
    }

    /**
     * Registrar error en log
     */
    private function logError(string $message): void
    {
        $logDir = BASE_PATH . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $line = "[" . date('Y-m-d H:i:s') . "] {$message}\n";
        file_put_contents($logDir . '/mailer.log', $line, FILE_APPEND | LOCK_EX);
    }
}
