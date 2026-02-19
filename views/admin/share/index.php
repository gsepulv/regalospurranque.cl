<?php
/**
 * Admin - Estadísticas de compartidos
 * Variables: $total, $porRed, $topContent, $topCom, $porDia, $desde, $hasta
 */
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <span>Compartidos</span>
</div>

<div class="toolbar">
    <h2 style="margin:0;flex:1">Estadísticas de Compartidos</h2>
    <form method="GET" action="<?= url('/admin/share') ?>" class="toolbar__group" style="gap:0.5rem">
        <input type="date" name="desde" class="form-control" value="<?= e($desde) ?>" style="padding:0.4rem 0.5rem;font-size:0.875rem">
        <span style="color:var(--color-gray)">a</span>
        <input type="date" name="hasta" class="form-control" value="<?= e($hasta) ?>" style="padding:0.4rem 0.5rem;font-size:0.875rem">
        <button type="submit" class="btn btn--primary btn--sm">Aplicar</button>
    </form>
</div>

<!-- Total -->
<div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr));margin-bottom:var(--spacing-6)">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__number"><?= number_format($total) ?></div>
        <div class="stat-card__label">Total compartidos</div>
    </div>
    <?php foreach ($porRed as $red): ?>
        <div class="stat-card">
            <div class="stat-card__number"><?= number_format($red['total']) ?></div>
            <div class="stat-card__label"><?= e(ucfirst($red['red_social'])) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-6);margin-bottom:var(--spacing-6)">

    <!-- Redes sociales -->
    <div class="admin-card">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Por red social</h3>
            <?php if (!empty($porRed)): ?>
                <?php $maxRed = max(array_column($porRed, 'total') ?: [1]); ?>
                <?php
                $colores = [
                    'facebook'  => '#1877f2',
                    'twitter'   => '#1da1f2',
                    'whatsapp'  => '#25d366',
                    'telegram'  => '#0088cc',
                    'email'     => '#6b7280',
                    'copiar'    => '#8b5cf6',
                    'linkedin'  => '#0a66c2',
                ];
                ?>
                <?php foreach ($porRed as $red): ?>
                    <?php
                    $pct = $maxRed > 0 ? ($red['total'] / $maxRed) * 100 : 0;
                    $color = $colores[$red['red_social']] ?? '#6b7280';
                    ?>
                    <div style="margin-bottom:var(--spacing-3)">
                        <div style="display:flex;justify-content:space-between;font-size:0.875rem;margin-bottom:4px">
                            <span style="font-weight:500"><?= e(ucfirst($red['red_social'])) ?></span>
                            <strong><?= number_format($red['total']) ?></strong>
                        </div>
                        <div style="background:var(--color-light);border-radius:4px;height:10px;overflow:hidden">
                            <div style="background:<?= $color ?>;height:100%;width:<?= max(2, $pct) ?>%;border-radius:4px"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;color:var(--color-gray)">Sin datos.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Comercios más compartidos -->
    <div class="admin-card">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Comercios más compartidos</h3>
            <?php if (!empty($topCom)): ?>
                <table class="admin-table" style="font-size:0.875rem">
                    <thead><tr><th>#</th><th>Comercio</th><th style="text-align:right">Compartidos</th></tr></thead>
                    <tbody>
                        <?php foreach ($topCom as $i => $com): ?>
                            <tr>
                                <td><strong><?= $i + 1 ?></strong></td>
                                <td><a href="<?= url('/comercio/' . $com['slug']) ?>" target="_blank"><?= e($com['nombre']) ?></a></td>
                                <td style="text-align:right"><strong><?= number_format($com['total']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;color:var(--color-gray)">Sin datos.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Contenido más compartido -->
<div class="admin-card">
    <div style="padding:var(--spacing-6)">
        <h3 style="margin:0 0 var(--spacing-4)">Páginas más compartidas</h3>
        <?php if (!empty($topContent)): ?>
            <table class="admin-table" style="font-size:0.875rem">
                <thead><tr><th>#</th><th>Página</th><th style="text-align:right">Compartidos</th></tr></thead>
                <tbody>
                    <?php foreach ($topContent as $i => $content): ?>
                        <tr>
                            <td><strong><?= $i + 1 ?></strong></td>
                            <td><small><?= e(truncate($content['pagina'], 60)) ?></small></td>
                            <td style="text-align:right"><strong><?= number_format($content['total']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center;color:var(--color-gray)">Sin datos.</p>
        <?php endif; ?>
    </div>
</div>
