<?php $editing = isset($categoria); ?>

<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a>
    <span>/</span>
    <a href="<?= url('/admin/categorias') ?>">Categorías</a>
    <span>/</span>
    <span><?= $editing ? 'Editar' : 'Crear' ?></span>
</div>

<h2><?= $editing ? 'Editar categoria' : 'Nueva categoria' ?></h2>

<?php if (!empty($flash['errors'])): ?>
    <div class="toast toast--error toast--inline" role="alert">
        <div>
            <strong>Se encontraron errores:</strong>
            <ul style="margin:0.5rem 0 0;padding-left:1.25rem">
                <?php foreach ($flash['errors'] as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<form action="<?= $editing ? url('/admin/categorias/update/' . $categoria['id']) : url('/admin/categorias/store') ?>"
      method="POST"
      enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="admin-card">
        <div class="admin-card__header">
            <h3 class="admin-card__title">Datos de la categoria</h3>
        </div>
        <div class="admin-card__body">

            <!-- Nombre y Slug -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input type="text"
                           id="nombre"
                           name="nombre"
                           class="form-control"
                           value="<?= e(old('nombre', $editing ? $categoria['nombre'] : '')) ?>"
                           data-slug-source
                           minlength="3"
                           maxlength="100"
                           required>
                    <small style="color:var(--color-gray)">Min. 3, max. 100 caracteres.</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="slug">Slug</label>
                    <input type="text"
                           id="slug"
                           name="slug"
                           class="form-control"
                           value="<?= e(old('slug', $editing ? $categoria['slug'] : '')) ?>"
                           data-slug-target
                           minlength="3"
                           maxlength="100"
                           required>
                </div>
            </div>

            <!-- Descripción -->
            <div class="form-group">
                <label class="form-label" for="descripcion">Descripción</label>
                <textarea id="descripcion"
                          name="descripcion"
                          class="form-control"
                          rows="3"
                          minlength="10"
                          maxlength="500"
                          required><?= e(old('descripcion', $editing ? $categoria['descripcion'] ?? '' : '')) ?></textarea>
                <small style="color:var(--color-gray)">Min. 10, max. 500 caracteres.</small>
            </div>

            <!-- Icono y Color -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="icono">Icono (emoji)</label>
                    <input type="text"
                           id="icono"
                           name="icono"
                           class="form-control"
                           value="<?= e(old('icono', $editing ? $categoria['icono'] ?? '' : '')) ?>"
                           placeholder="Ej: &#127861;"
                           minlength="1"
                           maxlength="10"
                           required>
                    <small style="color:var(--color-gray)">Min. 1, max. 10 caracteres.</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="color">Color</label>
                    <input type="color"
                           id="color"
                           name="color"
                           class="form-control"
                           value="<?= e(old('color', $editing ? $categoria['color'] ?? '#2563eb' : '#2563eb')) ?>"
                           style="height:42px;padding:4px">
                </div>
            </div>

            <!-- Orden y Activo -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="orden">Orden</label>
                    <input type="number"
                           id="orden"
                           name="orden"
                           class="form-control"
                           value="<?= e(old('orden', $editing ? $categoria['orden'] : ($maxOrden ?? 0) + 1)) ?>"
                           min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Activo</label>
                    <div style="padding-top:0.5rem">
                        <label class="toggle-switch">
                            <input type="checkbox"
                                   name="activo"
                                   value="1"
                                   <?= old('activo', $editing ? $categoria['activo'] : 1) ? 'checked' : '' ?>>
                            <span class="toggle-switch__slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Imagen -->
            <div class="form-group">
                <label class="form-label" for="imagen">Imagen</label>
                <?php if ($editing && !empty($categoria['imagen'])): ?>
                    <div style="margin-bottom:0.75rem">
                        <img src="<?= asset('img/categorias/' . $categoria['imagen']) ?>"
                             alt="<?= e($categoria['nombre']) ?>"
                             id="imagePreview"
                             loading="lazy"
                             style="max-width:200px;max-height:120px;border-radius:var(--radius-md);border:1px solid var(--color-border)">
                    </div>
                <?php else: ?>
                    <img src="" alt="" id="imagePreview" loading="lazy" style="display:none;max-width:200px;max-height:120px;border-radius:var(--radius-md);border:1px solid var(--color-border);margin-bottom:0.75rem">
                <?php endif; ?>
                <input type="file"
                       id="imagen"
                       name="imagen"
                       class="form-control"
                       accept="image/*"
                       onchange="if(this.files[0]){var r=new FileReader();r.onload=function(e){var p=document.getElementById('imagePreview');p.src=e.target.result;p.style.display='block'};r.readAsDataURL(this.files[0])}">
            </div>

        </div>
        <div class="admin-card__footer">
            <div class="toolbar" style="margin-bottom:0">
                <button type="submit" class="btn btn--primary">Guardar categoria</button>
                <a href="<?= url('/admin/categorias') ?>" class="btn btn--outline">Cancelar</a>
            </div>
        </div>
    </div>
</form>
