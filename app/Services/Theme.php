<?php
namespace App\Services;

use App\Core\Database;

/**
 * Servicio de tema dinámico
 * Gestiona colores y apariencia configurable desde admin
 */
class Theme
{
    private static ?array $colors = null;

    /**
     * Obtener todos los colores del tema
     */
    public static function getColors(): array
    {
        if (self::$colors !== null) {
            return self::$colors;
        }

        $defaults = self::getDefaults();

        try {
            $siteId = self::getSiteId();
            $rows = Database::getInstance()->fetchAll(
                "SELECT clave, valor FROM redes_sociales_config WHERE site_id = ? AND clave LIKE 'theme_%'",
                [$siteId]
            );

            $colors = $defaults;
            foreach ($rows as $row) {
                $key = str_replace('theme_', '', $row['clave']);
                if (isset($colors[$key]) && !empty($row['valor'])) {
                    $colors[$key] = $row['valor'];
                }
            }

            self::$colors = $colors;
        } catch (\Throwable $e) {
            self::$colors = $defaults;
        }

        return self::$colors;
    }

    /**
     * Obtener un color específico
     */
    public static function getColor(string $key, string $default = ''): string
    {
        $colors = self::getColors();
        return $colors[$key] ?? $default;
    }

    /**
     * Generar CSS variables para inyectar en el layout
     */
    public static function generateCssVariables(): string
    {
        $colors = self::getColors();
        $css = '';

        $map = [
            'primary'          => '--color-primary',
            'primary_light'    => '--color-primary-light',
            'primary_dark'     => '--color-primary-dark',
            'accent'           => '--color-accent-theme',
            'accent_light'     => '--color-accent-theme-light',
            'accent_dark'      => '--color-accent-theme-dark',
            'header_bg'        => '--color-header-bg',
            'header_text'      => '--color-header-text',
            'footer_bg'        => '--color-footer-bg',
            'footer_text'      => '--color-footer-text',
            'btn_primary_bg'   => '--color-btn-primary-bg',
            'btn_primary_text' => '--color-btn-primary-text',
            'btn_accent_bg'    => '--color-btn-accent-bg',
            'btn_accent_text'  => '--color-btn-accent-text',
        ];

        foreach ($map as $key => $var) {
            if (!empty($colors[$key])) {
                $css .= "{$var}: {$colors[$key]}; ";
            }
        }

        // Generate RGB values for primary color (used in rgba)
        if (!empty($colors['primary'])) {
            $rgb = self::hexToRgb($colors['primary']);
            if ($rgb) {
                $css .= "--color-primary-rgb: {$rgb['r']}, {$rgb['g']}, {$rgb['b']}; ";
            }
        }

        return $css;
    }

    /**
     * Obtener presets de colores
     */
    public static function getPresets(): array
    {
        return [
            'regalos' => [
                'label'        => 'Regalos Purranque',
                'emoji'        => '&#127873;',
                'primary'      => '#e53e3e',
                'primary_light'=> '#fed7d7',
                'primary_dark' => '#c53030',
                'accent'       => '#38a169',
                'accent_light' => '#c6f6d5',
                'accent_dark'  => '#2f855a',
                'header_bg'    => '#ffffff',
                'header_text'  => '#1a202c',
                'footer_bg'    => '#1a202c',
                'footer_text'  => '#ffffff',
                'btn_primary_bg'   => '#e53e3e',
                'btn_primary_text' => '#ffffff',
                'btn_accent_bg'    => '#38a169',
                'btn_accent_text'  => '#ffffff',
            ],
            'naranja' => [
                'label'        => 'Naranja vibrante',
                'emoji'        => '🟠',
                'primary'      => '#ea580c',
                'primary_light'=> '#fed7aa',
                'primary_dark' => '#c2410c',
                'accent'       => '#1a365d',
                'accent_light' => '#dbeafe',
                'accent_dark'  => '#1e3a5f',
                'header_bg'    => '#1e293b',
                'header_text'  => '#ffffff',
                'footer_bg'    => '#1e293b',
                'footer_text'  => '#ffffff',
                'btn_primary_bg'   => '#ea580c',
                'btn_primary_text' => '#ffffff',
                'btn_accent_bg'    => '#1a365d',
                'btn_accent_text'  => '#ffffff',
            ],
            'azul' => [
                'label'        => 'Azul corporativo',
                'emoji'        => '🔵',
                'primary'      => '#1a365d',
                'primary_light'=> '#dbeafe',
                'primary_dark' => '#1e3a5f',
                'accent'       => '#ea580c',
                'accent_light' => '#fed7aa',
                'accent_dark'  => '#c2410c',
                'header_bg'    => '#1a365d',
                'header_text'  => '#ffffff',
                'footer_bg'    => '#1e293b',
                'footer_text'  => '#ffffff',
                'btn_primary_bg'   => '#1a365d',
                'btn_primary_text' => '#ffffff',
                'btn_accent_bg'    => '#ea580c',
                'btn_accent_text'  => '#ffffff',
            ],
            'verde' => [
                'label'        => 'Verde naturaleza',
                'emoji'        => '🟢',
                'primary'      => '#16a34a',
                'primary_light'=> '#bbf7d0',
                'primary_dark' => '#15803d',
                'accent'       => '#1e293b',
                'accent_light' => '#e2e8f0',
                'accent_dark'  => '#0f172a',
                'header_bg'    => '#1e293b',
                'header_text'  => '#ffffff',
                'footer_bg'    => '#1e293b',
                'footer_text'  => '#ffffff',
                'btn_primary_bg'   => '#16a34a',
                'btn_primary_text' => '#ffffff',
                'btn_accent_bg'    => '#1e293b',
                'btn_accent_text'  => '#ffffff',
            ],
            'rojo' => [
                'label'        => 'Rojo pasión',
                'emoji'        => '🔴',
                'primary'      => '#dc2626',
                'primary_light'=> '#fecaca',
                'primary_dark' => '#b91c1c',
                'accent'       => '#1e293b',
                'accent_light' => '#e2e8f0',
                'accent_dark'  => '#0f172a',
                'header_bg'    => '#1e293b',
                'header_text'  => '#ffffff',
                'footer_bg'    => '#1e293b',
                'footer_text'  => '#ffffff',
                'btn_primary_bg'   => '#dc2626',
                'btn_primary_text' => '#ffffff',
                'btn_accent_bg'    => '#1e293b',
                'btn_accent_text'  => '#ffffff',
            ],
            'morado' => [
                'label'        => 'Morado elegante',
                'emoji'        => '🟣',
                'primary'      => '#7c3aed',
                'primary_light'=> '#ddd6fe',
                'primary_dark' => '#6d28d9',
                'accent'       => '#1e293b',
                'accent_light' => '#e2e8f0',
                'accent_dark'  => '#0f172a',
                'header_bg'    => '#1e293b',
                'header_text'  => '#ffffff',
                'footer_bg'    => '#1e293b',
                'footer_text'  => '#ffffff',
                'btn_primary_bg'   => '#7c3aed',
                'btn_primary_text' => '#ffffff',
                'btn_accent_bg'    => '#1e293b',
                'btn_accent_text'  => '#ffffff',
            ],
            'turquesa' => [
                'label'        => 'Turquesa fresco',
                'emoji'        => '🩵',
                'primary'      => '#0891b2',
                'primary_light'=> '#cffafe',
                'primary_dark' => '#0e7490',
                'accent'       => '#1e293b',
                'accent_light' => '#e2e8f0',
                'accent_dark'  => '#0f172a',
                'header_bg'    => '#1e293b',
                'header_text'  => '#ffffff',
                'footer_bg'    => '#1e293b',
                'footer_text'  => '#ffffff',
                'btn_primary_bg'   => '#0891b2',
                'btn_primary_text' => '#ffffff',
                'btn_accent_bg'    => '#1e293b',
                'btn_accent_text'  => '#ffffff',
            ],
            'oscuro' => [
                'label'        => 'Oscuro premium',
                'emoji'        => '🖤',
                'primary'      => '#1e293b',
                'primary_light'=> '#e2e8f0',
                'primary_dark' => '#0f172a',
                'accent'       => '#f59e0b',
                'accent_light' => '#fef3c7',
                'accent_dark'  => '#d97706',
                'header_bg'    => '#0f172a',
                'header_text'  => '#ffffff',
                'footer_bg'    => '#0f172a',
                'footer_text'  => '#ffffff',
                'btn_primary_bg'   => '#1e293b',
                'btn_primary_text' => '#ffffff',
                'btn_accent_bg'    => '#f59e0b',
                'btn_accent_text'  => '#1e293b',
            ],
            'rosa' => [
                'label'        => 'Rosa suave',
                'emoji'        => '🌸',
                'primary'      => '#ec4899',
                'primary_light'=> '#fce7f3',
                'primary_dark' => '#db2777',
                'accent'       => '#1e293b',
                'accent_light' => '#e2e8f0',
                'accent_dark'  => '#0f172a',
                'header_bg'    => '#1e293b',
                'header_text'  => '#ffffff',
                'footer_bg'    => '#1e293b',
                'footer_text'  => '#ffffff',
                'btn_primary_bg'   => '#ec4899',
                'btn_primary_text' => '#ffffff',
                'btn_accent_bg'    => '#1e293b',
                'btn_accent_text'  => '#ffffff',
            ],
        ];
    }

    /**
     * Aplicar un preset completo
     */
    public static function applyPreset(string $presetName): bool
    {
        $presets = self::getPresets();
        if (!isset($presets[$presetName])) {
            return false;
        }

        $preset = $presets[$presetName];
        $siteId = self::getSiteId();
        $db = Database::getInstance();

        foreach ($preset as $key => $value) {
            if (in_array($key, ['label', 'emoji'])) continue;
            self::setColor($key, $value);
        }

        // Save preset name
        self::setColor('preset', $presetName);
        self::$colors = null; // Reset cache

        return true;
    }

    /**
     * Guardar un color en la BD
     */
    public static function setColor(string $key, string $value): void
    {
        $siteId = self::getSiteId();
        $clave = 'theme_' . $key;
        $db = Database::getInstance();

        $exists = $db->fetch(
            "SELECT id FROM redes_sociales_config WHERE site_id = ? AND clave = ?",
            [$siteId, $clave]
        );

        if ($exists) {
            $db->execute(
                "UPDATE redes_sociales_config SET valor = ? WHERE site_id = ? AND clave = ?",
                [$value, $siteId, $clave]
            );
        } else {
            $db->insert('redes_sociales_config', [
                'site_id' => $siteId,
                'clave'   => $clave,
                'valor'   => $value,
            ]);
        }

        self::$colors = null; // Reset cache
    }

    /**
     * Auto-generar tonos claros y oscuros a partir de un color
     */
    public static function autoGenerateShades(string $hex): array
    {
        $rgb = self::hexToRgb($hex);
        if (!$rgb) return ['light' => '#e2e8f0', 'dark' => '#1e293b'];

        // Light: mezclar con blanco al 80%
        $light = self::rgbToHex(
            (int)(($rgb['r'] * 0.2) + (255 * 0.8)),
            (int)(($rgb['g'] * 0.2) + (255 * 0.8)),
            (int)(($rgb['b'] * 0.2) + (255 * 0.8))
        );

        // Dark: oscurecer al 80%
        $dark = self::rgbToHex(
            (int)($rgb['r'] * 0.8),
            (int)($rgb['g'] * 0.8),
            (int)($rgb['b'] * 0.8)
        );

        return ['light' => $light, 'dark' => $dark];
    }

    /**
     * Colores por defecto (naranja)
     */
    private static function getDefaults(): array
    {
        return [
            'primary'          => '#e53e3e',
            'primary_light'    => '#fed7d7',
            'primary_dark'     => '#c53030',
            'accent'           => '#38a169',
            'accent_light'     => '#c6f6d5',
            'accent_dark'      => '#2f855a',
            'header_bg'        => '#ffffff',
            'header_text'      => '#1a202c',
            'footer_bg'        => '#1a202c',
            'footer_text'      => '#ffffff',
            'btn_primary_bg'   => '#e53e3e',
            'btn_primary_text' => '#ffffff',
            'btn_accent_bg'    => '#38a169',
            'btn_accent_text'  => '#ffffff',
            'preset'           => 'regalos',
        ];
    }

    private static function getSiteId(): int
    {
        if (class_exists('\\App\\Services\\SiteManager')) {
            try {
                return SiteManager::getInstance()->getSiteId();
            } catch (\Throwable $e) {}
        }
        return 1;
    }

    private static function hexToRgb(string $hex): ?array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) return null;
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function rgbToHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02x%02x%02x', max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
    }
}
