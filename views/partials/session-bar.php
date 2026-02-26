<?php
/**
 * Barra de sesión global — se muestra en TODAS las páginas cuando hay sesión activa
 * Incluir en el layout base (public.php) justo después del <body>
 */
$_sbComerciante = $_SESSION['comerciante'] ?? null;
$_sbAdmin       = $_SESSION['admin'] ?? null;

if (!$_sbComerciante && !$_sbAdmin) return;
?>
<div id="sessionBar" style="background:#1F2937;color:#F9FAFB;font-size:0.8rem;padding:0.4rem 0;position:sticky;top:0;z-index:10000;box-shadow:0 1px 3px rgba(0,0,0,0.2)">
    <div style="max-width:1200px;margin:0 auto;padding:0 1rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.25rem">
        <?php if ($_sbComerciante): ?>
            <span style="color:#D1D5DB">Sesion activa: <strong style="color:#F9FAFB"><?= e($_sbComerciante['nombre'] ?? '') ?></strong></span>
            <div style="display:flex;gap:0.75rem;align-items:center">
                <a href="<?= url('/mi-comercio') ?>" style="color:#93C5FD;text-decoration:none">Mi comercio</a>
                <a href="<?= url('/mi-comercio/perfil') ?>" style="color:#93C5FD;text-decoration:none">Mi perfil</a>
                <a href="<?= url('/mi-comercio/logout') ?>" style="color:#FCA5A5;text-decoration:none">Cerrar sesion</a>
            </div>
        <?php elseif ($_sbAdmin): ?>
            <span style="color:#D1D5DB">Sesion admin: <strong style="color:#F9FAFB"><?= e($_sbAdmin['nombre'] ?? '') ?></strong></span>
            <div style="display:flex;gap:0.75rem;align-items:center">
                <a href="<?= url('/admin/dashboard') ?>" style="color:#93C5FD;text-decoration:none">Panel admin</a>
                <a href="<?= url('/admin/logout') ?>" style="color:#FCA5A5;text-decoration:none">Cerrar sesion</a>
            </div>
        <?php endif; ?>
    </div>
</div>
