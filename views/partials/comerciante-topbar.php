<?php
/**
 * Barra superior del panel del comerciante
 * Se incluye en todas las vistas autenticadas del comerciante
 * Requiere: $_SESSION['comerciante'] con keys: id, nombre, email
 */
if (empty($_SESSION['comerciante']['id'])) return;
$_cNombre = $_SESSION['comerciante']['nombre'] ?? '';
?>
<div style="background:#F9FAFB;border-bottom:1px solid #E5E7EB;padding:0.5rem 0;margin-bottom:1.5rem">
    <div class="container" style="max-width:720px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem">
        <span style="color:#374151;font-size:0.85rem">
            Hola, <strong><?= e($_cNombre) ?></strong>
        </span>
        <div style="display:flex;gap:1rem;align-items:center">
            <a href="<?= url('/mi-comercio') ?>" style="color:#6B7280;font-size:0.85rem;text-decoration:none">Inicio</a>
            <a href="<?= url('/mi-comercio/perfil') ?>" style="color:#6B7280;font-size:0.85rem;text-decoration:none">Mi perfil</a>
            <a href="<?= url('/mi-comercio/logout') ?>" style="color:#DC2626;font-size:0.85rem;text-decoration:none;font-weight:500">Cerrar sesion</a>
        </div>
    </div>
</div>
