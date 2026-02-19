<?php
/**
 * SEO Head - Meta tags completos
 * Protocolos: Basic, Open Graph, Twitter Cards, Apple/iOS, Schema.org JSON-LD, RSS, Favicons, Performance
 */

// Obtener defaults de OG desde config admin
$ogDefaults = \App\Services\RedesSociales::getOgDefaults();
$sameAs     = \App\Services\RedesSociales::getSameAsArray();
$profileTw  = \App\Services\RedesSociales::get('profile_twitter', '');
$twitterHandle = '';
if ($profileTw && preg_match('#(?:twitter\.com|x\.com)/([^/?]+)#', $profileTw, $m)) {
    $twitterHandle = '@' . $m[1];
}

// Variables SEO con fallbacks
$seoTitle       = $title ?? $ogDefaults['title'] ?: SITE_NAME;
$seoDescription = $description ?? $ogDefaults['description'] ?: SITE_DESCRIPTION;
$seoKeywords    = $keywords ?? 'comercios purranque, directorio comercial, tiendas purranque, ofertas purranque';
$seoUrl         = url($_SERVER['REQUEST_URI'] ?? '/');
$seoType        = $og_type ?? 'website';
$seoNoindex     = $noindex ?? false;

// Imagen OG con fallbacks — siempre desde el servidor propio
if (!empty($og_image)) {
    $seoImage = $og_image;
} elseif (!empty($ogDefaults['image'])) {
    $seoImage = asset('img/og/' . $ogDefaults['image']);
} else {
    $seoImage = asset('img/og/default.jpg');
}

// Detectar tipo MIME de la imagen OG
$seoImageType = 'image/jpeg';
if (str_ends_with(strtolower($seoImage), '.png')) {
    $seoImageType = 'image/png';
} elseif (str_ends_with(strtolower($seoImage), '.webp')) {
    $seoImageType = 'image/webp';
}

// Datos extra opcionales para artículos
$articleAuthor     = $article_author ?? '';
$articlePublished  = $article_published ?? '';
$articleModified   = $article_modified ?? '';
?>

<!-- Basic Meta -->
<title><?= e($seoTitle) ?></title>
<meta name="description" content="<?= e($seoDescription) ?>">
<?php if (!empty($seoKeywords)): ?>
<meta name="keywords" content="<?= e($seoKeywords) ?>">
<?php endif; ?>
<?php if ($seoNoindex): ?>
<meta name="robots" content="noindex, nofollow">
<?php else: ?>
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<?php endif; ?>
<meta name="author" content="<?= e($articleAuthor ?: SITE_NAME) ?>">
<meta name="generator" content="<?= e(SITE_NAME) ?> v<?= APP_VERSION ?>">
<link rel="canonical" href="<?= e($seoUrl) ?>">

<!-- Hreflang -->
<link rel="alternate" hreflang="es-CL" href="<?= e($seoUrl) ?>">
<link rel="alternate" hreflang="x-default" href="<?= e($seoUrl) ?>">

<!-- Open Graph -->
<meta property="og:title" content="<?= e($seoTitle) ?>">
<meta property="og:description" content="<?= e($seoDescription) ?>">
<meta property="og:image" content="<?= e($seoImage) ?>">
<meta property="og:image:secure_url" content="<?= e($seoImage) ?>">
<meta property="og:image:type" content="<?= e($seoImageType) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= e($seoTitle) ?>">
<meta property="og:url" content="<?= e($seoUrl) ?>">
<meta property="og:type" content="<?= e($seoType) ?>">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<meta property="og:locale" content="es_CL">
<meta property="fb:app_id" content="1223892215809376">
<?php if ($seoType === 'article' && $articlePublished): ?>
<meta property="article:published_time" content="<?= e($articlePublished) ?>">
<?php if ($articleModified): ?>
<meta property="article:modified_time" content="<?= e($articleModified) ?>">
<?php endif; ?>
<?php if ($articleAuthor): ?>
<meta property="article:author" content="<?= e($articleAuthor) ?>">
<?php endif; ?>
<?php endif; ?>

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($seoTitle) ?>">
<meta name="twitter:description" content="<?= e($seoDescription) ?>">
<meta name="twitter:image" content="<?= e($seoImage) ?>">
<meta name="twitter:image:alt" content="<?= e($seoTitle) ?>">
<?php if ($twitterHandle): ?>
<meta name="twitter:site" content="<?= e($twitterHandle) ?>">
<meta name="twitter:creator" content="<?= e($twitterHandle) ?>">
<?php endif; ?>

<!-- Apple / iOS -->
<meta name="apple-mobile-web-app-title" content="<?= e(SITE_NAME) ?>">
<meta name="format-detection" content="telephone=no">

<!-- Microsoft -->
<meta name="msapplication-TileColor" content="<?= e(\App\Services\Theme::getColor('primary', '#ea580c')) ?>">
<meta name="msapplication-config" content="none">

<!-- Pinterest -->
<?php if (!empty(\App\Services\RedesSociales::get('profile_pinterest'))): ?>
<meta name="pinterest" content="nopin">
<?php endif; ?>

<!-- Favicons -->
<link rel="icon" type="image/x-icon" href="<?= url('favicon.ico') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= asset('img/icons/favicon-32x32.png') ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?= asset('img/icons/favicon-16x16.png') ?>">
<link rel="apple-touch-icon" sizes="192x192" href="<?= asset('img/icons/icon-192x192.png') ?>">

<!-- Schema.org JSON-LD -->
<?php if (!empty($schemas)): ?>
    <?php foreach ($schemas as $schema): ?>
        <script type="application/ld+json">
        <?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
        </script>
    <?php endforeach; ?>
<?php endif; ?>

<?php
// Auto-inject Organization schema with sameAs on every page
$orgSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => SITE_NAME,
    'url' => SITE_URL,
    'description' => SITE_DESCRIPTION,
    'address' => [
        '@type' => 'PostalAddress',
        'addressLocality' => CITY_NAME,
        'addressRegion' => 'Los Lagos',
        'addressCountry' => 'CL',
    ],
];
if (!empty($sameAs)) {
    $orgSchema['sameAs'] = $sameAs;
}
?>
<script type="application/ld+json">
<?= json_encode($orgSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
