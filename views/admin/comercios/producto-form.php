<?php
/**
 * Formulario crear/editar producto - Admin
 * Variables: $comercio, $producto (null si nuevo)
 */
$esEdicion = !empty($producto);
?>

<div class="admin-breadcrumb">
    <a href="<?= url('/admin') ?>">Dashboard</a>
    <span>/</span>
    <a href="<?= url('/admin/comercios') ?>">Comercios</a>
    <span>/</span>
    <a href="<?= url('/admin/comercios/editar/' . $comercio['id']) ?>"><?= e($comercio['nombre']) ?></a>
    <span>/</span>
    <a href="<?= url('/admin/comercios/' . $comercio['id'] . '/productos') ?>">Productos</a>
    <span>/</span>
    <span><?= $esEdicion ? 'Editar' : 'Agregar' ?></span>
</div>

<h2 style="margin-bottom:0.25rem"><?= $esEdicion ? 'Editar producto — ' . e($producto['nombre']) : 'Agregar producto' ?></h2>
<p style="color:var(--color-gray);font-size:var(--font-size-sm);margin-bottom:1.25rem">Comercio: <?= e($comercio['nombre']) ?></p>

<div class="admin-card">
    <div class="admin-card__body">
        <form method="POST"
              action="<?= url($esEdicion ? '/admin/comercios/' . $comercio['id'] . '/productos/actualizar/' . $producto['id'] : '/admin/comercios/' . $comercio['id'] . '/productos/guardar') ?>"
              enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="nombre">Nombre del producto <span style="color:var(--color-danger)">*</span></label>
                <input type="text" id="nombre" name="nombre" class="form-control"
                       value="<?= e($producto['nombre'] ?? '') ?>"
                       required maxlength="150" placeholder="Ej: Ramo de rosas rojas">
            </div>

            <div class="form-group">
                <label class="form-label" for="descripcion">Descripcion</label>
                <textarea id="descripcion" name="descripcion" class="form-control"
                          maxlength="500" rows="3"
                          placeholder="Descripcion breve (opcional)"><?= e($producto['descripcion'] ?? '') ?></textarea>
                <small style="color:var(--color-gray)"><span id="descCount"><?= mb_strlen($producto['descripcion'] ?? '') ?></span> / 500</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="precio">Precio (CLP)</label>
                <input type="number" id="precio" name="precio" class="form-control"
                       value="<?= $producto['precio'] ?? '' ?>"
                       min="0" step="1" placeholder="Ej: 15990">
                <small style="color:var(--color-gray)">Dejar vacio si no desea mostrar precio</small>
            </div>

            <div class="form-group">
                <label class="form-label">Imagen</label>
                <?php if ($esEdicion && !empty($producto['imagen'])): ?>
                    <div style="margin-bottom:0.5rem">
                        <img src="<?= asset('img/productos/' . $comercio['id'] . '/' . $producto['imagen']) ?>"
                             alt="<?= e($producto['nombre']) ?>"
                             style="max-width:200px;max-height:200px;border-radius:var(--radius-md);object-fit:cover">
                        <div style="margin-top:0.35rem">
                            <label style="font-size:var(--font-size-sm);cursor:pointer">
                                <input type="checkbox" name="eliminar_imagen" value="1"> Eliminar imagen actual
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="file" name="imagen" class="form-control" accept=".jpg,.jpeg,.png,.webp" id="prodImagen">
                <small style="color:var(--color-gray)">JPG, PNG o WebP. Max 2 MB.</small>
                <div id="imgPreview" style="margin-top:0.5rem;display:none">
                    <img id="previewImg" src="" alt="Preview" style="max-width:200px;max-height:200px;border-radius:var(--radius-md);object-fit:cover">
                </div>
            </div>

            <div class="form-group">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer">
                    <input type="checkbox" name="activo" value="1" <?= ($producto['activo'] ?? 1) ? 'checked' : '' ?>>
                    <span>Producto activo</span>
                </label>
            </div>

            <div style="display:flex;gap:0.75rem;padding-top:0.5rem">
                <button type="submit" class="btn btn--primary"><?= $esEdicion ? 'Guardar cambios' : 'Crear producto' ?></button>
                <a href="<?= url('/admin/comercios/' . $comercio['id'] . '/productos') ?>" class="btn btn--outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    var desc = document.getElementById('descripcion');
    var counter = document.getElementById('descCount');
    if (desc && counter) {
        desc.addEventListener('input', function() { counter.textContent = this.value.length; });
    }
    var fileInput = document.getElementById('prodImagen');
    var preview = document.getElementById('imgPreview');
    var previewImg = document.getElementById('previewImg');
    if (fileInput && preview && previewImg) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                if (this.files[0].size > 2 * 1024 * 1024) {
                    alert('La imagen no puede superar los 2 MB.');
                    this.value = '';
                    preview.style.display = 'none';
                    return;
                }
                var reader = new FileReader();
                reader.onload = function(e) { previewImg.src = e.target.result; preview.style.display = 'block'; };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
})();
</script>
