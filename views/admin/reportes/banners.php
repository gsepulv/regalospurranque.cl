<?php
/**
 * Admin - Reportes de banners
 * Variables: $banners
 */
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/reportes') ?>">Reportes</a> &rsaquo;
    <span>Banners</span>
</div>

<div class="toolbar">
    <h2 style="margin:0;flex:1">Reportes de Banners</h2>
    <a href="<?= url('/admin/reportes/export?tipo=banners') ?>" class="btn btn--outline btn--sm">Exportar CSV</a>
</div>

<div class="admin-tabs" style="margin-bottom:var(--spacing-6)">
    <a href="<?= url('/admin/reportes') ?>" class="admin-tab">General</a>
    <a href="<?= url('/admin/reportes/visitas') ?>" class="admin-tab">Visitas</a>
    <a href="<?= url('/admin/reportes/comercios') ?>" class="admin-tab">Comercios</a>
    <a href="<?= url('/admin/reportes/categorias') ?>" class="admin-tab">Categorías</a>
    <a href="<?= url('/admin/reportes/fechas') ?>" class="admin-tab">Fechas</a>
    <a href="<?= url('/admin/reportes/banners') ?>" class="admin-tab admin-tab--active">Banners</a>
</div>

<?php if (!empty($banners)): ?>
    <div class="admin-card">
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Banner</th>
                        <th>Tipo</th>
                        <th>URL</th>
                        <th style="text-align:right">Impresiones</th>
                        <th style="text-align:right">Clicks</th>
                        <th style="text-align:right">CTR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($banners as $banner): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:var(--spacing-3)">
                                    <?php if (!empty($banner['imagen'])): ?>
                                        <img src="<?= asset('img/banners/' . $banner['imagen']) ?>"
                                             alt="" loading="lazy" style="width:48px;height:32px;object-fit:cover;border-radius:4px">
                                    <?php endif; ?>
                                    <span><?= e($banner['titulo'] ?? 'Banner #' . $banner['id']) ?></span>
                                </div>
                            </td>
                            <td><span class="badge"><?= e($banner['tipo']) ?></span></td>
                            <td><small style="color:var(--color-gray)"><?= e(truncate($banner['url'] ?? '-', 30)) ?></small></td>
                            <td style="text-align:right"><strong><?= number_format($banner['impresiones']) ?></strong></td>
                            <td style="text-align:right"><strong><?= number_format($banner['clicks']) ?></strong></td>
                            <td style="text-align:right">
                                <?php
                                $ctr = (float) $banner['ctr'];
                                $ctrColor = $ctr >= 2 ? '#059669' : ($ctr >= 0.5 ? '#d97706' : '#dc2626');
                                ?>
                                <span style="color:<?= $ctrColor ?>;font-weight:600"><?= $banner['ctr'] ?>%</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="admin-card">
        <div style="text-align:center;padding:3rem;color:var(--color-gray)">
            <p><strong>Sin banners activos</strong></p>
            <p style="font-size:0.875rem">No hay banners con estadísticas para mostrar.</p>
        </div>
    </div>
<?php endif; ?>
