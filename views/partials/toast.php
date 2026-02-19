<?php if (!empty($flash['success'])): ?>
    <div class="toast toast--success" role="alert">
        <span class="toast__message"><?= e($flash['success']) ?></span>
        <button class="toast__close" data-toast-close>&times;</button>
    </div>
<?php endif; ?>

<?php if (!empty($flash['error'])): ?>
    <div class="toast toast--error" role="alert">
        <span class="toast__message"><?= e($flash['error']) ?></span>
        <button class="toast__close" data-toast-close>&times;</button>
    </div>
<?php endif; ?>

<?php if (!empty($flash['warning'])): ?>
    <div class="toast toast--warning" role="alert">
        <span class="toast__message"><?= e($flash['warning']) ?></span>
        <button class="toast__close" data-toast-close>&times;</button>
    </div>
<?php endif; ?>

<?php if (!empty($flash['info'])): ?>
    <div class="toast toast--info" role="alert">
        <span class="toast__message"><?= e($flash['info']) ?></span>
        <button class="toast__close" data-toast-close>&times;</button>
    </div>
<?php endif; ?>
