<?php
/**
 * Admin - Formulario de usuario (crear / editar)
 * Variables: optionally $usuario
 */
$editing = isset($usuario);
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/usuarios') ?>">Usuarios</a> &rsaquo;
    <span><?= $editing ? 'Editar usuario' : 'Nuevo usuario' ?></span>
</div>

<h2><?= $editing ? 'Editar usuario' : 'Nuevo usuario' ?></h2>

<?php if (!empty($errors)): ?>
    <div class="toast toast--error toast--inline" role="alert">
        <span class="toast__message">
            <?php foreach ($errors as $field => $msg): ?>
                <?= e(is_array($msg) ? implode(', ', $msg) : $msg) ?><br>
            <?php endforeach; ?>
        </span>
    </div>
<?php endif; ?>

<form method="POST"
      action="<?= $editing ? url('/admin/usuarios/update/' . $usuario['id']) : url('/admin/usuarios/store') ?>">
    <?= csrf_field() ?>

    <div class="admin-card mb-3">
        <div class="admin-card__header">
            <h3>Datos del usuario</h3>
        </div>
        <div class="admin-card__body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre *</label>
                    <input type="text"
                           id="nombre"
                           name="nombre"
                           class="form-control"
                           value="<?= e(old('nombre', $usuario['nombre'] ?? '')) ?>"
                           required
                           minlength="3"
                           maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email *</label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control"
                           value="<?= e(old('email', $usuario['email'] ?? '')) ?>"
                           required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="telefono">Teléfono</label>
                    <input type="text"
                           id="telefono"
                           name="telefono"
                           class="form-control"
                           value="<?= e(old('telefono', $usuario['telefono'] ?? '')) ?>"
                           placeholder="+56 9 1234 5678">
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">
                        <?= $editing ? 'Nueva contrasena' : 'Contrasena *' ?>
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control"
                           <?= !$editing ? 'required' : '' ?>
                           minlength="8"
                           placeholder="<?= $editing ? 'Dejar vacio para no cambiar' : 'Minimo 8 caracteres' ?>">
                    <?php if ($editing): ?>
                        <small style="color:var(--color-gray)">Dejar en blanco para mantener la contrasena actual.</small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="rol">Rol *</label>
                    <select id="rol" name="rol" class="form-control" required>
                        <option value="">Seleccionar rol</option>
                        <?php
                        $roles = ['admin' => 'Admin', 'editor' => 'Editor', 'comerciante' => 'Comerciante'];
                        foreach ($roles as $val => $label):
                        ?>
                            <option value="<?= $val ?>"
                                    <?= old('rol', $usuario['rol'] ?? '') === $val ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex;align-items:center;gap:0.5rem;margin-top:1.75rem">
                        <input type="checkbox"
                               name="activo"
                               value="1"
                               <?= old('activo', $usuario['activo'] ?? 1) ? 'checked' : '' ?>>
                        Activo
                    </label>
                    <small style="color:var(--color-gray)">El usuario podra iniciar sesion en el panel.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones -->
    <div class="toolbar" style="margin-bottom:0">
        <button type="submit" class="btn btn--primary"><?= $editing ? 'Guardar cambios' : 'Crear usuario' ?></button>
        <a href="<?= url('/admin/usuarios') ?>" class="btn btn--outline">Cancelar</a>
    </div>
</form>
