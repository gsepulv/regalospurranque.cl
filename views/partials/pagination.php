<?php
/**
 * Partial de páginación
 * Variables requeridas: $currentPage, $totalPages, $baseUrl
 * Opcional: $queryParams (array extra de parámetros GET)
 */
$currentPage = $currentPage ?? 1;
$totalPages  = $totalPages ?? 1;
$baseUrl     = $baseUrl ?? '/';
$queryParams = $queryParams ?? [];

if ($totalPages <= 1) return;

// Construir URL con página
$buildUrl = function(int $page) use ($baseUrl, $queryParams): string {
    $params = array_merge($queryParams, ['page' => $page]);
    return url($baseUrl) . '?' . http_build_query($params);
};

// Rango de páginas a mostrar
$range = 2;
$start = max(1, $currentPage - $range);
$end   = min($totalPages, $currentPage + $range);
?>
<nav class="págination" aria-label="Páginación">
    <?php if ($currentPage > 1): ?>
        <a href="<?= $buildUrl($currentPage - 1) ?>" class="págination__link">&laquo; Anterior</a>
    <?php endif; ?>

    <?php if ($start > 1): ?>
        <a href="<?= $buildUrl(1) ?>" class="págination__link">1</a>
        <?php if ($start > 2): ?>
            <span class="págination__dots">&hellip;</span>
        <?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $start; $i <= $end; $i++): ?>
        <a href="<?= $buildUrl($i) ?>"
           class="págination__link <?= $i === $currentPage ? 'págination__link--active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($end < $totalPages): ?>
        <?php if ($end < $totalPages - 1): ?>
            <span class="págination__dots">&hellip;</span>
        <?php endif; ?>
        <a href="<?= $buildUrl($totalPages) ?>" class="págination__link"><?= $totalPages ?></a>
    <?php endif; ?>

    <?php if ($currentPage < $totalPages): ?>
        <a href="<?= $buildUrl($currentPage + 1) ?>" class="págination__link">Siguiente &raquo;</a>
    <?php endif; ?>
</nav>
