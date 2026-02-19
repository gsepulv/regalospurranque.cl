<?php
/**
 * Admin - Mantenimiento > Backups
 * Variables: $backups (array), $totalSize (int bytes), $diskFree (int bytes)
 */

function formatBytes($bytes, $precision = 2) {
    if ($bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $pow = floor(log($bytes, 1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/mantenimiento/backups') ?>">Mantenimiento</a> &rsaquo;
    <span>Backups</span>
</div>

<?php
$currentTab = 'backups';
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

<div class="toolbar">
    <h2 style="margin:0;flex:1">Backups</h2>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(3, 1fr);margin-bottom:var(--spacing-6)">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__number"><?= count($backups) ?></div>
        <div class="stat-card__label">Total Backups</div>
    </div>
    <div class="stat-card stat-card--warning">
        <div class="stat-card__number"><?= formatBytes($totalSize) ?></div>
        <div class="stat-card__label">Espacio Usado</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__number"><?= formatBytes($diskFreeSpace) ?></div>
        <div class="stat-card__label">Espacio Libre</div>
    </div>
</div>

<!-- Action Cards -->
<div class="backup-actions-grid" style="margin-bottom:var(--spacing-6)">
    <div class="admin-card">
        <div style="padding:var(--spacing-6);text-align:center">
            <div style="font-size:2.5rem;margin-bottom:var(--spacing-3)">&#128451;</div>
            <h3 style="margin:0 0 var(--spacing-2)">Backup BD</h3>
            <p style="color:var(--color-gray);font-size:0.875rem;margin-bottom:var(--spacing-4)">Exporta la base de datos completa en formato SQL comprimido.</p>
            <form method="POST" action="<?= url('/admin/mantenimiento/backup/db') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--primary" data-confirm="&iquest;Generar backup? Esto puede tomar unos minutos.">Generar Backup BD</button>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div style="padding:var(--spacing-6);text-align:center">
            <div style="font-size:2.5rem;margin-bottom:var(--spacing-3)">&#128193;</div>
            <h3 style="margin:0 0 var(--spacing-2)">Backup Archivos</h3>
            <p style="color:var(--color-gray);font-size:0.875rem;margin-bottom:var(--spacing-4)">Comprime todos los archivos subidos (im&aacute;genes, documentos, etc).</p>
            <form method="POST" action="<?= url('/admin/mantenimiento/backup/archivos') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--primary" data-confirm="&iquest;Generar backup? Esto puede tomar unos minutos.">Generar Backup Archivos</button>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div style="padding:var(--spacing-6);text-align:center">
            <div style="font-size:2.5rem;margin-bottom:var(--spacing-3)">&#128230;</div>
            <h3 style="margin:0 0 var(--spacing-2)">Backup Completo</h3>
            <p style="color:var(--color-gray);font-size:0.875rem;margin-bottom:var(--spacing-4)">Base de datos + archivos en un solo paquete comprimido.</p>
            <form method="POST" action="<?= url('/admin/mantenimiento/backup/completo') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--primary" data-confirm="&iquest;Generar backup? Esto puede tomar unos minutos.">Generar Backup Completo</button>
            </form>
        </div>
    </div>
</div>

<!-- Backups Table -->
<div class="admin-card">
    <div class="admin-card__header" style="padding:var(--spacing-4) var(--spacing-6)">
        <h3 style="margin:0">Backups existentes</h3>
    </div>
    <?php if (!empty($backups)): ?>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Tama&ntilde;o</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td>
                                <strong><?= e($backup['nombre']) ?></strong>
                            </td>
                            <td>
                                <?php
                                $tipoBadge = 'badge';
                                $tipoLabel = $backup['tipo'] ?? 'otro';
                                if ($tipoLabel === 'db') {
                                    $tipoBadge = 'badge badge--info';
                                    $tipoLabel = 'BD';
                                } elseif ($tipoLabel === 'files') {
                                    $tipoBadge = 'badge badge--success';
                                    $tipoLabel = 'Archivos';
                                } elseif ($tipoLabel === 'full') {
                                    $tipoBadge = 'badge badge--warning';
                                    $tipoLabel = 'Completo';
                                }
                                ?>
                                <span class="<?= $tipoBadge ?>"><?= e($tipoLabel) ?></span>
                            </td>
                            <td><?= formatBytes($backup['tamano'] ?? 0) ?></td>
                            <td>
                                <?php if (!empty($backup['fecha'])): ?>
                                    <?= date('d/m/Y H:i', strtotime($backup['fecha'])) ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <a href="<?= url('/admin/mantenimiento/backup/descargar/' . urlencode($backup['nombre'])) ?>"
                                       class="btn btn--outline btn--sm"
                                       title="Descargar">Descargar</a>
                                    <form method="POST"
                                          action="<?= url('/admin/mantenimiento/backup/eliminar/' . urlencode($backup['nombre'])) ?>"
                                          style="display:inline">
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                                class="btn btn--danger btn--sm"
                                                data-confirm="&iquest;Eliminar este backup? Esta acci&oacute;n no se puede deshacer.">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="padding:var(--spacing-6);text-align:center">
            <p style="color:var(--color-gray);margin:0">Sin backups disponibles. Genera tu primer backup usando los botones de arriba.</p>
        </div>
    <?php endif; ?>
</div>

<p style="margin-top:var(--spacing-4);font-size:0.8125rem;color:var(--color-gray)">
    Los backups se almacenan en <code>storage/backups/</code> protegido por <code>.htaccess</code>
</p>

<style>
.backup-actions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-4);
}
@media (max-width: 768px) {
    .backup-actions-grid {
        grid-template-columns: 1fr;
    }
}
</style>
