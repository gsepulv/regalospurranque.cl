<?php
namespace App\Core;

/**
 * Bootstrap de la aplicación
 * Inicializa sesión, middleware global y despacha la ruta
 */
class App
{
    private Router $router;
    private Request $request;

    public function __construct()
    {
        $this->startSession();
        $this->request = new Request();
        $this->router = new Router();
    }

    /**
     * Ejecutar la aplicación
     */
    public function run(): void
    {
        // Middleware global: modo mantenimiento
        try {
            $this->runMiddleware('maintenance');
        } catch (\Throwable $e) {
            if (APP_DEBUG) throw $e;
            error_log('Middleware maintenance error: ' . $e->getMessage());
        }

        // Middleware global: redirecciones SEO
        try {
            $this->runMiddleware('redirect');
        } catch (\Throwable $e) {
            if (APP_DEBUG) throw $e;
            error_log('Middleware redirect error: ' . $e->getMessage());
        }

        // Buscar ruta que coincida
        $route = $this->router->match($this->request);

        if (!$route) {
            Response::error(404);
            return;
        }

        // CSRF en POST (excepto rutas API)
        if ($this->request->method() === 'POST' && !str_starts_with($route['uri'], '/api/')) {
            $this->runMiddleware('csrf');
        }

        // Middleware de ruta (auth, permission, etc.)
        foreach ($route['middleware'] as $mw) {
            $this->runMiddleware($mw);
        }

        // Despachar al controlador
        $this->router->dispatch($route, $this->request);
    }

    /**
     * Iniciar sesión PHP
     */
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start([
                'cookie_lifetime' => SESSION_LIFETIME,
                'cookie_httponly'  => true,
                'cookie_secure'   => APP_ENV === 'production',
                'cookie_samesite' => 'Lax',
            ]);
        }
    }

    /**
     * Ejecutar un middleware por nombre
     */
    private function runMiddleware(string $name): void
    {
        $map = require BASE_PATH . '/config/middleware.php';
        if (isset($map[$name])) {
            $middleware = new $map[$name]();
            $middleware->handle($this->request);
        }
    }
}
