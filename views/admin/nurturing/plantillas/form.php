<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="<?= url('/admin/nurturing') ?>">Nurturing</a>
        <span>/</span>
        <a href="<?= url('/admin/nurturing/plantillas') ?>">Plantillas</a>
        <span>/</span>
        <span><?= $plantilla ? 'Editar' : 'Nueva' ?></span>
    </div>

    <h1><?= $plantilla ? 'Editar plantilla: ' . e($plantilla['nombre']) : 'Nueva plantilla' ?></h1>

    <?php if (!empty($_SESSION['flash']['error'])): ?>
        <div class="alert alert--danger"><?= e($_SESSION['flash']['error']) ?></div>
        <?php unset($_SESSION['flash']['error']); ?>
    <?php endif; ?>

    <?php
    $old = $_SESSION['flash']['old'] ?? $plantilla ?? [];
    unset($_SESSION['flash']['old']);
    $action = $plantilla
        ? url('/admin/nurturing/plantillas/' . $plantilla['id'])
        : url('/admin/nurturing/plantillas/crear');
    ?>

    <form method="POST" action="<?= $action ?>">
        <?= csrf_field() ?>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start;">
            <div>
                <div class="card" style="margin-bottom:1.5rem;">
                    <div class="card__header"><h3 style="margin:0;">Datos de la plantilla</h3></div>
                    <div class="card__body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div class="form-group">
                                <label>Nombre *</label>
                                <input type="text" name="nombre" class="form-control" required
                                       value="<?= e($old['nombre'] ?? '') ?>"
                                       placeholder="Ej: Amigable, Directo, Urgente">
                            </div>
                            <div class="form-group">
                                <label>Tono</label>
                                <input type="text" name="tono" class="form-control"
                                       value="<?= e($old['tono'] ?? 'amigable') ?>"
                                       placeholder="Ej: amigable, formal, directo">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Asunto del email *</label>
                            <input type="text" name="asunto" class="form-control" required
                                   value="<?= e($old['asunto'] ?? '') ?>"
                                   placeholder="Ej: {nombre}, ya hay {total_comercios} comercios en Regalos Purranque">
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-bottom:1.5rem;">
                    <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;">
                        <h3 style="margin:0;">Contenido HTML</h3>
                        <div style="display:flex;gap:4px;">
                            <button type="button" class="btn btn--outline btn--xs" onclick="showTab('html')">HTML</button>
                            <button type="button" class="btn btn--outline btn--xs" onclick="showTab('preview')">Vista previa</button>
                        </div>
                    </div>
                    <div class="card__body">
                        <div id="tab-html">
                            <textarea name="contenido_html" class="form-control" rows="16" required
                                      id="contenidoHtml" style="font-family:monospace;font-size:13px;"><?= e($old['contenido_html'] ?? '') ?></textarea>
                        </div>
                        <div id="tab-preview" style="display:none;border:1px solid #e2e8f0;border-radius:4px;padding:16px;min-height:200px;">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card__header"><h3 style="margin:0;">Version texto plano</h3></div>
                    <div class="card__body">
                        <textarea name="contenido_texto" class="form-control" rows="6"
                                  style="font-family:monospace;font-size:13px;"><?= e($old['contenido_texto'] ?? '') ?></textarea>
                        <small style="color:var(--gray-500);">Opcional. Se usa como fallback si el cliente de email no soporta HTML.</small>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <div class="card" style="margin-bottom:1rem;">
                    <div class="card__header"><h4 style="margin:0;">Variables disponibles</h4></div>
                    <div class="card__body" style="font-size:13px;">
                        <p style="color:var(--gray-500);margin:0 0 8px;">Haz clic para copiar:</p>
                        <?php
                        $variables = [
                            '{nombre}'             => 'Nombre del contacto',
                            '{email}'              => 'Email del contacto',
                            '{total_comercios}'    => 'Total de comercios activos',
                            '{link_registro}'      => 'URL de registro',
                            '{link_desuscripcion}' => 'URL de desuscripcion',
                        ];
                        foreach ($variables as $var => $desc):
                        ?>
                            <div style="margin-bottom:6px;display:flex;justify-content:space-between;align-items:center;">
                                <code style="cursor:pointer;background:#f1f5f9;padding:2px 6px;border-radius:3px;"
                                      onclick="copyVar('<?= $var ?>')" title="Clic para copiar"><?= $var ?></code>
                                <small style="color:var(--gray-500);"><?= $desc ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card__body">
                        <button type="submit" class="btn btn--primary" style="width:100%;">
                            <?= $plantilla ? 'Actualizar plantilla' : 'Crear plantilla' ?>
                        </button>
                        <a href="<?= url('/admin/nurturing/plantillas') ?>" class="btn btn--outline" style="width:100%;margin-top:8px;">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function showTab(tab) {
    document.getElementById('tab-html').style.display = tab === 'html' ? 'block' : 'none';
    var previewDiv = document.getElementById('tab-preview');
    previewDiv.style.display = tab === 'preview' ? 'block' : 'none';
    if (tab === 'preview') {
        var html = document.getElementById('contenidoHtml').value;
        html = html.replace(/\{nombre\}/g, 'Maria Gonzalez');
        html = html.replace(/\{email\}/g, 'maria@ejemplo.cl');
        html = html.replace(/\{total_comercios\}/g, '15');
        html = html.replace(/\{link_registro\}/g, '<?= SITE_URL ?>/registrar-comercio');
        html = html.replace(/\{link_desuscripcion\}/g, '#');
        previewDiv.innerHTML = html;
    }
}

function copyVar(v) {
    navigator.clipboard.writeText(v);
}
</script>
