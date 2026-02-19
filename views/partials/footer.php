<footer class="footer">
    <div class="container">
        <div class="footer__grid">
            <div>
                <h4 class="footer__title">&#x2139;&#xFE0F; Acerca de</h4>
                <p class="footer__desc"><?= e(SITE_DESCRIPTION) ?></p>
                <p class="footer__credit">Un proyecto de <a href="https://purranque.info" target="_blank" rel="noopener">PurranQUE.INFO</a></p>
            </div>
            <div>
                <h4 class="footer__title">&#129517; Navegación</h4>
                <div class="footer__links">
                    <a href="<?= url('/') ?>">Inicio</a>
                    <a href="<?= url('/categorias') ?>">Categorías</a>
                    <a href="<?= url('/comercios') ?>">Comercios</a>
                    <a href="<?= url('/noticias') ?>">Noticias</a>
                    <a href="<?= url('/celebraciones') ?>">Celebraciones</a>
                    <a href="<?= url('/mapa') ?>">Mapa</a>
                    <a href="<?= url('/contacto') ?>">Contacto</a>
                </div>
            </div>
            <div>
                <h4 class="footer__title">&#128737; Legal</h4>
                <div class="footer__links">
                    <a href="<?= url('/terminos') ?>">Términos y Condiciones</a>
                    <a href="<?= url('/privacidad') ?>">Política de Privacidad</a>
                    <a href="<?= url('/cookies') ?>">Política de Cookies</a>
                </div>
            </div>
            <div>
                <h4 class="footer__title">&#128222; Contacto</h4>
                <div class="footer__links">
                    <span>&#128205; Purranque, Región de Los Lagos, Chile</span>
                    <a href="mailto:contacto@purranque.info">&#9993; contacto@purranque.info</a>
                </div>
                <?php $position = 'footer'; include BASE_PATH . '/views/partials/social-profiles.php'; ?>
            </div>
        </div>
        <div class="footer__bottom">
            <p>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. Todos los derechos reservados.</p>
            <p class="footer__beta">
                <span class="badge-beta">BETA</span>
                Plataforma en etapa de validación territorial &mdash; <a href="<?= url('/contacto') ?>">Enviar feedback</a>
            </p>
            <p class="footer__credit-bottom">Un proyecto de <a href="https://purranque.info" target="_blank" rel="noopener">PurranQUE.INFO</a></p>
        </div>
    </div>
</footer>
