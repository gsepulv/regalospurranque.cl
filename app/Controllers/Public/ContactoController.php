<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Services\Captcha;
use App\Services\Notification;

class ContactoController extends Controller
{
    public function index(): void
    {
        $this->render('public/contacto', [
            'title'       => 'Contacto — ' . SITE_NAME,
            'description' => 'Contáctanos. Estamos en Purranque, Región de Los Lagos, Chile.',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => url('/')],
                ['label' => 'Contacto'],
            ],
        ]);
    }

    public function send(): void
    {
        // Validar hCaptcha
        if (!Captcha::verify($_POST['h-captcha-response'] ?? null)) {
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
        $this->db->insert('mensajes_contacto', [
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
