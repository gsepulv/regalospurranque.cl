<?php
/**
 * Formulario crear/editar producto
 * Variables: $comercio, $producto (null si es nuevo), $errors, $old
 */
$esEdicion = !empty($producto);
$errors = $_SESSION['flash_errors'] ?? [];
$old    = $_SESSION['flash_old'] ?? [];
unset($_SESSION['flash_errors'], $_SESSION['flash_old']);

$nombre      = e($old['nombre'] ?? $producto['nombre'] ?? '');
$descripcion = e($old['descripcion'] ?? $producto['descripcion'] ?? '');
$descripcion_detallada = e($old['descripcion_detallada'] ?? $producto['descripcion_detallada'] ?? '');
$precio      = $old['precio'] ?? $producto['precio'] ?? '';
$activo      = $old['activo'] ?? $producto['activo'] ?? 1;
$tipo        = $old['tipo'] ?? $producto['tipo'] ?? 'producto';
$estado      = $old['estado'] ?? $producto['estado'] ?? 'disponible';
$stock       = $old['stock'] ?? $producto['stock'] ?? '';
$condicion   = $old['condicion'] ?? $producto['condicion'] ?? '';
?>

<section class="section">
    <div class="container" style="max-width:720px">

        <div style="margin-bottom:1.5rem">
            <h1 style="font-size:1.5rem;margin:0"><?= $esEdicion ? 'Editar producto' : 'Nuevo producto' ?></h1>
            <a href="<?= url('/mi-comercio/productos') ?>" style="color:#6B7280;font-size:0.85rem;text-decoration:none">&larr; Volver a mis productos</a>
            <?php if (isset($totalProductos, $maxProductos)):
                $_restantes = $maxProductos - $totalProductos;
                $_color = $_restantes <= 0 ? '#dc2626' : ($_restantes === 1 ? '#d97706' : '#6B7280');
            ?>
                <p style="font-size:0.85rem;margin:0.5rem 0 0;color:<?= $_color ?>">&#128230; <?= $totalProductos ?> de <?= $maxProductos ?> productos utilizados (Plan: <?= e($plan['nombre'] ?? 'Freemium') ?>)</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($errors)): ?>
            <div style="background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <ul style="margin:0;padding-left:1.25rem">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST"
              action="<?= url($esEdicion ? '/mi-comercio/productos/actualizar/' . $producto['id'] : '/mi-comercio/productos/guardar') ?>"
              enctype="multipart/form-data"
              style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08)">

            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Tipo de publicaci&oacute;n *</label>
                <select name="tipo" class="form-control" id="prodTipo" onchange="toggleCamposTipo(this.value)">
                    <option value="producto" <?= $tipo === 'producto' ? 'selected' : '' ?>>&#128230; Producto</option>
                    <option value="servicio" <?= $tipo === 'servicio' ? 'selected' : '' ?>>&#128295; Servicio</option>
                    <option value="arriendo" <?= $tipo === 'arriendo' ? 'selected' : '' ?>>&#127968; Arriendo</option>
                    <option value="propiedad" <?= $tipo === 'propiedad' ? 'selected' : '' ?>>&#127969; Propiedad</option>
                </select>
            </div>

            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Nombre del producto *</label>
                <input type="text" name="nombre" value="<?= $nombre ?>" required maxlength="150"
                       class="form-control" placeholder="Ej: Ramo de rosas rojas">
            </div>

            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Descripción</label>
                <textarea name="descripcion" maxlength="500" rows="3" class="form-control"
                          placeholder="Descripción breve del producto (opcional)"
                          id="prodDescripcion"><?= $descripcion ?></textarea>
                <div style="text-align:right;font-size:0.75rem;color:#9CA3AF;margin-top:0.25rem">
                    <span id="descCount">0</span> / 500
                </div>
            </div>

            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Descripci&oacute;n detallada</label>
                <textarea name="descripcion_detallada" maxlength="2000" rows="5" class="form-control"
                          placeholder="Describe en detalle tu producto o servicio: caracter&iacute;sticas, medidas, colores, materiales, condiciones, etc."
                          id="prodDescDetallada"><?= $descripcion_detallada ?></textarea>
                <div style="text-align:right;font-size:0.75rem;color:#9CA3AF;margin-top:0.25rem">
                    <span id="descDetCount">0</span> / 2000
                </div>
            </div>

            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" id="precioLabel">Precio (CLP)</label>
                <input type="number" name="precio" value="<?= $precio ?>" min="0" step="1"
                       class="form-control" placeholder="Ej: 24990">
                <small style="color:#9CA3AF;font-size:0.75rem">Déjalo en blanco si prefieres no mostrar precio</small>
            </div>

            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Imagen del producto</label>
                <?php if ($esEdicion && !empty($producto['imagen'])): ?>
                    <div style="margin-bottom:0.5rem">
                        <img src="<?= asset('img/productos/' . $comercio['id'] . '/' . $producto['imagen']) ?>"
                             alt="<?= e($producto['nombre']) ?>"
                             style="max-width:200px;max-height:200px;border-radius:8px;object-fit:cover"
                             id="currentImg">
                        <p style="font-size:0.75rem;color:#9CA3AF;margin:0.25rem 0 0">Imagen actual. Sube otra para reemplazarla.</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" id="prodImagen">
                <small style="color:#9CA3AF;font-size:0.75rem">JPG, PNG o WebP. Máximo 2 MB.</small>
                <div id="imgPreview" style="margin-top:0.5rem;display:none">
                    <img id="previewImg" src="" alt="Preview" style="max-width:200px;max-height:200px;border-radius:8px;object-fit:cover">
                </div>
            </div>

            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Segunda imagen (opcional)</label>
                <?php if ($esEdicion && !empty($producto['imagen2'])): ?>
                    <div style="margin-bottom:0.5rem">
                        <img src="<?= asset('img/productos/' . $comercio['id'] . '/' . $producto['imagen2']) ?>"
                             alt="Segunda imagen"
                             style="max-width:200px;max-height:200px;border-radius:8px;object-fit:cover">
                        <p style="font-size:0.75rem;color:#9CA3AF;margin:0.25rem 0 0">Segunda imagen actual.</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="imagen2" accept="image/jpeg,image/png,image/webp" id="prodImagen2">
                <small style="color:#9CA3AF;font-size:0.75rem">JPG, PNG o WebP. M&aacute;ximo 2 MB.</small>
                <div id="imgPreview2" style="margin-top:0.5rem;display:none">
                    <img id="previewImg2" src="" alt="Preview" style="max-width:200px;max-height:200px;border-radius:8px;object-fit:cover">
                </div>
            </div>

            <div style="margin-bottom:1.25rem">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.9rem">
                    <input type="checkbox" name="activo" value="1" <?= $activo ? 'checked' : '' ?>>
                    <span>Producto activo (visible en mi perfil)</span>
                </label>
            </div>

            <div style="display:flex;gap:0.75rem">
                <button type="submit" class="btn btn--primary" style="flex:1">
                    <?= $esEdicion ? 'Guardar cambios' : 'Crear producto' ?>
                </button>
                <a href="<?= url('/mi-comercio/productos') ?>" class="btn btn--outline" style="flex:1;text-align:center">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</section>

<script>
(function() {
    var desc = document.getElementById('prodDescripcion');
    var counter = document.getElementById('descCount');
    if (desc && counter) {
        counter.textContent = desc.value.length;
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
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
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
    toggleCamposTipo(document.getElementById('prodTipo').value);

    // Desc detallada counter
    var descDet = document.getElementById('prodDescDetallada');
    var descDetC = document.getElementById('descDetCount');
    if (descDet && descDetC) {
        descDetC.textContent = descDet.value.length;
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
