<?php
/**
 * Listado de cambios pendientes de comerciantes
 * Variables: $cambios, $conteos, $estadoActual
 */
?>

<div class="admin-breadcrumb">
    <a href="<?= url('/admin') ?>">Dashboard</a>
    <span>/</span>
    <span>Cambios Pendientes</span>
</div>

<h2 style="margin-bottom:1.25rem">Cambios de comerciantes</h2>

<!-- Tabs de estado -->
<div style="display:flex;gap:0.5rem;margin-bottom:1.25rem;flex-wrap:wrap">
    <a href="<?= url('/admin/cambios-pendientes?estado=pendiente') ?>"
       class="btn <?= $estadoActual === 'pendiente' ? 'btn--primary' : 'btn--outline' ?>"
       style="font-size:0.85rem">
        ⏳ Pendientes <span style="background:rgba(255,255,255,0.2);padding:0.1rem 0.4rem;border-radius:10px;margin-left:0.25rem"><?= $conteos['pendiente'] ?></span>
    </a>
    <a href="<?= url('/admin/cambios-pendientes?estado=aprobado') ?>"
       class="btn <?= $estadoActual === 'aprobado' ? 'btn--primary' : 'btn--outline' ?>"
       style="font-size:0.85rem">
        ✅ Aprobados <span style="opacity:0.7;margin-left:0.25rem"><?= $conteos['aprobado'] ?></span>
    </a>
    <a href="<?= url('/admin/cambios-pendientes?estado=rechazado') ?>"
       class="btn <?= $estadoActual === 'rechazado' ? 'btn--primary' : 'btn--outline' ?>"
       style="font-size:0.85rem">
        ❌ Rechazados <span style="opacity:0.7;margin-left:0.25rem"><?= $conteos['rechazado'] ?></span>
    </a>
</div>

<?php if (empty($cambios)): ?>
    <div class="admin-card">
        <div class="admin-card__body" style="text-align:center;padding:2rem">
            <p style="color:var(--color-gray);font-size:1.1rem">
                <?= $estadoActual === 'pendiente' ? '🎉 No hay cambios pendientes de revisión.' : 'No hay registros.' ?>
            </p>
        </div>
    </div>
<?php else: ?>
    <div class="admin-card">
        <div class="admin-card__body" style="padding:0">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Comercio</th>
                        <th>Comerciante</th>
                        <th>Cambios</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cambios as $c): ?>
                        <?php
                        $datos = json_decode($c['cambios_json'], true) ?: [];
                        $numCambios = count($datos);
                        $campos = array_keys($datos);
                        $resumen = implode(', ', array_slice($campos, 0, 3));
                        if (count($campos) > 3) $resumen .= '...';
                        ?>
                        <tr>
                            <td>
                                <strong><?= e($c['comercio_nombre']) ?></strong>
                            </td>
                            <td>
                                <span style="font-size:0.85rem"><?= e($c['usuario_nombre']) ?></span><br>
                                <small style="color:var(--color-gray)"><?= e($c['usuario_email']) ?></small>
                            </td>
                            <td>
                                <span style="font-size:0.85rem"><?= $numCambios ?> campo<?= $numCambios > 1 ? 's' : '' ?></span><br>
                                <small style="color:var(--color-gray)"><?= e($resumen) ?></small>
                            </td>
                            <td>
                                <span style="font-size:0.85rem"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></span>
                            </td>
                            <td>
                                <?php if ($c['estado'] === 'pendiente'): ?>
                                    <span style="background:#FEF3C7;color:#92400E;padding:0.2rem 0.5rem;border-radius:10px;font-size:0.8rem">⏳ Pendiente</span>
                                <?php elseif ($c['estado'] === 'aprobado'): ?>
                                    <span style="background:#F0FDF4;color:#166534;padding:0.2rem 0.5rem;border-radius:10px;font-size:0.8rem">✅ Aprobado</span>
                                <?php else: ?>
                                    <span style="background:#FEE2E2;color:#991B1B;padding:0.2rem 0.5rem;border-radius:10px;font-size:0.8rem">❌ Rechazado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= url('/admin/cambios-pendientes/' . $c['id']) ?>" class="btn btn--outline btn--sm">
                                    Revisar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
