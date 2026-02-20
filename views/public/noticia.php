<?php
/**
 * Vista detalle de noticia
 * Variables: $noticia, $relacionadas, $banners
 */
$pageType = 'noticia';
$seoUrl = url('/noticia/' . $noticia['slug']);
?>

<article class="section">
    <div class="container">
        <div class="comercio-layout">

            <!-- Contenido principal -->
            <div class="comercio-main">

                <!-- Cabecera del articulo -->
                <header class="article-header">
                    <?php if ($noticia['destacada']): ?>
                        <span class="badge badge--warning mb-2">Destacada</span>
                    <?php endif; ?>
                    <h1 class="article-title"><?= e($noticia['titulo']) ?></h1>
                    <div class="article-meta">
                        <span class="text-muted"><?= fecha_es($noticia['fecha_publicacion'], 'd/m/Y') ?></span>
                        <?php if (!empty($noticia['autor'])): ?>
                            <span class="text-muted">· Por <?= e($noticia['autor']) ?></span>
                        <?php endif; ?>
                    </div>
                </header>

                <?php // Profiles: after_title position ?>
                <?php $position = 'after_title'; include BASE_PATH . '/views/partials/social-profiles.php'; ?>

                <?php // Share: above_content position ?>
                <?php
                $sharePageType = 'noticia';
                $sharePosition = 'above_content';
                $shareTitle = $noticia['titulo'];
                $shareDescription = $noticia['extracto'] ?? '';
                $shareUrl = $seoUrl;
                $shareSlug = $noticia['slug'];
                include BASE_PATH . '/views/partials/share-buttons.php';
                ?>

                <!-- Imagen principal -->
                <?php if (!empty($noticia['imagen'])): ?>
                    <div class="article-image">
                        <?= picture('img/noticias/' . $noticia['imagen'], $noticia['titulo'], '', false, 800, 450) ?>
                    </div>
                <?php endif; ?>

                <!-- Contenido -->
                <div class="article-content tinymce-content">
                    <?= $noticia['contenido'] ?>
                </div>

                <!-- Categorías y fechas -->
                <?php if (!empty($noticia['categorias'])): ?>
                    <div class="article-tags">
                        <strong>Categorías:</strong>
                        <?php foreach ($noticia['categorias'] as $cat): ?>
                            <a href="<?= url('/categoria/' . $cat['slug']) ?>" class="badge badge--primary"><?= e($cat['nombre']) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($noticia['fechas'])): ?>
                    <div class="article-tags">
                        <strong>Fechas especiales:</strong>
                        <?php foreach ($noticia['fechas'] as $fe): ?>
                            <a href="<?= url('/fecha/' . $fe['slug']) ?>" class="badge badge--success"><?= e($fe['nombre']) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Compartir: below_content -->
                <?php
                $sharePageType = 'noticia';
                $sharePosition = 'below_content';
                $shareTitle = $noticia['titulo'];
                $shareDescription = $noticia['extracto'] ?? '';
                $shareUrl = $seoUrl;
                $shareSlug = $noticia['slug'];
                include BASE_PATH . '/views/partials/share-buttons.php';
                ?>

                <!-- Noticias relacionadas -->
                <?php if (!empty($relacionadas)): ?>
                    <div class="article-related">
                        <h3>Noticias relacionadas</h3>
                        <div class="grid grid--3">
                            <?php foreach ($relacionadas as $rel): ?>
                                <a href="<?= url('/noticia/' . $rel['slug']) ?>" class="card">
                                    <?php if (!empty($rel['imagen'])): ?>
                                        <?= picture('img/noticias/' . $rel['imagen'], $rel['titulo'], 'card__img', true, 400, 267) ?>
                                    <?php endif; ?>
                                    <div class="card__body">
                                        <h3 class="card__title"><?= e(truncate($rel['titulo'], 60)) ?></h3>
                                        <span class="card__date"><?= fecha_es($rel['fecha_publicacion'], 'd/m/Y') ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Sidebar -->
            <aside class="comercio-sidebar">
                <?php // Profiles: sidebar position ?>
                <?php $position = 'sidebar'; include BASE_PATH . '/views/partials/social-profiles.php'; ?>

                <?php // Share: sidebar position ?>
                <?php
                $sharePosition = 'sidebar';
                include BASE_PATH . '/views/partials/share-buttons.php';
                ?>

                <?php foreach ($banners as $banner): ?>
                    <div class="sidebar-banner" data-banner-id="<?= $banner['id'] ?>">
                        <a href="<?= e($banner['url']) ?>" target="_blank" rel="noopener" onclick="trackBanner(<?= $banner['id'] ?>)">
                            <?= picture('img/banners/' . $banner['imagen'], $banner['titulo'] ?? 'Publicidad', '', true) ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </aside>
        </div>
    </div>
</article>

