<?php
/**
 * Formulario crear/editar comercio - Admin CRUD
 * Variables: $categorias, $fechas
 * Edicion: $comercio, $catIds, $catPrincipal, $fechaIds, $fechaData
 */
$editing = isset($comercio);
$errors  = $flash['errors'] ?? [];
?>

<div class="admin-breadcrumb">
    <a href="<?= url('/admin') ?>">Dashboard</a>
    <span>/</span>
    <a href="<?= url('/admin/comercios') ?>">Comercios</a>
    <span>/</span>
    <span><?= $editing ? 'Editar' : 'Crear' ?></span>
</div>

<h2 style="margin-bottom:1.25rem"><?= $editing ? 'Editar comercio' : 'Nuevo comercio' ?></h2>

<?php if (!empty($errors)): ?>
    <div class="toast toast--error toast--inline" role="alert">
        <span class="toast__message">
            <strong>Corrige los siguientes errores:</strong>
            <ul style="margin:0.5rem 0 0;padding-left:1.25rem">
                <?php foreach ($errors as $field => $msgs): ?>
                    <?php foreach ((array) $msgs as $msg): ?>
                        <li><?= e($msg) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </span>
    </div>
<?php endif; ?>

<form method="POST"
      action="<?= $editing ? url('/admin/comercios/update/' . $comercio['id']) : url('/admin/comercios/store') ?>"
      enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- 1. Información básica -->
    <div class="admin-card" style="margin-bottom:1.25rem">
        <div class="admin-card__header">
            <h3 class="admin-card__title">Información básica</h3>
        </div>
        <div class="admin-card__body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre <span style="color:var(--color-danger)">*</span></label>
                    <input type="text"
                           id="nombre"
                           name="nombre"
                           class="form-control <?= isset($errors['nombre']) ? 'form-control--error' : '' ?>"
                           value="<?= e(old('nombre', $editing ? $comercio['nombre'] : '')) ?>"
                           required
                           data-slug-source>
                </div>
                <div class="form-group">
                    <label class="form-label" for="slug">Slug <span style="color:var(--color-danger)">*</span></label>
                    <input type="text"
                           id="slug"
                           name="slug"
                           class="form-control <?= isset($errors['slug']) ? 'form-control--error' : '' ?>"
                           value="<?= e(old('slug', $editing ? $comercio['slug'] : '')) ?>"
                           required
                           data-slug-target>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="descripcion">Descripción</label>
                <textarea id="descripcion"
                          name="descripcion"
                          class="form-control"
                          rows="6"><?= e(old('descripcion', $editing ? $comercio['descripcion'] : '')) ?></textarea>
            </div>

            <div class="form-row form-row--3">
                <div class="form-group">
                    <label class="form-label" for="telefono">Teléfono</label>
                    <input type="text"
                           id="telefono"
                           name="telefono"
                           class="form-control"
                           value="<?= e(old('telefono', $editing ? $comercio['telefono'] : '')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="whatsapp">WhatsApp</label>
                    <input type="text"
                           id="whatsapp"
                           name="whatsapp"
                           class="form-control"
                           value="<?= e(old('whatsapp', $editing ? $comercio['whatsapp'] : '')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control"
                           value="<?= e(old('email', $editing ? $comercio['email'] : '')) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="sitio_web">Sitio web</label>
                    <input type="url"
                           id="sitio_web"
                           name="sitio_web"
                           class="form-control"
                           value="<?= e(old('sitio_web', $editing ? $comercio['sitio_web'] : '')) ?>"
                           placeholder="https://">
                </div>
                <div class="form-group">
                    <label class="form-label" for="direccion">Dirección</label>
                    <input type="text"
                           id="direccion"
                           name="direccion"
                           class="form-control"
                           value="<?= e(old('direccion', $editing ? $comercio['direccion'] : '')) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="lat">Latitud</label>
                    <input type="text"
                           id="lat"
                           name="lat"
                           class="form-control"
                           value="<?= e(old('lat', $editing ? ($comercio['lat'] ?? '') : '')) ?>"
                           placeholder="-40.0000">
                </div>
                <div class="form-group">
                    <label class="form-label" for="lng">Longitud</label>
                    <input type="text"
                           id="lng"
                           name="lng"
                           class="form-control"
                           value="<?= e(old('lng', $editing ? ($comercio['lng'] ?? '') : '')) ?>"
                           placeholder="-73.0000">
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Redes sociales del comercio -->
    <div class="admin-card" style="margin-bottom:1.25rem">
        <div class="admin-card__header">
            <h3 class="admin-card__title">Redes sociales</h3>
        </div>
        <div class="admin-card__body">
            <p style="margin-bottom:1rem;font-size:var(--font-size-sm);color:var(--color-gray)">
                Ingresa las URLs completas de las redes sociales del comercio. Deja en blanco las que no apliquen.
            </p>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="facebook">
                        <span style="display:inline-flex;align-items:center;gap:0.4rem">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#1877F2"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                            Facebook
                        </span>
                    </label>
                    <input type="url"
                           id="facebook"
                           name="facebook"
                           class="form-control"
                           value="<?= e(old('facebook', $editing ? ($comercio['facebook'] ?? '') : '')) ?>"
                           placeholder="https://www.facebook.com/mi-comercio">
                </div>
                <div class="form-group">
                    <label class="form-label" for="instagram">
                        <span style="display:inline-flex;align-items:center;gap:0.4rem">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#E4405F"><rect x="2" y="2" width="20" height="20" rx="5" fill="none" stroke="#E4405F" stroke-width="2"/><circle cx="12" cy="12" r="5" fill="none" stroke="#E4405F" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.5" fill="#E4405F"/></svg>
                            Instagram
                        </span>
                    </label>
                    <input type="url"
                           id="instagram"
                           name="instagram"
                           class="form-control"
                           value="<?= e(old('instagram', $editing ? ($comercio['instagram'] ?? '') : '')) ?>"
                           placeholder="https://www.instagram.com/mi-comercio">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="tiktok">
                        <span style="display:inline-flex;align-items:center;gap:0.4rem">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#000000"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                            TikTok
                        </span>
                    </label>
                    <input type="url"
                           id="tiktok"
                           name="tiktok"
                           class="form-control"
                           value="<?= e(old('tiktok', $editing ? ($comercio['tiktok'] ?? '') : '')) ?>"
                           placeholder="https://www.tiktok.com/@mi-comercio">
                </div>
                <div class="form-group">
                    <label class="form-label" for="youtube">
                        <span style="display:inline-flex;align-items:center;gap:0.4rem">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FF0000"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            YouTube
                        </span>
                    </label>
                    <input type="url"
                           id="youtube"
                           name="youtube"
                           class="form-control"
                           value="<?= e(old('youtube', $editing ? ($comercio['youtube'] ?? '') : '')) ?>"
                           placeholder="https://www.youtube.com/@mi-canal">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="x_twitter">
                        <span style="display:inline-flex;align-items:center;gap:0.4rem">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#000000"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            X (Twitter)
                        </span>
                    </label>
                    <input type="url"
                           id="x_twitter"
                           name="x_twitter"
                           class="form-control"
                           value="<?= e(old('x_twitter', $editing ? ($comercio['x_twitter'] ?? '') : '')) ?>"
                           placeholder="https://x.com/mi-comercio">
                </div>
                <div class="form-group">
                    <label class="form-label" for="linkedin">
                        <span style="display:inline-flex;align-items:center;gap:0.4rem">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#0A66C2"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            LinkedIn
                        </span>
                    </label>
                    <input type="url"
                           id="linkedin"
                           name="linkedin"
                           class="form-control"
                           value="<?= e(old('linkedin', $editing ? ($comercio['linkedin'] ?? '') : '')) ?>"
                           placeholder="https://www.linkedin.com/company/mi-comercio">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="telegram">
                        <span style="display:inline-flex;align-items:center;gap:0.4rem">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#26A5E4"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                            Telegram
                        </span>
                    </label>
                    <input type="url"
                           id="telegram"
                           name="telegram"
                           class="form-control"
                           value="<?= e(old('telegram', $editing ? ($comercio['telegram'] ?? '') : '')) ?>"
                           placeholder="https://t.me/mi-canal">
                </div>
                <div class="form-group">
                    <label class="form-label" for="pinterest">
                        <span style="display:inline-flex;align-items:center;gap:0.4rem">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#BD081C"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 01.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12.017 24c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641 0 12.017 0z"/></svg>
                            Pinterest
                        </span>
                    </label>
                    <input type="url"
                           id="pinterest"
                           name="pinterest"
                           class="form-control"
                           value="<?= e(old('pinterest', $editing ? ($comercio['pinterest'] ?? '') : '')) ?>"
                           placeholder="https://www.pinterest.com/mi-comercio">
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Imagenes -->
    <div class="admin-card" style="margin-bottom:1.25rem">
        <div class="admin-card__header">
            <h3 class="admin-card__title">Imágenes</h3>
        </div>
        <div class="admin-card__body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="logo">Logo</label>
                    <?php if ($editing && !empty($comercio['logo'])): ?>
                        <div style="margin-bottom:0.5rem">
                            <img src="<?= asset('img/logos/' . $comercio['logo']) ?>"
                                 alt="Logo actual"
                                 style="max-height:80px;border-radius:var(--radius-md);border:1px solid var(--color-border)">
                            <br><small style="color:var(--color-gray)">Logo actual. Selecciona otro para reemplazar.</small>
                        </div>
                    <?php endif; ?>
                    <input type="file"
                           id="logo"
                           name="logo"
                           class="form-control"
                           accept="image/*"
                           onchange="previewImage(this, 'logoPreview')">
                    <img id="logoPreview" src="" alt="" style="display:none;max-height:80px;margin-top:0.5rem;border-radius:var(--radius-md)">
                </div>
                <div class="form-group">
                    <label class="form-label" for="portada">Portada</label>
                    <?php if ($editing && !empty($comercio['portada'])): ?>
                        <div style="margin-bottom:0.5rem">
                            <img src="<?= asset('img/portadas/' . $comercio['portada']) ?>"
                                 alt="Portada actual"
                                 style="max-height:80px;border-radius:var(--radius-md);border:1px solid var(--color-border)">
                            <br><small style="color:var(--color-gray)">Portada actual. Selecciona otra para reemplazar.</small>
                        </div>
                    <?php endif; ?>
                    <input type="file"
                           id="portada"
                           name="portada"
                           class="form-control"
                           accept="image/*"
                           onchange="previewImage(this, 'portadaPreview')">
                    <img id="portadaPreview" src="" alt="" style="display:none;max-height:80px;margin-top:0.5rem;border-radius:var(--radius-md)">
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Categorías -->
    <div class="admin-card" style="margin-bottom:1.25rem">
        <div class="admin-card__header">
            <h3 class="admin-card__title">Categorías</h3>
        </div>
        <div class="admin-card__body">
            <p style="margin-bottom:1rem;font-size:var(--font-size-sm);color:var(--color-gray)">
                Selecciona las categorias del comercio y marca la categoria principal.
            </p>

            <?php if (!empty($categorias)): ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(250px, 1fr));gap:0.75rem">
                    <?php foreach ($categorias as $cat): ?>
                        <?php
                        $checked = $editing && in_array($cat['id'], $catIds ?? []);
                        $isPrincipal = $editing && (int) ($catPrincipal ?? 0) === (int) $cat['id'];
                        ?>
                        <div style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0.75rem;border:1px solid var(--color-border);border-radius:var(--radius-md)">
                            <input type="checkbox"
                                   name="categorias[]"
                                   value="<?= $cat['id'] ?>"
                                   id="cat_<?= $cat['id'] ?>"
                                   <?= $checked ? 'checked' : '' ?>>
                            <label for="cat_<?= $cat['id'] ?>" style="display:flex;align-items:center;gap:0.5rem;flex:1;cursor:pointer;font-size:var(--font-size-sm)">
                                <?php if (!empty($cat['icono'])): ?>
                                    <span><?= $cat['icono'] ?></span>
                                <?php endif; ?>
                                <?= e($cat['nombre']) ?>
                            </label>
                            <label style="display:flex;align-items:center;gap:0.25rem;font-size:var(--font-size-xs);color:var(--color-gray);cursor:pointer" title="Categoria principal">
                                <input type="radio"
                                       name="categoria_principal"
                                       value="<?= $cat['id'] ?>"
                                       <?= $isPrincipal ? 'checked' : '' ?>>
                                Principal
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color:var(--color-gray)">No hay categorias disponibles.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 5. Fechas especiales -->
    <div class="admin-card" style="margin-bottom:1.25rem">
        <div class="admin-card__header">
            <h3 class="admin-card__title">Fechas especiales</h3>
        </div>
        <div class="admin-card__body">
            <?php
            $fechasPorTipo = [
                'personal'  => ['label' => 'Celebraciones Personales', 'items' => []],
                'calendario' => ['label' => 'Fechas del Calendario', 'items' => []],
                'comercial' => ['label' => 'Eventos Comerciales', 'items' => []],
            ];
            foreach ($fechas as $f) {
                $tipo = $f['tipo'] ?? 'personal';
                if (isset($fechasPorTipo[$tipo])) {
                    $fechasPorTipo[$tipo]['items'][] = $f;
                }
            }
            ?>

            <?php foreach ($fechasPorTipo as $tipo => $group): ?>
                <?php if (empty($group['items'])) continue; ?>
                <details style="margin-bottom:1rem" <?= $tipo === 'personal' ? 'open' : '' ?>>
                    <summary style="cursor:pointer;font-weight:600;font-size:var(--font-size-sm);padding:0.5rem 0;color:var(--color-dark-soft)">
                        <?= e($group['label']) ?> (<?= count($group['items']) ?>)
                    </summary>
                    <div style="padding-top:0.75rem">
                        <?php foreach ($group['items'] as $fecha): ?>
                            <?php
                            $fChecked = $editing && in_array($fecha['id'], $fechaIds ?? []);
                            $fData    = ($editing && isset($fechaData[$fecha['id']])) ? $fechaData[$fecha['id']] : [];
                            ?>
                            <div style="border:1px solid var(--color-border);border-radius:var(--radius-md);padding:0.75rem;margin-bottom:0.5rem">
                                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:var(--font-size-sm)">
                                    <input type="checkbox"
                                           name="fechas[]"
                                           value="<?= $fecha['id'] ?>"
                                           class="fecha-checkbox"
                                           data-fecha-id="<?= $fecha['id'] ?>"
                                           <?= $fChecked ? 'checked' : '' ?>>
                                    <?php if (!empty($fecha['icono'])): ?>
                                        <span><?= $fecha['icono'] ?></span>
                                    <?php endif; ?>
                                    <strong><?= e($fecha['nombre']) ?></strong>
                                </label>

                                <div class="fecha-fields-<?= $fecha['id'] ?>" style="<?= $fChecked ? '' : 'display:none;' ?>margin-top:0.75rem;padding-left:1.5rem">
                                    <div class="form-group" style="margin-bottom:0.5rem">
                                        <label class="form-label" style="font-size:var(--font-size-xs)">Oferta especial</label>
                                        <input type="text"
                                               name="fecha_oferta_<?= $fecha['id'] ?>"
                                               class="form-control"
                                               value="<?= e(old('fecha_oferta_' . $fecha['id'], $fData['oferta_especial'] ?? '')) ?>"
                                               placeholder="Ej: 20% de descuento">
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group" style="margin-bottom:0">
                                            <label class="form-label" style="font-size:var(--font-size-xs)">Precio desde</label>
                                            <input type="number"
                                                   name="fecha_precio_desde_<?= $fecha['id'] ?>"
                                                   class="form-control"
                                                   value="<?= e(old('fecha_precio_desde_' . $fecha['id'], $fData['precio_desde'] ?? '')) ?>"
                                                   step="1"
                                                   min="0"
                                                   placeholder="$">
                                        </div>
                                        <div class="form-group" style="margin-bottom:0">
                                            <label class="form-label" style="font-size:var(--font-size-xs)">Precio hasta</label>
                                            <input type="number"
                                                   name="fecha_precio_hasta_<?= $fecha['id'] ?>"
                                                   class="form-control"
                                                   value="<?= e(old('fecha_precio_hasta_' . $fecha['id'], $fData['precio_hasta'] ?? '')) ?>"
                                                   step="1"
                                                   min="0"
                                                   placeholder="$">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 6. Configuración -->
    <div class="admin-card" style="margin-bottom:1.25rem">
        <div class="admin-card__header">
            <h3 class="admin-card__title">Configuración</h3>
        </div>
        <div class="admin-card__body">
            <div class="form-row form-row--3">
                <div class="form-group">
                    <label class="form-label" for="plan">Plan <span style="color:var(--color-danger)">*</span></label>
                    <select id="plan"
                            name="plan"
                            class="form-control"
                            required>
                        <?php
                        $planVal = old('plan', $editing ? $comercio['plan'] : 'freemium');
                        $planesDisponibles = \App\Core\Database::getInstance()->fetchAll("SELECT slug, nombre, icono FROM planes_config WHERE activo = 1 ORDER BY orden ASC");
                        foreach ($planesDisponibles as $pl):
                        ?>
                        <option value="<?= e($pl['slug']) ?>" <?= $planVal === $pl['slug'] ? 'selected' : '' ?>><?= $pl['icono'] ?> <?= e($pl['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;padding-top:0.5rem">
                        <input type="checkbox"
                               name="activo"
                               value="1"
                               <?= old('activo', $editing ? $comercio['activo'] : 1) ? 'checked' : '' ?>>
                        Activo
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;padding-top:0.5rem">
                        <input type="checkbox"
                               name="destacado"
                               value="1"
                               <?= old('destacado', $editing ? $comercio['destacado'] : 0) ? 'checked' : '' ?>>
                        Destacado
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- 7. Datos de Facturacion (solo admin, collapsible) -->
    <details class="admin-card" style="margin-bottom:1.25rem">
        <summary class="admin-card__header" style="cursor:pointer">
            <h3 class="admin-card__title">🔒 Datos de Facturacion</h3>
        </summary>
        <div class="admin-card__body">
            <p style="margin-bottom:1rem;font-size:var(--font-size-sm);color:var(--color-gray)">
                Información privada para facturación. No se muestra al público.
            </p>

            <!-- Datos tributarios -->
            <div style="margin-bottom:1.25rem">
                <h4 style="font-size:var(--font-size-sm);font-weight:600;color:var(--color-dark-soft);margin-bottom:0.75rem;padding-bottom:0.5rem;border-bottom:1px solid var(--color-border)">
                    Datos Tributarios
                </h4>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="razon_social">Razon Social</label>
                        <input type="text"
                               id="razon_social"
                               name="razon_social"
                               class="form-control"
                               value="<?= e(old('razon_social', $editing ? ($comercio['razon_social'] ?? '') : '')) ?>"
                               placeholder="Ej: Comercial Los Andes SpA">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rut_empresa">RUT Empresa</label>
                        <input type="text"
                               id="rut_empresa"
                               name="rut_empresa"
                               class="form-control"
                               value="<?= e(old('rut_empresa', $editing ? ($comercio['rut_empresa'] ?? '') : '')) ?>"
                               placeholder="XX.XXX.XXX-X"
                               maxlength="15">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="giro">Giro Comercial</label>
                    <input type="text"
                           id="giro"
                           name="giro"
                           class="form-control"
                           value="<?= e(old('giro', $editing ? ($comercio['giro'] ?? '') : '')) ?>"
                           placeholder="Ej: Venta al por menor de flores y plantas">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="direccion_tributaria">Dirección Tributaria</label>
                        <input type="text"
                               id="direccion_tributaria"
                               name="direccion_tributaria"
                               class="form-control"
                               value="<?= e(old('direccion_tributaria', $editing ? ($comercio['direccion_tributaria'] ?? '') : '')) ?>"
                               placeholder="Dirección registrada en SII">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="comuna_tributaria">Comuna Tributaria</label>
                        <input type="text"
                               id="comuna_tributaria"
                               name="comuna_tributaria"
                               class="form-control"
                               value="<?= e(old('comuna_tributaria', $editing ? ($comercio['comuna_tributaria'] ?? '') : '')) ?>"
                               placeholder="Ej: Purranque">
                    </div>
                </div>
            </div>

            <!-- Datos del contacto / propietario -->
            <div style="margin-bottom:1.25rem">
                <h4 style="font-size:var(--font-size-sm);font-weight:600;color:var(--color-dark-soft);margin-bottom:0.75rem;padding-bottom:0.5rem;border-bottom:1px solid var(--color-border)">
                    Contacto Propietario / Representante
                </h4>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="contacto_nombre">Nombre Completo</label>
                        <input type="text"
                               id="contacto_nombre"
                               name="contacto_nombre"
                               class="form-control"
                               value="<?= e(old('contacto_nombre', $editing ? ($comercio['contacto_nombre'] ?? '') : '')) ?>"
                               placeholder="Nombre del propietario o representante">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contacto_rut">RUT Personal</label>
                        <input type="text"
                               id="contacto_rut"
                               name="contacto_rut"
                               class="form-control"
                               value="<?= e(old('contacto_rut', $editing ? ($comercio['contacto_rut'] ?? '') : '')) ?>"
                               placeholder="XX.XXX.XXX-X"
                               maxlength="15">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="contacto_telefono">Teléfono Privado</label>
                        <input type="text"
                               id="contacto_telefono"
                               name="contacto_telefono"
                               class="form-control"
                               value="<?= e(old('contacto_telefono', $editing ? ($comercio['contacto_telefono'] ?? '') : '')) ?>"
                               placeholder="+56 9 XXXX XXXX">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contacto_email">Email Facturacion</label>
                        <input type="email"
                               id="contacto_email"
                               name="contacto_email"
                               class="form-control"
                               value="<?= e(old('contacto_email', $editing ? ($comercio['contacto_email'] ?? '') : '')) ?>"
                               placeholder="email@privado.cl">
                    </div>
                </div>
            </div>

            <!-- Datos comerciales internos -->
            <div>
                <h4 style="font-size:var(--font-size-sm);font-weight:600;color:var(--color-dark-soft);margin-bottom:0.75rem;padding-bottom:0.5rem;border-bottom:1px solid var(--color-border)">
                    Datos del Contrato
                </h4>
                <div class="form-row form-row--3">
                    <div class="form-group">
                        <label class="form-label" for="contrato_inicio">Fecha Inicio Contrato</label>
                        <input type="date"
                               id="contrato_inicio"
                               name="contrato_inicio"
                               class="form-control"
                               value="<?= e(old('contrato_inicio', $editing ? ($comercio['contrato_inicio'] ?? '') : '')) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contrato_monto">Monto Mensual (CLP)</label>
                        <input type="number"
                               id="contrato_monto"
                               name="contrato_monto"
                               class="form-control"
                               value="<?= e(old('contrato_monto', $editing ? ($comercio['contrato_monto'] ?? '') : '')) ?>"
                               min="0"
                               step="1"
                               placeholder="$ 0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="metodo_pago">Metodo de Pago</label>
                        <select id="metodo_pago"
                                name="metodo_pago"
                                class="form-control">
                            <?php $mpVal = old('metodo_pago', $editing ? ($comercio['metodo_pago'] ?? '') : ''); ?>
                            <option value="" <?= $mpVal === '' ? 'selected' : '' ?>>-- Seleccionar --</option>
                            <option value="transferencia" <?= $mpVal === 'transferencia' ? 'selected' : '' ?>>Transferencia Bancaria</option>
                            <option value="efectivo" <?= $mpVal === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                            <option value="debito" <?= $mpVal === 'debito' ? 'selected' : '' ?>>Tarjeta Debito</option>
                            <option value="credito" <?= $mpVal === 'credito' ? 'selected' : '' ?>>Tarjeta Credito</option>
                            <option value="cheque" <?= $mpVal === 'cheque' ? 'selected' : '' ?>>Cheque</option>
                            <option value="otro" <?= $mpVal === 'otro' ? 'selected' : '' ?>>Otro</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </details>

    <!-- 8. SEO (collapsible) -->
    <details class="admin-card" style="margin-bottom:1.25rem">
        <summary class="admin-card__header" style="cursor:pointer">
            <h3 class="admin-card__title">SEO</h3>
        </summary>
        <div class="admin-card__body">
            <div class="form-group">
                <label class="form-label" for="seo_titulo">Titulo SEO</label>
                <input type="text"
                       id="seo_titulo"
                       name="seo_titulo"
                       class="form-control"
                       value="<?= e(old('seo_titulo', $editing ? ($comercio['seo_titulo'] ?? '') : '')) ?>"
                       placeholder="Titulo para buscadores (max 70 caracteres)"
                       maxlength="70">
            </div>
            <div class="form-group">
                <label class="form-label" for="seo_descripcion">Descripción SEO</label>
                <textarea id="seo_descripcion"
                          name="seo_descripcion"
                          class="form-control"
                          rows="3"
                          placeholder="Descripción para buscadores (máx 160 caracteres)"
                          maxlength="160"><?= e(old('seo_descripcion', $editing ? ($comercio['seo_descripcion'] ?? '') : '')) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="seo_keywords">Keywords</label>
                <input type="text"
                       id="seo_keywords"
                       name="seo_keywords"
                       class="form-control"
                       value="value="<?= e(old('seo_keywords', $editing ? ($comercio['seo_keywords'] ?? '') : '')) ?>"
                       placeholder="Palabras clave separadas por coma">
            </div>
        </div>
    </details>

    <!-- 9. Botones -->
    <div class="toolbar" style="margin-bottom:0">
        <button type="submit" class="btn btn--primary">Guardar comercio</button>
        <a href="<?= url('/admin/comercios') ?>" class="btn btn--outline">Cancelar</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Slug auto-generation
    var source = document.querySelector('[data-slug-source]');
    var target = document.querySelector('[data-slug-target]');
    var slugManual = false;

    if (source && target) {
        target.addEventListener('input', function () {
            slugManual = true;
        });

        source.addEventListener('input', function () {
            if (slugManual) return;
            target.value = slugify(source.value);
        });
    }

    function slugify(text) {
        return text
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/[\s_]+/g, '-')
            .replace(/-+/g, '-');
    }

    // Image preview
    window.previewImage = function (input, previewId) {
        var preview = document.getElementById(previewId);
        if (!preview) return;

        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.style.display = 'none';
        }
    };

    // Fechas: toggle expanded fields
    document.querySelectorAll('.fecha-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var id = cb.getAttribute('data-fecha-id');
            var fields = document.querySelector('.fecha-fields-' + id);
            if (fields) {
                fields.style.display = cb.checked ? '' : 'none';
            }
        });
    });
});
</script>
