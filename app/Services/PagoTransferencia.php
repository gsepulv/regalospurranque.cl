<?php
namespace App\Services;

use App\Models\Configuracion;

/**
 * Pago por transferencia bancaria (manual)
 * Funcional desde el día 1 — el comerciante sube comprobante, el admin verifica
 */
class PagoTransferencia extends PasarelaPago
{
    protected string $metodo = 'transferencia';

    public function iniciarPago(array $data): array
    {
        // No hay redirect — el pago es manual
        return ['ok' => true, 'redirect_url' => null, 'token' => null, 'error' => null];
    }

    public function verificarPago(string $token): array
    {
        // Siempre requiere revisión manual del admin
        return ['ok' => true, 'estado' => 'manual', 'transaccion_id' => null];
    }

    public function callbackPago(array $request): array
    {
        // No aplica para transferencia manual
        return ['ok' => false, 'renovacion_id' => null, 'estado' => 'no_aplica'];
    }

    /**
     * Obtener datos bancarios para mostrar al comerciante
     */
    public function getDatosBancarios(): array
    {
        $claves = ['nombre', 'cuenta', 'tipo', 'rut', 'email'];
        $datos = [];

        foreach ($claves as $c) {
            try {
                $conf = Configuracion::getByKey("renovacion_banco_{$c}");
                $datos[$c] = $conf['valor'] ?? '';
            } catch (\Throwable $e) {
                $datos[$c] = '';
            }
        }

        return $datos;
    }
}
