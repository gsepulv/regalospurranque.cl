<div class="admin-breadcrumb">
    <span>Dashboard</span>
</div>

<h2>Dashboard</h2>
<p class="mb-3">Bienvenido, <strong><?= e($admin['nombre'] ?? 'Admin') ?></strong>. Resumen general del sitio.</p>

<!-- Stats principales -->
<div class="stats-grid">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__header">
            <div>
                <div class="stat-card__number"><?= number_format($stats['comercios_activos']) ?></div>
                <div class="stat-card__label">Comercios activos</div>
            </div>
            <div class="stat-card__icon">&#127978;</div>
        </div>
        <?php if ($stats['comercios_inactivos'] > 0): ?>
            <small style="color:var(--color-gray)"><?= $stats['comercios_inactivos'] ?> inactivo(s)</small>
        <?php endif; ?>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__header">
            <div>
                <div class="stat-card__number"><?= number_format($stats['categorias']) ?></div>
                <div class="stat-card__label">Categorías</div>
            </div>
            <div class="stat-card__icon">&#128194;</div>
        </div>
    </div>
    <div class="stat-card stat-card--warning">
        <div class="stat-card__header">
            <div>
                <div class="stat-card__number"><?= number_format($stats['noticias']) ?></div>
                <div class="stat-card__label">Noticias publicadas</div>
            </div>
            <div class="stat-card__icon">&#128240;</div>
        </div>
    </div>
    <div class="stat-card stat-card--danger">
        <div class="stat-card__header">
            <div>
                <div class="stat-card__number"><?= number_format($stats['resenas_pendientes']) ?></div>
                <div class="stat-card__label">Reseñas pendientes</div>
            </div>
            <div class="stat-card__icon">&#11088;</div>
        </div>
    </div>
</div>

<!-- Stats secundarios -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr)">
    <div class="stat-card">
        <div class="stat-card__number" style="font-size:1.5rem"><?= number_format($stats['visitas_hoy']) ?></div>
        <div class="stat-card__label">Visitas hoy</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__number" style="font-size:1.5rem"><?= number_format($stats['visitas_semana']) ?></div>
        <div class="stat-card__label">Visitas semana</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__number" style="font-size:1.5rem"><?= number_format($stats['visitas_mes']) ?></div>
        <div class="stat-card__label">Visitas mes</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__number" style="font-size:1.5rem"><?= $stats['fechas_personal'] + $stats['fechas_calendario'] + $stats['fechas_comercial'] ?></div>
        <div class="stat-card__label">Fechas especiales</div>
        <small style="color:var(--color-gray)"><?= $stats['fechas_personal'] ?>P / <?= $stats['fechas_calendario'] ?>C / <?= $stats['fechas_comercial'] ?>E</small>
    </div>
</div>

<!-- Accesos rapidos -->
<div class="admin-card mt-3">
    <div class="admin-card__header">
        <h3>Acciones rapidas</h3>
    </div>
    <div class="admin-card__body">
        <div class="toolbar" style="margin-bottom:0">
            <a href="<?= url('/admin/comercios/crear') ?>" class="btn btn--primary btn--sm">+ Nuevo comercio</a>
            <a href="<?= url('/admin/noticias/crear') ?>" class="btn btn--secondary btn--sm">+ Nueva noticia</a>
            <a href="<?= url('/admin/categorias/crear') ?>" class="btn btn--outline btn--sm">+ Nueva categoria</a>
            <a href="<?= url('/admin/fechas/crear') ?>" class="btn btn--outline btn--sm">+ Nueva fecha</a>
        </div>
    </div>
</div>

<!-- Widget de salud -->
<?php if (!empty($healthWidget)): ?>
<div class="admin-card mt-3" style="margin-bottom:1.25rem">
    <div class="admin-card__header">
        <h3>Salud del Sistema</h3>
        <a href="<?= url('/admin/mantenimiento/salud') ?>" class="btn btn--outline btn--xs">Ver detalle</a>
    </div>
    <div class="admin-card__body">
        <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap">
            <div style="text-align:center">
                <?php
                $hScore = (int) ($healthWidget['score'] ?? 0);
                $hColor = $hScore >= 80 ? '#059669' : ($hScore >= 50 ? '#d97706' : '#dc2626');
                ?>
                <div style="font-size:2rem;font-weight:800;color:<?= $hColor ?>"><?= $hScore ?><small style="font-size:1rem;font-weight:600">/100</small></div>
                <div style="font-size:0.75rem;color:var(--color-gray)">Puntuaci&oacute;n</div>
            </div>
            <div style="flex:1;display:flex;gap:1.5rem;flex-wrap:wrap">
                <div>
                    <div style="font-size:0.75rem;color:var(--color-gray)">Disco libre</div>
                    <div style="font-weight:600"><?= e($healthWidget['diskFree'] ?? 'N/A') ?></div>
                </div>
                <div>
                    <div style="font-size:0.75rem;color:var(--color-gray)">&Uacute;ltimo backup</div>
                    <div style="font-weight:600"><?= !empty($healthWidget['lastBackup']) ? date('d/m/Y', strtotime($healthWidget['lastBackup'])) : 'Ninguno' ?></div>
                </div>
                <?php if (!empty($healthWidget['alerts'])): ?>
                <div>
                    <div style="font-size:0.75rem;color:var(--color-gray)">Alertas</div>
                    <?php foreach ($healthWidget['alerts'] as $alert): ?>
                        <span class="badge badge--warning" style="margin-right:4px"><?= e($alert) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-top:1.25rem">
    <!-- Grafico visitas -->
    <div class="admin-card">
        <div class="admin-card__header">
            <h3>Visitas - ultimos 7 dias</h3>
        </div>
        <div class="admin-card__body">
            <?php
            $maxVisitas = max(1, max(array_values($chartData)));
            ?>
            <div class="chart-bars">
                <?php foreach ($chartData as $fecha => $total): ?>
                    <div class="chart-bar">
                        <div class="chart-bar__value"><?= $total ?></div>
                        <div class="chart-bar__fill" style="height:<?= round(($total / $maxVisitas) * 120) ?>px"></div>
                        <div class="chart-bar__label"><?= date('d/m', strtotime($fecha)) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Últimos comercios -->
    <div class="admin-card">
        <div class="admin-card__header">
            <h3>Últimos comercios</h3>
            <a href="<?= url('/admin/comercios') ?>" class="btn btn--outline btn--sm">Ver todos</a>
        </div>
        <div class="admin-card__body" style="padding:0">
            <?php if (!empty($ultimosComercios)): ?>
                <table class="admin-table">
                    <tbody>
                        <?php foreach ($ultimosComercios as $com): ?>
                            <tr>
                                <td>
                                    <strong><?= e($com['nombre']) ?></strong>
                                    <br><small style="color:var(--color-gray)"><?= fecha_es($com['created_at'], 'd/m/Y') ?></small>
                                </td>
                                <td><span class="badge badge--<?= $com['plan'] ?>"><?= $com['plan'] ?></span></td>
                                <td>
                                    <a href="<?= url('/admin/comercios/editar/' . $com['id']) ?>" class="btn btn--outline btn--xs">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="padding:1rem;color:var(--color-gray)">Sin comercios registrados.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-top:1.25rem">
    <!-- Reseñas pendientes -->
    <div class="admin-card">
        <div class="admin-card__header">
            <h3>Reseñas pendientes</h3>
            <a href="<?= url('/admin/resenas') ?>" class="btn btn--outline btn--sm">Ver todas</a>
        </div>
        <div class="admin-card__body" style="padding:0">
            <?php if (!empty($ultimasResenas)): ?>
                <table class="admin-table">
                    <tbody>
                        <?php foreach ($ultimasResenas as $res): ?>
                            <tr>
                                <td>
                                    <strong><?= e($res['nombre_autor']) ?></strong>
                                    <br><small style="color:var(--color-gray)"><?= e($res['comercio_nombre']) ?></small>
                                </td>
                                <td>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span style="color:<?= $i <= $res['calificacion'] ? '#f59e0b' : '#d1d5db' ?>">&#9733;</span>
                                    <?php endfor; ?>
                                </td>
                                <td><small style="color:var(--color-gray)"><?= fecha_es($res['created_at'], 'd/m') ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="padding:1rem;color:var(--color-gray)">Sin reseñas pendientes.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actividad reciente -->
    <div class="admin-card">
        <div class="admin-card__header">
            <h3>Actividad reciente</h3>
        </div>
        <div class="admin-card__body" style="padding:0">
            <?php if (!empty($acciones)): ?>
                <?php foreach ($acciones as $log): ?>
                    <div class="log-entry">
                        <div class="log-entry__time"><?= fecha_es($log['created_at'], 'd/m H:i') ?></div>
                        <div class="log-entry__content">
                            <div class="log-entry__message">
                                <span class="badge"><?= e($log['modulo']) ?></span>
                                <?= e($log['accion']) ?>
                            </div>
                            <div class="log-entry__user"><?= e($log['usuario_nombre']) ?> — <?= e(truncate($log['detalle'], 40)) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="padding:1rem;color:var(--color-gray)">Sin actividad registrada.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
