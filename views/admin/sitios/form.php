<?php
$isEdit = !empty($sitio);
$action = $isEdit
    ? url('/admin/sitios/update/' . $sitio['id'])
    : url('/admin/sitios/store');
?>

<div class="admin-page">
    <div class="admin-page__header">
        <h1><?= $isEdit ? 'Editar Sitio' : 'Nuevo Sitio' ?></h1>
        <div class="toolbar">
            <a href="<?= url('/admin/sitios') ?>" class="btn btn--outline">Volver</a>
        </div>
    </div>

    <form method="POST" action="<?= $action ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header">
                <h3>Información general</h3>
            </div>
            <div class="card__body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" class="form-control" required
                               value="<?= e($sitio['nombre'] ?? $_SESSION['_old']['nombre'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Slug *</label>
                        <input type="text" name="slug" class="form-control" required
                               value="<?= e($sitio['slug'] ?? $_SESSION['_old']['slug'] ?? '') ?>"
                               pattern="[a-z0-9\-]+" title="Solo letras minúsculas, números y guiones">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Dominio</label>
                        <input type="text" name="dominio" class="form-control"
                               value="<?= e($sitio['dominio'] ?? $_SESSION['_old']['dominio'] ?? '') ?>"
                               placeholder="regalos.ciudad.info">
                        <small class="form-help">Dominio sin https:// — Se usa para detectar el sitio automáticamente</small>
                    </div>
                    <div class="form-group">
                        <label>Ciudad *</label>
                        <input type="text" name="ciudad" class="form-control" required
                               value="<?= e($sitio['ciudad'] ?? $_SESSION['_old']['ciudad'] ?? 'Purranque') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?= e($sitio['descripcion'] ?? $_SESSION['_old']['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email de contacto</label>
                        <input type="email" name="email_contacto" class="form-control"
                               value="<?= e($sitio['email_contacto'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control"
                               value="<?= e($sitio['telefono'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Apariencia -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header">
                <h3>Apariencia</h3>
            </div>
            <div class="card__body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Color primario</label>
                        <input type="color" name="color_primario" class="form-control"
                               value="<?= e($sitio['color_primario'] ?? '#2563eb') ?>"
                               style="width:80px;height:40px;padding:4px;">
                    </div>
                    <div class="form-group">
                        <label>Color secundario</label>
                        <input type="color" name="color_secundario" class="form-control"
                               value="<?= e($sitio['color_secundario'] ?? '#1e40af') ?>"
                               style="width:80px;height:40px;padding:4px;">
                    </div>
                </div>

                <div class="form-group">
                    <label>Logo</label>
                    <?php if ($isEdit && !empty($sitio['logo'])): ?>
                        <div style="margin-bottom:8px;">
                            <img src="<?= asset('img/config/' . $sitio['logo']) ?>" alt="Logo"
                                 loading="lazy"
                                 style="max-height:60px;border-radius:4px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
            </div>
        </div>

        <!-- Ubicación -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header">
                <h3>Ubicación (centro del mapa)</h3>
            </div>
            <div class="card__body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Latitud</label>
                        <input type="number" name="lat" class="form-control" step="0.00000001"
                               value="<?= e($sitio['lat'] ?? '-40.91305000') ?>">
                    </div>
                    <div class="form-group">
                        <label>Longitud</label>
                        <input type="number" name="lng" class="form-control" step="0.00000001"
                               value="<?= e($sitio['lng'] ?? '-73.15913000') ?>">
                    </div>
                    <div class="form-group">
                        <label>Zoom</label>
                        <input type="number" name="zoom" class="form-control" min="5" max="20"
                               value="<?= (int) ($sitio['zoom'] ?? 15) ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__body">
                <div class="form-group">
                    <label class="toggle">
                        <input type="checkbox" name="activo" value="1"
                            <?= ($sitio['activo'] ?? 1) ? 'checked' : '' ?>
                            <?= ($isEdit && (int)$sitio['id'] === 1) ? 'disabled' : '' ?>>
                        <span class="toggle__label">Sitio activo</span>
                    </label>
                    <?php if ($isEdit && (int)$sitio['id'] === 1): ?>
                        <small class="form-help">El sitio principal no se puede desactivar</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="toolbar">
            <a href="<?= url('/admin/sitios') ?>" class="btn btn--outline">Cancelar</a>
            <button type="submit" class="btn btn--primary">
                <?= $isEdit ? 'Actualizar' : 'Crear sitio' ?>
            </button>
        </div>
    </form>
</div>
