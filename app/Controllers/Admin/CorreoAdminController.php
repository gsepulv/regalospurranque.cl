<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Categoria;
use App\Models\FechaEspecial;
use App\Models\MensajeContacto;
use App\Services\Mailer;

class CorreoAdminController extends Controller
{
    /**
     * GET /admin/correos/enviar — Formulario de envío de correo
     */
    public function enviar(): void
    {
        $mensajeId = (int) $this->request->get('mensaje_id', 0);
        $mensaje   = null;

        if ($mensajeId > 0) {
            $mensaje = MensajeContacto::find($mensajeId);
            if ($mensaje && !$mensaje['leido']) {
                MensajeContacto::marcarLeido($mensajeId);
            }
        }

        $categorias   = Categoria::getActiveForSelect();
        $proximaFecha = FechaEspecial::getProximaConFecha();

        $this->render('admin/correos/enviar', [
            'title'         => 'Enviar Correo — ' . SITE_NAME,
            'mensaje'       => $mensaje,
            'categorias'    => $categorias,
            'proximaFecha'  => $proximaFecha,
        ]);
    }

    /**
     * POST /admin/correos/enviar — Enviar correo
     */
    public function send(): void
    {
        $validator = $this->validate($_POST, [
            'para'      => 'required|email',
            'asunto'    => 'required|max:255',
            'contenido' => 'required',
        ]);

        if ($validator->fails()) {
            $this->back([
                'error'  => 'Corrige los errores del formulario.',
                'errors' => $validator->errors(),
                'old'    => $_POST,
            ]);
            return;
        }

        $data = $validator->validated();

        $to      = trim($data['para']);
        $subject = trim($data['asunto']);
        $html    = sanitize_html($data['contenido']);

        $mailer = new Mailer();
        $sent   = $mailer->sendHtml($to, $subject, $html, 'respuesta-manual', [
            'nombre'     => trim($_POST['nombre'] ?? ''),
            'comercio'   => trim($_POST['comercio'] ?? ''),
            'mensaje_id' => (int) ($_POST['mensaje_id'] ?? 0),
        ]);

        // Marcar mensaje original como respondido
        $mensajeId = (int) ($_POST['mensaje_id'] ?? 0);
        if ($sent && $mensajeId > 0) {
            MensajeContacto::marcarRespondido($mensajeId);
        }

        $this->log('correos', 'enviar', 'correo', 0, "Correo enviado a {$to}: {$subject}");

        if ($sent) {
            $redirectUrl = $mensajeId > 0 ? '/admin/contacto' : '/admin/correos/enviar';
            $this->redirect($redirectUrl, ['success' => "Correo enviado exitosamente a {$to}"]);
        } else {
            $this->back([
                'error' => 'No se pudo enviar el correo. Revisa la configuración de email.',
                'old'   => $_POST,
            ]);
        }
    }

    /**
     * POST /admin/correos/preview — Vista previa del email (AJAX)
     */
    public function preview(): void
    {
        $html = $_POST['contenido'] ?? '';
        $html = sanitize_html($html);

        $mailer = new Mailer();
        // Use reflection to access wrapInLayout, or just replicate layout wrapping
        $layoutPath = BASE_PATH . '/views/emails/layout.php';
        if (file_exists($layoutPath)) {
            $siteName     = SITE_NAME;
            $siteUrl      = SITE_URL;
            $year         = date('Y');
            $emailContent = $html;

            ob_start();
            include $layoutPath;
            $html = ob_get_clean();
        }

        $this->json(['html' => $html]);
    }
}
