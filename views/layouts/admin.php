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
    <title><?= e($title ?? 'Admin — ' . SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin-layout">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-56D8422F"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager -->

    <?php include BASE_PATH . '/views/partials/sidebar.php'; ?>

    <div class="admin-main">
        <?php include BASE_PATH . '/views/partials/topbar.php'; ?>

        <?php include BASE_PATH . '/views/partials/toast.php'; ?>

        <div class="admin-content">
            <?= $content ?>
        </div>
    </div>

    <!-- Modal de confirmacion de eliminacion -->
    <div class="modal" id="deleteModal">
        <div class="modal__overlay" data-modal-close></div>
        <div class="modal__content">
            <div class="modal__header">
                <h3>Confirmar eliminacion</h3>
                <button class="modal__close" data-modal-close>&times;</button>
            </div>
            <div class="modal__body">
                <p id="deleteModalText">¿Estas seguro de que deseas eliminar este elemento? Esta accion no se puede deshacer.</p>
                <form id="deleteForm" method="POST" style="display:inline">
                    <?= csrf_field() ?>
                    <div class="toolbar" style="margin-top:1rem;margin-bottom:0;justify-content:flex-end">
                        <button type="button" class="btn btn--outline" data-modal-close>Cancelar</button>
                        <button type="submit" class="btn btn--danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= asset('js/admin.js') ?>"></script>
</body>
</html>
