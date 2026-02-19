<?php
/**
 * Admin - Mantenimiento > Explorador de Archivos
 * Variables: $items (array), $currentPath (string), $breadcrumbs (array of [name, path])
 */

function formatFileSize($bytes) {
    if ($bytes <= 0) return '—';
    $units = ['B', 'KB', 'MB', 'GB'];
    $pow = floor(log($bytes, 1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
}

function fileIcon($type, $extension = '') {
    if ($type === 'dir') return '&#128193;';
    $ext = strtolower($extension);
    $icons = [
        'php'  => '&#128220;',
        'js'   => '&#128312;',
        'css'  => '&#127912;',
        'html' => '&#127760;',
        'htm'  => '&#127760;',
        'json' => '&#128196;',
        'xml'  => '&#128196;',
        'sql'  => '&#128451;',
        'md'   => '&#128196;',
        'txt'  => '&#128196;',
        'log'  => '&#128196;',
        'env'  => '&#128272;',
        'jpg'  => '&#128247;',
        'jpeg' => '&#128247;',
        'png'  => '&#128247;',
        'gif'  => '&#128247;',
        'svg'  => '&#128247;',
        'webp' => '&#128247;',
        'ico'  => '&#128247;',
        'pdf'  => '&#128213;',
        'zip'  => '&#128230;',
        'gz'   => '&#128230;',
        'tar'  => '&#128230;',
        'rar'  => '&#128230;',
        'htaccess' => '&#128274;',
    ];
    return $icons[$ext] ?? '&#128196;';
}

function isTextFile($extension) {
    $textExts = ['php', 'js', 'css', 'html', 'htm', 'json', 'xml', 'sql', 'md', 'txt', 'log', 'env', 'htaccess', 'yaml', 'yml', 'ini', 'conf', 'svg', 'csv'];
    return in_array(strtolower($extension), $textExts);
}

function isImageFile($extension) {
    $imgExts = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'bmp'];
    return in_array(strtolower($extension), $imgExts);
}
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/mantenimiento/backups') ?>">Mantenimiento</a> &rsaquo;
    <span>Explorador de Archivos</span>
</div>

<?php
$currentTab = 'archivos';
$tabs = [
    'backups'        => ['label' => 'Backups',           'url' => '/admin/mantenimiento/backups'],
    'archivos'       => ['label' => 'Explorador',        'url' => '/admin/mantenimiento/archivos'],
    'salud'          => ['label' => 'Salud',             'url' => '/admin/mantenimiento/salud'],
    'logs'           => ['label' => 'Logs',              'url' => '/admin/mantenimiento/logs'],
    'herramientas'   => ['label' => 'Herramientas',      'url' => '/admin/mantenimiento/herramientas'],
    'configuracion'  => ['label' => 'Configuraci&oacute;n',  'url' => '/admin/mantenimiento/configuracion'],
];
?>
<div class="admin-tabs" style="margin-bottom:var(--spacing-6)">
    <?php foreach ($tabs as $key => $tab): ?>
        <a href="<?= url($tab['url']) ?>" class="admin-tab <?= $currentTab === $key ? 'admin-tab--active' : '' ?>"><?= $tab['label'] ?></a>
    <?php endforeach; ?>
</div>

<?php if ($flash['success'] ?? false): ?>
    <div class="toast toast--success"><?= e($flash['success']) ?></div>
<?php endif; ?>
<?php if ($flash['error'] ?? false): ?>
    <div class="toast toast--error"><?= e($flash['error']) ?></div>
<?php endif; ?>

<div class="toolbar">
    <h2 style="margin:0;flex:1">Explorador de Archivos</h2>
    <button type="button" class="btn btn--primary btn--sm" onclick="document.getElementById('modalSubir').classList.add('modal--open')">Subir Archivo</button>
    <button type="button" class="btn btn--outline btn--sm" onclick="document.getElementById('modalCrearCarpeta').classList.add('modal--open')">Crear Carpeta</button>
</div>

<!-- Path breadcrumbs -->
<div class="admin-card" style="margin-bottom:var(--spacing-4)">
    <div style="padding:var(--spacing-3) var(--spacing-4);font-size:0.875rem;display:flex;align-items:center;gap:0.25rem;flex-wrap:wrap">
        <span style="color:var(--color-gray);margin-right:var(--spacing-2)">&#128193; Ruta:</span>
        <?php foreach ($breadcrumbs as $i => $crumb): ?>
            <?php if ($i > 0): ?>
                <span style="color:var(--color-gray)">/</span>
            <?php endif; ?>
            <?php if ($i < count($breadcrumbs) - 1): ?>
                <a href="<?= url('/admin/mantenimiento/archivos?path=' . urlencode($crumb['path'])) ?>"
                   style="color:var(--color-primary);text-decoration:none"><?= e($crumb['name']) ?></a>
            <?php else: ?>
                <strong><?= e($crumb['name']) ?></strong>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<!-- Files Table -->
<div class="admin-card">
    <?php if (!empty($items)): ?>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Tama&ntilde;o</th>
                        <th>Modificado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <span style="margin-right:var(--spacing-2)"><?= fileIcon($item['type'], $item['ext'] ?? '') ?></span>
                                <?php if ($item['type'] === 'dir'): ?>
                                    <a href="<?= url('/admin/mantenimiento/archivos?path=' . urlencode($currentPath . '/' . $item['name'])) ?>"
                                       style="font-weight:600;color:var(--color-primary);text-decoration:none">
                                        <?= e($item['name']) ?>
                                    </a>
                                <?php else: ?>
                                    <span><?= e($item['name']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['type'] === 'dir'): ?>
                                    <span class="badge">Carpeta</span>
                                <?php else: ?>
                                    <span class="badge badge--info"><?= e(strtoupper($item['ext'] ?? '—')) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['type'] === 'dir'): ?>
                                    —
                                <?php else: ?>
                                    <?= formatFileSize($item['size'] ?? 0) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($item['modified'])): ?>
                                    <?= date('d/m/Y H:i', strtotime($item['modified'])) ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <?php if ($item['type'] === 'dir'): ?>
                                        <a href="<?= url('/admin/mantenimiento/archivos?path=' . urlencode($currentPath . '/' . $item['name'])) ?>"
                                           class="btn btn--outline btn--sm">Entrar</a>
                                    <?php else: ?>
                                        <?php if (isTextFile($item['ext'] ?? '') || isImageFile($item['ext'] ?? '')): ?>
                                            <button type="button"
                                                    class="btn btn--outline btn--sm"
                                                    onclick="verArchivo('<?= e(addslashes($currentPath . '/' . $item['name'])) ?>', '<?= e(addslashes($item['name'])) ?>', '<?= e($item['extension'] ?? '') ?>')">Ver</button>
                                        <?php endif; ?>
                                        <a href="<?= url('/admin/mantenimiento/archivos/descargar?path=' . urlencode($currentPath . '/' . $item['name'])) ?>"
                                           class="btn btn--outline btn--sm">Descargar</a>
                                    <?php endif; ?>
                                    <button type="button"
                                            class="btn btn--outline btn--sm"
                                            onclick="abrirRenombrar('<?= e(addslashes(($currentPath !== '' ? $currentPath . '/' : '') . $item['name'])) ?>', '<?= e(addslashes($item['name'])) ?>')">Renombrar</button>
                                    <form method="POST"
                                          action="<?= url('/admin/mantenimiento/archivos/eliminar') ?>"
                                          style="display:inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="ruta" value="<?= e(($currentPath !== '' ? $currentPath . '/' : '') . $item['name']) ?>">
                                        <button type="submit"
                                                class="btn btn--danger btn--sm"
                                                data-confirm="&iquest;Eliminar '<?= e(addslashes($item['name'])) ?>'? Esta acci&oacute;n no se puede deshacer.">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="padding:var(--spacing-6);text-align:center">
            <p style="color:var(--color-gray);margin:0">Esta carpeta est&aacute; vac&iacute;a.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Ver Archivo -->
<div class="modal" id="modalVerArchivo">
    <div class="modal__backdrop" onclick="cerrarModal('modalVerArchivo')"></div>
    <div class="modal__content" style="max-width:800px;width:90%">
        <div class="modal__header">
            <h3 id="verArchivo_titulo" style="margin:0">Archivo</h3>
            <button type="button" class="modal__close" onclick="cerrarModal('modalVerArchivo')">&times;</button>
        </div>
        <div class="modal__body" id="verArchivo_contenido" style="max-height:70vh;overflow:auto">
            <p style="color:var(--color-gray);text-align:center">Cargando...</p>
        </div>
        <div class="modal__footer" style="display:flex;justify-content:flex-end;padding:var(--spacing-3) var(--spacing-4)">
            <button type="button" class="btn btn--outline" onclick="cerrarModal('modalVerArchivo')">Cerrar</button>
        </div>
    </div>
</div>

<!-- Modal: Renombrar -->
<div class="modal" id="modalRenombrar">
    <div class="modal__backdrop" onclick="cerrarModal('modalRenombrar')"></div>
    <div class="modal__content" style="max-width:480px">
        <div class="modal__header">
            <h3 style="margin:0">Renombrar</h3>
            <button type="button" class="modal__close" onclick="cerrarModal('modalRenombrar')">&times;</button>
        </div>
        <div class="modal__body">
            <form method="POST" action="<?= url('/admin/mantenimiento/archivos/renombrar') ?>" id="formRenombrar">
                <?= csrf_field() ?>
                <input type="hidden" name="ruta" id="renombrar_ruta" value="">
                <div class="form-group">
                    <label class="form-label">Nuevo nombre</label>
                    <input type="text" name="nombre_nuevo" id="renombrar_new_name" class="form-control" required>
                </div>
                <div style="display:flex;gap:var(--spacing-3);justify-content:flex-end;margin-top:var(--spacing-4)">
                    <button type="button" class="btn btn--outline" onclick="cerrarModal('modalRenombrar')">Cancelar</button>
                    <button type="submit" class="btn btn--primary">Renombrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Subir Archivo -->
<div class="modal" id="modalSubir">
    <div class="modal__backdrop" onclick="cerrarModal('modalSubir')"></div>
    <div class="modal__content" style="max-width:480px">
        <div class="modal__header">
            <h3 style="margin:0">Subir Archivo</h3>
            <button type="button" class="modal__close" onclick="cerrarModal('modalSubir')">&times;</button>
        </div>
        <div class="modal__body">
            <form method="POST" action="<?= url('/admin/mantenimiento/archivos/subir') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="directorio" value="<?= e($currentPath) ?>">
                <div class="form-group">
                    <label class="form-label">Seleccionar archivo</label>
                    <input type="file" name="archivo" class="form-control" required>
                    <small class="form-hint">Tama&ntilde;o m&aacute;ximo: 10 MB</small>
                </div>
                <div style="display:flex;gap:var(--spacing-3);justify-content:flex-end;margin-top:var(--spacing-4)">
                    <button type="button" class="btn btn--outline" onclick="cerrarModal('modalSubir')">Cancelar</button>
                    <button type="submit" class="btn btn--primary">Subir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Crear Carpeta -->
<div class="modal" id="modalCrearCarpeta">
    <div class="modal__backdrop" onclick="cerrarModal('modalCrearCarpeta')"></div>
    <div class="modal__content" style="max-width:480px">
        <div class="modal__header">
            <h3 style="margin:0">Crear Carpeta</h3>
            <button type="button" class="modal__close" onclick="cerrarModal('modalCrearCarpeta')">&times;</button>
        </div>
        <div class="modal__body">
            <form method="POST" action="<?= url('/admin/mantenimiento/archivos/crear-carpeta') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="directorio" value="<?= e($currentPath) ?>">
                <div class="form-group">
                    <label class="form-label">Nombre de la carpeta</label>
                    <input type="text" name="nombre" class="form-control" required placeholder="nueva-carpeta">
                </div>
                <div style="display:flex;gap:var(--spacing-3);justify-content:flex-end;margin-top:var(--spacing-4)">
                    <button type="button" class="btn btn--outline" onclick="cerrarModal('modalCrearCarpeta')">Cancelar</button>
                    <button type="submit" class="btn btn--primary">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cerrarModal(id) {
    document.getElementById(id).classList.remove('modal--open');
}

function verArchivo(filePath, fileName, extension) {
    var modal = document.getElementById('modalVerArchivo');
    var titulo = document.getElementById('verArchivo_titulo');
    var contenido = document.getElementById('verArchivo_contenido');

    titulo.textContent = fileName;
    contenido.innerHTML = '<p style="color:var(--color-gray);text-align:center">Cargando...</p>';
    modal.classList.add('modal--open');

    fetch('<?= url('/admin/mantenimiento/archivos/ver') ?>?path=' + encodeURIComponent(filePath))
        .then(function(response) {
            if (!response.ok) throw new Error('Error al cargar el archivo');
            return response.json();
        })
        .then(function(data) {
            if (data.error) throw new Error(data.error);
            if (data.type === 'image') {
                contenido.innerHTML = '<div style="text-align:center"><img src="<?= url('/') ?>/' + data.path + '" alt="' + data.name.replace(/"/g, '&quot;') + '" style="max-width:100%;max-height:60vh;border-radius:4px"></div>';
            } else {
                var pre = document.createElement('pre');
                pre.style.cssText = 'margin:0;padding:var(--spacing-4);background:var(--color-light);border-radius:4px;overflow-x:auto;font-size:0.8125rem;line-height:1.5;max-height:60vh;white-space:pre-wrap;word-break:break-all';
                var code = document.createElement('code');
                code.textContent = data.content;
                pre.appendChild(code);
                contenido.innerHTML = '';
                contenido.appendChild(pre);
            }
        })
        .catch(function(err) {
            contenido.innerHTML = '<p style="color:var(--color-danger);text-align:center">' + err.message + '</p>';
        });
}

function abrirRenombrar(ruta, nombre) {
    document.getElementById('renombrar_ruta').value = ruta;
    document.getElementById('renombrar_new_name').value = nombre;
    document.getElementById('modalRenombrar').classList.add('modal--open');
    setTimeout(function() {
        var input = document.getElementById('renombrar_new_name');
        input.focus();
        var dotIndex = nombre.lastIndexOf('.');
        if (dotIndex > 0) {
            input.setSelectionRange(0, dotIndex);
        } else {
            input.select();
        }
    }, 100);
}

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var modals = document.querySelectorAll('.modal.modal--open');
        modals.forEach(function(modal) {
            modal.classList.remove('modal--open');
        });
    }
});
</script>
