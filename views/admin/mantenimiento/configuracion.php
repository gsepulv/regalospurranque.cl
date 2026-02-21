<?php
/**
 * Admin - Mantenimiento > Configuración del Sistema
 * Variables: $config (associative array of all config key => value)
 */
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/mantenimiento/backups') ?>">Mantenimiento</a> &rsaquo;
    <span>Configuraci&oacute;n del Sistema</span>
</div>

<?php
$currentTab = 'configuracion';
$tabs = [
    'backups'        => ['label' => 'Backups',           'url' => '/admin/mantenimiento/backups'],
    'archivos'       => ['label' => 'Explorador',        'url' => '/admin/mantenimiento/archivos'],
    'salud'          => ['label' => 'Salud',             'url' => '/admin/mantenimiento/salud'],
    'logs'           => ['label' => 'Logs',              'url' => '/admin/mantenimiento/logs'],
    'herramientas'   => ['label' => 'Herramientas',      'url' => '/admin/mantenimiento/herramientas'],
    'configuracion'  => ['label' => 'Configuraci&oacute;n',  'url' => '/admin/mantenimiento/configuracion'],
];
?>
<div class="admin-tabs" style="margin-bottom:var(--spacing-6)">
    <?php foreach ($tabs as $key => $tab): ?>
        <a href="<?= url($tab['url']) ?>" class="admin-tab <?= $currentTab === $key ? 'admin-tab--active' : '' ?>"><?= $tab['label'] ?></a>
    <?php endforeach; ?>
</div>

<?php if ($flash['success'] ?? false): ?>
    <div class="toast toast--success"><?= e($flash['success']) ?></div>
<?php endif; ?>
<?php if ($flash['error'] ?? false): ?>
    <div class="toast toast--error"><?= e($flash['error']) ?></div>
<?php endif; ?>

<form method="POST" action="<?= url('/admin/mantenimiento/configuracion') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Section 1: General -->
    <div class="admin-card config-section">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-5)">General</h3>

            <div class="config-grid">
                <div class="form-group config-grid--full">
                    <label class="form-label">Nombre del sitio</label>
                    <input type="text" name="site_name" class="form-control" value="<?= e($config['site_name'] ?? 'Regalos Purranque') ?>">
                </div>

                <div class="form-group config-grid--full">
                    <label class="form-label">Descripci&oacute;n del sitio</label>
                    <textarea name="site_description" class="form-control" rows="3"><?= e($config['site_description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Email de contacto</label>
                    <input type="email" name="contact_email" class="form-control" value="<?= e($config['contact_email'] ?? '') ?>" placeholder="contacto@ejemplo.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Tel&eacute;fono de contacto</label>
                    <input type="tel" name="contact_phone" class="form-control" value="<?= e($config['contact_phone'] ?? '') ?>" placeholder="+56 9 1234 5678">
                </div>

                <div class="form-group config-grid--full">
                    <label class="form-label">Direcci&oacute;n f&iacute;sica</label>
                    <input type="text" name="address" class="form-control" value="<?= e($config['address'] ?? '') ?>" placeholder="Calle 123, Purranque">
                </div>

                <div class="form-group">
                    <label class="form-label">Logo del sitio</label>
                    <?php if (!empty($config['logo'])): ?>
                        <div class="config-file-preview">
                            <img src="<?= asset($config['logo']) ?>" alt="Logo actual" loading="lazy" style="max-height:60px;margin-bottom:var(--spacing-2)">
                            <small style="color:var(--color-gray);display:block">Logo actual</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">Favicon</label>
                    <?php if (!empty($config['favicon'])): ?>
                        <div class="config-file-preview">
                            <img src="<?= asset($config['favicon']) ?>" alt="Favicon actual" loading="lazy" style="max-height:32px;margin-bottom:var(--spacing-2)">
                            <small style="color:var(--color-gray);display:block">Favicon actual</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="favicon" class="form-control" accept="image/*,.ico">
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Redes Sociales -->
    <div class="admin-card config-section">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-5)">Redes Sociales</h3>

            <div class="config-grid">
                <div class="form-group">
                    <label class="form-label">Facebook URL</label>
                    <input type="url" name="facebook_url" class="form-control" value="<?= e($config['facebook_url'] ?? '') ?>" placeholder="https://facebook.com/...">
                </div>

                <div class="form-group">
                    <label class="form-label">Instagram URL</label>
                    <input type="url" name="instagram_url" class="form-control" value="<?= e($config['instagram_url'] ?? '') ?>" placeholder="https://instagram.com/...">
                </div>

                <div class="form-group">
                    <label class="form-label">Twitter/X URL</label>
                    <input type="url" name="twitter_url" class="form-control" value="<?= e($config['twitter_url'] ?? '') ?>" placeholder="https://x.com/...">
                </div>

                <div class="form-group">
                    <label class="form-label">YouTube URL</label>
                    <input type="url" name="youtube_url" class="form-control" value="<?= e($config['youtube_url'] ?? '') ?>" placeholder="https://youtube.com/...">
                </div>

                <div class="form-group config-grid--full">
                    <label class="form-label">TikTok URL</label>
                    <input type="url" name="tiktok_url" class="form-control" value="<?= e($config['tiktok_url'] ?? '') ?>" placeholder="https://tiktok.com/@...">
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Correo -->
    <div class="admin-card config-section">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-5)">Correo</h3>

            <div class="config-grid">
                <div class="form-group">
                    <label class="form-label">Email de notificaciones</label>
                    <input type="email" name="notification_email" class="form-control" value="<?= e($config['notification_email'] ?? '') ?>" placeholder="notificaciones@ejemplo.com">
                    <small class="form-hint">Direcci&oacute;n donde se enviar&aacute;n las notificaciones del sistema.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Asunto de notificaciones</label>
                    <input type="text" name="notification_subject" class="form-control" value="<?= e($config['notification_subject'] ?? '[Regalos Purranque] Notificación') ?>">
                    <small class="form-hint">Prefijo para el asunto de los correos de notificaci&oacute;n.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Funcionalidades -->
    <div class="admin-card config-section">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-5)">Funcionalidades</h3>

            <div class="config-toggles">
                <div class="config-toggle">
                    <div class="config-toggle__info">
                        <div class="config-toggle__label">Rese&ntilde;as habilitadas</div>
                        <div class="config-toggle__desc">Permite a los usuarios dejar rese&ntilde;as en los comercios.</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="resenas_enabled" value="0">
                        <input type="checkbox" name="resenas_enabled" value="1" <?= ($config['resenas_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                        <span class="toggle-switch__slider"></span>
                    </label>
                </div>

                <div class="config-toggle">
                    <div class="config-toggle__info">
                        <div class="config-toggle__label">Mapa habilitado</div>
                        <div class="config-toggle__desc">Muestra el mapa interactivo en la p&aacute;gina principal.</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="mapa_enabled" value="0">
                        <input type="checkbox" name="mapa_enabled" value="1" <?= ($config['mapa_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                        <span class="toggle-switch__slider"></span>
                    </label>
                </div>

                <div class="config-toggle">
                    <div class="config-toggle__info">
                        <div class="config-toggle__label">Noticias habilitadas</div>
                        <div class="config-toggle__desc">Activa la secci&oacute;n de noticias en el sitio p&uacute;blico.</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="noticias_enabled" value="0">
                        <input type="checkbox" name="noticias_enabled" value="1" <?= ($config['noticias_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                        <span class="toggle-switch__slider"></span>
                    </label>
                </div>

                <div class="config-toggle">
                    <div class="config-toggle__info">
                        <div class="config-toggle__label">Banners habilitados</div>
                        <div class="config-toggle__desc">Muestra banners promocionales en la p&aacute;gina principal.</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="banners_enabled" value="0">
                        <input type="checkbox" name="banners_enabled" value="1" <?= ($config['banners_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                        <span class="toggle-switch__slider"></span>
                    </label>
                </div>

                <div class="config-toggle">
                    <div class="config-toggle__info">
                        <div class="config-toggle__label">Share habilitado</div>
                        <div class="config-toggle__desc">Permite compartir comercios en redes sociales.</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="share_enabled" value="0">
                        <input type="checkbox" name="share_enabled" value="1" <?= ($config['share_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                        <span class="toggle-switch__slider"></span>
                    </label>
                </div>

                <div class="config-toggle">
                    <div class="config-toggle__info">
                        <div class="config-toggle__label">Analytics habilitado</div>
                        <div class="config-toggle__desc">Activa el seguimiento de visitas y estad&iacute;sticas del sitio.</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="analytics_enabled" value="0">
                        <input type="checkbox" name="analytics_enabled" value="1" <?= ($config['analytics_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                        <span class="toggle-switch__slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 5: Apariencia -->
    <div class="admin-card config-section">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-5)">Apariencia</h3>

            <div class="config-grid">
                <div class="form-group">
                    <label class="form-label">Color primario</label>
                    <div class="config-color-input">
                        <input type="color" name="color_primary" value="<?= e($config['color_primary'] ?? '#2563eb') ?>" class="config-color-picker">
                        <input type="text" class="form-control" value="<?= e($config['color_primary'] ?? '#2563eb') ?>" readonly style="width:100px;font-family:monospace;font-size:0.8125rem" onclick="this.previousElementSibling.click()">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Color de acento</label>
                    <div class="config-color-input">
                        <input type="color" name="color_accent" value="<?= e($config['color_accent'] ?? '#059669') ?>" class="config-color-picker">
                        <input type="text" class="form-control" value="<?= e($config['color_accent'] ?? '#059669') ?>" readonly style="width:100px;font-family:monospace;font-size:0.8125rem" onclick="this.previousElementSibling.click()">
                    </div>
                </div>

                <div class="form-group config-grid--full">
                    <label class="form-label" style="display:flex;align-items:center;gap:var(--spacing-2)">
                        <input type="hidden" name="show_visit_counter" value="0">
                        <input type="checkbox" name="show_visit_counter" value="1" <?= ($config['show_visit_counter'] ?? '0') == '1' ? 'checked' : '' ?>>
                        Mostrar contador de visitas p&uacute;blico
                    </label>
                    <small class="form-hint">Muestra el n&uacute;mero de visitas en el sitio p&uacute;blico.</small>
                </div>

                <div class="form-group config-grid--full">
                    <label class="form-label" style="display:flex;align-items:center;gap:var(--spacing-2)">
                        <input type="hidden" name="show_rating_cards" value="0">
                        <input type="checkbox" name="show_rating_cards" value="1" <?= ($config['show_rating_cards'] ?? '0') == '1' ? 'checked' : '' ?>>
                        Mostrar calificaci&oacute;n en cards
                    </label>
                    <small class="form-hint">Muestra la calificaci&oacute;n promedio en las tarjetas de comercios.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div style="display:flex;gap:var(--spacing-3);margin-top:var(--spacing-4)">
        <button type="submit" class="btn btn--primary">Guardar Configuraci&oacute;n</button>
    </div>
</form>

<style>
/* Config sections spacing */
.config-section {
    margin-bottom: var(--spacing-6);
}

/* Config grid (2 columns on desktop) */
.config-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-4);
}
.config-grid--full {
    grid-column: 1 / -1;
}

/* Toggle switch (CSS-only) */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
    cursor: pointer;
}
.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}
.toggle-switch__slider {
    position: absolute;
    inset: 0;
    background: #d1d5db;
    border-radius: 26px;
    transition: background 0.25s;
}
.toggle-switch__slider::before {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    left: 3px;
    bottom: 3px;
    background: #fff;
    border-radius: 50%;
    transition: transform 0.25s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.toggle-switch input:checked + .toggle-switch__slider {
    background: var(--color-primary, #2563eb);
}
.toggle-switch input:checked + .toggle-switch__slider::before {
    transform: translateX(22px);
}

/* Toggle rows */
.config-toggles {
    display: flex;
    flex-direction: column;
    gap: 0;
}
.config-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--spacing-4);
    padding: var(--spacing-4) 0;
    border-bottom: 1px solid var(--color-border, #e5e7eb);
}
.config-toggle:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.config-toggle:first-child {
    padding-top: 0;
}
.config-toggle__label {
    font-weight: 600;
    font-size: 0.9375rem;
    margin-bottom: 2px;
}
.config-toggle__desc {
    font-size: 0.8125rem;
    color: var(--color-gray);
}

/* Color input */
.config-color-input {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}
.config-color-picker {
    width: 44px;
    height: 44px;
    padding: 2px;
    border: 2px solid var(--color-border, #e5e7eb);
    border-radius: var(--radius-md, 8px);
    cursor: pointer;
    background: none;
}
.config-color-picker::-webkit-color-swatch-wrapper {
    padding: 0;
}
.config-color-picker::-webkit-color-swatch {
    border: none;
    border-radius: 4px;
}

/* File preview */
.config-file-preview {
    margin-bottom: var(--spacing-3);
    padding: var(--spacing-3);
    background: var(--color-light, #f9fafb);
    border-radius: var(--radius-md, 8px);
    display: inline-block;
}

@media (max-width: 768px) {
    .config-grid {
        grid-template-columns: 1fr;
    }
    .config-grid--full {
        grid-column: 1;
    }
    .config-toggle {
        flex-direction: row;
    }
}
</style>

<script>
// Sync color picker with text display
document.querySelectorAll('.config-color-picker').forEach(function(picker) {
    picker.addEventListener('input', function() {
        var textInput = this.nextElementSibling;
        if (textInput) {
            textInput.value = this.value;
        }
    });
});
</script>
