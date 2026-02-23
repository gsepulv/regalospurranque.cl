<?php
/**
 * Vista mapa interactivo de comercios
 * Variables: $comercios, $categorias
 */
?>

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

<!-- Leaflet CSS + JS (OpenStreetMap, gratis, sin API key) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
(function() {
    // Datos de comercios desde PHP
    var comercios = <?= json_encode(array_map(function($c) {
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
    }, $comercios), JSON_UNESCAPED_UNICODE) ?>;

    // Inicializar mapa centrado en Plaza de Purranque
    var map = L.map('map').setView([<?= $centerLat ?>, <?= $centerLng ?>], <?= $zoom ?>);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 18
    }).addTo(map);

    var markers = [];
    var siteUrl = '<?= rtrim(SITE_URL, '/') ?>';
    var assetBase = siteUrl + '/assets/img/logos/';

    // Ícono personalizado de caja de regalo
    var giftIcon = L.divIcon({
        className: 'gift-marker',
        html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 44" width="36" height="44">' +
            '<defs><filter id="ms" x="-20%" y="-10%" width="140%" height="140%">' +
            '<feDropShadow dx="0" dy="1" stdDeviation="1.5" flood-opacity="0.3"/></filter></defs>' +
            '<path d="M18 44 C18 44 0 28 0 16 A18 18 0 0 1 36 16 C36 28 18 44 18 44Z" fill="#c53030" filter="url(#ms)"/>' +
            '<rect x="8" y="17" width="20" height="14" rx="2" fill="#e53e3e"/>' +
            '<rect x="8" y="12" width="20" height="7" rx="2" fill="#fc8181"/>' +
            '<rect x="16.5" y="12" width="3" height="19" fill="#fff" opacity="0.9"/>' +
            '<rect x="8" y="15" width="20" height="3" fill="#fff" opacity="0.9"/>' +
            '<path d="M18 12 C18 12 14 6 10.5 8 C7 10 12 12 18 12Z" fill="#fc8181" stroke="#e53e3e" stroke-width="0.5"/>' +
            '<path d="M18 12 C18 12 22 6 25.5 8 C29 10 24 12 18 12Z" fill="#fc8181" stroke="#e53e3e" stroke-width="0.5"/>' +
            '</svg>',
        iconSize: [36, 44],
        iconAnchor: [18, 44],
        popupAnchor: [0, -40]
    });

    // Crear marcadores
    comercios.forEach(function(com) {
        if (!com.lat || !com.lng) return;

        var marker = L.marker([com.lat, com.lng], {icon: giftIcon}).addTo(map);

        var popup = '<div style="text-align:center;min-width:150px">' +
            '<h4 style="margin:0 0 4px;font-size:14px">' + com.nombre + '</h4>' +
            (com.dir ? '<p style="margin:0 0 8px;font-size:12px;color:#666">' + com.dir + '</p>' : '') +
            '<a href="' + siteUrl + '/comercio/' + com.slug + '" style="font-size:12px">Ver más &rarr;</a>' +
            '</div>';

        marker.bindPopup(popup);
        marker.comercioData = {
            id: com.id,
            cats: com.cats ? com.cats.split(',') : []
        };
        markers.push(marker);
    });

    // Filtro por categoria
    var filterSelect = document.getElementById('categoryFilter');
    var countEl = document.getElementById('mapCount');
    var items = document.querySelectorAll('.business-item');

    filterSelect.addEventListener('change', function() {
        var cat = this.value;
        var visible = 0;

        markers.forEach(function(marker) {
            if (!cat || marker.comercioData.cats.indexOf(cat) !== -1) {
                marker.addTo(map);
                visible++;
            } else {
                map.removeLayer(marker);
            }
        });

        items.forEach(function(item) {
            var itemCats = (item.dataset.categories || '').split(',');
            if (!cat || itemCats.indexOf(cat) !== -1) {
                item.style.display = '';
                visible++;
            } else {
                item.style.display = 'none';
            }
        });

        countEl.textContent = Math.ceil(visible / 2) + ' comercios';
    });
})();
</script>

<style>
.map-container {
    height: 500px;
    width: 100%;
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-8);
    border: 1px solid var(--color-border);
}

.map-filters {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    background: var(--color-white);
    padding: var(--spacing-4);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-4);
    box-shadow: var(--shadow-sm);
    flex-wrap: wrap;
}

.map-filters__label {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.map-list {
    margin-top: var(--spacing-8);
}

.map-list h2 {
    margin-bottom: var(--spacing-6);
}

.gift-marker {
    background: none !important;
    border: none !important;
    transition: transform 0.2s ease;
}
.gift-marker:hover {
    transform: scale(1.2);
}

@media (max-width: 768px) {
    .map-container {
        height: 350px;
    }
    .map-filters__label {
        flex-direction: column;
        align-items: flex-start;
        width: 100%;
    }
    .map-filters .filter-select {
        width: 100%;
    }
}
</style>
