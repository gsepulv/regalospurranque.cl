<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a>
    <span>/</span>
    <span>Categorías</span>
</div>

<div class="toolbar">
    <h2 style="margin:0;flex:1">Categorías</h2>
    <a href="<?= url('/admin/categorias/crear') ?>" class="btn btn--primary btn--sm">+ Nueva categoria</a>
</div>

<?php if (!empty($categorias)): ?>
    <div class="admin-card">
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Icono</th>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Comercios vinculados</th>
                        <th>Orden</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $cat): ?>
                        <tr>
                            <td style="font-size:1.5rem;text-align:center"><?= e($cat['icono'] ?? '') ?></td>
                            <td><strong><?= e($cat['nombre']) ?></strong></td>
                            <td><code><?= e($cat['slug']) ?></code></td>
                            <td>
                                <?php if ($cat['comercios_count'] > 0): ?>
                                    <span class="badge badge--primary"><?= (int)$cat['comercios_count'] ?></span>
                                <?php else: ?>
                                    <span class="badge">0</span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$cat['orden'] ?></td>
                            <td>
                                <?php if ($cat['activo']): ?>
                                    <span class="status status--active">Activo</span>
                                <?php else: ?>
                                    <span class="status status--inactive">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <a href="<?= url('/admin/categorias/editar/' . $cat['id']) ?>" class="btn btn--outline btn--sm">Editar</a>
                                    <?php if ($cat['comercios_count'] == 0): ?>
                                        <button type="button"
                                                class="btn btn--danger btn--sm"
                                                data-delete-url="<?= url('/admin/categorias/eliminar/' . $cat['id']) ?>"
                                                data-delete-name="<?= e($cat['nombre']) ?>"
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
            <div class="empty-state__icon">&#128194;</div>
            <div class="empty-state__title">Sin categorias</div>
            <div class="empty-state__text">Aun no se han creado categorias. Crea la primera para organizar los comercios.</div>
            <a href="<?= url('/admin/categorias/crear') ?>" class="btn btn--primary">+ Nueva categoria</a>
        </div>
    </div>
<?php endif; ?>
