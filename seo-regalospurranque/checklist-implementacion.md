# Checklist de Implementación SEO — regalospurranque.cl

**Fecha:** 23 de febrero de 2026
**Stack:** PHP 8.3 vanilla MVC / Apache / HostGator
**Deploy:** cPanel Git Version Control → Update from Remote

---

## PRIORIDAD ALTA — Resolver esta semana

### 1. Fix HEAD 404 (Router.php) ⭐ MÁS URGENTE

**Archivo:** `app/Core/Router.php`, línea 31

**Cambio exacto:**
```php
// ANTES (línea 31):
if ($routeMethod !== $method) {

// DESPUÉS:
if ($routeMethod !== $method && !($routeMethod === 'GET' && $method === 'HEAD')) {
```

**Verificación local:**
```bash
# Desde Laragon, verificar que HEAD devuelve 200:
curl -s -o /dev/null -w "%{http_code}" -X HEAD "http://localhost/regalospurranque.cl/"
# Debe devolver: 200
```

**Verificación en producción:**
```bash
curl -s -o /dev/null -w "%{http_code}" -X HEAD "https://regalospurranque.cl/"
# Debe devolver: 200 (antes devolvía 404)
```

**Impacto esperado:** +15-20 páginas indexadas en 2-4 semanas.

---

### 2. Ajustar Cache-Control para páginas públicas

**Archivo:** `index.php` (antes de `session_start()`)

Agregar antes de `session_start()`:
```php
// Evitar que session_start() envíe Cache-Control: no-store
session_cache_limiter('');
```

**Archivo:** `app/Core/App.php` (en método `run()`, después del routing exitoso)

Para rutas públicas (no admin, no API), agregar header:
```php
if (!str_starts_with($uri, '/admin') && !str_starts_with($uri, '/api')) {
    header('Cache-Control: public, max-age=300');
}
```

**Alternativa más simple** — en `.htaccess`, agregar regla para HTML:
```apache
<IfModule mod_headers.c>
    <FilesMatch "\.php$">
        Header set Cache-Control "public, max-age=300"
    </FilesMatch>
</IfModule>
```
*Nota: Esto afectaría admin también. La solución PHP es más precisa.*

---

### 3. Deploy y solicitar re-indexación en GSC

Después de implementar puntos 1 y 2:

1. **Commit y push:**
```bash
git add app/Core/Router.php index.php
git commit -m "fix: HEAD requests return 200 instead of 404 + cache headers"
git push origin main
```

2. **Deploy:** cPanel → Git Version Control → Update from Remote

3. **Google Search Console:**
   - Ir a "Inspección de URLs"
   - Inspeccionar `https://regalospurranque.cl/`
   - Clic "Solicitar indexación"
   - Repetir para las 5-10 páginas más importantes
   - Ir a "Sitemaps" → Re-enviar `https://regalospurranque.cl/sitemap.xml`

---

## PRIORIDAD MEDIA — Resolver esta semana o la siguiente

### 4. noindex para categorías vacías

**Archivo:** `app/Controllers/Public/CategoriaController.php`, método `show()`

Agregar variable `$noindex` al array de datos de la vista:
```php
$noindex = count($comercios) === 0;
// ... pasar a la vista junto con el resto de datos
'noindex' => $noindex,
```

Verificar que `views/partials/seo-head.php` use `$noindex` para cambiar el meta robots:
```php
<meta name="robots" content="<?= ($noindex ?? false) ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' ?>">
```

---

### 5. Fix truncamiento de títulos

**Archivo:** `app/Controllers/Public/ComercioController.php`

Buscar la línea con `mb_substr(..., 0, 55)` y reemplazar con truncamiento por palabra:
```php
$base = $comercio['nombre'] . ' . ' . $catPrincipal . ' en Purranque';
if (mb_strlen($base) > 55) {
    $base = mb_substr($base, 0, 55);
    $lastSpace = mb_strrpos($base, ' ');
    if ($lastSpace) $base = mb_substr($base, 0, $lastSpace);
}
$seoTitle = $base . ' . Regalos Purranque';
```

---

### 6. BreadcrumbList en /noticias

**Archivo:** `app/Controllers/Public/NoticiaController.php`, método `index()`

Agregar al array de datos:
```php
'schemas' => [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => SITE_URL . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Noticias', 'item' => SITE_URL . '/noticias'],
        ],
    ],
],
```

---

### 7. Separador consistente en título de /noticias

**Archivo:** `app/Controllers/Public/NoticiaController.php`, método `index()`

Cambiar:
```php
'seo_title' => 'Noticias - Regalos Purranque',
// a:
'seo_title' => 'Noticias . Regalos Purranque',
```

---

### 8. Mejorar meta description de homepage

**Archivo:** `app/Controllers/Public/HomeController.php` o donde se defina SITE_DESCRIPTION

Cambiar de:
```
Directorio comercial de Purranque, Chile. Encuentra los mejores comercios, ofertas y servicios.
```
A:
```
Directorio de regalos y comercios de Purranque, Chile. Flores, joyería, moda y más para cumpleaños, bodas y celebraciones.
```

---

### 9. Actualizar sitemap dinámico

**Archivo:** `app/Controllers/Public/HomeController.php`, método `sitemap()`

Modificar la consulta de categorías para excluir las vacías:
```sql
-- Agregar condición para solo incluir categorías con comercios
HAVING comercios_count > 0
```

Excluir `/fecha/ostermontag` del sitemap (filtrar por slug o eliminar de la BD).

Agregar `/contacto` al sitemap.

Excluir `/buscar` del sitemap (no tiene contenido propio).

Excluir `/cookies` y `/contenidos` del sitemap (bajo valor SEO).

---

### 10. Actualizar robots.txt

Reemplazar el archivo `robots.txt` actual con la versión mejorada de `seo-regalospurranque/robots.txt`. Diferencias clave:
- Agrega `Disallow: /buscar`
- Agrega `Disallow: /mis-resenas`
- Agrega `Disallow: /mi-comercio`
- Agrega `Disallow: /compartir/`
- Agrega `Disallow: /registrar-comercio`
- Bloquea parámetros de búsqueda duplicados

---

## PRIORIDAD BAJA — Resolver cuando sea conveniente

### 11. Cambiar `lang="es"` a `lang="es-CL"`

**Archivo:** Layout principal (probablemente `views/partials/header.php` o `views/layout.php`)

```html
<!-- Antes -->
<html lang="es">
<!-- Después -->
<html lang="es-CL">
```

---

### 12. Eliminar o renombrar "ostermontag"

**Opción A:** Eliminar desde admin (`/admin/fechas`) la fecha "ostermontag".
**Opción B:** Renombrar a "lunes-de-pascua" y actualizar nombre/descripción en español.

---

### 13. Crear apple-touch-icon.png

Crear un archivo `apple-touch-icon.png` de 180x180px con el logo del sitio y colocarlo en la raíz.

---

### 14. Mejorar meta description para categorías vacías

En `CategoriaController@show`, cuando `$total === 0`:
```php
$seoDesc = "Próximamente: comercios de {$cat['nombre']} en Purranque. Directorio en crecimiento.";
```

---

### 15. Agregar contenido a categorías vacías

Las categorías vacías son la mayor fuente de "thin content". Para mejorar:
- Agregar texto descriptivo sobre cada categoría
- Sugerir categorías relacionadas con comercios
- O mejor aún: conseguir comercios reales para cada categoría

---

### 16. Redirecciones v1 → v2

Si quedaron URLs de v1 indexadas en Google (formato `comercio.php?slug=...`), agregar las redirecciones del archivo `htaccess-o-config.txt` sección 3b.

Verificar primero en GSC si hay URLs v1 en el informe.

---

## Verificación post-implementación

Después de deployar todos los cambios:

1. **Verificar HEAD 200:**
```bash
curl -s -o /dev/null -w "%{http_code}" -X HEAD "https://regalospurranque.cl/"
curl -s -o /dev/null -w "%{http_code}" -X HEAD "https://regalospurranque.cl/sitemap.xml"
curl -s -o /dev/null -w "%{http_code}" -X HEAD "https://regalospurranque.cl/comercio/joyeria-la-hermosura"
# Todos deben devolver 200
```

2. **Verificar Cache-Control:**
```bash
curl -s -I "https://regalospurranque.cl/" | grep -i cache-control
# Debe mostrar: public, max-age=300 (o similar)
```

3. **Verificar sitemap:**
```bash
curl -s "https://regalospurranque.cl/sitemap.xml" | grep -c "<url>"
# Debe mostrar el número correcto de URLs (sin categorías vacías, sin ostermontag)
```

4. **Google Search Console:**
   - Esperar 48 horas después del deploy
   - Revisar "Páginas" → "Por qué las páginas no se indexan"
   - Los números de "rastreadas no indexadas" y "descubiertas no indexadas" deberían bajar progresivamente
   - Monitorear semanalmente durante 4-6 semanas

5. **Herramienta de inspección de URL (GSC):**
   - Inspeccionar homepage, verificar que muestra "URL disponible para Google"
   - Verificar que no hay advertencias de robots.txt
   - Verificar que el canonical detectado es correcto
