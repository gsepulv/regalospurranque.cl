<?php
/**
 * Admin - Reportes por categoría
 * Variables: $categorias, $desde, $hasta
 */
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/reportes') ?>">Reportes</a> &rsaquo;
    <span>Categorías</span>
</div>

<div class="toolbar">
    <h2 style="margin:0;flex:1">Reportes por Categoría</h2>
    <form method="GET" action="<?= url('/admin/reportes/categorias') ?>" class="toolbar__group" style="gap:0.5rem">
        <input type="date" name="desde" class="form-control" value="<?= e($desde) ?>" style="padding:0.4rem 0.5rem;font-size:0.875rem">
        <span style="color:var(--color-gray)">a</span>
        <input type="date" name="hasta" class="form-control" value="<?= e($hasta) ?>" style="padding:0.4rem 0.5rem;font-size:0.875rem">
        <button type="submit" class="btn btn--primary btn--sm">Aplicar</button>
    </form>
</div>

<div class="admin-tabs" style="margin-bottom:var(--spacing-6)">
    <a href="<?= url('/admin/reportes?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">General</a>
    <a href="<?= url('/admin/reportes/visitas?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">Visitas</a>
    <a href="<?= url('/admin/reportes/comercios?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">Comercios</a>
    <a href="<?= url('/admin/reportes/categorias?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab admin-tab--active">Categorías</a>
    <a href="<?= url('/admin/reportes/fechas?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">Fechas</a>
    <a href="<?= url('/admin/reportes/banners') ?>" class="admin-tab">Banners</a>
</div>

<div class="admin-card">
    <div style="padding:var(--spacing-6)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--spacing-4)">
            <h3 style="margin:0">Visitas por categoría</h3>
            <a href="<?= url('/admin/reportes/export?tipo=categorias&desde=' . e($desde) . '&hasta=' . e($hasta)) ?>"
               class="btn btn--outline btn--sm">Exportar CSV</a>
        </div>

        <?php if (!empty($categorias)): ?>
            <?php $maxCat = max(array_column($categorias, 'visitas') ?: [1]); ?>
            <div style="max-width:700px">
                <?php foreach ($categorias as $cat): ?>
                    <?php $pct = $maxCat > 0 ? ($cat['visitas'] / $maxCat) * 100 : 0; ?>
                    <div style="margin-bottom:var(--spacing-4)">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                            <a href="<?= url('/categoria/' . $cat['slug']) ?>" target="_blank" style="font-size:0.9rem;font-weight:500">
                                <?= e($cat['nombre']) ?>
                            </a>
                            <strong style="font-size:0.9rem"><?= number_format($cat['visitas']) ?> visitas</strong>
                        </div>
                        <div style="background:var(--color-light);border-radius:4px;height:12px;overflow:hidden">
                            <div style="background:<?= e($cat['color']) ?>;height:100%;width:<?= max(2, $pct) ?>%;border-radius:4px;transition:width 0.3s"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align:center;color:var(--color-gray);padding:2rem">Sin datos para el período seleccionado.</p>
        <?php endif; ?>
    </div>
</div>
