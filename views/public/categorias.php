<?php
/**
 * Listado de todas las categorias
 * Variables: $categorias, $breadcrumbs
 */
$pageType = 'categoria';
?>

<section class="section">
    <div class="container">
        <h1 class="section__title">Categorías</h1>
        <p class="text-center text-muted mb-4">Explora todas las categorias de comercios y servicios en Purranque</p>

        <?php if (!empty($categorias)): ?>
            <div class="category-grid">
                <?php foreach ($categorias as $cat): ?>
                    <a href="<?= url('/categoria/' . $cat['slug']) ?>" class="category-card">
                        <span class="category-card__icon"><?= !empty($cat['icono']) ? $cat['icono'] : mb_substr($cat['nombre'], 0, 1) ?></span>
                        <span class="category-card__name"><?= e($cat['nombre']) ?></span>
                        <span class="category-card__count"><?= (int) ($cat['comercios_count'] ?? 0) ?> comercio<?= ($cat['comercios_count'] ?? 0) != 1 ? 's' : '' ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-state__emoji">&#128194;</span>
                <h3>Aun no hay categorias</h3>
                <p>Las categorias se mostraran aqui cuando esten configuradas.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
