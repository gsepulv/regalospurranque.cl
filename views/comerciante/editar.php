<?php
/**
 * Editar mi comercio — Panel del comerciante
 * Variables: $comercio, $categorias, $fechas, $catIds, $catPrincipal, $fechaIds, $plan, $redActual, $usuario
 */
$errores = $_SESSION['flash_errors'] ?? [];
unset($_SESSION['flash_errors']);

$fechasPorTipo = [
    'personal'   => ['label' => '🎉 Celebraciones Personales', 'items' => []],
    'calendario' => ['label' => '📅 Fechas del Calendario', 'items' => []],
    'comercial'  => ['label' => '💰 Eventos Comerciales', 'items' => []],
];
foreach ($fechas as $f) {
    $tipo = $f['tipo'] ?? 'personal';
    if (isset($fechasPorTipo[$tipo])) {
        $fechasPorTipo[$tipo]['items'][] = $f;
    }
}

$maxRedes = (int)($plan['max_redes'] ?? 1);
?>

<section class="section">
    <div class="container" style="max-width:680px">

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
            <div>
                <h1 style="font-size:1.5rem;margin:0">Editar mi comercio</h1>
                <p style="color:#6B7280;margin:0.25rem 0 0;font-size:0.85rem">
                    Los cambios serán revisados antes de publicarse.
                </p>
            </div>
            <a href="<?= url('/mi-comercio') ?>" style="color:#6B7280;font-size:0.85rem;text-decoration:none">
                ← Volver
            </a>
        </div>

        <?php if (!empty($errores)): ?>
            <div style="background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <?php foreach ($errores as $err): ?>
                    <p style="margin:0.25rem 0"><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/mi-comercio/guardar') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <!-- Información básica -->
            <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.25rem">
                <h3 style="margin:0 0 1rem;font-size:1.1rem">📋 Información básica</h3>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Nombre del comercio <span style="color:#DC2626">*</span>
                    </label>
                    <input type="text" name="nombre" class="form-control"
                           value="<?= e($comercio['nombre']) ?>" minlength="3" maxlength="100" required>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="4" minlength="20" maxlength="5000" required><?= e($comercio['descripcion']) ?></textarea>
                    <small style="color:var(--color-gray)">Min. 20, max. 5000 caracteres.</small>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                            WhatsApp <span style="color:#DC2626">*</span>
                        </label>
                        <input type="text" name="whatsapp" class="form-control"
                               value="<?= e($comercio['whatsapp']) ?>" minlength="9" maxlength="15" required>
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Teléfono</label>
                        <input type="text" name="telefono" class="form-control"
                               value="<?= e($comercio['telefono']) ?>" minlength="9" maxlength="15" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Email del comercio</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= e($comercio['email']) ?>" maxlength="100" required>
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Sitio web</label>
                        <input type="url" name="sitio_web" class="form-control"
                               value="<?= e($comercio['sitio_web']) ?>" minlength="10" maxlength="255" required>
                    </div>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Dirección</label>
                    <input type="text" name="direccion" class="form-control"
                           value="<?= e($comercio['direccion']) ?>" minlength="5" maxlength="255" required>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Latitud</label>
                        <input type="text" name="lat" class="form-control"
                               value="<?= e($comercio['lat']) ?>">
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Longitud</label>
                        <input type="text" name="lng" class="form-control"
                               value="<?= e($comercio['lng']) ?>">
                    </div>
                </div>
            </div>

            <!-- Imágenes -->
            <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.25rem">
                <h3 style="margin:0 0 1rem;font-size:1.1rem">🖼️ Imágenes</h3>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Logo</label>
                        <?php if ($comercio['logo']): ?>
                            <img src="<?= asset('img/logos/' . $comercio['logo']) ?>" alt="Logo actual"
                                 style="max-height:60px;border-radius:4px;margin-bottom:0.5rem;display:block">
                        <?php endif; ?>
                        <input type="file" name="logo" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small style="color:#6B7280;font-size:0.8rem">Dejar vacío para mantener el actual</small>
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Portada</label>
                        <?php if ($comercio['portada']): ?>
                            <img src="<?= asset('img/portadas/' . $comercio['portada']) ?>" alt="Portada actual"
                                 style="max-height:60px;border-radius:4px;margin-bottom:0.5rem;display:block">
                        <?php endif; ?>
                        <input type="file" name="portada" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small style="color:#6B7280;font-size:0.8rem">Dejar vacío para mantener la actual</small>
                    </div>
                </div>
            </div>

            <!-- Red social -->
            <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.25rem">
                <h3 style="margin:0 0 0.5rem;font-size:1.1rem">📱 Red social</h3>
                <?php if ($maxRedes <= 1): ?>
                    <p style="font-size:0.85rem;color:#6B7280;margin-bottom:1rem">
                        Tu plan permite 1 red social.
                        <a href="<?= url('/planes') ?>" style="color:#3B82F6">¿Necesitas más?</a>
                    </p>
                <?php endif; ?>

                <div style="display:grid;grid-template-columns:1fr 2fr;gap:0.75rem;align-items:end">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Red social</label>
                        <select name="red_social_tipo" class="form-control" id="redTipo">
                            <option value="">— Ninguna —</option>
                            <option value="facebook" <?= $redActual['tipo'] === 'facebook' ? 'selected' : '' ?>>Facebook</option>
                            <option value="instagram" <?= $redActual['tipo'] === 'instagram' ? 'selected' : '' ?>>Instagram</option>
                            <option value="tiktok" <?= $redActual['tipo'] === 'tiktok' ? 'selected' : '' ?>>TikTok</option>
                            <option value="youtube" <?= $redActual['tipo'] === 'youtube' ? 'selected' : '' ?>>YouTube</option>
                            <option value="x_twitter" <?= $redActual['tipo'] === 'x_twitter' ? 'selected' : '' ?>>X (Twitter)</option>
                            <option value="linkedin" <?= $redActual['tipo'] === 'linkedin' ? 'selected' : '' ?>>LinkedIn</option>
                            <option value="telegram" <?= $redActual['tipo'] === 'telegram' ? 'selected' : '' ?>>Telegram</option>
                            <option value="pinterest" <?= $redActual['tipo'] === 'pinterest' ? 'selected' : '' ?>>Pinterest</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">URL</label>
                        <input type="url" name="red_social_url" class="form-control" id="redUrl"
                               value="<?= e($redActual['url']) ?>" placeholder="https://...">
                    </div>
                </div>
            </div>

            <!-- Categorías -->
            <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.25rem">
                <h3 style="margin:0 0 0.5rem;font-size:1.1rem">📂 Categorías</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem">
                    <?php foreach ($categorias as $cat): ?>
                        <label style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0.75rem;background:#F9FAFB;border-radius:8px;cursor:pointer;font-size:0.9rem">
                            <input type="checkbox" name="categorias[]" value="<?= $cat['id'] ?>"
                                   <?= in_array($cat['id'], $catIds) ? 'checked' : '' ?>>
                            <input type="radio" name="categoria_principal" value="<?= $cat['id'] ?>"
                                   <?= $catPrincipal == $cat['id'] ? 'checked' : '' ?>>
                            <span><?= e($cat['icono'] ?? '') ?> <?= e($cat['nombre']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <small style="color:#6B7280;font-size:0.8rem;display:block;margin-top:0.5rem">
                    ☑ = pertenece &nbsp; ◉ = principal
                </small>
            </div>

            <!-- Fechas -->
            <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.25rem">
                <h3 style="margin:0 0 0.5rem;font-size:1.1rem">🎁 Fechas especiales</h3>
                <?php foreach ($fechasPorTipo as $tipo => $grupo): ?>
                    <?php if (!empty($grupo['items'])): ?>
                        <div style="margin-bottom:1rem">
                            <h4 style="font-size:0.95rem;margin:0 0 0.5rem"><?= $grupo['label'] ?></h4>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.35rem">
                                <?php foreach ($grupo['items'] as $fecha): ?>
                                    <label style="display:flex;align-items:center;gap:0.5rem;padding:0.4rem 0.6rem;background:#F9FAFB;border-radius:6px;cursor:pointer;font-size:0.85rem">
                                        <input type="checkbox" name="fechas[]" value="<?= $fecha['id'] ?>"
                                               <?= in_array($fecha['id'], $fechaIds) ? 'checked' : '' ?>>
                                        <span><?= e($fecha['icono'] ?? '') ?> <?= e($fecha['nombre']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Enviar -->
            <div style="text-align:center;margin-bottom:2rem">
                <button type="submit" class="btn btn--primary" style="padding:0.75rem 2rem;font-size:1.05rem">
                    📤 Enviar cambios para revisión
                </button>
                <p style="font-size:0.8rem;color:#6B7280;margin-top:0.75rem">
                    Los cambios serán revisados por nuestro equipo antes de publicarse.
                </p>
            </div>

        </form>
    </div>
</section>

<script>
document.getElementById('redTipo').addEventListener('change', function() {
    var ph = {
        'facebook': 'https://www.facebook.com/tu-pagina',
        'instagram': 'https://www.instagram.com/tu-cuenta',
        'tiktok': 'https://www.tiktok.com/@tu-cuenta',
        'youtube': 'https://www.youtube.com/@tu-canal',
        'x_twitter': 'https://x.com/tu-cuenta',
        'linkedin': 'https://www.linkedin.com/company/tu-empresa',
        'telegram': 'https://t.me/tu-canal',
        'pinterest': 'https://www.pinterest.com/tu-cuenta'
    };
    document.getElementById('redUrl').placeholder = ph[this.value] || 'https://...';
});
</script>
