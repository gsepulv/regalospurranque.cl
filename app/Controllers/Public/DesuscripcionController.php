<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Database;

class DesuscripcionController extends Controller
{
    public function confirmar(string $token): void
    {
        // Validar token: solo hex, largo 64
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            $this->render('desuscripcion/error', [
                'title' => 'Enlace no valido — ' . SITE_NAME,
            ], 'public');
            return;
        }

        $db = Database::getInstance();
        $msg = $db->fetch(
            "SELECT id, email, desuscrito FROM mensajes_contacto WHERE token_desuscripcion = ?",
            [$token]
        );

        if (!$msg) {
            $this->render('desuscripcion/error', [
                'title' => 'Enlace no valido — ' . SITE_NAME,
            ], 'public');
            return;
        }

        if (!$msg['desuscrito']) {
            $db->update('mensajes_contacto', [
                'desuscrito'              => 1,
                'desuscrito_at'           => date('Y-m-d H:i:s'),
                'proximo_recordatorio_at' => null,
            ], 'id = ?', [$msg['id']]);

            $db->insert('admin_log', [
                'usuario_id'     => 0,
                'usuario_nombre' => 'Sistema',
                'modulo'         => 'nurturing',
                'accion'         => 'desuscripcion',
                'entidad_tipo'   => 'mensaje_contacto',
                'entidad_id'     => $msg['id'],
                'detalle'        => "Contacto desuscrito: {$msg['email']}",
                'ip'             => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'user_agent'     => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
        }

        $this->render('desuscripcion/confirmacion', [
            'title' => 'Desuscripcion confirmada — ' . SITE_NAME,
        ], 'public');
    }
}
