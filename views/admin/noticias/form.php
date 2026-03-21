<?php
/**
 * Admin - Formulario de noticia (crear / editar) con Quill.js
 * Variables: $categorias, $fechas, optionally $noticia, $catIds, $fechaIds
 */
$editing  = isset($noticia);
$catIds   = $catIds ?? [];
$fechaIds = $fechaIds ?? [];
$errors  = $flash['errors'] ?? [];
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
                       minlength="10"
                       maxlength="200"
                       required>
                <small style="color:var(--color-gray)">Min. 10, max. 200 caracteres.</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="slug">Slug *</label>
                <input type="text"
                       id="slug"
                       name="slug"
                       class="form-control"
                       value="<?= e(old('slug', $noticia['slug'] ?? '')) ?>"
                       data-slug-target
                       minlength="3"
                       maxlength="200"
                       required>
                <small style="color:var(--color-gray)">Se genera automaticamente desde el titulo.</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="contenido">Contenido *</label>
                <!-- Textarea oculto que recibe el valor final para el POST -->
                <textarea id="contenido" name="contenido" style="display:none;"><?= old('contenido', $noticia['contenido'] ?? '') ?></textarea>
                <!-- Contenedor visible del editor Quill -->
                <div id="editor-quill" style="min-height: 300px; background: white;"></div>
                <small style="color:var(--color-gray)">Min. 50 caracteres.</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="extracto">Extracto *</label>
                <textarea id="extracto"
                          name="extracto"
                          class="form-control"
                          rows="3"
                          minlength="20"
                          maxlength="500"
                          placeholder="Resumen breve para listados y SEO"
                          required><?= e(old('extracto', $noticia['extracto'] ?? '')) ?></textarea>
                <small style="color:var(--color-gray)">Min. 20, max. 500 caracteres.</small>
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
                             loading="lazy"
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
                <img src="" alt="Preview" loading="lazy" style="max-width:300px;max-height:200px;border-radius:6px;border:1px solid var(--color-border)">
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
                                <span><?= e($cat['icono']) ?></span>
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
                                        <span><?= e($f['icono']) ?></span>
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
                       minlength="10"
                       maxlength="70"
                       required>
                <small style="color:var(--color-gray)">Min. 10, max. 70 caracteres. Si se deja vacio se usa el titulo de la noticia.</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="seo_descripcion">Descripción SEO</label>
                <textarea id="seo_descripcion"
                          name="seo_descripcion"
                          class="form-control"
                          rows="2"
                          placeholder="Descripción para motores de búsqueda"
                          minlength="50"
                          maxlength="160"
                          required><?= e(old('seo_descripcion', $noticia['seo_descripcion'] ?? '')) ?></textarea>
                <small style="color:var(--color-gray)">Min. 50, max. 160 caracteres.</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="seo_keywords">Keywords</label>
                <input type="text"
                       id="seo_keywords"
                       name="seo_keywords"
                       class="form-control"
                       value="<?= e(old('seo_keywords', $noticia['seo_keywords'] ?? '')) ?>"
                       placeholder="palabra1, palabra2, palabra3"
                       minlength="3"
                       maxlength="255"
                       required>
                <small style="color:var(--color-gray)">Min. 3, max. 255 caracteres.</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="seo_imagen_og">Imagen Open Graph</label>
                <?php if ($editing && !empty($noticia['seo_imagen_og'])): ?>
                    <div style="margin-bottom:0.5rem">
                        <img src="<?= asset('img/og/' . $noticia['seo_imagen_og']) ?>"
                             alt="OG Image actual"
                             loading="lazy"
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

<!-- Quill.js CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.min.css" rel="stylesheet">

<!-- Quill.js JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>

<script>
// Inicializar Quill
var quill = new Quill('#editor-quill', {
    theme: 'snow',
    placeholder: 'Escribe el contenido de la noticia aquí...',
    modules: {
        toolbar: [
            [{ 'header': [2, 3, 4, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link'],
            ['clean']
        ]
    }
});

// Si hay contenido previo (modo editar), cargarlo en Quill
var contenidoInicial = document.getElementById('contenido').value;
if (contenidoInicial) {
    quill.root.innerHTML = contenidoInicial;
}

// Validación y copia del contenido antes de enviar
document.querySelector('form').addEventListener('submit', function(e) {
    // Copiar el HTML de Quill al textarea oculto
    document.getElementById('contenido').value = quill.root.innerHTML;

    // Validar mínimo 50 caracteres
    var text = quill.getText().trim();
    if (text.length < 50) {
        e.preventDefault();
        alert('El contenido debe tener al menos 50 caracteres (actualmente ' + text.length + ')');
        quill.focus();
        return;
    }

    var btn = this.querySelector('button[type="submit"]');
    if (btn) { btn.disabled = true; btn.textContent = 'Guardando...'; }
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
