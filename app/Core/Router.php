<?php
namespace App\Core;

/**
 * Router: mapea URL → Controller@método
 * Soporta parámetros dinámicos: /comercio/{slug}
 */
class Router
{
    private array $routes;

    public function __construct()
    {
        $this->routes = require BASE_PATH . '/config/routes.php';
    }

    /**
     * Buscar una ruta que coincida con el request
     */
    public function match(Request $request): ?array
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes as $route) {
            $routeMethod     = $route[0];
            $routeUri        = $route[1];
            $routeHandler    = $route[2];
            $routeMiddleware = $route[3] ?? [];

            if ($routeMethod !== $method) {
                continue;
            }

            $params = $this->matchUri($routeUri, $uri);

            if ($params !== false) {
                return [
                    'handler'    => $routeHandler,
                    'params'     => $params,
                    'middleware'  => $routeMiddleware,
                    'uri'        => $routeUri,
                ];
            }
        }

        return null;
    }

    /**
     * Intentar matchear un patrón de ruta con la URI actual
     * Retorna array de params si coincide, false si no
     */
    private function matchUri(string $routeUri, string $requestUri): array|false
    {
        // Ruta estática (sin parámetros)
        if (!str_contains($routeUri, '{')) {
            return $routeUri === $requestUri ? [] : false;
        }

        // Convertir {param} en grupo de captura regex
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $routeUri);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestUri, $matches)) {
            // Retornar solo capturas con nombre
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    /**
     * Despachar la ruta al controlador correspondiente
     */
    public function dispatch(array $route, Request $request): void
    {
        [$controllerName, $method] = explode('@', $route['handler']);
        $controllerClass = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($controllerClass)) {
            Response::error(404);
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            Response::error(404);
            return;
        }

        // Llamar método con parámetros de la ruta
        call_user_func_array([$controller, $method], $route['params']);
    }
}
