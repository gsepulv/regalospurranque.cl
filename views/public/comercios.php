<?php
/**
 * Listado de todos los comercios
 * Variables: $comercios, $categorias, $banners, $total, $currentPage, $totalPages, $baseUrl, $breadcrumbs
 */
$pageType = 'comercio';
?>

<section class="section">
    <div class="container">
        <h1 class="section__title">Comercios</h1>
        <p class="text-center text-muted mb-4">Directorio completo de comercios y servicios en Purranque (<?= $total ?> comercio<?= $total != 1 ? 's' : '' ?>)</p>

        <?php if (!empty($comercios)): ?>
            <div class="grid grid--auto">
                <?php foreach ($comercios as $com): ?>
                    <a href="<?= url('/comercio/' . $com['slug']) ?>" class="card<?= in_array($com['plan'] ?? '', ['sponsor','premium']) ? ' card--' . $com['plan'] : '' ?>">
                        <?php if (!empty($com['portada'])): ?>
                            <img src="<?= asset('img/portadas/' . $com['portada']) ?>"
                                 alt="<?= e($com['nombre']) ?>"
                                 class="card__img" loading="lazy">
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
                            <?php if (!empty($com['calificación_promedio'])): ?>
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

            <?php include BASE_PATH . '/views/partials/pagination.php'; ?>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-state__emoji">&#127978;</span>
                <h3>Aun no hay comercios registrados</h3>
                <p>Los comercios se mostraran aqui cuando esten disponibles.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
