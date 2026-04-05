<?php
/**
 * Share page for individual product - serves OG meta tags then redirects
 * Variables: $producto, $comercio
 */
$ogTitle = e($producto['nombre']) . ' — ' . e($comercio['nombre']);
$ogDesc = $producto['descripcion'] ? e($producto['descripcion']) : e($producto['nombre']) . ' disponible en ' . e($comercio['nombre']);
$ogImage = SITE_URL . '/assets/img/productos/' . $producto['comercio_id'] . '/' . ($producto['imagen'] ?: ('../logos/' . $comercio['logo']));
$ogUrl = SITE_URL . '/producto/' . $producto['id'];
$precioFmt = $producto['precio'] ? number_format($producto['precio'], 0, ',', '.') : '';
$redirectUrl = url('/comercio/' . $comercio['slug']) . '#producto-' . $producto['id'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta property="og:title" content="<?= $ogTitle ?>">
    <meta property="og:description" content="<?= $ogDesc ?>">
    <meta property="og:image" content="<?= $ogImage ?>">
    <meta property="og:url" content="<?= $ogUrl ?>">
    <meta property="og:type" content="product">
    <meta property="og:site_name" content="Regalos Purranque">
    <?php if ($producto['precio']): ?>
    <meta property="product:price:amount" content="<?= $producto['precio'] ?>">
    <meta property="product:price:currency" content="CLP">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $ogTitle ?>">
    <?php if ($producto['imagen']): ?>
    <meta name="twitter:image" content="<?= $ogImage ?>">
    <?php endif; ?>
    <title><?= $ogTitle ?> | Regalos Purranque</title>
    <script>window.location.href='<?= $redirectUrl ?>';</script>
</head>
<body style="font-family:system-ui;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0">
    <p>Redirigiendo a <?= e($producto['nombre']) ?>...</p>
</body>
</html>
