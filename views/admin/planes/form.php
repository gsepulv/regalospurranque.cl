<?php
/**
 * Admin - Formulario Crear/Editar Plan
 * Variables: $plan (null si crear, array si editar)
 */
$esEditar = !empty($plan);
$errors   = $flash['errors'] ?? [];
?>
<style>
.plan-form{max-width:780px}
.plan-form .form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem}
.plan-form .form-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1rem}
.plan-form .form-hint{font-size:.75rem;color:#a0aec0;margin-top:.15rem}
.plan-form .section-title{font-size:.95rem;font-weight:700;color:#4a5568;margin:1.5rem 0 .75rem;padding-bottom:.35rem;border-bottom:1px solid #e2e8f0}
.features-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;padding:.75rem;background:#f7fafc;border-radius:8px;margin-bottom:1rem}
.form-check-plan{display:flex;align-items:center;gap:.5rem;padding:.4rem 0;cursor:pointer}
.form-check-plan input[type="checkbox"]{width:18px;height:18px;cursor:pointer}
@media(max-width:768px){
    .plan-form .form-row,.plan-form .form-row-3{grid-template-columns:1fr}
    .features-grid{grid-template-columns:1fr}
}
</style>

<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/planes') ?>">Planes</a> &rsaquo;
    <span><?= $esEditar ? 'Editar: ' . e($plan['nombre']) : 'Nuevo Plan' ?></span>
</div>

<h2><?= $esEditar ? '✏️ Editar plan: ' . e($plan['nombre']) : '➕ Nuevo Plan' ?></h2>

<?php if (!empty($errors)): ?>
    <div class="toast toast--error toast--inline" role="alert">
        <span class="toast__message">
            <?php foreach ($errors as $field => $msg): ?>
                <?= e(is_array($msg) ? implode(', ', $msg) : $msg) ?><br>
            <?php endforeach; ?>
        </span>
    </div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card__body">
        <form method="POST"
              action="<?= $esEditar ? url('/admin/planes/update/' . $plan['id']) : url('/admin/planes/store') ?>"
              class="plan-form">
            <?= csrf_field() ?>

            <!-- Identificación -->
            <div class="section-title">📌 Identificación</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="slug">Slug (identificador único)</label>
                    <input type="text" id="slug" name="slug" class="form-control"
                           value="<?= e($plan['slug'] ?? '') ?>"
                           placeholder="ej: premium" pattern="[a-z0-9_]+"
                           minlength="3" maxlength="50" required
                           <?= $esEditar ? 'readonly style="background:#f7fafc"' : '' ?>>
                    <div class="form-hint">Solo minúsculas, números y guión bajo. No se puede cambiar después.</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre visible</label>
                    <input type="text" id="nombre" name="nombre" class="form-control"
                           value="<?= e($plan['nombre'] ?? '') ?>" placeholder="ej: Premium"
                           minlength="3" maxlength="50" required>
                    <small style="color:var(--color-gray)">Min. 3, max. 50 caracteres.</small>
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label" for="icono">Icono (emoji)</label>
                    <input type="text" id="icono" name="icono" class="form-control" style="max-width:80px"
                           value="<?= e($plan['icono'] ?? '') ?>" placeholder="⭐">
                </div>
                <div class="form-group">
                    <label class="form-label" for="color">Color</label>
                    <div style="display:flex;gap:.5rem;align-items:center">
                        <input type="color" id="color" name="color"
                               value="<?= e($plan['color'] ?? '#6B7280') ?>"
                               style="width:45px;height:36px;border:1px solid #e2e8f0;border-radius:6px;cursor:pointer">
                        <span id="colorHex" style="font-size:.8rem;color:#718096"><?= e($plan['color'] ?? '#6B7280') ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="orden">Orden</label>
                    <input type="number" id="orden" name="orden" class="form-control" style="max-width:70px"
                           value="<?= e($plan['orden'] ?? 0) ?>" min="0">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:1rem">
                <label class="form-label" for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="2"
                          placeholder="Descripción breve del plan..."
                          minlength="10" maxlength="500" required><?= e($plan['descripcion'] ?? '') ?></textarea>
                <small style="color:var(--color-gray)">Min. 10, max. 500 caracteres.</small>
            </div>

            <!-- Precios -->
            <div class="section-title">💰 Precios (CLP mensual)</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="precio_intro">Precio introductorio</label>
                    <input type="number" id="precio_intro" name="precio_intro" class="form-control"
                           value="<?= e($plan['precio_intro'] ?? 0) ?>" min="0" step="10">
                    <div class="form-hint">Primeros meses post-Beta</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="precio_regular">Precio regular</label>
                    <input type="number" id="precio_regular" name="precio_regular" class="form-control"
                           value="<?= e($plan['precio_regular'] ?? 0) ?>" min="0" step="10">
                    <div class="form-hint">Precio definitivo</div>
                </div>
            </div>

            <!-- Duración -->
            <div class="form-group">
                <label class="form-label" for="duracion_dias">Duraci&oacute;n del plan (d&iacute;as)</label>
                <input type="number" id="duracion_dias" name="duracion_dias" class="form-control"
                       value="<?= e($plan['duracion_dias'] ?? 30) ?>" min="1" max="365" step="1">
                <div class="form-hint">Cu&aacute;ntos d&iacute;as dura el plan al renovar (por defecto 30)</div>
            </div>

            <!-- Límites -->
            <div class="section-title">📏 Límites (vacío = sin límite)</div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label" for="max_fotos">Máximo fotos</label>
                    <input type="number" id="max_fotos" name="max_fotos" class="form-control"
                           value="<?= e($plan['max_fotos'] ?? 1) ?>" min="0" max="50">
                </div>
                <div class="form-group">
                    <label class="form-label" for="max_redes">Máximo redes sociales</label>
                    <input type="number" id="max_redes" name="max_redes" class="form-control"
                           value="<?= e($plan['max_redes'] ?? 1) ?>" min="0" max="99">
                    <div class="form-hint">99 = todas las redes</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="max_cupos">Cupos globales</label>
                    <input type="number" id="max_cupos" name="max_cupos" class="form-control"
                           value="<?= e($plan['max_cupos'] ?? '') ?>" min="0">
                    <div class="form-hint">Vacío = ilimitado</div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="max_cupos_categoria">Máx. cupos por categoría</label>
                    <input type="number" id="max_cupos_categoria" name="max_cupos_categoria" class="form-control"
                           value="<?= e($plan['max_cupos_categoria'] ?? '') ?>" min="0">
                    <div class="form-hint">Vacío = sin límite por categoría</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="posicion">Posición en listados</label>
                    <select id="posicion" name="posicion" class="form-control">
                        <option value="normal" <?= ($plan['posicion'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
                        <option value="prioritaria" <?= ($plan['posicion'] ?? '') === 'prioritaria' ? 'selected' : '' ?>>Prioritaria</option>
                        <option value="primero" <?= ($plan['posicion'] ?? '') === 'primero' ? 'selected' : '' ?>>SIEMPRE PRIMERO</option>
                    </select>
                </div>
            </div>

            <!-- Características -->
            <div class="section-title">✨ Características incluidas</div>
            <div class="features-grid">
                <label class="form-check-plan">
                    <input type="checkbox" name="tiene_mapa" value="1" <?= !empty($plan['tiene_mapa']) ? 'checked' : '' ?>>
                    🗺️ Mapa integrado en ficha
                </label>
                <label class="form-check-plan">
                    <input type="checkbox" name="tiene_horarios" value="1" <?= !empty($plan['tiene_horarios']) ? 'checked' : '' ?>>
                    🕐 Horarios de atención
                </label>
                <label class="form-check-plan">
                    <input type="checkbox" name="tiene_sello" value="1" <?= !empty($plan['tiene_sello']) ? 'checked' : '' ?>>
                    ✅ Sello verificado del plan
                </label>
                <label class="form-check-plan">
                    <input type="checkbox" name="tiene_reporte" value="1" <?= !empty($plan['tiene_reporte']) ? 'checked' : '' ?>>
                    📊 Reporte mensual de visitas
                </label>
            </div>

            <label class="form-check-plan" style="margin-bottom:1.5rem">
                <input type="checkbox" name="activo" value="1" <?= ($plan === null || !empty($plan['activo'])) ? 'checked' : '' ?>>
                🟢 Plan activo (visible para asignar)
            </label>

            <!-- Botones -->
            <div class="toolbar" style="margin-bottom:0">
                <a href="<?= url('/admin/planes') ?>" class="btn btn--outline">← Volver</a>
                <button type="submit" class="btn btn--primary">💾 <?= $esEditar ? 'Guardar cambios' : 'Crear plan' ?></button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('color')?.addEventListener('input', function() {
    document.getElementById('colorHex').textContent = this.value;
});
</script>
