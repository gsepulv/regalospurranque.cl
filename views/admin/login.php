<div class="login-container">
    <div class="login-card">
        <div class="login-card__logo">
            <span class="login-card__logo-text"><?= e(SITE_NAME) ?></span>
            <span class="login-card__logo-sub">Panel de Administración</span>
        </div>

        <?php if (!empty($flash['error'])): ?>
            <div class="toast toast--error toast--inline" role="alert">
                <span class="toast__message"><?= e($flash['error']) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($flash['success'])): ?>
            <div class="toast toast--success toast--inline" role="alert">
                <span class="toast__message"><?= e($flash['success']) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= url('/admin/login') ?>" method="POST" class="login-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control"
                       value="<?= old('email') ?>"
                       placeholder="tu@email.com"
                       required
                       autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control"
                       placeholder="Tu contraseña"
                       required>
            </div>

            <?= \App\Services\Captcha::widget() ?>

            <button type="submit" class="btn btn--primary btn--block">
                Iniciar Sesión
            </button>
        </form>

        <div class="login-card__footer">
            <a href="<?= url('/') ?>">Volver al sitio</a>
        </div>
    </div>
</div>
