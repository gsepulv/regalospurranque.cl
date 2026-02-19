<?php
namespace App\Core;

/**
 * Motor de vistas PHP
 * Renderiza templates dentro de layouts con variables compartidas
 */
class View
{
    /**
     * Renderizar una vista dentro de un layout
     *
     * @param string      $view   Ruta de la vista (ej: 'public/home', 'admin/dashboard')
     * @param array       $data   Variables para la vista
     * @param string|null $layout Layout a usar (null = auto-detectar, 'none' = sin layout)
     */
    public static function render(string $view, array $data = [], ?string $layout = null): void
    {
        // Auto-detectar layout según prefijo de la vista
        if ($layout === null) {
            if (str_starts_with($view, 'admin/login')) {
                $layout = 'login';
            } elseif (str_starts_with($view, 'admin/')) {
                $layout = 'admin';
            } else {
                $layout = 'public';
            }
        }

        // Variables comunes disponibles en todas las vistas
        $admin = $_SESSION['admin'] ?? null;
        $csrf  = csrf_token();
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);

        // Merge con datos del controlador
        $data = array_merge($data, [
            'admin' => $admin,
            'csrf'  => $csrf,
            'flash' => $flash,
        ]);

        // Extraer variables al scope actual
        extract($data);

        // Renderizar vista a buffer
        ob_start();
        $viewFile = BASE_PATH . '/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            ob_end_clean();
            Response::error(404);
            return;
        }
        include $viewFile;
        $content = ob_get_clean();

        // Renderizar layout (o solo el contenido si layout = 'none')
        if ($layout === 'none') {
            echo $content;
            return;
        }

        $layoutFile = BASE_PATH . '/views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Renderizar un partial (fragmento reutilizable)
     * Nota: En layouts, usar include directo para heredar scope de variables
     */
    public static function partial(string $partial, array $data = []): void
    {
        extract($data);
        $file = BASE_PATH . '/views/partials/' . $partial . '.php';
        if (file_exists($file)) {
            include $file;
        }
    }
}
