<?php
/**
 * Admin - Dashboard de reportes/analytics
 * Variables: $dashboard, $visitasDia, $páginasTop, $tiposVisita, $referrersTop, $desde, $hasta
 */
$maxVisitas = max(array_column($visitasDia, 'visitas') ?: [1]);
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <span>Reportes</span>
</div>

<div class="toolbar">
    <h2 style="margin:0;flex:1">Reportes y Estadísticas</h2>

    <!-- Selector de período -->
    <form method="GET" action="<?= url('/admin/reportes') ?>" class="toolbar__group" style="gap:0.5rem">
        <input type="date" name="desde" class="form-control" value="<?= e($desde) ?>" style="padding:0.4rem 0.5rem;font-size:0.875rem">
        <span style="color:var(--color-gray)">a</span>
        <input type="date" name="hasta" class="form-control" value="<?= e($hasta) ?>" style="padding:0.4rem 0.5rem;font-size:0.875rem">
        <button type="submit" class="btn btn--primary btn--sm">Aplicar</button>
    </form>
</div>

<!-- Sub-nav -->
<div class="admin-tabs" style="margin-bottom:var(--spacing-6)">
    <a href="<?= url('/admin/reportes?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab admin-tab--active">General</a>
    <a href="<?= url('/admin/reportes/visitas?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">Visitas</a>
    <a href="<?= url('/admin/reportes/comercios?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">Comercios</a>
    <a href="<?= url('/admin/reportes/categorias?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">Categorías</a>
    <a href="<?= url('/admin/reportes/fechas?desde=' . e($desde) . '&hasta=' . e($hasta)) ?>" class="admin-tab">Fechas</a>
    <a href="<?= url('/admin/reportes/banners') ?>" class="admin-tab">Banners</a>
</div>

<!-- Estadísticas generales -->
<div class="stats-grid" style="margin-bottom:var(--spacing-6)">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__number"><?= number_format($dashboard['visitas']) ?></div>
        <div class="stat-card__label">Visitas totales</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__number"><?= number_format($dashboard['visitantes_unicos']) ?></div>
        <div class="stat-card__label">Visitantes únicos</div>
    </div>
    <div class="stat-card stat-card--warning">
        <div class="stat-card__number"><?= number_format($dashboard['comercios_visitados']) ?></div>
        <div class="stat-card__label">Comercios visitados</div>
    </div>
    <div class="stat-card stat-card--danger">
        <div class="stat-card__number"><?= number_format($dashboard['whatsapp_clicks']) ?></div>
        <div class="stat-card__label">Clicks WhatsApp</div>
    </div>
</div>

<!-- Gráfico de visitas por día (CSS bars) -->
<div class="admin-card" style="margin-bottom:var(--spacing-6)">
    <div style="padding:var(--spacing-6)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--spacing-4)">
            <h3 style="margin:0">Visitas por día</h3>
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
            <p style="text-align:center;color:var(--color-gray);padding:2rem">Sin datos para el período seleccionado.</p>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-6);margin-bottom:var(--spacing-6)">

    <!-- Páginas más visitadas -->
    <div class="admin-card">
        <div style="padding:var(--spacing-6)">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--spacing-4)">
                <h3 style="margin:0">Páginas más visitadas</h3>
                <a href="<?= url('/admin/reportes/export?tipo=páginas&desde=' . e($desde) . '&hasta=' . e($hasta)) ?>"
                   class="btn btn--outline btn--sm">CSV</a>
            </div>
            <?php if (!empty($páginasTop)): ?>
                <table class="admin-table" style="font-size:0.875rem">
                    <thead><tr><th>Página</th><th style="text-align:right">Visitas</th><th style="text-align:right">Únicos</th></tr></thead>
                    <tbody>
                        <?php foreach ($páginasTop as $p): ?>
                            <tr>
                                <td><small><?= e(truncate($p['página'], 40)) ?></small></td>
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

    <!-- Columna derecha -->
    <div>
        <!-- Tipos de visita -->
        <div class="admin-card" style="margin-bottom:var(--spacing-6)">
            <div style="padding:var(--spacing-6)">
                <h3 style="margin:0 0 var(--spacing-4)">Tipos de evento</h3>
                <?php if (!empty($tiposVisita)): ?>
                    <?php $maxTipo = max(array_column($tiposVisita, 'total') ?: [1]); ?>
                    <?php foreach ($tiposVisita as $tipo): ?>
                        <?php $pct = $maxTipo > 0 ? ($tipo['total'] / $maxTipo) * 100 : 0; ?>
                        <div style="margin-bottom:var(--spacing-3)">
                            <div style="display:flex;justify-content:space-between;font-size:0.8rem;margin-bottom:2px">
                                <span><?= e($tipo['tipo']) ?></span>
                                <strong><?= number_format($tipo['total']) ?></strong>
                            </div>
                            <div style="background:var(--color-light);border-radius:4px;height:8px;overflow:hidden">
                                <div style="background:var(--color-primary);height:100%;width:<?= $pct ?>%;border-radius:4px"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center;color:var(--color-gray)">Sin datos.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Fuentes de tráfico -->
        <div class="admin-card">
            <div style="padding:var(--spacing-6)">
                <h3 style="margin:0 0 var(--spacing-4)">Fuentes de tráfico</h3>
                <?php if (!empty($referrersTop)): ?>
                    <table class="admin-table" style="font-size:0.875rem">
                        <thead><tr><th>Fuente</th><th style="text-align:right">Visitas</th></tr></thead>
                        <tbody>
                            <?php foreach ($referrersTop as $ref): ?>
                                <tr>
                                    <td><small><?= e(truncate($ref['fuente'], 35)) ?></small></td>
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
</div>
