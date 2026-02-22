<?php
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>

<section class="section">
    <div class="container" style="max-width:440px">

        <div style="text-align:center;margin-bottom:2rem">
            <span style="font-size:3rem">🔒</span>
            <h1 style="font-size:1.75rem;margin:0.5rem 0">Nueva contraseña</h1>
            <p class="text-muted">Ingresa tu nueva contraseña para acceder a tu comercio.</p>
        </div>

        <?php if ($error): ?>
            <div style="background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
            <form method="POST" action="<?= url('/mi-comercio/reset/' . e($token)) ?>" id="resetForm">
                <?= csrf_field() ?>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Nueva contraseña</label>
                    <input type="password" name="password" class="form-control" id="password"
                           placeholder="Mínimo 8 caracteres" required minlength="8" autofocus>
                </div>

                <div style="margin-bottom:1.5rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Confirmar contraseña</label>
                    <input type="password" name="password_confirm" class="form-control" id="password_confirm"
                           placeholder="Repite tu contraseña" required minlength="8">
                    <small id="matchMsg" style="display:none;color:#991B1B;font-size:0.8rem;margin-top:0.25rem">Las contraseñas no coinciden.</small>
                </div>

                <button type="submit" class="btn btn--primary" style="width:100%;padding:0.75rem;font-size:1rem" id="submitBtn">
                    Cambiar contraseña
                </button>
            </form>
        </div>

        <div style="text-align:center;margin-top:1.25rem">
            <p style="font-size:0.85rem;color:#6B7280">
                <a href="<?= url('/mi-comercio/login') ?>" style="color:#3B82F6;font-weight:600">Volver al login</a>
            </p>
        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var pass = document.getElementById('password');
    var confirm = document.getElementById('password_confirm');
    var msg = document.getElementById('matchMsg');
    var btn = document.getElementById('submitBtn');

    function check() {
        if (confirm.value.length === 0) { msg.style.display = 'none'; btn.disabled = false; return; }
        if (pass.value !== confirm.value) {
            msg.style.display = 'block';
            btn.disabled = true;
        } else {
            msg.style.display = 'none';
            btn.disabled = false;
        }
    }
    pass.addEventListener('input', check);
    confirm.addEventListener('input', check);
});
</script>
