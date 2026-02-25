<?php
namespace App\Services;

use App\Models\Configuracion;

/**
 * Clase base abstracta para pasarelas de pago
 * Implementaciones: PagoTransferencia (día 1), PagoFlow, PagoWebpay, PagoMercadoPago (stubs)
 */
abstract class PasarelaPago
{
    protected string $metodo;

    /**
     * ¿Está activo este método de pago?
     */
    public function isActive(): bool
    {
        try {
            $conf = Configuracion::getByKey("pago_{$this->metodo}_activo");
            return ($conf['valor'] ?? '0') === '1';
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Iniciar un pago
     * @return array ['ok' => bool, 'redirect_url' => ?string, 'token' => ?string, 'error' => ?string]
     */
    abstract public function iniciarPago(array $data): array;

    /**
     * Verificar estado de un pago
     * @return array ['ok' => bool, 'estado' => string, 'transaccion_id' => ?string]
     */
    abstract public function verificarPago(string $token): array;

    /**
     * Procesar callback de la pasarela
     * @return array ['ok' => bool, 'renovacion_id' => ?int, 'estado' => string]
     */
    abstract public function callbackPago(array $request): array;

    /**
     * Factory: crear instancia según método
     */
    public static function factory(string $metodo): self
    {
        return match ($metodo) {
            'transferencia' => new PagoTransferencia(),
            'flow'          => new PagoFlow(),
            'webpay'        => new PagoWebpay(),
            'mercadopago'   => new PagoMercadoPago(),
            default         => throw new \InvalidArgumentException("Método de pago no soportado: {$metodo}"),
        };
    }

    /**
     * Obtener métodos de pago activos
     */
    public static function getMetodosActivos(): array
    {
        $metodos = ['transferencia', 'efectivo', 'webpay', 'flow', 'mercadopago'];
        $activos = [];

        foreach ($metodos as $m) {
            try {
                $conf = Configuracion::getByKey("pago_{$m}_activo");
                if (($conf['valor'] ?? '0') === '1') {
                    $activos[] = $m;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $activos;
    }
}
