<?php
namespace App\Models;

use App\Core\Database;

class NurturingConfig
{
    public static function getAll(): array
    {
        $rows = Database::getInstance()->fetchAll(
            "SELECT * FROM nurturing_config ORDER BY grupo, id"
        );
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['grupo']][] = $row;
        }
        return $grouped;
    }

    public static function get(string $clave, ?string $default = null): ?string
    {
        $row = Database::getInstance()->fetch(
            "SELECT valor FROM nurturing_config WHERE clave = ?", [$clave]
        );
        return $row['valor'] ?? $default;
    }

    public static function set(string $clave, string $valor): void
    {
        Database::getInstance()->execute(
            "UPDATE nurturing_config SET valor = ? WHERE clave = ?",
            [$valor, $clave]
        );
    }

    public static function setMultiple(array $datos): void
    {
        $db = Database::getInstance();
        foreach ($datos as $clave => $valor) {
            $db->execute(
                "UPDATE nurturing_config SET valor = ? WHERE clave = ?",
                [$valor, $clave]
            );
        }
    }

    public static function isServicioActivo(): bool
    {
        return (bool) (int) self::get('servicio_activo', '0');
    }

    public static function getHoraEnvio(): string
    {
        return self::get('hora_envio', '10:00');
    }

    public static function getMaxRecordatorios(): int
    {
        return (int) self::get('max_recordatorios', '4');
    }

    public static function getIntervaloDias(): int
    {
        return (int) self::get('intervalo_dias', '7');
    }

    public static function getEstadosExcluidos(): array
    {
        $val = self::get('estados_excluidos', 'convertido,descartado');
        return array_filter(array_map('trim', explode(',', $val)));
    }

    public static function getDiasEsperaPrimera(): int
    {
        return (int) self::get('dias_espera_primera', '7');
    }
}
