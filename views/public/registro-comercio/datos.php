<?php
/**
 * Registro de comercio — Paso 2: Datos del comercio
 * Plan Freemium: 2 fotos (logo+portada), 1 red social, mapa, sin horarios, sin sello
 */
$errores = $_SESSION['flash_errors'] ?? [];
$old     = $_SESSION['flash_old'] ?? [];
$error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_errors'], $_SESSION['flash_old'], $_SESSION['flash_error']);

// Agrupar fechas por tipo
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
?>

<section class="section">
    <div class="container" style="max-width:680px">

        <div style="text-align:center;margin-bottom:2rem">
            <span style="font-size:3rem">📝</span>
            <h1 style="font-size:1.75rem;margin:0.5rem 0">Datos de tu comercio</h1>
            <p class="text-muted">
                Hola <strong><?= e($nombreUsuario) ?></strong>, completa la información de tu negocio.
            </p>
            <p style="font-size:var(--font-size-sm);color:var(--color-gray)">
                Paso <strong>2</strong> de <strong>2</strong> — Información del comercio
            </p>
            <div style="display:inline-block;background:#F3F4F6;color:#6B7280;padding:0.25rem 0.75rem;border-radius:20px;font-size:0.8rem;margin-top:0.5rem">
                🆓 Plan Freemium — Gratuito
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
            <div style="background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <?php foreach ($errores as $err): ?>
                    <p style="margin:0.25rem 0"><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/registrar-comercio/store') ?>" enctype="multipart/form-data" id="formDatos">
            <?= csrf_field() ?>

            <!-- ═══ 1. INFORMACIÓN BÁSICA ═══ -->
            <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.25rem">
                <h3 style="margin:0 0 1rem;font-size:1.1rem;color:var(--color-text)">📋 Información básica</h3>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Nombre del comercio <span style="color:#DC2626">*</span>
                    </label>
                    <input type="text" name="nombre" class="form-control"
                           value="<?= e($old['nombre'] ?? '') ?>"
                           placeholder="Ej: Floristería Las Rosas" minlength="3" maxlength="150" required>
                    <small style="color:var(--color-gray)">Min. 3, max. 150 caracteres.</small>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Descripción del negocio
                    </label>
                    <textarea name="descripcion" class="form-control" rows="4"
                              placeholder="Cuéntanos qué ofrece tu comercio, qué productos o servicios tienes..."
                              minlength="20" maxlength="5000" required><?= e($old['descripcion'] ?? '') ?></textarea>
                    <small style="color:var(--color-gray)">Min. 20, max. 5000 caracteres.</small>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                            WhatsApp <span style="color:#DC2626">*</span>
                        </label>
                        <input type="text" name="whatsapp" class="form-control"
                               value="<?= e($old['whatsapp'] ?? '') ?>"
                               placeholder="+56 9 XXXX XXXX" minlength="9" maxlength="20" required>
                        <small style="color:#6B7280;font-size:0.8rem">Contacto principal para tus clientes</small>
                        <small style="color:var(--color-gray)">Min. 9, max. 20 caracteres.</small>
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Teléfono fijo <span style="color:#6B7280;font-weight:400">(opcional)</span></label>
                        <input type="text" name="telefono" class="form-control"
                               value="<?= e($old['telefono'] ?? '') ?>" placeholder="064 XXXXXX"
                               minlength="9" maxlength="20">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Email del comercio</label>
                        <input type="email" name="email_comercio" class="form-control"
                               value="<?= e($old['email_comercio'] ?? '') ?>" placeholder="contacto@micomercio.cl"
                               maxlength="150" required>
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Sitio web <span style="color:#6B7280;font-weight:400">(opcional)</span></label>
                        <input type="url" name="sitio_web" class="form-control"
                               value="<?= e($old['sitio_web'] ?? '') ?>" placeholder="https://..."
                               maxlength="255">
                    </div>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Dirección</label>
                    <input type="text" name="direccion" class="form-control"
                           value="<?= e($old['direccion'] ?? '') ?>"
                           placeholder="Ej: Av. Bernardo O'Higgins 123, Purranque"
                           minlength="5" maxlength="255" required>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Latitud</label>
                        <input type="text" name="lat" class="form-control"
                               value="<?= e($old['lat'] ?? '-40.9117') ?>" placeholder="-40.9117">
                        <small style="color:#6B7280;font-size:0.8rem">Para ubicar tu negocio en el mapa</small>
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Longitud</label>
                        <input type="text" name="lng" class="form-control"
                               value="<?= e($old['lng'] ?? '-73.1347') ?>" placeholder="-73.1347">
                        <small style="color:#6B7280;font-size:0.8rem"><a href="https://www.google.com/maps" target="_blank" style="color:#3B82F6">Buscar en Google Maps</a></small>
                    </div>
                </div>
            </div>

            <!-- ═══ 2. IMÁGENES (Logo + Portada) ═══ -->
            <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.25rem">
                <h3 style="margin:0 0 0.5rem;font-size:1.1rem;color:var(--color-text)">🖼️ Imágenes</h3>
                <p style="font-size:0.85rem;color:#6B7280;margin-bottom:1rem">
                    Plan Freemium permite 2 imágenes: logo y portada. JPG o PNG, máximo 2 MB cada una.
                </p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Logo</label>
                        <input type="file" name="logo" class="form-control" accept="image/jpeg,image/png,image/webp"
                               onchange="previewImg(this,'logoP')">
                        <img id="logoP" src="" alt="" loading="lazy" style="display:none;max-height:80px;margin-top:0.5rem;border-radius:4px">
                        <small style="color:#6B7280;font-size:0.8rem">Ideal: 800x800px. Se muestra circular.</small>
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Foto de portada</label>
                        <input type="file" name="portada" class="form-control" accept="image/jpeg,image/png,image/webp"
                               onchange="previewImg(this,'portP')">
                        <img id="portP" src="" alt="" loading="lazy" style="display:none;max-height:80px;margin-top:0.5rem;border-radius:4px">
                        <small style="color:#6B7280;font-size:0.8rem">Ideal: 1200x400px. Se muestra como banner.</small>
                    </div>
                </div>
            </div>

            <!-- ═══ 3. RED SOCIAL (solo 1) ═══ -->
            <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.25rem">
                <h3 style="margin:0 0 0.5rem;font-size:1.1rem;color:var(--color-text)">📱 Red social</h3>
                <p style="font-size:0.85rem;color:#6B7280;margin-bottom:1rem">
                    Plan Freemium permite 1 red social. Elige la más importante para tu negocio.
                    <span style="color:#3B82F6">¿Necesitas más? Consulta nuestros <a href="<?= url('/planes') ?>" style="color:#3B82F6;font-weight:600">planes</a>.</span>
                </p>

                <div style="display:grid;grid-template-columns:1fr 2fr;gap:0.75rem;align-items:end">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Red social</label>
                        <select name="red_social_tipo" class="form-control" id="redTipo">
                            <option value="">— Seleccionar —</option>
                            <option value="facebook" <?= ($old['red_social_tipo'] ?? '') === 'facebook' ? 'selected' : '' ?>>Facebook</option>
                            <option value="instagram" <?= ($old['red_social_tipo'] ?? '') === 'instagram' ? 'selected' : '' ?>>Instagram</option>
                            <option value="tiktok" <?= ($old['red_social_tipo'] ?? '') === 'tiktok' ? 'selected' : '' ?>>TikTok</option>
                            <option value="youtube" <?= ($old['red_social_tipo'] ?? '') === 'youtube' ? 'selected' : '' ?>>YouTube</option>
                            <option value="x_twitter" <?= ($old['red_social_tipo'] ?? '') === 'x_twitter' ? 'selected' : '' ?>>X (Twitter)</option>
                            <option value="linkedin" <?= ($old['red_social_tipo'] ?? '') === 'linkedin' ? 'selected' : '' ?>>LinkedIn</option>
                            <option value="telegram" <?= ($old['red_social_tipo'] ?? '') === 'telegram' ? 'selected' : '' ?>>Telegram</option>
                            <option value="pinterest" <?= ($old['red_social_tipo'] ?? '') === 'pinterest' ? 'selected' : '' ?>>Pinterest</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">URL del perfil</label>
                        <input type="url" name="red_social_url" class="form-control" id="redUrl"
                               value="<?= e($old['red_social_url'] ?? '') ?>"
                               placeholder="https://...">
                    </div>
                </div>
            </div>

            <!-- ═══ 4. CATEGORÍAS ═══ -->
            <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.25rem">
                <h3 style="margin:0 0 0.5rem;font-size:1.1rem;color:var(--color-text)">📂 Categorías</h3>
                <p style="font-size:0.85rem;color:#6B7280;margin-bottom:1rem">
                    Selecciona las categorías de tu comercio. Marca la principal con el círculo.
                </p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem">
                    <?php foreach ($categorias as $cat): ?>
                        <label style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0.75rem;background:#F9FAFB;border-radius:8px;cursor:pointer;font-size:0.9rem">
                            <input type="checkbox" name="categorias[]" value="<?= $cat['id'] ?>"
                                   <?= in_array($cat['id'], $old['categorias'] ?? []) ? 'checked' : '' ?>>
                            <input type="radio" name="categoria_principal" value="<?= $cat['id'] ?>"
                                   title="Marcar como principal"
                                   <?= ($old['categoria_principal'] ?? '') == $cat['id'] ? 'checked' : '' ?>>
                            <span><?= e($cat['icono'] ?? '') ?> <?= e($cat['nombre']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <small style="color:#6B7280;font-size:0.8rem;display:block;margin-top:0.5rem">
                    ☑ = pertenece a esta categoría &nbsp; ◉ = categoría principal
                </small>
            </div>

            <!-- ═══ 5. FECHAS ESPECIALES ═══ -->
            <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.25rem">
                <h3 style="margin:0 0 0.5rem;font-size:1.1rem;color:var(--color-text)">🎁 Fechas especiales</h3>
                <p style="font-size:0.85rem;color:#6B7280;margin-bottom:1rem">
                    ¿Para qué fechas ofreces productos o servicios especiales?
                </p>

                <?php foreach ($fechasPorTipo as $tipo => $grupo): ?>
                    <?php if (!empty($grupo['items'])): ?>
                        <div style="margin-bottom:1rem">
                            <h4 style="font-size:0.95rem;margin:0 0 0.5rem;color:var(--color-text)"><?= $grupo['label'] ?></h4>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.35rem">
                                <?php foreach ($grupo['items'] as $fecha): ?>
                                    <label style="display:flex;align-items:center;gap:0.5rem;padding:0.4rem 0.6rem;background:#F9FAFB;border-radius:6px;cursor:pointer;font-size:0.85rem">
                                        <input type="checkbox" name="fechas[]" value="<?= $fecha['id'] ?>"
                                               <?= in_array($fecha['id'], $old['fechas'] ?? []) ? 'checked' : '' ?>>
                                        <span><?= e($fecha['icono'] ?? '') ?> <?= e($fecha['nombre']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- ═══ ENVIAR ═══ -->
            <div style="text-align:center;margin-bottom:2rem">
                <?= \App\Services\Captcha::widget() ?>

                <button type="submit" class="btn btn--primary" id="btnRegistroComercio" style="padding:0.75rem 2rem;font-size:1.05rem;margin-top:1rem">
                    🏪 Enviar mi comercio para revisión
                </button>
                <p style="font-size:0.8rem;color:#6B7280;margin-top:0.75rem">
                    Nuestro equipo revisará tu información y te notificaremos cuando esté publicado.
                </p>
            </div>

        </form>
    </div>
</section>

<script>
function previewImg(input, imgId) {
    var img = document.getElementById(imgId);
    if (input.files && input.files[0]) {
        // Validar tamaño
        if (input.files[0].size > 2 * 1024 * 1024) {
            alert('La imagen no debe superar los 2 MB');
            input.value = '';
            img.style.display = 'none';
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Placeholder dinámico según red social seleccionada
document.getElementById('redTipo').addEventListener('change', function() {
    var placeholders = {
        'facebook': 'https://www.facebook.com/tu-pagina',
        'instagram': 'https://www.instagram.com/tu-cuenta',
        'tiktok': 'https://www.tiktok.com/@tu-cuenta',
        'youtube': 'https://www.youtube.com/@tu-canal',
        'x_twitter': 'https://x.com/tu-cuenta',
        'linkedin': 'https://www.linkedin.com/company/tu-empresa',
        'telegram': 'https://t.me/tu-canal',
        'pinterest': 'https://www.pinterest.com/tu-cuenta'
    };
    var urlInput = document.getElementById('redUrl');
    urlInput.placeholder = placeholders[this.value] || 'https://...';
});

document.getElementById('btnRegistroComercio').closest('form').addEventListener('submit', function() {
    var btn = document.getElementById('btnRegistroComercio');
    btn.disabled = true;
    btn.textContent = 'Enviando...';
});
</script>
