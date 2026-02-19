<?php
namespace App\Core;

/**
 * Clase base para middleware
 * Cada middleware debe implementar handle()
 */
abstract class Middleware
{
    /**
     * Procesar el request
     * Si hay error, debe terminar la ejecución (redirect, error, exit)
     * Si todo OK, simplemente retorna
     */
    abstract public function handle(Request $request): void;
}
