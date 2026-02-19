<?php
/**
 * Admin - Reportes de reseñas
 * Variables: $reportes, $currentPage, $totalPages, $total
 */
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/resenas') ?>">Reseñas</a> &rsaquo;
    <span>Reportes</span>
</div>

<div class="toolbar">
    <h2 style="margin:0;flex:1">Reportes de Reseñas <small style="color:var(--color-gray);font-weight:400">(<?= number_format($total) ?>)</small></h2>
    <a href="<?= url('/admin/resenas') ?>" class="btn btn--outline btn--sm">&larr; Volver a reseñas</a>
</div>

<?php if (!empty($reportes)): ?>
    <div class="admin-card">
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Reseña</th>
                        <th>Autor reseña</th>
                        <th>Comercio</th>
                        <th>Motivo reporte</th>
                        <th>Descripción</th>
                        <th>Estado reseña</th>
                        <th>Fecha reporte</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportes as $reporte): ?>
                        <tr>
                            <td>
                                <div class="stars-display">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?= $i <= $reporte['calificacion'] ? 'star--filled' : '' ?>">&#9733;</span>
                                    <?php endfor; ?>
                                </div>
                                <small style="color:var(--color-gray)"><?= e(truncate($reporte['comentario'] ?? '', 40)) ?></small>
                            </td>
                            <td><strong><?= e($reporte['nombre_autor']) ?></strong></td>
                            <td><?= e($reporte['comercio_nombre']) ?></td>
                            <td><span class="badge badge--warning"><?= e($reporte['motivo']) ?></span></td>
                            <td><small><?= e(truncate($reporte['descripcion'] ?? '', 60)) ?></small></td>
                            <td>
                                <?php
                                $badgeClass = match($reporte['resena_estado']) {
                                    'pendiente' => 'badge--warning',
                                    'aprobada'  => 'badge--success',
                                    'rechazada' => 'badge--danger',
                                    default     => ''
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($reporte['resena_estado']) ?></span>
                            </td>
                            <td><small><?= fecha_es($reporte['created_at'], 'd/m/Y H:i') ?></small></td>
                            <td>
                                <div class="admin-table__actions">
                                    <a href="<?= url('/admin/resenas/' . $reporte['resena_id']) ?>"
                                       class="btn btn--outline btn--sm">Ver reseña</a>
                                    <form method="POST" action="<?= url('/admin/resenas/reportes/eliminar/' . $reporte['id']) ?>" style="display:inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--outline btn--sm" onclick="return confirm('¿Descartar este reporte?')">Descartar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="admin-págination">
                <div class="admin-págination__info">
                    Página <?= $currentPage ?> de <?= $totalPages ?> (<?= number_format($total) ?> reportes)
                </div>
                <div class="admin-págination__links">
                    <?php
                    $baseUrl     = '/admin/resenas/reportes';
                    $queryParams = [];
                    include BASE_PATH . '/views/partials/págination.php';
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="admin-card">
        <div style="text-align:center;padding:3rem;color:var(--color-gray)">
            <div style="font-size:2.5rem;margin-bottom:0.5rem">&#9989;</div>
            <p><strong>Sin reportes pendientes</strong></p>
            <p style="font-size:0.875rem">No hay reportes de reseñas por revisar.</p>
        </div>
    </div>
<?php endif; ?>
