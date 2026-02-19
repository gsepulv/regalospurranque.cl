<?php
/**
 * Admin - Detalle de reseña
 * Variables: $resena, $reportes
 */
$badgeClass = match($resena['estado']) {
    'pendiente' => 'badge--warning',
    'aprobada'  => 'badge--success',
    'rechazada' => 'badge--danger',
    default     => ''
};
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/resenas') ?>">Reseñas</a> &rsaquo;
    <span>Reseña #<?= $resena['id'] ?></span>
</div>

<div class="toolbar">
    <h2 style="margin:0;flex:1">
        Reseña #<?= $resena['id'] ?>
        <span class="badge <?= $badgeClass ?>" style="font-size:0.75rem;vertical-align:middle"><?= ucfirst($resena['estado']) ?></span>
    </h2>
    <a href="<?= url('/admin/resenas') ?>" class="btn btn--outline btn--sm">&larr; Volver</a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--spacing-6);align-items:start">

    <!-- Columna principal -->
    <div>
        <!-- Datos de la reseña -->
        <div class="admin-card" style="margin-bottom:var(--spacing-6)">
            <div style="padding:var(--spacing-6)">

                <!-- Cabecera: autor + calificación -->
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:var(--spacing-4)">
                    <div>
                        <h3 style="margin:0 0 4px"><?= e($resena['nombre_autor']) ?></h3>
                        <p style="margin:0;color:var(--color-gray);font-size:0.875rem">
                            <?= e($resena['email_autor'] ?? 'Sin email') ?>
                            &middot; IP: <?= e($resena['ip'] ?? 'N/A') ?>
                        </p>
                    </div>
                    <div class="stars-display stars-display--lg">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= $resena['calificacion'] ? 'star--filled' : '' ?>">&#9733;</span>
                        <?php endfor; ?>
                        <span style="font-size:1.25rem;font-weight:600;margin-left:4px"><?= $resena['calificacion'] ?>/5</span>
                    </div>
                </div>

                <!-- Comercio -->
                <p style="margin:0 0 var(--spacing-4);font-size:0.875rem">
                    <strong>Comercio:</strong>
                    <a href="<?= url('/comercio/' . $resena['comercio_slug']) ?>" target="_blank">
                        <?= e($resena['comercio_nombre']) ?>
                    </a>
                </p>

                <!-- Comentario -->
                <div style="background:var(--color-light);border-radius:var(--radius-md);padding:var(--spacing-4);margin-bottom:var(--spacing-4)">
                    <p style="margin:0;line-height:1.6"><?= nl2br(e($resena['comentario'] ?? 'Sin comentario')) ?></p>
                </div>

                <!-- Fecha -->
                <p style="margin:0;color:var(--color-gray);font-size:0.8rem">
                    Publicada el <?= fecha_es($resena['created_at'], 'd/m/Y H:i') ?>
                </p>

                <!-- Respuesta del comercio -->
                <?php if (!empty($resena['respuesta_comercio'])): ?>
                    <div style="margin-top:var(--spacing-4);border-left:3px solid var(--color-primary);padding-left:var(--spacing-4)">
                        <p style="margin:0 0 4px;font-weight:600;font-size:0.875rem">Respuesta del comercio:</p>
                        <p style="margin:0;line-height:1.6"><?= nl2br(e($resena['respuesta_comercio'])) ?></p>
                        <?php if (!empty($resena['fecha_respuesta'])): ?>
                            <p style="margin:4px 0 0;color:var(--color-gray);font-size:0.75rem">
                                Respondida el <?= fecha_es($resena['fecha_respuesta'], 'd/m/Y H:i') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulario de respuesta -->
        <div class="admin-card" style="margin-bottom:var(--spacing-6)">
            <div style="padding:var(--spacing-6)">
                <h3 style="margin:0 0 var(--spacing-4)">
                    <?= !empty($resena['respuesta_comercio']) ? 'Editar respuesta' : 'Responder a esta reseña' ?>
                </h3>
                <form method="POST" action="<?= url('/admin/resenas/responder/' . $resena['id']) ?>">
                    <?= csrf_field() ?>
                    <textarea name="respuesta"
                              class="form-control"
                              rows="4"
                              placeholder="Escribe una respuesta..."
                              data-maxlength="2000"
                              style="width:100%;margin-bottom:var(--spacing-3)"><?= e($resena['respuesta_comercio'] ?? '') ?></textarea>
                    <button type="submit" class="btn btn--primary btn--sm">Guardar respuesta</button>
                </form>
            </div>
        </div>

        <!-- Reportes de esta reseña -->
        <?php if (!empty($reportes)): ?>
            <div class="admin-card">
                <div style="padding:var(--spacing-6)">
                    <h3 style="margin:0 0 var(--spacing-4)">
                        Reportes <span class="badge badge--danger"><?= count($reportes) ?></span>
                    </h3>
                    <div class="admin-table-wrapper">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Motivo</th>
                                    <th>Descripción</th>
                                    <th>IP</th>
                                    <th>Fecha</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportes as $reporte): ?>
                                    <tr>
                                        <td><span class="badge badge--warning"><?= e($reporte['motivo']) ?></span></td>
                                        <td><small><?= e(truncate($reporte['descripcion'] ?? '', 80)) ?></small></td>
                                        <td><small><?= e($reporte['ip'] ?? 'N/A') ?></small></td>
                                        <td><small><?= fecha_es($reporte['created_at'], 'd/m/Y H:i') ?></small></td>
                                        <td>
                                            <form method="POST" action="<?= url('/admin/resenas/reportes/eliminar/' . $reporte['id']) ?>" style="display:inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn--outline btn--sm" onclick="return confirm('¿Descartar este reporte?')">Descartar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar: Acciones -->
    <div>
        <div class="admin-card">
            <div style="padding:var(--spacing-6)">
                <h3 style="margin:0 0 var(--spacing-4)">Acciones</h3>

                <div style="display:flex;flex-direction:column;gap:var(--spacing-3)">
                    <?php if ($resena['estado'] !== 'aprobada'): ?>
                        <form method="POST" action="<?= url('/admin/resenas/aprobar/' . $resena['id']) ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn--success" style="width:100%">&#10003; Aprobar</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($resena['estado'] !== 'rechazada'): ?>
                        <form method="POST" action="<?= url('/admin/resenas/rechazar/' . $resena['id']) ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn--outline" style="width:100%">&#10007; Rechazar</button>
                        </form>
                    <?php endif; ?>

                    <hr style="border:none;border-top:1px solid var(--color-border);margin:var(--spacing-2) 0">

                    <form method="POST" action="<?= url('/admin/resenas/eliminar/' . $resena['id']) ?>" onsubmit="return confirm('¿Eliminar esta reseña permanentemente?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn--danger" style="width:100%">Eliminar reseña</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info adicional -->
        <div class="admin-card" style="margin-top:var(--spacing-4)">
            <div style="padding:var(--spacing-6)">
                <h3 style="margin:0 0 var(--spacing-4)">Información</h3>
                <table style="width:100%;font-size:0.875rem">
                    <tr>
                        <td style="padding:4px 0;color:var(--color-gray)">ID</td>
                        <td style="padding:4px 0;text-align:right">#<?= $resena['id'] ?></td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;color:var(--color-gray)">Estado</td>
                        <td style="padding:4px 0;text-align:right"><span class="badge <?= $badgeClass ?>"><?= ucfirst($resena['estado']) ?></span></td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;color:var(--color-gray)">Calificación</td>
                        <td style="padding:4px 0;text-align:right"><?= $resena['calificacion'] ?>/5</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;color:var(--color-gray)">Fecha</td>
                        <td style="padding:4px 0;text-align:right"><?= fecha_es($resena['created_at'], 'd/m/Y') ?></td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;color:var(--color-gray)">IP</td>
                        <td style="padding:4px 0;text-align:right"><?= e($resena['ip'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;color:var(--color-gray)">Reportes</td>
                        <td style="padding:4px 0;text-align:right"><?= count($reportes) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
