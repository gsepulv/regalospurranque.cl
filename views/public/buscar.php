<?php
/**
 * Vista de búsqueda y listado de comercios
 * Variables: $comercios, $categorias, $fechas, $banners, $filters, $total, $currentPage, $totalPages, $baseUrl, $queryParams
 */
?>

<section class="section">
    <div class="container">

        <!-- Cabecera y filtros -->
        <div class="search-header">
            <h1>Buscar Comercios</h1>

            <form action="<?= url('/buscar') ?>" method="GET" class="search-filters">
                <!-- Barra de búsqueda -->
                <div class="search-input-group">
                    <input type="text"
                           name="q"
                           value="<?= e($filters['query'] ?? '') ?>"
                           placeholder="Buscar por nombre, descripción o dirección..."
                           class="form-control">
                    <button type="submit" class="btn btn--primary">Buscar</button>
                </div>

                <!-- Filtros -->
                <div class="filters-row">
                    <select name="categoria" class="form-control filter-select">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($filters['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="fecha" class="form-control filter-select">
                        <option value="">Todas las fechas</option>
                        <?php foreach ($fechas as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= ($filters['fecha_id'] ?? '') == $f['id'] ? 'selected' : '' ?>>
                                <?= e($f['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="plan" class="form-control filter-select">
                        <option value="">Todos los planes</option>
                        <option value="sponsor" <?= ($filters['plan'] ?? '') === 'sponsor' ? 'selected' : '' ?>>Sponsor</option>
                        <option value="premium" <?= ($filters['plan'] ?? '') === 'premium' ? 'selected' : '' ?>>Premium</option>
                        <option value="basico" <?= ($filters['plan'] ?? '') === 'basico' ? 'selected' : '' ?>>Básico</option>
                    </select>

                    <label class="checkbox-filter">
                        <input type="checkbox" name="destacado" value="1" <?= !empty($filters['destacado']) ? 'checked' : '' ?>>
                        Solo destacados
                    </label>

                    <?php if (!empty($queryParams)): ?>
                        <a href="<?= url('/buscar') ?>" class="btn btn--outline btn--sm">Limpiar filtros</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Resultados -->
        <div class="search-results">
            <p class="results-count">
                <?php if ($total > 0): ?>
                    <?= $total ?> resultado<?= $total != 1 ? 's' : '' ?> encontrado<?= $total != 1 ? 's' : '' ?>
                <?php else: ?>
                    No se encontraron resultados
                <?php endif; ?>
            </p>

            <?php if (!empty($comercios)): ?>
                <div class="grid grid--auto">
                    <?php $bannerIndex = 0; ?>
                    <?php foreach ($comercios as $i => $com): ?>
                        <a href="<?= url('/comercio/' . $com['slug']) ?>" class="card">
                            <?php if (!empty($com['portada'])): ?>
                                <?= picture('img/portadas/' . $com['portada'], $com['nombre'], 'card__img', true, 400, 267) ?>
                            <?php else: ?>
                                <div class="card__img card__img--placeholder">
                                    <?= mb_substr($com['nombre'], 0, 1) ?>
                                </div>
                            <?php endif; ?>
                            <div class="card__body">
                                <h3 class="card__title"><?= e($com['nombre']) ?></h3>
                                <?php if (!empty($com['categorias_nombres'])): ?>
                                    <p class="card__text card__text--small"><?= e($com['categorias_nombres']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($com['direccion'])): ?>
                                    <p class="card__text card__text--small">&#128205; <?= e(truncate($com['direccion'], 50)) ?></p>
                                <?php endif; ?>
                                <?php if ($com['calificación_promedio']): ?>
                                    <div class="rating-small">
                                        <?php for ($si = 1; $si <= 5; $si++): ?>
                                            <span class="star <?= $si <= round($com['calificación_promedio']) ? 'star--filled' : '' ?>">&#9733;</span>
                                        <?php endfor; ?>
                                        <span class="text-muted">(<?= $com['total_resenas'] ?>)</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>

                        <?php if (($i + 1) % 6 === 0 && isset($banners[$bannerIndex])): ?>
                            <div class="between-banner" data-banner-id="<?= $banners[$bannerIndex]['id'] ?>">
                                <a href="<?= e($banners[$bannerIndex]['url']) ?>" target="_blank" rel="noopener" onclick="trackBanner(<?= $banners[$bannerIndex]['id'] ?>)">
                                    <?= picture('img/banners/' . $banners[$bannerIndex]['imagen'], $banners[$bannerIndex]['titulo'] ?? 'Publicidad', '', true) ?>
                                </a>
                            </div>
                            <?php $bannerIndex++; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <?php include BASE_PATH . '/views/partials/pagination.php'; ?>

            <?php elseif ($total === 0 && !empty($queryParams)): ?>
                <div class="empty-state">
                    <p>No se encontraron comercios con los filtros seleccionados.</p>
                    <a href="<?= url('/buscar') ?>" class="btn btn--primary">Ver todos los comercios</a>
                </div>
            <?php endif; ?>
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
