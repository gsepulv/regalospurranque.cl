<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Iniciar sesión — ' . SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="login-page">

    <?= $content ?>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
