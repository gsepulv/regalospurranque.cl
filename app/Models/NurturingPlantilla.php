<?php
namespace App\Models;

use App\Core\Database;

class NurturingPlantilla
{
    public static function getAll(): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM nurturing_plantillas ORDER BY numero ASC"
        );
    }

    public static function getById(int $id): ?array
    {
        return Database::getInstance()->fetch(
            "SELECT * FROM nurturing_plantillas WHERE id = ?", [$id]
        );
    }

    public static function getByNumero(int $numero): ?array
    {
        return Database::getInstance()->fetch(
            "SELECT * FROM nurturing_plantillas WHERE numero = ? AND activo = 1", [$numero]
        );
    }

    public static function crear(array $datos): int
    {
        $db = Database::getInstance();
        $max = $db->fetch("SELECT COALESCE(MAX(numero), 0) as m FROM nurturing_plantillas");
        $datos['numero'] = ((int) $max['m']) + 1;
        return $db->insert('nurturing_plantillas', $datos);
    }

    public static function actualizar(int $id, array $datos): void
    {
        Database::getInstance()->update('nurturing_plantillas', $datos, 'id = ?', [$id]);
    }

    public static function eliminar(int $id): bool
    {
        $db = Database::getInstance();
        $total = $db->fetch("SELECT COUNT(*) as c FROM nurturing_plantillas");
        if ((int) ($total['c'] ?? 0) <= 1) {
            return false;
        }

        $plantilla = self::getById($id);
        if (!$plantilla) {
            return false;
        }

        $db->execute("DELETE FROM nurturing_plantillas WHERE id = ?", [$id]);
        self::reordenarNumeros();
        return true;
    }

    public static function reordenar(array $ids): void
    {
        $db = Database::getInstance();
        foreach ($ids as $i => $id) {
            $db->execute(
                "UPDATE nurturing_plantillas SET numero = ? WHERE id = ?",
                [$i + 1, (int) $id]
            );
        }
    }

    public static function toggleActivo(int $id): void
    {
        Database::getInstance()->execute(
            "UPDATE nurturing_plantillas SET activo = 1 - activo WHERE id = ?", [$id]
        );
    }

    public static function count(): int
    {
        $r = Database::getInstance()->fetch(
            "SELECT COUNT(*) as c FROM nurturing_plantillas"
        );
        return (int) ($r['c'] ?? 0);
    }

    public static function preview(int $id): ?string
    {
        $plantilla = self::getById($id);
        if (!$plantilla) return null;

        $totalComercios = Database::getInstance()->count('comercios', 'activo = 1');

        $vars = [
            '{nombre}'           => 'Maria Gonzalez',
            '{email}'            => 'maria@ejemplo.cl',
            '{total_comercios}'  => (string) $totalComercios,
            '{link_registro}'    => SITE_URL . '/registrar-comercio',
            '{link_desuscripcion}' => SITE_URL . '/desuscribir/ejemplo-token',
        ];

        return str_replace(array_keys($vars), array_values($vars), $plantilla['contenido_html']);
    }

    private static function reordenarNumeros(): void
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll("SELECT id FROM nurturing_plantillas ORDER BY numero ASC");
        foreach ($rows as $i => $row) {
            $db->execute(
                "UPDATE nurturing_plantillas SET numero = ? WHERE id = ?",
                [$i + 1, $row['id']]
            );
        }
    }
}
