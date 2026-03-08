<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="<?= url('/admin/nurturing') ?>">Nurturing</a>
        <span>/</span>
        <span>Plantillas</span>
    </div>

    <div class="admin-page__header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <h1>Plantillas de recordatorio</h1>
        <?php if (count($plantillas) < $maxRec): ?>
            <a href="<?= url('/admin/nurturing/plantillas/crear') ?>" class="btn btn--primary btn--sm">
                + Nueva plantilla
            </a>
        <?php else: ?>
            <span class="badge" style="font-size:12px;">Maximo alcanzado (<?= $maxRec ?>)</span>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['flash']['success'])): ?>
        <div class="alert alert--success"><?= e($_SESSION['flash']['success']) ?></div>
        <?php unset($_SESSION['flash']['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash']['error'])): ?>
        <div class="alert alert--danger"><?= e($_SESSION['flash']['error']) ?></div>
        <?php unset($_SESSION['flash']['error']); ?>
    <?php endif; ?>

    <?php if (empty($plantillas)): ?>
        <div class="empty-state">
            <div class="empty-state__icon">&#128236;</div>
            <div class="empty-state__title">Sin plantillas</div>
            <div class="empty-state__text">Crea tu primera plantilla de recordatorio.</div>
            <a href="<?= url('/admin/nurturing/plantillas/crear') ?>" class="btn btn--primary btn--sm">+ Nueva plantilla</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table" id="tablePlantillas">
                <thead>
                    <tr>
                        <th style="width:50px;">Orden</th>
                        <th>Nombre</th>
                        <th>Tono</th>
                        <th>Asunto</th>
                        <th style="width:80px;">Activa</th>
                        <th style="width:200px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plantillas as $p): ?>
                        <tr data-id="<?= $p['id'] ?>">
                            <td style="text-align:center;">
                                <span style="font-weight:600;color:var(--primary);">R<?= $p['numero'] ?></span>
                            </td>
                            <td><?= e($p['nombre']) ?></td>
                            <td><span class="badge"><?= e($p['tono']) ?></span></td>
                            <td style="font-size:13px;"><?= e(mb_substr($p['asunto'], 0, 60)) ?></td>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" <?= $p['activo'] ? 'checked' : '' ?>
                                           data-toggle-url="<?= url('/admin/nurturing/plantillas/' . $p['id'] . '/toggle') ?>">
                                    <span class="toggle-switch__slider"></span>
                                </label>
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <button type="button" class="btn btn--outline btn--xs"
                                            onclick="previewPlantilla(<?= $p['id'] ?>)">Preview</button>
                                    <a href="<?= url('/admin/nurturing/plantillas/' . $p['id']) ?>"
                                       class="btn btn--outline btn--xs">Editar</a>
                                    <form method="POST" action="<?= url('/admin/nurturing/plantillas/' . $p['id'] . '/eliminar') ?>"
                                          style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--danger btn--xs"
                                                onclick="return confirm('Eliminar plantilla?')">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal preview -->
<div id="previewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;padding:40px;">
    <div style="max-width:700px;margin:0 auto;background:white;border-radius:8px;overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">
        <div style="padding:12px 16px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
            <strong>Vista previa</strong>
            <button onclick="document.getElementById('previewModal').style.display='none'" style="border:none;background:none;font-size:20px;cursor:pointer;">&times;</button>
        </div>
        <iframe id="previewFrame" style="flex:1;border:none;min-height:400px;"></iframe>
    </div>
</div>

<script>
function previewPlantilla(id) {
    document.getElementById('previewFrame').src = '<?= url('/admin/nurturing/plantillas/') ?>' + id + '/preview';
    document.getElementById('previewModal').style.display = 'block';
}

// Toggle activo via AJAX
document.querySelectorAll('[data-toggle-url]').forEach(function(el) {
    el.addEventListener('change', function() {
        fetch(this.dataset.toggleUrl, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json'},
            body: JSON.stringify({_token: '<?= csrf_token() ?>'})
        });
    });
});
</script>
