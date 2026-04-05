<!DOCTYPE html>
<html lang="es-CL">
<head>
    <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
    <link rel="preconnect" href="https://connect.facebook.net" crossorigin>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-56D8422F');</script>
    <!-- End Google Tag Manager -->
    <!-- Meta Pixel (Facebook) -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1223892215809376');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=1223892215809376&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include BASE_PATH . '/views/partials/seo-head.php'; ?>
    <!-- PWA -->
    <link rel="manifest" href="<?= url('manifest.json') ?>">
    <meta name="theme-color" content="<?= \App\Services\Theme::getColor('primary', '#ea580c') ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?= e(SITE_NAME) ?>">
    <link rel="apple-touch-icon" href="<?= asset('img/icons/icon-192x192.png') ?>">
    <!-- Theme Colors -->
    <style id="theme-colors">
    :root { <?= \App\Services\Theme::generateCssVariables() ?> }
    </style>
    <?php $cssFile = APP_ENV === 'production' ? 'css/rp2.min.css' : 'css/rp2.css'; ?>
    <link rel="preload" href="<?= asset($cssFile . '?v=202603101200') ?>" as="style">
    <link rel="stylesheet" href="<?= asset($cssFile . '?v=202603101200') ?>">
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

    <?php include BASE_PATH . '/views/partials/session-bar.php'; ?>

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

    <!-- Widget flotante captación comerciantes -->
    <div id="widgetComercio" style="display:none;position:fixed;bottom:20px;left:20px;z-index:999;font-family:inherit">
        <div style="background:#4caf50;color:#fff;border-radius:28px;padding:0.65rem 1.25rem;box-shadow:0 4px 16px rgba(0,0,0,0.2);display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.9rem;font-weight:600;text-decoration:none;transition:transform 0.2s" onclick="window.location.href='<?= url('/registrar-comercio') ?>'" role="button">
            <span style="font-size:1.2rem">&#127978;</span>
            <span class="widget-comercio-text">¿Tienes un negocio? Publícalo gratis</span>
            <button onclick="event.stopPropagation();document.getElementById('widgetComercio').style.display='none';sessionStorage.setItem('widgetCerrado','1')" style="background:none;border:none;color:#fff;font-size:1.1rem;cursor:pointer;padding:0 0 0 0.25rem;line-height:1;opacity:0.8" aria-label="Cerrar">&times;</button>
        </div>
    </div>
    <style>
    @media(max-width:600px){.widget-comercio-text{display:none}}
    </style>
    <script>
    (function(){
        if(sessionStorage.getItem('widgetCerrado'))return;
        setTimeout(function(){
            var w=document.getElementById('widgetComercio');
            if(w){w.style.display='block';w.style.animation='fadeIn 0.4s ease';}
        },3000);
    })();
    </script>
    <style>@keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}</style>

    <button class="back-to-top" id="backToTop" aria-label="Volver arriba">&uarr;</button>

    <?php $jsFile = APP_ENV === 'production' ? 'js/app.min.js' : 'js/app.js'; ?>
    <script src="<?= asset($jsFile) ?>" defer></script>
    <div class="beta-floating">Beta</div>

    <?php include BASE_PATH . '/views/partials/cookie-banner.php'; ?>
</body>
</html>
