<?php
/**
 * Admin - Listado de usuarios
 * Variables: $usuarios
 * Disponible globalmente: $admin (usuario logueado)
 */
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <span>Usuarios</span>
</div>

<h2>Usuarios</h2>

<!-- Toolbar -->
<div class="toolbar">
    <a href="<?= url('/admin/usuarios/crear') ?>" class="btn btn--primary btn--sm">+ Nuevo usuario</a>
</div>

<!-- Tabla de usuarios -->
<div class="admin-card">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Ultimo login</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;padding:2rem;color:var(--color-gray)">
                            No se encontraron usuarios.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <?php
                        $isSelf = ($usuario['id'] === $admin['id']);

                        // Badge de rol
                        $rolBadges = [
                            'admin'       => 'badge--danger',
                            'editor'      => 'badge--primary',
                            'comerciante' => 'badge--warning',
                        ];
                        $rolBadge = $rolBadges[$usuario['rol']] ?? '';
                        ?>
                        <tr>
                            <td>
                                <strong><?= e($usuario['nombre']) ?></strong>
                                <?php if ($isSelf): ?>
                                    <small style="color:var(--color-primary)">(tu)</small>
                                <?php endif; ?>
                            </td>
                            <td><?= e($usuario['email']) ?></td>
                            <td>
                                <span class="badge <?= $rolBadge ?>"><?= e($usuario['rol']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($usuario['last_login'])): ?>
                                    <?= fecha_es($usuario['last_login'], 'd/m/Y H:i') ?>
                                <?php else: ?>
                                    <span style="color:var(--color-gray)">Nunca</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox"
                                           <?= $usuario['activo'] ? 'checked' : '' ?>
                                           <?= $isSelf ? 'disabled' : '' ?>
                                           data-toggle-url="<?= url('/admin/usuarios/toggle/' . $usuario['id']) ?>">
                                    <span class="toggle-switch__slider"></span>
                                </label>
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <a href="<?= url('/admin/usuarios/editar/' . $usuario['id']) ?>"
                                       class="btn btn--outline btn--sm"
                                       title="Editar">Editar</a>

                                    <?php if (!$isSelf): ?>
                                        <button type="button"
                                                class="btn btn--danger btn--sm"
                                                data-delete-url="<?= url('/admin/usuarios/eliminar/' . $usuario['id']) ?>"
                                                data-delete-name="<?= e($usuario['nombre']) ?>"
                                                title="Eliminar">Eliminar</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
