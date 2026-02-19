<?php
/**
 * Admin - Reportes por fecha especial
 * Variables: $fechas, $desde, $hasta
 */
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/reportes') ?>">Reportes</a> &rsaquo;
    <span>Fechas Especiales</span>
</div>

<div class="toolbar">
    <h2 style="margin:0;flex:1">Reportes por Fecha Especial</h2>
    <form method="GET" action="<?= url('/admin/reportes/fechas') ?>" class="toolbar__group" style="gap:0.5rem">
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
    <a href="<?= url('/admin/reportes/categorias?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">Categorías</a>
    <a href="<?= url('/admin/reportes/fechas?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab admin-tab--active">Fechas</a>
    <a href="<?= url('/admin/reportes/banners') ?>" class="admin-tab">Banners</a>
</div>

<div class="admin-card">
    <div style="padding:var(--spacing-6)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--spacing-4)">
            <h3 style="margin:0">Visitas relacionadas con fechas especiales</h3>
            <a href="<?= url('/admin/reportes/export?tipo=fechas&desde=' . e($desde) . '&hasta=' . e($hasta)) ?>"
               class="btn btn--outline btn--sm">Exportar CSV</a>
        </div>

        <?php if (!empty($fechas)): ?>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha especial</th>
                            <th>Tipo</th>
                            <th style="text-align:right">Visitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fechas as $i => $fecha): ?>
                            <tr>
                                <td><strong><?= $i + 1 ?></strong></td>
                                <td>
                                    <a href="<?= url('/fecha/' . $fecha['slug']) ?>" target="_blank">
                                        <?= e($fecha['nombre']) ?>
                                    </a>
                                </td>
                                <td><span class="badge badge--<?= $fecha['tipo'] ?>"><?= ucfirst($fecha['tipo']) ?></span></td>
                                <td style="text-align:right"><strong><?= number_format($fecha['visitas']) ?></strong></td>
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
