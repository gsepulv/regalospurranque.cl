<?php $editing = isset($fecha); ?>

<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a>
    <span>/</span>
    <a href="<?= url('/admin/fechas') ?>">Fechas Especiales</a>
    <span>/</span>
    <span><?= $editing ? 'Editar' : 'Crear' ?></span>
</div>

<h2><?= $editing ? 'Editar fecha especial' : 'Nueva fecha especial' ?></h2>

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

<form action="<?= $editing ? url('/admin/fechas/update/' . $fecha['id']) : url('/admin/fechas/store') ?>"
      method="POST"
      enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="admin-card">
        <div class="admin-card__header">
            <h3 class="admin-card__title">Datos de la fecha especial</h3>
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
                           value="<?= e(old('nombre', $editing ? $fecha['nombre'] : '')) ?>"
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
                           value="<?= e(old('slug', $editing ? $fecha['slug'] : '')) ?>"
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
                          required><?= e(old('descripcion', $editing ? $fecha['descripcion'] ?? '' : '')) ?></textarea>
                <small style="color:var(--color-gray)">Min. 10, max. 500 caracteres.</small>
            </div>

            <!-- Tipo e Icono -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" class="form-control" required>
                        <?php
                        $tipoActual = old('tipo', $editing ? $fecha['tipo'] ?? '' : '');
                        $tipos = ['personal' => 'Personal', 'calendario' => 'Calendario', 'comercial' => 'Comercial'];
                        ?>
                        <option value="">Seleccionar tipo</option>
                        <?php foreach ($tipos as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $tipoActual === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="icono">Icono (emoji)</label>
                    <input type="text"
                           id="icono"
                           name="icono"
                           class="form-control"
                           value="<?= e(old('icono', $editing ? $fecha['icono'] ?? '' : '')) ?>"
                           minlength="1"
                           maxlength="10"
                           placeholder="Ej: &#127873;">
                    <small style="color:var(--color-gray)">Min. 1, max. 10 caracteres.</small>
                </div>
            </div>

            <!-- Colores: fondo y texto -->
            <?php
            $colorFondo = e(old('color', $editing ? $fecha['color'] ?? '#e53e3e' : '#e53e3e'));
            $colorTexto = e(old('color_texto', $editing ? $fecha['color_texto'] ?? '#ffffff' : '#ffffff'));
            ?>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="color">Color de fondo</label>
                    <div style="display:flex;gap:0.75rem;align-items:center;">
                        <input type="color" id="color" name="color"
                               value="<?= $colorFondo ?>"
                               style="width:50px;height:40px;border:1px solid var(--color-border);border-radius:var(--radius-md);cursor:pointer;padding:2px;">
                        <input type="text" id="color_hex" value="<?= $colorFondo ?>"
                               class="form-control"
                               style="max-width:120px;font-family:var(--font-mono);font-size:var(--font-size-sm);"
                               pattern="#[0-9a-fA-F]{6}" placeholder="#e53e3e">
                    </div>
                    <small class="text-muted" style="margin-top:0.25rem;display:block;">Fondo del hero y borde de tarjetas</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="color_texto">Color del texto</label>
                    <div style="display:flex;gap:0.75rem;align-items:center;">
                        <input type="color" id="color_texto" name="color_texto"
                               value="<?= $colorTexto ?>"
                               style="width:50px;height:40px;border:1px solid var(--color-border);border-radius:var(--radius-md);cursor:pointer;padding:2px;">
                        <input type="text" id="color_texto_hex" value="<?= $colorTexto ?>"
                               class="form-control"
                               style="max-width:120px;font-family:var(--font-mono);font-size:var(--font-size-sm);"
                               pattern="#[0-9a-fA-F]{6}" placeholder="#ffffff">
                    </div>
                    <small class="text-muted" style="margin-top:0.25rem;display:block;">Texto sobre el fondo del hero</small>
                </div>
            </div>

            <!-- Preview de colores -->
            <div class="form-group">
                <label class="form-label">Vista previa del hero</label>
                <div id="colorPreview"
                     style="background:<?= $colorFondo ?>;color:<?= $colorTexto ?>;padding:1.5rem;border-radius:var(--radius-lg);text-align:center;transition:all 0.2s ease;">
                    <div style="font-size:2rem;margin-bottom:0.5rem;" id="previewIcono"><?= e(old('icono', $editing ? $fecha['icono'] ?? '🎁' : '🎁')) ?></div>
                    <div style="font-size:1.25rem;font-weight:700;" id="previewNombre"><?= e(old('nombre', $editing ? $fecha['nombre'] : 'Nombre de la celebración')) ?></div>
                    <div style="opacity:0.85;margin-top:0.25rem;font-size:0.875rem;">Así se verá en la página principal</div>
                </div>
            </div>

            <!-- Fecha inicio y Fecha fin -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="fecha_inicio">Fecha inicio</label>
                    <input type="date"
                           id="fecha_inicio"
                           name="fecha_inicio"
                           class="form-control"
                           value="<?= e(old('fecha_inicio', $editing ? $fecha['fecha_inicio'] ?? '' : '')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="fecha_fin">Fecha fin</label>
                    <input type="date"
                           id="fecha_fin"
                           name="fecha_fin"
                           class="form-control"
                           value="<?= e(old('fecha_fin', $editing ? $fecha['fecha_fin'] ?? '' : '')) ?>">
                </div>
            </div>

            <!-- Recurrente y Activo -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Recurrente</label>
                    <div style="padding-top:0.5rem">
                        <label class="toggle-switch">
                            <input type="checkbox"
                                   name="recurrente"
                                   value="1"
                                   <?= old('recurrente', $editing ? ($fecha['recurrente'] ?? 0) : 0) ? 'checked' : '' ?>>
                            <span class="toggle-switch__slider"></span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Activo</label>
                    <div style="padding-top:0.5rem">
                        <label class="toggle-switch">
                            <input type="checkbox"
                                   name="activo"
                                   value="1"
                                   <?= old('activo', $editing ? $fecha['activo'] : 1) ? 'checked' : '' ?>>
                            <span class="toggle-switch__slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Imagen -->
            <div class="form-group">
                <label class="form-label" for="imagen">Imagen</label>
                <?php if ($editing && !empty($fecha['imagen'])): ?>
                    <div style="margin-bottom:0.75rem">
                        <img src="<?= asset('img/fechas/' . $fecha['imagen']) ?>"
                             alt="<?= e($fecha['nombre']) ?>"
                             id="imagePreview"
                             style="max-width:200px;max-height:120px;border-radius:var(--radius-md);border:1px solid var(--color-border)">
                    </div>
                <?php else: ?>
                    <img src="" alt="" id="imagePreview" style="display:none;max-width:200px;max-height:120px;border-radius:var(--radius-md);border:1px solid var(--color-border);margin-bottom:0.75rem">
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
                <button type="submit" class="btn btn--primary">Guardar fecha</button>
                <a href="<?= url('/admin/fechas') ?>" class="btn btn--outline">Cancelar</a>
            </div>
        </div>
    </div>
</form>

<script>
(function() {
    var preview = document.getElementById('colorPreview');

    // Sincronizar color de fondo
    var colorPicker = document.getElementById('color');
    var colorHex = document.getElementById('color_hex');
    colorPicker.addEventListener('input', function() {
        colorHex.value = this.value;
        preview.style.background = this.value;
    });
    colorHex.addEventListener('input', function() {
        var val = this.value.trim();
        if (/^#[0-9a-fA-F]{6}$/.test(val)) {
            colorPicker.value = val;
            preview.style.background = val;
        }
    });

    // Sincronizar color de texto
    var textoPicker = document.getElementById('color_texto');
    var textoHex = document.getElementById('color_texto_hex');
    textoPicker.addEventListener('input', function() {
        textoHex.value = this.value;
        preview.style.color = this.value;
    });
    textoHex.addEventListener('input', function() {
        var val = this.value.trim();
        if (/^#[0-9a-fA-F]{6}$/.test(val)) {
            textoPicker.value = val;
            preview.style.color = val;
        }
    });

    // Actualizar nombre en preview
    var nombreInput = document.getElementById('nombre');
    if (nombreInput) {
        nombreInput.addEventListener('input', function() {
            document.getElementById('previewNombre').textContent = this.value || 'Nombre de la celebración';
        });
    }

    // Actualizar icono en preview
    var iconoInput = document.getElementById('icono');
    if (iconoInput) {
        iconoInput.addEventListener('input', function() {
            document.getElementById('previewIcono').textContent = this.value || '🎁';
        });
    }
})();
</script>
