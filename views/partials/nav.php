<?php
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$isActive = function(string $path) use ($currentUri): string {
    if ($path === '/') {
        return $currentUri === '/' ? 'active' : '';
    }
    return str_starts_with($currentUri, $path) ? 'active' : '';
};
?>
<nav class="nav" id="mainNav">
    <div class="container nav__container">
        <a href="<?= url('/') ?>" class="nav__logo">
            <span class="nav__emoji">&#127873;</span>
            <strong><?= e(SITE_NAME) ?></strong>
        </a>

        <form action="<?= url('/buscar') ?>" method="GET" class="nav__search">
            <input type="text" name="q" class="nav__search-input" placeholder="Buscar comercios, regalos..." aria-label="Buscar">
            <button type="submit" class="nav__search-btn" aria-label="Buscar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
        </form>

        <button class="nav__toggle" id="navToggle" aria-label="Menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <ul class="nav__links" id="navLinks">
            <li><a href="<?= url('/categorias') ?>" class="<?= $isActive('/categoria') ?>">Categorías</a></li>
            <li><a href="<?= url('/comercios') ?>" class="<?= $isActive('/comercio') ?>">Comercios</a></li>
            <li><a href="<?= url('/noticias') ?>" class="<?= $isActive('/noticias') . $isActive('/noticia') ?>">Noticias</a></li>
            <li><a href="<?= url('/celebraciones') ?>" class="<?= $isActive('/celebracion') . $isActive('/fecha') ?>">Celebraciones</a></li>
        </ul>
    </div>
</nav>
