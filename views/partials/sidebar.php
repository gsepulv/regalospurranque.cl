<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar__header">
        <a href="<?= url('/admin/dashboard') ?>">
            <strong><?= e(SITE_NAME) ?></strong>
            <small>Panel Admin</small>
        </a>
    </div>

    <nav class="sidebar__nav">
        <?php
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        $role = $admin['rol'] ?? 'editor';
        $permission = new \App\Services\Permission();

        // Contar reseñas pendientes para badge
        $pendientes = 0;
        try {
            $pendientes = \App\Core\Database::getInstance()->count('resenas', "estado = 'pendiente'");
        } catch (\Throwable $e) {}
        $cambiosPendientes = 0;
        try {
        $cambiosPendientes = \App\Core\Database::getInstance()->count('comercio_cambios_pendientes', "estado = 'pendiente'");
        } catch (\Throwable $e) {}
        $renovacionesPendientes = 0;
        try {
            $renovacionesPendientes = \App\Core\Database::getInstance()->count('comercio_renovaciones', "estado = 'pendiente'");
        } catch (\Throwable $e) {}
        $mensajesNoLeidos = 0;
        try {
            $mensajesNoLeidos = \App\Core\Database::getInstance()->count('mensajes_contacto', 'leido = 0');
        } catch (\Throwable $e) {}

        // Contar comercios para badge
        $totalComercios = 0;
        try {
            $totalComercios = \App\Core\Database::getInstance()->count('comercios');
        } catch (\Throwable $e) {}

        $menuItems = [
            ['dashboard',    'Dashboard',        '/admin/dashboard',     '&#128202;', null],
            ['comercios',    'Comercios',         '/admin/comercios',     '&#127978;', $totalComercios ?: null],
            ['categorias',   'Categorías',        '/admin/categorias',    '&#128194;', null],
            ['fechas',       'Fechas Especiales', '/admin/fechas',        '&#128197;', null],
            ['noticias',     'Noticias',          '/admin/noticias',      '&#128240;', null],
            ['banners',      'Banners',           '/admin/banners',       '&#128444;', null],
            ['resenas',      'Reseñas',           '/admin/resenas',       '&#11088;',  $pendientes ?: null],
            ['planes',       'Planes',            '/admin/planes',        '&#128176;', null],
            ['cambios',      'Cambios Pendientes','/admin/cambios-pendientes', '&#128221;', $cambiosPendientes ?: null],
            ['renovaciones', 'Renovaciones',      '/admin/renovaciones',      '&#128260;', $renovacionesPendientes ?: null],
            ['contacto',     'Contacto',          '/admin/contacto',      '&#128233;', $mensajesNoLeidos ?: null],
            ['correos',      'Enviar Correo',     '/admin/correos/enviar', '&#9993;',   null],
            ['reportes',     'Reportes',          '/admin/reportes',      '&#128200;', null],
            ['share',        'Compartidos',       '/admin/share',         '&#128279;', null],
            ['redes',        'Redes Sociales',    '/admin/redes-sociales','&#128241;', null],
            ['apariencia',   'Apariencia',        '/admin/apariencia',    '&#127912;', null],
            ['seo',             'SEO',               '/admin/seo',              '&#128269;', null],
            ['notificaciones', 'Notificaciones',   '/admin/notificaciones',  '&#128236;', null],
            ['usuarios',       'Usuarios',          '/admin/usuarios',        '&#128101;', null],
            ['mantenimiento',  'Mantenimiento',     '/admin/mantenimiento',   '&#128295;', null],
            ['sitios',          'Sitios',            '/admin/sitios',          '&#127760;', null],
        ];
        ?>

        <?php foreach ($menuItems as [$mod, $label, $href, $icon, $badge]): ?>
            <?php if ($permission->can($role, $mod)): ?>
                <a href="<?= url($href) ?>"
                   class="sidebar__link <?= str_starts_with($currentUri, $href) ? 'sidebar__link--active' : '' ?>">
                    <span class="sidebar__icon"><?= $icon ?></span>
                    <?= $label ?>
                    <?php if ($badge): ?>
                        <span class="sidebar__link-badge"><?= $badge ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar__footer">
        <a href="<?= url('/admin/perfil') ?>"
           class="sidebar__link <?= str_starts_with($currentUri, '/admin/perfil') ? 'sidebar__link--active' : '' ?>">
            <span class="sidebar__icon">&#128100;</span>
            Mi Perfil
        </a>
        <a href="<?= url('/') ?>" class="sidebar__link" target="_blank">
            <span class="sidebar__icon">&#8599;</span>
            Ver sitio
        </a>
        <a href="<?= url('/admin/logout') ?>" class="sidebar__link sidebar__link--danger">
            <span class="sidebar__icon">&#10140;</span>
            Cerrar sesion
        </a>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
