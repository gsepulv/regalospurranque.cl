# Diagnóstico SEO — regalospurranque.cl

**Fecha:** 23 de febrero de 2026
**Dominio:** https://regalospurranque.cl
**Stack:** PHP 8.3 vanilla MVC / Apache / HostGator compartido

---

## Resumen Ejecutivo

El sitio tiene una base técnica sólida (SSL, headers de seguridad, datos estructurados, meta tags en todas las páginas, sitemap dinámico), pero **un bug crítico en el Router PHP hace que todas las peticiones HEAD devuelvan HTTP 404**. Esto hace que Googlebot interprete que las páginas no existen, lo que explica directamente por qué solo 25 de 56 páginas están indexadas. Secundariamente, el sitio tiene problemas de caché agresiva (no-store en HTML), categorías vacías indexables, y una fecha especial de prueba en alemán.

---

## 1. PROBLEMAS CRÍTICOS (Prioridad Alta)

### 1.1 Bug HEAD 404 — TODAS las páginas responden 404 a peticiones HEAD

**Archivo:** `app/Core/Router.php`, línea 31
**Causa:** El router compara `$routeMethod !== $method` de forma estricta. Las rutas están definidas como `GET`, pero las peticiones HEAD tienen method `HEAD`. Como no hay match, el router devuelve `Response::error(404)`.

**Impacto:** Googlebot envía peticiones HEAD para verificar el estado de las páginas antes de decidir si indexarlas. Al recibir 404, Google concluye que la página no existe y la marca como "crawled - currently not indexed" o "discovered - currently not indexed".

**Evidencia:**
```
GET  https://regalospurranque.cl/ → 200 OK
HEAD https://regalospurranque.cl/ → 404 Not Found
```

**Solución:** En `Router.php`, tratar HEAD como GET para efectos de matching:
```php
if ($routeMethod !== $method && !($routeMethod === 'GET' && $method === 'HEAD')) {
    continue;
}
```

### 1.2 Sitemap.xml devuelve 404 para HEAD

El sitemap.xml se sirve a través del router PHP (no es un archivo estático). Cuando Google envía HEAD a `/sitemap.xml`, recibe 404. Esto puede hacer que Google ignore el sitemap completo.

**Solución:** Misma corrección del punto 1.1 resuelve este problema.

### 1.3 Cache-Control: no-store, no-cache en TODAS las páginas HTML

Todas las páginas PHP envían `Cache-Control: no-store, no-cache, must-revalidate`. Esto:
- Fuerza a Googlebot a re-crawlear completamente cada vez
- Consume crawl budget innecesariamente
- No permite que navegadores cacheen la página

**Solución:** Configurar un ETag o `Cache-Control: public, max-age=300` (5 minutos) para páginas públicas.

---

## 2. PROBLEMAS IMPORTANTES (Prioridad Media)

### 2.1 Categorías vacías indexables con meta description "0 opciones"

Las categorías sin comercios (gastronomía, belleza-y-spa, experiencias, decoración, tecnología-y-gadgets) generan:
- Meta description: *"Encuentra 0 opciones con ubicación, contacto y reseñas"*
- Contenido: solo un mensaje "Aún no hay comercios"
- ~26 KB de HTML para una página esencialmente vacía

**Impacto:** Google considera estas páginas de "thin content" y las clasifica como "crawled but not indexed".

**Solución:** Agregar `noindex` a categorías con 0 comercios, o mejorar el contenido con texto descriptivo.

### 2.2 Bug de truncamiento de títulos en comercios

**Archivo:** `ComercioController.php`, ~línea 93
El título se genera con `mb_substr($nombre . ' . ' . $categoria . ' en Purranque', 0, 55)` y luego se agrega ` . Regalos Purranque`. Esto corta palabras a la mitad:
- "Joyería La Hermosura . Joyería y Accesorios en Purranqu" (falta la 'e')

**Solución:** Truncar por palabra completa, no por carácter.

### 2.3 Fecha especial "ostermontag" (palabra en alemán)

La URL `/fecha/ostermontag` contiene una fecha especial con nombre en alemán ("Lunes de Pascua"). En un sitio chileno en español, esto es confuso para Google y los usuarios.

**Solución:** Eliminar o renombrar a "lunes-de-pascua".

### 2.4 Falta BreadcrumbList en /noticias

Todas las páginas de listado tienen schema BreadcrumbList excepto `/noticias`. El `NoticiaController::index()` no pasa el array `schemas` ni `breadcrumbs`.

### 2.5 Separador de título inconsistente en /noticias

`/noticias` usa guión (`Noticias - Regalos Purranque`) mientras que todas las demás páginas usan punto medio (` . Regalos Purranque`).

### 2.6 Páginas válidas ausentes del sitemap

Las siguientes páginas devuelven 200 pero no están en el sitemap:
- `/contacto`
- `/planes`
- `/registrar-comercio`
- `/feed/rss.xml`
- `/derechos`

---

## 3. OPORTUNIDADES DE MEJORA (Prioridad Baja)

### 3.1 Falta apple-touch-icon.png

Devuelve 404. Los dispositivos Apple lo solicitan automáticamente.

### 3.2 HTML lang="es" en vez de "es-CL"

El atributo `<html lang="es">` podría ser más específico: `lang="es-CL"`. Aunque los hreflang tags sí especifican `es-CL`.

### 3.3 /buscar en el sitemap

La página `/buscar` (sin parámetros) muestra una página de búsqueda vacía. Google considera estas páginas de bajo valor. Se recomienda excluirla del sitemap.

### 3.4 No hay archivo security.txt

`/.well-known/security.txt` devuelve 404. Es buena práctica tener uno.

### 3.5 Session cookie en cada request

Cada request crea una cookie `regalos_sess` con 2 horas de vida. Googlebot no maneja cookies, pero esto genera sesiones innecesarias para crawlers.

### 3.6 Oportunidad: Agregar más comercios

Con solo 5 comercios activos, la mayoría de las categorías están vacías. Aumentar el contenido es la mejora SEO más efectiva a largo plazo.

---

## 4. CORRELACIÓN CON GOOGLE SEARCH CONSOLE

### 4.1 "1 página bloqueada por robots.txt"

**Explicación:** Probablemente es `/admin/` o una URL bajo `/api/`, `/storage/` o `/config/`, todas bloqueadas en robots.txt. Esto es **correcto y deseado** — no requiere acción. Si Google encontró un enlace a `/admin/login` (que aparece en la configuración), lo reporta como bloqueado.

### 4.2 "1 página con redirección problemática"

**Explicación:** La redirección de `v2.regalos.purranque.info` a `regalospurranque.cl` funciona correctamente (301). Sin embargo, si Google indexó URLs del dominio antiguo, puede haber una **cadena de redirección** cuando combina HTTPS enforcement + domain redirect:
```
http://v2.regalos.purranque.info → https://v2.regalos.purranque.info → https://regalospurranque.cl
```
Esto son 2 redirecciones en cadena, que Google reporta como problemática.

**Solución:** Agregar regla en `.htaccess` para que `http://v2.regalos.purranque.info` redirija directamente a `https://regalospurranque.cl` en un solo salto.

### 4.3 "16 páginas descubiertas pero no indexadas"

**Explicación:** Google descubrió estas URLs (vía sitemap o enlaces) pero decidió no crawlearlas. Causas probables:
1. **Bug HEAD 404:** Google envía HEAD, recibe 404, y no se molesta en hacer GET
2. **Páginas de bajo valor percibido:** Categorías vacías, `/buscar` sin contenido
3. **Crawl budget agotado:** Con `no-cache` en todo, Google debe re-crawlear constantemente y prioriza

Las 16 URLs probables son: 10 categorías (muchas vacías) + páginas legales (términos, privacidad, cookies, contenidos) + /buscar + /mapa.

### 4.4 "13 páginas rastreadas pero no indexadas"

**Explicación:** Google sí crawleó estas páginas (hizo GET y obtuvo contenido) pero decidió no indexarlas. Causas:
1. **Thin content:** Categorías vacías con solo "Aún no hay comercios"
2. **Contenido duplicado percibido:** Múltiples categorías vacías con estructura idéntica
3. **Bajo valor único:** Páginas legales estándar sin contenido distintivo
4. **Bug HEAD 404:** Incluso si el GET devuelve 200, si un HEAD posterior devuelve 404, Google puede desindexar

### 4.5 "Solo 25 de 56 páginas indexadas"

**Cálculo del sitemap:**
- Sitemap tiene 49 URLs
- Rutas activas fuera del sitemap: ~7 más
- Total real: ~56 URLs

**Las 25 indexadas** son probablemente: homepage + 5 páginas de listado + ~5 comercios + ~12 fechas especiales + 2 noticias.

**Las 31 no indexadas:** Resultado combinado de HEAD 404, thin content, y páginas de bajo valor.

---

## 5. IMPACTO ESTIMADO DE LAS CORRECCIONES

| Corrección | Páginas afectadas | Impacto esperado |
|---|---|---|
| Fix HEAD 404 | TODAS (49+) | **+15-20 páginas indexadas** en 2-4 semanas |
| Cache-Control adecuado | TODAS | Mejor crawl efficiency, indexación más rápida |
| noindex en categorías vacías | 5-7 | Señal más limpia, elimina thin content |
| Fix truncamiento títulos | ~3 comercios | Mejor CTR en resultados |
| Agregar /contacto al sitemap | 1 | +1 página indexada |
| Eliminar "ostermontag" | 1 | Eliminar contenido confuso |
| Fix cadena de redirección | 1 | Resolver warning GSC |

**Estimación:** Corrigiendo solo el bug HEAD 404 + cache headers, se debería pasar de 25 a ~40 páginas indexadas en 4-6 semanas.
