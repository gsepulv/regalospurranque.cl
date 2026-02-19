<?php
/**
 * Admin - Reportes de comercios
 * Variables: $comercios, $whatsappTop, $desde, $hasta
 */
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/reportes') ?>">Reportes</a> &rsaquo;
    <span>Comercios</span>
</div>

<div class="toolbar">
    <h2 style="margin:0;flex:1">Reportes de Comercios</h2>
    <form method="GET" action="<?= url('/admin/reportes/comercios') ?>" class="toolbar__group" style="gap:0.5rem">
        <input type="date" name="desde" class="form-control" value="<?= e($desde) ?>" style="padding:0.4rem 0.5rem;font-size:0.875rem">
        <span style="color:var(--color-gray)">a</span>
        <input type="date" name="hasta" class="form-control" value="<?= e($hasta) ?>" style="padding:0.4rem 0.5rem;font-size:0.875rem">
        <button type="submit" class="btn btn--primary btn--sm">Aplicar</button>
    </form>
</div>

<div class="admin-tabs" style="margin-bottom:var(--spacing-6)">
    <a href="<?= url('/admin/reportes?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">General</a>
    <a href="<?= url('/admin/reportes/visitas?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">Visitas</a>
    <a href="<?= url('/admin/reportes/comercios?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab admin-tab--active">Comercios</a>
    <a href="<?= url('/admin/reportes/categorias?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">Categorías</a>
    <a href="<?= url('/admin/reportes/fechas?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">Fechas</a>
    <a href="<?= url('/admin/reportes/banners') ?>" class="admin-tab">Banners</a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--spacing-6)">

    <!-- Ranking de comercios por visitas -->
    <div class="admin-card">
        <div style="padding:var(--spacing-6)">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--spacing-4)">
                <h3 style="margin:0">Comercios más visitados</h3>
                <a href="<?= url('/admin/reportes/export?tipo=comercios&desde=' . e($desde) . '&hasta=' . e($hasta)) ?>"
                   class="btn btn--outline btn--sm">Exportar CSV</a>
            </div>
            <?php if (!empty($comercios)): ?>
                <div class="admin-table-wrapper">
                    <table class="admin-table" style="font-size:0.875rem">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Comercio</th>
                                <th>Plan</th>
                                <th style="text-align:right">Visitas</th>
                                <th style="text-align:right">Únicos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comercios as $i => $com): ?>
                                <tr>
                                    <td><strong><?= $i + 1 ?></strong></td>
                                    <td>
                                        <a href="<?= url('/comercio/' . $com['slug']) ?>" target="_blank">
                                            <?= e($com['nombre']) ?>
                                        </a>
                                    </td>
                                    <td><span class="badge badge--<?= $com['plan'] ?>"><?= ucfirst($com['plan']) ?></span></td>
                                    <td style="text-align:right"><strong><?= number_format($com['visitas']) ?></strong></td>
                                    <td style="text-align:right"><?= number_format($com['unicos']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align:center;color:var(--color-gray);padding:2rem">Sin datos para el período seleccionado.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- WhatsApp clicks -->
    <div class="admin-card">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Top WhatsApp clicks</h3>
            <?php if (!empty($whatsappTop)): ?>
                <?php $maxClicks = max(array_column($whatsappTop, 'clicks') ?: [1]); ?>
                <?php foreach ($whatsappTop as $com): ?>
                    <?php $pct = $maxClicks > 0 ? ($com['clicks'] / $maxClicks) * 100 : 0; ?>
                    <div style="margin-bottom:var(--spacing-3)">
                        <div style="display:flex;justify-content:space-between;font-size:0.8rem;margin-bottom:2px">
                            <span><?= e(truncate($com['nombre'], 25)) ?></span>
                            <strong><?= number_format($com['clicks']) ?></strong>
                        </div>
                        <div style="background:var(--color-light);border-radius:4px;height:8px;overflow:hidden">
                            <div style="background:#25d366;height:100%;width:<?= $pct ?>%;border-radius:4px"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;color:var(--color-gray)">Sin datos.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
