<?php
/**
 * Admin - Formulario de noticia (crear / editar) con TinyMCE
 * Variables: $categorias, $fechas, optionally $noticia, $catIds, $fechaIds
 */
$editing  = isset($noticia);
$catIds   = $catIds ?? [];
$fechaIds = $fechaIds ?? [];

// TinyMCE config desde admin
$tinymceApiKey     = \App\Services\RedesSociales::get('tinymce_api_key', '');
$tinymceHeight     = \App\Services\RedesSociales::get('tinymce_height', '500');
$tinymceLanguage   = \App\Services\RedesSociales::get('tinymce_language', 'es');
$tinymceAutosave   = \App\Services\RedesSociales::get('tinymce_autosave', '1');
$tinymceMaxImgMb   = \App\Services\RedesSociales::get('tinymce_max_image_mb', '3');
$tinymceMaxImgW    = \App\Services\RedesSociales::get('tinymce_max_image_width', '1200');
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/noticias') ?>">Noticias</a> &rsaquo;
    <span><?= $editing ? 'Editar noticia' : 'Nueva noticia' ?></span>
</div>

<h2><?= $editing ? 'Editar noticia' : 'Nueva noticia' ?></h2>

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
      action="<?= $editing ? url('/admin/noticias/update/' . $noticia['id']) : url('/admin/noticias/store') ?>"
      enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- 1. Información básica -->
    <div class="admin-card mb-3">
        <div class="admin-card__header">
            <h3>Información básica</h3>
        </div>
        <div class="admin-card__body">
            <div class="form-group">
                <label class="form-label" for="titulo">Titulo *</label>
                <input type="text"
                       id="titulo"
                       name="titulo"
                       class="form-control"
                       value="<?= e(old('titulo', $noticia['titulo'] ?? '')) ?>"
                       data-slug-source
                       required>
            </div>

            <div class="form-group">
                <label class="form-label" for="slug">Slug *</label>
                <input type="text"
                       id="slug"
                       name="slug"
                       class="form-control"
                       value="<?= e(old('slug', $noticia['slug'] ?? '')) ?>"
                       data-slug-target
                       required>
                <small style="color:var(--color-gray)">Se genera automaticamente desde el titulo.</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="contenido">Contenido</label>
                <textarea id="contenido"
                          name="contenido"
                          class="form-control tinymce-editor"
                          rows="15"><?= old('contenido', $noticia['contenido'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="extracto">Extracto</label>
                <textarea id="extracto"
                          name="extracto"
                          class="form-control"
                          rows="3"
                          placeholder="Resumen breve para listados y SEO"><?= e(old('extracto', $noticia['extracto'] ?? '')) ?></textarea>
            </div>
        </div>
    </div>

    <!-- 2. Imagen -->
    <div class="admin-card mb-3">
        <div class="admin-card__header">
            <h3>Imagen</h3>
        </div>
        <div class="admin-card__body">
            <?php if ($editing && !empty($noticia['imagen'])): ?>
                <div class="form-group">
                    <label class="form-label">Imagen actual</label>
                    <div>
                        <img src="<?= asset('img/noticias/' . $noticia['imagen']) ?>"
                             alt="Imagen actual"
                             style="max-width:300px;max-height:200px;border-radius:6px;border:1px solid var(--color-border)">
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label" for="imagen"><?= $editing ? 'Cambiar imagen' : 'Imagen' ?></label>
                <input type="file"
                       id="imagen"
                       name="imagen"
                       class="form-control"
                       accept="image/*">
                <small style="color:var(--color-gray)">Formatos: JPG, PNG, WebP. Max 2MB.</small>
            </div>

            <div id="imagen-preview" style="display:none;margin-top:0.5rem">
                <img src="" alt="Preview" style="max-width:300px;max-height:200px;border-radius:6px;border:1px solid var(--color-border)">
            </div>
        </div>
    </div>

    <!-- 3. Metadata -->
    <div class="admin-card mb-3">
        <div class="admin-card__header">
            <h3>Metadata</h3>
        </div>
        <div class="admin-card__body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="autor">Autor</label>
                    <input type="text"
                           id="autor"
                           name="autor"
                           class="form-control"
                           value="<?= e(old('autor', $noticia['autor'] ?? '')) ?>"
                           placeholder="Nombre del autor">
                </div>
                <div class="form-group">
                    <label class="form-label" for="fecha_publicacion">Fecha de publicacion</label>
                    <input type="datetime-local"
                           id="fecha_publicacion"
                           name="fecha_publicacion"
                           class="form-control"
                           value="<?= e(old('fecha_publicacion', !empty($noticia['fecha_publicacion']) ? date('Y-m-d\TH:i', strtotime($noticia['fecha_publicacion'])) : '')) ?>">
                </div>
            </div>

            <div class="form-row" style="margin-top:1rem">
                <div class="form-group">
                    <label class="form-label" style="display:flex;align-items:center;gap:0.5rem">
                        <input type="checkbox"
                               name="activo"
                               value="1"
                               <?= old('activo', $noticia['activo'] ?? 1) ? 'checked' : '' ?>>
                        Activa
                    </label>
                    <small style="color:var(--color-gray)">La noticia sera visible en el sitio publico.</small>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex;align-items:center;gap:0.5rem">
                        <input type="checkbox"
                               name="destacada"
                               value="1"
                               <?= old('destacada', $noticia['destacada'] ?? 0) ? 'checked' : '' ?>>
                        Destacada
                    </label>
                    <small style="color:var(--color-gray)">Se mostrara en posiciones destacadas.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Categorías -->
    <div class="admin-card mb-3">
        <div class="admin-card__header">
            <h3>Categorías</h3>
        </div>
        <div class="admin-card__body">
            <?php if (empty($categorias)): ?>
                <p style="color:var(--color-gray)">No hay categorias disponibles.</p>
            <?php else: ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:0.5rem">
                    <?php foreach ($categorias as $cat): ?>
                        <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;cursor:pointer">
                            <input type="checkbox"
                                   name="categorias[]"
                                   value="<?= $cat['id'] ?>"
                                   <?= in_array($cat['id'], $catIds) ? 'checked' : '' ?>>
                            <?php if (!empty($cat['icono'])): ?>
                                <span><?= $cat['icono'] ?></span>
                            <?php endif; ?>
                            <?= e($cat['nombre']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 5. Fechas especiales -->
    <div class="admin-card mb-3">
        <div class="admin-card__header">
            <h3>Fechas especiales</h3>
        </div>
        <div class="admin-card__body">
            <?php if (empty($fechas)): ?>
                <p style="color:var(--color-gray)">No hay fechas especiales disponibles.</p>
            <?php else: ?>
                <?php
                $fechasPorTipo = [];
                foreach ($fechas as $f) {
                    $fechasPorTipo[$f['tipo']][] = $f;
                }
                ?>
                <?php foreach ($fechasPorTipo as $tipo => $items): ?>
                    <div style="margin-bottom:1rem">
                        <strong style="display:block;margin-bottom:0.5rem;text-transform:capitalize;font-size:0.875rem;color:var(--color-dark-soft)">
                            <?= e(ucfirst($tipo)) ?>
                        </strong>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:0.5rem">
                            <?php foreach ($items as $f): ?>
                                <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;cursor:pointer">
                                    <input type="checkbox"
                                           name="fechas[]"
                                           value="<?= $f['id'] ?>"
                                           <?= in_array($f['id'], $fechaIds) ? 'checked' : '' ?>>
                                    <?php if (!empty($f['icono'])): ?>
                                        <span><?= $f['icono'] ?></span>
                                    <?php endif; ?>
                                    <?= e($f['nombre']) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- 6. SEO -->
    <div class="admin-card mb-3">
        <div class="admin-card__header">
            <h3>SEO</h3>
        </div>
        <div class="admin-card__body">
            <div class="form-group">
                <label class="form-label" for="seo_titulo">Titulo SEO</label>
                <input type="text"
                       id="seo_titulo"
                       name="seo_titulo"
                       class="form-control"
                       value="<?= e(old('seo_titulo', $noticia['seo_titulo'] ?? '')) ?>"
                       placeholder="Titulo para motores de búsqueda"
                       maxlength="70">
                <small style="color:var(--color-gray)">Maximo 70 caracteres. Si se deja vacio se usa el titulo de la noticia.</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="seo_descripcion">Descripción SEO</label>
                <textarea id="seo_descripcion"
                          name="seo_descripcion"
                          class="form-control"
                          rows="2"
                          placeholder="Descripción para motores de búsqueda"
                          maxlength="160"><?= e(old('seo_descripcion', $noticia['seo_descripcion'] ?? '')) ?></textarea>
                <small style="color:var(--color-gray)">Maximo 160 caracteres.</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="seo_keywords">Keywords</label>
                <input type="text"
                       id="seo_keywords"
                       name="seo_keywords"
                       class="form-control"
                       value="<?= e(old('seo_keywords', $noticia['seo_keywords'] ?? '')) ?>"
                       placeholder="palabra1, palabra2, palabra3">
            </div>

            <div class="form-group">
                <label class="form-label" for="seo_imagen_og">Imagen Open Graph</label>
                <?php if ($editing && !empty($noticia['seo_imagen_og'])): ?>
                    <div style="margin-bottom:0.5rem">
                        <img src="<?= asset('img/og/' . $noticia['seo_imagen_og']) ?>"
                             alt="OG Image actual"
                             style="max-width:200px;max-height:120px;border-radius:4px;border:1px solid var(--color-border)">
                    </div>
                <?php endif; ?>
                <input type="file"
                       id="seo_imagen_og"
                       name="seo_imagen_og"
                       class="form-control"
                       accept="image/*">
                <small style="color:var(--color-gray)">Imagen recomendada: 1200x630px.</small>
            </div>

            <div class="form-group">
                <label class="form-label" style="display:flex;align-items:center;gap:0.5rem">
                    <input type="checkbox"
                           name="seo_noindex"
                           value="1"
                           <?= old('seo_noindex', $noticia['seo_noindex'] ?? 0) ? 'checked' : '' ?>>
                    No indexar (noindex)
                </label>
                <small style="color:var(--color-gray)">Impide que los motores de búsqueda indexen esta noticia.</small>
            </div>
        </div>
    </div>

    <!-- Botones -->
    <div class="toolbar" style="margin-bottom:0">
        <button type="submit" class="btn btn--primary"><?= $editing ? 'Guardar cambios' : 'Crear noticia' ?></button>
        <a href="<?= url('/admin/noticias') ?>" class="btn btn--outline">Cancelar</a>
    </div>
</form>

<!-- TinyMCE 6 self-hosted (MIT) -->
<script src="<?= asset('vendor/tinymce/tinymce.min.js') ?>"></script>

<script>
// TinyMCE initialization
tinymce.init({
    selector: '.tinymce-editor',
    height: <?= (int)$tinymceHeight ?>,
    language: '<?= e($tinymceLanguage) ?>',
    language_url: '<?= asset('vendor/tinymce/langs/' . e($tinymceLanguage) . '.js') ?>',
    plugins: 'advlist autolink lists link image charmap anchor searchreplace visualblocks code fullscreen insertdatetime media table wordcount emoticons autoresize quickbars help',
    toolbar: [
        'undo redo | styles | bold italic underline strikethrough | forecolor backcolor | removeformat',
        'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | table emoticons charmap | code fullscreen help'
    ],
    style_formats: [
        { title: 'Encabezados', items: [
            { title: 'Encabezado 2', block: 'h2' },
            { title: 'Encabezado 3', block: 'h3' },
            { title: 'Encabezado 4', block: 'h4' }
        ]},
        { title: 'Bloques', items: [
            { title: 'Parrafo', block: 'p' },
            { title: 'Cita', block: 'blockquote' },
            { title: 'Código', block: 'pre' }
        ]},
        { title: 'Inline', items: [
            { title: 'Destacado', inline: 'span', classes: 'text-highlight' },
            { title: 'Código', inline: 'code' },
            { title: 'Pequeño', inline: 'span', classes: 'text-small' }
        ]}
    ],
    content_css: '<?= asset('css/main.css') ?>',
    content_style: 'body { font-family: system-ui, -apple-system, sans-serif; font-size: 16px; line-height: 1.8; padding: 16px; color: #334155; }',
    image_class_list: [
        { title: 'Responsive', value: 'img-responsive' },
        { title: 'Centrada', value: 'img-center' },
        { title: 'Flotante izquierda', value: 'img-left' },
        { title: 'Flotante derecha', value: 'img-right' }
    ],
    images_upload_url: '<?= url('/admin/noticias/upload-imagen') ?>',
    images_upload_handler: function (blobInfo) {
        return new Promise(function (resolve, reject) {
            var maxMb = <?= (int)$tinymceMaxImgMb ?>;
            if (blobInfo.blob().size > maxMb * 1024 * 1024) {
                reject('La imagen excede ' + maxMb + 'MB');
                return;
            }

            var formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            formData.append('_csrf', '<?= csrf_token() ?>');

            fetch('<?= url('/admin/noticias/upload-imagen') ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(resp) { return resp.json(); })
            .then(function(data) {
                if (data.location) {
                    resolve(data.location);
                } else {
                    reject(data.error || 'Error al subir imagen');
                }
            })
            .catch(function() {
                reject('Error de conexion al subir imagen');
            });
        });
    },
    image_dimensions: false,
    paste_as_text: false,
    paste_word_valid_elements: 'p,b,strong,i,em,h2,h3,h4,ul,ol,li,a[href],blockquote,br',
    paste_retain_style_properties: 'none',
    paste_strip_class_attributes: 'all',
    automatic_uploads: true,
    file_picker_types: 'image',
    image_advtab: true,
    image_caption: true,
    quickbars_selection_toolbar: 'bold italic | link h2 h3 blockquote',
    quickbars_insert_toolbar: 'image media table hr',
    autoresize_bottom_margin: 20,
    min_height: 300,
    max_height: 800,
    <?php if ($tinymceAutosave === '1'): ?>
    autosave_interval: '<?= (int)(\App\Services\RedesSociales::get('tinymce_autosave_interval', '30')) ?>s',
    autosave_restore_when_empty: true,
    <?php endif; ?>
    setup: function (editor) {
        editor.on('NodeChange', function (e) {
            var imgs = editor.getBody().querySelectorAll('img:not([loading])');
            imgs.forEach(function(img) {
                img.setAttribute('loading', 'lazy');
            });
        });
    },
    promotion: false,
    branding: false
});

// Preview de imagen al seleccionar archivo
document.getElementById('imagen').addEventListener('change', function(e) {
    var preview = document.getElementById('imagen-preview');
    var img = preview.querySelector('img');
    if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function(ev) {
            img.src = ev.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(this.files[0]);
    } else {
        preview.style.display = 'none';
    }
});
</script>
