<?php
/**
 * Admin - Mi Perfil / Cambio de contraseña
 * Variables: $admin, $csrf, $flash, $errors (optional)
 */
$errors = $flash['errors'] ?? [];
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <span>Mi Perfil</span>
</div>

<h2>Mi Perfil</h2>

<?php if (!empty($errors)): ?>
    <div class="toast toast--error toast--inline" role="alert">
        <span class="toast__message">
            <?php foreach ($errors as $field => $msg): ?>
                <?= e(is_array($msg) ? implode(', ', $msg) : $msg) ?><br>
            <?php endforeach; ?>
        </span>
    </div>
<?php endif; ?>

<!-- Datos del usuario (solo lectura) -->
<div class="admin-card mb-3">
    <div class="admin-card__header">
        <h3>Datos de la cuenta</h3>
    </div>
    <div class="admin-card__body">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Nombre</label>
                <input type="text"
                       class="form-control"
                       value="<?= e($admin['nombre'] ?? '') ?>"
                       disabled>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="text"
                       class="form-control"
                       value="<?= e($admin['email'] ?? '') ?>"
                       disabled>
            </div>
        </div>
    </div>
</div>

<!-- Formulario de cambio de contraseña -->
<form method="POST" action="<?= url('/admin/perfil/password') ?>">
    <?= csrf_field() ?>

    <div class="admin-card mb-3">
        <div class="admin-card__header">
            <h3>Cambiar contraseña</h3>
        </div>
        <div class="admin-card__body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="current_password">Contraseña actual *</label>
                    <input type="password"
                           id="current_password"
                           name="current_password"
                           class="form-control"
                           required>
                </div>
                <div class="form-group"></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="new_password">Nueva contraseña *</label>
                    <input type="password"
                           id="new_password"
                           name="new_password"
                           class="form-control"
                           required
                           minlength="8"
                           placeholder="Minimo 8 caracteres">
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirmar nueva contraseña *</label>
                    <input type="password"
                           id="confirm_password"
                           name="confirm_password"
                           class="form-control"
                           required
                           minlength="8">
                </div>
            </div>
        </div>
    </div>

    <!-- Botones -->
    <div class="toolbar" style="margin-bottom:0">
        <button type="submit" class="btn btn--primary">Guardar contraseña</button>
        <a href="<?= url('/admin/dashboard') ?>" class="btn btn--outline">Cancelar</a>
    </div>
</form>
