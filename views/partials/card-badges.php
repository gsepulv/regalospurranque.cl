<?php
/**
 * Partial: Badges de plan y validado para tarjetas de comercio
 * Usar: include con variable $com disponible
 * 
 * Muestra badges según plan (no muestra freemium) y validado
 */
$_plan = $com['plan'] ?? 'freemium';
$_validado = !empty($com['validado']);
$_hasBadges = ($_plan !== 'freemium') || $_validado;
?>
<?php if ($_hasBadges): ?>
    <div class="card__badges">
        <?php if ($_plan === 'sponsor'): ?>
            <span class="badge badge--plan badge--sponsor">&#127942; Sponsor</span>
        <?php elseif ($_plan === 'premium'): ?>
            <span class="badge badge--plan badge--premium">&#11088; Premium</span>
        <?php elseif ($_plan === 'basico'): ?>
            <span class="badge badge--plan badge--basico">&#9989; Básico</span>
        <?php elseif ($_plan === 'banner'): ?>
            <span class="badge badge--plan badge--banner">&#128226; Banner</span>
        <?php endif; ?>
        <?php if ($_validado): ?>
            <span class="badge badge--validado">&#9989; Validado</span>
        <?php endif; ?>
    </div>
<?php endif; ?>
