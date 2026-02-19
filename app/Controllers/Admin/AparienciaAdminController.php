<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\Theme;

/**
 * Gestión de Apariencia (colores dinámicos) desde el panel admin
 */
class AparienciaAdminController extends Controller
{
    public function index(): void
    {
        $colors  = Theme::getColors();
        $presets = Theme::getPresets();

        $this->render('admin/apariencia/index', [
            'title'   => 'Apariencia — ' . SITE_NAME,
            'colors'  => $colors,
            'presets' => $presets,
        ]);
    }

    public function update(): void
    {
        $colorKeys = [
            'primary', 'primary_light', 'primary_dark',
            'accent', 'accent_light', 'accent_dark',
            'header_bg', 'header_text',
            'footer_bg', 'footer_text',
            'btn_primary_bg', 'btn_primary_text',
            'btn_accent_bg', 'btn_accent_text',
        ];

        foreach ($colorKeys as $key) {
            if (isset($_POST[$key]) && preg_match('/^#[0-9a-fA-F]{6}$/', $_POST[$key])) {
                Theme::setColor($key, $_POST[$key]);
            }
        }

        $this->log('apariencia', 'actualizar', 'config', 0, 'Colores actualizados manualmente');
        $this->redirect('/admin/apariencia', ['success' => 'Colores actualizados correctamente']);
    }

    public function preset(): void
    {
        $presetName = $_POST['preset'] ?? '';
        if (Theme::applyPreset($presetName)) {
            $this->log('apariencia', 'preset', 'config', 0, "Preset aplicado: {$presetName}");
            $this->redirect('/admin/apariencia', ['success' => "Preset \"{$presetName}\" aplicado correctamente"]);
        } else {
            $this->redirect('/admin/apariencia', ['error' => 'Preset no encontrado']);
        }
    }

    public function autoShades(): void
    {
        $color = $_POST['color'] ?? '';
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $this->json(['ok' => false, 'error' => 'Color inválido'], 400);
            return;
        }

        $shades = Theme::autoGenerateShades($color);
        $this->json(['ok' => true, 'shades' => $shades]);
    }
}
