<?php
/**
 * Vista mapa interactivo de comercios
 * Variables: $comercios, $categorias
 */
?>

<?php $mapaCss = APP_ENV === 'production' ? 'css/mapa.min.css' : 'css/mapa.css'; ?>
<link rel="stylesheet" href="<?= asset($mapaCss) ?>">

<section class="section">
    <div class="container">

        <div class="page-header">
            <h1>Mapa de Comercios</h1>
            <p class="text-muted">Encuentra los comercios de <?= e(CITY_NAME) ?> en el mapa interactivo</p>
        </div>

        <!-- Filtro por categoria -->
        <div class="map-filters">
            <label class="map-filters__label">
                <strong>Filtrar por categoría:</strong>
                <select id="categoryFilter" class="form-control filter-select">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= e($cat['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <span class="map-filters__count text-muted text-sm" id="mapCount"><?= count($comercios) ?> comercios</span>
        </div>

        <!-- Mapa -->
        <div id="map" class="map-container"></div>

        <!-- Listado debajo del mapa -->
        <div class="map-list">
            <h2>Listado de comercios</h2>
            <div class="grid grid--auto" id="businessList">
                <?php foreach ($comercios as $com): ?>
                    <a href="<?= url('/comercio/' . $com['slug']) ?>"
                       class="card business-item"
                       data-id="<?= $com['id'] ?>"
                       data-lat="<?= $com['lat'] ?>"
                       data-lng="<?= $com['lng'] ?>"
                       data-categories="<?= e($com['categorias_ids'] ?? '') ?>">
                        <div class="card__body">
                            <h3 class="card__title"><?= e($com['nombre']) ?></h3>
                            <?php if (!empty($com['direccion'])): ?>
                                <p class="card__text card__text--small">&#128205; <?= e(truncate($com['direccion'], 60)) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($com['telefono'])): ?>
                                <p class="card__text card__text--small">&#128222; <?= e($com['telefono']) ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</section>

<!-- Leaflet CSS + JS (self-hosted, OpenStreetMap tiles) -->
<link rel="stylesheet" href="<?= asset('vendor/leaflet/leaflet.css') ?>">
<script src="<?= asset('vendor/leaflet/leaflet.js') ?>" defer></script>

<!-- Datos PHP para el mapa (bridge PHP→JS) -->
<script>
window.__mapaData = {
    comercios: <?= json_encode(array_map(function($c) {
        return [
            'id'     => (int) $c['id'],
            'nombre' => $c['nombre'],
            'slug'   => $c['slug'],
            'dir'    => $c['direccion'] ?? '',
            'lat'    => (float) $c['lat'],
            'lng'    => (float) $c['lng'],
            'logo'   => $c['logo'] ?? '',
            'cats'   => $c['categorias_ids'] ?? '',
        ];
    }, $comercios), JSON_UNESCAPED_UNICODE) ?>,
    centerLat: <?= $centerLat ?>,
    centerLng: <?= $centerLng ?>,
    zoom: <?= $zoom ?>,
    siteUrl: '<?= rtrim(SITE_URL, '/') ?>'
};
</script>
<?php $mapaJs = APP_ENV === 'production' ? 'js/mapa.min.js' : 'js/mapa.js'; ?>
<script src="<?= asset($mapaJs) ?>" defer></script>
