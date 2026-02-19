<?php
/**
 * Admin - Apariencia (colores din&aacute;micos)
 * Variables: $colors (array), $presets (array)
 */
$currentPreset = $colors['preset'] ?? 'naranja';
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <span>Apariencia</span>
</div>

<h2>Apariencia y Colores</h2>

<!-- ═══════ PRESETS ═══════ -->
<div class="admin-card" style="margin-bottom:var(--spacing-6)">
    <div style="padding:var(--spacing-6)">
        <h3 style="margin:0 0 var(--spacing-4)">Temas predefinidos</h3>
        <p style="color:var(--color-muted);margin:0 0 var(--spacing-4)">Selecciona un tema para aplicar todos los colores autom&aacute;ticamente.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:var(--spacing-4)">
            <?php foreach ($presets as $name => $preset): ?>
                <form method="POST" action="<?= url('/admin/apariencia/preset') ?>" style="margin:0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="preset" value="<?= e($name) ?>">
                    <button type="submit" class="admin-card" style="width:100%;cursor:pointer;padding:var(--spacing-4);text-align:center;border:2px solid <?= $currentPreset === $name ? 'var(--color-primary)' : 'transparent' ?>;transition:border-color 0.2s;background:var(--color-white)">
                        <div style="display:flex;gap:4px;justify-content:center;margin-bottom:var(--spacing-2)">
                            <span style="width:24px;height:24px;border-radius:50%;background:<?= e($preset['primary']) ?>;display:inline-block;border:1px solid rgba(0,0,0,0.1)"></span>
                            <span style="width:24px;height:24px;border-radius:50%;background:<?= e($preset['accent'] ?? $preset['primary']) ?>;display:inline-block;border:1px solid rgba(0,0,0,0.1)"></span>
                            <span style="width:24px;height:24px;border-radius:50%;background:<?= e($preset['header_bg']) ?>;display:inline-block;border:1px solid rgba(0,0,0,0.1)"></span>
                        </div>
                        <strong style="font-size:var(--font-size-sm)"><?= e($preset['label']) ?></strong>
                        <?php if ($currentPreset === $name): ?>
                            <div style="color:var(--color-primary);font-size:var(--font-size-xs);margin-top:2px">Activo</div>
                        <?php endif; ?>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ═══════ COLORES PERSONALIZADOS ═══════ -->
<form method="POST" action="<?= url('/admin/apariencia/update') ?>">
    <?= csrf_field() ?>

    <div class="admin-card" style="margin-bottom:var(--spacing-6)">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Colores principales</h3>
            <p style="color:var(--color-muted);margin:0 0 var(--spacing-4)">Personaliza los colores o ajusta despu&eacute;s de aplicar un preset.</p>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:var(--spacing-4)">
                <?php
                $colorFields = [
                    'primary'          => 'Color primario',
                    'primary_light'    => 'Primario claro',
                    'primary_dark'     => 'Primario oscuro',
                    'accent'           => 'Color acento',
                    'accent_light'     => 'Acento claro',
                    'accent_dark'      => 'Acento oscuro',
                ];
                foreach ($colorFields as $key => $label):
                ?>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label"><?= $label ?></label>
                    <div style="display:flex;align-items:center;gap:var(--spacing-2)">
                        <input type="color" name="<?= $key ?>" value="<?= e($colors[$key] ?? '#000000') ?>"
                               style="width:48px;height:38px;padding:2px;border:1px solid var(--color-border);border-radius:var(--radius-sm);cursor:pointer"
                               data-color-input>
                        <input type="text" class="form-control" value="<?= e($colors[$key] ?? '') ?>"
                               style="width:120px;font-family:monospace;font-size:var(--font-size-sm)"
                               data-color-text data-target="<?= $key ?>" readonly>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="admin-card" style="margin-bottom:var(--spacing-6)">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Header y Footer</h3>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:var(--spacing-4)">
                <?php
                $layoutFields = [
                    'header_bg'   => 'Fondo header',
                    'header_text' => 'Texto header',
                    'footer_bg'   => 'Fondo footer',
                    'footer_text' => 'Texto footer',
                ];
                foreach ($layoutFields as $key => $label):
                ?>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label"><?= $label ?></label>
                    <div style="display:flex;align-items:center;gap:var(--spacing-2)">
                        <input type="color" name="<?= $key ?>" value="<?= e($colors[$key] ?? '#000000') ?>"
                               style="width:48px;height:38px;padding:2px;border:1px solid var(--color-border);border-radius:var(--radius-sm);cursor:pointer"
                               data-color-input>
                        <input type="text" class="form-control" value="<?= e($colors[$key] ?? '') ?>"
                               style="width:120px;font-family:monospace;font-size:var(--font-size-sm)"
                               data-color-text data-target="<?= $key ?>" readonly>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="admin-card" style="margin-bottom:var(--spacing-6)">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Botones</h3>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:var(--spacing-4)">
                <?php
                $btnFields = [
                    'btn_primary_bg'   => 'Bot&oacute;n primario (fondo)',
                    'btn_primary_text' => 'Bot&oacute;n primario (texto)',
                    'btn_accent_bg'    => 'Bot&oacute;n acento (fondo)',
                    'btn_accent_text'  => 'Bot&oacute;n acento (texto)',
                ];
                foreach ($btnFields as $key => $label):
                ?>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label"><?= $label ?></label>
                    <div style="display:flex;align-items:center;gap:var(--spacing-2)">
                        <input type="color" name="<?= $key ?>" value="<?= e($colors[$key] ?? '#000000') ?>"
                               style="width:48px;height:38px;padding:2px;border:1px solid var(--color-border);border-radius:var(--radius-sm);cursor:pointer"
                               data-color-input>
                        <input type="text" class="form-control" value="<?= e($colors[$key] ?? '') ?>"
                               style="width:120px;font-family:monospace;font-size:var(--font-size-sm)"
                               data-color-text data-target="<?= $key ?>" readonly>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Preview -->
    <div class="admin-card" style="margin-bottom:var(--spacing-6)">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Vista previa</h3>
            <div id="colorPreview" style="border:1px solid var(--color-border);border-radius:var(--radius-md);overflow:hidden">
                <div id="previewHeader" style="background:<?= e($colors['header_bg'] ?? '#1e293b') ?>;color:<?= e($colors['header_text'] ?? '#fff') ?>;padding:var(--spacing-4);font-weight:600">
                    <?= e(SITE_NAME) ?> &mdash; Header
                </div>
                <div style="padding:var(--spacing-6);background:var(--color-white)">
                    <p style="margin:0 0 var(--spacing-4)">Texto de ejemplo para ver los colores aplicados.</p>
                    <div style="display:flex;gap:var(--spacing-3);flex-wrap:wrap">
                        <span id="previewBtnPrimary" class="btn" style="background:<?= e($colors['btn_primary_bg'] ?? '#ea580c') ?>;color:<?= e($colors['btn_primary_text'] ?? '#fff') ?>">Bot&oacute;n Primario</span>
                        <span id="previewBtnAccent" class="btn" style="background:<?= e($colors['btn_accent_bg'] ?? '#1a365d') ?>;color:<?= e($colors['btn_accent_text'] ?? '#fff') ?>">Bot&oacute;n Acento</span>
                    </div>
                </div>
                <div id="previewFooter" style="background:<?= e($colors['footer_bg'] ?? '#1e293b') ?>;color:<?= e($colors['footer_text'] ?? '#fff') ?>;padding:var(--spacing-4);font-size:var(--font-size-sm)">
                    Footer &copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="toolbar" style="justify-content:flex-end">
        <button type="submit" class="btn btn--primary">Guardar colores</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sync color inputs with text displays
    document.querySelectorAll('[data-color-input]').forEach(function(input) {
        var textInput = input.nextElementSibling;
        input.addEventListener('input', function() {
            textInput.value = input.value;
            updatePreview();
        });
    });

    function updatePreview() {
        var getValue = function(name) {
            var el = document.querySelector('input[type="color"][name="' + name + '"]');
            return el ? el.value : '';
        };
        var h = document.getElementById('previewHeader');
        var f = document.getElementById('previewFooter');
        var bp = document.getElementById('previewBtnPrimary');
        var ba = document.getElementById('previewBtnAccent');
        if (h) { h.style.background = getValue('header_bg'); h.style.color = getValue('header_text'); }
        if (f) { f.style.background = getValue('footer_bg'); f.style.color = getValue('footer_text'); }
        if (bp) { bp.style.background = getValue('btn_primary_bg'); bp.style.color = getValue('btn_primary_text'); }
        if (ba) { ba.style.background = getValue('btn_accent_bg'); ba.style.color = getValue('btn_accent_text'); }
    }
});
</script>
