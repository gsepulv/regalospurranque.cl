<?php
/**
 * Registro de comercio — Paso 1: Crear cuenta
 */
$errores = $_SESSION['flash_errors'] ?? [];
$old     = $_SESSION['flash_old'] ?? [];
$error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_errors'], $_SESSION['flash_old'], $_SESSION['flash_error']);
?>

<section class="section">
    <div class="container" style="max-width:520px">

        <div style="text-align:center;margin-bottom:2rem">
            <span style="font-size:3rem">🏪</span>
            <h1 style="font-size:1.75rem;margin:0.5rem 0">Registra tu comercio</h1>
            <p class="text-muted">Publica tu negocio en el directorio digital de Purranque. <strong>Es gratis.</strong></p>
            <p style="font-size:var(--font-size-sm);color:var(--color-gray)">
                Paso <strong>1</strong> de <strong>2</strong> — Crear tu cuenta
            </p>
        </div>

        <?php if (!empty($error)): ?>
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

        <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
            <form method="POST" action="<?= url('/registrar-comercio/cuenta') ?>" id="formCuenta">
                <?= csrf_field() ?>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Tu nombre completo <span style="color:#DC2626">*</span>
                    </label>
                    <input type="text" name="nombre" class="form-control"
                           value="<?= e($old['nombre'] ?? '') ?>"
                           placeholder="Ej: María González" required>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Email <span style="color:#DC2626">*</span>
                    </label>
                    <input type="email" name="email" class="form-control"
                           value="<?= e($old['email'] ?? '') ?>"
                           placeholder="tu@email.com" required>
                    <small style="color:#6B7280;font-size:0.8rem">Será tu usuario para acceder</small>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Teléfono / WhatsApp
                    </label>
                    <input type="text" name="telefono" class="form-control"
                           value="<?= e($old['telefono'] ?? '') ?>"
                           placeholder="+56 9 XXXX XXXX">
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Contraseña <span style="color:#DC2626">*</span>
                    </label>
                    <input type="password" name="password" id="pw1" class="form-control"
                           placeholder="Mínimo 6 caracteres" minlength="6" required>
                </div>

                <div style="margin-bottom:1.5rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Confirmar contraseña <span style="color:#DC2626">*</span>
                    </label>
                    <input type="password" name="password_confirm" id="pw2" class="form-control"
                           placeholder="Repite tu contraseña" minlength="6" required>
                </div>

                <button type="submit" class="btn btn--primary" style="width:100%;padding:0.75rem;font-size:1rem">
                    Continuar → Datos del comercio
                </button>
            </form>
        </div>

        <div style="text-align:center;margin-top:1.5rem">
            <p style="font-size:0.8rem;color:#6B7280">
                🔒 Tu información está segura y no será compartida.<br>
                Tu comercio será revisado antes de ser publicado.
            </p>
        </div>

    </div>
</section>

<script>
document.getElementById('formCuenta').addEventListener('submit', function(e) {
    if (document.getElementById('pw1').value !== document.getElementById('pw2').value) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
    }
});
</script>
