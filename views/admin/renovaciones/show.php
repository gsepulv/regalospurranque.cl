<?php
/**
 * Admin - Detalle de solicitud de renovación
 * Variables: $renovacion, $comercio, $planActual, $planSolicitado, $historial
 */
$esPendiente = $renovacion['estado'] === 'pendiente';
?>

<div class="admin-breadcrumb">
    <a href="<?= url('/admin') ?>">Dashboard</a>
    <span>/</span>
    <a href="<?= url('/admin/renovaciones') ?>">Renovaciones</a>
    <span>/</span>
    <span>Revisar #<?= $renovacion['id'] ?></span>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.5rem">
    <h2 style="margin:0">Renovaci&oacute;n: <?= e($renovacion['comercio_nombre']) ?></h2>
    <?php if ($renovacion['estado'] === 'pendiente'): ?>
        <span style="background:#FEF3C7;color:#92400E;padding:0.3rem 0.75rem;border-radius:10px;font-size:0.85rem;font-weight:600">&#9203; Pendiente</span>
    <?php elseif ($renovacion['estado'] === 'aprobada'): ?>
        <span style="background:#F0FDF4;color:#166534;padding:0.3rem 0.75rem;border-radius:10px;font-size:0.85rem;font-weight:600">&#9989; Aprobada</span>
    <?php else: ?>
        <span style="background:#FEE2E2;color:#991B1B;padding:0.3rem 0.75rem;border-radius:10px;font-size:0.85rem;font-weight:600">&#10060; Rechazada</span>
    <?php endif; ?>
</div>

<!-- Datos de la solicitud -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem">

    <!-- Info solicitud -->
    <div class="admin-card">
        <div class="admin-card__header">Datos de la solicitud</div>
        <div class="admin-card__body">
            <table style="width:100%;font-size:0.9rem">
                <tr><td style="padding:0.35rem 0;color:var(--color-gray)">Solicitante</td><td style="padding:0.35rem 0"><strong><?= e($renovacion['usuario_nombre']) ?></strong><br><small><?= e($renovacion['usuario_email']) ?></small></td></tr>
                <tr><td style="padding:0.35rem 0;color:var(--color-gray)">Fecha solicitud</td><td style="padding:0.35rem 0"><?= date('d/m/Y H:i', strtotime($renovacion['created_at'])) ?></td></tr>
                <tr><td style="padding:0.35rem 0;color:var(--color-gray)">M&eacute;todo de pago</td><td style="padding:0.35rem 0"><?= e(ucfirst($renovacion['metodo_pago'] ?? '—')) ?></td></tr>
                <tr><td style="padding:0.35rem 0;color:var(--color-gray)">Monto</td><td style="padding:0.35rem 0"><?= $renovacion['monto'] ? '$' . number_format((float)$renovacion['monto'], 0, ',', '.') . ' CLP' : '—' ?></td></tr>
                <?php if ($renovacion['fecha_pago']): ?>
                    <tr><td style="padding:0.35rem 0;color:var(--color-gray)">Fecha de pago</td><td style="padding:0.35rem 0"><?= date('d/m/Y', strtotime($renovacion['fecha_pago'])) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Info comercio -->
    <div class="admin-card">
        <div class="admin-card__header">Comercio</div>
        <div class="admin-card__body">
            <table style="width:100%;font-size:0.9rem">
                <tr><td style="padding:0.35rem 0;color:var(--color-gray)">Nombre</td><td style="padding:0.35rem 0"><strong><?= e($comercio['nombre'] ?? '') ?></strong></td></tr>
                <tr><td style="padding:0.35rem 0;color:var(--color-gray)">Plan actual</td><td style="padding:0.35rem 0"><?= e($planActual['icono'] ?? '') ?> <?= e($planActual['nombre'] ?? $renovacion['plan_actual']) ?></td></tr>
                <tr><td style="padding:0.35rem 0;color:var(--color-gray)">Plan solicitado</td><td style="padding:0.35rem 0"><strong><?= e($planSolicitado['icono'] ?? '') ?> <?= e($planSolicitado['nombre'] ?? $renovacion['plan_solicitado']) ?></strong></td></tr>
                <tr><td style="padding:0.35rem 0;color:var(--color-gray)">Vigencia actual</td><td style="padding:0.35rem 0"><?= $comercio['plan_fin'] ? date('d/m/Y', strtotime($comercio['plan_fin'])) : 'Sin fecha' ?></td></tr>
                <tr><td style="padding:0.35rem 0;color:var(--color-gray)">Estado</td><td style="padding:0.35rem 0"><?= $comercio['activo'] ? '<span style="color:#166534">Activo</span>' : '<span style="color:#991B1B">Inactivo</span>' ?></td></tr>
                <?php if ($planSolicitado): ?>
                    <tr><td style="padding:0.35rem 0;color:var(--color-gray)">Duraci&oacute;n plan</td><td style="padding:0.35rem 0"><?= (int)($planSolicitado['duracion_dias'] ?? 30) ?> d&iacute;as</td></tr>
                <?php endif; ?>
            </table>
            <div style="margin-top:0.75rem">
                <a href="<?= url('/admin/comercios/editar/' . $comercio['id']) ?>" class="btn btn--outline btn--sm" target="_blank">Ver en admin</a>
            </div>
        </div>
    </div>
</div>

<!-- Comprobante de pago -->
<?php if ($renovacion['comprobante_pago']): ?>
    <div class="admin-card" style="margin-bottom:1.25rem">
        <div class="admin-card__header">Comprobante de pago</div>
        <div class="admin-card__body" style="text-align:center">
            <img src="<?= url('/assets/img/comprobantes/' . $renovacion['comprobante_pago']) ?>"
                 alt="Comprobante" style="max-width:500px;max-height:600px;border-radius:8px;border:1px solid #e2e8f0"
                 loading="lazy">
        </div>
    </div>
<?php endif; ?>

<!-- Resultado si ya fue procesada -->
<?php if (!$esPendiente): ?>
    <div class="admin-card" style="margin-bottom:1.25rem">
        <div class="admin-card__header">Resultado</div>
        <div class="admin-card__body">
            <p><strong>Procesada el:</strong> <?= date('d/m/Y H:i', strtotime($renovacion['updated_at'])) ?></p>
            <?php if ($renovacion['motivo_rechazo']): ?>
                <p><strong>Motivo de rechazo:</strong> <?= e($renovacion['motivo_rechazo']) ?></p>
            <?php endif; ?>
            <?php if ($renovacion['notas_admin']): ?>
                <p><strong>Notas admin:</strong> <?= e($renovacion['notas_admin']) ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Acciones (solo si pendiente) -->
<?php if ($esPendiente): ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem">

        <!-- Aprobar -->
        <div class="admin-card" style="border-top:3px solid #059669">
            <div class="admin-card__header">&#9989; Aprobar renovaci&oacute;n</div>
            <div class="admin-card__body">
                <form method="POST" action="<?= url('/admin/renovaciones/aprobar/' . $renovacion['id']) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group" style="margin-bottom:1rem">
                        <label class="form-label" for="notas_aprobar">Notas internas (opcional)</label>
                        <textarea id="notas_aprobar" name="notas" class="form-control" rows="2" placeholder="Notas internas..."></textarea>
                    </div>
                    <p style="font-size:0.85rem;color:var(--color-gray);margin:0 0 1rem">
                        Al aprobar: el comercio se reactivar&aacute; con plan <strong><?= e($planSolicitado['nombre'] ?? $renovacion['plan_solicitado']) ?></strong>
                        por <?= (int)($planSolicitado['duracion_dias'] ?? 30) ?> d&iacute;as.
                    </p>
                    <button type="submit" class="btn btn--success" onclick="return confirm('&iquest;Aprobar esta renovaci&oacute;n?')">
                        &#9989; Aprobar y reactivar
                    </button>
                </form>
            </div>
        </div>

        <!-- Rechazar -->
        <div class="admin-card" style="border-top:3px solid #dc2626">
            <div class="admin-card__header">&#10060; Rechazar solicitud</div>
            <div class="admin-card__body">
                <form method="POST" action="<?= url('/admin/renovaciones/rechazar/' . $renovacion['id']) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group" style="margin-bottom:1rem">
                        <label class="form-label" for="motivo">Motivo del rechazo <span style="color:var(--color-danger)">*</span></label>
                        <textarea id="motivo" name="motivo" class="form-control" rows="3" required minlength="10" placeholder="Explica al comerciante por qu&eacute; se rechaza..."></textarea>
                        <small style="color:var(--color-gray)">M&iacute;nimo 10 caracteres. Se enviar&aacute; por correo al comerciante.</small>
                    </div>
                    <div class="form-group" style="margin-bottom:1rem">
                        <label class="form-label" for="notas_rechazar">Notas internas (opcional)</label>
                        <textarea id="notas_rechazar" name="notas" class="form-control" rows="2" placeholder="Notas internas..."></textarea>
                    </div>
                    <button type="submit" class="btn btn--danger" onclick="return confirm('&iquest;Rechazar esta solicitud?')">
                        &#10060; Rechazar
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Historial -->
<?php if (!empty($historial) && count($historial) > 1): ?>
    <div class="admin-card">
        <div class="admin-card__header">Historial de renovaciones de este comercio</div>
        <div class="admin-card__body" style="padding:0">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Plan</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $h): ?>
                        <tr style="<?= $h['id'] == $renovacion['id'] ? 'background:#f8fafc;font-weight:600' : '' ?>">
                            <td><?= $h['id'] ?></td>
                            <td><?= e($h['plan_actual']) ?> &rarr; <?= e($h['plan_solicitado']) ?></td>
                            <td><?= $h['monto'] ? '$' . number_format((float)$h['monto'], 0, ',', '.') : '—' ?></td>
                            <td>
                                <?php if ($h['estado'] === 'pendiente'): ?>
                                    <span style="color:#92400E">Pendiente</span>
                                <?php elseif ($h['estado'] === 'aprobada'): ?>
                                    <span style="color:#166534">Aprobada</span>
                                <?php else: ?>
                                    <span style="color:#991B1B">Rechazada</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($h['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<div style="margin-top:1rem">
    <a href="<?= url('/admin/renovaciones') ?>" class="btn btn--outline">&larr; Volver a renovaciones</a>
</div>
