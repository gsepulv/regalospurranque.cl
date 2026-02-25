<?php
namespace App\Services;

/**
 * Pasarela Flow (stub — conectar cuando se contraten credenciales)
 */
class PagoFlow extends PasarelaPago
{
    protected string $metodo = 'flow';

    public function iniciarPago(array $data): array
    {
        return ['ok' => false, 'redirect_url' => null, 'token' => null, 'error' => 'Flow no está configurado aún.'];
    }

    public function verificarPago(string $token): array
    {
        return ['ok' => false, 'estado' => 'no_configurado', 'transaccion_id' => null];
    }

    public function callbackPago(array $request): array
    {
        return ['ok' => false, 'renovacion_id' => null, 'estado' => 'no_configurado'];
    }
}
