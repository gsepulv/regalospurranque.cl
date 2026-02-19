<div class="admin-page">
    <div class="admin-page__header">
        <h1>Sitios</h1>
        <div class="toolbar">
            <a href="<?= url('/admin/sitios/crear') ?>" class="btn btn--primary">
                + Nuevo sitio
            </a>
        </div>
    </div>

    <?php if (empty($sitios)): ?>
        <div class="empty-state">
            <p>No hay sitios registrados.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Dominio</th>
                        <th>Ciudad</th>
                        <th>Comercios</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sitios as $sitio): ?>
                        <tr>
                            <td><?= (int) $sitio['id'] ?></td>
                            <td>
                                <strong><?= e($sitio['nombre']) ?></strong>
                                <br><small class="text-muted"><?= e($sitio['slug']) ?></small>
                            </td>
                            <td>
                                <?php if (!empty($sitio['dominio'])): ?>
                                    <a href="https://<?= e($sitio['dominio']) ?>" target="_blank" class="text-link">
                                        <?= e($sitio['dominio']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Sin dominio</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($sitio['ciudad']) ?></td>
                            <td>
                                <span class="badge"><?= number_format($sitio['total_comercios'] ?? 0) ?></span>
                            </td>
                            <td>
                                <?php if ((int)$sitio['id'] === 1): ?>
                                    <span class="badge badge--success">Principal</span>
                                <?php else: ?>
                                    <button class="toggle-btn <?= $sitio['activo'] ? 'toggle-btn--active' : '' ?>"
                                            data-toggle-url="<?= url('/admin/sitios/toggle/' . $sitio['id']) ?>"
                                            data-csrf="<?= csrf_token() ?>">
                                        <?= $sitio['activo'] ? 'Activo' : 'Inactivo' ?>
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= url('/admin/sitios/editar/' . $sitio['id']) ?>"
                                       class="btn btn--sm btn--outline">Editar</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Info -->
    <div class="card" style="margin-top:2rem;">
        <div class="card__body">
            <p class="text-muted" style="font-size:0.85rem;">
                <strong>Multi-sitio:</strong> Cada sitio es un directorio comercial independiente con sus propios comercios,
                categorías y configuración. Los usuarios superadmin pueden gestionar todos los sitios desde un mismo panel.
            </p>
        </div>
    </div>
</div>
