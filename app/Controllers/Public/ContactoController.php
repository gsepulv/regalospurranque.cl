<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\MensajeContacto;
use App\Services\Captcha;
use App\Services\Notification;

class ContactoController extends Controller
{
    public function index(): void
    {
        $this->render('public/contacto', [
            'title'       => 'Registra tu Comercio en Purranque · ' . SITE_NAME,
            'description' => 'Registra tu comercio en el directorio de Purranque, Chile. Contacto, consultas y soporte para comerciantes.',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('/')],
                ['label' => 'Contacto'],
            ],
        ]);
    }

    public function send(): void
    {
        // Validar Turnstile
        if (!Captcha::verify($_POST['cf-turnstile-response'] ?? null)) {
            $this->back([
                'error' => 'Verificación anti-bot fallida. Intenta nuevamente.',
                'old'   => $_POST,
            ]);
            return;
        }

        $validator = $this->validate($_POST, [
            'nombre'  => 'required|min:2|max:100',
            'email'   => 'required|email|max:255',
            'asunto'  => 'required|min:3|max:200',
            'mensaje' => 'required|min:10|max:5000',
        ]);

        if ($validator->fails()) {
            $this->back([
                'error'  => 'Por favor corrige los errores del formulario.',
                'errors' => $validator->errors(),
                'old'    => $_POST,
            ]);
            return;
        }

        $data = $validator->validated();

        // Guardar mensaje en BD
        MensajeContacto::create([
            'nombre'  => $data['nombre'],
            'email'   => $data['email'],
            'asunto'  => $data['asunto'],
            'mensaje' => $data['mensaje'],
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        // Notificar a admins
        Notification::nuevoMensajeContacto($data);

        $this->redirect('/contacto', [
            'success' => 'Tu mensaje ha sido enviado correctamente. Te responderemos a la brevedad.',
        ]);
    }
}
