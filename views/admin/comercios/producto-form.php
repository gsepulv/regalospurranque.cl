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
<p style="color:var(--color-gray);font-size:var(--font-size-sm);margin-bottom:0.5rem">Comercio: <?= e($comercio['nombre']) ?></p>
<?php
$_restantes = $maxProductos - $totalProductos;
$_color = $_restantes <= 0 ? 'var(--color-danger, #dc2626)' : ($_restantes === 1 ? '#d97706' : 'var(--color-gray)');
?>
<p style="font-size:var(--font-size-sm);margin-bottom:1.25rem;color:<?= $_color ?>">
    &#128230; <?= $totalProductos ?> de <?= $maxProductos ?> productos utilizados (Plan: <?= e($plan['nombre'] ?? 'Freemium') ?>)
</p>

<div class="admin-card">
    <div class="admin-card__body">
        <form method="POST"
              action="<?= url($esEdicion ? '/admin/comercios/' . $comercio['id'] . '/productos/actualizar/' . $producto['id'] : '/admin/comercios/' . $comercio['id'] . '/productos/guardar') ?>"
              enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="tipo">Tipo de publicaci&oacute;n <span style="color:var(--color-danger)">*</span></label>
                <select id="tipo" name="tipo" class="form-control" onchange="toggleCamposTipo(this.value)">
                    <option value="producto" <?= ($producto['tipo'] ?? 'producto') === 'producto' ? 'selected' : '' ?>>&#128230; Producto</option>
                    <option value="servicio" <?= ($producto['tipo'] ?? '') === 'servicio' ? 'selected' : '' ?>>&#128295; Servicio</option>
                    <option value="arriendo" <?= ($producto['tipo'] ?? '') === 'arriendo' ? 'selected' : '' ?>>&#127968; Arriendo</option>
                    <option value="propiedad" <?= ($producto['tipo'] ?? '') === 'propiedad' ? 'selected' : '' ?>>&#127969; Propiedad</option>
                </select>
            </div>

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
                <label class="form-label" for="descripcion_detallada">Descripci&oacute;n detallada</label>
                <textarea id="descripcion_detallada" name="descripcion_detallada" class="form-control"
                          maxlength="2000" rows="5"
                          placeholder="Describe en detalle: caracter&iacute;sticas, medidas, colores, materiales, condiciones..."><?= e($producto['descripcion_detallada'] ?? '') ?></textarea>
                <small style="color:var(--color-gray)"><span id="descDetCount"><?= mb_strlen($producto['descripcion_detallada'] ?? '') ?></span> / 2000</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="precio" id="precioLabel">Precio (CLP)</label>
                <input type="number" id="precio" name="precio" class="form-control"
                       value="<?= $producto['precio'] ?? '' ?>"
                       min="0" step="1" placeholder="Ej: 15990">
                <small style="color:var(--color-gray)">Dejar vacio si no desea mostrar precio</small>
            </div>

            <div class="form-group" id="stockGroup">
                <label class="form-label" for="stock">Unidades disponibles (opcional)</label>
                <input type="number" id="stock" name="stock" class="form-control"
                       value="<?= $producto['stock'] ?? '' ?>"
                       min="0" step="1" placeholder="Ej: 10">
                <small style="color:var(--color-gray)">Deja vac&iacute;o si no maneja stock</small>
            </div>

            <div class="form-group" id="condicionGroup">
                <label class="form-label" for="condicion">Condici&oacute;n</label>
                <select id="condicion" name="condicion" class="form-control">
                    <option value="">&#8212; Sin especificar &#8212;</option>
                    <option value="nuevo" <?= ($producto['condicion'] ?? '') === 'nuevo' ? 'selected' : '' ?>>Nuevo</option>
                    <option value="usado" <?= ($producto['condicion'] ?? '') === 'usado' ? 'selected' : '' ?>>Usado</option>
                    <option value="reacondicionado" <?= ($producto['condicion'] ?? '') === 'reacondicionado' ? 'selected' : '' ?>>Reacondicionado</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="estado">Estado <span style="color:var(--color-danger)">*</span></label>
                <select id="estado" name="estado" class="form-control">
                    <option value="disponible" <?= ($producto['estado'] ?? 'disponible') === 'disponible' ? 'selected' : '' ?>>&#9989; Disponible</option>
                    <option value="vendido" <?= ($producto['estado'] ?? '') === 'vendido' ? 'selected' : '' ?>>&#128308; Vendido</option>
                    <option value="reservado" <?= ($producto['estado'] ?? '') === 'reservado' ? 'selected' : '' ?>>&#128993; Reservado</option>
                    <option value="agotado" <?= ($producto['estado'] ?? '') === 'agotado' ? 'selected' : '' ?>>&#9899; Agotado</option>
                </select>
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
                <label class="form-label">Segunda imagen (opcional)</label>
                <?php if ($esEdicion && !empty($producto['imagen2'])): ?>
                    <div style="margin-bottom:0.5rem">
                        <img src="<?= asset('img/productos/' . $comercio['id'] . '/' . $producto['imagen2']) ?>"
                             alt="Segunda imagen"
                             style="max-width:200px;max-height:200px;border-radius:var(--radius-md);object-fit:cover">
                        <div style="margin-top:0.35rem">
                            <label style="font-size:var(--font-size-sm);cursor:pointer">
                                <input type="checkbox" name="eliminar_imagen2" value="1"> Eliminar segunda imagen
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="file" name="imagen2" class="form-control" accept=".jpg,.jpeg,.png,.webp" id="prodImagen2">
                <small style="color:var(--color-gray)">JPG, PNG o WebP. Max 2 MB.</small>
                <div id="imgPreview2" style="margin-top:0.5rem;display:none">
                    <img id="previewImg2" src="" alt="Preview" style="max-width:200px;max-height:200px;border-radius:var(--radius-md);object-fit:cover">
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

    // Toggle campos segun tipo
    function toggleCamposTipo(tipo) {
        var stockG = document.getElementById('stockGroup');
        var condG = document.getElementById('condicionGroup');
        var precioL = document.getElementById('precioLabel');
        if (stockG) stockG.style.display = (tipo === 'producto') ? '' : 'none';
        if (condG) condG.style.display = (tipo === 'producto') ? '' : 'none';
        if (precioL) {
            if (tipo === 'arriendo') precioL.textContent = 'Precio mensual (CLP)';
            else if (tipo === 'propiedad') precioL.textContent = 'Precio de venta (CLP)';
            else precioL.textContent = 'Precio (CLP)';
        }
    }
    toggleCamposTipo(document.getElementById('tipo').value);

    // Desc detallada counter
    var descDet = document.getElementById('descripcion_detallada');
    var descDetC = document.getElementById('descDetCount');
    if (descDet && descDetC) {
        descDet.addEventListener('input', function() { descDetC.textContent = this.value.length; });
    }

    // Imagen2 preview
    var fi2 = document.getElementById('prodImagen2');
    var pv2 = document.getElementById('imgPreview2');
    var pi2 = document.getElementById('previewImg2');
    if (fi2 && pv2 && pi2) {
        fi2.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                if (this.files[0].size > 2*1024*1024) { alert('La imagen no puede superar los 2 MB.'); this.value=''; pv2.style.display='none'; return; }
                var r = new FileReader();
                r.onload = function(e) { pi2.src=e.target.result; pv2.style.display='block'; };
                r.readAsDataURL(this.files[0]);
            }
        });
    }
</script>
