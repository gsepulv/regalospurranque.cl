<?php
/**
 * Admin - Formulario de banner (crear / editar)
 * Variables: $comercios, optionally $banner
 */
$editing = isset($banner);

// Especificaciones por tipo de banner
$bannerSpecs = [
    'hero' => [
        'label'  => 'Hero (Portada principal)',
        'ancho'  => 1200,
        'alto'   => 400,
        'ratio'  => '3:1',
        'desc'   => 'Banner principal de la portada. Maxima visibilidad.',
        'nota'   => 'Se muestra como slider si hay multiples banners hero activos.',
    ],
    'sidebar' => [
        'label'  => 'Sidebar (Columna lateral)',
        'ancho'  => 300,
        'alto'   => 250,
        'ratio'  => '6:5',
        'desc'   => 'Banner compacto en la columna lateral.',
        'nota'   => 'Visible en páginas de listado y detalle de comercios.',
    ],
    'entre_comercios' => [
        'label'  => 'Entre comercios (Horizontal)',
        'ancho'  => 728,
        'alto'   => 90,
        'ratio'  => '8:1',
        'desc'   => 'Tira horizontal entre listados de comercios.',
        'nota'   => 'Formato leaderboard estandar. Ideal para promociones.',
    ],
    'footer' => [
        'label'  => 'Footer (Pie de página)',
        'ancho'  => 728,
        'alto'   => 90,
        'ratio'  => '8:1',
        'desc'   => 'Banner horizontal sobre el pie de página.',
        'nota'   => 'Visible en todas las páginas del sitio.',
    ],
];
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/banners') ?>">Banners</a> &rsaquo;
    <span><?= $editing ? 'Editar banner' : 'Nuevo banner' ?></span>
</div>

<h2><?= $editing ? 'Editar banner' : 'Nuevo banner' ?></h2>

<?php if (!empty($errors)): ?>
    <div class="toast toast--error toast--inline" role="alert">
        <span class="toast__message">
            <?php foreach ($errors as $field => $msg): ?>
                <?= e(is_array($msg) ? implode(', ', $msg) : $msg) ?><br>
            <?php endforeach; ?>
        </span>
    </div>
<?php endif; ?>

<form method="POST"
      action="<?= $editing ? url('/admin/banners/update/' . $banner['id']) : url('/admin/banners/store') ?>"
      enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="admin-card mb-3">
        <div class="admin-card__header">
            <h3 class="admin-card__title">Datos del banner</h3>
        </div>
        <div class="admin-card__body">
            <div class="form-group">
                <label class="form-label" for="titulo">Titulo</label>
                <input type="text"
                       id="titulo"
                       name="titulo"
                       class="form-control"
                       value="<?= e(old('titulo', $banner['titulo'] ?? '')) ?>"
                       placeholder="Titulo descriptivo del banner (uso interno)"
                       minlength="3" maxlength="100" required>
                <small style="color:var(--color-gray)">Min. 3, max. 100 caracteres.</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="tipo">Tipo de banner *</label>
                    <select id="tipo" name="tipo" class="form-control" required>
                        <option value="">Seleccionar tipo</option>
                        <?php foreach ($bannerSpecs as $val => $spec): ?>
                            <option value="<?= $val ?>"
                                    <?= old('tipo', $banner['tipo'] ?? '') === $val ? 'selected' : '' ?>>
                                <?= $spec['label'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="url">URL de destino</label>
                    <input type="url"
                           id="url"
                           name="url"
                           class="form-control"
                           value="<?= e(old('url', $banner['url'] ?? '')) ?>"
                           placeholder="https://ejemplo.com"
                           minlength="10" maxlength="255" required>
                    <small style="color:var(--color-gray)">Min. 10, max. 255 caracteres.</small>
                </div>
            </div>

            <!-- Guia de dimensiones (se actualiza con JS segun tipo) -->
            <div id="banner-spec-info" style="display:none; margin-bottom:1rem; padding:1rem; background:var(--color-bg-alt, #f0f7ff); border-radius:8px; border-left:4px solid var(--color-primary, #3b82f6);">
                <div style="display:flex; align-items:flex-start; gap:1rem; flex-wrap:wrap;">
                    <div style="flex:1; min-width:200px;">
                        <p style="margin:0 0 0.25rem; font-weight:600; color:var(--color-text, #1e293b);" id="spec-title"></p>
                        <p style="margin:0 0 0.5rem; font-size:0.85rem; color:var(--color-gray, #64748b);" id="spec-desc"></p>
                        <p style="margin:0; font-size:0.8rem; color:var(--color-gray, #64748b);" id="spec-nota"></p>
                    </div>
                    <div style="text-align:center; flex-shrink:0;">
                        <div id="spec-preview-box" style="border:2px dashed var(--color-primary, #3b82f6); border-radius:6px; display:flex; align-items:center; justify-content:center; background:#fff; position:relative; overflow:hidden;">
                            <span style="font-size:0.7rem; color:var(--color-gray, #94a3b8); padding:0.25rem;" id="spec-dimensions"></span>
                        </div>
                        <p style="margin:0.25rem 0 0; font-size:0.75rem; color:var(--color-gray, #94a3b8);">Proporcion recomendada</p>
                    </div>
                </div>
            </div>

            <!-- Imagen -->
            <div class="form-group">
                <label class="form-label" for="imagen"><?= $editing ? 'Cambiar imagen' : 'Imagen *' ?></label>
                <?php if ($editing && !empty($banner['imagen'])): ?>
                    <div style="margin-bottom:0.5rem">
                        <img src="<?= asset('img/banners/' . $banner['imagen']) ?>"
                             alt="Imagen actual"
                             loading="lazy"
                             style="max-width:100%;max-height:200px;border-radius:6px;border:1px solid var(--color-border)">
                        <p style="margin:0.25rem 0 0; font-size:0.8rem; color:var(--color-gray);">Imagen actual</p>
                    </div>
                <?php endif; ?>
                <input type="file"
                       id="imagen"
                       name="imagen"
                       class="form-control"
                       accept="image/*"
                       <?= !$editing ? 'required' : '' ?>>
                <small style="color:var(--color-gray)">Formatos: JPG, PNG, WebP. Max 2MB.</small>
            </div>

            <!-- Preview de imagen nueva con validacion de dimensiones -->
            <div id="imagen-preview" style="display:none; margin-top:0.5rem; margin-bottom:1rem;">
                <img src="" alt="Preview" loading="lazy" style="max-width:100%;max-height:250px;border-radius:6px;border:1px solid var(--color-border)">
                <div id="imagen-size-info" style="margin-top:0.35rem; font-size:0.8rem; padding:0.35rem 0.75rem; border-radius:4px; display:inline-block;"></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="posicion">Página de destino</label>
                    <select id="posicion" name="posicion" class="form-control">
                        <option value="" <?= old('posicion', $banner['posicion'] ?? '') === '' ? 'selected' : '' ?>>Todas las páginas</option>
                        <option value="home" <?= old('posicion', $banner['posicion'] ?? '') === 'home' ? 'selected' : '' ?>>Solo portada</option>
                        <option value="listado" <?= old('posicion', $banner['posicion'] ?? '') === 'listado' ? 'selected' : '' ?>>Listado de comercios</option>
                        <option value="detalle" <?= old('posicion', $banner['posicion'] ?? '') === 'detalle' ? 'selected' : '' ?>>Detalle de comercio</option>
                        <option value="categoria" <?= old('posicion', $banner['posicion'] ?? '') === 'categoria' ? 'selected' : '' ?>>Páginas de categoria</option>
                        <option value="noticias" <?= old('posicion', $banner['posicion'] ?? '') === 'noticias' ? 'selected' : '' ?>>Noticias</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="comercio_id">Comercio asociado</label>
                    <select id="comercio_id" name="comercio_id" class="form-control">
                        <option value="">— Ninguno (banner propio) —</option>
                        <?php foreach ($comercios as $com): ?>
                            <option value="<?= $com['id'] ?>"
                                    <?= (int) old('comercio_id', $banner['comercio_id'] ?? '') === $com['id'] ? 'selected' : '' ?>>
                                <?= e($com['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="fecha_inicio">Fecha inicio</label>
                    <input type="date"
                           id="fecha_inicio"
                           name="fecha_inicio"
                           class="form-control"
                           value="<?= e(old('fecha_inicio', $banner['fecha_inicio'] ?? '')) ?>">
                    <small style="color:var(--color-gray)">Dejar vacio = siempre visible</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="fecha_fin">Fecha fin</label>
                    <input type="date"
                           id="fecha_fin"
                           name="fecha_fin"
                           class="form-control"
                           value="<?= e(old('fecha_fin', $banner['fecha_fin'] ?? '')) ?>">
                    <small style="color:var(--color-gray)">Dejar vacio = sin expiracion</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="orden">Orden</label>
                    <input type="number"
                           id="orden"
                           name="orden"
                           class="form-control"
                           value="<?= e(old('orden', $banner['orden'] ?? 0)) ?>"
                           min="0">
                    <small style="color:var(--color-gray)">Menor numero = aparece primero</small>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex;align-items:center;gap:0.5rem;margin-top:1.75rem">
                        <input type="checkbox"
                               name="activo"
                               value="1"
                               <?= old('activo', $banner['activo'] ?? 1) ? 'checked' : '' ?>>
                        Activo
                    </label>
                </div>
            </div>

            <?php if ($editing && isset($banner['clicks'])): ?>
            <div style="margin-top:1rem; padding:0.75rem 1rem; background:var(--color-bg-alt, #f8fafc); border-radius:6px; display:flex; gap:2rem; font-size:0.85rem; color:var(--color-gray, #64748b);">
                <span>📊 Impresiones: <strong style="color:var(--color-text, #1e293b)"><?= number_format($banner['impresiones']) ?></strong></span>
                <span>🖱️ Clicks: <strong style="color:var(--color-text, #1e293b)"><?= number_format($banner['clicks']) ?></strong></span>
                <?php if ($banner['impresiones'] > 0): ?>
                    <span>📈 CTR: <strong style="color:var(--color-text, #1e293b)"><?= number_format(($banner['clicks'] / $banner['impresiones']) * 100, 2) ?>%</strong></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Botones -->
    <div class="toolbar" style="margin-bottom:0">
        <button type="submit" class="btn btn--primary"><?= $editing ? 'Guardar cambios' : 'Crear banner' ?></button>
        <a href="<?= url('/admin/banners') ?>" class="btn btn--outline">Cancelar</a>
    </div>
</form>

<script>
(function() {
    // Especificaciones de banners por tipo
    var specs = <?= json_encode($bannerSpecs) ?>;

    var tipoSelect = document.getElementById('tipo');
    var specInfo = document.getElementById('banner-spec-info');
    var specTitle = document.getElementById('spec-title');
    var specDesc = document.getElementById('spec-desc');
    var specNota = document.getElementById('spec-nota');
    var specBox = document.getElementById('spec-preview-box');
    var specDims = document.getElementById('spec-dimensions');

    // Escala para la cajita de preview (max 200px de ancho)
    var maxPreviewW = 200;

    function updateSpec() {
        var tipo = tipoSelect.value;
        if (!tipo || !specs[tipo]) {
            specInfo.style.display = 'none';
            return;
        }
        var s = specs[tipo];
        specInfo.style.display = 'block';
        specTitle.textContent = '📐 Dimension recomendada: ' + s.ancho + ' × ' + s.alto + ' px (' + s.ratio + ')';
        specDesc.textContent = s.desc;
        specNota.textContent = '💡 ' + s.nota;

        // Cajita proporcional
        var scale = maxPreviewW / s.ancho;
        var boxW = Math.round(s.ancho * scale);
        var boxH = Math.max(Math.round(s.alto * scale), 20);
        specBox.style.width = boxW + 'px';
        specBox.style.height = boxH + 'px';
        specDims.textContent = s.ancho + '×' + s.alto;
    }

    tipoSelect.addEventListener('change', updateSpec);
    updateSpec(); // ejecutar al cargar si ya tiene valor

    // Preview de imagen con validacion de dimensiones
    var fileInput = document.getElementById('imagen');
    var previewContainer = document.getElementById('imagen-preview');
    var previewImg = previewContainer.querySelector('img');
    var sizeInfo = document.getElementById('imagen-size-info');

    fileInput.addEventListener('change', function() {
        if (!this.files || !this.files[0]) {
            previewContainer.style.display = 'none';
            return;
        }

        var file = this.files[0];
        var reader = new FileReader();
        reader.onload = function(ev) {
            previewImg.src = ev.target.result;
            previewContainer.style.display = 'block';

            // Obtener dimensiones reales de la imagen
            var img = new Image();
            img.onload = function() {
                var w = img.width;
                var h = img.height;
                var tipo = tipoSelect.value;
                var msg = 'Imagen: ' + w + ' × ' + h + ' px';

                if (tipo && specs[tipo]) {
                    var recW = specs[tipo].ancho;
                    var recH = specs[tipo].alto;
                    var ratioOk = Math.abs((w/h) - (recW/recH)) < 0.3;

                    if (w === recW && h === recH) {
                        msg += ' ✅ Dimensiones perfectas';
                        sizeInfo.style.background = '#dcfce7';
                        sizeInfo.style.color = '#166534';
                    } else if (w >= recW && h >= recH && ratioOk) {
                        msg += ' ✅ Buena — se ajustara automaticamente';
                        sizeInfo.style.background = '#dcfce7';
                        sizeInfo.style.color = '#166534';
                    } else if (ratioOk) {
                        msg += ' ⚠️ Proporcion correcta pero resolucion baja (recomendado: ' + recW + '×' + recH + ')';
                        sizeInfo.style.background = '#fef9c3';
                        sizeInfo.style.color = '#854d0e';
                    } else {
                        msg += ' ⚠️ Proporcion diferente a la recomendada (' + recW + '×' + recH + '). Puede verse cortada o distorsionada.';
                        sizeInfo.style.background = '#fee2e2';
                        sizeInfo.style.color = '#991b1b';
                    }
                }

                sizeInfo.textContent = msg;
                sizeInfo.style.display = 'inline-block';
            };
            img.src = ev.target.result;
        };
        reader.readAsDataURL(file);
    });
})();
</script>
