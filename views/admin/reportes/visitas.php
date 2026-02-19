<?php
/**
 * Admin - Reporte detallado de visitas
 * Variables: $visitasDia, $páginasTop, $referrersTop, $totalVisitas, $totalUnicos, $desde, $hasta
 */
$maxVisitas = max(array_column($visitasDia, 'visitas') ?: [1]);
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/reportes') ?>">Reportes</a> &rsaquo;
    <span>Visitas</span>
</div>

<div class="toolbar">
    <h2 style="margin:0;flex:1">Reporte de Visitas</h2>
    <form method="GET" action="<?= url('/admin/reportes/visitas') ?>" class="toolbar__group" style="gap:0.5rem">
        <input type="date" name="desde" class="form-control" value="<?= e($desde) ?>" style="padding:0.4rem 0.5rem;font-size:0.875rem">
        <span style="color:var(--color-gray)">a</span>
        <input type="date" name="hasta" class="form-control" value="<?= e($hasta) ?>" style="padding:0.4rem 0.5rem;font-size:0.875rem">
        <button type="submit" class="btn btn--primary btn--sm">Aplicar</button>
    </form>
</div>

<!-- Sub-nav -->
<div class="admin-tabs" style="margin-bottom:var(--spacing-6)">
    <a href="<?= url('/admin/reportes?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">General</a>
    <a href="<?= url('/admin/reportes/visitas?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab admin-tab--active">Visitas</a>
    <a href="<?= url('/admin/reportes/comercios?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">Comercios</a>
    <a href="<?= url('/admin/reportes/categorias?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">Categor&iacute;as</a>
    <a href="<?= url('/admin/reportes/fechas?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">Fechas</a>
    <a href="<?= url('/admin/reportes/banners') ?>" class="admin-tab">Banners</a>
</div>

<!-- Resumen -->
<div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));margin-bottom:var(--spacing-6)">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__number"><?= number_format($totalVisitas) ?></div>
        <div class="stat-card__label">Visitas totales</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__number"><?= number_format($totalUnicos) ?></div>
        <div class="stat-card__label">Visitantes &uacute;nicos</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__number"><?= count($visitasDia) ?></div>
        <div class="stat-card__label">D&iacute;as con datos</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__number"><?= count($visitasDia) > 0 ? number_format($totalVisitas / count($visitasDia), 1) : 0 ?></div>
        <div class="stat-card__label">Promedio/d&iacute;a</div>
    </div>
</div>

<!-- Gráfico de visitas por día -->
<div class="admin-card" style="margin-bottom:var(--spacing-6)">
    <div style="padding:var(--spacing-6)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--spacing-4)">
            <h3 style="margin:0">Visitas por d&iacute;a</h3>
            <a href="<?= url('/admin/reportes/export?tipo=visitas&desde=' . e($desde) . '&hasta=' . e($hasta)) ?>"
               class="btn btn--outline btn--sm">Exportar CSV</a>
        </div>

        <?php if (!empty($visitasDia)): ?>
            <div class="chart-bars" style="height:200px">
                <?php foreach ($visitasDia as $dia): ?>
                    <?php $pct = $maxVisitas > 0 ? ($dia['visitas'] / $maxVisitas) * 100 : 0; ?>
                    <div class="chart-bar">
                        <span class="chart-bar__value"><?= $dia['visitas'] ?></span>
                        <div class="chart-bar__fill" style="height:<?= max(2, $pct) ?>%"></div>
                        <span class="chart-bar__label"><?= date('d/m', strtotime($dia['fecha'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align:center;color:var(--color-gray);padding:2rem">Sin datos para el per&iacute;odo seleccionado.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Tabla detallada de visitas por día -->
<div class="admin-card" style="margin-bottom:var(--spacing-6)">
    <div style="padding:var(--spacing-6)">
        <h3 style="margin:0 0 var(--spacing-4)">Detalle por d&iacute;a</h3>
        <?php if (!empty($visitasDia)): ?>
            <div class="admin-table-wrapper">
                <table class="admin-table" style="font-size:0.875rem">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th style="text-align:right">Visitas</th>
                            <th style="text-align:right">&Uacute;nicos</th>
                            <th style="text-align:right">% del total</th>
                            <th>Proporci&oacute;n</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($visitasDia as $dia): ?>
                            <?php $pct = $totalVisitas > 0 ? ($dia['visitas'] / $totalVisitas) * 100 : 0; ?>
                            <tr>
                                <td><?= fecha_es($dia['fecha'], 'l d/m/Y') ?></td>
                                <td style="text-align:right"><strong><?= number_format($dia['visitas']) ?></strong></td>
                                <td style="text-align:right"><?= number_format($dia['unicos']) ?></td>
                                <td style="text-align:right"><?= number_format($pct, 1) ?>%</td>
                                <td style="width:200px">
                                    <div style="background:var(--color-light);border-radius:4px;height:8px;overflow:hidden">
                                        <div style="background:var(--color-primary);height:100%;width:<?= ($maxVisitas > 0 ? ($dia['visitas'] / $maxVisitas) * 100 : 0) ?>%;border-radius:4px"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align:center;color:var(--color-gray)">Sin datos.</p>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-6);margin-bottom:var(--spacing-6)">
    <!-- Top 20 páginas más visitadas -->
    <div class="admin-card">
        <div style="padding:var(--spacing-6)">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--spacing-4)">
                <h3 style="margin:0">Top 20 p&aacute;ginas</h3>
                <a href="<?= url('/admin/reportes/export?tipo=páginas&desde=' . e($desde) . '&hasta=' . e($hasta)) ?>"
                   class="btn btn--outline btn--sm">CSV</a>
            </div>
            <?php if (!empty($páginasTop)): ?>
                <?php $maxPag = max(array_column($páginasTop, 'visitas') ?: [1]); ?>
                <table class="admin-table" style="font-size:0.8rem">
                    <thead>
                        <tr>
                            <th style="width:30px">#</th>
                            <th>P&aacute;gina</th>
                            <th style="text-align:right">Visitas</th>
                            <th style="text-align:right">&Uacute;nicos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($páginasTop as $i => $p): ?>
                            <tr>
                                <td style="color:var(--color-gray)"><?= $i + 1 ?></td>
                                <td>
                                    <code style="font-size:0.75rem"><?= e(truncate($p['página'], 45)) ?></code>
                                    <div style="background:var(--color-light);border-radius:3px;height:4px;overflow:hidden;margin-top:3px">
                                        <div style="background:var(--color-primary);height:100%;width:<?= ($p['visitas'] / $maxPag) * 100 ?>%;border-radius:3px"></div>
                                    </div>
                                </td>
                                <td style="text-align:right"><strong><?= number_format($p['visitas']) ?></strong></td>
                                <td style="text-align:right"><?= number_format($p['unicos']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;color:var(--color-gray)">Sin datos.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top 20 fuentes de tráfico -->
    <div class="admin-card">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Top 20 fuentes de tr&aacute;fico</h3>
            <?php if (!empty($referrersTop)): ?>
                <?php $maxRef = max(array_column($referrersTop, 'visitas') ?: [1]); ?>
                <table class="admin-table" style="font-size:0.8rem">
                    <thead>
                        <tr>
                            <th style="width:30px">#</th>
                            <th>Fuente</th>
                            <th style="text-align:right">Visitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($referrersTop as $i => $ref): ?>
                            <tr>
                                <td style="color:var(--color-gray)"><?= $i + 1 ?></td>
                                <td>
                                    <?= e(truncate($ref['fuente'], 45)) ?>
                                    <div style="background:var(--color-light);border-radius:3px;height:4px;overflow:hidden;margin-top:3px">
                                        <div style="background:var(--color-success);height:100%;width:<?= ($ref['visitas'] / $maxRef) * 100 ?>%;border-radius:3px"></div>
                                    </div>
                                </td>
                                <td style="text-align:right"><strong><?= number_format($ref['visitas']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;color:var(--color-gray)">Sin datos.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
