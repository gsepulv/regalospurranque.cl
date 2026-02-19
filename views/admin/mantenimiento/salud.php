<?php
/**
 * Admin - Mantenimiento > Salud del Sistema
 * Variables: $checks, $score, $serverInfo, $dbSize, $totalDbSize
 */
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/mantenimiento') ?>">Mantenimiento</a> &rsaquo;
    <span>Salud del Sistema</span>
</div>

<div class="toolbar">
    <h2 style="margin:0;flex:1">Salud del Sistema</h2>
</div>

<?php
$currentTab = 'salud';
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

<?php
// Determine score color
$scoreColor = '#dc2626'; // red
if ($score >= 80) {
    $scoreColor = '#059669'; // green
} elseif ($score >= 50) {
    $scoreColor = '#d97706'; // yellow/orange
}

// Score gauge calculations
$circumference = 2 * M_PI * 80; // radius = 80
$dashOffset = $circumference - ($circumference * $score / 100);

/**
 * Format bytes to human-readable size
 */
function formatBytes($bytes, $precision = 2) {
    if ($bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $pow = floor(log($bytes) / log(1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}
?>

<style>
    .health-score {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 2rem;
    }
    .health-score__gauge {
        position: relative;
        width: 200px;
        height: 200px;
    }
    .health-score__gauge svg {
        width: 200px;
        height: 200px;
        transform: rotate(-90deg);
    }
    .health-score__gauge .gauge-bg {
        fill: none;
        stroke: #e5e7eb;
        stroke-width: 10;
    }
    .health-score__gauge .gauge-fill {
        fill: none;
        stroke: <?= $scoreColor ?>;
        stroke-width: 10;
        stroke-linecap: round;
        stroke-dasharray: <?= $circumference ?>;
        stroke-dashoffset: <?= $dashOffset ?>;
        transition: stroke-dashoffset 1s ease;
    }
    .health-score__number {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 3rem;
        font-weight: 800;
        color: <?= $scoreColor ?>;
        line-height: 1;
    }
    .health-score__number small {
        font-size: 1.2rem;
        font-weight: 600;
    }
    .health-score__label {
        margin-top: 0.75rem;
        font-size: 1rem;
        color: var(--color-gray);
        font-weight: 500;
    }

    .check-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .check-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--color-border);
    }
    .check-item:last-child {
        border-bottom: none;
    }
    .check-item__icon {
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 700;
        color: #fff;
    }
    .check-item__icon--ok {
        background: #059669;
    }
    .check-item__icon--warning {
        background: #d97706;
    }
    .check-item__icon--error {
        background: #dc2626;
    }
    .check-item__body {
        flex: 1;
        min-width: 0;
    }
    .check-item__name {
        font-weight: 600;
        font-size: 0.9rem;
    }
    .check-item__detail {
        font-size: 0.8rem;
        color: var(--color-gray);
        margin-top: 2px;
    }
    .check-item__points {
        flex-shrink: 0;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--color-gray);
        white-space: nowrap;
    }

    .db-size-table .total-row td {
        font-weight: 700;
        border-top: 2px solid var(--color-border);
    }
</style>

<!-- Score Gauge -->
<div class="admin-card" style="margin-bottom:var(--spacing-6)">
    <div class="health-score">
        <div class="health-score__gauge">
            <svg viewBox="0 0 200 200">
                <circle class="gauge-bg" cx="100" cy="100" r="80"/>
                <circle class="gauge-fill" cx="100" cy="100" r="80"/>
            </svg>
            <div class="health-score__number">
                <?= (int)$score ?><small>/100</small>
            </div>
        </div>
        <div class="health-score__label">Puntuaci&oacute;n de Salud</div>
        <form method="POST" action="<?= url('/admin/mantenimiento/salud/refresh') ?>" style="margin-top:1rem">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn--primary btn--sm">Verificar nuevamente</button>
        </form>
    </div>
</div>

<!-- Server Info -->
<div class="stats-grid" style="margin-bottom:var(--spacing-6)">
    <div class="stat-card">
        <div class="stat-card__number" style="font-size:1.3rem"><?= e($serverInfo['php_version'] ?? 'N/A') ?></div>
        <div class="stat-card__label">PHP Version</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__number" style="font-size:1.3rem"><?= e($serverInfo['mysql_version'] ?? 'N/A') ?></div>
        <div class="stat-card__label">MySQL Version</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__number" style="font-size:1.3rem"><?= e($serverInfo['disk_space'] ?? 'N/A') ?></div>
        <div class="stat-card__label">Disco Libre</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__number" style="font-size:1.3rem"><?= e($serverInfo['memory_limit'] ?? 'N/A') ?></div>
        <div class="stat-card__label">Memoria PHP</div>
    </div>
</div>

<!-- Health Checks -->
<div class="admin-card" style="margin-bottom:var(--spacing-6)">
    <div style="padding:1rem 1rem 0.5rem;border-bottom:1px solid var(--color-border)">
        <h3 style="margin:0 0 0.5rem">Verificaciones del Sistema</h3>
    </div>
    <?php if (!empty($checks)): ?>
        <ul class="check-list">
            <?php foreach ($checks as $check): ?>
                <li class="check-item">
                    <div class="check-item__icon check-item__icon--<?= e($check['status']) ?>">
                        <?php if ($check['status'] === 'ok'): ?>
                            &#10003;
                        <?php elseif ($check['status'] === 'warning'): ?>
                            &#9888;
                        <?php else: ?>
                            &#10007;
                        <?php endif; ?>
                    </div>
                    <div class="check-item__body">
                        <div class="check-item__name"><?= e($check['name']) ?></div>
                        <div class="check-item__detail"><?= e($check['detail']) ?></div>
                    </div>
                    <div class="check-item__points"><?= (int)$check['points'] ?> / <?= (int)$check['maxPoints'] ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div style="padding:2rem;text-align:center;color:var(--color-gray)">
            <p>No hay verificaciones disponibles. Ejecuta una verificaci&oacute;n.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Database Size -->
<div class="admin-card">
    <div class="collapsible__header" style="padding:1rem;cursor:pointer;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--color-border)">
        <h3 style="margin:0">Tama&ntilde;o de Base de Datos</h3>
        <span style="font-size:0.875rem;color:var(--color-gray)"><?= formatBytes($totalDbSize) ?> total &mdash; clic para expandir</span>
    </div>
    <div class="collapsible__body collapsed" style="max-height:0;overflow:hidden;transition:max-height 0.3s ease">
        <?php if (!empty($dbSize)): ?>
            <div class="admin-table-wrapper">
                <table class="admin-table db-size-table">
                    <thead>
                        <tr>
                            <th>Tabla</th>
                            <th style="text-align:right;width:150px">Tama&ntilde;o</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dbSize as $table): ?>
                            <tr>
                                <td><code style="font-size:0.8rem"><?= e($table['name'] ?? $table['tabla'] ?? '') ?></code></td>
                                <td style="text-align:right"><?= formatBytes($table['size'] ?? $table['tamano'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td><strong>Total</strong></td>
                            <td style="text-align:right"><strong><?= formatBytes($totalDbSize) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="padding:2rem;text-align:center;color:var(--color-gray)">
                <p>No se pudo obtener informaci&oacute;n del tama&ntilde;o de la base de datos.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
