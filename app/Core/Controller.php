<?php
namespace App\Core;

/**
 * Clase base para todos los controladores
 * Provee acceso a DB, request, render, redirect, validación y logging
 */
abstract class Controller
{
    protected Database $db;
    protected Request $request;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->request = new Request();
    }

    /**
     * Renderizar vista con datos
     */
    protected function render(string $view, array $data = [], ?string $layout = null): void
    {
        View::render($view, $data, $layout);
    }

    /**
     * Redireccionar con mensajes flash opcionales
     */
    protected function redirect(string $url, array $flash = []): void
    {
        foreach ($flash as $key => $value) {
            $_SESSION['flash'][$key] = $value;
        }
        Response::redirect($url);
    }

    /**
     * Volver a la página anterior con datos flash
     */
    protected function back(array $flash = []): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        // Extraer solo el path del referer
        $path = parse_url($referer, PHP_URL_PATH) ?: '/';
        $this->redirect($path, $flash);
    }

    /**
     * Responder con JSON
     */
    protected function json(array $data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    /**
     * Validar datos según reglas
     */
    protected function validate(array $data, array $rules): \App\Services\Validator
    {
        $validator = new \App\Services\Validator($data, $rules);
        $validator->validate();
        return $validator;
    }

    /**
     * Registrar acción en el log de admin
     */
    protected function log(string $modulo, string $accion, string $entidadTipo, int $entidadId, string $detalle = ''): void
    {
        \App\Services\Logger::log($modulo, $accion, $entidadTipo, $entidadId, $detalle);
    }
}
