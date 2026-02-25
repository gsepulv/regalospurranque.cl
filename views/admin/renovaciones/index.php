<?php
/**
 * Admin - Listado de solicitudes de renovación
 * Variables: $renovaciones, $conteos, $estadoActual, $page, $totalPages, $total
 */
?>

<div class="admin-breadcrumb">
    <a href="<?= url('/admin') ?>">Dashboard</a>
    <span>/</span>
    <span>Renovaciones</span>
</div>

<h2 style="margin-bottom:1.25rem">Solicitudes de renovaci&oacute;n</h2>

<!-- Tabs de estado -->
<div style="display:flex;gap:0.5rem;margin-bottom:1.25rem;flex-wrap:wrap">
    <a href="<?= url('/admin/renovaciones?estado=pendiente') ?>"
       class="btn <?= $estadoActual === 'pendiente' ? 'btn--primary' : 'btn--outline' ?>"
       style="font-size:0.85rem">
        &#9203; Pendientes <span style="background:rgba(255,255,255,0.2);padding:0.1rem 0.4rem;border-radius:10px;margin-left:0.25rem"><?= $conteos['pendiente'] ?></span>
    </a>
    <a href="<?= url('/admin/renovaciones?estado=aprobada') ?>"
       class="btn <?= $estadoActual === 'aprobada' ? 'btn--primary' : 'btn--outline' ?>"
       style="font-size:0.85rem">
        &#9989; Aprobadas <span style="opacity:0.7;margin-left:0.25rem"><?= $conteos['aprobada'] ?></span>
    </a>
    <a href="<?= url('/admin/renovaciones?estado=rechazada') ?>"
       class="btn <?= $estadoActual === 'rechazada' ? 'btn--primary' : 'btn--outline' ?>"
       style="font-size:0.85rem">
        &#10060; Rechazadas <span style="opacity:0.7;margin-left:0.25rem"><?= $conteos['rechazada'] ?></span>
    </a>
</div>

<?php if (empty($renovaciones)): ?>
    <div class="admin-card">
        <div class="admin-card__body" style="text-align:center;padding:2rem">
            <p style="color:var(--color-gray);font-size:1.1rem">
                <?= $estadoActual === 'pendiente' ? '&#127881; No hay solicitudes pendientes de revisi&oacute;n.' : 'No hay registros.' ?>
            </p>
        </div>
    </div>
<?php else: ?>
    <div class="admin-card">
        <div class="admin-card__body" style="padding:0">
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Comercio</th>
                            <th>Comerciante</th>
                            <th>Plan</th>
                            <th>Monto</th>
                            <th>M&eacute;todo</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($renovaciones as $r): ?>
                            <tr>
                                <td><strong><?= e($r['comercio_nombre']) ?></strong></td>
                                <td>
                                    <span style="font-size:0.85rem"><?= e($r['usuario_nombre']) ?></span><br>
                                    <small style="color:var(--color-gray)"><?= e($r['usuario_email']) ?></small>
                                </td>
                                <td>
                                    <span style="font-size:0.85rem"><?= e($r['plan_actual']) ?></span>
                                    <span style="color:var(--color-gray)">&rarr;</span>
                                    <strong style="font-size:0.85rem"><?= e($r['plan_solicitado']) ?></strong>
                                </td>
                                <td>
                                    <?php if ($r['monto']): ?>
                                        <span style="font-size:0.85rem">$<?= number_format((float)$r['monto'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span style="color:var(--color-gray);font-size:0.85rem">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-size:0.85rem"><?= e(ucfirst($r['metodo_pago'] ?? '—')) ?></span>
                                </td>
                                <td>
                                    <span style="font-size:0.85rem"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></span>
                                </td>
                                <td>
                                    <?php if ($r['estado'] === 'pendiente'): ?>
                                        <span style="background:#FEF3C7;color:#92400E;padding:0.2rem 0.5rem;border-radius:10px;font-size:0.8rem">&#9203; Pendiente</span>
                                    <?php elseif ($r['estado'] === 'aprobada'): ?>
                                        <span style="background:#F0FDF4;color:#166534;padding:0.2rem 0.5rem;border-radius:10px;font-size:0.8rem">&#9989; Aprobada</span>
                                    <?php else: ?>
                                        <span style="background:#FEE2E2;color:#991B1B;padding:0.2rem 0.5rem;border-radius:10px;font-size:0.8rem">&#10060; Rechazada</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= url('/admin/renovaciones/ver/' . $r['id']) ?>" class="btn btn--outline btn--sm">
                                        Revisar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
        <?php
        $baseUrl = '/admin/renovaciones';
        $queryParams = ['estado' => $estadoActual];
        include __DIR__ . '/../../partials/pagination.php';
        ?>
    <?php endif; ?>
<?php endif; ?>
