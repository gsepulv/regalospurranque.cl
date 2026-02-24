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
    var regUrl = siteUrl + '/registrar-comercio';
    var html = '';

    // ── EDITABLE: Saludo personalizado ──
    html += '<p style="color:#2D2D2D;margin:0 0 16px;line-height:1.6;font-size:16px;">&iexcl;Hola <strong>' + esc(nombre) + '</strong>!</p>';

    // ── EDITABLE: Parrafo de apertura personalizado ──
    html += '<p style="color:#4A4A4A;margin:0 0 20px;line-height:1.7;font-size:15px;">Muchas gracias por escribirnos y por su inter&eacute;s en ser parte de <strong>Regalos Purranque</strong>. La respuesta es s&iacute;, &iexcl;por supuesto que puede incluirse!</p>';

    // ── EDITABLE: Mencion de categoria especial ──
    if (categoria) {
        html += '<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">';
        html += '<tr><td style="background:#FFF8F0;border:2px solid #D4A017;border-radius:8px;padding:18px 20px;">';
        html += '<p style="margin:0 0 6px;font-size:16px;font-weight:bold;color:#CC0000;">&#127991; Categor&iacute;a: ' + esc(categoria) + '</p>';
        html += '<p style="margin:0;color:#4A4A4A;font-size:14px;line-height:1.6;">Le sugerimos registrarse en esta categor&iacute;a.</p>';
        html += '</td></tr></table>';
    }

    // ── EDITABLE: Parrafo de fecha especial ──
    if (inclFecha && proximaFecha) {
        html += '<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">';
        html += '<tr><td style="background:#FFF8F0;border-left:4px solid #CC0000;border-radius:4px;padding:16px 20px;">';
        html += '<p style="margin:0;color:#4A4A4A;font-size:15px;line-height:1.7;">Se acerca <strong>' + esc(proximaFecha.nombre) + '</strong> (' + proximaFecha.fecha_inicio + '). Si registra su emprendimiento ahora, estar&aacute; visible cuando la gente busque regalos para esta fecha.</p>';
        html += '</td></tr></table>';
    }

    // ── ESTANDAR: Que es Regalos Purranque ──
    html += seccionTitulo('&#127873; &iquest;Qu&eacute; es Regalos Purranque?');
    html += '<p style="color:#4A4A4A;margin:0 0 20px;line-height:1.7;font-size:15px;">Regalos Purranque es una plataforma web que re&uacute;ne a los comercios, emprendedores y artesanos de Purranque y la provincia en un solo lugar. Nuestro objetivo es que las personas encuentren f&aacute;cilmente d&oacute;nde comprar regalos para cada ocasi&oacute;n especial, apoyando al comercio local.</p>';

    // ── ESTANDAR: Beneficios ──
    html += seccionTitulo('&#11088; Beneficios de estar en la plataforma');
    html += '<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;border:1px solid #E8D5B0;border-radius:8px;overflow:hidden;">';
    var beneficios = [
        'P&aacute;gina exclusiva de su negocio con fotos, contacto y horarios',
        'Visibilidad en b&uacute;squedas locales de regalos en Purranque',
        'Aparici&oacute;n en fechas especiales (D&iacute;a de la Madre, Navidad, etc.)',
        'Enlace directo a su WhatsApp y redes sociales',
        'Panel de administraci&oacute;n para gestionar su informaci&oacute;n'
    ];
    for (var i = 0; i < beneficios.length; i++) {
        var bg = i % 2 === 0 ? '#FFF8F0' : '#FFFFFF';
        var brd = i < beneficios.length - 1 ? 'border-bottom:1px solid #E8D5B0;' : '';
        html += '<tr><td style="background:' + bg + ';padding:12px 16px;' + brd + '">';
        html += '<p style="margin:0;color:#2D2D2D;font-size:14px;">&#10004;&#65039; <strong>' + beneficios[i].split('</strong>')[0].replace(/<strong>/, '') + '</strong>' + (beneficios[i].includes('</strong>') ? beneficios[i].split('</strong>')[1] : '') + '</p>';
        html += '</td></tr>';
    }
    html += '</table>';

    // Caja Plan Gratuito
    html += '<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">';
    html += '<tr><td style="background:#FFF8F0;border:3px solid #D4A017;border-radius:10px;padding:20px;text-align:center;">';
    html += '<p style="margin:0 0 6px;font-size:20px;font-weight:bold;color:#CC0000;">&#127881; Plan Gratuito &mdash; Sin Costo por 30 d&iacute;as</p>';
    html += '<p style="margin:0;font-size:15px;color:#4A4A4A;">Estamos en etapa Beta. Su negocio ser&aacute; publicado sin ning&uacute;n costo durante 30 d&iacute;as.</p>';
    html += '</td></tr></table>';

    // ── ESTANDAR: Como registrarse ──
    html += seccionTitulo('&#128221; &iquest;C&oacute;mo registrarse?');
    html += '<p style="color:#4A4A4A;margin:0 0 12px;line-height:1.6;font-size:15px;">Ingrese a la siguiente direcci&oacute;n y complete el formulario:</p>';
    html += '<p style="margin:0 0 20px;text-align:center;"><a href="' + regUrl + '" style="display:inline-block;background:#CC0000;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:16px;font-weight:bold;">Registrar mi comercio</a></p>';

    var pasos = [
        'Ingrese a <a href="' + regUrl + '" style="color:#CC0000;text-decoration:underline;font-weight:bold;">' + regUrl + '</a>',
        'Cree su cuenta con email y contrase&ntilde;a',
        'Complete la informaci&oacute;n de su negocio',
        'Suba su logo y foto de portada',
        'Seleccione sus categor&iacute;as y fechas especiales'
    ];
    html += '<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">';
    for (var j = 0; j < pasos.length; j++) {
        html += '<tr><td style="padding:8px 0;"><p style="margin:0;font-size:15px;color:#2D2D2D;line-height:1.7;">';
        html += '<span style="display:inline-block;width:28px;height:28px;background:#CC0000;color:#ffffff;border-radius:50%;text-align:center;line-height:28px;font-weight:bold;font-size:14px;margin-right:8px;">' + (j+1) + '</span>';
        html += pasos[j] + '</p></td></tr>';
    }
    html += '</table>';

    // ── ESTANDAR: Datos requeridos ──
    html += seccionTitulo('&#128203; Datos que necesitar&aacute;');
    html += seccionDatos('&#128100; DATOS PERSONALES (para crear su cuenta)', [
        'Su nombre completo',
        'Email (ser&aacute; su usuario para acceder a la plataforma)',
        'Tel&eacute;fono / WhatsApp',
        'Contrase&ntilde;a'
    ]);
    html += '<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFF8F0;border:1px solid #D4A017;border-radius:6px;margin:0 0 12px;"><tr><td style="padding:12px 16px;">';
    html += '<p style="margin:0 0 6px;color:#CC0000;font-size:14px;font-weight:bold;">&#128274; Importante sobre su contrase&ntilde;a:</p>';
    html += '<p style="margin:0;color:#4A4A4A;font-size:14px;line-height:1.5;">Gu&aacute;rdela en un lugar seguro. Si la olvida: <a href="' + siteUrl + '/mi-comercio/olvide-contrasena" style="color:#CC0000;text-decoration:underline;">' + siteUrl + '/mi-comercio/olvide-contrasena</a></p>';
    html += '</td></tr></table>';

    html += seccionDatos('&#127978; INFORMACI&Oacute;N DE SU NEGOCIO', [
        'Nombre del comercio',
        'Descripci&oacute;n de su negocio',
        'WhatsApp de contacto para sus clientes',
        'Tel&eacute;fono fijo',
        'Email del comercio',
        'Sitio web o enlace a su red social',
        'Direcci&oacute;n f&iacute;sica (si tiene)'
    ]);
    html += seccionDatos('&#128444; IM&Aacute;GENES (JPG o PNG, m&aacute;x. 2 MB)', [
        '<strong>Logo</strong> &mdash; ideal 800 &times; 800 px',
        '<strong>Portada</strong> &mdash; ideal 1200 &times; 400 px'
    ]);
    html += '<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFF5F5;border:1px solid #E8B0B0;border-radius:6px;margin:0 0 12px;"><tr><td style="padding:10px 16px;">';
    html += '<p style="margin:0;color:#CC0000;font-size:13px;font-weight:bold;">&#9888;&#65039; Solo se publicar&aacute;n im&aacute;genes de productos o servicios. No se aceptar&aacute;n fotos de personas.</p>';
    html += '</td></tr></table>';

    html += seccionDatos('&#128241; RED SOCIAL', ['El Plan Gratuito permite incluir 1 red social (Facebook, Instagram, TikTok, etc.)']);
    html += seccionDatos('&#128194; CATEGOR&Iacute;AS', ['Seleccione las categor&iacute;as de su negocio y marque la principal']);
    html += seccionDatos('&#127873; FECHAS ESPECIALES', ['Seleccione las fechas para las que ofrece productos especiales']);

    // ── ESTANDAR: Politicas ──
    html += '<table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0 8px;"><tr><td style="border-bottom:3px solid #CC0000;padding:0 0 8px;">';
    html += '<p style="margin:0;font-size:18px;font-weight:bold;color:#CC0000;">&#9888;&#65039; Lectura Obligatoria Antes de Registrarse</p>';
    html += '</td></tr></table>';
    html += '<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFF5F5;border:2px solid #CC0000;border-radius:8px;margin:0 0 24px;"><tr><td style="padding:20px;">';
    html += '<p style="margin:0 0 14px;color:#CC0000;font-size:14px;line-height:1.6;">Para completar su registro es <strong>OBLIGATORIO</strong> leer los siguientes documentos:</p>';
    var politicas = ['T&eacute;rminos y Condiciones', 'Pol&iacute;tica de Privacidad', 'Pol&iacute;tica de Contenidos', 'Derechos de Autor', 'Pol&iacute;tica de Cookies'];
    var politicasUrls = ['/terminos', '/privacidad', '/contenidos', '/derechos', '/cookies'];
    for (var k = 0; k < politicas.length; k++) {
        html += '<p style="margin:0 0 6px;color:#CC0000;font-size:14px;">&#128209; ' + (k+1) + '. <a href="' + siteUrl + politicasUrls[k] + '" style="color:#CC0000;text-decoration:underline;font-weight:bold;">' + politicas[k] + '</a></p>';
    }
    html += '<p style="margin:14px 0 0;color:#CC0000;font-size:14px;font-weight:bold;">&#128683; El rechazo de cualquiera de estas pol&iacute;ticas impide la publicaci&oacute;n.</p>';
    html += '</td></tr></table>';

    // ── ESTANDAR: Que pasa despues ──
    html += seccionTitulo('&#128203; &iquest;Qu&eacute; pasa despu&eacute;s?');
    html += '<p style="color:#4A4A4A;margin:0 0 20px;line-height:1.7;font-size:15px;">Una vez que complete el formulario, nuestro equipo revisar&aacute; los datos. Si se ajustan a los t&eacute;rminos, su negocio ser&aacute; publicado y le notificaremos por email.</p>';

    // ── ESTANDAR: Edicion ──
    html += seccionTitulo('&#9998;&#65039; Edici&oacute;n de su publicaci&oacute;n');
    html += '<p style="color:#4A4A4A;margin:0 0 8px;line-height:1.7;font-size:15px;">Una vez publicado, podr&aacute; editar su informaci&oacute;n desde:</p>';
    html += '<p style="margin:0 0 20px;">&#128073; <a href="' + siteUrl + '/mi-comercio/" style="color:#CC0000;text-decoration:underline;font-weight:bold;">' + siteUrl + '/mi-comercio/</a></p>';

    // ── ESTANDAR: Contacto ──
    html += '<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFF8F0;border:1px solid #E8D5B0;border-radius:8px;margin:0 0 24px;"><tr><td style="padding:16px 20px;">';
    html += '<p style="margin:0 0 8px;font-size:15px;font-weight:bold;color:#CC0000;">&#128172; &iquest;Tiene dudas?</p>';
    html += '<p style="margin:0 0 4px;color:#4A4A4A;font-size:14px;">Puede responder directamente a este correo o escribirnos a:</p>';
    html += '<p style="margin:0 0 4px;color:#4A4A4A;font-size:14px;">&#128233; <a href="mailto:contacto@regalospurranque.cl" style="color:#CC0000;">contacto@regalospurranque.cl</a></p>';
    html += '<p style="margin:0;color:#4A4A4A;font-size:14px;">&#127760; <a href="' + siteUrl + '/contacto" style="color:#CC0000;">' + siteUrl + '/contacto</a></p>';
    html += '</td></tr></table>';

    html += firmaCorporativa();
    return html;
}

// Helpers para HTML corporativo
function seccionTitulo(titulo) {
    return '<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 8px;"><tr><td style="border-bottom:3px solid #D4A017;padding:0 0 8px;">' +
           '<p style="margin:0;font-size:18px;font-weight:bold;color:#CC0000;">' + titulo + '</p>' +
           '</td></tr></table>';
}

function seccionDatos(titulo, items) {
    var h = '<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFFBF5;border:1px solid #E8D5B0;border-radius:8px;margin:0 0 12px;">';
    h += '<tr><td style="background:#D4A017;padding:10px 16px;border-radius:8px 8px 0 0;">';
    h += '<p style="margin:0;font-size:15px;font-weight:bold;color:#ffffff;">' + titulo + '</p></td></tr>';
    h += '<tr><td style="padding:14px 16px;">';
    for (var i = 0; i < items.length; i++) {
        h += '<p style="margin:0' + (i < items.length - 1 ? ' 0 4px' : '') + ';color:#4A4A4A;font-size:14px;">&#8226; ' + items[i] + '</p>';
    }
    h += '</td></tr></table>';
    return h;
}

function firmaCorporativa() {
    var siteUrl = '<?= SITE_URL ?>';
    return '<table width="100%" cellpadding="0" cellspacing="0" style="border-top:2px solid #D4A017;margin:0;">' +
        '<tr><td style="padding:20px 0 0;text-align:center;">' +
        '<p style="margin:0 0 4px;color:#4A4A4A;font-size:15px;">&iexcl;Bienvenida/o a la comunidad de Regalos Purranque! &#127873;</p>' +
        '<p style="margin:0 0 4px;color:#4A4A4A;font-size:15px;">Saludos cordiales,</p>' +
        '<p style="margin:0 0 8px;color:#2D2D2D;font-size:16px;font-weight:bold;">Equipo Regalos Purranque</p>' +
        '<p style="margin:0 0 4px;"><a href="' + siteUrl + '" style="color:#CC0000;text-decoration:underline;font-weight:bold;">' + siteUrl + '</a></p>' +
        '</td></tr></table>' +
        '<p style="color:#999999;font-size:12px;margin:16px 0 0;line-height:1.5;text-align:center;">&#128231; Si no encuentra este correo en su bandeja, revise Spam o Correo no deseado.</p>';
}

function tplBienvenida(nombre, comercio) {
    var siteUrl = '<?= SITE_URL ?>';
    var html = '';
    html += '<p style="color:#2D2D2D;margin:0 0 16px;line-height:1.6;font-size:16px;">&iexcl;Hola <strong>' + esc(nombre) + '</strong>!</p>';
    html += '<p style="color:#4A4A4A;margin:0 0 20px;line-height:1.7;font-size:15px;">&iexcl;Felicitaciones! Su negocio' + (comercio ? ' <strong>' + esc(comercio) + '</strong>' : '') + ' ya est&aacute; publicado en Regalos Purranque.</p>';
    html += '<p style="color:#4A4A4A;margin:0 0 8px;line-height:1.7;font-size:15px;">Puede administrar su informaci&oacute;n en cualquier momento desde:</p>';
    html += '<p style="margin:0 0 20px;">&#128073; <a href="' + siteUrl + '/mi-comercio" style="color:#CC0000;text-decoration:underline;font-weight:bold;">' + siteUrl + '/mi-comercio</a></p>';
    html += '<p style="color:#4A4A4A;margin:0 0 20px;line-height:1.7;font-size:15px;">Desde ah&iacute; podr&aacute; actualizar su descripci&oacute;n, fotos, horarios y datos de contacto.</p>';
    html += firmaCorporativa();
    return html;
}

function tplInstrucciones(nombre) {
    var siteUrl = '<?= SITE_URL ?>';
    var regUrl = siteUrl + '/registrar-comercio';
    var html = '';
    html += '<p style="color:#2D2D2D;margin:0 0 16px;line-height:1.6;font-size:16px;">&iexcl;Hola <strong>' + esc(nombre) + '</strong>!</p>';
    html += '<p style="color:#4A4A4A;margin:0 0 20px;line-height:1.7;font-size:15px;">A continuaci&oacute;n le enviamos las instrucciones detalladas para registrar su negocio en Regalos Purranque:</p>';
    html += '<p style="margin:0 0 20px;text-align:center;"><a href="' + regUrl + '" style="display:inline-block;background:#CC0000;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:16px;font-weight:bold;">Registrar mi comercio</a></p>';

    html += seccionDatos('&#128100; DATOS PERSONALES', ['Nombre completo', 'Email', 'Tel&eacute;fono / WhatsApp', 'Contrase&ntilde;a']);
    html += seccionDatos('&#127978; INFORMACI&Oacute;N DEL NEGOCIO', ['Nombre del comercio', 'Descripci&oacute;n', 'WhatsApp, tel&eacute;fono, email', 'Sitio web o red social', 'Direcci&oacute;n f&iacute;sica']);
    html += seccionDatos('&#128444; IM&Aacute;GENES (m&aacute;x. 2 MB)', ['<strong>Logo</strong> &mdash; 800 &times; 800 px', '<strong>Portada</strong> &mdash; 1200 &times; 400 px']);
    html += '<p style="color:#4A4A4A;margin:0 0 20px;line-height:1.7;font-size:15px;">Tambi&eacute;n podr&aacute; seleccionar sus categor&iacute;as y fechas especiales en el formulario.</p>';
    html += '<p style="color:#4A4A4A;margin:0 0 20px;line-height:1.7;font-size:15px;">Si tiene cualquier duda, responda a este correo.</p>';
    html += firmaCorporativa();
    return html;
}

function tplCorreoLibre(nombre) {
    var html = '';
    html += '<p style="color:#2D2D2D;margin:0 0 16px;line-height:1.6;font-size:16px;">&iexcl;Hola <strong>' + esc(nombre) + '</strong>!</p>';
    html += '<p style="color:#4A4A4A;margin:0 0 20px;line-height:1.7;font-size:15px;"></p>';
    html += firmaCorporativa();
    return html;
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
