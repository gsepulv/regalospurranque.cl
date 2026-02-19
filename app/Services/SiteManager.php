<?php
namespace App\Services;

use App\Core\Database;

/**
 * Gestión multi-sitio
 * Detecta el sitio actual por dominio y gestiona el contexto
 */
class SiteManager
{
    private static ?self $instance = null;
    private ?array $currentSite = null;
    private int $currentSiteId = 1;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Detectar sitio actual por dominio HTTP_HOST
     */
    public function detect(): void
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // Quitar puerto si existe
        $host = explode(':', $host)[0];

        try {
            $db = Database::getInstance();
            $site = $db->fetch(
                "SELECT * FROM sitios WHERE dominio = ? AND activo = 1",
                [$host]
            );

            if ($site) {
                $this->currentSite = $site;
                $this->currentSiteId = (int) $site['id'];
            } else {
                // Fallback: primer sitio activo
                $site = $db->fetch(
                    "SELECT * FROM sitios WHERE activo = 1 ORDER BY id ASC LIMIT 1"
                );
                if ($site) {
                    $this->currentSite = $site;
                    $this->currentSiteId = (int) $site['id'];
                }
            }
        } catch (\Throwable $e) {
            // Si la tabla no existe aún, usar defaults
            $this->currentSiteId = 1;
        }
    }

    /**
     * Obtener ID del sitio actual
     */
    public function getSiteId(): int
    {
        return $this->currentSiteId;
    }

    /**
     * Obtener datos del sitio actual
     */
    public function getSite(): ?array
    {
        return $this->currentSite;
    }

    /**
     * Establecer sitio manualmente (para admin/superadmin)
     */
    public function setSiteId(int $id): void
    {
        $this->currentSiteId = $id;

        try {
            $db = Database::getInstance();
            $site = $db->fetch("SELECT * FROM sitios WHERE id = ?", [$id]);
            if ($site) {
                $this->currentSite = $site;
            }
        } catch (\Throwable $e) {
            // Silencioso
        }
    }

    /**
     * Obtener todos los sitios activos (para selector)
     */
    public function getAllSites(): array
    {
        try {
            $db = Database::getInstance();
            return $db->fetchAll(
                "SELECT id, nombre, slug, dominio, ciudad, activo FROM sitios ORDER BY nombre"
            );
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Verificar si el usuario tiene acceso al sitio
     */
    public function userCanAccessSite(array $user, int $siteId): bool
    {
        // Superadmin puede acceder a todos los sitios
        if (($user['rol'] ?? '') === 'superadmin') {
            return true;
        }

        // Los demás solo acceden a su sitio asignado
        return ((int) ($user['site_id'] ?? 1)) === $siteId;
    }

    /**
     * Agregar filtro de sitio a una consulta SQL
     * Retorna " AND tabla.site_id = ?" y agrega el parámetro
     */
    public function addSiteFilter(string $alias = '', array &$params = []): string
    {
        $prefix = $alias ? "{$alias}." : '';
        $params[] = $this->currentSiteId;
        return " AND {$prefix}site_id = ?";
    }

    /**
     * Obtener nombre del sitio actual
     */
    public function getSiteName(): string
    {
        return $this->currentSite['nombre'] ?? SITE_NAME;
    }

    /**
     * Obtener color primario del sitio
     */
    public function getColor(): string
    {
        return $this->currentSite['color_primario'] ?? '#2563eb';
    }

    /**
     * Obtener coordenadas del sitio
     */
    public function getCoords(): array
    {
        return [
            'lat'  => (float) ($this->currentSite['lat'] ?? CITY_LAT),
            'lng'  => (float) ($this->currentSite['lng'] ?? CITY_LNG),
            'zoom' => (int) ($this->currentSite['zoom'] ?? CITY_ZOOM),
        ];
    }
}
