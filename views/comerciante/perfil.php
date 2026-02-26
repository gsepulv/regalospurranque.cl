<?php
/**
 * Perfil del comerciante — Datos básicos + Cambiar contraseña
 * Variables: $usuario (row de admin_usuarios)
 */
$success = $_SESSION['flash_success'] ?? '';
$error   = $_SESSION['flash_error'] ?? '';
$errores = $_SESSION['flash_errors'] ?? [];
$old     = $_SESSION['flash_old'] ?? [];
unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['flash_errors'], $_SESSION['flash_old']);
?>

<?php include BASE_PATH . '/views/partials/comerciante-topbar.php'; ?>

<section class="section">
    <div class="container" style="max-width:600px">

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
            <h1 style="font-size:1.5rem;margin:0">Mi perfil</h1>
            <a href="<?= url('/mi-comercio') ?>" style="color:#6B7280;font-size:0.85rem;text-decoration:none">
                &larr; Volver al panel
            </a>
        </div>

        <!-- Mensajes flash -->
        <?php if ($success): ?>
            <div style="background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <?= e($success) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div style="background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <?= e($error) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errores)): ?>
            <div style="background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <?php foreach ($errores as $err): ?>
                    <p style="margin:0.25rem 0"><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ═══ DATOS BÁSICOS ═══ -->
        <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.25rem">
            <h3 style="margin:0 0 1rem;font-size:1.1rem">Datos de la cuenta</h3>

            <form method="POST" action="<?= url('/mi-comercio/perfil/datos') ?>">
                <?= csrf_field() ?>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Nombre <span style="color:#DC2626">*</span>
                    </label>
                    <input type="text" name="nombre" class="form-control"
                           value="<?= e($old['nombre'] ?? $usuario['nombre']) ?>"
                           minlength="3" maxlength="100" required>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Email <span style="color:#DC2626">*</span>
                    </label>
                    <input type="email" name="email" class="form-control"
                           value="<?= e($old['email'] ?? $usuario['email']) ?>"
                           maxlength="100" required>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Teléfono <span style="color:#DC2626">*</span>
                    </label>
                    <input type="text" name="telefono" class="form-control"
                           value="<?= e($old['telefono'] ?? $usuario['telefono']) ?>"
                           minlength="9" maxlength="15" required>
                </div>

                <button type="submit" class="btn btn--primary" style="padding:0.6rem 1.5rem">
                    Guardar datos
                </button>
            </form>
        </div>

        <!-- ═══ CAMBIAR CONTRASEÑA ═══ -->
        <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.25rem">
            <h3 style="margin:0 0 1rem;font-size:1.1rem">Cambiar contraseña</h3>

            <form method="POST" action="<?= url('/mi-comercio/perfil/password') ?>">
                <?= csrf_field() ?>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Contraseña actual <span style="color:#DC2626">*</span>
                    </label>
                    <input type="password" name="password_actual" class="form-control" required>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Nueva contraseña <span style="color:#DC2626">*</span>
                    </label>
                    <input type="password" name="password_nueva" class="form-control"
                           minlength="8" required>
                    <small style="color:#6B7280">Mínimo 8 caracteres.</small>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Confirmar nueva contraseña <span style="color:#DC2626">*</span>
                    </label>
                    <input type="password" name="password_confirm" class="form-control"
                           minlength="8" required>
                </div>

                <button type="submit" class="btn btn--outline" style="padding:0.6rem 1.5rem">
                    Cambiar contraseña
                </button>
            </form>
        </div>

    </div>
</section>
