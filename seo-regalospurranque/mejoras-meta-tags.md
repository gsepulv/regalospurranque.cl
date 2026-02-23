# Mejoras de Meta Tags — regalospurranque.cl

## Cambios a implementar en el código PHP

---

### 1. CORRECCIÓN CRÍTICA: Router.php (HEAD → 404)

**Archivo:** `app/Core/Router.php`, línea 31

**Antes:**
```php
if ($routeMethod !== $method) {
    continue;
}
```

**Después:**
```php
// HEAD debe matchear rutas GET (HTTP spec: HEAD = GET sin body)
if ($routeMethod !== $method && !($routeMethod === 'GET' && $method === 'HEAD')) {
    continue;
}
```

---

### 2. Fix truncamiento de títulos en ComercioController

**Archivo:** `app/Controllers/Public/ComercioController.php`, ~línea 93

**Problema:** `mb_substr(..., 0, 55)` corta palabras a la mitad. "Purranque" → "Purranqu"

**Solución:** Reemplazar la lógica de truncamiento por:
```php
// Función helper para truncar por palabra completa
function truncateTitle(string $text, int $maxLen = 55): string {
    if (mb_strlen($text) <= $maxLen) return $text;
    $truncated = mb_substr($text, 0, $maxLen);
    $lastSpace = mb_strrpos($truncated, ' ');
    return $lastSpace ? mb_substr($truncated, 0, $lastSpace) : $truncated;
}

// Uso:
$seoTitle = truncateTitle($comercio['nombre'] . ' . ' . $catPrincipal . ' en Purranque') . ' . Regalos Purranque';
```

---

### 3. Separador inconsistente en /noticias

**Archivo:** `app/Controllers/Public/NoticiaController.php`, método `index()`

**Antes:** `'seo_title' => 'Noticias - Regalos Purranque'`
**Después:** `'seo_title' => 'Noticias . Regalos Purranque'`

---

### 4. BreadcrumbList faltante en /noticias

**Archivo:** `app/Controllers/Public/NoticiaController.php`, método `index()`

Agregar al array de datos que se pasa a la vista:
```php
'breadcrumbs' => [
    ['name' => 'Inicio', 'url' => '/'],
    ['name' => 'Noticias', 'url' => '/noticias'],
],
'schemas' => [
    [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => 'https://regalospurranque.cl/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Noticias', 'item' => 'https://regalospurranque.cl/noticias'],
        ],
    ],
],
```

---

### 5. noindex para categorías vacías

**Archivo:** `app/Controllers/Public/CategoriaController.php`, método `show()`

Agregar lógica condicional:
```php
// Si la categoría no tiene comercios, marcar como noindex
$noindex = (int)($total ?? 0) === 0;

// Pasar $noindex a la vista para que seo-head.php lo use
$data['noindex'] = $noindex;
```

**Además, mejorar la meta description para categorías vacías:**
```php
if ($total === 0) {
    $seoDesc = "Próximamente: comercios de {$categoria['nombre']} en Purranque, Chile. Directorio en crecimiento.";
} else {
    $seoDesc = "Los mejores comercios de {$categoria['nombre']} en Purranque, Chile. Encuentra {$total} opciones con ubicación, contacto y reseñas.";
}
```

---

### 6. Meta description de la homepage mejorada

**Antes:**
```
Directorio comercial de Purranque, Chile. Encuentra los mejores comercios, ofertas y servicios.
```

**Sugerencia (más descriptiva, con keywords locales):**
```
Directorio de regalos y comercios de Purranque, Chile. Flores, joyería, moda y más para cumpleaños, bodas y celebraciones. Encuentra tiendas locales con contacto y ubicación.
```

---

### 7. Meta description de /noticias mejorada

**Antes:**
```
Las últimas noticias y novedades del comercio local de Purranque
```

**Sugerencia:**
```
Noticias y novedades del comercio local de Purranque, Chile. Nuevos locales, ofertas especiales y eventos de la comuna.
```

---

### 8. Agregar Cache-Control para páginas públicas

**Archivo:** `app/Core/Response.php` o `index.php` o directamente en los controllers públicos

Agregar antes de cualquier output en páginas públicas:
```php
// Solo para páginas públicas (no admin, no API)
if (!str_starts_with($uri, '/admin') && !str_starts_with($uri, '/api')) {
    header('Cache-Control: public, max-age=300, s-maxage=600');
    header('Vary: Accept-Encoding');
    // Remover las cabeceras PHP de sesión que fuerzan no-cache
}
```

**Nota:** El `session_start()` de PHP automáticamente envía `Cache-Control: no-store`. Para evitarlo, se puede llamar `session_cache_limiter('')` **antes** de `session_start()`.

---

### 9. Agregar `lang="es-CL"` en vez de `lang="es"`

**Archivo:** `views/partials/header.php` o el layout principal

**Antes:** `<html lang="es">`
**Después:** `<html lang="es-CL">`

---

### 10. Sitemap dinámico: excluir categorías vacías y ostermontag

**Archivo:** `app/Controllers/Public/HomeController.php`, método `sitemap()`

En la query que genera las categorías para el sitemap, agregar:
```php
// Solo incluir categorías con al menos 1 comercio activo
WHERE EXISTS (SELECT 1 FROM comercios_categorias cc
              JOIN comercios c ON c.id = cc.comercio_id
              WHERE cc.categoria_id = categorias.id AND c.activo = 1)
```

Para ostermontag, excluir por slug o eliminarlo de la base de datos.
