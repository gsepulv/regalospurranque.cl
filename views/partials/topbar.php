<header class="admin-topbar">
    <div class="topbar__left">
        <button class="topbar__toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <h1 class="topbar__title"><?= e($title ?? 'Dashboard') ?></h1>
    </div>
    <div class="topbar__right">
        <?php if (($admin['rol'] ?? '') === 'superadmin'): ?>
            <?php
            $siteManager = \App\Services\SiteManager::getInstance();
            $allSites = $siteManager->getAllSites();
            $currentSiteId = $_SESSION['admin_site_id'] ?? $siteManager->getSiteId();
            ?>
            <?php if (count($allSites) > 1): ?>
                <form method="POST" action="<?= url('/admin/sitios/cambiar') ?>" class="topbar__site-selector" style="display:inline;">
                    <?= csrf_field() ?>
                    <select name="site_id" class="form-control form-control--sm" onchange="this.form.submit()" style="min-width:140px;font-size:0.8rem;">
                        <?php foreach ($allSites as $s): ?>
                            <option value="<?= (int)$s['id'] ?>" <?= (int)$s['id'] === (int)$currentSiteId ? 'selected' : '' ?>>
                                <?= e($s['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>
        <?php endif; ?>
        <span class="topbar__user">
            <?= e($admin['nombre'] ?? 'Admin') ?>
            <small>(<?= e($admin['rol'] ?? '') ?>)</small>
        </span>
    </div>
</header>
