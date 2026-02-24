<div class="admin-page">
    <div class="admin-page__header">
        <h1>Enviar Correo</h1>
        <div class="toolbar">
            <?php if ($mensaje): ?>
                <a href="<?= url('/admin/contacto') ?>" class="btn btn--outline">Volver a mensajes</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($mensaje): ?>
    <!-- Mensaje original -->
    <div class="card" style="margin-bottom:1.5rem;border-left:4px solid #2563eb;">
        <div class="card__header">
            <h3>Mensaje original</h3>
        </div>
        <div class="card__body">
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong><?= e($mensaje['nombre']) ?></strong> &lt;<?= e($mensaje['email']) ?>&gt; — <?= date('d/m/Y H:i', strtotime($mensaje['created_at'])) ?>
            </p>
            <p style="margin:0 0 4px;"><strong>Asunto:</strong> <?= e($mensaje['asunto']) ?></p>
            <p style="margin:0;white-space:pre-wrap;color:#334155;"><?= e($mensaje['mensaje']) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= url('/admin/correos/enviar') ?>" id="formCorreo">
        <?= csrf_field() ?>
        <input type="hidden" name="mensaje_id" value="<?= $mensaje['id'] ?? 0 ?>">

        <!-- Destinatario -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header"><h3>Destinatario</h3></div>
            <div class="card__body">
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Email *</label>
                        <input type="email" name="para" id="para" class="form-control" required
                               value="<?= e(old('para', $mensaje['email'] ?? '')) ?>">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control"
                               value="<?= e(old('nombre', $mensaje['nombre'] ?? '')) ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Comercio</label>
                        <input type="text" name="comercio" id="comercio" class="form-control"
                               value="<?= e(old('comercio', '')) ?>">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Asunto *</label>
                        <input type="text" name="asunto" id="asunto" class="form-control" required
                               value="<?= e(old('asunto', $mensaje ? 'Re: ' . $mensaje['asunto'] : '')) ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Plantilla -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header"><h3>Plantilla</h3></div>
            <div class="card__body">
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Seleccionar plantilla</label>
                        <select id="plantilla" class="form-control">
                            <option value="">— Sin plantilla —</option>
                            <option value="consulta-inscripcion">Respuesta a consulta de inscripción</option>
                            <option value="bienvenida">Bienvenida</option>
                            <option value="instrucciones">Instrucciones de registro</option>
                            <option value="correo-libre">Correo libre (saludo + firma)</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Categoría sugerida</label>
                        <select id="categoria" class="form-control">
                            <option value="">— Ninguna —</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= e($cat['nombre']) ?>"><?= e($cat['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" id="incluirFecha" <?= $proximaFecha ? '' : 'disabled' ?>>
                        Incluir próxima fecha especial
                        <?php if ($proximaFecha): ?>
                            (<?= e($proximaFecha['nombre']) ?> — <?= date('d/m/Y', strtotime($proximaFecha['fecha_inicio'])) ?>)
                        <?php else: ?>
                            <small style="color:#94a3b8;">(no hay fechas próximas)</small>
                        <?php endif; ?>
                    </label>
                </div>
            </div>
        </div>

        <!-- Contenido -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header"><h3>Contenido del correo</h3></div>
            <div class="card__body">
                <textarea name="contenido" id="contenido"><?= old('contenido', '') ?></textarea>
            </div>
        </div>

        <!-- Acciones -->
        <div class="toolbar" style="margin-bottom:0;">
            <button type="button" class="btn btn--outline" onclick="previewEmail()">Vista previa</button>
            <button type="submit" class="btn btn--primary">Enviar correo</button>
            <?php if ($mensaje): ?>
                <a href="<?= url('/admin/contacto') ?>" class="btn btn--outline">Cancelar</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Modal preview -->
<div class="modal" id="previewModal">
    <div class="modal__overlay" data-modal-close></div>
    <div class="modal__content" style="max-width:700px;">
        <div class="modal__header">
            <h3>Vista previa del correo</h3>
            <button class="modal__close" data-modal-close>&times;</button>
        </div>
        <div class="modal__body" style="padding:0;">
            <iframe id="previewFrame" style="width:100%;height:500px;border:none;"></iframe>
        </div>
        <div class="modal__footer">
            <button class="btn btn--outline" data-modal-close>Cerrar</button>
        </div>
    </div>
</div>

<!-- TinyMCE -->
<script src="<?= asset('vendor/tinymce/tinymce.min.js') ?>"></script>

<script>
tinymce.init({
    selector: '#contenido',
    height: 500,
    language: 'es',
    language_url: '<?= asset('vendor/tinymce/langs/es.js') ?>',
    plugins: 'advlist autolink lists link charmap table code fullscreen emoticons',
    toolbar: [
        'undo redo | styles | bold italic underline strikethrough | forecolor backcolor | removeformat',
        'alignleft aligncenter alignright | bullist numlist outdent indent | link | table emoticons charmap | code fullscreen'
    ],
    content_style: 'body { font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.6; padding: 16px; color: #475569; }',
    valid_elements: '*[*]',
    paste_as_text: false,
    menubar: false,
    branding: false,
    promotion: false,
    setup: function(editor) {
        editor.on('init', function() {
            <?php if ($mensaje): ?>
            // Auto-seleccionar plantilla si viene de un mensaje
            setTimeout(function() {
                document.getElementById('plantilla').value = 'consulta-inscripcion';
                applyTemplate('consulta-inscripcion');
            }, 300);
            <?php endif; ?>
        });
    }
});

// Datos de próxima fecha para templates
var proximaFecha = <?= json_encode($proximaFecha ? [
    'nombre'       => $proximaFecha['nombre'],
    'fecha_inicio' => date('d/m/Y', strtotime($proximaFecha['fecha_inicio'])),
] : null) ?>;

// Cambio de plantilla
document.getElementById('plantilla').addEventListener('change', function() {
    var tpl = this.value;
    if (!tpl) return;

    var editor = tinymce.get('contenido');
    var current = editor ? editor.getContent() : '';
    if (current.trim() !== '' && !confirm('¿Reemplazar el contenido actual con la plantilla?')) {
        this.value = '';
        return;
    }
    applyTemplate(tpl);
});

function applyTemplate(tpl) {
    var nombre   = document.getElementById('nombre').value || 'estimado/a';
    var comercio = document.getElementById('comercio').value || '';
    var catSel   = document.getElementById('categoria');
    var categoria = catSel.value || '';
    var inclFecha = document.getElementById('incluirFecha').checked;
    var html = '';

    switch (tpl) {
        case 'consulta-inscripcion':
            html = tplConsultaInscripcion(nombre, comercio, categoria, inclFecha);
            break;
        case 'bienvenida':
            html = tplBienvenida(nombre, comercio);
            break;
        case 'instrucciones':
            html = tplInstrucciones(nombre);
            break;
        case 'correo-libre':
            html = tplCorreoLibre(nombre);
            break;
    }

    var editor = tinymce.get('contenido');
    if (editor) editor.setContent(html);
}

function tplConsultaInscripcion(nombre, comercio, categoria, inclFecha) {
    var siteUrl = '<?= SITE_URL ?>';
    var html = '';
    html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;">&iexcl;Hola <strong>' + esc(nombre) + '</strong>!</p>';
    html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;">Muchas gracias por escribirnos y por tu inter&eacute;s en ser parte de <strong>Regalos Purranque</strong>.</p>';

    html += '<table width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;margin:0 0 16px;">';
    html += '<tr><td style="padding:16px;">';
    html += '<p style="margin:0 0 8px;font-size:15px;font-weight:bold;color:#1e40af;">&#128640; Estamos en etapa Beta</p>';
    html += '<p style="margin:0;color:#1e40af;font-size:14px;line-height:1.5;">Tu negocio podr&aacute; ser publicado en nuestro <strong>Plan Gratuito</strong> (sin costo), con una duraci&oacute;n de 30 d&iacute;as.</p>';
    html += '</td></tr></table>';

    html += '<p style="color:#475569;margin:0 0 12px;line-height:1.6;">Algunos beneficios de estar en la plataforma:</p>';
    html += '<ul style="color:#475569;margin:0 0 16px;padding-left:20px;line-height:1.8;">';
    html += '<li>Visibilidad en b&uacute;squedas locales de regalos</li>';
    html += '<li>P&aacute;gina exclusiva de tu negocio con fotos, contacto y horarios</li>';
    html += '<li>Aparici&oacute;n en fechas especiales (D&iacute;a de la Madre, Navidad, etc.)</li>';
    html += '<li>Enlace directo a tu WhatsApp y redes sociales</li>';
    html += '</ul>';

    if (categoria) {
        html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;">Seg&uacute;n lo que nos comentas, te sugerimos registrarte en la categor&iacute;a <strong>&ldquo;' + esc(categoria) + '&rdquo;</strong>.</p>';
    }

    html += '<p style="color:#1e293b;font-size:15px;font-weight:bold;margin:0 0 8px;">Pasos para registrarte:</p>';
    html += '<ol style="color:#475569;margin:0 0 16px;padding-left:20px;line-height:1.8;">';
    html += '<li>Ingresa a <a href="' + siteUrl + '/registrar-comercio" style="color:#2563eb;">' + siteUrl + '/registrar-comercio</a></li>';
    html += '<li>Crea tu cuenta con email y contrase&ntilde;a</li>';
    html += '<li>Completa la informaci&oacute;n de tu negocio (nombre, descripci&oacute;n, contacto)</li>';
    html += '<li>Sube tu logo y foto de portada</li>';
    html += '<li>Selecciona tus categor&iacute;as y fechas especiales</li>';
    html += '</ol>';

    html += '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;margin:0 0 16px;">';
    html += '<tr><td style="padding:16px;text-align:center;">';
    html += '<p style="margin:0;font-size:16px;font-weight:bold;color:#15803d;">&#127881; La inscripci&oacute;n es 100% gratuita</p>';
    html += '</td></tr></table>';

    if (inclFecha && proximaFecha) {
        html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;">Adem&aacute;s, se acerca <strong>' + esc(proximaFecha.nombre) + '</strong> (' + proximaFecha.fecha_inicio + '), as&iacute; que es un excelente momento para registrarte y aparecer en las b&uacute;squedas de esa fecha.</p>';
    }

    html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;">Si tienes cualquier duda durante el registro, no dudes en responder a este correo.</p>';
    html += firma();
    return html;
}

function tplBienvenida(nombre, comercio) {
    var siteUrl = '<?= SITE_URL ?>';
    var html = '';
    html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;">&iexcl;Hola <strong>' + esc(nombre) + '</strong>!</p>';
    html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;">&iexcl;Felicitaciones! Tu negocio' + (comercio ? ' <strong>' + esc(comercio) + '</strong>' : '') + ' ya est&aacute; publicado en Regalos Purranque.</p>';
    html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;">Puedes administrar tu informaci&oacute;n en cualquier momento desde:</p>';
    html += '<p style="margin:0 0 16px;"><a href="' + siteUrl + '/mi-comercio" style="color:#2563eb;text-decoration:underline;">' + siteUrl + '/mi-comercio</a></p>';
    html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;">Desde ah&iacute; podr&aacute;s actualizar tu descripci&oacute;n, fotos, horarios y datos de contacto.</p>';
    html += firma();
    return html;
}

function tplInstrucciones(nombre) {
    var siteUrl = '<?= SITE_URL ?>';
    var html = '';
    html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;">&iexcl;Hola <strong>' + esc(nombre) + '</strong>!</p>';
    html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;">A continuaci&oacute;n te enviamos las instrucciones detalladas para registrar tu negocio en Regalos Purranque:</p>';
    html += '<p style="margin:0 0 16px;">&#128073; <a href="' + siteUrl + '/registrar-comercio" style="color:#2563eb;text-decoration:underline;">' + siteUrl + '/registrar-comercio</a></p>';

    html += '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;margin:0 0 16px;">';
    html += '<tr><td style="padding:16px;">';
    html += '<p style="margin:0 0 10px;font-size:15px;font-weight:bold;color:#15803d;">&#128100; DATOS PERSONALES (para crear tu cuenta)</p>';
    html += '<p style="margin:0 0 4px;color:#166534;font-size:14px;">&#8226; Tu nombre completo</p>';
    html += '<p style="margin:0 0 4px;color:#166534;font-size:14px;">&#8226; Email (ser&aacute; tu usuario)</p>';
    html += '<p style="margin:0 0 4px;color:#166534;font-size:14px;">&#8226; Tel&eacute;fono / WhatsApp</p>';
    html += '<p style="margin:0;color:#166534;font-size:14px;">&#8226; Contrase&ntilde;a</p>';
    html += '</td></tr></table>';

    html += '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;margin:0 0 16px;">';
    html += '<tr><td style="padding:16px;">';
    html += '<p style="margin:0 0 10px;font-size:15px;font-weight:bold;color:#15803d;">&#127978; INFORMACI&Oacute;N DEL NEGOCIO</p>';
    html += '<p style="margin:0 0 4px;color:#166534;font-size:14px;">&#8226; Nombre del comercio</p>';
    html += '<p style="margin:0 0 4px;color:#166534;font-size:14px;">&#8226; Descripci&oacute;n (qu&eacute; vendes, marcas, etc.)</p>';
    html += '<p style="margin:0 0 4px;color:#166534;font-size:14px;">&#8226; WhatsApp, tel&eacute;fono, email del comercio</p>';
    html += '<p style="margin:0 0 4px;color:#166534;font-size:14px;">&#8226; Sitio web o red social</p>';
    html += '<p style="margin:0;color:#166534;font-size:14px;">&#8226; Direcci&oacute;n f&iacute;sica (si tienes)</p>';
    html += '</td></tr></table>';

    html += '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;margin:0 0 16px;">';
    html += '<tr><td style="padding:16px;">';
    html += '<p style="margin:0 0 10px;font-size:15px;font-weight:bold;color:#15803d;">&#128444; IM&Aacute;GENES (JPG o PNG, m&aacute;x. 2 MB)</p>';
    html += '<p style="margin:0 0 4px;color:#166534;font-size:14px;">&#8226; <strong>Logo</strong> &mdash; ideal 800 x 800 px</p>';
    html += '<p style="margin:0;color:#166534;font-size:14px;">&#8226; <strong>Portada</strong> &mdash; ideal 1200 x 400 px</p>';
    html += '</td></tr></table>';

    html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;">Tambi&eacute;n podr&aacute;s seleccionar tus categor&iacute;as y fechas especiales en el formulario.</p>';
    html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;">Si tienes cualquier duda, responde a este correo.</p>';
    html += firma();
    return html;
}

function tplCorreoLibre(nombre) {
    var html = '';
    html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;">&iexcl;Hola <strong>' + esc(nombre) + '</strong>!</p>';
    html += '<p style="color:#475569;margin:0 0 16px;line-height:1.6;"></p>';
    html += firma();
    return html;
}

function firma() {
    return '<p style="color:#475569;margin:0 0 4px;line-height:1.6;">Saludos cordiales,</p>' +
           '<p style="color:#475569;margin:0 0 4px;line-height:1.6;font-weight:bold;">Equipo Regalos Purranque</p>' +
           '<p style="margin:0 0 16px;"><a href="<?= SITE_URL ?>" style="color:#2563eb;text-decoration:underline;"><?= SITE_URL ?></a></p>';
}

function esc(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Vista previa
function previewEmail() {
    var editor = tinymce.get('contenido');
    var content = editor ? editor.getContent() : '';
    if (!content.trim()) {
        alert('Escribe contenido antes de ver la vista previa.');
        return;
    }

    fetch('<?= url('/admin/correos/preview') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_csrf=<?= csrf_token() ?>&contenido=' + encodeURIComponent(content)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('previewFrame').srcdoc = data.html;
        document.getElementById('previewModal').classList.add('modal--visible');
    })
    .catch(function() {
        alert('Error al generar vista previa.');
    });
}
</script>
