<?php
/**
 * Página de contacto
 * Variables: $title, $description, $breadcrumbs, $flash, $csrf
 */
$pageType = 'contacto';
$old = $flash['old'] ?? [];
$errors = $flash['errors'] ?? [];
?>

<section class="section">
    <div class="container">

        <div class="page-header">
            <h1>Contacto</h1>
            <p class="text-muted">¿Tienes dudas, sugerencias o quieres registrar tu comercio? Escríbenos.</p>
        </div>

        <div class="contacto-layout">

            <!-- Formulario -->
            <div class="contacto-main">
                <div class="admin-card">
                    <div style="padding:var(--spacing-6)">
                        <h2 style="margin:0 0 var(--spacing-4)">Envíanos un mensaje</h2>

                        <?php if (!empty($flash['success'])): ?>
                            <script>if(typeof fbq==='function')fbq('track','Contact');</script>
                            <div class="alert alert--success" style="margin-bottom:var(--spacing-4)">
                                <?= e($flash['success']) ?>
                            </div>
                            <div style="background:#EFF6FF;border:1px solid #BFDBFE;color:#1E40AF;padding:0.75rem 1rem;border-radius:8px;margin-bottom:var(--spacing-4);font-size:0.85rem;line-height:1.5">
                                📧 Si no encuentras nuestro correo en tu bandeja de entrada, revisa tu carpeta de <strong>Spam</strong> o <strong>Correo no deseado</strong>. El mensaje puede tardar unos minutos en llegar.
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($flash['error'])): ?>
                            <div class="alert alert--error" style="margin-bottom:var(--spacing-4)">
                                <?= e($flash['error']) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= url('/contacto/enviar') ?>" novalidate>
                            <input type="hidden" name="_csrf" value="<?= $csrf ?>">

                            <div class="form-group">
                                <label class="form-label" for="contactNombre">Nombre *</label>
                                <input type="text" name="nombre" id="contactNombre" class="form-control <?= !empty($errors['nombre']) ? 'form-control--error' : '' ?>"
                                       value="<?= e($old['nombre'] ?? '') ?>"
                                       placeholder="Tu nombre completo"
                                       required minlength="2" maxlength="100">
                                <?php if (!empty($errors['nombre'])): ?>
                                    <small class="form-error"><?= e($errors['nombre'][0]) ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="contactEmail">Correo electrónico *</label>
                                <input type="email" name="email" id="contactEmail" class="form-control <?= !empty($errors['email']) ? 'form-control--error' : '' ?>"
                                       value="<?= e($old['email'] ?? '') ?>"
                                       placeholder="tu@email.com"
                                       required maxlength="255">
                                <?php if (!empty($errors['email'])): ?>
                                    <small class="form-error"><?= e($errors['email'][0]) ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="contactAsunto">Asunto *</label>
                                <input type="text" name="asunto" id="contactAsunto" class="form-control <?= !empty($errors['asunto']) ? 'form-control--error' : '' ?>"
                                       value="<?= e($old['asunto'] ?? '') ?>"
                                       placeholder="Motivo de tu mensaje"
                                       required minlength="3" maxlength="200">
                                <?php if (!empty($errors['asunto'])): ?>
                                    <small class="form-error"><?= e($errors['asunto'][0]) ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="contactMensaje">Mensaje *</label>
                                <textarea name="mensaje" id="contactMensaje" class="form-control <?= !empty($errors['mensaje']) ? 'form-control--error' : '' ?>"
                                          rows="6" placeholder="Escribe tu mensaje aquí..."
                                          required minlength="10" maxlength="5000"><?= e($old['mensaje'] ?? '') ?></textarea>
                                <?php if (!empty($errors['mensaje'])): ?>
                                    <small class="form-error"><?= e($errors['mensaje'][0]) ?></small>
                                <?php endif; ?>
                            </div>

                            <?= \App\Services\Captcha::widget() ?>
                            <button type="submit" class="btn btn--primary" id="contactSubmit">Enviar mensaje</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info lateral -->
            <aside class="contacto-sidebar">
                <div class="admin-card">
                    <div style="padding:var(--spacing-6)">
                        <h3 style="margin:0 0 var(--spacing-4)"><?= e(SITE_NAME) ?></h3>

                        <div class="contact-item">
                            <span class="contact-item__icon">&#128205;</span>
                            <span>Purranque, Regi&oacute;n de Los Lagos, Chile</span>
                        </div>

                        <div class="contact-item">
                            <span class="contact-item__icon">&#9993;</span>
                            <a href="mailto:contacto@purranque.info">contacto@purranque.info</a>
                        </div>

                        <?php // Profiles: sidebar position ?>
                        <?php $position = 'sidebar'; include BASE_PATH . '/views/partials/social-profiles.php'; ?>
                    </div>
                </div>

                <!-- Mapa -->
                <div class="admin-card">
                    <div style="padding:0;overflow:hidden;border-radius:var(--radius-md)">
                        <div id="contactMap" class="contacto-map"></div>
                    </div>
                    <div style="padding:var(--spacing-3) var(--spacing-4)">
                        <p class="text-sm text-muted" style="margin:0">
                            &#128205; Plaza de Purranque, centro de la ciudad
                        </p>
                    </div>
                </div>
            </aside>
        </div>

    </div>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function() {
    var map = L.map('contactMap', {
        scrollWheelZoom: false,
        dragging: !L.Browser.mobile
    }).setView([-40.91305, -73.15913], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    var giftIcon = L.divIcon({
        className: 'gift-marker',
        html: '<div class="gift-pin"><span class="gift-pin__emoji">🎁</span><div class="gift-pin__arrow"></div></div>',
        iconSize: [40, 48],
        iconAnchor: [20, 48],
        popupAnchor: [0, -44]
    });

    L.marker([-40.91305, -73.15913], {icon: giftIcon})
        .addTo(map)
        .bindPopup('<strong><?= e(SITE_NAME) ?></strong><br>Purranque, Chile')
        .openPopup();
})();
</script>
