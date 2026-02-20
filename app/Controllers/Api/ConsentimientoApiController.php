<?php
namespace App\Controllers\Api;

use App\Core\Controller;

/**
 * API para registrar consentimiento de cookies (Ley 21.719)
 */
class ConsentimientoApiController extends Controller
{
    /**
     * POST /api/consentimiento
     */
    public function store(): void
    {
        $tipo = $_POST['tipo'] ?? '';
        $tipos_validos = ['cookies_esenciales', 'cookies_todas'];

        if (!in_array($tipo, $tipos_validos)) {
            $this->json(['error' => 'Tipo inválido'], 400);
            return;
        }

        $session_id = session_id() ?: 'sin_sesion';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $user_agent = mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512);

        $this->db->insert('consentimientos', [
            'session_id' => $session_id,
            'ip'         => $ip,
            'tipo'       => $tipo,
            'user_agent' => $user_agent,
        ]);

        $this->json(['ok' => true, 'tipo' => $tipo]);
    }
}
