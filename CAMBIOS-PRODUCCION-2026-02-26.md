# CAMBIOS APLICADOS EN PRODUCCIÓN - 26 Feb 2026

## Contexto
Se detectaron y corrigieron 6 problemas en regalospurranque.cl directamente en producción mediante scripts PHP. Claude Code debe replicar estos cambios en el repositorio local para mantener sincronía.

---

## CAMBIO 1: Service Worker — Versión de caché

**Archivo:** `sw.js` (raíz del sitio)

**Cambio:** Actualizar versión del caché para forzar refresh en todos los visitantes.

```diff
- var CACHE_NAME = 'regalos-v2-cache-v1';
+ var CACHE_NAME = 'regalos-v2-cache-v2';
```

**Motivo:** El caché viejo causaba 8 errores en consola. Al cambiar la versión, el SW limpia cachés antiguos automáticamente.

---

## CAMBIO 2: Banner footer — Renderizado en footer.php

**Archivo:** `views/partials/footer.php`

**Cambio:** Agregar sección de banners tipo "footer" ANTES de `<footer class="footer">`. El admin permitía crear banners tipo footer, pero el frontend nunca los mostraba.

**Agregar AL INICIO del archivo, antes de `<footer>`:**

```php
<!-- Banner Footer -->
<?php
if (!isset($bannersFooter)) {
    $bannersFooter = \App\Models\Banner::getByTipo('footer');
}
if (!empty($bannersFooter)): ?>
<section class="footer-banners">
    <div class="container">
        <div class="footer-banners__grid">
            <?php foreach ($bannersFooter as $fb): ?>
                <div class="footer-banner-item">
                    <?php if (!empty($fb['url'])): ?>
                        <a href="<?= e($fb['url']) ?>" target="_blank" rel="noopener"
                           data-banner-id="<?= $fb['id'] ?>"
                           onclick="if(typeof trackBanner==='function')trackBanner(<?= $fb['id'] ?>)">
                            <img src="<?= asset('img/banners/' . $fb['imagen']) ?>"
                                 alt="<?= e($fb['titulo'] ?? 'Banner') ?>"
                                 loading="lazy"
                                 class="footer-banner-item__img">
                        </a>
                    <?php else: ?>
                        <img src="<?= asset('img/banners/' . $fb['imagen']) ?>"
                             alt="<?= e($fb['titulo'] ?? 'Banner') ?>"
                             loading="lazy"
                             class="footer-banner-item__img">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
```

---

## CAMBIO 3: Banner hero — Cambiar background-image por img tag

**Archivo:** `views/public/home.php`

**Cambio:** En la sección "Banners visibles incluso con countdown activo" (aprox línea 98-133), reemplazar el sistema de `background-image` en divs por tags `<img>` reales. Los divs con `background-image` colapsaban a 0px en navegadores móviles.

**Buscar el bloque que comienza con:**
```php
<!-- Banners (visibles incluso con countdown activo) -->
```

**Reemplazar con (SOLO las clases `--img`, SIN las clases base `banner-slider` ni `banner-slide`):**

```php
<!-- Banners (visibles incluso con countdown activo) -->
<?php if (!empty($banners) && !empty($proximaFecha)): ?>
<section class="section section--banners">
    <div class="container">
        <div class="banner-slider--img" id="bannerSliderBelow">
            <?php foreach ($banners as $i => $banner): ?>
                <div class="banner-slide--img <?= $i === 0 ? 'banner-slide--active' : '' ?>">
                    <?php if (!empty($banner['url'])): ?>
                        <a href="<?= e($banner['url']) ?>"
                           data-banner-id="<?= $banner['id'] ?>"
                           onclick="if(typeof trackBanner==='function')trackBanner(<?= $banner['id'] ?>)">
                            <img src="<?= asset('img/banners/' . $banner['imagen']) ?>"
                                 alt="<?= e($banner['titulo'] ?? 'Banner') ?>"
                                 class="banner-slide__img"
                                 loading="lazy">
                        </a>
                    <?php else: ?>
                        <img src="<?= asset('img/banners/' . $banner['imagen']) ?>"
                             alt="<?= e($banner['titulo'] ?? 'Banner') ?>"
                             class="banner-slide__img"
                             loading="lazy">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (count($banners) > 1): ?>
                <button class="banner-control banner-control--prev" onclick="changeBannerBelow(-1)" aria-label="Anterior">&#8249;</button>
                <button class="banner-control banner-control--next" onclick="changeBannerBelow(1)" aria-label="Siguiente">&#8250;</button>
                <div class="banner-dots">
                    <?php foreach ($banners as $i => $b): ?>
                        <button class="banner-dot <?= $i === 0 ? 'banner-dot--active' : '' ?>"
                                onclick="goToBannerBelow(<?= $i ?>)" aria-label="Banner <?= $i + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>
```

**IMPORTANTE:** Las clases son `banner-slider--img` y `banner-slide--img` (con sufijo `--img`), NO `banner-slider banner-slider--img`. La clase base `.banner-slide` tiene `position: absolute` y `opacity: 0` que entran en conflicto.

---

## CAMBIO 4: Selectores JavaScript del banner slider

**Archivo:** `views/public/home.php` (script inline al final) y `assets/js/app.js`

**Cambio en el script inline de home.php:**

```diff
- var slides = document.querySelectorAll('#bannerSliderBelow .banner-slide');
+ var slides = document.querySelectorAll('#bannerSliderBelow .banner-slide--img');
```

**Cambio en app.js** (función del banner slider below):

```diff
- var slides = document.querySelectorAll("#bannerSliderBelow .banner-slide");
+ var slides = document.querySelectorAll("#bannerSliderBelow .banner-slide--img");
```

**Motivo:** Los selectores apuntaban a `.banner-slide` pero las clases ahora son `.banner-slide--img`.

---

## CAMBIO 5: CSS — Nuevos estilos de banners

**Archivo:** `assets/css/main.css`

**Agregar al final del archivo los siguientes bloques de CSS:**

### 5a. Estilos para banner slider con img (hero debajo del countdown)

```css
/* ── Banner Slider con <img> (más confiable en móvil) ── */
.banner-slider--img {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    margin: 1.5rem 0;
    background: #f0f0f0;
}

.banner-slide--img {
    display: none;
    position: relative;
    width: 100%;
    text-align: center;
    background: transparent;
}

.banner-slide--img.banner-slide--active {
    display: block;
}

.banner-slide__img {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 12px;
}

@media (max-width: 768px) {
    .banner-slider--img {
        border-radius: 8px;
        margin: 0.75rem 0;
    }
    .banner-slide__img {
        border-radius: 8px;
    }
}
```

### 5b. Estilos para banners footer y sidebar

```css
/* ── Banner Footer ── */
.footer-banners {
    background: #f8f9fa;
    padding: 1.5rem 0;
    border-top: 1px solid #e9ecef;
}

.footer-banners__grid {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.footer-banner-item {
    max-width: 728px;
    width: 100%;
    text-align: center;
}

.footer-banner-item__img {
    width: 100%;
    height: auto;
    max-height: 120px;
    object-fit: contain;
    border-radius: 8px;
    transition: opacity 0.3s;
}

.footer-banner-item a:hover .footer-banner-item__img {
    opacity: 0.9;
}

/* ── Banner Sidebar ── */
.sidebar-banners {
    margin-top: 1.5rem;
}

.sidebar-banner-item {
    margin-bottom: 1rem;
}

.sidebar-banner-item__img {
    width: 100%;
    height: auto;
    max-height: 300px;
    object-fit: contain;
    border-radius: 8px;
    transition: opacity 0.3s;
}

.sidebar-banner-item a:hover .sidebar-banner-item__img {
    opacity: 0.9;
}

@media (max-width: 768px) {
    .footer-banners {
        padding: 1rem 0;
    }
    .footer-banner-item__img {
        max-height: 80px;
    }
    .sidebar-banner-item__img {
        max-height: 200px;
    }
}
```

### 5c. Fix de visibilidad forzada

```css
/* ── FIX-BANNER-VISIBILITY: Garantizar visibilidad del banner hero ── */
.section.section--banners {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    overflow: visible !important;
}

.section--banners .container {
    display: block !important;
    visibility: visible !important;
}

.section--banners .banner-slider--img {
    display: block !important;
    visibility: visible !important;
    position: relative !important;
    width: 100% !important;
    overflow: hidden;
    border-radius: 12px;
    margin: 1rem 0;
}

.section--banners .banner-slide--img {
    display: none;
    position: relative !important;
    width: 100% !important;
    opacity: 1 !important;
    height: auto !important;
    top: auto !important;
    left: auto !important;
}

.section--banners .banner-slide--img.banner-slide--active {
    display: block !important;
}

.section--banners .banner-slide__img {
    width: 100% !important;
    height: auto !important;
    display: block !important;
    border-radius: 12px;
    max-width: 100%;
}

@media (max-width: 768px) {
    .section--banners .banner-slider--img {
        border-radius: 8px;
        margin: 0.75rem 0;
    }
    .section--banners .banner-slide__img {
        border-radius: 8px;
    }
}
```

---

## CAMBIO 6: Meta tag deprecado (PENDIENTE)

**Archivo:** `views/layouts/public.php`

**Cambio:**

```diff
- <meta name="apple-mobile-web-app-capable" content="yes">
+ <meta name="mobile-web-app-capable" content="yes">
```

**Motivo:** `apple-mobile-web-app-capable` está deprecado en Chrome/Edge. El nuevo estándar es `mobile-web-app-capable`.

---

## LIMPIEZA REQUERIDA

### Eliminar archivos .bak de producción

Se crearon backups automáticos durante los fixes. Eliminar:

- `views/public/home.php.bak.*`
- `views/partials/footer.php.bak.*`
- `views/layouts/public.php.bak.*` (si existe)
- `assets/css/main.css.bak.*`
- `assets/js/app.js.bak.*`

### Eliminar scripts de diagnóstico (si quedan)

- `diagnostico-integral.php`
- `diagnostico-banners.php`
- `diag-footer.php`
- `diag-final.php`
- `fix-integral.php`
- `fix-banner-movil.php`
- `fix-banner-definitivo.php`
- `fix-visibilidad.php`
- `test-banner-movil.php`
- `rastreo-fechas.php`

---

## NOTA SOBRE CSS DUPLICADO

El main.css puede tener reglas duplicadas de `.banner-slide` acumuladas de los distintos fixes. Claude Code debería consolidar todos los estilos de banners en un solo bloque limpio, eliminando duplicados y reglas que ya no aplican (las de `.banner-slide` con `background-image` que fueron reemplazadas por `.banner-slide--img` con `<img>`).

Las reglas que DEBEN mantenerse son:
- `.banner-slider--img` y sus hijos (el sistema nuevo con `<img>`)
- `.footer-banners` y sus hijos
- `.sidebar-banners` y sus hijos
- `.section--banners` (fix de visibilidad)

Las reglas que PUEDEN LIMPIARSE son:
- `.banner-slider` (sin `--img`) con `height: 350px` — solo se usa en el hero slider original (elseif sin fecha)
- Reglas duplicadas de `.banner-slide` con `background-size: contain` y `background-color: #e8a020`
- Override móvil con `aspect-ratio: auto !important` y `padding-bottom: 33%` — ya no necesario con `<img>`
