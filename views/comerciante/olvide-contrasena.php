<?php
$error   = $_SESSION['flash_error'] ?? '';
$success = $_SESSION['flash_success'] ?? '';
$old     = $_SESSION['flash_old'] ?? [];
unset($_SESSION['flash_error'], $_SESSION['flash_success'], $_SESSION['flash_old']);
?>

<section class="section">
    <div class="container" style="max-width:440px">

        <div style="text-align:center;margin-bottom:2rem">
            <span style="font-size:3rem">🔑</span>
            <h1 style="font-size:1.75rem;margin:0.5rem 0">Recuperar contraseña</h1>
            <p class="text-muted">Ingresa tu email y te enviaremos instrucciones para restablecer tu contraseña.</p>
        </div>

        <?php if ($error): ?>
            <div style="background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
            <form method="POST" action="<?= url('/mi-comercio/olvide-contrasena') ?>">
                <?= csrf_field() ?>

                <div style="margin-bottom:1.5rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= e($old['email'] ?? '') ?>"
                           placeholder="tu@email.com" required autofocus>
                </div>

                <?= \App\Services\Captcha::widget() ?>

                <button type="submit" class="btn btn--primary" style="width:100%;padding:0.75rem;font-size:1rem">
                    Enviar instrucciones
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
