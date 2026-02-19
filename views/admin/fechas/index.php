<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a>
    <span>/</span>
    <span>Fechas Especiales</span>
</div>

<div class="toolbar">
    <h2 style="margin:0;flex:1">Fechas Especiales</h2>
    <a href="<?= url('/admin/fechas/crear') ?>" class="btn btn--primary btn--sm">+ Nueva fecha</a>
</div>

<!-- Tabs de filtro por tipo -->
<div class="toolbar" style="margin-bottom:1rem">
    <a href="<?= url('/admin/fechas') ?>"
       class="btn btn--sm <?= empty($tipoActivo) ? 'btn--primary' : 'btn--outline' ?>">
        Todas
    </a>
    <a href="<?= url('/admin/fechas?tipo=personal') ?>"
       class="btn btn--sm <?= ($tipoActivo ?? '') === 'personal' ? 'btn--primary' : 'btn--outline' ?>">
        Personal
    </a>
    <a href="<?= url('/admin/fechas?tipo=calendario') ?>"
       class="btn btn--sm <?= ($tipoActivo ?? '') === 'calendario' ? 'btn--primary' : 'btn--outline' ?>">
        Calendario
    </a>
    <a href="<?= url('/admin/fechas?tipo=comercial') ?>"
       class="btn btn--sm <?= ($tipoActivo ?? '') === 'comercial' ? 'btn--primary' : 'btn--outline' ?>">
        Comercial
    </a>
</div>

<?php if (!empty($fechas)): ?>
    <div class="admin-card">
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Icono</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Fecha inicio</th>
                        <th>Fecha fin</th>
                        <th>Recurrente</th>
                        <th>Comercios</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fechas as $fecha): ?>
                        <tr>
                            <td style="font-size:1.5rem;text-align:center"><?= e($fecha['icono'] ?? '') ?></td>
                            <td><strong><?= e($fecha['nombre']) ?></strong></td>
                            <td>
                                <?php
                                $tipoBadge = match($fecha['tipo'] ?? '') {
                                    'personal'   => 'badge--success',
                                    'calendario' => 'badge--primary',
                                    'comercial'  => 'badge--warning',
                                    default      => '',
                                };
                                ?>
                                <span class="badge <?= $tipoBadge ?>"><?= e(ucfirst($fecha['tipo'] ?? '')) ?></span>
                            </td>
                            <td><?= !empty($fecha['fecha_inicio']) ? fecha_es($fecha['fecha_inicio'], 'd/m/Y') : '—' ?></td>
                            <td><?= !empty($fecha['fecha_fin']) ? fecha_es($fecha['fecha_fin'], 'd/m/Y') : '—' ?></td>
                            <td>
                                <?php if ($fecha['recurrente'] ?? false): ?>
                                    <span class="badge badge--success">Si</span>
                                <?php else: ?>
                                    <span class="badge">No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php $comerciosCount = (int)($fecha['comercios_count'] ?? 0); ?>
                                <?php if ($comerciosCount > 0): ?>
                                    <span class="badge badge--primary"><?= $comerciosCount ?></span>
                                <?php else: ?>
                                    <span class="badge">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($fecha['activo'] ?? false): ?>
                                    <span class="status status--active">Activo</span>
                                <?php else: ?>
                                    <span class="status status--inactive">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <a href="<?= url('/admin/fechas/editar/' . $fecha['id']) ?>" class="btn btn--outline btn--sm">Editar</a>
                                    <?php if ($comerciosCount == 0): ?>
                                        <button type="button"
                                                class="btn btn--danger btn--sm"
                                                data-delete-url="<?= url('/admin/fechas/eliminar/' . $fecha['id']) ?>"
                                                data-delete-name="<?= e($fecha['nombre']) ?>"
                                                data-modal-open="deleteModal">
                                            Eliminar
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn--danger btn--sm" disabled title="No se puede eliminar: tiene comercios vinculados">
                                            Eliminar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="admin-card">
        <div class="empty-state">
            <div class="empty-state__icon">&#128197;</div>
            <div class="empty-state__title">Sin fechas especiales</div>
            <div class="empty-state__text">Aun no se han creado fechas especiales. Crea la primera para asociar comercios con eventos y celebraciones.</div>
            <a href="<?= url('/admin/fechas/crear') ?>" class="btn btn--primary">+ Nueva fecha</a>
        </div>
    </div>
<?php endif; ?>
