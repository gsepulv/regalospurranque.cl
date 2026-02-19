<?php
// Uso: incluir con variables $modalId, $modalTitle, $modalBody
$modalId    = $modalId ?? 'modal';
$modalTitle = $modalTitle ?? '';
$modalBody  = $modalBody ?? '';
?>
<div class="modal" id="<?= e($modalId) ?>">
    <div class="modal__overlay" data-modal-close></div>
    <div class="modal__content">
        <?php if ($modalTitle): ?>
            <div class="modal__header">
                <h3><?= e($modalTitle) ?></h3>
                <button class="modal__close" data-modal-close>&times;</button>
            </div>
        <?php endif; ?>
        <div class="modal__body">
            <?= $modalBody ?>
        </div>
    </div>
</div>
