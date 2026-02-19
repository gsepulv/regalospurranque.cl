<?php
/**
 * Admin - Mantenimiento > Logs de Auditoría
 * Variables: $logs, $total, $pagina, $totalPages, $filtros, $usuarios, $modulos,
 *            $stats (total_registros, top_acciones, top_usuarios, top_modulos),
 *            $actividad (date => count, last 30 days)
 */
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/mantenimiento/backups') ?>">Mantenimiento</a> &rsaquo;
    <span>Logs de Auditor&iacute;a</span>
</div>

<?php
$currentTab = 'logs';
$tabs = [
    'backups'        => ['label' => 'Backups',           'url' => '/admin/mantenimiento/backups'],
    'archivos'       => ['label' => 'Explorador',        'url' => '/admin/mantenimiento/archivos'],
    'salud'          => ['label' => 'Salud',             'url' => '/admin/mantenimiento/salud'],
    'logs'           => ['label' => 'Logs',              'url' => '/admin/mantenimiento/logs'],
    'herramientas'   => ['label' => 'Herramientas',      'url' => '/admin/mantenimiento/herramientas'],
    'configuracion'  => ['label' => 'Configuraci&oacute;n',  'url' => '/admin/mantenimiento/configuracion'],
];
?>
<div class="admin-tabs" style="margin-bottom:var(--spacing-6)">
    <?php foreach ($tabs as $key => $tab): ?>
        <a href="<?= url($tab['url']) ?>" class="admin-tab <?= $currentTab === $key ? 'admin-tab--active' : '' ?>"><?= $tab['label'] ?></a>
    <?php endforeach; ?>
</div>

<?php if ($flash['success'] ?? false): ?>
    <div class="toast toast--success"><?= e($flash['success']) ?></div>
<?php endif; ?>
<?php if ($flash['error'] ?? false): ?>
    <div class="toast toast--error"><?= e($flash['error']) ?></div>
<?php endif; ?>

<!-- Stats -->
<?php
$topAccion  = !empty($topAcciones)  ? $topAcciones[0]  : null;
$topUsuario = !empty($topUsuarios)  ? $topUsuarios[0]  : null;
$topModulo  = !empty($topModulos)   ? $topModulos[0]   : null;
?>
<div class="stats-grid" style="grid-template-columns:repeat(4, 1fr);margin-bottom:var(--spacing-6)">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__number"><?= number_format($totalRecords ?? 0) ?></div>
        <div class="stat-card__label">Total Registros</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__number"><?= e($topAccion['accion'] ?? 'N/A') ?></div>
        <div class="stat-card__label">Acci&oacute;n m&aacute;s frecuente</div>
    </div>
    <div class="stat-card stat-card--warning">
        <div class="stat-card__number"><?= e($topUsuario['usuario_nombre'] ?? 'N/A') ?></div>
        <div class="stat-card__label">Usuario m&aacute;s activo</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__number"><?= e($topModulo['modulo'] ?? 'N/A') ?></div>
        <div class="stat-card__label">M&oacute;dulo m&aacute;s activo</div>
    </div>
</div>

<!-- Activity Chart (last 30 days) -->
<?php if (!empty($actividad30d)): ?>
    <?php
    $actividadMap = [];
    $maxActividad = 1;
    foreach ($actividad30d as $row) {
        $actividadMap[$row['fecha']] = (int) $row['total'];
        if ((int) $row['total'] > $maxActividad) $maxActividad = (int) $row['total'];
    }
    ?>
    <div class="admin-card" style="margin-bottom:var(--spacing-6)">
        <div style="padding:var(--spacing-4) var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Actividad &uacute;ltimos 30 d&iacute;as</h3>
            <div class="activity-chart">
                <?php foreach ($actividadMap as $fecha => $count): ?>
                    <?php $heightPct = ($count / $maxActividad) * 100; ?>
                    <div class="activity-chart__col" title="<?= e($fecha) ?>: <?= $count ?> registros">
                        <div class="activity-chart__bar" style="height:<?= $heightPct ?>%"></div>
                        <div class="activity-chart__label"><?= date('d', strtotime($fecha)) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="admin-card" style="margin-bottom:var(--spacing-6)">
    <details class="logs-filters" <?= array_filter($filtros) ? 'open' : '' ?>>
        <summary style="padding:var(--spacing-4) var(--spacing-6);cursor:pointer;font-weight:600;user-select:none">
            Filtros
            <?php if (array_filter($filtros)): ?>
                <span class="badge badge--primary" style="margin-left:var(--spacing-2)">Activos</span>
            <?php endif; ?>
        </summary>
        <div style="padding:0 var(--spacing-6) var(--spacing-6)">
            <form method="GET" action="<?= url('/admin/mantenimiento/logs') ?>">
                <div class="logs-filters__grid">
                    <div class="form-group">
                        <label class="form-label">Usuario</label>
                        <select name="usuario" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach ($usuarios as $u): ?>
                                <option value="<?= e($u['usuario_nombre']) ?>" <?= ($filtros['usuario'] ?? '') === $u['usuario_nombre'] ? 'selected' : '' ?>><?= e($u['usuario_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">M&oacute;dulo</label>
                        <select name="modulo" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach ($modulos as $m): ?>
                                <option value="<?= e($m['modulo']) ?>" <?= ($filtros['modulo'] ?? '') === $m['modulo'] ? 'selected' : '' ?>><?= e($m['modulo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Acci&oacute;n</label>
                        <input type="text" name="accion" class="form-control" value="<?= e($filtros['accion'] ?? '') ?>" placeholder="Ej: crear, editar...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Desde</label>
                        <input type="date" name="desde" class="form-control" value="<?= e($filtros['desde'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="hasta" class="form-control" value="<?= e($filtros['hasta'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="buscar" class="form-control" value="<?= e($filtros['buscar'] ?? '') ?>" placeholder="Buscar en detalle...">
                    </div>
                </div>
                <div style="display:flex;gap:var(--spacing-3);margin-top:var(--spacing-4)">
                    <button type="submit" class="btn btn--primary btn--sm">Aplicar filtros</button>
                    <a href="<?= url('/admin/mantenimiento/logs') ?>" class="btn btn--outline btn--sm">Limpiar</a>
                </div>
            </form>
        </div>
    </details>
</div>

<!-- Actions Toolbar -->
<?php
$exportParams = http_build_query(array_filter($filtros));
?>
<div class="toolbar" style="margin-bottom:var(--spacing-4)">
    <div style="flex:1">
        <span style="color:var(--color-gray);font-size:0.875rem"><?= number_format($total) ?> registros encontrados</span>
    </div>
    <a href="<?= url('/admin/mantenimiento/logs/exportar' . ($exportParams ? '?' . $exportParams : '')) ?>" class="btn btn--outline btn--sm">Exportar CSV</a>
    <button type="button" class="btn btn--danger btn--sm" onclick="document.getElementById('modalLimpiar').classList.add('modal--open')">Limpiar Logs</button>
</div>

<!-- Logs Table -->
<div class="admin-card">
    <?php if (!empty($logs)): ?>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Usuario</th>
                        <th>M&oacute;dulo</th>
                        <th>Acci&oacute;n</th>
                        <th>Entidad</th>
                        <th>Detalle</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <?php
                        $accion = strtolower($log['accion'] ?? '');
                        $accionBadge = 'badge';
                        if (in_array($accion, ['crear', 'create'])) {
                            $accionBadge = 'badge log-badge--crear';
                        } elseif (in_array($accion, ['editar', 'update', 'actualizar'])) {
                            $accionBadge = 'badge log-badge--editar';
                        } elseif (in_array($accion, ['eliminar', 'delete', 'borrar'])) {
                            $accionBadge = 'badge log-badge--eliminar';
                        } elseif ($accion === 'login') {
                            $accionBadge = 'badge log-badge--login';
                        } elseif ($accion === 'toggle') {
                            $accionBadge = 'badge log-badge--toggle';
                        }

                        $detalle = $log['detalle'] ?? '';
                        $detalleTruncado = mb_strlen($detalle) > 50 ? mb_substr($detalle, 0, 50) . '...' : $detalle;
                        ?>
                        <tr class="logs-row" onclick="window.location='<?= url('/admin/mantenimiento/logs/' . $log['id']) ?>'" style="cursor:pointer">
                            <td>
                                <small><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></small>
                            </td>
                            <td><?= e($log['usuario_nombre'] ?? 'Sistema') ?></td>
                            <td><small><?= e($log['modulo'] ?? '') ?></small></td>
                            <td>
                                <span class="<?= $accionBadge ?>"><?= e($log['accion'] ?? '') ?></span>
                            </td>
                            <td>
                                <?php if (!empty($log['entidad_tipo'])): ?>
                                    <small><?= e($log['entidad_tipo']) ?><?= !empty($log['entidad_id']) ? ' #' . e($log['entidad_id']) : '' ?></small>
                                <?php else: ?>
                                    <small style="color:var(--color-gray)">&mdash;</small>
                                <?php endif; ?>
                            </td>
                            <td><small title="<?= e($detalle) ?>"><?= e($detalleTruncado) ?></small></td>
                            <td><small><?= e($log['ip'] ?? '') ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="padding:var(--spacing-6);text-align:center">
            <p style="color:var(--color-gray);margin:0">No se encontraron registros de auditor&iacute;a.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Págination -->
<?php if ($totalPages > 1): ?>
    <?php
    $queryParams = array_filter($filtros);
    ?>
    <div class="logs-págination">
        <?php if ($pagina > 1): ?>
            <a href="<?= url('/admin/mantenimiento/logs?' . http_build_query(array_merge($queryParams, ['pagina' => $pagina - 1]))) ?>" class="btn btn--outline btn--sm">&laquo; Anterior</a>
        <?php else: ?>
            <span class="btn btn--outline btn--sm" style="opacity:0.5;pointer-events:none">&laquo; Anterior</span>
        <?php endif; ?>

        <span class="logs-págination__info">P&aacute;gina <?= $pagina ?> de <?= $totalPages ?></span>

        <?php if ($pagina < $totalPages): ?>
            <a href="<?= url('/admin/mantenimiento/logs?' . http_build_query(array_merge($queryParams, ['pagina' => $pagina + 1]))) ?>" class="btn btn--outline btn--sm">Siguiente &raquo;</a>
        <?php else: ?>
            <span class="btn btn--outline btn--sm" style="opacity:0.5;pointer-events:none">Siguiente &raquo;</span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Modal Limpiar Logs -->
<div class="modal" id="modalLimpiar">
    <div class="modal__backdrop" onclick="document.getElementById('modalLimpiar').classList.remove('modal--open')"></div>
    <div class="modal__content">
        <div class="modal__header">
            <h3 style="margin:0">Limpiar Logs Antiguos</h3>
            <button type="button" class="modal__close" onclick="document.getElementById('modalLimpiar').classList.remove('modal--open')">&times;</button>
        </div>
        <form method="POST" action="<?= url('/admin/mantenimiento/logs/limpiar') ?>">
            <?= csrf_field() ?>
            <div class="modal__body">
                <p style="color:var(--color-danger);font-weight:600">Atenci&oacute;n: Esta acci&oacute;n no se puede deshacer.</p>
                <p>Se eliminar&aacute;n todos los registros de auditor&iacute;a con m&aacute;s de los d&iacute;as especificados.</p>
                <div class="form-group">
                    <label class="form-label">Eliminar logs con m&aacute;s de (d&iacute;as):</label>
                    <input type="number" name="dias" class="form-control" value="90" min="1" max="365" style="width:120px">
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" onclick="document.getElementById('modalLimpiar').classList.remove('modal--open')">Cancelar</button>
                <button type="submit" class="btn btn--danger">Limpiar Logs</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Activity Chart */
.activity-chart {
    display: flex;
    align-items: flex-end;
    gap: 3px;
    height: 120px;
    padding-bottom: 24px;
    position: relative;
}
.activity-chart__col {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
    justify-content: flex-end;
}
.activity-chart__bar {
    width: 100%;
    min-height: 2px;
    background: var(--color-primary, #2563eb);
    border-radius: 2px 2px 0 0;
    transition: opacity 0.2s;
}
.activity-chart__col:hover .activity-chart__bar {
    opacity: 0.7;
}
.activity-chart__label {
    font-size: 0.625rem;
    color: var(--color-gray);
    margin-top: 4px;
    position: absolute;
    bottom: 0;
}

/* Action badges */
.log-badge--crear {
    background: #dcfce7;
    color: #166534;
}
.log-badge--editar {
    background: #dbeafe;
    color: #1e40af;
}
.log-badge--eliminar {
    background: #fee2e2;
    color: #991b1b;
}
.log-badge--login {
    background: #f3f4f6;
    color: #374151;
}
.log-badge--toggle {
    background: #fef9c3;
    color: #854d0e;
}

/* Clickable rows */
.logs-row:hover {
    background: var(--color-light, #f9fafb);
}

/* Filters grid */
.logs-filters__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-4);
}

/* Págination */
.logs-págination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-4);
    margin-top: var(--spacing-6);
}
.logs-págination__info {
    font-size: 0.875rem;
    color: var(--color-gray);
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .logs-filters__grid {
        grid-template-columns: 1fr;
    }
    .activity-chart__label {
        display: none;
    }
}
</style>
