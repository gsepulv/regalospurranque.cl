<?php $pageType = 'home'; ?>
<!-- Hero -->
<?php if (!empty($proximaFecha)): ?>
    <?php
    $heroColor = !empty($proximaFecha['color']) ? $proximaFecha['color'] : '#e53e3e';
    $r = hexdec(substr($heroColor, 1, 2));
    $g = hexdec(substr($heroColor, 3, 2));
    $b = hexdec(substr($heroColor, 5, 2));
    $heroTexto = !empty($proximaFecha['color_texto']) ? $proximaFecha['color_texto'] : '#ffffff';
    $heroColorDark = sprintf('#%02x%02x%02x', max(0, (int)($r * 0.7)), max(0, (int)($g * 0.7)), max(0, (int)($b * 0.7)));
    ?>
    <section class="hero hero--countdown" style="background: linear-gradient(135deg, <?= $heroColor ?> 0%, <?= $heroColorDark ?> 100%); --hero-text-color: <?= $heroTexto ?>;">
        <div class="container hero__content">
            <?php if (!empty($proximaFecha['icono'])): ?>
                <span class="hero__fecha-icon"><?= $proximaFecha['icono'] ?></span>
            <?php endif; ?>
            <h1><?= e($proximaFecha['nombre']) ?>. Celebra con un detalle especial.</h1>
            <div class="hero__countdown" id="heroCountdown" data-target="<?= e($proximaFecha['fecha_inicio']) ?>">
                <div class="countdown__item">
                    <span class="countdown__number" id="cdDias">00</span>
                    <span class="countdown__label">Dias</span>
                </div>
                <div class="countdown__item">
                    <span class="countdown__number" id="cdHoras">00</span>
                    <span class="countdown__label">Horas</span>
                </div>
                <div class="countdown__item">
                    <span class="countdown__number" id="cdMin">00</span>
                    <span class="countdown__label">Min</span>
                </div>
                <div class="countdown__item">
                    <span class="countdown__number" id="cdSeg">00</span>
                    <span class="countdown__label">Seg</span>
                </div>
            </div>
            <form action="<?= url('/buscar') ?>" method="GET" class="hero__search-form">
                <input type="text" name="q" class="hero__search-input" placeholder="¿Qué estás buscando? Flores, chocolates, spa..." required>
                <button type="submit" class="hero__search-btn-orange">Buscar</button>
            </form>
        </div>
    </section>
<?php elseif (!empty($banners)): ?>
    <section class="hero hero--banner">
        <div class="hero-slider" id="heroSlider">
            <?php foreach ($banners as $i => $banner): ?>
                <div class="hero-slide <?= $i === 0 ? 'hero-slide--active' : '' ?>"
                     style="background-image: url('<?= asset('img/banners/' . $banner['imagen']) ?>')">
                    <div class="hero-slide__overlay"></div>
                    <div class="container hero-slide__content">
                        <?php if (!empty($banner['titulo'])): ?>
                            <h2><?= e($banner['titulo']) ?></h2>
                        <?php endif; ?>
                        <?php if (!empty($banner['url'])): ?>
                            <a href="<?= e($banner['url']) ?>"
                               class="btn btn--primary btn--lg"
                               data-banner-id="<?= $banner['id'] ?>"
                               onclick="trackBanner(<?= $banner['id'] ?>)">Ver mas</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($banners) > 1): ?>
            <button class="hero-control hero-control--prev" onclick="changeSlide(-1)" aria-label="Anterior">&#8249;</button>
            <button class="hero-control hero-control--next" onclick="changeSlide(1)" aria-label="Siguiente">&#8250;</button>
            <div class="hero-dots">
                <?php foreach ($banners as $i => $b): ?>
                    <button class="hero-dot <?= $i === 0 ? 'hero-dot--active' : '' ?>"
                            onclick="goToSlide(<?= $i ?>)" aria-label="Slide <?= $i + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="hero-search">
            <div class="container">
                <form action="<?= url('/buscar') ?>" method="GET" class="search-form">
                    <input type="text" name="q" class="search-input" placeholder="Buscar comercios, productos o servicios..." required>
                    <button type="submit" class="btn btn--primary">Buscar</button>
                </form>
            </div>
        </div>
    </section>
<?php else: ?>
    <section class="hero hero--countdown">
        <div class="container hero__content">
            <span class="hero__badge">&#127873; Regalos Purranque</span>
            <h1>Descubre los mejores comercios de Purranque</h1>
            <p class="hero__subtitle">Tu directorio comercial local. Encuentra tiendas, restaurantes, servicios y mucho mas.</p>
            <form action="<?= url('/buscar') ?>" method="GET" class="hero__search-form">
                <input type="text" name="q" class="hero__search-input" placeholder="Buscar comercios, productos o servicios..." required>
                <button type="submit" class="hero__search-btn-orange">Buscar</button>
            </form>
        </div>
    </section>
<?php endif; ?>

<!-- Banners (visibles incluso con countdown activo) -->
<?php if (!empty($banners) && !empty($proximaFecha)): ?>
<section class="section section--banners">
    <div class="container">
        <div class="banner-slider" id="bannerSliderBelow">
            <?php foreach ($banners as $i => $banner): ?>
                <div class="banner-slide <?= $i === 0 ? 'banner-slide--active' : '' ?>"
                     style="background-image: url('<?= asset('img/banners/' . $banner['imagen']) ?>')">
                    <div class="banner-slide__overlay"></div>
                    <div class="banner-slide__content">
                        <?php if (!empty($banner['titulo'])): ?>
                            <h2><?= e($banner['titulo']) ?></h2>
                        <?php endif; ?>
                        <?php if (!empty($banner['url'])): ?>
                            <a href="<?= e($banner['url']) ?>"
                               class="btn btn--primary btn--lg"
                               data-banner-id="<?= $banner['id'] ?>"
                               onclick="trackBanner(<?= $banner['id'] ?>)">Ver mas</a>
                        <?php endif; ?>
                    </div>
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

<!-- Categorías -->
<?php if (!empty($categorias)): ?>
<section class="section section-categorias-home">
    <div class="container">
        <h2 class="section__title">Categorías</h2>
        <div class="category-grid">
            <?php foreach ($categorias as $cat): ?>
                <a href="<?= url('/categoria/' . $cat['slug']) ?>" class="category-card">
                    <span class="category-card__icon"><?= !empty($cat['icono']) ? $cat['icono'] : mb_substr($cat['nombre'], 0, 1) ?></span>
                    <span class="category-card__name"><?= e($cat['nombre']) ?></span>
                    <span class="category-card__count"><?= (int)$cat['comercios_count'] ?> comercio<?= $cat['comercios_count'] != 1 ? 's' : '' ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Celebraciones Personales -->
<?php if (!empty($fechasPersonales)): ?>
<section class="section section--alt section-celebraciones-home">
    <div class="container">
        <h2 class="section__title">&#127881; Celebraciones Personales</h2>
        <p class="text-center text-muted mb-4">Encuentra el regalo ideal para cada momento especial de tu vida</p>
        <div class="celebracion-grid">
            <?php foreach ($fechasPersonales as $fe): ?>
                <?php $feColor = !empty($fe['color']) ? $fe['color'] : '#e53e3e'; ?>
                <a href="<?= url('/fecha/' . $fe['slug']) ?>" class="celebracion-card" style="--card-color: <?= $feColor ?>;">
                    <span class="celebracion-card__icon"><?= !empty($fe['icono']) ? $fe['icono'] : '&#127873;' ?></span>
                    <span class="celebracion-card__name"><?= e($fe['nombre']) ?></span>
                    <?php if (!empty($fe['descripcion'])): ?>
                        <span class="celebracion-card__desc"><?= e(truncate($fe['descripcion'], 80)) ?></span>
                    <?php endif; ?>
                    <span class="celebracion-card__count"><?= (int)$fe['comercios_count'] ?> comercio<?= $fe['comercios_count'] != 1 ? 's' : '' ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3 mobile-only">
            <a href="<?= url('/celebraciones') ?>" class="btn btn--outline btn--sm">Ver todas las celebraciones &rarr;</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Fechas del Calendario -->
<?php if (!empty($fechasCalendario)): ?>
<section class="section section-fechas-home">
    <div class="container">
        <h2 class="section__title">&#128197; Fechas del Calendario</h2>
        <p class="text-center text-muted mb-4">Ofertas especiales para cada fecha importante del ano</p>
        <div class="celebracion-grid">
            <?php foreach ($fechasCalendario as $fe): ?>
                <?php $feColor = !empty($fe['color']) ? $fe['color'] : '#e53e3e'; ?>
                <a href="<?= url('/fecha/' . $fe['slug']) ?>" class="celebracion-card" style="--card-color: <?= $feColor ?>;" <?php if (!empty($fe['fecha_inicio'])): ?>title="<?= fecha_es($fe['fecha_inicio'], 'd/m') ?><?php if (!empty($fe['fecha_fin']) && $fe['fecha_fin'] !== $fe['fecha_inicio']): ?> — <?= fecha_es($fe['fecha_fin'], 'd/m') ?><?php endif; ?>"<?php endif; ?>>
                    <span class="celebracion-card__icon"><?= !empty($fe['icono']) ? $fe['icono'] : '&#128197;' ?></span>
                    <span class="celebracion-card__name"><?= e($fe['nombre']) ?></span>
                    <?php if (!empty($fe['fecha_inicio'])): ?>
                        <span class="celebracion-card__date"><?= fecha_es($fe['fecha_inicio'], 'd/m') ?><?php if (!empty($fe['fecha_fin']) && $fe['fecha_fin'] !== $fe['fecha_inicio']): ?> — <?= fecha_es($fe['fecha_fin'], 'd/m') ?><?php endif; ?></span>
                    <?php endif; ?>
                    <span class="celebracion-card__count"><?= (int)$fe['comercios_count'] ?> comercio<?= $fe['comercios_count'] != 1 ? 's' : '' ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3 mobile-only">
            <a href="<?= url('/celebraciones') ?>" class="btn btn--outline btn--sm">Ver todas las fechas &rarr;</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Eventos Comerciales -->
<?php if (!empty($fechasComerciales)): ?>
<section class="section section--alt section-eventos-home">
    <div class="container">
        <h2 class="section__title">&#128176; Eventos Comerciales</h2>
        <p class="text-center text-muted mb-4">Sumate a los grandes eventos de descuentos del ano</p>
        <div class="celebracion-grid">
            <?php foreach ($fechasComerciales as $fe): ?>
                <?php $feColor = !empty($fe['color']) ? $fe['color'] : '#1e293b'; ?>
                <a href="<?= url('/fecha/' . $fe['slug']) ?>" class="celebracion-card" style="--card-color: <?= $feColor ?>;" <?php if (!empty($fe['fecha_inicio'])): ?>title="<?= fecha_es($fe['fecha_inicio'], 'd/m') ?><?php if (!empty($fe['fecha_fin']) && $fe['fecha_fin'] !== $fe['fecha_inicio']): ?> — <?= fecha_es($fe['fecha_fin'], 'd/m') ?><?php endif; ?>"<?php endif; ?>>
                    <span class="celebracion-card__icon"><?= !empty($fe['icono']) ? $fe['icono'] : '&#128176;' ?></span>
                    <span class="celebracion-card__name"><?= e($fe['nombre']) ?></span>
                    <?php if (!empty($fe['fecha_inicio'])): ?>
                        <span class="celebracion-card__date"><?= fecha_es($fe['fecha_inicio'], 'd/m') ?><?php if (!empty($fe['fecha_fin']) && $fe['fecha_fin'] !== $fe['fecha_inicio']): ?> — <?= fecha_es($fe['fecha_fin'], 'd/m') ?><?php endif; ?></span>
                    <?php endif; ?>
                    <span class="celebracion-card__count"><?= (int)$fe['comercios_count'] ?> comercio<?= $fe['comercios_count'] != 1 ? 's' : '' ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Comercios Destacados -->
<?php if (!empty($comercios)): ?>
<section class="section section-comercios-home">
    <div class="container">
        <h2 class="section__title">Comercios Destacados</h2>
        <div class="grid grid--4">
            <?php foreach ($comercios as $com): ?>
                <a href="<?= url('/comercio/' . $com['slug']) ?>" class="card<?= in_array($com['plan'] ?? '', ['sponsor','premium']) ? ' card--' . $com['plan'] : '' ?>">
                    <?php if (!empty($com['portada'])): ?>
                        <?= picture('img/portadas/' . $com['portada'], $com['nombre'], 'card__img', true, 400, 267) ?>
                    <?php else: ?>
                        <div class="card__img card__img--placeholder">
                            <?= mb_substr($com['nombre'], 0, 1) ?>
                        </div>
                    <?php endif; ?>
                    <div class="card__body">
                        <?php include BASE_PATH . '/views/partials/card-badges.php'; ?>
                        <h3 class="card__title"><?= e($com['nombre']) ?></h3>
                        <?php if (!empty($com['categorias_nombres'])): ?>
                            <p class="card__text card__text--small"><?= e($com['categorias_nombres']) ?></p>
                        <?php endif; ?>
                        <?php if ($com['calificación_promedio']): ?>
                            <div class="rating-small">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?= $i <= round($com['calificación_promedio']) ? 'star--filled' : '' ?>">&#9733;</span>
                                <?php endfor; ?>
                                <span class="text-muted">(<?= $com['total_resenas'] ?>)</span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($com['direccion'])): ?>
                            <p class="card__text card__text--small">&#128205; <?= e(truncate($com['direccion'], 60)) ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
            <a href="<?= url('/comercios') ?>" class="btn btn--outline">Ver todos los comercios</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Noticias -->
<?php if (!empty($noticias)): ?>
<section class="section section-noticias-home">
    <div class="container">
        <h2 class="section__title">Últimas Noticias</h2>
        <div class="grid grid--3">
            <?php foreach ($noticias as $not): ?>
                <a href="<?= url('/noticia/' . $not['slug']) ?>" class="card">
                    <?php if (!empty($not['imagen'])): ?>
                        <?= picture('img/noticias/' . $not['imagen'], $not['titulo'], 'card__img', true, 400, 267) ?>
                    <?php endif; ?>
                    <div class="card__body">
                        <h3 class="card__title"><?= e($not['titulo']) ?></h3>
                        <?php if (!empty($not['extracto'])): ?>
                            <p class="card__text"><?= e(truncate($not['extracto'], 120)) ?></p>
                        <?php endif; ?>
                        <span class="card__date"><?= fecha_es($not['fecha_publicacion'], 'd/m/Y') ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
            <a href="<?= url('/noticias') ?>" class="btn btn--outline">Ver todas las noticias</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Scripts del hero slider -->
<?php if (!empty($banners) && empty($proximaFecha) && count($banners) > 1): ?>
<script>
(function() {
    var currentSlide = 0;
    var slides = document.querySelectorAll('.hero-slide');
    var dots = document.querySelectorAll('.hero-dot');
    if (slides.length <= 1) return;

    function showSlide(n) {
        slides.forEach(function(s) { s.classList.remove('hero-slide--active'); });
        dots.forEach(function(d) { d.classList.remove('hero-dot--active'); });
        currentSlide = ((n % slides.length) + slides.length) % slides.length;
        slides[currentSlide].classList.add('hero-slide--active');
        dots[currentSlide].classList.add('hero-dot--active');
    }

    window.changeSlide = function(dir) { showSlide(currentSlide + dir); };
    window.goToSlide = function(n) { showSlide(n); };

    setInterval(function() { showSlide(currentSlide + 1); }, 5000);
})();
</script>
<?php endif; ?>

<?php if (!empty($banners) && !empty($proximaFecha) && count($banners) > 1): ?>
<script>
(function() {
    var currentB = 0;
    var slides = document.querySelectorAll('#bannerSliderBelow .banner-slide');
    var dots = document.querySelectorAll('#bannerSliderBelow .banner-dot');
    if (slides.length <= 1) return;

    function showBanner(n) {
        slides.forEach(function(s) { s.classList.remove('banner-slide--active'); });
        dots.forEach(function(d) { d.classList.remove('banner-dot--active'); });
        currentB = ((n % slides.length) + slides.length) % slides.length;
        slides[currentB].classList.add('banner-slide--active');
        if (dots[currentB]) dots[currentB].classList.add('banner-dot--active');
    }

    window.changeBannerBelow = function(dir) { showBanner(currentB + dir); };
    window.goToBannerBelow = function(n) { showBanner(n); };

    setInterval(function() { showBanner(currentB + 1); }, 6000);
})();
</script>
<?php endif; ?>

<script>
function trackBanner(bannerId) {
    fetch('<?= url('/api/banner-track') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({banner_id: bannerId, tipo: 'click'})
    });
}
</script>
