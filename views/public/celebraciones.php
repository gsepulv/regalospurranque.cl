<?php
/**
 * Listado de todas las celebraciones y fechas especiales
 * Variables: $fechasPersonales, $fechasCalendario, $fechasComerciales, $breadcrumbs
 */
$pageType = 'fecha';
?>

<section class="section">
    <div class="container">
        <h1 class="section__title">Celebraciones y Fechas Especiales</h1>
        <p class="text-center text-muted mb-4">Descubre las mejores ofertas y comercios para cada ocasion especial en Purranque</p>
    </div>
</section>

<?php if (!empty($fechasPersonales)): ?>
<section class="section section--alt">
    <div class="container">
        <h2 class="section__title">Celebraciones Personales</h2>
        <p class="text-center text-muted mb-4">Encuentra el regalo perfecto para cada ocasion especial</p>
        <div class="category-grid">
            <?php foreach ($fechasPersonales as $fe): ?>
                <a href="<?= url('/fecha/' . $fe['slug']) ?>" class="category-card">
                    <span class="category-card__icon"><?= !empty($fe['icono']) ? $fe['icono'] : '&#127873;' ?></span>
                    <span class="category-card__name"><?= e($fe['nombre']) ?></span>
                    <span class="category-card__count"><?= (int) ($fe['comercios_count'] ?? 0) ?> comercio<?= ($fe['comercios_count'] ?? 0) != 1 ? 's' : '' ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($fechasCalendario)): ?>
<section class="section">
    <div class="container">
        <h2 class="section__title">Fechas del Calendario</h2>
        <p class="text-center text-muted mb-4">Ofertas especiales para cada fecha importante del ano</p>
        <div class="category-grid">
            <?php foreach ($fechasCalendario as $fe): ?>
                <a href="<?= url('/fecha/' . $fe['slug']) ?>" class="category-card" <?php if (!empty($fe['fecha_inicio'])): ?>title="<?= fecha_es($fe['fecha_inicio'], 'd/m') ?><?php if (!empty($fe['fecha_fin']) && $fe['fecha_fin'] !== $fe['fecha_inicio']): ?> — <?= fecha_es($fe['fecha_fin'], 'd/m') ?><?php endif; ?>"<?php endif; ?>>
                    <span class="category-card__icon"><?= !empty($fe['icono']) ? $fe['icono'] : '&#128197;' ?></span>
                    <span class="category-card__name"><?= e($fe['nombre']) ?></span>
                    <?php if (!empty($fe['fecha_inicio'])): ?>
                        <span class="category-card__date"><?= fecha_es($fe['fecha_inicio'], 'd/m') ?><?php if (!empty($fe['fecha_fin']) && $fe['fecha_fin'] !== $fe['fecha_inicio']): ?> — <?= fecha_es($fe['fecha_fin'], 'd/m') ?><?php endif; ?></span>
                    <?php endif; ?>
                    <span class="category-card__count"><?= (int) ($fe['comercios_count'] ?? 0) ?> comercio<?= ($fe['comercios_count'] ?? 0) != 1 ? 's' : '' ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($fechasComerciales)): ?>
<section class="section section--alt">
    <div class="container">
        <h2 class="section__title">Eventos Comerciales</h2>
        <p class="text-center text-muted mb-4">Sumate a los grandes eventos de descuentos del ano</p>
        <div class="category-grid">
            <?php foreach ($fechasComerciales as $fe): ?>
                <a href="<?= url('/fecha/' . $fe['slug']) ?>" class="category-card" <?php if (!empty($fe['fecha_inicio'])): ?>title="<?= fecha_es($fe['fecha_inicio'], 'd/m') ?><?php if (!empty($fe['fecha_fin']) && $fe['fecha_fin'] !== $fe['fecha_inicio']): ?> — <?= fecha_es($fe['fecha_fin'], 'd/m') ?><?php endif; ?>"<?php endif; ?>>
                    <span class="category-card__icon"><?= !empty($fe['icono']) ? $fe['icono'] : '&#128176;' ?></span>
                    <span class="category-card__name"><?= e($fe['nombre']) ?></span>
                    <?php if (!empty($fe['fecha_inicio'])): ?>
                        <span class="category-card__date"><?= fecha_es($fe['fecha_inicio'], 'd/m') ?><?php if (!empty($fe['fecha_fin']) && $fe['fecha_fin'] !== $fe['fecha_inicio']): ?> — <?= fecha_es($fe['fecha_fin'], 'd/m') ?><?php endif; ?></span>
                    <?php endif; ?>
                    <span class="category-card__count"><?= (int) ($fe['comercios_count'] ?? 0) ?> comercio<?= ($fe['comercios_count'] ?? 0) != 1 ? 's' : '' ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (empty($fechasPersonales) && empty($fechasCalendario) && empty($fechasComerciales)): ?>
<section class="section">
    <div class="container">
        <div class="empty-state">
            <span class="empty-state__emoji">&#127881;</span>
            <h3>Aun no hay celebraciones configuradas</h3>
            <p>Las celebraciones y fechas especiales se mostraran aqui cuando esten disponibles.</p>
        </div>
    </div>
</section>
<?php endif; ?>
