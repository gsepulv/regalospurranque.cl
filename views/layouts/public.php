<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-56D8422F');</script>
    <!-- End Google Tag Manager -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include BASE_PATH . '/views/partials/seo-head.php'; ?>
    <!-- PWA -->
    <link rel="manifest" href="<?= url('manifest.json') ?>">
    <meta name="theme-color" content="<?= \App\Services\Theme::getColor('primary', '#ea580c') ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?= e(SITE_NAME) ?>">
    <link rel="apple-touch-icon" href="<?= asset('img/icons/icon-192x192.png') ?>">
    <!-- Theme Colors -->
    <style id="theme-colors">
    :root { <?= \App\Services\Theme::generateCssVariables() ?> }
    </style>
    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.tiny.cloud">
    <link rel="preload" href="<?= asset('css/main.css?v=202602170318') ?>" as="style">
    <link rel="stylesheet" href="<?= asset('css/main.css?v=202602170318') ?>">
    <?php if (!empty($extraCss)): ?>
    <link rel="stylesheet" href="<?= asset($extraCss) ?>">
    <?php endif; ?>
    <!-- RSS -->
    <link rel="alternate" type="application/rss+xml" title="<?= e(SITE_NAME) ?> — Noticias" href="<?= url('/feed/rss.xml') ?>">
    <?= \App\Services\Captcha::script() ?>
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-56D8422F"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager -->

    <a href="#main-content" class="skip-to-content">Ir al contenido principal</a>

    <?php include BASE_PATH . '/views/partials/nav.php'; ?>

    <?php // Profiles: below_header position ?>
    <?php $position = 'below_header'; include BASE_PATH . '/views/partials/social-profiles.php'; ?>

    <?php include BASE_PATH . '/views/partials/toast.php'; ?>

    <main id="main-content" role="main">
        <?php if (!empty($breadcrumbs)): ?>
            <?php include BASE_PATH . '/views/partials/breadcrumbs.php'; ?>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <?php // Profiles: before_footer position ?>
    <?php $position = 'before_footer'; include BASE_PATH . '/views/partials/social-profiles.php'; ?>

    <?php include BASE_PATH . '/views/partials/cta-comercio.php'; ?>

    <?php include BASE_PATH . '/views/partials/footer.php'; ?>

    <?php include BASE_PATH . '/views/partials/whatsapp-float.php'; ?>

    <?php include BASE_PATH . '/views/partials/share-float.php'; ?>

    <?php // Profiles: floating position ?>
    <?php $position = 'floating'; include BASE_PATH . '/views/partials/social-profiles.php'; ?>

    <?php // Share: floating_bar position ?>
    <?php
    $sharePosition = 'floating_bar';
    $sharePageType = $pageType ?? '';
    include BASE_PATH . '/views/partials/share-buttons.php';
    ?>

    <button class="back-to-top" id="backToTop" aria-label="Volver arriba">&uarr;</button>

    <script src="<?= asset('js/app.js') ?>" defer></script>
    <div class="beta-floating">Beta</div>

    <?php include BASE_PATH . '/views/partials/cookie-banner.php'; ?>
</body>
</html>
