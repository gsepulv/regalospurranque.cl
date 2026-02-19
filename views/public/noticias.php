<?php
/**
 * Vista listado de noticias
 * Variables: $noticias, $destacadas, $banners, $total, $currentPage, $totalPages, $baseUrl
 */
?>

<section class="section">
    <div class="container">

        <div class="page-header">
            <h1>Noticias</h1>
            <p class="text-muted">Mantente informado con las últimas novedades de Purranque</p>
        </div>

        <div class="comercio-layout">

            <!-- Contenido principal -->
            <div class="comercio-main">
                <?php if (!empty($noticias)): ?>
                    <div class="noticias-list">
                        <?php foreach ($noticias as $not): ?>
                            <article class="noticia-card">
                                <?php if (!empty($not['imagen'])): ?>
                                    <a href="<?= url('/noticia/' . $not['slug']) ?>" class="noticia-card__img-link">
                                        <img src="<?= asset('img/noticias/' . $not['imagen']) ?>"
                                             alt="<?= e($not['titulo']) ?>"
                                             class="noticia-card__img"
                                             loading="lazy">
                                    </a>
                                <?php endif; ?>
                                <div class="noticia-card__content">
                                    <div class="noticia-card__meta">
                                        <?php if ($not['destacada']): ?>
                                            <span class="badge badge--warning">Destacada</span>
                                        <?php endif; ?>
                                        <span class="text-muted text-sm"><?= fecha_es($not['fecha_publicacion'], 'd/m/Y') ?></span>
                                        <?php if (!empty($not['autor'])): ?>
                                            <span class="text-muted text-sm">· <?= e($not['autor']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <h2 class="noticia-card__title">
                                        <a href="<?= url('/noticia/' . $not['slug']) ?>"><?= e($not['titulo']) ?></a>
                                    </h2>
                                    <?php if (!empty($not['extracto'])): ?>
                                        <p class="noticia-card__extracto"><?= e($not['extracto']) ?></p>
                                    <?php endif; ?>
                                    <a href="<?= url('/noticia/' . $not['slug']) ?>" class="btn btn--outline btn--sm">Leer más &rarr;</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <?php include BASE_PATH . '/views/partials/pagination.php'; ?>

                <?php else: ?>
                    <div class="empty-state">
                        <p>No hay noticias publicadas todavía.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="comercio-sidebar">

                <!-- Noticias destacadas -->
                <?php if (!empty($destacadas)): ?>
                    <div class="card sidebar-card">
                        <div class="card__body">
                            <h3 class="sidebar-card__title">Noticias destacadas</h3>
                            <?php foreach ($destacadas as $dest): ?>
                                <a href="<?= url('/noticia/' . $dest['slug']) ?>" class="noticia-mini">
                                    <?php if (!empty($dest['imagen'])): ?>
                                        <img src="<?= asset('img/noticias/' . $dest['imagen']) ?>"
                                             alt="<?= e($dest['titulo']) ?>"
                                             class="noticia-mini__img"
                                             loading="lazy">
                                    <?php endif; ?>
                                    <div class="noticia-mini__content">
                                        <h4><?= e(truncate($dest['titulo'], 60)) ?></h4>
                                        <span class="text-muted" style="font-size:0.75rem"><?= fecha_es($dest['fecha_publicacion'], 'd/m/Y') ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Banners -->
                <?php foreach ($banners as $banner): ?>
                    <div class="sidebar-banner" data-banner-id="<?= $banner['id'] ?>">
                        <a href="<?= e($banner['url']) ?>" target="_blank" rel="noopener" onclick="trackBanner(<?= $banner['id'] ?>)">
                            <img src="<?= asset('img/banners/' . $banner['imagen']) ?>" alt="<?= e($banner['titulo'] ?? 'Publicidad') ?>" loading="lazy">
                        </a>
                    </div>
                <?php endforeach; ?>
            </aside>
        </div>
    </div>
</section>

<script>
function trackBanner(bannerId) {
    fetch('<?= url('/api/banner-track') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({banner_id: bannerId, tipo: 'click'})
    });
}
</script>
